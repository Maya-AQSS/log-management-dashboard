import { useTranslation } from 'react-i18next';
import { DateRangeFilter as SharedDateRangeFilter } from '@maya/shared-data-react';

type DateRangeFilterProps = {
  from: string | null;
  to: string | null;
  onChange: (range: { from: string | null; to: string | null }) => void;
  fromLabel?: string;
  toLabel?: string;
};

/**
 * Wrapper sobre `@maya/shared-data-react` que rellena los textos por defecto
 * desde `common.filters.dateFrom/dateTo`.
 */
export function DateRangeFilter({ from, to, onChange, fromLabel, toLabel }: DateRangeFilterProps) {
  const { t } = useTranslation('common');

  return (
    <SharedDateRangeFilter
      from={from}
      to={to}
      onChange={onChange}
      fromLabel={fromLabel ?? t('filters.dateFrom')}
      toLabel={toLabel ?? t('filters.dateTo')}
    />
  );
}
