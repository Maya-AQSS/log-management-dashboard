/**
 * Perfil proyectado desde el JWT en `GET /api/v1/me`.
 * Refleja el payload producido por `JwtMiddleware::setCurrentUser()`.
 */
export type MeProfile = {
  id: string;
  email: string | null;
  name: string | null;
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

export type MeProfileResponse = {
  data: MeProfile;
};
