import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { SortDir } from '../../types/api';
import type { ArchivedLog } from '../../types/logs';
import { formatDateTime } from '../../utils/date';
import { SeverityBadge } from '../severity';
import { SortHeader } from '../table';

export type ArchivedLogsSortKey = 'application' | 'severity' | 'archived_at' | 'original_created_at';

type ArchivedLogsTableProps = {
  logs: ArchivedLog[];
  sortBy: ArchivedLogsSortKey | null;
  sortDir: SortDir | null;
  onSort: (column: ArchivedLogsSortKey) => void;
  emptyText?: string;
};

function truncate(text: string | null | undefined, max = 120): string {
  if (!text) return '-';
  if (text.length <= max) return text;
  return `${text.slice(0, max)}…`;
}

export function ArchivedLogsTable({
  logs,
  sortBy,
  sortDir,
  onSort,
  emptyText,
}: ArchivedLogsTableProps) {
  const { t } = useTranslation('archivedLogs');
  const resolvedEmptyText = emptyText ?? t('columns.emptyText');
  const sortTitle = t('columns.sortTitle');

  if (logs.length === 0) {
    return (
      <div className="mt-4 rounded-lg border border-dashed border-ui-border bg-ui-card p-6 text-center text-sm text-text-muted dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-muted">
        {resolvedEmptyText}
      </div>
    );
  }

  return (
    <div className="mt-4 overflow-x-auto rounded-lg border border-ui-border bg-ui-card shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card">
      <table className="min-w-[56rem] w-full text-sm">
        <thead className="bg-ui-body text-xs text-text-secondary dark:bg-ui-dark-border dark:text-text-dark-secondary">
          <tr>
            <th className="px-3 py-2 text-left">
              <SortHeader<ArchivedLogsSortKey>
                label={t('columns.application')}
                column="application"
                activeColumn={sortBy}
                direction={sortDir}
                onSort={onSort}
                title={sortTitle}
              />
            </th>
            <th className="px-3 py-2 text-left">
              <SortHeader<ArchivedLogsSortKey>
                label={t('columns.severity')}
                column="severity"
                activeColumn={sortBy}
                direction={sortDir}
                onSort={onSort}
                title={sortTitle}
              />
            </th>
            <th className="min-w-[16rem] px-3 py-2 text-left md:min-w-[18rem]">{t('columns.message')}</th>
            <th className="px-3 py-2 text-left">
              <SortHeader<ArchivedLogsSortKey>
                label={t('columns.archived')}
                column="archived_at"
                activeColumn={sortBy}
                direction={sortDir}
                onSort={onSort}
                title={sortTitle}
              />
            </th>
          </tr>
        </thead>
        <tbody className="divide-y divide-ui-border dark:divide-ui-dark-border">
          {logs.map((log) => (
            <tr
              key={log.id}
              className="align-top hover:bg-ui-body dark:hover:bg-ui-dark-border/40"
            >
              <td className="px-3 py-2 text-text-primary dark:text-text-dark-primary">
                <Link to={`/archived-logs/${log.id}`} className="block">
                  {log.application?.name ?? '-'}
                </Link>
              </td>
              <td className="px-3 py-2 whitespace-nowrap">
                <Link to={`/archived-logs/${log.id}`} className="block">
                  <SeverityBadge severity={log.severity} />
                </Link>
              </td>
              <td className="min-w-[16rem] max-w-md px-3 py-2 text-text-primary dark:text-text-dark-primary md:min-w-[18rem]">
                <Link to={`/archived-logs/${log.id}`} className="block break-words">
                  {truncate(log.message, 120)}
                </Link>
              </td>
              <td className="px-3 py-2 whitespace-nowrap text-text-primary dark:text-text-dark-primary">
                <Link to={`/archived-logs/${log.id}`} className="block">
                  {formatDateTime(log.archived_at)}
                </Link>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
