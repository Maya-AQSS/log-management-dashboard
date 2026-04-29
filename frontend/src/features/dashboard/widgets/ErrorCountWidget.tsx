import { useEffect, useState } from'react';
import { Link } from'react-router-dom';
import { useTranslation } from'react-i18next';
import { fetchLogs } from'../../../api/logs';
import { useLogStream } from'../../../hooks';

/**
 * StatCard widget — count of CRITICAL+HIGH (treated as"errors") logs in the
 * last 24h. The backend exposes filtering by severity and date_from/date_to,
 * so we ask the API for a single-page count and read pagination.meta.total.
 */
function ErrorCountWidget() {
 const { t } = useTranslation('dashboard');
 const [count, setCount] = useState<number | null>(null);
 const [status, setStatus] = useState<'loading' |'ready' |'error'>('loading');

 const { payload: streamPayload } = useLogStream({ intervalMs: 5000 });

 useEffect(() => {
 let cancelled = false;
 const since = new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString();
 fetchLogs({
 severity: ['critical','high'],
 archived:'without',
 date_from: since,
 per_page: 1,
 })
 .then((res) => {
 if (!cancelled) {
 setCount(res.meta?.total ?? res.data?.length ?? 0);
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
 return (<div className="h-full flex items-center justify-center">
 <div className="h-12 w-24 bg-outline-variant rounded-lg animate-pulse" />
 </div>
 );
 }

 if (status ==='error') {
 return (<p className="text-sm text-error text-center py-4">
 {t('error')}
 </p>
 );
 }

 return (<Link
 to="/logs?severity=critical,high"
 className="block h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary rounded-lg"
 aria-label={t('widgets.errorCount.label')}
 >
 <div className="h-full flex flex-col items-center justify-center text-center px-2">
 <span
 className="text-5xl sm:text-6xl font-extrabold leading-none bg-clip-text text-transparent bg-gradient-to-br from-danger to-warning-dark"
 >
 {count ?? 0}
 </span>
 <span className="mt-2 text-xs uppercase tracking-wide font-medium text-on-surface-variant">
 {t('widgets.errorCount.label')}
 </span>
 </div>
 </Link>
 );
}

export default ErrorCountWidget;
