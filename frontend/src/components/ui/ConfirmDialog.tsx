import { useEffect } from 'react';

type ConfirmDialogProps = {
  open: boolean;
  title: string;
  message?: string;
  confirmLabel?: string;
  cancelLabel?: string;
  confirmTone?: 'primary' | 'danger';
  busy?: boolean;
  onConfirm: () => void;
  onCancel: () => void;
};

export function ConfirmDialog({
  open,
  title,
  message,
  confirmLabel = 'Confirmar',
  cancelLabel = 'Cancelar',
  confirmTone = 'primary',
  busy = false,
  onConfirm,
  onCancel,
}: ConfirmDialogProps) {
  useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && !busy) onCancel();
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [open, busy, onCancel]);

  if (!open) return null;

  const confirmClass =
    confirmTone === 'danger'
      ? 'bg-danger text-text-inverse border-danger hover:bg-danger-dark hover:border-danger-dark'
      : 'bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d';

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="confirm-dialog-title"
      onClick={() => {
        if (!busy) onCancel();
      }}
    >
      <div
        className="w-full max-w-md rounded-lg border border-ui-border bg-ui-card p-5 shadow-lg dark:border-ui-dark-border dark:bg-ui-dark-card"
        onClick={(e) => e.stopPropagation()}
      >
        <h2
          id="confirm-dialog-title"
          className="text-lg font-semibold text-text-primary dark:text-text-dark-primary"
        >
          {title}
        </h2>
        {message && (
          <p className="mt-2 text-sm text-text-secondary dark:text-text-dark-secondary">
            {message}
          </p>
        )}
        <div className="mt-5 flex justify-end gap-2">
          <button
            type="button"
            onClick={onCancel}
            disabled={busy}
            className="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer disabled:cursor-not-allowed disabled:opacity-60"
          >
            {cancelLabel}
          </button>
          <button
            type="button"
            onClick={onConfirm}
            disabled={busy}
            className={`inline-flex items-center border px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer shadow-sm disabled:cursor-not-allowed disabled:opacity-60 ${confirmClass}`}
          >
            {busy ? '…' : confirmLabel}
          </button>
        </div>
      </div>
    </div>
  );
}
