import { useTranslation } from'react-i18next';
import type { ApplicationRef } from'../../types/logs';

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

 return (<div className="grid grid-cols-1 gap-4 md:grid-cols-2">
 <div>
 <label
 htmlFor="error-code-name"
 className="block text-sm font-medium text-on-surface-variant"
 >
 {t('form.name')} <span className="text-danger">*</span>
 </label>
 <input
 id="error-code-name"
 type="text"
 value={value.name}
 onChange={(e) => onChange({ name: e.target.value })}
 disabled={disabled}
 required
 className="mt-1 w-full rounded-lg border border-outline bg-surface px-3 py-2.5 text-sm text-on-surface shadow-inner focus:border-primary focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
 />
 </div>

 <div>
 <label
 htmlFor="error-code-code"
 className="block text-sm font-medium text-on-surface-variant"
 >
 {t('form.code')} <span className="text-danger">*</span>
 </label>
 <input
 id="error-code-code"
 type="text"
 value={value.code}
 onChange={(e) => onChange({ code: e.target.value })}
 disabled={disabled}
 readOnly={codeReadOnly}
 required
 className="mt-1 w-full rounded-lg border border-outline bg-surface px-3 py-2.5 font-mono text-sm text-on-surface shadow-inner focus:border-primary focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 read-only:bg-surface-container-low dark:read-only:bg-surface-container-low"
 />
 </div>

 <div>
 <label
 htmlFor="error-code-application"
 className="block text-sm font-medium text-on-surface-variant"
 >
 {t('form.application')} <span className="text-danger">*</span>
 </label>
 <div className="relative mt-1">
 <select
 id="error-code-application"
 value={value.application_id ??''}
 onChange={(e) => {
 const v = e.target.value;
 onChange({ application_id: v ==='' ? null : Number(v) });
 }}
 disabled={applicationLocked}
 required
 className="w-full appearance-none rounded-lg border border-outline bg-surface px-3 py-2.5 pr-10 text-sm text-on-surface shadow-inner focus:border-primary focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
 >
 <option value="">{t('form.applicationPlaceholder')}</option>
 {applications.map((app) => (<option key={app.id} value={app.id}>
 {app.name}
 </option>
 ))}
 </select>
 <span
 className="pointer-events-none absolute inset-y-0 right-3 flex items-center text-on-surface-muted"
 aria-hidden
 >
 ▾
 </span>
 </div>
 </div>

 <div>
 <label
 htmlFor="error-code-file"
 className="block text-sm font-medium text-on-surface-variant"
 >
 {t('form.file')}
 </label>
 <input
 id="error-code-file"
 type="text"
 value={value.file}
 onChange={(e) => onChange({ file: e.target.value })}
 disabled={disabled}
 className="mt-1 w-full rounded-lg border border-outline bg-surface px-3 py-2.5 font-mono text-sm text-on-surface shadow-inner focus:border-primary focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
 />
 </div>

 <div>
 <label
 htmlFor="error-code-line"
 className="block text-sm font-medium text-on-surface-variant"
 >
 {t('form.line')}
 </label>
 <input
 id="error-code-line"
 type="number"
 min={1}
 value={value.line}
 onChange={(e) => onChange({ line: e.target.value })}
 disabled={disabled}
 className="mt-1 w-full rounded-lg border border-outline bg-surface px-3 py-2.5 text-sm text-on-surface shadow-inner focus:border-primary focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
 />
 </div>

 <div className="md:col-span-2">
 <label
 htmlFor="error-code-description"
 className="block text-sm font-medium text-on-surface-variant"
 >
 {t('form.description')}
 </label>
 <textarea
 id="error-code-description"
 value={value.description}
 onChange={(e) => onChange({ description: e.target.value })}
 disabled={disabled}
 rows={4}
 className="mt-1 w-full rounded-lg border border-outline bg-surface px-3 py-2.5 text-sm text-on-surface shadow-inner focus:border-primary focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
 />
 </div>
 </div>
 );
}
