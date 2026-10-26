# Guía de estilos — Patitas Felices

Esta guía resume el sistema visual de referencia del portal de gestión veterinaria a partir de los archivos incluidos en [`stitch_patitas_felices_management_portal.zip`](./stitch_patitas_felices_management_portal.zip): `DESIGN.md` (tokens y criterios de diseño), `code.html` (prototipo Tailwind) y `screen.png` (referencia visual).

> **Alcance:** describe el diseño de referencia; no afirma que estos estilos ya estén aplicados a todas las vistas de Laravel. La hoja actual de la aplicación conserva la tipografía predeterminada de Laravel, `Instrument Sans`, en [`resources/css/app.css`](../resources/css/app.css).

## Identidad visual

El estilo busca combinar la precisión de una herramienta clínica con una experiencia cercana para tutores de mascotas: autoridad y claridad, suavizadas por superficies lavanda/periwinkle, geometría amable y jerarquía visual fácil de recorrer. La interfaz debe permitir consultar rápidamente pacientes, citas, inventario, vacunas y alertas sin perder legibilidad.

## Fuentes de referencia y discrepancias

`DESIGN.md` y el tema Tailwind embebido en `code.html` no coinciden en todos sus valores. El prototipo nombra y consume los colores del bloque de tokens YAML; por eso, para implementar la interfaz se recomienda usar esos tokens como referencia técnica y no mezclar valores de la prosa sin resolver primero la discrepancia.

| Tema | Valores del bloque de tokens / prototipo | Valores alternativos de la prosa |
|---|---|---|
| Primario | `primary: #112175`; `primary-container: #2c3a8c` | Llama primario a `#2c3a8c` |
| Secundario | `secondary: #585798` | `#6766a8` |
| Terciario | `tertiary: #2a2557` | `#9f9ad4` |
| Fondo y superficies | `surface: #fbf8ff`; contenedores `#f4f2ff`, `#ececff`, `#e5e6ff`, `#dee0ff` | Canvas blanco, superficie `#f4f3f9`, acento `#e6e4f1` |
| Bordes redondeados | El YAML define `sm` 4 px, `DEFAULT` 8 px, `md` 12 px, `lg` 16 px y `xl` 24 px | El tema de `code.html` redefine `DEFAULT` a 4 px, `lg` a 8 px y `xl` a 12 px |
| Estados | El token `error` es `#ba1a1a` | La prosa también propone success `#238652`, warning `#b87309` y danger `#c7283c`, que no están en el bloque de tokens |

Hasta que se consolide una única fuente, conserva los nombres semánticos (`primary`, `surface`, `error`, etc.) y evita codificar directamente los colores alternativos de la prosa. No asumas que una clase como `rounded-xl` equivale al radio de 24 px sin verificar la configuración activa.

## Paleta de tokens

Los siguientes valores son los tokens de color definidos en el YAML de `DESIGN.md` y replicados en el tema Tailwind de `code.html`.

| Token | Valor | Uso de referencia |
|---|---|---|
| `primary` | `#112175` | Acciones y elementos de marca principales |
| `on-primary` | `#ffffff` | Contenido sobre `primary` |
| `primary-container` | `#2c3a8c` | Contenedor/acento primario; el prototipo lo usa en acciones |
| `on-primary-container` | `#9ca9ff` | Contenido sobre el contenedor primario |
| `secondary` | `#585798` | Acciones y elementos secundarios |
| `on-secondary` | `#ffffff` | Contenido sobre `secondary` |
| `secondary-container` | `#b9b7ff` | Contenedores secundarios |
| `on-secondary-container` | `#474685` | Contenido sobre el contenedor secundario |
| `tertiary` | `#2a2557` | Acento terciario |
| `tertiary-container` | `#403c6f` | Contenedor terciario |
| `surface` / `background` | `#fbf8ff` | Fondo general |
| `surface-container-lowest` | `#ffffff` | Superficie blanca de tarjetas y paneles |
| `surface-container-low` | `#f4f2ff` | Superficie secundaria suave |
| `surface-container` | `#ececff` | Superficie de chips y elementos anidados |
| `surface-container-high` | `#e5e6ff` | Nivel de superficie elevado |
| `surface-container-highest` / `surface-variant` | `#dee0ff` | Superficie de mayor contraste |
| `surface-dim` | `#d3d8ff` | Superficie atenuada |
| `on-surface` / `on-background` | `#111840` | Texto principal |
| `on-surface-variant` | `#454651` | Texto secundario |
| `outline` | `#767683` | Contornos |
| `outline-variant` | `#c6c5d3` | Contornos de bajo contraste |
| `error` | `#ba1a1a` | Error |
| `error-container` | `#ffdad6` | Fondo de estado de error |
| `on-error` | `#ffffff` | Contenido sobre `error` |
| `on-error-container` | `#93000a` | Contenido sobre el contenedor de error |

