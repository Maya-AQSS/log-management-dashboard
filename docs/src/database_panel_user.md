# Usuario PostgreSQL `panel_user` y variables `DB_PANEL_*`

## Propósito

`panel_user` es un **rol de PostgreSQL** dedicado al panel de administración, con **permisos mínimos** acordes a las tablas que la aplicación debe tocar. El script de creación y permisos está en `database/sql/setup_panel_user.sql` y debe ejecutarse como superusuario (por ejemplo el usuario `postgres` o el que use Sail).

La conexión Laravel asociada se llama **`panel`** en `config/database.php` y lee:

- `DB_PANEL_USERNAME`
- `DB_PANEL_PASSWORD`

## ¿Se usa activamente en runtime?

Verificación con:

```bash
grep -r "panel_user\|DB_PANEL" app/ config/ routes/
```

**Resultado esperado:** en `app/` y `routes/` **no** hay referencias a `panel_user` ni a `DB_PANEL_*`. Solo **`config/database.php`** define la conexión `panel` con esas variables.

Los modelos Eloquent usan la **conexión por defecto** (`DB_CONNECTION`, `DB_USERNAME`, `DB_PASSWORD`).

**Uso actual de la conexión `panel`:**

- **Tests de integración** (`tests/Feature/DatabaseConnectionTest.php`): comprobaciones de que `panel_user` no puede ejecutar `DELETE` en `archived_logs` (diseño intencional según F-05.1).
- **Despliegue opcional:** si en producción se quiere que el panel use solo el usuario dedicado, habría que apuntar la aplicación a esa conexión (por ejemplo `DB_CONNECTION=panel` o `$connection = 'panel'` en los modelos) **y** rellenar `DB_PANEL_USERNAME` / `DB_PANEL_PASSWORD` en el entorno.

**Resumen:** las variables `DB_PANEL_*` y el script SQL están **preparados** para el modelo de menor privilegio; en desarrollo habitual la app sigue usando `DB_USERNAME` (por ejemplo `sail`).

## Verificación ampliada en el repositorio

```bash
grep -r "panel_user\|DB_PANEL" app/ config/ routes/ tests/ .env.example database/sql/
```

Se espera ver `DB_PANEL_*` en `config/database.php` y en `.env.example`, y `panel_user` en el SQL, tests y esta nota.
