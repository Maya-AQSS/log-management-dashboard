<div class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-white p-2">
    <button type="button" x-on:click="execCommand('bold')" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">B</button>
    <button type="button" x-on:click="execCommand('italic')" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">I</button>
    <button type="button" x-on:click="execCommand('strike')" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">S</button>
    <button type="button" x-on:click="execCommand('h2')" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">H2</button>
    <button type="button" x-on:click="execCommand('bulletList')" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">• List</button>
    <button type="button" x-on:click="execCommand('orderedList')" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">1. List</button>
    <button type="button" x-on:click="execCommand('link')" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Link</button>
    <button type="button" x-on:click="triggerImagePicker()" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Image</button>
</div>