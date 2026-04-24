import { severityBadgeClassFor } from './palette';

type SeverityBadgeProps = {
  severity?: string | null;
};

export function SeverityBadge({ severity }: SeverityBadgeProps) {
  const label = severity ? severity.toUpperCase() : '-';
  const classes = severityBadgeClassFor(severity);
  return <span className={classes}>{label}</span>;
}
