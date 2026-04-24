import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import { useOidcSession } from '../../../auth/useOidcSession';
import { fetchMe } from '../../../api/auth';
import type { MeProfile } from '../../../types/users';

export type UserProfileContextValue = {
  profile: MeProfile | null;
  loading: boolean;
  error: Error | null;
  /** Recarga el perfil desde GET /api/v1/me (p. ej. tras cambios de rol). */
  reload: () => Promise<void>;
  hasRole: (role: string) => boolean;
};

const UserProfileContext = createContext<UserProfileContextValue | undefined>(undefined);

export function UserProfileProvider({ children }: { children: ReactNode }) {
  const { isOidcSignedIn } = useOidcSession();
  const [profile, setProfile] = useState<MeProfile | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const load = useCallback(async () => {
    if (!isOidcSignedIn) {
      setProfile(null);
      setError(null);
      return;
    }
    setLoading(true);
    setError(null);
    try {
      const data = await fetchMe();
      setProfile(data);
    } catch (e) {
      setProfile(null);
      setError(e instanceof Error ? e : new Error(String(e)));
    } finally {
      setLoading(false);
    }
  }, [isOidcSignedIn]);

  useEffect(() => {
    if (!isOidcSignedIn) {
      setProfile(null);
      setError(null);
      setLoading(false);
      return;
    }
    void load();
  }, [isOidcSignedIn, load]);

  const hasRole = useCallback(
    (role: string) => profile?.roles.includes(role) ?? false,
    [profile],
  );

  const value = useMemo(
    (): UserProfileContextValue => ({
      profile,
      loading,
      error,
      reload: load,
      hasRole,
    }),
    [profile, loading, error, load, hasRole],
  );

  return <UserProfileContext.Provider value={value}>{children}</UserProfileContext.Provider>;
}

export function useUserProfile(): UserProfileContextValue {
  const ctx = useContext(UserProfileContext);
  if (ctx === undefined) {
    throw new Error('useUserProfile debe usarse dentro de UserProfileProvider');
  }
  return ctx;
}

/** Iniciales para avatar a partir de nombre, first/last o email. */
export function profileDisplayInitials(profile: MeProfile | null): string {
  if (!profile) return 'U';
  const full =
    [profile.first_name, profile.last_name].filter(Boolean).join(' ').trim() ||
    profile.name?.trim() ||
    profile.username?.trim() ||
    profile.email?.trim() ||
    '';
  if (!full) return 'U';
  const parts = full.split(/\s+/).filter(Boolean);
  if (parts.length >= 2) {
    return `${parts[0][0] ?? ''}${parts[1][0] ?? ''}`.toUpperCase().slice(0, 2) || 'U';
  }
  return full.slice(0, 2).toUpperCase();
}