El tema también define variantes `inverse-*`, `*-fixed`, `*-fixed-dim`, `surface-bright` y `surface-tint`. Consulta el bloque YAML de `DESIGN.md` antes de crear nuevas variantes para mantener sus pares de contraste.

### Semántica de estados indicada en la prosa

La prosa describe estos colores para estados veterinarios, pero todavía no están formalizados como tokens del YAML/prototipo:

- Correcto / vacunado: texto `#238652` y fondo `#e8f6ee`.
- Pendiente / advertencia: texto `#b87309` y fondo `#fdf6e7`.
- Crítico / peligro: texto `#c7283c` y fondo `#faebee`.

Antes de emplearlos como colores de producto, incorpóralos de forma coherente al tema y define sus colores de contenido; no sustituyas automáticamente el token `error`.

## Tipografía

La familia de referencia es **Plus Jakarta Sans**. El prototipo la carga desde Google Fonts y usa pesos 400, 600 y 700. Mantén una jerarquía explícita para que nombres de pacientes, valores clínicos, etiquetas y datos tabulares se puedan escanear rápidamente.

| Estilo | Tamano / interlineado | Peso |
|---|---:|---:|
| `display-lg` | 48 / 56 px | 700 |
| `display-lg-mobile` | 34 / 42 px | 700 |
| `headline-lg` | 32 / 40 px | 700 |
| `headline-lg-mobile` | 26 / 34 px | 700 |
| `headline-md` | 24 / 32 px | 600 |
| `headline-sm` | 20 / 28 px | 600 |
| `title-md` | 18 / 26 px | 600 |
| `body-lg` | 16 / 24 px | 400 |
| `body-md` | 14 / 20 px | 400 |
| `body-sm` | 12 / 18 px | 400 |
| `label-lg` | 14 / 20 px | 600 |
| `label-md` | 12 / 16 px | 600 |
| `label-sm` | 10 / 14 px | 700 |

El `@theme` actual de [`resources/css/app.css`](../resources/css/app.css) todavía declara `Instrument Sans`; para que el producto coincida con la referencia habría que integrar Plus Jakarta Sans y sus estilos de forma deliberada.

## Espaciado y composición

La escala usa incrementos de 4 y 8 px:

| Token | Valor |
|---|---:|
| `space-xs` | 0.25 rem / 4 px |
| `space-sm` | 0.5 rem / 8 px |
| `space-md` | 1 rem / 16 px |
| `space-lg` | 1.5 rem / 24 px |
| `space-xl` | 2.5 rem / 40 px |
| `gutter-sm` / `margin-sm` | 1 rem / 16 px |
| `gutter` | 1.5 rem / 24 px |
| `margin` | 2 rem / 32 px |

- **Escritorio (>= 1200 px):** grilla de 12 columnas, margen exterior de 32 px y gutters de 24 px; admite paneles y barras laterales en paralelo.
- **Tablet (768-1199 px):** grilla de 8 columnas, margen exterior de 24 px y gutters de 16 px; la navegación lateral pasa a un menú tipo drawer.
- **Móvil (<= 767 px):** grilla de 4 columnas, márgenes y gutters de 16 px; tarjetas apiladas y barra de tareas inferior fija para acciones frecuentes.
- Usa 16 px de padding de tarjeta en móvil y 24 px en escritorio; reserva 40 px para separar secciones.

