import type { LogSeverity } from '../../types/logs';

export type SeverityKey = LogSeverity | 'all';

export type SeverityCardPalette = {
  background: string;
  text: string;
  border: string;
};

export const SEVERITY_BADGE_CLASSES: Record<LogSeverity, string> = {
  critical:
    'inline-flex items-center rounded-full bg-danger-light px-2 py-0.5 text-xs font-semibold text-danger-dark',
  high: 'inline-flex items-center rounded-full bg-warning-light px-2 py-0.5 text-xs font-semibold text-warning-dark',
  medium:
    'inline-flex items-center rounded-full bg-warning-light px-2 py-0.5 text-xs font-semibold text-warning-dark opacity-80',
  low: 'inline-flex items-center rounded-full bg-success-light px-2 py-0.5 text-xs font-semibold text-success-dark',
  other:
    'inline-flex items-center rounded-full bg-ui-body px-2 py-0.5 text-xs font-semibold text-text-secondary',
};

export const SEVERITY_CARD_CLASSES: Record<LogSeverity, SeverityCardPalette> = {
  critical: {
    background: 'bg-danger-light dark:bg-danger-dark/50',
    text: 'text-danger-dark dark:text-white',
    border: 'border-danger/20 dark:border-danger/50',
  },
  high: {
    background: 'bg-warning-light dark:bg-warning-dark/50',
    text: 'text-warning-dark dark:text-white',
    border: 'border-warning/20 dark:border-warning/50',
  },
  medium: {
    background: 'bg-orange-100 dark:bg-orange-800/50',
    text: 'text-orange-800 dark:text-white',
    border: 'border-orange-300 dark:border-orange-500/50',
  },
  low: {
    background: 'bg-odoo-teal/10 dark:bg-odoo-teal/40',
    text: 'text-odoo-teal-d dark:text-white',
    border: 'border-odoo-teal/20 dark:border-odoo-teal/50',
  },
  other: {
    background: 'bg-ui-card dark:bg-ui-dark-card',
    text: 'text-text-primary dark:text-white',
    border: 'border-ui-border dark:border-ui-dark-border',
  },
};

export const SEVERITY_CARD_DEFAULT: SeverityCardPalette = {
  background: 'bg-odoo-purple/10 dark:bg-odoo-purple/40',
  text: 'text-odoo-purple-d dark:text-white',
  border: 'border-odoo-purple/20 dark:border-odoo-purple/50',
};

export function severityCardPaletteFor(key: string): SeverityCardPalette {
  return (SEVERITY_CARD_CLASSES as Record<string, SeverityCardPalette>)[key] ?? SEVERITY_CARD_DEFAULT;
}

export function severityBadgeClassFor(severity: string | null | undefined): string {
  if (!severity) return 'text-slate-500';
  return (
    (SEVERITY_BADGE_CLASSES as Record<string, string>)[severity] ?? 'text-slate-500'
  );
}

export const SEVERITY_LABELS_ES: Record<string, string> = {
  all: 'Todos',
  critical: 'Crítica',
  high: 'Alta',
  medium: 'Media',
  low: 'Baja',
  other: 'Otra',
};

export function severityLabel(key: string): string {
  return SEVERITY_LABELS_ES[key] ?? key;
}
