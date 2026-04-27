import { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { archiveLog, fetchLog, resolveLog, type LogDetailResponse } from '../api/logs';
import { LogDetailView } from '../components/logs';
import { ConfirmDialog } from '../components/ui';

type State =
  | { status: 'loading'; data: LogDetailResponse | null }
  | { status: 'ready'; data: LogDetailResponse }
  | { status: 'error'; error: string; data: LogDetailResponse | null }
  | { status: 'not-found' };

type Dialog = 'none' | 'archive' | 'resolve';

export function LogDetailPage() {
  const { t } = useTranslation('logs');
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const logId = id ? Number(id) : NaN;
  const validId = Number.isFinite(logId) && logId > 0;

  const [state, setState] = useState<State>({ status: 'loading', data: null });
  const [dialog, setDialog] = useState<Dialog>('none');
  const [busy, setBusy] = useState(false);
  const [actionError, setActionError] = useState<string | null>(null);

  const load = useCallback(() => {
    if (!validId) {
      setState({ status: 'not-found' });
      return () => {};
    }
    let cancelled = false;
    setState((prev) => ({
      status: 'loading',
      data: prev.status === 'ready' || prev.status === 'error' ? prev.data : null,
    }));
    fetchLog(logId)
      .then((data) => {
        if (!cancelled) setState({ status: 'ready', data });
      })
      .catch((e) => {
        if (cancelled) return;
        const message = e instanceof Error ? e.message : String(e);
        if (/404/.test(message)) {
          setState({ status: 'not-found' });
        } else {
          setState((prev) => ({
            status: 'error',
            error: message,
            data: prev.status === 'ready' || prev.status === 'error' ? prev.data : null,
          }));
        }
      });
    return () => {
      cancelled = true;
    };
  }, [logId, validId]);

  useEffect(() => load(), [load]);

  const onArchive = useCallback(async () => {
    setBusy(true);
    setActionError(null);
    try {
      const res = await archiveLog(logId);
      navigate(`/archived-logs/${res.data.archived_log_id}`);
    } catch (e) {
      setActionError(e instanceof Error ? e.message : String(e));
      setBusy(false);
      setDialog('none');
    }
  }, [logId, navigate]);

  const onResolve = useCallback(async () => {
    setBusy(true);
    setActionError(null);
    try {
      await resolveLog(logId);
      setDialog('none');
      setBusy(false);
      load();
    } catch (e) {
      setActionError(e instanceof Error ? e.message : String(e));
      setBusy(false);
      setDialog('none');
    }
  }, [logId, load]);

  if (state.status === 'not-found') {
    return (
      <div className="px-4 py-6 sm:px-6 lg:px-8">
        <Link
          to="/logs"
          className="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer"
        >
          {t('detail.back')}
        </Link>
        <div className="mt-4 rounded-lg border border-dashed border-ui-border bg-ui-card p-6 text-center text-sm text-text-muted dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-muted">
          {t('detail.notFound')}
        </div>
      </div>
    );
  }

  const log = state.status === 'ready' || state.status === 'error' ? state.data?.data : null;
  const archivedLogId =
    state.status === 'ready' || state.status === 'error'
      ? (state.data?.meta.archived_log_id ?? null)
      : null;

  return (
    <div className="px-4 py-6 sm:px-6 lg:px-8">
      <div className="flex min-h-[2.5rem] items-start justify-between gap-3">
        <Link
          to="/logs"
          className="bg-transparent text-text-secondary dark:text-text-dark-secondary border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
        >
          {t('detail.back')}
        </Link>

        <div className="flex flex-1 flex-col items-center justify-center text-center">
          <h1 className="text-xl font-semibold leading-tight text-text-primary md:text-2xl dark:text-text-dark-primary">
            {log ? t('detail.titleWithId', { id: log.id }) : t('detail.title')}
          </h1>
        </div>

        <div className="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-3">
          {log && archivedLogId === null && (
            <button
              type="button"
              onClick={() => setDialog('archive')}
              className="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border shadow-sm"
            >
              {t('actions.archive')}
            </button>
          )}
          {log && !log.resolved && (
            <button
              type="button"
              onClick={() => setDialog('resolve')}
              className="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border shadow-sm"
            >
              {t('actions.resolve')}
            </button>
          )}
        </div>
      </div>

      {actionError && (
        <div className="mt-4 rounded-lg border border-danger-light bg-danger-light/30 p-3 text-sm text-danger-dark dark:border-danger/40 dark:bg-danger/10 dark:text-danger">
          {actionError}
        </div>
      )}

      {state.status === 'error' && (
        <div className="mt-4 rounded-lg border border-danger-light bg-danger-light/30 p-3 text-sm text-danger-dark dark:border-danger/40 dark:bg-danger/10 dark:text-danger">
          {t('detail.loadError', { message: state.error })}
        </div>
      )}

      {state.status === 'loading' && !log && (
        <div className="mt-4 rounded-lg border border-ui-border bg-ui-card p-6 text-center text-sm text-text-muted dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-muted">
          {t('detail.loading')}
        </div>
      )}

      {log && <LogDetailView log={log} archivedLogId={archivedLogId} />}

      <ConfirmDialog
        open={dialog === 'archive'}
        title={t('confirmations.archive.title')}
        description={t('confirmations.archive.message')}
        confirmLabel={t('confirmations.archive.confirmLabel')}
        loading={busy}
        onConfirm={onArchive}
        onCancel={() => !busy && setDialog('none')}
      />
      <ConfirmDialog
        open={dialog === 'resolve'}
        title={t('confirmations.resolve.title')}
        description={t('confirmations.resolve.message')}
        confirmLabel={t('confirmations.resolve.confirmLabel')}
        loading={busy}
        onConfirm={onResolve}
        onCancel={() => !busy && setDialog('none')}
      />
    </div>
  );
}
