---
layout: home

hero:
  name: "Log Management Dashboard"
  text: "Panel de gestión de logs multi-aplicación"
  tagline: Visualiza, filtra y archiva logs de error en tiempo real. Backend Laravel 12 + Frontend React 19 conectados a PostgreSQL vía SSE.
  actions:
    - theme: brand
      text: Descripción del Proyecto
      link: /0_descripcion_proyecto
    - theme: alt
      text: Épicas y Features
      link: /1_epics_and_features
    - theme: alt
      text: Ver en GitHub
      link: https://github.com/Maya-AQSS/log-management-dashboard

features:
  - icon: ⚡
    title: Dashboard en Tiempo Real
    details: Cards de resumen por tipo de error (Critical, High, Medium, Low) actualizadas automáticamente mediante Server-Sent Events (SSE) sin necesidad de recargar la página.

  - icon: 🔍
    title: Filtrado y Búsqueda Avanzada
    details: Tabla paginada de logs con filtros por tipo, aplicación origen, rango de fechas y texto libre. Columnas ordenables y persistencia de filtros en URL.

  - icon: 📦
    title: Archivado con Rich Text
    details: Archiva logs en el histórico con un comentario inicial obligatorio. Editor TipTap 2 con soporte de imágenes, tablas y formato enriquecido.

  - icon: 📚
    title: Histórico de Logs
    details: Vista dedicada para logs archivados con hilo de comentarios múltiples, ordenación por tipo y fecha, y los mismos filtros que la vista activa.

  - icon: 🏗️
    title: Stack Moderno
    details: Monorepo con Laravel 12 (API REST + SSE via StreamedResponse + Sanctum) y React 19 + Vite como SPA. PostgreSQL como base de datos compartida con n8n.

  - icon: 🔒
    title: Seguro por Diseño
    details: Análisis STRIDE completo. Autenticación con Laravel Sanctum, queries parametrizadas, credenciales en .env y usuario de DB con permisos mínimos.
---