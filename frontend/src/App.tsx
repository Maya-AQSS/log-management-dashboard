import { useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { Navigate, Route, Routes } from 'react-router-dom';
import { AppLayout } from '@maya/shared-layout-react';
import './App.css';
import { useOidcSession } from './auth/useOidcSession';
import { useNavItems } from './components/layout';
import { LanguageSwitcher } from './components/ui/LanguageSwitcher';
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
  const { logout } = useOidcSession();
  const { profile } = useUserProfile();
  const navItems = useNavItems();

  const userName = profile?.name?.trim() ?? '';
  const userInitials = profileDisplayInitials(profile);

  return (
    <AppLayout
      navItems={navItems}
      brandName="Maya Logs"
      brandVersion="Maya Logs v1.0"
      userName={userName}
      userInitials={userInitials}
      onLogout={logout}
      topbarActions={<LanguageSwitcher />}
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
