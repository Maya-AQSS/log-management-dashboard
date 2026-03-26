import { defineConfig } from 'vitepress';
import { withMermaid } from 'vitepress-plugin-mermaid';  // Necesario para diagramas C4/Mermaid

export default withMermaid(
  defineConfig({
    title: "Log Management Dashboard",
    description: "Panel de administración y gestión de logs multi-aplicación en tiempo real. Laravel 12 + Livewire 3.",
    lang: 'es-ES',

    // Para GitHub Pages
    base: '/log-management-dashboard/',
    srcDir: './src',

    ignoreDeadLinks: true,

    mermaid: {
      theme: 'dark'
    },

    themeConfig: {
      nav: [
        { text: 'Inicio', link: '/' },
        { text: 'Proyecto', link: '/0_descripcion_proyecto' },
        { text: 'Manual de Usuario', link: '/UserManual/' },
        { text: 'Épicas y Features', link: '/1_epics_and_features' },
        { text: 'Backlog', link: '/backlog/F-00.1_setup_laravel_livewire' },
        { text: 'Auditoría', link: '/AUDIT_LOG' },
      ],

      sidebar: [
        {
          text: '📋 Proyecto',
          items: [
            { text: 'Descripción del Proyecto', link: '/0_descripcion_proyecto' },
            { text: 'Manual de Usuario', link: '/UserManual/' },
            { text: 'Épicas y Features', link: '/1_epics_and_features' },
            { text: 'Arquitectura y Riesgos', link: '/2_architecture_risks' },
            { text: 'Diagramas C4', link: '/3_c4_diagrams' },
            { text: 'Registro de Auditoría', link: '/AUDIT_LOG' },
          ]
        },

        {
          text: '👤 Manual de Usuario',
          collapsed: false,
          items: [
            { text: 'Indice del manual', link: '/UserManual/' },
            { text: 'Dashboard', link: '/UserManual/01_dashboard' },
            { text: 'Listado de logs', link: '/UserManual/02_logs_listado' },
            { text: 'Detalle de log activo', link: '/UserManual/03_log_detalle' },
            { text: 'Listado de logs archivados', link: '/UserManual/04_archivados_listado' },
            { text: 'Detalle de log archivado', link: '/UserManual/05_archivado_detalle' },
            { text: 'Listado de error codes', link: '/UserManual/06_error_codes_listado' },
            { text: 'Alta de error code', link: '/UserManual/07_error_code_creacion' },
            { text: 'Detalle y edicion de error code', link: '/UserManual/08_error_code_detalle_edicion' },
          ]
        },

        // ─────────────────────────────────────────────────
        //  BACKLOG agrupado por CATEGORÍA UNIVERSAL
        // ─────────────────────────────────────────────────

        {
          text: '🏗️ Infrastructure',
          collapsed: false,
          items: [
            { text: 'F-00.1 Setup Laravel + Livewire', link: '/backlog/F-00.1_setup_laravel_livewire' },
          ]
        },

        {
          text: '🗄️ Data',
          collapsed: false,
          items: [
            { text: 'F-00.2 Migraciones y Modelos', link: '/backlog/F-00.2_migraciones_modelos' },
            { text: 'F-03.5 Log persistente (sin TTL)', link: '/backlog/F-03.5_log_persistente' },
            { text: 'F-05.1 Conexión PostgreSQL', link: '/backlog/F-05.1_conexion_postgresql' },
          ]
        },

        {
          text: '🔌 Integration',
          collapsed: false,
          items: [
            { text: 'F-01.3 SSE — Tiempo real', link: '/backlog/F-01.3_sse_tiempo_real' },
            { text: 'F-05.2 Endpoint SSE', link: '/backlog/F-05.2_endpoint_sse' },
          ]
        },

        {
          text: '🔒 Security',
          collapsed: false,
          items: [
            { text: 'F-05.3 Autenticación externa', link: '/backlog/F-05.3_autenticacion_admin' },
            { text: 'F-05.5 Tabla Users y Mock de Sesión', link: '/backlog/F-05.5_usuarios_mock_session' },
          ]
        },

        {
          text: '🛠️ DX / Tooling',
          collapsed: false,
          items: [
            { text: 'F-00.4 Internacionalización (i18n)', link: '/backlog/F-00.4_internacionalizacion' },
          ]
        },

        {
          text: '🖥️ UI / Presentation',
          collapsed: false,
          items: [
            // EPIC-00 Setup
            { text: 'F-00.3 Layout y Navegación compartida', link: '/backlog/F-00.3_layout_navegacion' },
            // EPIC-01 Dashboard
            { text: 'F-01.1 Cards por tipo de error', link: '/backlog/F-01.1_dashboard_cards_tipo' },
            { text: 'F-01.2 Card "Todos los errores"', link: '/backlog/F-01.2_dashboard_card_todos' },
            { text: 'F-01.4 Indicador de severidad', link: '/backlog/F-01.4_indicador_severidad' },
            { text: 'F-01.5 Card por aplicación', link: '/backlog/F-01.5_card_por_aplicacion' },
            // EPIC-02 Logs
            { text: 'F-02.1 Tabla paginada de logs', link: '/backlog/F-02.1_tabla_logs_paginada' },
            { text: 'F-02.2 Filtro por tipo de error', link: '/backlog/F-02.2_filtro_tipo_error' },
            { text: 'F-02.3 Filtro por aplicación', link: '/backlog/F-02.3_filtro_aplicacion' },
            { text: 'F-02.4 Filtro por fechas', link: '/backlog/F-02.4_filtro_fechas' },
            { text: 'F-02.5 Filtro texto libre', link: '/backlog/F-02.5_filtro_texto_libre' },
            { text: 'F-02.6 Columnas ordenables', link: '/backlog/F-02.6_columnas_ordenables' },
            { text: 'F-02.7 Persistencia filtros en URL', link: '/backlog/F-02.7_persistencia_filtros_url' },
            // EPIC-03 Detalle/Archivado
            { text: 'F-03.1 Vista detalle del log', link: '/backlog/F-03.1_detalle_log' },
            { text: 'F-03.3 Editor rich text (TipTap)', link: '/backlog/F-03.3_editor_rich_text' },
            // EPIC-04 Histórico
            { text: 'F-04.1 Vista histórico (pestaña)', link: '/backlog/F-04.1_vista_historico_pestana' },
            { text: 'F-04.2 Ordenación histórico', link: '/backlog/F-04.2_ordenacion_historico' },
            { text: 'F-04.3 Filtros histórico', link: '/backlog/F-04.3_filtros_historico' },
            { text: 'F-04.5 Rich text en comentarios', link: '/backlog/F-04.5_rich_text_historico' },
            { text: 'F-04.6 Visualización hilo en detalle', link: '/backlog/F-04.6_visualizacion_hilo_detalle' },
            // EPIC-06 Error Codes
            { text: 'F-06.1 Listado catálogo error codes', link: '/backlog/F-06.1_error_codes_index' },
          ]
        },

        {
          text: '⚙️ Logic / Business',
          collapsed: false,
          items: [
            // EPIC-02 Logs
            { text: 'F-02.8 Estado log (solucionado)', link: '/backlog/F-02.8_estado_log_solucionado' },
            // EPIC-03 Detalle/Archivado
            { text: 'F-03.2 Archivar en histórico', link: '/backlog/F-03.2_archivar_historico' },
            { text: 'F-03.4 Log desaparece de vista activa', link: '/backlog/F-03.4_log_desaparece_vista_activa' },
            // EPIC-04 Histórico
            { text: 'F-04.4 Hilo de comentarios', link: '/backlog/F-04.4_hilo_comentarios' },
            { text: 'F-04.7 URL Tutorial en histórico', link: '/backlog/F-04.7_url_tutorial' },
            { text: 'F-04.8 Descripción editable en histórico', link: '/backlog/F-04.8_descripcion_editable_historico' },
            { text: 'F-04.9 Borrar entrada de histórico', link: '/backlog/F-04.9_borrar_historico' },
            // EPIC-05 Backend
            { text: 'F-05.4 Rutas web y Livewire Actions', link: '/backlog/F-05.4_api_rest_crud' },
            // EPIC-06 Error Codes
            { text: 'F-06.2 Detalle / CRUD error code', link: '/backlog/F-06.2_error_codes_detalle_crud' },
            { text: 'F-06.3 Comentarios en error codes', link: '/backlog/F-06.3_error_codes_comentarios' },
          ]
        },
      ],

      socialLinks: [
        { icon: 'github', link: 'https://github.com/Maya-AQSS/log-management-dashboard' }
      ],

      footer: {
        message: 'Log Management Dashboard — Documentación del Proyecto',
        copyright: 'Copyright © 2026'
      },

      search: {
        provider: 'local'
      },

      editLink: {
        pattern: 'https://github.com/Maya-AQSS/log-management-dashboard/edit/main/docs/:path',
        text: 'Editar esta página en GitHub'
      },

      lastUpdated: {
        text: 'Última actualización',
        formatOptions: {
          dateStyle: 'short',
          timeStyle: 'short'
        }
      }
    }
  })
)
