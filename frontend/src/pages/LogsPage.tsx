import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Alert,
  DataTable,
  DatePicker,
  MultiSelect,
  PageTitle,
  Pagination,
  useTablePreferences,
  type ColumnDef,
  type SortState,
} from '@maya/shared-ui-react';
import { useTranslation } from 'react-i18next';
import { useSearchParams } from 'react-router-dom';
import { fetchApplications } from '../api/applications';
import { fetchLogs, type LogsFilters as ApiLogsFilters, type LogsSortBy } from '../api/logs';
import type { LogsFiltersState } from '../components/logs';
import { SeverityBadge, severityLabel } from '../components/severity';
import { useLogStream } from '../hooks';
import type { PaginatedResponse, SortDir } from '../types/api';
import type { ApplicationRef, Log } from '../types/logs';
import { LOG_SEVERITY_KEYS } from '../types/logs';
import { formatDateTime } from '../utils/date';

export type LogsSortKey = 'application' | 'severity' | 'created_at';

const VALID_SORT_COLUMNS: readonly LogsSortKey[] = ['application', 'severity', 'created_at'];
const VALID_SORT_DIRS: readonly SortDir[] = ['asc', 'desc'];

const inputClass =
  'w-full bg-ui-card dark:bg-ui-dark-card border border-ui-border dark:border-ui-dark-border rounded-md text-text-primary dark:text-text-dark-primary px-3 py-2 text-sm outline-none focus:border-odoo-purple dark:focus:border-odoo-dark-purple transition-colors';
const fieldLabelClass =
  'text-text-secondary dark:text-text-dark-secondary text-xs font-medium uppercase tracking-wide';

type ListState =
  | { status: 'loading'; data: PaginatedResponse<Log> | null }
  | { status: 'ready'; data: PaginatedResponse<Log> }
  | { status: 'error'; error: string; data: PaginatedResponse<Log> | null };

function truncate(text: string | null | undefined, max = 120): string {
  if (!text) return '-';
  if (text.length <= max) return text;
  return `${text.slice(0, max)}…`;
}

function parseFiltersFromUrl(params: URLSearchParams): {
  filters: LogsFiltersState;
  sortBy: LogsSortKey | null;
  sortDir: SortDir | null;
  page: number;
} {
  const severityRaw = params.get('severity');
  const severity = severityRaw
    ? severityRaw
        .split(',')
        .map((s) => s.trim())
        .filter((s) => (LOG_SEVERITY_KEYS as readonly string[]).includes(s))
    : [];

  const applicationIdRaw = params.get('application_id');
  const applicationId = applicationIdRaw ? Number(applicationIdRaw) : null;

  const resolvedRaw = params.get('resolved');
  const resolved: LogsFiltersState['resolved'] =
    resolvedRaw === 'only' || resolvedRaw === 'unresolved' ? resolvedRaw : 'all';

  const sortByRaw = params.get('sort_by');
  const sortBy = (VALID_SORT_COLUMNS as readonly string[]).includes(sortByRaw ?? '')
    ? (sortByRaw as LogsSortKey)
    : null;

  const sortDirRaw = params.get('sort_dir');
  const sortDir = (VALID_SORT_DIRS as readonly string[]).includes(sortDirRaw ?? '')
    ? (sortDirRaw as SortDir)
    : null;

  const pageRaw = params.get('page');
  const pageNum = pageRaw ? Number(pageRaw) : 1;
  const page = Number.isFinite(pageNum) && pageNum > 0 ? Math.floor(pageNum) : 1;

  return {
    filters: {
      search: params.get('search') ?? '',
      severity,
      applicationId: applicationId != null && !Number.isNaN(applicationId) ? applicationId : null,
      dateFrom: params.get('date_from'),
      dateTo: params.get('date_to'),
      resolved,
    },
    sortBy,
    sortDir,
    page,
  };
}

