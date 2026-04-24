import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import type { NavItem } from '@maya/shared-layout-react';
import {
  AppsIcon,
  FolderIcon,
  HomeIcon,
  SearchIcon,
} from '@maya/shared-layout-react';

export function useNavItems(): NavItem[] {
  const { t } = useTranslation('nav');
  return useMemo<NavItem[]>(
    () => [
      { id: 'dashboard', label: t('dashboard'), icon: HomeIcon, path: '/dashboard' },
      { id: 'logs', label: t('logs'), icon: SearchIcon, path: '/logs' },
      { id: 'archived-logs', label: t('archivedLogs'), icon: FolderIcon, path: '/archived-logs' },
      { id: 'error-codes', label: t('errorCodes'), icon: AppsIcon, path: '/error-codes' },
    ],
    [t],
  );
}
