<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Contador Livewire</h1>

    <div class="flex items-center gap-4">
        <button
            wire:click="increment"
            class="px-4 py-2 bg-indigo-600 text-white rounded"
        >
            Incrementar
        </button>

        <span class="text-lg">Valor: {{ $count }}</span>
    </div>
</div>