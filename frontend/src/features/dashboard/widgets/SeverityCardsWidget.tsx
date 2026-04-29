import { useEffect, useState } from'react';
import { useTranslation } from'react-i18next';
import { fetchDashboard } from'../../../api/dashboard';
import type { DashboardPayload } from'../../../types/dashboard';
import { SeverityCard } from'../../../components/dashboard';
import { severityLabel } from'../../../components/severity';
import { useLogStream } from'../../../hooks';

function hrefForSeverity(key: string): string {
 if (key ==='all') return'/logs';
 return`/logs?severity=${encodeURIComponent(key)}`;
}

/** Severity grid widget — preserves the legacy SeverityCard set. */
function SeverityCardsWidget() {
 const { t } = useTranslation('dashboard');
 const [data, setData] = useState<DashboardPayload | null>(null);
 const [status, setStatus] = useState<'loading' |'ready' |'error'>('loading');

 const { payload: streamPayload } = useLogStream({ intervalMs: 5000 });

 useEffect(() => {
 let cancelled = false;
 fetchDashboard()
 .then((d) => {
 if (!cancelled) {
 setData(d);
 setStatus('ready');
 }
 })
 .catch(() => {
 if (!cancelled) setStatus('error');
 });
 return () => {
 cancelled = true;
 };
 }, [streamPayload]);

 if (status ==='loading') {
 return (<div className="grid grid-cols-2 md:grid-cols-3 gap-3">
 {[1, 2, 3].map((n) => (<div
 key={n}
 className="h-20 bg-outline-variant rounded-lg animate-pulse"
 />
 ))}
 </div>
 );
 }

 if (status ==='error' || !data) {
 return (<p className="text-sm text-danger-dark dark:text-danger text-center py-4">
 {t('error')}
 </p>
 );
 }

 return (<div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
 {data.severity_cards.map((card) => (<SeverityCard
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
 );
}

export default SeverityCardsWidget;
