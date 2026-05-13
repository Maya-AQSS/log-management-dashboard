import type { BaseMeProfile } from '@maya/shared-profile-react';

/**
 * Shape del perfil devuelto por `GET /api/v1/me` de maya_logs. Hoy proyecta
 * el JWT (`Maya\Profile\Repositories\Resolvers\JwtPassthroughResolver`).
 */
export type MeProfile = BaseMeProfile & {
  first_name: string | null;
  last_name: string | null;
  username: string | null;
  department: string | null;
  organization_id: string | null;
  /** @deprecated Preferir `permissions` (maya_authorization); se mantiene por compatibilidad con tokens antiguos. */
  roles: string[];
  /** Códigos de permiso (claim JWT `permissions`). */
  permissions: string[];
  scope: string;
};
