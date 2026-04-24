import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useSearchParams } from 'react-router-dom';
import { fetchApplications } from '../api/applications';
import {
  fetchArchivedLogs,
  type ArchivedLogsFilters as ApiArchivedLogsFilters,
  type ArchivedLogsSortBy,
} from '../api/archivedLogs';
import {
  ArchivedLogsFilters,
  ArchivedLogsTable,
  type ArchivedLogsFiltersState,
  type ArchivedLogsSortKey,
} from '../components/archived-logs';
import { Pagination } from '../components/table';
import type { PaginatedResponse, SortDir } from '../types/api';
import type { ApplicationRef, ArchivedLog } from '../types/logs';
import { LOG_SEVERITY_KEYS } from '../types/logs';

const VALID_SORT_COLUMNS: readonly ArchivedLogsSortKey[] = [
  'application',
  'severity',
  'archived_at',
  'original_created_at',
];
const VALID_SORT_DIRS: readonly SortDir[] = ['asc', 'desc'];

type ListState =
  | { status: 'loading'; data: PaginatedResponse<ArchivedLog> | null }
  | { status: 'ready'; data: PaginatedResponse<ArchivedLog> }
  | { status: 'error'; error: string; data: PaginatedResponse<ArchivedLog> | null };

function parseFiltersFromUrl(params: URLSearchParams): {
  filters: ArchivedLogsFiltersState;
  sortBy: ArchivedLogsSortKey | null;
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

  const sortByRaw = params.get('sort_by');
  const sortBy = (VALID_SORT_COLUMNS as readonly string[]).includes(sortByRaw ?? '')
    ? (sortByRaw as ArchivedLogsSortKey)
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
      severity,
      applicationId: applicationId != null && !Number.isNaN(applicationId) ? applicationId : null,
      dateFrom: params.get('date_from'),
      dateTo: params.get('date_to'),
    },
    sortBy,
    sortDir,
    page,
  };
}

function writeFiltersToUrl(
  filters: ArchivedLogsFiltersState,
  sortBy: ArchivedLogsSortKey | null,
  sortDir: SortDir | null,
  page: number,
): URLSearchParams {
  const qs = new URLSearchParams();
  if (filters.severity.length > 0) qs.set('severity', filters.severity.join(','));
  if (filters.applicationId != null) qs.set('application_id', String(filters.applicationId));
  if (filters.dateFrom) qs.set('date_from', filters.dateFrom);
  if (filters.dateTo) qs.set('date_to', filters.dateTo);
  if (sortBy) qs.set('sort_by', sortBy);
  if (sortDir) qs.set('sort_dir', sortDir);
  if (page > 1) qs.set('page', String(page));
  return qs;
}

function toApiFilters(
  filters: ArchivedLogsFiltersState,
  sortBy: ArchivedLogsSortKey | null,
  sortDir: SortDir | null,
  page: number,
): ApiArchivedLogsFilters {
  return {
    severity: filters.severity.length > 0 ? filters.severity : null,
    application_id: filters.applicationId ?? null,
    date_from: filters.dateFrom,
    date_to: filters.dateTo,
    sort_by: sortBy ? (sortBy as ArchivedLogsSortBy) : null,
    sort_dir: sortDir,
    page,
  };
}

export function ArchivedLogsPage() {
  const { t } = useTranslation('archivedLogs');
  const [searchParams, setSearchParams] = useSearchParams();
  const [applications, setApplications] = useState<ApplicationRef[]>([]);
  const [state, setState] = useState<ListState>({ status: 'loading', data: null });

  const { filters, sortBy, sortDir, page } = useMemo(
    () => parseFiltersFromUrl(searchParams),
    [searchParams],
  );

  useEffect(() => {
    let cancelled = false;
    fetchApplications('with_archived_logs')
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
    fetchArchivedLogs(toApiFilters(filters, sortBy, sortDir, page))
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
  }, [filters, sortBy, sortDir, page]);

  const updateFilters = useCallback(
    (patch: Partial<ArchivedLogsFiltersState>) => {
      const next = { ...filters, ...patch };
      setSearchParams(writeFiltersToUrl(next, sortBy, sortDir, 1));
    },
    [filters, sortBy, sortDir, setSearchParams],
  );

  const resetFilters = useCallback(() => {
    setSearchParams(new URLSearchParams());
  }, [setSearchParams]);

  const changePage = useCallback(
    (nextPage: number) => {
      setSearchParams(writeFiltersToUrl(filters, sortBy, sortDir, nextPage));
    },
    [filters, sortBy, sortDir, setSearchParams],
  );

  const changeSort = useCallback(
    (column: ArchivedLogsSortKey) => {
      let nextDir: SortDir = 'desc';
      if (sortBy === column) {
        nextDir = sortDir === 'asc' ? 'desc' : 'asc';
      }
      setSearchParams(writeFiltersToUrl(filters, column, nextDir, 1));
    },
    [filters, sortBy, sortDir, setSearchParams],
  );

  const pagination = state.data;
  const logs = pagination?.data ?? [];

  return (
    <div className="px-4 py-6 sm:px-6 lg:px-8">
      <header className="flex items-center justify-between gap-3">
        <h1 className="text-xl font-semibold text-text-primary dark:text-text-dark-primary">
          {t('title')}
        </h1>
      </header>

      <ArchivedLogsFilters
        value={filters}
        applications={applications}
        onChange={updateFilters}
        onReset={resetFilters}
      />

      {state.status === 'error' && (
        <div className="mt-4 rounded-lg border border-danger-light bg-danger-light/30 p-3 text-sm text-danger-dark dark:border-danger/40 dark:bg-danger/10 dark:text-danger">
          {t('listLoadError', { message: state.error })}
        </div>
      )}

      {state.status === 'loading' && !pagination && (
        <div className="mt-4 rounded-lg border border-ui-border bg-ui-card p-6 text-center text-sm text-text-muted dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-muted">
          {t('detail.loading')}
        </div>
      )}

      {pagination && (
        <>
          <ArchivedLogsTable
            logs={logs}
            sortBy={sortBy}
            sortDir={sortDir}
            onSort={changeSort}
          />
          <Pagination meta={pagination.meta} onChangePage={changePage} />
        </>
      )}
    </div>
  );
}
