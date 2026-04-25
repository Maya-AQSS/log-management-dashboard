import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { SortDir } from '../../types/api';
import type { Log } from '../../types/logs';
import { formatDateTime } from '../../utils/date';
import { SeverityBadge } from '../severity';
import { SortHeader } from '../table';

export type LogsSortKey = 'application' | 'severity' | 'created_at';

type LogsTableProps = {
  logs: Log[];
  sortBy: LogsSortKey | null;
  sortDir: SortDir | null;
  onSort: (column: LogsSortKey) => void;
  emptyText?: string;
};

function truncate(text: string | null | undefined, max = 120): string {
  if (!text) return '-';
  if (text.length <= max) return text;
  return `${text.slice(0, max)}…`;
}

export function LogsTable({ logs, sortBy, sortDir, onSort, emptyText }: LogsTableProps) {
  const { t } = useTranslation('logs');
  const resolvedEmptyText = emptyText ?? t('emptyText', 'Sin logs');

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
              <SortHeader<LogsSortKey>
                label={t('columns.application')}
                column="application"
                activeColumn={sortBy}
                direction={sortDir}
                onSort={onSort}
                title={t('columns.sortTitle', 'Ordenar')}
              />
            </th>
            <th className="px-3 py-2 text-left">
              <SortHeader<LogsSortKey>
                label={t('columns.severity')}
                column="severity"
                activeColumn={sortBy}
                direction={sortDir}
                onSort={onSort}
                title={t('columns.sortTitle', 'Ordenar')}
              />
            </th>
            <th className="min-w-[16rem] px-3 py-2 text-left md:min-w-[18rem]">
              {t('columns.message')}
            </th>
            <th className="px-3 py-2 text-left">{t('columns.errorCode')}</th>
            <th className="px-3 py-2 text-left">
              <SortHeader<LogsSortKey>
                label={t('columns.createdAt')}
                column="created_at"
                activeColumn={sortBy}
                direction={sortDir}
                onSort={onSort}
                title={t('columns.sortTitle', 'Ordenar')}
              />
            </th>
            <th className="px-3 py-2 text-left">{t('columns.resolved')}</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-ui-border dark:divide-ui-dark-border">
          {logs.map((log) => (
            <tr
              key={log.id}
              className="align-top hover:bg-ui-body dark:hover:bg-ui-dark-border/40"
            >
              <td className="px-3 py-2 text-text-primary dark:text-text-dark-primary">
                <Link to={`/logs/${log.id}`} className="block">
                  {log.application?.name ?? '-'}
                </Link>
              </td>
              <td className="px-3 py-2 whitespace-nowrap">
                <Link to={`/logs/${log.id}`} className="block">
                  <SeverityBadge severity={log.severity} />
                </Link>
              </td>
              <td className="min-w-[16rem] max-w-md px-3 py-2 text-text-primary dark:text-text-dark-primary md:min-w-[18rem]">
                <Link to={`/logs/${log.id}`} className="block break-words">
                  {truncate(log.message, 120)}
                </Link>
              </td>
              <td className="px-3 py-2 whitespace-nowrap text-text-secondary dark:text-text-dark-secondary">
                <Link to={`/logs/${log.id}`} className="block">
                  {log.error_code?.code ?? '-'}
                </Link>
              </td>
              <td className="px-3 py-2 whitespace-nowrap text-text-primary dark:text-text-dark-primary">
                <Link to={`/logs/${log.id}`} className="block">
                  {formatDateTime(log.created_at)}
                </Link>
              </td>
              <td className="px-3 py-2 whitespace-nowrap">
                <Link to={`/logs/${log.id}`} className="block">
                  <span
                    className={
                      log.resolved
                        ? 'inline-flex items-center rounded-full bg-success/10 px-2 py-0.5 text-xs font-medium text-success ring-1 ring-inset ring-success/20'
                        : 'inline-flex items-center rounded-full bg-warning/10 px-2 py-0.5 text-xs font-medium text-warning ring-1 ring-inset ring-warning/20'
                    }
                  >
                    {log.resolved
                      ? t('detail.fields.resolved')
                      : t('detail.fields.unresolved')}
                  </span>
                </Link>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