## Superficies, elevación y formas

La profundidad se expresa con bordes tenues y sombras índigo/lavanda, no con sombras negras duras:

| Nivel | Aplicación | Sombra de referencia |
|---|---|---|
| 0 | Canvas `#f4f3f9` descrito en la prosa | Sin elevación |
| 1 | Tarjeta blanca, borde `1px solid #e6e4f1` | `0 4px 16px -2px rgba(44, 58, 140, 0.04), 0 2px 6px -1px rgba(103, 102, 168, 0.03)` |
| 2 | Hover o seleccion activa | `0 10px 24px -4px rgba(44, 58, 140, 0.08), 0 4px 8px -2px rgba(103, 102, 168, 0.04)` |
| 3 | Modales, popovers y paneles flotantes | `0 20px 32px -8px rgba(44, 58, 140, 0.14), 0 8px 16px -4px rgba(30, 37, 77, 0.06)` |

Radios descritos para los componentes: 8 px en controles e inputs, 12 px en botones, 16 px en tarjetas, 24 px en modales y perfiles destacados, y píldora completa (`9999px`) en badges y chips. Estos valores deben reconciliarse con el tema Tailwind activo antes de depender de nombres de clase abreviados.

## Componentes y estados

- **Botón primario:** fondo `#2c3a8c` según la prosa, texto blanco, radio 12 px y padding 12 x 24 px. Hover más oscuro (`#232f72`); foco visible de 3 px en `#9f9ad4`.
- **Botón secundario:** contorno de 1.5 px `#6766a8`, fondo blanco o transparente y texto `#2c3a8c`; hover `#f4f3f9`.
- **Botón terciario / ghost:** transparente, texto `#2c3a8c` y tinte `#e6e4f1` al pasar el puntero.
- **Chips y badges:** forma pill. Filtros inactivos con fondo blanco y borde `#e6e4f1`; filtro activo con fondo primario y texto blanco. Especie/categoría usa `#e6e4f1` con texto `#2c3a8c`.
- **Inputs:** fondo blanco, texto `#1e254d`, borde de 1.5 px `#e6e4f1`, radio de 8 px; al enfocar, borde primario y anillo de 3 px `#9f9ad4`. Checkbox y radio seleccionados usan azul primario; deshabilitados, lavanda atenuado.
- **Tarjetas:** superficie blanca elevada sobre fondo lavanda, con encabezado y divisor sutil; agrupan diagnóstico, microchip y datos de contacto de emergencia.
- **Ficha/banda de paciente:** foto o miniatura, especie, raza, edad, peso y alertas médicas con niveles de contraste claros.
- **Seguimiento de vacunas:** línea periwinkle con checkpoints circulares e indicadores primarios para renovaciones próximas.
- **Matriz de citas:** espacios sobre superficie lavanda que, al reservarse, pasan a primario con texto invertido blanco.

## Iconografía, interacción y accesibilidad

El prototipo utiliza **Material Symbols Outlined** para acciones e indicadores, junto a etiquetas de texto en controles importantes. Mantén un estilo consistente y evita que el icono sea la única forma de comunicar una acción o estado.

- Conserva contraste legible entre texto, fondo y estados; las alertas deben tener texto además del color.
- Mantén visible el estado de foco por teclado y no elimines el anillo sin reemplazo.
- Distingue estados activos, pendientes, deshabilitados y críticos con etiquetas comprensibles.
- En móvil, prioriza acciones frecuentes y presentación apilada sin ocultar datos clínicos esenciales.
- El material no especifica un tema oscuro ni reglas de movimiento reducido; no los des por definidos.

## Aplicación al proyecto

El sistema descrito es una referencia del prototipo Stitch. La hoja actual de la aplicación contiene la importación base de Tailwind CSS v4 y una declaración `--font-sans` de `Instrument Sans`; no contiene todavía la paleta ni las escalas de la referencia. Si se implementa el sistema en Laravel, traduce primero los tokens a `@theme` en CSS y aplica sus variantes de forma semántica en las vistas Blade. Evita copiar la configuración Tailwind CDN del prototipo como configuración de producción.
