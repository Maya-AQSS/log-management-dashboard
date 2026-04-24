import { useTranslation } from 'react-i18next';
import { SUPPORTED_LOCALES, type SupportedLocale } from '../../i18n';

const LABELS: Record<SupportedLocale, string> = {
  es: 'ES',
  va: 'VA',
  en: 'EN',
};

const FULL_LABELS: Record<SupportedLocale, string> = {
  es: 'Español',
  va: 'Valencià',
  en: 'English',
};

export function LanguageSwitcher() {
  const { i18n } = useTranslation();
  const current = (i18n.resolvedLanguage ?? i18n.language ?? 'es').split('-')[0] as SupportedLocale;

  return (
    <div
      role="group"
      aria-label="Language"
      className="inline-flex overflow-hidden rounded-md border border-ui-border bg-ui-card text-xs dark:border-ui-dark-border dark:bg-ui-dark-card"
    >
      {SUPPORTED_LOCALES.map((code) => {
        const active = current === code;
        return (
          <button
            key={code}
            type="button"
            onClick={() => void i18n.changeLanguage(code)}
            aria-pressed={active}
            aria-label={FULL_LABELS[code]}
            title={FULL_LABELS[code]}
            className={[
              'px-2 py-1 font-semibold transition-colors',
              active
                ? 'bg-odoo-purple text-white'
                : 'text-text-secondary hover:bg-ui-muted dark:text-text-dark-secondary dark:hover:bg-ui-dark-muted',
            ].join(' ')}
          >
            {LABELS[code]}
          </button>
        );
      })}
    </div>
  );
}
