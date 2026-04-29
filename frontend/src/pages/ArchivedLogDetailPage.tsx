import { useCallback, useEffect, useState } from'react';
import { Alert, Button, PageTitle } from'@maya/shared-ui-react';
import { useTranslation } from'react-i18next';
import { useNavigate, useParams } from'react-router-dom';
import {
 deleteArchivedLog,
 fetchArchivedLog,
 updateArchivedLog,
} from'../api/archivedLogs';
import { ArchivedLogDetailView } from'../components/archived-logs';
import { CommentThread } from'../components/comments';
import { ConfirmDialog } from'../components/ui';
import type { ArchivedLog } from'../types/logs';

type State =
 | { status:'loading'; data: ArchivedLog | null }
 | { status:'ready'; data: ArchivedLog }
 | { status:'error'; error: string; data: ArchivedLog | null }
 | { status:'not-found' };

type EditForm = {
 description: string;
 url_tutorial: string;
};

function toEditForm(log: ArchivedLog): EditForm {
 return {
 description: log.description ??'',
 url_tutorial: log.url_tutorial ??'',
 };
}

export function ArchivedLogDetailPage() {
 const { t } = useTranslation('archivedLogs');
 const { id } = useParams<{ id: string }>();
 const navigate = useNavigate();

 const logId = id ? Number(id) : NaN;
 const validId = Number.isFinite(logId) && logId > 0;

 const [state, setState] = useState<State>({ status:'loading', data: null });
 const [editing, setEditing] = useState(false);
 const [form, setForm] = useState<EditForm>({ description:'', url_tutorial:'' });
 const [saving, setSaving] = useState(false);
 const [saveError, setSaveError] = useState<string | null>(null);
 const [confirmDelete, setConfirmDelete] = useState(false);
 const [deleting, setDeleting] = useState(false);
 const [deleteError, setDeleteError] = useState<string | null>(null);

 const load = useCallback(() => {
 if (!validId) {
 setState({ status:'not-found' });
 return () => {};
 }
 let cancelled = false;
 setState((prev) => ({
 status:'loading',
 data: prev.status ==='ready' || prev.status ==='error' ? prev.data : null,
 }));
 fetchArchivedLog(logId)
 .then((data) => {
 if (!cancelled) setState({ status:'ready', data });
 })
 .catch((e) => {
 if (cancelled) return;
 const message = e instanceof Error ? e.message : String(e);
 if (/404/.test(message)) {
 setState({ status:'not-found' });
 } else {
 setState((prev) => ({
 status:'error',
 error: message,
 data: prev.status ==='ready' || prev.status ==='error' ? prev.data : null,
 }));
 }
 });
 return () => {
 cancelled = true;
 };
 }, [logId, validId]);

 useEffect(() => load(), [load]);

 const log = state.status ==='ready' || state.status ==='error' ? state.data : null;

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
 description: form.description.trim() ==='' ? null : form.description,
 url_tutorial: form.url_tutorial.trim() ==='' ? null : form.url_tutorial,
 });
 setState({ status:'ready', data: updated });
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

 if (state.status ==='not-found') {
 return (<div className="px-4 py-6 sm:px-6 lg:px-8">
 <PageTitle title={t('detail.title')} onBack={() => navigate(-1)} backLabel={t('detail.back')} />
 <div className="mt-4 rounded-lg border border-dashed border-outline bg-surface-container-low p-6 text-center text-sm text-on-surface-muted">
 {t('detail.notFound')}
 </div>
 </div>
 );
 }

 return (<div className="px-4 py-6 sm:px-6 lg:px-8">
 <PageTitle
 title={log ? t('detail.titleWithId', { id: log.id }) : t('detail.title')}
 onBack={() => navigate(-1)}
 backLabel={t('detail.back')}
 actions={
 log && !editing ? (<>
 <Button variant="outline" size="sm" onClick={onStartEdit}>
 {t('detail.edit')}
 </Button>
 <Button variant="danger" size="sm" onClick={() => setConfirmDelete(true)}>
 {t('detail.delete')}
 </Button>
 </>
 ) : undefined
 }
 />

 {deleteError && (<Alert tone="danger" className="mt-4">{deleteError}</Alert>
 )}

 {state.status ==='error' && (<Alert tone="danger" className="mt-4">{t('detail.loadError', { message: state.error })}
 </Alert>
 )}

 {state.status ==='loading' && !log && (<div className="mt-4 rounded-lg border border-outline bg-surface-container-low p-6 text-center text-sm text-on-surface-muted">
 {t('detail.loading')}
 </div>
 )}

 {log && (<div className="mt-4 space-y-4">
 <ArchivedLogDetailView log={log} />

 <div className="rounded-lg border border-outline bg-surface-container-low p-4">
 <h2 className="text-base font-semibold text-on-surface">
 {t('detail.editableInfo')}
 </h2>

 {editing ? (<div className="mt-3 space-y-4">
 <div>
 <label
 htmlFor="archived-log-description"
 className="block text-sm font-medium text-on-surface-variant"
 >
 {t('detail.fields.description')}
 </label>
 <textarea
 id="archived-log-description"
 value={form.description}
 onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))}
 rows={4}
 disabled={saving}
 className="mt-1 w-full rounded-lg border border-outline bg-surface px-3 py-2.5 text-sm text-on-surface shadow-inner focus:border-primary focus:outline-none"
 />
 </div>

 <div>
 <label
 htmlFor="archived-log-url-tutorial"
 className="block text-sm font-medium text-on-surface-variant"
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
 className="mt-1 w-full rounded-lg border border-outline bg-surface px-3 py-2.5 text-sm text-on-surface shadow-inner focus:border-primary focus:outline-none"
 />
 </div>

 {saveError && (<Alert tone="danger" className="mt-4">{saveError}</Alert>
 )}

 <div className="flex justify-end gap-2">
 <Button variant="secondary" size="sm" onClick={onCancelEdit} disabled={saving}>
 {t('detail.cancel')}
 </Button>
 <Button variant="primary" size="sm" onClick={onSave} disabled={saving} loading={saving}>
 {saving ?'…' : t('detail.save')}
 </Button>
 </div>
 </div>
 ) : (<dl className="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
 <div>
 <dt className="text-sm font-medium text-on-surface-variant">
 {t('detail.fields.description')}
 </dt>
 <dd className="mt-1 rounded-lg border border-outline bg-surface px-3 py-2.5 text-sm text-on-surface whitespace-pre-wrap break-words shadow-inner">
 {log.description && log.description.trim() !=='' ? (log.description
 ) : (<span className="italic text-on-surface-muted">
 {t('detail.fields.noDescription')}
 </span>
 )}
 </dd>
 </div>
 <div>
 <dt className="text-sm font-medium text-on-surface-variant">
 {t('detail.fields.urlTutorial')}
 </dt>
 <dd className="mt-1 rounded-lg border border-outline bg-surface px-3 py-2.5 text-sm shadow-inner">
 {log.url_tutorial && log.url_tutorial.trim() !=='' ? (<a
 href={log.url_tutorial}
 target="_blank"
 rel="noopener noreferrer"
 className="text-primary hover:underline break-all"
 >
 {log.url_tutorial}
 </a>
 ) : (<span className="italic text-on-surface-muted">
 {t('detail.fields.noUrl')}
 </span>
 )}
 </dd>
 </div>
 </dl>
 )}
 </div>

 <div className="rounded-lg border border-outline bg-surface-container-low p-4">
 <h2 className="text-base font-semibold text-on-surface">
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
 description={t('confirmations.delete.message')}
 confirmLabel={t('confirmations.delete.confirmLabel')}
 variant="danger"
 loading={deleting}
 onConfirm={onDelete}
 onCancel={() => !deleting && setConfirmDelete(false)}
 />
 </div>
 );
}
