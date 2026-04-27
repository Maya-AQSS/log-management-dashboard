import { useTranslation } from 'react-i18next';
import { Pagination as SharedPagination, type PaginationMeta } from '@maya/shared-data-react';

type PaginationProps = {
  meta: PaginationMeta;
  onChangePage: (page: number) => void;
};

/**
 * Wrapper que conecta el `Pagination` compartido con i18next (`common.pagination.*`).
 */
export function Pagination({ meta, onChangePage }: PaginationProps) {
  const { t } = useTranslation('common');

  return (
    <SharedPagination
      meta={meta}
      onChangePage={onChangePage}
      ariaLabel={t('pagination.ariaLabel')}
      renderRange={({ from, to, total }) =>
        t('pagination.rangeOf', { from, to, total })
      }
    />
  );
}
