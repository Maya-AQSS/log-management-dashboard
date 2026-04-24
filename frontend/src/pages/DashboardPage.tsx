import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { fetchDashboard } from '../api/dashboard';
import type { DashboardPayload } from '../types/dashboard';
import { ApplicationTile, SeverityCard } from '../components/dashboard';
import { severityLabel } from '../components/severity';
import { useLogStream } from '../hooks';

type State =
  | { status: 'loading' }
  | { status: 'ready'; data: DashboardPayload }
  | { status: 'error'; error: string };

function hrefForSeverity(key: string): string {
  if (key === 'all') return '/logs';
  return `/logs?severity=${encodeURIComponent(key)}`;
}

function hrefForApplication(id: number): string {
  return `/logs?application_id=${id}`;
}

export function DashboardPage() {
  const { t } = useTranslation('dashboard');
  const [state, setState] = useState<State>({ status: 'loading' });
  const [refreshNonce, setRefreshNonce] = useState(0);
  const lastStreamIdRef = useRef<number | null>(null);

  useEffect(() => {
    let cancelled = false;
    fetchDashboard()
      .then((data) => {
        if (!cancelled) setState({ status: 'ready', data });
      })
      .catch((error: unknown) => {
        if (!cancelled) {
          const message = error instanceof Error ? error.message : String(error);
          setState({ status: 'error', error: message });
        }
      });
    return () => {
      cancelled = true;
    };
  }, [refreshNonce]);

  const { payload: streamPayload } = useLogStream({ intervalMs: 5000 });

  useEffect(() => {
    if (!streamPayload || streamPayload.length === 0) return;
    const maxId = streamPayload.reduce((acc, item) => (item.id > acc ? item.id : acc), 0);
    if (lastStreamIdRef.current === null) {
      lastStreamIdRef.current = maxId;
      return;
    }
    if (maxId > lastStreamIdRef.current) {
      lastStreamIdRef.current = maxId;
      setRefreshNonce((n) => n + 1);
    }
  }, [streamPayload]);

  if (state.status === 'loading') {
    return (
      <div className="p-6 text-text-muted dark:text-text-dark-muted">
        {t('loading')}
      </div>
    );
  }

  if (state.status === 'error') {
    return (
      <div className="p-6 text-danger-dark">
        {t('error')}: {state.error}
      </div>
    );
  }

  const { severity_cards: cards, application_totals: apps } = state.data;

  return (
    <div className="px-4 py-2">
      <div className="py-3 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
        {cards.map((card) => (
          <SeverityCard
            key={card.key}
            title={severityLabel(card.key)}
            href={hrefForSeverity(card.key)}
            severityKey={card.key}
            unresolvedCount={card.unresolvedCount}
            resolvedCount={card.resolvedCount}
            unresolvedLabel={t('unresolvedLabel')}
            resolvedLabel={t('resolvedLabel')}
          />
        ))}
      </div>

      {apps.length > 0 && (
        <section className="py-3 w-full" aria-labelledby="dashboard-by-app-heading">
          <div className="rounded-lg border border-ui-border bg-ui-card p-6 shadow-card dark:border-ui-dark-border dark:bg-ui-dark-card">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
              <div className="min-w-0 border-l-4 border-odoo-purple pl-4">
                <h2
                  id="dashboard-by-app-heading"
                  className="text-xl font-bold tracking-tight text-text-primary dark:text-text-dark-primary sm:text-2xl"
                >
                  {t('byApplication')}
                </h2>
                <p className="mt-1.5 max-w-xl text-sm leading-relaxed text-text-secondary dark:text-text-dark-secondary">
                  {t('byApplicationHint')}
                </p>
              </div>
            </div>

            <ul className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2" role="list">
              {apps.map((row) => (
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
          </div>
        </section>
      )}
    </div>
  );
}
