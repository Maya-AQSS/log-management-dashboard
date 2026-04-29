import { useCallback, useEffect, useState } from'react';
import { Alert, Button, PageTitle } from'@maya/shared-ui-react';
import { useTranslation } from'react-i18next';
import { useNavigate, useParams } from'react-router-dom';
import { archiveLog, fetchLog, resolveLog, type LogDetailResponse } from'../api/logs';
import { LogDetailView } from'../components/logs';
import { ConfirmDialog } from'../components/ui';

type State =
 | { status:'loading'; data: LogDetailResponse | null }
 | { status:'ready'; data: LogDetailResponse }
 | { status:'error'; error: string; data: LogDetailResponse | null }
 | { status:'not-found' };

type Dialog ='none' |'archive' |'resolve';

export function LogDetailPage() {
 const { t } = useTranslation('logs');
 const { id } = useParams<{ id: string }>();
 const navigate = useNavigate();

 const logId = id ? Number(id) : NaN;
 const validId = Number.isFinite(logId) && logId > 0;

 const [state, setState] = useState<State>({ status:'loading', data: null });
 const [dialog, setDialog] = useState<Dialog>('none');
 const [busy, setBusy] = useState(false);
 const [actionError, setActionError] = useState<string | null>(null);

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
 fetchLog(logId)
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

 if (state.status ==='not-found') {
 return (<div className="px-4 py-6 sm:px-6 lg:px-8">
 <PageTitle title={t('detail.title')} onBack={() => navigate(-1)} backLabel={t('detail.back')} />
 <div className="mt-4 rounded-lg border border-dashed border-outline bg-surface-container-low p-6 text-center text-sm text-on-surface-muted">
 {t('detail.notFound')}
 </div>
 </div>
 );
 }

 const log = state.status ==='ready' || state.status ==='error' ? state.data?.data : null;
 const archivedLogId =
 state.status ==='ready' || state.status ==='error'
 ? (state.data?.meta.archived_log_id ?? null)
 : null;

 return (<div className="px-4 py-6 sm:px-6 lg:px-8">
 <PageTitle
 title={log ? t('detail.titleWithId', { id: log.id }) : t('detail.title')}
 onBack={() => navigate(-1)}
 backLabel={t('detail.back')}
 actions={
 <>
 {log && archivedLogId === null && (<Button variant="primary" size="sm" onClick={() => setDialog('archive')}>
 {t('actions.archive')}
 </Button>
 )}
 {log && !log.resolved && (<Button variant="teal" size="sm" onClick={() => setDialog('resolve')}>
 {t('actions.resolve')}
 </Button>
 )}
 </>
 }
 />

 {actionError && (<Alert tone="danger" className="mt-4">{actionError}</Alert>
 )}

 {state.status ==='error' && (<Alert tone="danger" className="mt-4">{t('detail.loadError', { message: state.error })}
 </Alert>
 )}

 {state.status ==='loading' && !log && (<div className="mt-4 rounded-lg border border-outline bg-surface-container-low p-6 text-center text-sm text-on-surface-muted">
 {t('detail.loading')}
 </div>
 )}

 {log && <LogDetailView log={log} archivedLogId={archivedLogId} />}

 <ConfirmDialog
 open={dialog ==='archive'}
 title={t('confirmations.archive.title')}
 description={t('confirmations.archive.message')}
 confirmLabel={t('confirmations.archive.confirmLabel')}
 loading={busy}
 onConfirm={onArchive}
 onCancel={() => !busy && setDialog('none')}
 />
 <ConfirmDialog
 open={dialog ==='resolve'}
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
