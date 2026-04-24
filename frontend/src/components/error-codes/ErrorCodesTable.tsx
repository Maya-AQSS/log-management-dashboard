import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import type { ErrorCode } from '../../types/logs';

type ErrorCodesTableProps = {
  errorCodes: ErrorCode[];
  emptyText?: string;
};

export function ErrorCodesTable({
  errorCodes,
  emptyText,
}: ErrorCodesTableProps) {
  const { t } = useTranslation('errorCodes');
  const resolvedEmptyText = emptyText ?? t('emptyFiltered');

  if (errorCodes.length === 0) {
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
            <th className="px-3 py-2 text-left">{t('columns.code')}</th>
            <th className="px-3 py-2 text-left">{t('columns.application')}</th>
            <th className="min-w-[14rem] px-3 py-2 text-left">{t('columns.name')}</th>
            <th className="px-3 py-2 text-left">{t('columns.file')}</th>
            <th className="px-3 py-2 text-left">{t('columns.line')}</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-ui-border dark:divide-ui-dark-border">
          {errorCodes.map((ec) => (
            <tr
              key={ec.id}
              className="align-top hover:bg-ui-body dark:hover:bg-ui-dark-border/40"
            >
              <td className="px-3 py-2 font-mono text-text-primary dark:text-text-dark-primary whitespace-nowrap">
                <Link to={`/error-codes/${ec.id}`} className="block">
                  {ec.code}
                </Link>
              </td>
              <td className="px-3 py-2 text-text-primary dark:text-text-dark-primary">
                <Link to={`/error-codes/${ec.id}`} className="block">
                  {ec.application?.name ?? '-'}
                </Link>
              </td>
              <td className="min-w-[14rem] px-3 py-2 text-text-primary dark:text-text-dark-primary">
                <Link to={`/error-codes/${ec.id}`} className="block break-words">
                  {ec.name}
                </Link>
              </td>
              <td className="px-3 py-2 font-mono text-xs text-text-primary dark:text-text-dark-primary">
                <Link to={`/error-codes/${ec.id}`} className="block break-all">
                  {ec.file ?? '-'}
                </Link>
              </td>
              <td className="px-3 py-2 whitespace-nowrap text-text-primary dark:text-text-dark-primary">
                <Link to={`/error-codes/${ec.id}`} className="block">
                  {ec.line ?? '-'}
                </Link>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
