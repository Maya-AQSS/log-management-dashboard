import type { MeProfile, MeProfileResponse } from '../types/users';
import { apiGetJson } from './http';

export type { MeProfile } from '../types/users';

/** GET /api/v1/me — perfil proyectado desde el JWT. */
export async function fetchMe(): Promise<MeProfile> {
  const body = await apiGetJson<MeProfileResponse>('me');
  const d = body.data;
  return {
    ...d,
    roles: Array.isArray(d.roles) ? d.roles : [],
    permissions: Array.isArray(d.permissions) ? d.permissions : [],
  };
}
