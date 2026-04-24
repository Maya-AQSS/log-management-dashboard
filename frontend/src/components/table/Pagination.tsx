import { useTranslation } from 'react-i18next';
import type { PaginationMeta } from '../../types/api';

type PaginationProps = {
  meta: PaginationMeta;
  onChangePage: (page: number) => void;
};

export function Pagination({ meta, onChangePage }: PaginationProps) {
  const { t } = useTranslation('common');
  if (meta.last_page <= 1) return null;

  const current = meta.current_page;
  const last = meta.last_page;

  const pages: Array<number | 'ellipsis'> = [];
  const window = 2;
  for (let p = 1; p <= last; p++) {
    if (
      p === 1 ||
      p === last ||
      (p >= current - window && p <= current + window)
    ) {
      pages.push(p);
    } else if (pages[pages.length - 1] !== 'ellipsis') {
      pages.push('ellipsis');
    }
  }

  return (
    <nav
      className="mt-4 flex items-center justify-between gap-2"
      aria-label={t('pagination.ariaLabel')}
    >
      <div className="text-xs text-text-muted dark:text-text-dark-muted">
        {t('pagination.rangeOf', {
          from: meta.from ?? 0,
          to: meta.to ?? 0,
          total: meta.total,
        })}
      </div>
      <ul className="flex items-center gap-1">
        <li>
          <button
            type="button"
            onClick={() => onChangePage(Math.max(1, current - 1))}
            disabled={current <= 1}
            className="rounded-md border border-ui-border bg-ui-card px-2 py-1 text-sm disabled:opacity-40 dark:border-ui-dark-border dark:bg-ui-dark-card"
          >
            ‹
          </button>
        </li>
        {pages.map((p, idx) =>
          p === 'ellipsis' ? (
            <li
              key={`e-${idx}`}
              className="px-2 text-xs text-text-muted dark:text-text-dark-muted"
            >
              …
            </li>
          ) : (
            <li key={p}>
              <button
                type="button"
                onClick={() => onChangePage(p)}
                aria-current={p === current ? 'page' : undefined}
                className={[
                  'rounded-md border px-3 py-1 text-sm',
                  p === current
                    ? 'border-odoo-purple bg-odoo-purple text-white'
                    : 'border-ui-border bg-ui-card dark:border-ui-dark-border dark:bg-ui-dark-card',
                ].join(' ')}
              >
                {p}
              </button>
            </li>
          ),
        )}
        <li>
          <button
            type="button"
            onClick={() => onChangePage(Math.min(last, current + 1))}
            disabled={current >= last}
            className="rounded-md border border-ui-border bg-ui-card px-2 py-1 text-sm disabled:opacity-40 dark:border-ui-dark-border dark:bg-ui-dark-card"
          >
            ›
          </button>
        </li>
      </ul>
    </nav>
  );
}
