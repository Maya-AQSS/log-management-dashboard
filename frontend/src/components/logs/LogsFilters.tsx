import { useTranslation } from 'react-i18next';
import type { ApplicationRef } from '../../types/logs';
import {
  ApplicationSelect,
  DateRangeFilter,
  ResolvedFilter,
  SearchInput,
  SeverityFilterCheckboxes,
} from '../filters';
import type { ResolvedFilterValue } from '../filters/ResolvedFilter';

export type LogsFiltersState = {
  search: string;
  severity: string[];
  applicationId: number | null;
  dateFrom: string | null;
  dateTo: string | null;
  resolved: ResolvedFilterValue;
};

type LogsFiltersProps = {
  value: LogsFiltersState;
  applications: ApplicationRef[];
  onChange: (patch: Partial<LogsFiltersState>) => void;
  onReset: () => void;
};

export function LogsFilters({ value, applications, onChange, onReset }: LogsFiltersProps) {
  const { t } = useTranslation('logs');

  return (
    <div className="mt-4 rounded-lg border border-ui-border bg-ui-body p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
      <div className="mb-4">
        <SearchInput
          value={value.search}
          placeholder={t('filters.searchPlaceholder')}
          onChange={(search) => onChange({ search })}
        />
      </div>

      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div className="space-y-4">
          <DateRangeFilter
            from={value.dateFrom}
            to={value.dateTo}
            onChange={({ from, to }) => onChange({ dateFrom: from, dateTo: to })}
          />
          <SeverityFilterCheckboxes
            selected={value.severity}
            onChange={(severity) => onChange({ severity })}
          />
        </div>
        <div className="space-y-4">
          <ApplicationSelect
            applications={applications}
            value={value.applicationId}
            hideLabel
            placeholder={t('filters.applicationAll')}
            onChange={(applicationId) => onChange({ applicationId })}
          />
          <ResolvedFilter
            value={value.resolved}
            label={t('filters.resolved')}
            onChange={(resolved) => onChange({ resolved })}
          />
        </div>
      </div>

      <div className="mt-4 flex w-full justify-center gap-2">
        <button
          type="button"
          onClick={onReset}
          className="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer"
        >
          {t('filters.reset')}
        </button>
      </div>
    </div>
  );
}
