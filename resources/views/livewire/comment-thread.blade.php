<div>
    <style>
        /* ── Estilos compartidos: editor y visualización de comentarios ── */
        .rte-content a {
            color: #2563eb;
            text-decoration: underline;
            cursor: pointer;
        }

        .rte-content a:hover {
            color: #1d4ed8;
        }

        .dark .rte-content a {
            color: #93c5fd;
        }

        .dark .rte-content a:hover {
            color: #bfdbfe;
        }

        .rte-content img {
            max-width: 100%;
            border-radius: 0.75rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .rte-content ul:not([data-type="taskList"]) {
            list-style: disc;
            padding-left: 1.5rem;
        }

        .rte-content ol {
            list-style: decimal;
            padding-left: 1.5rem;
        }

        .rte-content h1 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-top: 1rem;
            margin-bottom: 0.35rem;
            color: #0f172a;
        }

        .dark .rte-content h1 {
            color: #f1f5f9;
        }

        .rte-content h2 {
            font-size: 1.375rem;
            font-weight: 700;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.2rem;
        }

        .dark .rte-content h2 {
            color: #f1f5f9;
            border-bottom-color: #334155;
        }

        .rte-content h3 {
            font-size: 1.075rem;
            font-weight: 600;
            margin-top: 0.5rem;
            margin-bottom: 0.15rem;
            color: #475569;
        }

        .dark .rte-content h3 {
            color: #cbd5e1;
        }

        .rte-content blockquote {
            border-left: 4px solid #cbd5e1;
            margin: 0.5rem 0;
            padding: 0.4rem 0.75rem;
            color: #475569;
            font-style: italic;
            background: #f8fafc;
            border-radius: 0 0.25rem 0.25rem 0;
        }

        .dark .rte-content blockquote {
            border-left-color: #475569;
            color: #cbd5e1;
            background: #1e293b;
        }

        .rte-content hr {
            border: none;
            border-top: 2px solid #e2e8f0;
            margin: 1rem 0;
        }

        .dark .rte-content hr {
            border-top-color: #334155;
        }

        .rte-content pre {
            background: #1e293b;
            color: #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            overflow-x: auto;
            margin: 0.5rem 0;
        }

        .rte-content code:not(pre code) {
            background: #f1f5f9;
            border-radius: 0.25rem;
            padding: 0.1em 0.35em;
            font-size: 0.85em;
        }

        .dark .rte-content code:not(pre code) {
            background: #334155;
            color: #e2e8f0;
        }

        .rte-content ul[data-type="taskList"] {
            list-style: none;
            padding-left: 0;
        }

        .rte-content ul[data-type="taskList"] li {
            display: flex;
            align-items: flex-start;
            gap: 0.4rem;
        }

        /* Checkbox en el editor: Tiptap renderiza <label><input> en vivo */
        .rte-prosemirror ul[data-type="taskList"] li > label {
            margin-top: 0.15rem;
        }

        /* Checkbox en la vista de sólo lectura (label/input son eliminados por HTMLPurifier):
           se muestra mediante ::before usando el atributo data-checked del <li> */
        .rte-content:not(.rte-prosemirror) ul[data-type="taskList"] li[data-type="taskItem"]::before {
            content: '\2610'; /* ☐ cuadrado vacío */
            flex-shrink: 0;
            font-size: 1rem;
            margin-top: 0.05rem;
        }

        .rte-content:not(.rte-prosemirror) ul[data-type="taskList"] li[data-type="taskItem"][data-checked="true"]::before {
            content: '\2611'; /* ☑ cuadrado marcado */
            color: var(--color-odoo-purple);
        }

        /* Checkboxes no interactivos en la vista de sólo lectura del comentario */
        .rte-content:not(.rte-prosemirror) ul[data-type="taskList"] input {
            pointer-events: none;
        }

        /* ── Editor Tiptap ── */
        .rte-prosemirror {
            min-height: 9rem;
            outline: none;
            color: #0f172a;
        }

        .dark .rte-prosemirror {
            color: #e2e8f0;
            caret-color: #e2e8f0;
        }

        .rte-prosemirror p.is-editor-empty:first-child::before {
            content: attr(data-placeholder);
            color: #94a3b8;
            float: left;
            height: 0;
            pointer-events: none;
        }

        .dark .rte-prosemirror p.is-editor-empty:first-child::before {
            color: #64748b;
        }

        /* Toolbar fija */
        .rte-toolbar-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 2px;
            padding: 6px 8px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .dark .rte-toolbar-bar {
            background: #1e293b;
            border-bottom-color: #334155;
        }

        .rte-toolbar-bar button {
            background: transparent;
            border: none;
            color: #334155;
            border-radius: 4px;
            padding: 4px 7px;
            font-size: 0.8rem;
            cursor: pointer;
            line-height: 1.4;
            display: inline-flex;
            align-items: center;
        }

        .dark .rte-toolbar-bar button {
            color: #cbd5e1;
        }

        .rte-toolbar-bar button:hover {
            background: #e2e8f0;
        }

        .dark .rte-toolbar-bar button:hover {
            background: #334155;
        }

        .rte-toolbar-bar button.is-active {
            background: var(--color-odoo-purple);
            color: #fff;
        }

        .rte-tb-sep {
            width: 1px;
            background: #e2e8f0;
            align-self: stretch;
            margin: 4px 3px;
        }

        .dark .rte-tb-sep {
            background: #334155;
        }

        /* Caja del editor: toolbar arriba + contenido abajo */
        .rte-editor-box {
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .dark .rte-editor-box {
            border-color: #475569;
            background: #0f172a;
            box-shadow: none;
        }

        /* Nuevos formatos inline */
        .rte-content u {
            text-decoration: underline;
        }

        .rte-content mark {
            background-color: #fef08a;
            color: inherit;
            border-radius: 0.15em;
            padding: 0 0.1em;
        }

        .rte-content sup {
            font-size: 0.75em;
            vertical-align: super;
        }

        .rte-content sub {
            font-size: 0.75em;
            vertical-align: sub;
        }

        /* Slash menu */
        .rte-slash-menu {
            position: absolute;
            z-index: 50;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            min-width: 200px;
            padding: 4px;
            display: flex;
            flex-direction: column;
        }

        .dark .rte-slash-menu {
            background: #0f172a;
            border-color: #334155;
            box-shadow: 0 8px 24px rgba(0,0,0,0.35);
        }

        .rte-slash-menu button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            text-align: left;
            background: transparent;
            border: none;
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 0.85rem;
            color: #0f172a;
            cursor: pointer;
        }

        .dark .rte-slash-menu button {
            color: #e2e8f0;
        }

        .rte-slash-menu button:hover,
        .rte-slash-menu button.is-active {
            background: #f1f5f9;
        }

        .dark .rte-slash-menu button:hover,
        .dark .rte-slash-menu button.is-active {
            background: #1e293b;
        }

        .rte-slash-icon {
            width: 1.5rem;
            display: inline-block;
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }

        .dark .rte-slash-icon {
            color: #94a3b8;
        }
    </style>

    <div class="mt-4 space-y-4">
        <div
                wire:key="comment-editor-create-{{ $newCommentKey }}"
            class="space-y-3 rounded-xl border border-ui-border bg-ui-card p-4 shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card"
                x-data="tiptapEditor({
                    initialValue: '',
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
        <label class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('comments.form.new_comment') }}</label>
        
                <div wire:ignore style="position: relative">
                    <div class="rte-editor-box">
                        <x-rte-toolbar />
                        <div class="p-3 cursor-text bg-ui-body dark:bg-ui-dark-bg rounded-b-xl">
                            <div
                                x-ref="editorEl"
                                data-placeholder="{{ __('comments.editor.placeholder') }}"
                            ></div>
                        </div>
                    </div>
                    <input
                        x-ref="imageInput"
                        type="file"
                        name="image-upload-create"
                        class="hidden"
                        accept="image/png,image/jpeg,image/gif,image/webp"
                        x-on:change="onImageInputChange($event)"
                    >
                </div>
        
                <p class="text-xs text-text-muted dark:text-text-dark-muted">{{ __('comments.editor.hint') }}</p>

                @error('content')
                    <p class="mt-1 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900 dark:border-red-500 dark:bg-red-950 dark:!text-red-50" role="alert">{{ $message }}</p>
                @enderror
        
                <button
                    type="button"
                    x-on:click.prevent="submitToWire('addComment')"
                    class="bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
        >
                    {{ __('comments.buttons.save') }}
        </button>
        </div>
        </div>

    <div class="mt-4 space-y-3">
        @forelse ($comments as $comment)
            <article class="rounded-xl border border-ui-border bg-ui-card p-4 shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-text-primary dark:text-text-dark-primary">{{ $comment->user?->name ?? __('comments.user.unknown') }}</p>
                        <p class="text-xs text-text-secondary dark:text-text-dark-secondary">{{ $comment->created_at?->format('Y-m-d H:i') }}</p>
                    </div>

                    @if (auth()->id() === $comment->user_id)
                        <div class="flex gap-2">
                            <button type="button" wire:click="startEditing({{ $comment->id }})" class="text-sm font-medium text-odoo-purple hover:underline">
                                {{ __('comments.buttons.edit') }}
                            </button>
                            <button type="button" wire:click="deleteComment({{ $comment->id }})" class="text-sm font-medium text-danger hover:underline">
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
                        <div wire:ignore style="position: relative">
                            <div class="rte-editor-box">
                                <x-rte-toolbar />
                                <div class="p-3 cursor-text bg-ui-body dark:bg-ui-dark-bg rounded-b-xl">
                                    <div
                                        x-ref="editorEl"
                                        data-placeholder="{{ __('comments.editor.placeholder') }}"
                                    ></div>
                                </div>
                            </div>
                            <input
                                x-ref="imageInput"
                                type="file"
                                name="image-upload-edit"
                                class="hidden"
                                accept="image/png,image/jpeg,image/gif,image/webp"
                                x-on:change="onImageInputChange($event)"
                            >
                        </div>

                        @error('editingContent')
                            <p class="mt-1 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900 dark:border-red-500 dark:bg-red-950 dark:!text-red-50" role="alert">{{ $message }}</p>
                        @enderror

                        <div class="flex gap-2">
                            <button
                                type="button"
                                x-on:click.prevent="submitToWire('updateComment', {{ $comment->id }})"
                                class="bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
                            >
                                {{ __('comments.buttons.update') }}
                            </button>
                            <button
                                type="button"
                                wire:click="cancelEditing"
                                class="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer"
                            >
                                {{ __('comments.buttons.cancel') }}
                            </button>
 
                        </div>
                    </div>
                @else
                    <div class="rte-content mt-3 text-sm text-text-primary dark:text-text-dark-primary">{!! $comment->content !!}</div>
                @endif
            </article>
        @empty
            <p class="rounded-xl border border-dashed border-ui-border bg-ui-card px-4 py-6 text-center text-sm text-text-secondary dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-secondary">
                {{ __('comments.empty') }}
            </p>
        @endforelse
    </div>
</div>
