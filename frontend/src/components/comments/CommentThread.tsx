import { useCallback, useEffect, useState } from'react';
import { Alert, Button } from'@maya/shared-ui-react';
import { useTranslation } from'react-i18next';
import {
 createComment,
 deleteComment,
 fetchComments,
 updateComment,
 type CommentableKind,
} from'../../api/comments';
import type { Comment } from'../../types/logs';
import { ConfirmDialog } from'../ui';

type CommentThreadProps = {
 commentableType: CommentableKind;
 commentableId: number;
};

type LoadState =
 | { status:'loading' }
 | { status:'ready'; comments: Comment[] }
 | { status:'error'; error: string; comments: Comment[] };

function formatTimestamp(value: string | null): string {
 if (!value) return'';
 const d = new Date(value);
 if (Number.isNaN(d.getTime())) return'';
 const pad = (n: number) => String(n).padStart(2,'0');
 return`${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function plainTextToHtml(text: string): string {
 const escaped = text
 .replace(/&/g,'&amp;')
 .replace(/</g,'&lt;')
 .replace(/>/g,'&gt;');
 const paragraphs = escaped
 .split(/\n{2,}/)
 .map((p) =>`<p>${p.replace(/\n/g,'<br>')}</p>`)
 .join('');
 return paragraphs;
}

export function CommentThread({ commentableType, commentableId }: CommentThreadProps) {
 const { t } = useTranslation('comments');
 const [state, setState] = useState<LoadState>({ status:'loading' });
 const [newContent, setNewContent] = useState('');
 const [creating, setCreating] = useState(false);
 const [createError, setCreateError] = useState<string | null>(null);

 const [editingId, setEditingId] = useState<number | null>(null);
 const [editingContent, setEditingContent] = useState('');
 const [editingBusy, setEditingBusy] = useState(false);
 const [editingError, setEditingError] = useState<string | null>(null);

 const [deleteTargetId, setDeleteTargetId] = useState<number | null>(null);
 const [deleteBusy, setDeleteBusy] = useState(false);
 const [deleteError, setDeleteError] = useState<string | null>(null);

 const load = useCallback(() => {
 let cancelled = false;
 setState((prev) =>
 prev.status ==='ready' || prev.status ==='error'
 ? { status:'error', error:'', comments: prev.comments }
 : { status:'loading' },
 );
 fetchComments(commentableType, commentableId)
 .then((comments) => {
 if (!cancelled) setState({ status:'ready', comments });
 })
 .catch((e) => {
 if (cancelled) return;
 const message = e instanceof Error ? e.message : String(e);
 setState((prev) => ({
 status:'error',
 error: message,
 comments: prev.status ==='ready' || prev.status ==='error' ? prev.comments : [],
 }));
 });
 return () => {
 cancelled = true;
 };
 }, [commentableType, commentableId]);

 useEffect(() => {
 const cleanup = load();
 return cleanup;
 }, [load]);

 const comments = state.status ==='ready' || state.status ==='error' ? state.comments : [];

 const onCreate = useCallback(async () => {
 const content = newContent.trim();
 if (content.length < 3) {
 setCreateError(t('minLength'));
 return;
 }
 setCreating(true);
 setCreateError(null);
 try {
 const created = await createComment(commentableType, commentableId, {
 content: plainTextToHtml(content),
 });
 setState((prev) => {
 const prior = prev.status ==='ready' || prev.status ==='error' ? prev.comments : [];
 return { status:'ready', comments: [created, ...prior] };
 });
 setNewContent('');
 } catch (e) {
 setCreateError(e instanceof Error ? e.message : String(e));
 } finally {
 setCreating(false);
 }
 }, [commentableType, commentableId, newContent, t]);

 const onStartEdit = useCallback((comment: Comment) => {
 setEditingId(comment.id);
 const stripped = comment.content
 .replace(/<br\s*\/?\s*>/gi,'\n')
 .replace(/<\/p>\s*<p[^>]*>/gi,'\n\n')
 .replace(/<\/?p[^>]*>/gi,'')
 .replace(/<[^>]+>/g,'')
 .replace(/&lt;/g,'<')
 .replace(/&gt;/g,'>')
 .replace(/&amp;/g,'&')
 .trim();
 setEditingContent(stripped);
 setEditingError(null);
 }, []);

 const onCancelEdit = useCallback(() => {
 setEditingId(null);
 setEditingContent('');
 setEditingError(null);
 }, []);

 const onUpdate = useCallback(async () => {
 if (editingId == null) return;
 const content = editingContent.trim();
 if (content.length < 3) {
 setEditingError(t('minLength'));
 return;
 }
 setEditingBusy(true);
 setEditingError(null);
 try {
 const updated = await updateComment(editingId, { content: plainTextToHtml(content) });
 setState((prev) => {
 const prior = prev.status ==='ready' || prev.status ==='error' ? prev.comments : [];
 return {
 status:'ready',
 comments: prior.map((c) => (c.id === updated.id ? updated : c)),
 };
 });
 setEditingId(null);
 setEditingContent('');
 } catch (e) {
 setEditingError(e instanceof Error ? e.message : String(e));
 } finally {
 setEditingBusy(false);
 }
 }, [editingId, editingContent, t]);

 const onConfirmDelete = useCallback(async () => {
 if (deleteTargetId == null) return;
 setDeleteBusy(true);
 setDeleteError(null);
 try {
 await deleteComment(deleteTargetId);
 setState((prev) => {
 const prior = prev.status ==='ready' || prev.status ==='error' ? prev.comments : [];
 return {
 status:'ready',
 comments: prior.filter((c) => c.id !== deleteTargetId),
 };
 });
 setDeleteTargetId(null);
 } catch (e) {
 setDeleteError(e instanceof Error ? e.message : String(e));
 } finally {
 setDeleteBusy(false);
 }
 }, [deleteTargetId]);

 return (<div className="space-y-4">
 <div className="space-y-3 rounded-xl border border-outline bg-surface-container-low p-4 shadow-sm">
 <label
 htmlFor={`new-comment-${commentableType}-${commentableId}`}
 className="block text-sm font-medium text-on-surface-variant"
 >
 {t('newComment')}
 </label>
 <textarea
 id={`new-comment-${commentableType}-${commentableId}`}
 value={newContent}
 onChange={(e) => setNewContent(e.target.value)}
 disabled={creating}
 rows={4}
 placeholder={t('placeholder')}
 className="w-full rounded-lg border border-outline bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary disabled:cursor-not-allowed disabled:opacity-60"
 />
 {createError && (<p
 role="alert"
 className="rounded-lg border border-error-container bg-error-container/30 px-3 py-2 text-sm text-error"
 >
 {createError}
 </p>
 )}
 <div className="flex justify-end">
 <Button variant="primary" size="sm" onClick={onCreate} disabled={creating} loading={creating}>
 {creating ? t('busy') : t('save')}
 </Button>
 </div>
 </div>

 {state.status ==='error' && state.error && (<Alert tone="danger" className="mt-4">{t('listLoadError', { message: state.error })}
 </Alert>
 )}

 {deleteError && (<Alert tone="danger" className="mt-4">{deleteError}</Alert>
 )}

 <div className="space-y-3">
 {state.status ==='loading' && (<p className="rounded-xl border border-dashed border-outline bg-surface-container-low px-4 py-6 text-center text-sm text-on-surface-variant">
 {t('loading')}
 </p>
 )}

 {state.status !=='loading' && comments.length === 0 && (<p className="rounded-xl border border-dashed border-outline bg-surface-container-low px-4 py-6 text-center text-sm text-on-surface-variant">
 {t('empty')}
 </p>
 )}

 {comments.map((comment) => {
 const isEditing = editingId === comment.id;
 return (<article
 key={comment.id}
 className="rounded-xl border border-outline bg-surface-container-low p-4 shadow-sm"
 >
 <div className="flex items-start justify-between gap-4">
 <div>
 <p className="text-sm font-semibold text-on-surface">
 {comment.user?.name ?? t('unknownUser')}
 </p>
 <p className="text-xs text-on-surface-variant">
 {formatTimestamp(comment.created_at)}
 </p>
 </div>
 {!isEditing && (comment.can_edit || comment.can_delete) && (<div className="flex gap-2">
 {comment.can_edit && (<Button variant="ghost" size="xs" onClick={() => onStartEdit(comment)}>
 {t('edit')}
 </Button>
 )}
 {comment.can_delete && (<Button variant="danger" size="xs" onClick={() => setDeleteTargetId(comment.id)}>
 {t('delete')}
 </Button>
 )}
 </div>
 )}
 </div>

 {isEditing ? (<div className="mt-3 space-y-3">
 <textarea
 value={editingContent}
 onChange={(e) => setEditingContent(e.target.value)}
 disabled={editingBusy}
 rows={4}
 className="w-full rounded-lg border border-outline bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary disabled:cursor-not-allowed disabled:opacity-60"
 />
 {editingError && (<p
 role="alert"
 className="rounded-lg border border-error-container bg-error-container/30 px-3 py-2 text-sm text-error"
 >
 {editingError}
 </p>
 )}
 <div className="flex gap-2">
 <Button
 variant="primary"
 size="sm"
 onClick={onUpdate}
 disabled={editingBusy}
 loading={editingBusy}
 >
 {editingBusy ? t('busy') : t('update')}
 </Button>
 <Button variant="secondary" size="sm" onClick={onCancelEdit} disabled={editingBusy}>
 {t('cancel')}
 </Button>
 </div>
 </div>
 ) : (<div
 className="rte-content mt-3 text-sm text-on-surface"
 dangerouslySetInnerHTML={{ __html: comment.content }}
 />
 )}
 </article>
 );
 })}
 </div>

 <ConfirmDialog
 open={deleteTargetId !== null}
 title={t('confirmDelete.title')}
 message={t('confirmDelete.message')}
 confirmLabel={t('confirmDelete.confirmLabel')}
 confirmTone="danger"
 busy={deleteBusy}
 onConfirm={onConfirmDelete}
 onCancel={() => !deleteBusy && setDeleteTargetId(null)}
 />
 </div>
 );
}
