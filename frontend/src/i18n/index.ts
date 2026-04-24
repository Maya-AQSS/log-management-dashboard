import i18next from 'i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import { initReactI18next } from 'react-i18next';

import {
  DEFAULT_LOCALE,
  NAMESPACES,
  SUPPORTED_LOCALES,
  resources,
  type SupportedLocale,
} from './resources';

const STORAGE_KEY = 'maya_logs.locale';

void i18next
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources,
    fallbackLng: DEFAULT_LOCALE,
    supportedLngs: SUPPORTED_LOCALES as unknown as string[],
    load: 'languageOnly',
    defaultNS: 'common',
    ns: NAMESPACES as unknown as string[],
    interpolation: { escapeValue: false },
    returnNull: false,
    detection: {
      order: ['localStorage', 'navigator', 'htmlTag'],
      lookupLocalStorage: STORAGE_KEY,
      caches: ['localStorage'],
    },
    react: { useSuspense: false },
  });

export function changeLocale(locale: SupportedLocale): Promise<unknown> {
  return i18next.changeLanguage(locale);
}

export function getCurrentLocale(): SupportedLocale {
  const lang = (i18next.resolvedLanguage ?? i18next.language ?? DEFAULT_LOCALE).split('-')[0];
  return (SUPPORTED_LOCALES as readonly string[]).includes(lang)
    ? (lang as SupportedLocale)
    : DEFAULT_LOCALE;
}

export { DEFAULT_LOCALE, SUPPORTED_LOCALES } from './resources';
export type { SupportedLocale, Namespace } from './resources';
export default i18next;
