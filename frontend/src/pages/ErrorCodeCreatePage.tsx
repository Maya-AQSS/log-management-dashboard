import { useCallback, useEffect, useState } from 'react';
import { Alert, Button, PageTitle } from '@maya/shared-ui-react';
import { Link, useNavigate } from 'react-router-dom';
import { fetchApplications } from '../api/applications';
import { createErrorCode, type ErrorCodePayload } from '../api/errorCodes';
import { ErrorCodeForm, type ErrorCodeFormState } from '../components/error-codes';
import type { ApplicationRef } from '../types/logs';

const EMPTY_FORM: ErrorCodeFormState = {
  application_id: null,
  code: '',
  name: '',
  file: '',
  line: '',
  description: '',
};

function toPayload(form: ErrorCodeFormState): ErrorCodePayload {
  const parsedLine = form.line.trim() === '' ? null : Number(form.line);
  return {
    application_id: form.application_id as number,
    code: form.code,
    name: form.name,
    file: form.file.trim() === '' ? null : form.file,
    line: parsedLine != null && Number.isFinite(parsedLine) ? parsedLine : null,
    description: form.description.trim() === '' ? null : form.description,
  };
}

export function ErrorCodeCreatePage() {
  const navigate = useNavigate();
  const [applications, setApplications] = useState<ApplicationRef[]>([]);
  const [form, setForm] = useState<ErrorCodeFormState>(EMPTY_FORM);
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);

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

  const onChangeForm = useCallback((patch: Partial<ErrorCodeFormState>) => {
    setForm((f) => ({ ...f, ...patch }));
  }, []);

  const onSave = useCallback(async () => {
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
      const created = await createErrorCode(toPayload(form));
      navigate(`/error-codes/${created.id}`);
    } catch (e) {
      setSaveError(e instanceof Error ? e.message : String(e));
      setSaving(false);
    }
  }, [form, navigate]);

  return (
    <div className="px-4 py-6 sm:px-6 lg:px-8">
      <PageTitle
        title="Nuevo código de error"
        onBack={() => navigate(-1)}
        backLabel="Volver"
      />

      <div className="mt-4 rounded-lg border border-ui-border bg-ui-card p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
        <ErrorCodeForm
          value={form}
          applications={applications}
          disabled={saving}
          onChange={onChangeForm}
        />

        {saveError && (
          <Alert tone="danger" className="mt-4">{saveError}</Alert>
        )}

        <div className="mt-4 flex justify-end gap-2">
          <Link
            to="/error-codes"
            className="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer"
          >
            Cancelar
          </Link>
          <Button variant="primary" size="sm" onClick={onSave} disabled={saving} loading={saving}>
            {saving ? '…' : 'Crear'}
          </Button>
        </div>
      </div>
    </div>
  );
}
