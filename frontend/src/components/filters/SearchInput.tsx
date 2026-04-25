import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

type SearchInputProps = {
  value: string;
  onChange: (value: string) => void;
  label?: string;
  hideLabel?: boolean;
  placeholder?: string;
  debounceMs?: number;
};

export function SearchInput({
  value,
  onChange,
  label,
  hideLabel = false,
  placeholder,
  debounceMs = 300,
}: SearchInputProps) {
  const { t } = useTranslation('common');
  const [local, setLocal] = useState(value);

  const resolvedLabel = label ?? t('filters.searchLabel');
  const resolvedPlaceholder = placeholder ?? t('filters.searchPlaceholder');

  useEffect(() => {
    setLocal(value);
  }, [value]);

  useEffect(() => {
    if (local === value) return;
    const handle = setTimeout(() => onChange(local), debounceMs);
    return () => clearTimeout(handle);
  }, [local, value, onChange, debounceMs]);

  return (
    <div>
      {!hideLabel && resolvedLabel && (
        <label className="mb-1 block text-xs font-semibold text-text-secondary dark:text-text-dark-secondary">
          {resolvedLabel}
        </label>
      )}
      <input
        type="search"
        value={local}
        onChange={(e) => setLocal(e.target.value)}
        placeholder={resolvedPlaceholder}
        className="w-full rounded-lg border border-ui-border bg-ui-card px-3 py-2 text-sm shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary focus:border-odoo-purple focus:outline-none focus:ring-2 focus:ring-odoo-purple/20"
      />
    </div>
  );
}
