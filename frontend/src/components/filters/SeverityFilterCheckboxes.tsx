import { useTranslation } from 'react-i18next';
import { LOG_SEVERITY_KEYS } from '../../types/logs';
import { severityLabel } from '../severity';

type SeverityFilterCheckboxesProps = {
  selected: string[];
  onChange: (selected: string[]) => void;
  label?: string;
};

export function SeverityFilterCheckboxes({
  selected,
  onChange,
  label,
}: SeverityFilterCheckboxesProps) {
  const { t } = useTranslation('common');
  const resolvedLabel = label ?? t('filters.severityLabel');

  function toggle(key: string) {
    if (selected.includes(key)) {
      onChange(selected.filter((k) => k !== key));
    } else {
      onChange([...selected, key]);
    }
  }

  return (
    <fieldset>
      <legend className="mb-1 block text-xs font-semibold text-text-secondary dark:text-text-dark-secondary">
        {resolvedLabel}
      </legend>
      <div className="flex flex-wrap gap-x-4 gap-y-1.5">
        {LOG_SEVERITY_KEYS.map((key) => (
          <label
            key={key}
            className="flex items-center gap-2 text-sm text-text-primary dark:text-text-dark-primary"
          >
            <input
              type="checkbox"
              checked={selected.includes(key)}
              onChange={() => toggle(key)}
              className="h-4 w-4 rounded border-ui-border bg-ui-card text-odoo-purple shadow-sm focus:ring-odoo-purple/30 dark:border-ui-dark-border dark:bg-ui-dark-card"
            />
            <span>{severityLabel(key)}</span>
          </label>
        ))}
      </div>
    </fieldset>
  );
}
