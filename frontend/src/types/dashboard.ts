/**
 * Tipos BFF del endpoint `/api/v1/dashboard`.
 */

/** Card de severidad agregada (incluye la card especial "all"). */
export type SeverityCard = {
  key: string;
  totalCount: number;
  resolvedCount: number;
  unresolvedCount: number;
};

/** Totales por aplicación usados en la segunda fila del dashboard. */
export type ApplicationTotal = {
  application_id: number;
  name: string;
  total: number;
};

export type DashboardPayload = {
  severity_cards: SeverityCard[];
  application_totals: ApplicationTotal[];
};

export type DashboardResponse = {
  data: DashboardPayload;
};
