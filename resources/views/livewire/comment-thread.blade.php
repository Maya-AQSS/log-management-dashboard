<style>
    .rte-prosemirror {
        min-height: 9rem;
        outline: none;
        color: #0f172a;
    }

    .rte-prosemirror p.is-editor-empty:first-child::before {
        content: attr(data-placeholder);
        color: #94a3b8;
        float: left;
        height: 0;
        pointer-events: none;
    }

    .rte-prosemirror img {
        max-width: 100%;
        border-radius: 0.75rem;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .rte-prosemirror ul,
    .rte-prosemirror ol {
        padding-left: 1.25rem;
    }
    .rte-prosemirror h2 {
        font-size: 1.125rem;
        font-weight: 600;
        margin-top: 0.25rem;
        margin-bottom: 0.25rem;
    }
</style>

<div class="mt-4 space-y-4">
    <div
        wire:key="comment-editor-create"
        class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4"
        x-data="tiptapEditor({
            wireModel: 'content',
            initialValue: @js($content),
            messages: {
                imageTooLarge: @js(__('comments.editor.image_too_large')),
                imageInvalidType: @js(__('comments.editor.image_invalid_type')),
                commentTooLarge: @js(__('comments.editor.comment_too_large')),
                invalidLink: @js(__('comments.editor.invalid_link')),
            },
        })"
        x-init="init()"
        x-on:paste="handlePaste($event)"
        x-on:drop="handleDrop($event)"
        x-on:dragover.prevent
    >
        <label class="block text-sm font-medium text-slate-700">{{ __('comments.form.new_comment') }}</label>

        <div wire:ignore class="space-y-2">
            <x-rte-toolbar />

            <div class="rounded-xl border border-slate-300 bg-white p-3 shadow-sm">
                <div class="cursor-text">
                    <div
                        x-ref="editorEl"
                        data-placeholder="{{ __('comments.editor.placeholder') }}"
                    ></div>
                </div>
            </div>

            <input
                x-ref="imageInput"
                type="file"
                class="hidden"
                accept="image/png,image/jpeg,image/gif,image/webp"
                x-on:change="onImageInputChange($event)"
            >
        </div>

        <p class="text-xs text-slate-500">{{ __('comments.editor.hint') }}</p>

        @error('content')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <button
            type="button"
            x-on:click.prevent.stop="console.log('[TipTap] save-button:clicked'); submitToWire('addComment')"
            class="rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]"
        >
            {{ __('comments.buttons.save') }}
        </button>
    </div>

    <div class="space-y-3">
        @forelse ($comments as $comment)
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $comment->user?->name ?? __('comments.user.unknown') }}</p>
                        <p class="text-xs text-slate-500">{{ $comment->created_at?->format('Y-m-d H:i') }}</p>
                    </div>

                    @if (auth()->id() === $comment->user_id)
                        <div class="flex gap-2">
                            <button type="button" wire:click="startEditing({{ $comment->id }})" class="text-sm font-medium text-[#5b3853] hover:underline">
                                {{ __('comments.buttons.edit') }}
                            </button>
                            <button type="button" wire:click="deleteComment({{ $comment->id }})" class="text-sm font-medium text-rose-600 hover:underline">
                                {{ __('comments.buttons.delete') }}
                            </button>
                        </div>
                    @endif
                </div>

                @if ($editingCommentId === $comment->id)
                    <div
                        wire:key="comment-editor-edit-{{ $comment->id }}"
                        class="mt-3 space-y-3"
                        x-data="tiptapEditor({
                            wireModel: 'editingContent',
                            initialValue: @js($editingContent),
                            messages: {
                                imageTooLarge: @js(__('comments.editor.image_too_large')),
                                imageInvalidType: @js(__('comments.editor.image_invalid_type')),
                                commentTooLarge: @js(__('comments.editor.comment_too_large')),
                                invalidLink: @js(__('comments.editor.invalid_link')),
                            },
                        })"
                        x-init="init()"
                        x-on:paste="handlePaste($event)"
                        x-on:drop="handleDrop($event)"
                        x-on:dragover.prevent
                    >
                        <div wire:ignore class="space-y-2">
                            <x-rte-toolbar />

                            <div class="rounded-xl border border-slate-300 bg-white p-3 shadow-sm">
                                <div class="cursor-text">
                                    <div
                                        x-ref="editorEl"
                                        data-placeholder="{{ __('comments.editor.placeholder') }}"
                                    ></div>
                                </div>
                            </div>

                            <input
                                x-ref="imageInput"
                                type="file"
                                class="hidden"
                                accept="image/png,image/jpeg,image/gif,image/webp"
                                x-on:change="onImageInputChange($event)"
                            >
                        </div>

                        @error('editingContent')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror

                        <div class="flex gap-2">
                            <button
                                type="button"
                                x-on:click.prevent.stop="console.log('[TipTap] update-button:clicked'); submitToWire('updateComment')"
                                class="rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]"
                            >
                                {{ __('comments.buttons.update') }}
                            </button>
                            <button type="button" wire:click="cancelEditing" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                {{ __('comments.buttons.cancel') }}
                            </button>
                        </div>
                    </div>
                @else
                    <div class="prose prose-sm mt-3 max-w-none text-slate-700 [&_img]:rounded-lg [&_img]:max-w-full [&_img]:my-2">{!! $comment->content !!}</div>
                @endif
            </article>
        @empty
            <p class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                {{ __('comments.empty') }}
            </p>
        @endforelse
    </div>
</div>