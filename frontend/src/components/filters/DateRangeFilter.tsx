import { useTranslation } from 'react-i18next';

type DateRangeFilterProps = {
  from: string | null;
  to: string | null;
  onChange: (range: { from: string | null; to: string | null }) => void;
  fromLabel?: string;
  toLabel?: string;
};

export function DateRangeFilter({
  from,
  to,
  onChange,
  fromLabel,
  toLabel,
}: DateRangeFilterProps) {
  const { t } = useTranslation('common');
  const resolvedFromLabel = fromLabel ?? t('filters.dateFrom');
  const resolvedToLabel = toLabel ?? t('filters.dateTo');

  const inputClass =
    'w-full rounded-lg border border-ui-border bg-ui-card px-3 py-2 text-base shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary focus:border-odoo-purple focus:outline-none focus:ring-2 focus:ring-odoo-purple/20';
  const labelClass =
    'mb-1 block text-sm font-medium text-text-secondary dark:text-text-dark-secondary';
  return (
    <div className="grid grid-cols-2 gap-2">
      <div>
        <label className={labelClass}>{resolvedFromLabel}</label>
        <input
          type="date"
          value={from ?? ''}
          onChange={(e) => onChange({ from: e.target.value || null, to })}
          className={inputClass}
        />
      </div>
      <div>
        <label className={labelClass}>{resolvedToLabel}</label>
        <input
          type="date"
          value={to ?? ''}
          onChange={(e) => onChange({ from, to: e.target.value || null })}
          className={inputClass}
        />
      </div>
    </div>
  );
}
