import { useCallback, useEffect, useState } from 'react';
import { Alert, Button, PageTitle } from '@maya/shared-ui-react';
import { useNavigate, useParams } from 'react-router-dom';
import { fetchApplications } from '../api/applications';
import {
  deleteErrorCode,
  fetchErrorCode,
  updateErrorCode,
  type ErrorCodePayload,
} from '../api/errorCodes';
import { CommentThread } from '../components/comments';
import { ErrorCodeForm, type ErrorCodeFormState } from '../components/error-codes';
import { ConfirmDialog } from '@maya/shared-ui-react';
import type { ApplicationRef, ErrorCode } from '../types/logs';

type State =
  | { status: 'loading'; data: ErrorCode | null }
  | { status: 'ready'; data: ErrorCode }
  | { status: 'error'; error: string; data: ErrorCode | null }
  | { status: 'not-found' };

function toFormState(ec: ErrorCode): ErrorCodeFormState {
  return {
    application_id: ec.application?.id ?? null,
    code: ec.code,
    name: ec.name,
    file: ec.file ?? '',
    line: ec.line != null ? String(ec.line) : '',
    description: ec.description ?? '',
  };
}

function toPayload(form: ErrorCodeFormState): Partial<ErrorCodePayload> {
  const parsedLine = form.line.trim() === '' ? null : Number(form.line);
  return {
    application_id: form.application_id ?? undefined,
    code: form.code,
    name: form.name,
    file: form.file.trim() === '' ? null : form.file,
    line: parsedLine != null && Number.isFinite(parsedLine) ? parsedLine : null,
    description: form.description.trim() === '' ? null : form.description,
  };
}

export function ErrorCodeDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const errorCodeId = id ? Number(id) : NaN;
  const validId = Number.isFinite(errorCodeId) && errorCodeId > 0;

  const [applications, setApplications] = useState<ApplicationRef[]>([]);
  const [state, setState] = useState<State>({ status: 'loading', data: null });
  const [editing, setEditing] = useState(false);
  const [form, setForm] = useState<ErrorCodeFormState>({
    application_id: null,
    code: '',
    name: '',
    file: '',
    line: '',
    description: '',
  });
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [confirmDelete, setConfirmDelete] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [deleteError, setDeleteError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;
    fetchApplications('all')
      .then((apps) => {
        if (!cancelled) setApplications(apps);
      })
      .catch(() => {
        /* ignorar, select mostrará vacío */
      });
    return () => {
      cancelled = true;
    };
  }, []);

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
    fetchErrorCode(errorCodeId)
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
  }, [errorCodeId, validId]);

  useEffect(() => load(), [load]);

  const ec = state.status === 'ready' || state.status === 'error' ? state.data : null;

  const onStartEdit = useCallback(() => {
    if (!ec) return;
    setForm(toFormState(ec));
    setSaveError(null);
    setEditing(true);
  }, [ec]);

  const onCancelEdit = useCallback(() => {
    setEditing(false);
    setSaveError(null);
  }, []);

  const onChangeForm = useCallback((patch: Partial<ErrorCodeFormState>) => {
    setForm((f) => ({ ...f, ...patch }));
  }, []);

  const onSave = useCallback(async () => {
    if (!validId) return;
    if (form.application_id == null) {
      setSaveError('Selecciona una aplicación.');
      return;
    }
    if (form.code.trim() === '' || form.name.trim() === '') {
      setSaveError('El código y el nombre son obligatorios.');
      return;
    }
    setSaving(true);
    setSaveError(null);
    try {
      const updated = await updateErrorCode(errorCodeId, toPayload(form));
      setState({ status: 'ready', data: updated });
      setEditing(false);
    } catch (e) {
      setSaveError(e instanceof Error ? e.message : String(e));
    } finally {
      setSaving(false);
    }
  }, [errorCodeId, validId, form]);

  const onDelete = useCallback(async () => {
    if (!validId) return;
    setDeleting(true);
    setDeleteError(null);
    try {
      await deleteErrorCode(errorCodeId);
      navigate('/error-codes');
    } catch (e) {
      setDeleteError(e instanceof Error ? e.message : String(e));
      setDeleting(false);
      setConfirmDelete(false);
    }
  }, [errorCodeId, validId, navigate]);

  if (state.status === 'not-found') {
    return (
      <div className="px-4 py-6 sm:px-6 lg:px-8">
        <PageTitle title="Código de error" onBack={() => navigate(-1)} backLabel="Volver" />
        <div className="mt-4 rounded-lg border border-dashed border-ui-border bg-ui-card p-6 text-center text-sm text-text-muted dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-muted">
          No se encontró el código de error solicitado.
        </div>
      </div>
    );
  }

  return (
    <div className="px-4 py-6 sm:px-6 lg:px-8">
      <PageTitle
        title={ec ? `Código de error: ${ec.code}` : 'Código de error'}
        onBack={() => navigate(-1)}
        backLabel="Volver"
        actions={
          ec && !editing ? (
            <>
              <Button variant="outline" size="sm" onClick={onStartEdit}>
                Editar
              </Button>
              <Button variant="danger" size="sm" onClick={() => setConfirmDelete(true)}>
                Eliminar
              </Button>
            </>
          ) : undefined
        }
      />

      {deleteError && (
        <Alert tone="danger" className="mt-4">{deleteError}</Alert>
      )}

      {state.status === 'error' && (
        <Alert tone="danger" className="mt-4">No se pudo cargar el código de error: {state.error}
        </Alert>
      )}

      {state.status === 'loading' && !ec && (
        <div className="mt-4 rounded-lg border border-ui-border bg-ui-card p-6 text-center text-sm text-text-muted dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-muted">
          Cargando…
        </div>
      )}

      {ec && (
        <div className="mt-4 space-y-4">
          <div className="rounded-lg border border-ui-border bg-ui-card p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
            <ErrorCodeForm
              value={editing ? form : toFormState(ec)}
              applications={applications}
              disabled={!editing || saving}
              codeReadOnly
              applicationReadOnly
              onChange={onChangeForm}
            />

            {editing && saveError && (
              <Alert tone="danger" className="mt-4">{saveError}</Alert>
            )}

            {editing && (
              <div className="mt-4 flex justify-end gap-2">
                <Button variant="secondary" size="sm" onClick={onCancelEdit} disabled={saving}>
                  Cancelar
                </Button>
                <Button variant="primary" size="sm" onClick={onSave} disabled={saving} loading={saving}>
                  {saving ? '…' : 'Guardar'}
                </Button>
              </div>
            )}
          </div>

          <div className="rounded-lg border border-ui-border bg-ui-card p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
            <h2 className="text-base font-semibold text-text-primary dark:text-text-dark-primary">
              Comentarios
            </h2>
            <div className="mt-3">
              <CommentThread commentableType="error-codes" commentableId={ec.id} />
            </div>
          </div>
        </div>
      )}

      <ConfirmDialog
        open={confirmDelete}
        title="Eliminar código de error"
        description="¿Confirmas que quieres eliminar este código de error? Esta acción no se puede deshacer."
        confirmLabel="Eliminar"
        variant="danger"
        loading={deleting}
        onConfirm={onDelete}
        onCancel={() => !deleting && setConfirmDelete(false)}
      />
    </div>
  );
}
