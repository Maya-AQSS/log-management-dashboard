import { useState } from'react';
import { useTranslation } from'react-i18next';
import type { ApplicationRef } from'../../types/logs';
import { Button } from'../ui';
import {
 ApplicationSelect,
 ResolvedFilter,
 SearchInput,
 SeverityFilterCheckboxes,
} from'../filters';
import type { ResolvedFilterValue } from'../filters/ResolvedFilter';

const ChevronIcon = ({ open }: { open: boolean }) => (<svg
 xmlns="http://www.w3.org/2000/svg"
 viewBox="0 0 20 20"
 fill="currentColor"
 aria-hidden="true"
 className={`w-4 h-4 transition-transform ${open ?'rotate-180' :''}`}
 >
 <path
 fillRule="evenodd"
 d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
 clipRule="evenodd"
 />
 </svg>
);

const dateInputClass =
'w-full rounded-lg border border-outline bg-surface-container-low px-3 py-2 text-sm shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20';
const labelClass =
'mb-1 block text-xs font-semibold text-on-surface-variant';

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
 const [isOpen, setIsOpen] = useState(false);

 const hasActiveFilters =
 value.search ||
 value.severity.length > 0 ||
 value.applicationId !== null ||
 value.dateFrom ||
 value.dateTo ||
 value.resolved !=='all';

 return (<div className="bg-surface-container-low border border-outline rounded-lg mb-6 shadow-sm">
 {/* Toggle visible solo en móvil */}
 <Button
 variant="unstyled"
 size="sm"
 onClick={() => setIsOpen((v) => !v)}
 aria-expanded={isOpen}
 aria-controls="logs-filter-panel"
 className="md:hidden w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-on-surface"
 >
 <span>
 {t('filters.title')}
 {hasActiveFilters && (<span className="ml-2 inline-flex items-center justify-center w-2 h-2 rounded-full bg-primary" aria-hidden="true" />
 )}
 </span>
 <ChevronIcon open={isOpen} />
 </Button>

 {/* Panel de filtros: colapsable en móvil, siempre visible en ≥ md */}
 <div
 id="logs-filter-panel"
 className={`${isOpen ?'flex' :'hidden'} md:flex flex-col gap-3 p-4`}
 >
 {/* Fila 1: búsqueda */}
 <SearchInput
 value={value.search}
 placeholder={t('filters.searchPlaceholder')}
 hideLabel
 onChange={(search) => onChange({ search })}
 />

 {/* Fila 2: fechas + aplicación + estado */}
 <div className="flex flex-wrap items-end gap-2">
 <div className="flex-1 min-w-[110px]">
 <label className={labelClass}>{t('filters.dateFrom')}</label>
 <input
 type="date"
 value={value.dateFrom ??''}
 onChange={(e) => onChange({ dateFrom: e.target.value || null })}
 className={dateInputClass}
 />
 </div>
 <div className="flex-1 min-w-[110px]">
 <label className={labelClass}>{t('filters.dateTo')}</label>
 <input
 type="date"
 value={value.dateTo ??''}
 onChange={(e) => onChange({ dateTo: e.target.value || null })}
 className={dateInputClass}
 />
 </div>
 <div className="flex-1 min-w-[140px]">
 <ApplicationSelect
 applications={applications}
 value={value.applicationId}
 placeholder={t('filters.applicationAll')}
 onChange={(applicationId) => onChange({ applicationId })}
 />
 </div>
 <div className="flex-1 min-w-[130px]">
 <ResolvedFilter
 value={value.resolved}
 label={t('filters.resolved')}
 onChange={(resolved) => onChange({ resolved })}
 />
 </div>
 </div>

 {/* Fila 3: severidad + botón reset */}
 <div className="flex flex-wrap items-end justify-between gap-3">
 <SeverityFilterCheckboxes
 selected={value.severity}
 onChange={(severity) => onChange({ severity })}
 />
 <Button type="button" variant="secondary" size="sm" onClick={onReset}>
 {t('filters.reset')}
 </Button>
 </div>
 </div>
 </div>
 );
}
