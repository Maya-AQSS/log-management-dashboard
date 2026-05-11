import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { fetchDashboard } from '../../../api/dashboard';
import type { DashboardPayload } from '../../../types/dashboard';
import { ApplicationTile } from '../../../components/dashboard';

const REFRESH_INTERVAL_MS = 30_000;

function hrefForApplication(id: number): string {
  return `/logs?application_id=${id}`;
}

/** Per-application totals widget — preserves the legacy "By application" section. */
function ApplicationTotalsWidget() {
  const { t } = useTranslation('dashboard');
  const [data, setData] = useState<DashboardPayload | null>(null);
  const [status, setStatus] = useState<'loading' | 'ready' | 'error'>('loading');

  useEffect(() => {
    let cancelled = false;
    function load() {
      fetchDashboard()
        .then((d) => { if (!cancelled) { setData(d); setStatus('ready'); } })
        .catch(() => { if (!cancelled) setStatus('error'); });
    }
    load();
    const id = setInterval(load, REFRESH_INTERVAL_MS);
    return () => { cancelled = true; clearInterval(id); };
  }, []);

  if (status === 'loading') {
    return (
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {[1, 2].map((n) => (
          <div
            key={n}
            className="h-16 bg-ui-border-l dark:bg-ui-dark-border rounded-lg animate-pulse"
          />
        ))}
      </div>
    );
  }

  if (status === 'error' || !data) {
    return (
      <p className="text-sm text-danger-dark dark:text-danger text-center py-4">
        {t('error')}
      </p>
    );
  }

  if (data.application_totals.length === 0) {
    return (
      <p className="text-sm text-text-secondary dark:text-text-dark-secondary text-center py-4">
        {t('widgets.applicationTotals.empty')}
      </p>
    );
  }

  return (
    <ul className="grid grid-cols-1 sm:grid-cols-2 gap-3" role="list">
      {data.application_totals.map((row) => (
        <li key={row.application_id}>
          <ApplicationTile
            href={hrefForApplication(row.application_id)}
            name={row.name}
            total={row.total}
            totalLabel={t('logsTotalLabel')}
            ariaLabel={t('openFilteredLogs', { app: row.name })}
          />
        </li>
      ))}
    </ul>
  );
}

export default ApplicationTotalsWidget;
