<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

/**
 * Modal de confirmación reutilizable solo para: borrar error code (DELETE), borrar histórico (DELETE),
 * archivar (POST), marcar resuelto (PATCH).
 */
class ConfirmActionModal extends Component
{
    public string $resolvedTitle;

    public string $resolvedMessage;

    public string $resolvedConfirmLabel;

    public string $resolvedCancelLabel;

    public bool $usesMethodSpoofing;

    public ?string $spoofedHttpMethod;

    /** @var 'danger'|'primary' */
    public string $confirmVariant;

    public function __construct(
        public string $action,
        public string $openVar,
        public string $intent,
        public ?string $title = null,
        public ?string $message = null,
        public ?string $confirmLabel = null,
        public ?string $cancelLabel = null,
    ) {
        if (! in_array($intent, ['delete', 'delete_archived', 'archive', 'resolve'], true)) {
            throw new InvalidArgumentException('ConfirmActionModal intent must be delete, delete_archived, archive, or resolve.');
        }

        $this->resolvedTitle = $title ?? match ($intent) {
            'delete' => __('error_codes.buttons.delete'),
            'delete_archived' => __('archived_logs.buttons.delete'),
            'archive' => __('logs.buttons.archive'),
            'resolve' => __('logs.buttons.solved'),
        };

        $this->resolvedMessage = $message ?? match ($intent) {
            'delete' => __('error_codes.messages.delete_confirm'),
            'delete_archived' => __('archived_logs.confirm_delete'),
            'archive' => __('logs.confirm_archive'),
            'resolve' => __('logs.confirm_resolve'),
        };

        $this->resolvedConfirmLabel = $confirmLabel ?? match ($intent) {
            'delete' => __('error_codes.buttons.delete'),
            'delete_archived' => __('archived_logs.buttons.delete'),
            'archive' => __('logs.buttons.archive'),
            'resolve' => __('logs.buttons.solved'),
        };

        $this->resolvedCancelLabel = $cancelLabel ?? match ($intent) {
            'delete' => __('error_codes.buttons.cancel'),
            'delete_archived' => __('archived_logs.buttons.cancel'),
            default => __('logs.buttons.cancel'),
        };

        $this->usesMethodSpoofing = in_array($intent, ['delete', 'delete_archived', 'resolve'], true);
        $this->spoofedHttpMethod = match ($intent) {
            'delete', 'delete_archived' => 'DELETE',
            'resolve' => 'PATCH',
            default => null,
        };

        $this->confirmVariant = in_array($intent, ['delete', 'delete_archived'], true) ? 'danger' : 'primary';
    }

    public function render(): View|Closure|string
    {
        return view('components.confirm-action-modal');
    }
}