function writeFiltersToUrl(
  filters: LogsFiltersState,
  sortBy: LogsSortKey | null,
  sortDir: SortDir | null,
  page: number,
): URLSearchParams {
  const qs = new URLSearchParams();
  if (filters.search.trim() !== '') qs.set('search', filters.search.trim());
  if (filters.severity.length > 0) qs.set('severity', filters.severity.join(','));
  if (filters.applicationId != null) qs.set('application_id', String(filters.applicationId));
  if (filters.dateFrom) qs.set('date_from', filters.dateFrom);
  if (filters.dateTo) qs.set('date_to', filters.dateTo);
  if (filters.resolved !== 'all') qs.set('resolved', filters.resolved);
  if (sortBy) qs.set('sort_by', sortBy);
  if (sortDir) qs.set('sort_dir', sortDir);
  if (page > 1) qs.set('page', String(page));
  return qs;
}

function toApiFilters(
  filters: LogsFiltersState,
  sortBy: LogsSortKey | null,
  sortDir: SortDir | null,
  page: number,
): ApiLogsFilters {
  return {
    search: filters.search.trim() || null,
    severity: filters.severity.length > 0 ? filters.severity : null,
    application_id: filters.applicationId ?? null,
    archived: 'without',
    resolved: filters.resolved === 'all' ? null : filters.resolved,
    date_from: filters.dateFrom,
    date_to: filters.dateTo,
    sort_by: sortBy ? (sortBy as LogsSortBy) : null,
    sort_dir: sortDir,
    page,
  };
}

function countActiveFilters(f: LogsFiltersState): number {
  let n = 0;
  if (f.search.trim() !== '') n += 1;
  if (f.severity.length > 0) n += 1;
  if (f.applicationId != null) n += 1;
  if (f.dateFrom) n += 1;
  if (f.dateTo) n += 1;
  if (f.resolved !== 'all') n += 1;
  return n;
}

