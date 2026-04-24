import { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
  deleteArchivedLog,
  fetchArchivedLog,
  updateArchivedLog,
} from '../api/archivedLogs';
import { ArchivedLogDetailView } from '../components/archived-logs';
import { CommentThread } from '../components/comments';
import { ConfirmDialog } from '../components/ui';
import type { ArchivedLog } from '../types/logs';

type State =
  | { status: 'loading'; data: ArchivedLog | null }
  | { status: 'ready'; data: ArchivedLog }
  | { status: 'error'; error: string; data: ArchivedLog | null }
  | { status: 'not-found' };

type EditForm = {
  description: string;
  url_tutorial: string;
};

function toEditForm(log: ArchivedLog): EditForm {
  return {
    description: log.description ?? '',
    url_tutorial: log.url_tutorial ?? '',
  };
}

export function ArchivedLogDetailPage() {
  const { t } = useTranslation('archivedLogs');
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const logId = id ? Number(id) : NaN;
  const validId = Number.isFinite(logId) && logId > 0;

  const [state, setState] = useState<State>({ status: 'loading', data: null });
  const [editing, setEditing] = useState(false);
  const [form, setForm] = useState<EditForm>({ description: '', url_tutorial: '' });
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [confirmDelete, setConfirmDelete] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [deleteError, setDeleteError] = useState<string | null>(null);

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
    fetchArchivedLog(logId)
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

  const log = state.status === 'ready' || state.status === 'error' ? state.data : null;

  const onStartEdit = useCallback(() => {
    if (!log) return;
    setForm(toEditForm(log));
    setSaveError(null);
    setEditing(true);
  }, [log]);

  const onCancelEdit = useCallback(() => {
    setEditing(false);
    setSaveError(null);
  }, []);

  const onSave = useCallback(async () => {
    if (!validId) return;
    setSaving(true);
    setSaveError(null);
    try {
      const updated = await updateArchivedLog(logId, {
        description: form.description.trim() === '' ? null : form.description,
        url_tutorial: form.url_tutorial.trim() === '' ? null : form.url_tutorial,
      });
      setState({ status: 'ready', data: updated });
      setEditing(false);
    } catch (e) {
      setSaveError(e instanceof Error ? e.message : String(e));
    } finally {
      setSaving(false);
    }
  }, [logId, validId, form]);

  const onDelete = useCallback(async () => {
    if (!validId) return;
    setDeleting(true);
    setDeleteError(null);
    try {
      await deleteArchivedLog(logId);
      navigate('/archived-logs');
    } catch (e) {
      setDeleteError(e instanceof Error ? e.message : String(e));
      setDeleting(false);
      setConfirmDelete(false);
    }
  }, [logId, validId, navigate]);

  if (state.status === 'not-found') {
    return (
      <div className="px-4 py-6 sm:px-6 lg:px-8">
        <Link
          to="/archived-logs"
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

  return (
    <div className="px-4 py-6 sm:px-6 lg:px-8">
      <div className="flex min-h-[2.5rem] items-start justify-between gap-3">
        <Link
          to="/archived-logs"
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
          {log && !editing && (
            <>
              <button
                type="button"
                onClick={onStartEdit}
                className="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border shadow-sm"
              >
                {t('detail.edit')}
              </button>
              <button
                type="button"
                onClick={() => setConfirmDelete(true)}
                className="inline-flex items-center bg-danger text-text-inverse border-danger hover:bg-danger-dark hover:border-danger-dark px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border shadow-sm"
              >
                {t('detail.delete')}
              </button>
            </>
          )}
        </div>
      </div>

      {deleteError && (
        <div className="mt-4 rounded-lg border border-danger-light bg-danger-light/30 p-3 text-sm text-danger-dark dark:border-danger/40 dark:bg-danger/10 dark:text-danger">
          {deleteError}
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

      {log && (
        <div className="mt-4 space-y-4">
          <ArchivedLogDetailView log={log} />

          <div className="rounded-lg border border-ui-border bg-ui-card p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
            <h2 className="text-base font-semibold text-text-primary dark:text-text-dark-primary">
              {t('detail.editableInfo')}
            </h2>

            {editing ? (
              <div className="mt-3 space-y-4">
                <div>
                  <label
                    htmlFor="archived-log-description"
                    className="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary"
                  >
                    {t('detail.fields.description')}
                  </label>
                  <textarea
                    id="archived-log-description"
                    value={form.description}
                    onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))}
                    rows={4}
                    disabled={saving}
                    className="mt-1 w-full rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner focus:border-odoo-purple focus:outline-none dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary dark:focus:border-odoo-dark-purple"
                  />
                </div>

                <div>
                  <label
                    htmlFor="archived-log-url-tutorial"
                    className="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary"
                  >
                    {t('detail.fields.urlTutorial')}
                  </label>
                  <input
                    id="archived-log-url-tutorial"
                    type="url"
                    value={form.url_tutorial}
                    onChange={(e) => setForm((f) => ({ ...f, url_tutorial: e.target.value }))}
                    disabled={saving}
                    placeholder="https://…"
                    className="mt-1 w-full rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner focus:border-odoo-purple focus:outline-none dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary dark:focus:border-odoo-dark-purple"
                  />
                </div>

                {saveError && (
                  <div className="rounded-lg border border-danger-light bg-danger-light/30 p-3 text-sm text-danger-dark dark:border-danger/40 dark:bg-danger/10 dark:text-danger">
                    {saveError}
                  </div>
                )}

                <div className="flex justify-end gap-2">
                  <button
                    type="button"
                    onClick={onCancelEdit}
                    disabled={saving}
                    className="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    {t('detail.cancel')}
                  </button>
                  <button
                    type="button"
                    onClick={onSave}
                    disabled={saving}
                    className="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border shadow-sm disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    {saving ? '…' : t('detail.save')}
                  </button>
                </div>
              </div>
            ) : (
              <dl className="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                <div>
                  <dt className="text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
                    {t('detail.fields.description')}
                  </dt>
                  <dd className="mt-1 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary whitespace-pre-wrap break-words shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">
                    {log.description && log.description.trim() !== '' ? (
                      log.description
                    ) : (
                      <span className="italic text-text-muted dark:text-text-dark-muted">
                        {t('detail.fields.noDescription')}
                      </span>
                    )}
                  </dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
                    {t('detail.fields.urlTutorial')}
                  </dt>
                  <dd className="mt-1 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg">
                    {log.url_tutorial && log.url_tutorial.trim() !== '' ? (
                      <a
                        href={log.url_tutorial}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-odoo-purple hover:underline dark:text-odoo-dark-purple break-all"
                      >
                        {log.url_tutorial}
                      </a>
                    ) : (
                      <span className="italic text-text-muted dark:text-text-dark-muted">
                        {t('detail.fields.noUrl')}
                      </span>
                    )}
                  </dd>
                </div>
              </dl>
            )}
          </div>

          <div className="rounded-lg border border-ui-border bg-ui-card p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
            <h2 className="text-base font-semibold text-text-primary dark:text-text-dark-primary">
              {t('detail.comments')}
            </h2>
            <div className="mt-3">
              <CommentThread commentableType="archived-logs" commentableId={log.id} />
            </div>
          </div>
        </div>
      )}

      <ConfirmDialog
        open={confirmDelete}
        title={t('confirmations.delete.title')}
        message={t('confirmations.delete.message')}
        confirmLabel={t('confirmations.delete.confirmLabel')}
        confirmTone="danger"
        busy={deleting}
        onConfirm={onDelete}
        onCancel={() => !deleting && setConfirmDelete(false)}
      />
    </div>
  );
}
