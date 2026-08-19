---
name: impecable
description: Use when creating or editing any UI in this project — Blade views, CSS, layouts, components, styling, or visual design. Enforces the "Impecable" design system (colors, typography, spacing, cards, forms, tables, buttons, modals) so every screen looks polished and consistent.
---

# Impecable — Sistema de Diseño

Convenciones visuales obligatorias para todas las vistas Blade del proyecto.

## Principios

- Todo el UI se construye con componentes de Bootstrap 5 (CDN) más estilos propios en `public/css/app.css`.
- Nunca escribir CSS inline `style=""` en las vistas; usar clases utilitarias o el archivo de estilos.
- Español para todo texto visible en pantalla.
- Responsivo: todo debe verse bien en móvil (tablas con `.table-responsive`).

## Paleta

| Variable           | Valor     | Uso                                  |
| ------------------ | --------- | ------------------------------------ |
| `--ip-primary`     | `#0d6efd` | Acciones principales, botones azules |
| `--ip-accent`      | `#6f42c1` | Acentos, badges, íconos secundarios  |
| `--ip-success`     | `#198754` | Estados positivos / "Activo"         |
| `--ip-danger`      | `#dc3545` | Eliminar, estados negativos          |
| `--ip-bg`          | `#f4f6fb` | Fondo de páginas                     |
| `--ip-surface`     | `#ffffff` | Tarjetas, paneles                    |
| `--ip-text`        | `#1f2937` | Texto principal                      |
| `--ip-muted`       | `#6c757d` | Texto secundario                     |

## Tipografía

- Familia: `Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif`.
- Encabezados de sección: `.ip-heading` (22px, peso 700, color texto).
- Títulos de tarjeta: `.ip-card-title` (16px, peso 600).
- Texto de apoyo: `.ip-muted`, tamaño 14px.

## Estructura de cada página CRUD

1. Cabecera con título (`.ip-heading`) y botón primario de acción a la derecha ("Nuevo ...").
2. Tarjeta con tabla (`.ip-card` > `.ip-table`) o formulario.
3. Filas de acción con íconos Bootstrap (ver "Acciones").

## Componentes

### Tarjeta

```html
<div class="ip-card">
  <div class="ip-card-header">
    <h5 class="ip-card-title mb-0">Título</h5>
  </div>
  <div class="ip-card-body p-0">
    <!-- tabla o contenido -->
  </div>
</div>
```

### Tabla

- Envolver siempre en `.table-responsive`.
- Clase `.table ip-table` con `thead` de fondo `--ip-primary` claro (`#eaf1ff`), cabeceras en peso 600.
- Columna de acciones al final, alineada a la derecha, con ancho mínimo 110px.

### Botones

| Clase              | Uso                    |
| ------------------ | ---------------------- |
| `btn ip-btn`       | Acción principal (azul)|
| `btn ip-btn-outline`| Acción secundaria      |
| `btn ip-btn-danger` | Eliminar               |
| `btn ip-btn-success`| Guardar / Aceptar      |

### Formularios

- Usar grid de Bootstrap (`row g-3`) dentro de `.ip-card-body`.
- Campos con clase `form-control`, etiquetas con `form-label` peso 500.
- Campos obligatorios con `*` en rojo junto a la etiqueta.
- Errores de validación debajo de cada campo: `@error('...') <div class="text-danger small">...`.
- Pie del formulario con `.ip-form-actions`: botón cancelar (outline) + botón guardar (success).

### Badges de estado

```html
<span class="badge ip-badge-active">Activo</span>
<span class="badge ip-badge-inactive">Inactivo</span>
```

### Acciones de fila

Usar iconos de Bootstrap Icons:

```html
<a href="{{ route('...edit', $item->id) }}" class="ip-action" title="Editar"><i class="bi bi-pencil-square"></i></a>
<form action="{{ route('...destroy', $item->id) }}" method="POST" class="d-inline ip-delete-form">
  @csrf @method('DELETE')
  <button type="submit" class="ip-action ip-action-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
</form>
```

- Confirmación de borrado: `onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')"`.
- `.ip-action`: botón 30x30px, borde redondeado, color texto `--ip-primary`, hover fondo `#eaf1ff`.
- `.ip-action-danger`: color `--ip-danger`, hover fondo `#fdecea`.

## Alertas flash

- Mensajes `session('success')`: alerta `alert-success` (verde, estilo `ip-alert`).
- Mensajes `session('error')`: alerta `alert-danger`.
- Colocar al inicio del `main`, antes del contenido.

## Layout maestro

- `resources/views/layouts/app.blade.php` define: sidebar fijo con enlaces a cada módulo (Escuelas, Sucursales, Empleados, Usuarios, Roles), topbar con nombre de usuario, y `<main class="ip-main">` con `@yield('content')`.
- Los enlaces activos usan `request()->routeIs('*')`.
- Ícono de cada sección en el sidebar con Bootstrap Icons.

## Carpetas

- CSS propio: `public/css/app.css` (variables + clases `ip-*`).
- Componentes reutilizables en `resources/views/components/`.

Regla final: **todo nuevo UI debe cumplir estas convenciones o no está terminado.**
