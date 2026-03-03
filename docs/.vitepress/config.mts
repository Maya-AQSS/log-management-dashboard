import { defineConfig } from 'vitepress';
import { withMermaid } from 'vitepress-plugin-mermaid';  // Necesario para diagramas C4/Mermaid

export default withMermaid(
  defineConfig({
    title: "Log Management Dashboard",
    description: "Panel de administración y gestión de logs multi-aplicación en tiempo real",
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
        { text: 'Épicas y Features', link: '/1_epics_and_features' },
        { text: 'Backlog', link: '/backlog/F-01.1_dashboard_cards_tipo' },
        { text: 'Auditoría', link: '/AUDIT_LOG' },
      ],

      sidebar: [
        {
          text: '📋 Proyecto',
          items: [
            { text: 'Descripción del Proyecto', link: '/0_descripcion_proyecto' },
            { text: 'Épicas y Features', link: '/1_epics_and_features' },
            { text: 'Arquitectura y Riesgos', link: '/2_architecture_risks' },
            { text: 'Diagramas C4', link: '/3_c4_diagrams' },
            { text: 'Registro de Auditoría', link: '/AUDIT_LOG' },
          ]
        },
        {
          text: '🗂️ Backlog — Epic 01: Dashboard',
          collapsed: false,
          items: [
            { text: 'F-01.1 Cards por tipo de error', link: '/backlog/F-01.1_dashboard_cards_tipo' },
            { text: 'F-01.2 Card "Todos los errores"', link: '/backlog/F-01.2_dashboard_card_todos' },
            { text: 'F-01.3 SSE — Tiempo real', link: '/backlog/F-01.3_sse_tiempo_real' },
            { text: 'F-01.4 Indicador de severidad', link: '/backlog/F-01.4_indicador_severidad' },
            { text: 'F-01.5 Card por aplicación', link: '/backlog/F-01.5_card_por_aplicacion' },
          ]
        },
        {
          text: '🗂️ Backlog — Epic 02: Listado y Filtros',
          collapsed: false,
          items: [
            { text: 'F-02.1 Tabla paginada', link: '/backlog/F-02.1_tabla_logs_paginada' },
            { text: 'F-02.2 Filtro por tipo de error', link: '/backlog/F-02.2_filtro_tipo_error' },
            { text: 'F-02.3 Filtro por aplicación', link: '/backlog/F-02.3_filtro_aplicacion' },
            { text: 'F-02.4 Filtro por fechas', link: '/backlog/F-02.4_filtro_fechas' },
            { text: 'F-02.5 Filtro texto libre', link: '/backlog/F-02.5_filtro_texto_libre' },
            { text: 'F-02.6 Columnas ordenables', link: '/backlog/F-02.6_columnas_ordenables' },
            { text: 'F-02.7 Persistencia filtros en URL', link: '/backlog/F-02.7_persistencia_filtros_url' },
          ]
        },
        {
          text: '🗂️ Backlog — Epic 03: Detalle y Archivado',
          collapsed: false,
          items: [
            { text: 'F-03.1 Vista detalle del log', link: '/backlog/F-03.1_detalle_log' },
            { text: 'F-03.2 Archivar en histórico', link: '/backlog/F-03.2_archivar_historico' },
            { text: 'F-03.3 Editor rich text', link: '/backlog/F-03.3_editor_rich_text' },
            { text: 'F-03.4 Log desaparece de vista activa', link: '/backlog/F-03.4_log_desaparece_vista_activa' },
            { text: 'F-03.5 Log persistente', link: '/backlog/F-03.5_log_persistente' },
          ]
        },
        {
          text: '🗂️ Backlog — Epic 04: Histórico',
          collapsed: false,
          items: [
            { text: 'F-04.1 Vista histórico (pestaña)', link: '/backlog/F-04.1_vista_historico_pestana' },
            { text: 'F-04.2 Ordenación histórico', link: '/backlog/F-04.2_ordenacion_historico' },
            { text: 'F-04.3 Filtros histórico', link: '/backlog/F-04.3_filtros_historico' },
            { text: 'F-04.4 Hilo de comentarios', link: '/backlog/F-04.4_hilo_comentarios' },
            { text: 'F-04.5 Rich text en histórico', link: '/backlog/F-04.5_rich_text_historico' },
            { text: 'F-04.6 Visualización hilo en detalle', link: '/backlog/F-04.6_visualizacion_hilo_detalle' },
          ]
        },
        {
          text: '🗂️ Backlog — Epic 05: Infraestructura',
          collapsed: false,
          items: [
            { text: 'F-05.1 Conexión PostgreSQL', link: '/backlog/F-05.1_conexion_postgresql' },
            { text: 'F-05.2 Endpoint SSE', link: '/backlog/F-05.2_endpoint_sse' },
            { text: 'F-05.3 Autenticación admin', link: '/backlog/F-05.3_autenticacion_admin' },
            { text: 'F-05.4 API REST CRUD', link: '/backlog/F-05.4_api_rest_crud' },
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