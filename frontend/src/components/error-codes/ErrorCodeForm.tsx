import { useTranslation } from 'react-i18next';
import { FieldLabel, Select, TextArea, TextInput } from '@maya/shared-ui-react';
import type { ApplicationRef } from '../../types/logs';

export type ErrorCodeFormState = {
  application_id: number | null;
  code: string;
  name: string;
  file: string;
  line: string;
  description: string;
};

type ErrorCodeFormProps = {
  value: ErrorCodeFormState;
  applications: ApplicationRef[];
  disabled?: boolean;
  codeReadOnly?: boolean;
  applicationReadOnly?: boolean;
  onChange: (patch: Partial<ErrorCodeFormState>) => void;
};

export function ErrorCodeForm({
  value,
  applications,
  disabled = false,
  codeReadOnly = false,
  applicationReadOnly = false,
  onChange,
}: ErrorCodeFormProps) {
  const { t } = useTranslation('errorCodes');
  const applicationLocked = applicationReadOnly || disabled;

  return (
    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div>
        <FieldLabel htmlFor="error-code-name" required>
          {t('form.name')}
        </FieldLabel>
        <TextInput
          id="error-code-name"
          fieldSize="comfortable"
          value={value.name}
          onChange={(e) => onChange({ name: e.target.value })}
          disabled={disabled}
          required
        />
      </div>

      <div>
        <FieldLabel htmlFor="error-code-code" required>
          {t('form.code')}
        </FieldLabel>
        <TextInput
          id="error-code-code"
          fieldSize="comfortable"
          value={value.code}
          onChange={(e) => onChange({ code: e.target.value })}
          disabled={disabled}
          readOnly={codeReadOnly}
          required
          className="font-mono"
        />
      </div>

      <div>
        <FieldLabel htmlFor="error-code-application" required>
          {t('form.application')}
        </FieldLabel>
        <Select
          id="error-code-application"
          fieldSize="comfortable"
          value={value.application_id ?? ''}
          onChange={(e) => {
            const v = e.target.value;
            onChange({ application_id: v === '' ? null : Number(v) });
          }}
          disabled={applicationLocked}
          required
        >
          <option value="">{t('form.applicationPlaceholder')}</option>
          {applications.map((app) => (
            <option key={app.id} value={app.id}>
              {app.name}
            </option>
          ))}
        </Select>
      </div>

      <div>
        <FieldLabel htmlFor="error-code-file">{t('form.file')}</FieldLabel>
        <TextInput
          id="error-code-file"
          fieldSize="comfortable"
          value={value.file}
          onChange={(e) => onChange({ file: e.target.value })}
          disabled={disabled}
          className="font-mono"
        />
      </div>

      <div>
        <FieldLabel htmlFor="error-code-line">{t('form.line')}</FieldLabel>
        <TextInput
          id="error-code-line"
          type="number"
          fieldSize="comfortable"
          min={1}
          value={value.line}
          onChange={(e) => onChange({ line: e.target.value })}
          disabled={disabled}
        />
      </div>

      <div className="md:col-span-2">
        <FieldLabel htmlFor="error-code-description">{t('form.description')}</FieldLabel>
        <TextArea
          id="error-code-description"
          fieldSize="comfortable"
          value={value.description}
          onChange={(e) => onChange({ description: e.target.value })}
          disabled={disabled}
          rows={4}
        />
      </div>
    </div>
  );
}