export function LogsPage() {
  const { t } = useTranslation('logs');
  const { t: tCommon } = useTranslation('common');
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [applications, setApplications] = useState<ApplicationRef[]>([]);
  const [state, setState] = useState<ListState>({ status: 'loading', data: null });
  const [refreshNonce, setRefreshNonce] = useState(0);
  const { hiddenIds, toggleHidden } = useTablePreferences({
    storageKey: 'maya:logs:logs-table',
  });
  const lastStreamIdRef = useRef<number | null>(null);

  const { filters, sortBy, sortDir, page } = useMemo(
    () => parseFiltersFromUrl(searchParams),
    [searchParams],
  );

  useEffect(() => {
    let cancelled = false;
    fetchApplications('with_logs')
      .then((apps) => {
        if (!cancelled) setApplications(apps);
      })
      .catch(() => {
        /* sin bloqueador: dejamos dropdown vacío */
      });
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    let cancelled = false;
    setState((prev) => ({ status: 'loading', data: prev.data }));
    fetchLogs(toApiFilters(filters, sortBy, sortDir, page))
      .then((data) => {
        if (!cancelled) setState({ status: 'ready', data });
      })
      .catch((e) => {
        if (!cancelled) {
          setState((prev) => ({
            status: 'error',
            error: e instanceof Error ? e.message : String(e),
            data: prev.data,
          }));
        }
      });
    return () => {
      cancelled = true;
    };
  }, [filters, sortBy, sortDir, page, refreshNonce]);

  const { payload: streamPayload } = useLogStream({ intervalMs: 5000 });

  useEffect(() => {
    if (!streamPayload || streamPayload.length === 0) return;
    const maxId = streamPayload.reduce((acc, item) => (item.id > acc ? item.id : acc), 0);
    if (lastStreamIdRef.current === null) {
      lastStreamIdRef.current = maxId;
      return;
    }
    if (maxId > lastStreamIdRef.current) {
      lastStreamIdRef.current = maxId;
      setRefreshNonce((n) => n + 1);
    }
  }, [streamPayload]);

  const updateFilters = useCallback(
    (patch: Partial<LogsFiltersState>) => {
      const next = { ...filters, ...patch };
      setSearchParams(writeFiltersToUrl(next, sortBy, sortDir, 1));
    },
    [filters, sortBy, sortDir, setSearchParams],
  );

  const resetFilters = useCallback(() => {
    const emptyFilters: LogsFiltersState = {
      search: '',
      severity: [],
      applicationId: null,
      dateFrom: null,
      dateTo: null,
      resolved: 'all',
    };
    setSearchParams(writeFiltersToUrl(emptyFilters, sortBy, sortDir, 1));
  }, [setSearchParams, sortBy, sortDir]);

  const changePage = useCallback(
    (nextPage: number) => {
      setSearchParams(writeFiltersToUrl(filters, sortBy, sortDir, nextPage));
    },
    [filters, sortBy, sortDir, setSearchParams],
  );

  const onSortChange = useCallback(
    (next: SortState) => {
      const column = next.columnId as LogsSortKey;
      if (!(VALID_SORT_COLUMNS as readonly string[]).includes(column)) return;
      setSearchParams(writeFiltersToUrl(filters, column, next.direction, 1));
    },
    [filters, setSearchParams],
  );

  const sortState: SortState | null = useMemo(
    () => (sortBy && sortDir ? { columnId: sortBy, direction: sortDir } : null),
    [sortBy, sortDir],
  );

  const columns: ColumnDef<Log>[] = useMemo(
    () => [
      {
        id: 'application',
        header: t('columns.application'),
        cell: (l) => l.application?.name ?? '-',
        sortable: true,
      },
      {
        id: 'severity',
        header: t('columns.severity'),
        cell: (l) => <SeverityBadge severity={l.severity} />,
        sortable: true,
      },
      {
        id: 'message',
        header: t('columns.message'),
        cell: (l) => (
          <span className="block break-words max-w-md">{truncate(l.message, 120)}</span>
        ),
      },
      {
        id: 'errorCode',
        header: t('columns.errorCode'),
        cell: (l) => l.error_code?.code ?? '-',
      },
      {
        id: 'created_at',
        header: t('columns.createdAt'),
        cell: (l) => formatDateTime(l.created_at),
        sortable: true,
      },
      {
        id: 'resolved',
        header: t('columns.resolved'),
        cell: (l) => (
          <span
            className={
              l.resolved
                ? 'inline-flex items-center rounded-full bg-success/10 px-2 py-0.5 text-xs font-medium text-success ring-1 ring-inset ring-success/20'
                : 'inline-flex items-center rounded-full bg-warning/10 px-2 py-0.5 text-xs font-medium text-warning ring-1 ring-inset ring-warning/20'
            }
          >
            {l.resolved ? t('detail.fields.resolved') : t('detail.fields.unresolved')}
          </span>
        ),
      },
    ],
    [t],
  );

  const pagination = state.data;
  const logs = pagination?.data ?? [];
  const meta = pagination?.meta;
  const startIndex = meta && meta.from != null ? meta.from : 0;
  const endIndex = meta && meta.to != null ? meta.to : 0;
  const total = meta?.total ?? 0;
  const activeCount = countActiveFilters(filters);

  const filtersPanel = (
    <>
      <div className="flex flex-col gap-1.5 min-w-0">
        <label className={fieldLabelClass} htmlFor="logs-filter-search">
          {tCommon('filters.searchLabel')}
        </label>
        <input
          id="logs-filter-search"
          type="search"
          value={filters.search}
          placeholder={t('filters.searchPlaceholder')}
          onChange={(e) => updateFilters({ search: e.target.value })}
          className={inputClass}
        />
      </div>
      <div className="flex flex-col gap-1.5 min-w-0">
        <label className={fieldLabelClass}>{t('filters.dateFrom')}</label>
        <DatePicker
          value={filters.dateFrom}
          onChange={(d) => updateFilters({ dateFrom: d })}
          placeholder={t('filters.dateFrom')}
          ariaLabel={t('filters.dateFrom')}
          max={filters.dateTo ?? undefined}
        />
      </div>
      <div className="flex flex-col gap-1.5 min-w-0">
        <label className={fieldLabelClass}>{t('filters.dateTo')}</label>
        <DatePicker
          value={filters.dateTo}
          onChange={(d) => updateFilters({ dateTo: d })}
          placeholder={t('filters.dateTo')}
          ariaLabel={t('filters.dateTo')}
          min={filters.dateFrom ?? undefined}
        />
      </div>
      <div className="flex flex-col gap-1.5 min-w-0">
        <label className={fieldLabelClass} htmlFor="logs-filter-application">
          {tCommon('filters.applicationLabel')}
        </label>
        <select
          id="logs-filter-application"
          value={filters.applicationId ?? ''}
          onChange={(e) => {
            const v = e.target.value;
            updateFilters({ applicationId: v === '' ? null : Number(v) });
          }}
          className={inputClass}
        >
          <option value="">{t('filters.applicationAll')}</option>
          {applications.map((app) => (
            <option key={app.id} value={app.id}>
              {app.name}
            </option>
          ))}
        </select>
      </div>
      <div className="flex flex-col gap-1.5 min-w-0">
        <label className={fieldLabelClass} htmlFor="logs-filter-resolved">
          {t('filters.resolved')}
        </label>
        <select
          id="logs-filter-resolved"
          value={filters.resolved}
          onChange={(e) =>
            updateFilters({ resolved: e.target.value as LogsFiltersState['resolved'] })
          }
          className={inputClass}
        >
          <option value="all">{tCommon('resolved.all')}</option>
          <option value="unresolved">{tCommon('resolved.unresolved')}</option>
          <option value="only">{tCommon('resolved.only')}</option>
        </select>
      </div>
      <div className="flex flex-col gap-1.5 min-w-0">
        <label className={fieldLabelClass}>{tCommon('filters.severityLabel')}</label>
        <MultiSelect
          options={LOG_SEVERITY_KEYS.map((key) => ({ value: key, label: severityLabel(key) }))}
          value={filters.severity}
          onChange={(next) => updateFilters({ severity: next })}
          placeholder={t('filters.severityAll', { defaultValue: 'Todas' })}
          ariaLabel={tCommon('filters.severityLabel')}
        />
      </div>
    </>
  );

  return (
    <div className="px-4 py-6 sm:px-6 lg:px-8">
      <PageTitle title={t('title')} />

      {state.status === 'error' && (
        <Alert tone="danger" className="mt-4">{t('loadError', { message: state.error })}
        </Alert>
      )}

      <div className="mt-3">
        <DataTable
          title={t('table.activeTitle', { defaultValue: 'Logs activos' })}
          columns={columns}
          rows={logs}
          rowKey={(l) => l.id}
          loading={state.status === 'loading'}
          hiddenColumnIds={hiddenIds}
          onToggleHiddenColumn={toggleHidden}
          filtersStorageKey="maya:logs:logs-table"
          filtersPanel={filtersPanel}
          filtersActiveCount={activeCount}
          onClearFilters={resetFilters}
          filtersDefaultOpen={false}
          sortBy={sortState}
          onSortChange={onSortChange}
          onRowClick={(l) => navigate(`/logs/${l.id}`)}
          emptyMessage={t('table.emptyText')}
        />
      </div>
      {meta && (
        <div className="mt-4">
          <Pagination
            currentPage={meta.current_page}
            totalPages={meta.last_page}
            onChange={changePage}
            ariaLabel={tCommon('pagination.ariaLabel')}
            prevLabel={tCommon('pagination.previous')}
            nextLabel={tCommon('pagination.next')}
            info={tCommon('pagination.rangeOf', { from: startIndex, to: endIndex, total })}
          />
        </div>
      )}
    </div>
  );
}
