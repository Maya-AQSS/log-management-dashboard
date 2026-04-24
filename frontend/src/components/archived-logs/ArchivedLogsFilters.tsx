import { useTranslation } from 'react-i18next';
import type { ApplicationRef } from '../../types/logs';
import {
  ApplicationSelect,
  DateRangeFilter,
  SeverityFilterCheckboxes,
} from '../filters';

export type ArchivedLogsFiltersState = {
  severity: string[];
  applicationId: number | null;
  dateFrom: string | null;
  dateTo: string | null;
};

type ArchivedLogsFiltersProps = {
  value: ArchivedLogsFiltersState;
  applications: ApplicationRef[];
  onChange: (patch: Partial<ArchivedLogsFiltersState>) => void;
  onReset: () => void;
};

export function ArchivedLogsFilters({
  value,
  applications,
  onChange,
  onReset,
}: ArchivedLogsFiltersProps) {
  const { t } = useTranslation('archivedLogs');
  return (
    <div className="mt-4 rounded-lg border border-ui-border bg-ui-body p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
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
