{{-- Toolbar fija de formato. Los botones usan data-toolbar-cmd + listener nativo (sin Alpine eval). --}}
<div x-ref="toolbarEl" class="rte-toolbar-bar">
    <button type="button" data-toolbar-cmd="bold"        title="Negrita"><strong>B</strong></button>
    <button type="button" data-toolbar-cmd="italic"      title="Cursiva"><em>I</em></button>
    <button type="button" data-toolbar-cmd="underline"   title="Subrayado"><u>U</u></button>
    <button type="button" data-toolbar-cmd="strike"      title="Tachado"><s>S</s></button>
    <button type="button" data-toolbar-cmd="code"        title="Código inline"><code>&lt;&gt;</code></button>
    <span class="rte-tb-sep"></span>
    <button type="button" data-toolbar-cmd="alignLeft"   title="Alinear izquierda">
        <svg width="13" height="11" viewBox="0 0 13 11" fill="none" aria-hidden="true"><rect x="0" y="0" width="13" height="1.5" rx="0.75" fill="currentColor"/><rect x="0" y="4" width="9" height="1.5" rx="0.75" fill="currentColor"/><rect x="0" y="8" width="11" height="1.5" rx="0.75" fill="currentColor"/></svg>
    </button>
    <button type="button" data-toolbar-cmd="alignCenter" title="Centrar">
        <svg width="13" height="11" viewBox="0 0 13 11" fill="none" aria-hidden="true"><rect x="0" y="0" width="13" height="1.5" rx="0.75" fill="currentColor"/><rect x="2" y="4" width="9" height="1.5" rx="0.75" fill="currentColor"/><rect x="1" y="8" width="11" height="1.5" rx="0.75" fill="currentColor"/></svg>
    </button>
    <button type="button" data-toolbar-cmd="alignRight"  title="Alinear derecha">
        <svg width="13" height="11" viewBox="0 0 13 11" fill="none" aria-hidden="true"><rect x="0" y="0" width="13" height="1.5" rx="0.75" fill="currentColor"/><rect x="4" y="4" width="9" height="1.5" rx="0.75" fill="currentColor"/><rect x="2" y="8" width="11" height="1.5" rx="0.75" fill="currentColor"/></svg>
    </button>
    <span class="rte-tb-sep"></span>
    <button type="button" data-toolbar-cmd="highlight"   title="Resaltar texto">
        <svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true"><rect x="1" y="9" width="11" height="2.5" rx="0.5" fill="#fde047"/><path d="M4 8L6.5 1L9 8H4Z" fill="currentColor"/></svg>
    </button>
    <button type="button" data-toolbar-cmd="link"        title="Enlace">🔗</button>
    <span class="rte-tb-sep"></span>
    <button type="button" data-toolbar-cmd="superscript" title="Superíndice">x<sup style="font-size:0.6em">2</sup></button>
    <button type="button" data-toolbar-cmd="subscript"   title="Subíndice">x<sub style="font-size:0.6em">2</sub></button>
    <span class="rte-tb-sep"></span>
    <button type="button" data-toolbar-cmd="image"       title="Insertar imagen">🖼️</button>
</div>

{{-- SlashMenu: se posiciona absolutamente dentro del contenedor del editor. --}}
<div
    x-ref="slashMenuEl"
    x-show="slash.show"
    x-on:click.outside="slash.show = false"
    x-on:mousedown.prevent
    class="rte-slash-menu"
    style="display:none"
>
    <template x-for="(item, index) in slash.items" :key="index">
        <button
            type="button"
            :class="index === slash.selectedIndex ? 'is-active' : ''"
            x-on:click.prevent="executeSlashItem(item)"
            x-on:mouseenter="slash.selectedIndex = index"
        >
            <span class="rte-slash-icon" x-text="item.icon"></span>
            <span x-text="item.label"></span>
        </button>
    </template>
</div>
