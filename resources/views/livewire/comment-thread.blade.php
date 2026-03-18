<div class="mt-4 space-y-4">
    <form wire:submit="addComment" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <label for="content" class="block text-sm font-medium text-slate-700">Nuevo comentario</label>
        <textarea
            id="content"
            wire:model="content"
            rows="4"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
        ></textarea>
        @error('content')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <button type="submit" class="rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]">
            Guardar comentario
        </button>
    </form>

    <div class="space-y-3">
        @forelse ($comments as $comment)
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $comment->user?->name ?? 'Usuario' }}</p>
                        <p class="text-xs text-slate-500">{{ $comment->created_at?->format('Y-m-d H:i') }}</p>
                    </div>

                    @if (auth()->id() === $comment->user_id)
                        <div class="flex gap-2">
                            <button type="button" wire:click="startEditing({{ $comment->id }})" class="text-sm font-medium text-[#5b3853] hover:underline">
                                Editar
                            </button>
                            <button type="button" wire:click="deleteComment({{ $comment->id }})" class="text-sm font-medium text-rose-600 hover:underline">
                                Eliminar
                            </button>
                        </div>
                    @endif
                </div>

                @if ($editingCommentId === $comment->id)
                    <form wire:submit="updateComment" class="mt-3 space-y-3">
                        <textarea
                            wire:model="editingContent"
                            rows="4"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                        ></textarea>
                        @error('editingContent')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror

                        <div class="flex gap-2">
                            <button type="submit" class="rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]">
                                Actualizar
                            </button>
                            <button type="button" wire:click="cancelEditing" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Cancelar
                            </button>
                        </div>
                    </form>
                @else
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $comment->content }}</p>
                @endif
            </article>
        @empty
            <p class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                Todavía no hay comentarios para este elemento.
            </p>
        @endforelse
    </div>
</div>