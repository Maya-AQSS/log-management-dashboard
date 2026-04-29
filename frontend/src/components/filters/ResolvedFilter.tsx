import { useTranslation } from'react-i18next';

export type ResolvedFilterValue ='all' |'unresolved' |'only';

type ResolvedFilterProps = {
 value: ResolvedFilterValue;
 onChange: (value: ResolvedFilterValue) => void;
 label?: string;
 hideLabel?: boolean;
};

export function ResolvedFilter({ value, onChange, label, hideLabel = false }: ResolvedFilterProps) {
 const { t } = useTranslation('common');
 const resolvedLabel = label ?? t('filters.resolvedLabel');

 const options: Array<{ value: ResolvedFilterValue; label: string }> = [
 { value:'all', label: t('resolved.all') },
 { value:'unresolved', label: t('resolved.unresolved') },
 { value:'only', label: t('resolved.only') },
 ];

 return (<div>
 {!hideLabel && (<label className="mb-1 block text-xs font-semibold text-on-surface-variant">
 {resolvedLabel}
 </label>
 )}
 <div className="relative">
 <select
 value={value}
 onChange={(e) => onChange(e.target.value as ResolvedFilterValue)}
 className="w-full appearance-none rounded-lg border border-outline bg-surface-container-low px-3 py-2 pr-10 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
 >
 {options.map((opt) => (<option key={opt.value} value={opt.value}>
 {opt.label}
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
