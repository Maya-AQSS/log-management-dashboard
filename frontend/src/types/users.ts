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
  roles: string[];
  scope: string;
};

export type MeProfileResponse = {
  data: MeProfile;
};
