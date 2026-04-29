import { useTranslation } from'react-i18next';
import type { ApplicationRef } from'../../types/logs';

type ApplicationSelectProps = {
 applications: ApplicationRef[];
 value: number | null;
 onChange: (id: number | null) => void;
 label?: string;
 hideLabel?: boolean;
 placeholder?: string;
};

export function ApplicationSelect({
 applications,
 value,
 onChange,
 label,
 hideLabel = false,
 placeholder,
}: ApplicationSelectProps) {
 const { t } = useTranslation('common');
 const resolvedLabel = label ?? t('filters.applicationLabel');
 const resolvedPlaceholder = placeholder ?? t('filters.applicationAll');

 return (<div>
 {!hideLabel && resolvedLabel && (<label className="mb-1 block text-xs font-semibold text-on-surface-variant">
 {resolvedLabel}
 </label>
 )}
 <div className="relative">
 <select
 value={value ??''}
 onChange={(e) => {
 const v = e.target.value;
 onChange(v ==='' ? null : Number(v));
 }}
 className="w-full appearance-none rounded-lg border border-outline bg-surface-container-low px-3 py-2 pr-10 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
 >
 <option value="">{resolvedPlaceholder}</option>
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
 );
}
