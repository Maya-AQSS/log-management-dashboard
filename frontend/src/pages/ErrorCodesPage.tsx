import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  Alert,
  ColumnVisibilityMenu,
  DataTable,
  PageTitle,
  Pagination,
  type ColumnDef,
} from '@maya/shared-ui-react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { fetchApplications } from '../api/applications';
import { fetchErrorCodes, type ErrorCodesFilters as ApiErrorCodesFilters } from '../api/errorCodes';
import { ErrorCodesFilters, type ErrorCodesFiltersState } from '../components/error-codes';
import type { PaginatedResponse } from '../types/api';
import type { ApplicationRef, ErrorCode } from '../types/logs';

type ListState =
  | { status: 'loading'; data: PaginatedResponse<ErrorCode> | null }
  | { status: 'ready'; data: PaginatedResponse<ErrorCode> }
  | { status: 'error'; error: string; data: PaginatedResponse<ErrorCode> | null };

function parseFiltersFromUrl(params: URLSearchParams): {
  filters: ErrorCodesFiltersState;
  page: number;
} {
  const search = params.get('search') ?? '';

  const applicationIdRaw = params.get('application_id');
  const applicationIdNum = applicationIdRaw ? Number(applicationIdRaw) : null;
  const applicationId =
    applicationIdNum != null && Number.isFinite(applicationIdNum) ? applicationIdNum : null;

  const pageRaw = params.get('page');
  const pageNum = pageRaw ? Number(pageRaw) : 1;
  const page = Number.isFinite(pageNum) && pageNum > 0 ? Math.floor(pageNum) : 1;

  return {
    filters: { search, applicationId },
    page,
  };
}

function writeFiltersToUrl(filters: ErrorCodesFiltersState, page: number): URLSearchParams {
  const qs = new URLSearchParams();
  if (filters.search.trim() !== '') qs.set('search', filters.search);
  if (filters.applicationId != null) qs.set('application_id', String(filters.applicationId));
  if (page > 1) qs.set('page', String(page));
  return qs;
}

function toApiFilters(filters: ErrorCodesFiltersState, page: number): ApiErrorCodesFilters {
  return {
    search: filters.search.trim() === '' ? null : filters.search,
    application_id: filters.applicationId ?? null,
    page,
  };
}

export function ErrorCodesPage() {
  const { t } = useTranslation('errorCodes');
  const { t: tCommon } = useTranslation('common');
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [applications, setApplications] = useState<ApplicationRef[]>([]);
  const [state, setState] = useState<ListState>({ status: 'loading', data: null });
  const [hiddenIds, setHiddenIds] = useState<Set<string>>(new Set());

  const { filters, page } = useMemo(() => parseFiltersFromUrl(searchParams), [searchParams]);

  useEffect(() => {
    let cancelled = false;
    fetchApplications('all')
      .then((apps) => {
        if (!cancelled) setApplications(apps);
      })
      .catch(() => {
        /* dropdown vacío si falla */
      });
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    let cancelled = false;
    setState((prev) => ({ status: 'loading', data: prev.data }));
    fetchErrorCodes(toApiFilters(filters, page))
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
  }, [filters, page]);

  const updateFilters = useCallback(
    (patch: Partial<ErrorCodesFiltersState>) => {
      const next = { ...filters, ...patch };
      setSearchParams(writeFiltersToUrl(next, 1));
    },
    [filters, setSearchParams],
  );

  const resetFilters = useCallback(() => {
    setSearchParams(new URLSearchParams());
  }, [setSearchParams]);

  const changePage = useCallback(
    (nextPage: number) => {
      setSearchParams(writeFiltersToUrl(filters, nextPage));
    },
    [filters, setSearchParams],
  );

  const toggleHidden = useCallback((id: string) => {
    setHiddenIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }, []);

  const columns: ColumnDef<ErrorCode>[] = useMemo(
    () => [
      {
        id: 'code',
        header: t('columns.code'),
        cell: (ec) => <span className="font-mono whitespace-nowrap">{ec.code}</span>,
      },
      {
        id: 'application',
        header: t('columns.application'),
        cell: (ec) => ec.application?.name ?? '-',
      },
      {
        id: 'name',
        header: t('columns.name'),
        cell: (ec) => <span className="break-words">{ec.name}</span>,
      },
      {
        id: 'file',
        header: t('columns.file'),
        cell: (ec) => (
          <span className="font-mono text-xs break-all">{ec.file ?? '-'}</span>
        ),
      },
      {
        id: 'line',
        header: t('columns.line'),
        cell: (ec) => <span className="whitespace-nowrap">{ec.line ?? '-'}</span>,
      },
    ],
    [t],
  );

  const pagination = state.data;
  const errorCodes = pagination?.data ?? [];
  const meta = pagination?.meta;
  const startIndex = meta && meta.from != null ? meta.from : 0;
  const endIndex = meta && meta.to != null ? meta.to : 0;
  const total = meta?.total ?? 0;

  return (
    <div className="px-4 py-6 sm:px-6 lg:px-8">
      <PageTitle
        title="Códigos de error"
        actions={
          <Link
            to="/error-codes/create"
            className="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border shadow-sm"
          >
            Nuevo código
          </Link>
        }
      />

      <ErrorCodesFilters
        value={filters}
        applications={applications}
        onChange={updateFilters}
        onReset={resetFilters}
      />

      <div className="mt-3 flex items-center justify-end">
        <ColumnVisibilityMenu
          columns={columns}
          hiddenColumnIds={hiddenIds}
          onToggle={toggleHidden}
        />
      </div>

      {state.status === 'error' && (
        <Alert tone="danger" className="mt-4">No se pudieron cargar los códigos de error: {state.error}
        </Alert>
      )}

      {state.status === 'loading' && !pagination && (
        <div className="mt-4 rounded-lg border border-ui-border bg-ui-card p-6 text-center text-sm text-text-muted dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-muted">
          Cargando…
        </div>
      )}

      {pagination && (
        <>
          <div className="mt-3">
            <DataTable
              columns={columns}
              rows={errorCodes}
              rowKey={(ec) => ec.id}
              hiddenColumnIds={hiddenIds}
              onRowClick={(ec) => navigate(`/error-codes/${ec.id}`)}
              emptyMessage={t('emptyFiltered')}
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
        </>
      )}
    </div>
  );
}
