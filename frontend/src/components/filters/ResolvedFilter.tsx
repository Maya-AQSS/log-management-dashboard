import { useTranslation } from 'react-i18next';

export type ResolvedFilterValue = 'all' | 'unresolved' | 'only';

type ResolvedFilterProps = {
  value: ResolvedFilterValue;
  onChange: (value: ResolvedFilterValue) => void;
  label?: string;
};

export function ResolvedFilter({ value, onChange, label }: ResolvedFilterProps) {
  const { t } = useTranslation('common');
  const resolvedLabel = label ?? t('filters.resolvedLabel');

  const options: Array<{ value: ResolvedFilterValue; label: string }> = [
    { value: 'all', label: t('resolved.all') },
    { value: 'unresolved', label: t('resolved.unresolved') },
    { value: 'only', label: t('resolved.only') },
  ];

  return (
    <fieldset>
      <legend className="mb-1 block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
        {resolvedLabel}
      </legend>
      <div className="flex flex-wrap gap-3">
        {options.map((opt) => (
          <label
            key={opt.value}
            className="flex items-center gap-2 text-sm text-text-primary dark:text-text-dark-primary"
          >
            <input
              type="radio"
              name="resolved-filter"
              value={opt.value}
              checked={value === opt.value}
              onChange={() => onChange(opt.value)}
              className="h-4 w-4 border-ui-border text-odoo-purple focus:ring-odoo-purple/30 dark:border-ui-dark-border"
            />
            <span>{opt.label}</span>
          </label>
        ))}
      </div>
    </fieldset>
  );
}
