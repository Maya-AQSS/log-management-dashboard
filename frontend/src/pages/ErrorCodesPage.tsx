import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { fetchApplications } from '../api/applications';
import { fetchErrorCodes, type ErrorCodesFilters as ApiErrorCodesFilters } from '../api/errorCodes';
import {
  ErrorCodesFilters,
  ErrorCodesTable,
  type ErrorCodesFiltersState,
} from '../components/error-codes';
import { Pagination } from '../components/table';
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
  const [searchParams, setSearchParams] = useSearchParams();
  const [applications, setApplications] = useState<ApplicationRef[]>([]);
  const [state, setState] = useState<ListState>({ status: 'loading', data: null });

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

  const pagination = state.data;
  const errorCodes = pagination?.data ?? [];

  return (
    <div className="px-4 py-6 sm:px-6 lg:px-8">
      <header className="flex items-center justify-between gap-3">
        <h1 className="text-xl font-semibold text-text-primary dark:text-text-dark-primary">
          Códigos de error
        </h1>
        <Link
          to="/error-codes/create"
          className="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border shadow-sm"
        >
          Nuevo código
        </Link>
      </header>

      <ErrorCodesFilters
        value={filters}
        applications={applications}
        onChange={updateFilters}
        onReset={resetFilters}
      />

      {state.status === 'error' && (
        <div className="mt-4 rounded-lg border border-danger-light bg-danger-light/30 p-3 text-sm text-danger-dark dark:border-danger/40 dark:bg-danger/10 dark:text-danger">
          No se pudieron cargar los códigos de error: {state.error}
        </div>
      )}

      {state.status === 'loading' && !pagination && (
        <div className="mt-4 rounded-lg border border-ui-border bg-ui-card p-6 text-center text-sm text-text-muted dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-muted">
          Cargando…
        </div>
      )}

      {pagination && (
        <>
          <ErrorCodesTable errorCodes={errorCodes} />
          <Pagination meta={pagination.meta} onChangePage={changePage} />
        </>
      )}
    </div>
  );
}
