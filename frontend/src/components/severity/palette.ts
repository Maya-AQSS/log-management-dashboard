import type { LogSeverity } from'../../types/logs';

export type SeverityKey = LogSeverity |'all';

export type SeverityCardPalette = {
 background: string;
 text: string;
 border: string;
};

export const SEVERITY_BADGE_CLASSES: Record<LogSeverity, string> = {
 critical:
'inline-flex items-center rounded-full bg-danger-light px-2 py-0.5 text-xs font-semibold text-danger-dark',
 high:'inline-flex items-center rounded-full bg-warning-light px-2 py-0.5 text-xs font-semibold text-warning-dark',
 medium:
'inline-flex items-center rounded-full bg-warning-light px-2 py-0.5 text-xs font-semibold text-warning-dark opacity-80',
 low:'inline-flex items-center rounded-full bg-success-light px-2 py-0.5 text-xs font-semibold text-success-dark',
 other:
'inline-flex items-center rounded-full bg-surface px-2 py-0.5 text-xs font-semibold text-on-surface-variant',
};

export const SEVERITY_CARD_CLASSES: Record<LogSeverity, SeverityCardPalette> = {
 critical: {
 background:'bg-danger-light dark:bg-danger-dark/50',
 text:'text-danger-dark',
 border:'border-danger/20 dark:border-danger/50',
 },
 high: {
 background:'bg-warning-light dark:bg-warning-dark/50',
 text:'text-warning-dark',
 border:'border-warning/20',
 },
 medium: {
 background:'bg-warning-light dark:bg-warning-dark/50',
 text:'text-warning-dark',
 border:'border-warning/30',
 },
 low: {
 background:'bg-secondary/10',
 text:'text-secondary-hover',
 border:'border-secondary/20',
 },
 other: {
 background:'bg-surface-container-low',
 text:'text-on-surface',
 border:'border-outline',
 },
};

export const SEVERITY_CARD_DEFAULT: SeverityCardPalette = {
 background:'bg-primary/10',
 text:'text-primary-hover',
 border:'border-primary/20',
};

export function severityCardPaletteFor(key: string): SeverityCardPalette {
 return (SEVERITY_CARD_CLASSES as Record<string, SeverityCardPalette>)[key] ?? SEVERITY_CARD_DEFAULT;
}

export function severityBadgeClassFor(severity: string | null | undefined): string {
 if (!severity) return'text-on-surface-muted';
 return ((SEVERITY_BADGE_CLASSES as Record<string, string>)[severity] ??'text-on-surface-muted'
 );
}

export const SEVERITY_LABELS_ES: Record<string, string> = {
 all:'Todos',
 critical:'Crítica',
 high:'Alta',
 medium:'Media',
 low:'Baja',
 other:'Otra',
};

export function severityLabel(key: string): string {
 return SEVERITY_LABELS_ES[key] ?? key;
}
