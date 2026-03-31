# Livewire: inyección de dependencias (convención del proyecto)

El proyecto usa **Livewire 4.x** (véase `composer.json`). La documentación oficial indica que las dependencias del contenedor de Laravel se resuelven **tipando parámetros en hooks de ciclo de vida** (`mount()`, `boot()`, etc.), no en el constructor, porque el componente se reconstruye entre peticiones.

Los servicios de aplicación se asignan en **`boot()`** a propiedades **privadas** y se reutilizan en `render()` y en el resto de métodos, evitando `app(Interface::class)` disperso.

**Referencia:** [Lifecycle hooks — Livewire 4](https://livewire.laravel.com/docs/4.x/lifecycle-hooks)
