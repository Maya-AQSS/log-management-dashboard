import type { DashboardPayload, DashboardResponse } from'../types/dashboard';
import { apiGetJson } from'./http';

export type { DashboardPayload, SeverityCard, ApplicationTotal } from'../types/dashboard';

/** GET /api/v1/dashboard — cards de severidad + totales por aplicación. */
export async function fetchDashboard(): Promise<DashboardPayload> {
 const body = await apiGetJson<DashboardResponse>('dashboard');
 return body.data;
}
