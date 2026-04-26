import { useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { Navigate, Route, Routes } from 'react-router-dom';
import { AppLayout } from '@maya/shared-layout-react';
import { LocaleSelector, NotificationsBell, SidebarFavorites } from '@maya/shared-sidebar-react';
import './App.css';
import { useOidcSession } from './auth/useOidcSession';
import { useNavItems } from './components/layout';
import { profileDisplayInitials, useUserProfile } from './features/user-profile';

import {
  ArchivedLogDetailPage,
  ArchivedLogsPage,
  DashboardPage,
  ErrorCodeCreatePage,
  ErrorCodeDetailPage,
  ErrorCodesPage,
  LogDetailPage,
  LogsPage,
  PlaceholderPage,
} from './pages';

const DASHBOARD_API_URL = (import.meta.env.VITE_DASHBOARD_API_URL as string | undefined)
  ?? 'http://maya_dashboard_api.localhost';

function AppRoutes() {
  const { t } = useTranslation('common');
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/dashboard" replace />} />
      <Route path="/dashboard" element={<DashboardPage />} />
      <Route path="/logs" element={<LogsPage />} />
      <Route path="/logs/:id" element={<LogDetailPage />} />
      <Route path="/archived-logs" element={<ArchivedLogsPage />} />
      <Route path="/archived-logs/:id" element={<ArchivedLogDetailPage />} />
      <Route path="/error-codes" element={<ErrorCodesPage />} />
      <Route path="/error-codes/create" element={<ErrorCodeCreatePage />} />
      <Route path="/error-codes/:id" element={<ErrorCodeDetailPage />} />
      <Route path="*" element={<PlaceholderPage title={t('notFound')} />} />
    </Routes>
  );
}

function AppWithLayout() {
  const { logout, user } = useOidcSession();
  const { profile } = useUserProfile();
  const navItems = useNavItems();
  const { i18n } = useTranslation();

  // Prefer backend profile name; fall back to Keycloak token (same pattern as other apps)
  const tokenDisplayName = ((user?.name ?? user?.preferred_username ?? '') as string).trim();
  const userName = profile?.name?.trim() || tokenDisplayName;
  const userInitials = profile
    ? profileDisplayInitials(profile)
    : tokenDisplayName
      ? tokenDisplayName.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase() || 'U'
      : 'U';

  // Sync Keycloak locale preference to i18next
  useEffect(() => {
    if (user?.locale) void i18n.changeLanguage(user.locale);
  }, [user?.locale, i18n]);

  // Sync locale changes made in other browser tabs
  useEffect(() => {
    const onStorage = (e: StorageEvent) => {
      if (e.key === 'locale' && e.newValue) void i18n.changeLanguage(e.newValue);
    };
    window.addEventListener('storage', onStorage);
    return () => window.removeEventListener('storage', onStorage);
  }, [i18n]);

  return (
    <AppLayout
      navItems={navItems}
      brandName="Maya Logs"
      brandVersion="Maya Logs v1.0"
      userName={userName}
      userInitials={userInitials}
      onLogout={logout}
      topbarActions={
        <>
          <NotificationsBell dashboardApiUrl={DASHBOARD_API_URL} />
          <LocaleSelector />
        </>
      }
      sidebarFooter={<SidebarFavorites label="Favoritas" dashboardApiUrl={DASHBOARD_API_URL} />}
    >
      <AppRoutes />
    </AppLayout>
  );
}

export default function App() {
  const { t } = useTranslation('auth');
  const { isOidcLoading, isOidcSignedIn, beginSignIn } = useOidcSession();

  useEffect(() => {
    if (!isOidcLoading && !isOidcSignedIn) {
      beginSignIn();
    }
  }, [isOidcLoading, isOidcSignedIn, beginSignIn]);

  if (isOidcLoading) {
    return (
      <div className="flex items-center justify-center h-screen bg-ui-body dark:bg-ui-dark-bg text-text-muted dark:text-text-dark-muted font-sans">
        {t('initializing')}
      </div>
    );
  }

  if (!isOidcSignedIn) {
    return (
      <div className="flex items-center justify-center h-screen bg-ui-body dark:bg-ui-dark-bg text-text-muted dark:text-text-dark-muted font-sans">
        {t('redirecting')}
      </div>
    );
  }

  return <AppWithLayout />;
}
