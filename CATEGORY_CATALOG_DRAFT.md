# Borrador: catálogo de categorías y comercios

## Objetivo

Incorporar una clasificación consistente de gastos para que registrar un movimiento
siga siendo rápido y, posteriormente, se puedan generar reportes y gráficas útiles.

La clasificación tendrá dos niveles:

- **Categoría:** agrupación para el análisis financiero, por ejemplo: `Comida`,
  `Transporte`, `Hogar` o `Entretenimiento`.
- **Comercio / detalle:** nombre específico que identifica el gasto, por ejemplo:
  `Wendy's`, `Cinépolis`, `Uber` o `Costco`.

Las gráficas agruparán primordialmente por categoría. El comercio permitirá ver
detalles, sugerir categorías y consultar los lugares donde más se gasta.

## Experiencia al crear un gasto

1. La persona captura monto, fecha y el nombre del comercio o detalle.
2. El campo de comercio ofrece coincidencias existentes (autocompletado).
3. Al elegir un comercio conocido, se propone su categoría predeterminada.
4. La categoría puede cambiarse para ese gasto concreto.
5. Si el comercio es nuevo, se podrá capturar como texto libre y, opcionalmente,
   guardarlo en el catálogo con una categoría predeterminada.

La creación de gastos no debe depender de haber configurado un catálogo antes.
Un gasto puede registrarse manualmente con categoría elegida y nombre libre.

Ejemplo:

```text
Monto:       $350.00
Detalle:     Wendy's
Categoría:   Comida
Fecha:       2026-08-18
```

## Categorías iniciales sugeridas

Las categorías iniciales son solo una propuesta; el hogar podrá renombrarlas,
crear otras o archivarlas.

| Categoría | Ejemplos |
| --- | --- |
| Comida | Supermercado, restaurantes, café, delivery |
| Transporte | Gasolina, Uber, estacionamiento, mantenimiento |
| Hogar | Servicios, reparaciones, muebles, renta |
| Entretenimiento | Cine, conciertos, videojuegos, streaming |
| Salud | Médico, farmacia, gimnasio, seguro |
| Mascotas | Veterinario, alimento y accesorios |
| Compras personales | Ropa, cuidado personal, tecnología |
| Suscripciones | Spotify, Netflix, software |
| Educación | Cursos, libros y colegiaturas |
| Viajes | Hospedaje, vuelos y actividades |
| Regalos | Regalos y donaciones |
| Otros | Gastos excepcionales o sin clasificar |

No se planean subcategorías en la primera versión. Se evaluarán después de usar
el sistema, si una categoría resulta demasiado amplia.

## Modelo de datos propuesto

### Tabla `categories`

| Campo | Tipo sugerido | Reglas / propósito |
| --- | --- | --- |
| `id` | `BIGINT` / `INT` | Clave primaria. |
| `household_id` | `BIGINT` / `INT` | Dueño compartido de la categoría. Usar la entidad equivalente existente si el proyecto tiene otro nombre. |
| `name` | `VARCHAR(100)` | Nombre visible, por ejemplo `Comida`. Requerido. |
| `icon` | `VARCHAR(50)` nullable | Clave de un ícono de la librería del frontend, por ejemplo `utensils`, `car` o `house`. No almacenar archivos de imagen. |
| `color` | `VARCHAR(7)` nullable | Color hexadecimal, por ejemplo `#F97316`, para gráficas e interfaz. |
| `is_active` | `BOOLEAN` / `TINYINT(1)` | `true` si se puede elegir en gastos nuevos; `false` si está archivada. |
| `created_at` | `DATETIME` | Fecha de creación. |
| `updated_at` | `DATETIME` | Fecha de última modificación. |

Restricciones recomendadas:

- Índice en `household_id`.
- Índice compuesto en `(household_id, is_active)` para cargar el selector.
- Nombre único por hogar, idealmente sin distinguir mayúsculas/minúsculas, para
  evitar dos categorías llamadas `Comida`.
- Las categorías no se eliminan si tienen gastos asociados: se archivan mediante
  `is_active = false`.

### Cambios a tabla `expenses`

Agregar un campo opcional durante la transición:

| Campo | Tipo sugerido | Reglas / propósito |
| --- | --- | --- |
| `category_id` | `BIGINT` / `INT` nullable | Llave foránea a `categories.id`. Identifica la categoría usada en el gasto. |

La aplicación ya guarda un campo `name` para el nombre/descripción del gasto.
En la primera versión puede conservarse para el comercio o detalle escrito por
la persona. Por tanto, no es indispensable crear la tabla de comercios desde el
inicio.

Restricciones recomendadas:

- Índice en `category_id`, importante para filtros y agregaciones de reportes.
- Llave foránea `expenses.category_id -> categories.id` con `ON DELETE RESTRICT`
  (o equivalente). Las categorías con historial no deben borrarse.
- Una vez terminada la migración, `category_id` deberá ser obligatorio para los
  gastos nuevos. Los registros históricos ambiguos pueden conservar `NULL` hasta
  que se clasifiquen.

### Tabla futura opcional `merchants`

Esta tabla se implementará solo cuando el autocompletado y la sugerencia de
categorías lo justifiquen.

| Campo | Tipo sugerido | Propósito |
| --- | --- | --- |
| `id` | `BIGINT` / `INT` | Clave primaria. |
| `household_id` | `BIGINT` / `INT` | Dueño compartido del comercio. |
| `name` | `VARCHAR(150)` | Nombre normalizado, por ejemplo `Wendy's`. |
| `default_category_id` | `BIGINT` / `INT` nullable | Categoría sugerida al seleccionar ese comercio. |
| `is_active` | `BOOLEAN` / `TINYINT(1)` | Permite archivarlo. |
| `created_at`, `updated_at` | `DATETIME` | Auditoría básica. |

En esa fase podrá añadirse `merchant_id` nullable a `expenses`. La categoría se
seguirá guardando directamente en cada gasto; no se debe depender únicamente de
la categoría predeterminada del comercio, porque puede cambiar según la compra.

## Idiomas

En esta primera versión no hace falta una tabla de traducciones para categorías.
`categories.name` guarda contenido del hogar y puede ser personalizado: `Comida`,
`Boda`, `Proyecto casa` o `Japan trip`. El idioma de la interfaz se traduce en el
frontend como se hace actualmente, de forma independiente.

Al crear un hogar se pueden insertar categorías iniciales en el idioma elegido.
Si en el futuro se requiere que las categorías predefinidas se muestren en varios
idiomas sin cambiar sus datos, se añadirá un catálogo global de plantillas y sus
traducciones. Las categorías creadas por usuarios seguirán guardando su nombre
directamente, sin traducción automática.

## Íconos y colores

`icon` no contiene una imagen. Almacena una clave estable de la librería de
íconos del frontend. La interfaz debe limitar la selección a un conjunto permitido
y usar un ícono por defecto, como `tag` o `circle`, si no se elige uno.

Ejemplos:

| Categoría | `icon` | `color` |
| --- | --- | --- |
| Comida | `utensils` | `#F97316` |
| Transporte | `car` | `#3B82F6` |
| Hogar | `house` | `#8B5CF6` |
| Salud | `heart-pulse` | `#EF4444` |
| Entretenimiento | `film` | `#EC4899` |

## Plan de implementación gradual

1. Crear `categories`, agregar `category_id` nullable a `expenses` e insertar
   las categorías iniciales.
2. Añadir selección de categoría al alta y edición de gastos, sin cambiar el
   campo actual `name`.
3. Crear una sección de Settings para administrar categorías: crear, editar,
   elegir color/ícono y archivar.
4. Clasificar gastos históricos mediante una migración/script con reglas conocidas
   y dejar los casos ambiguos pendientes de revisión.
5. Crear reportes por rango de fechas y categoría.
6. Si el uso lo justifica, añadir `merchants` y autocompletado con categoría
   predeterminada.

## Migración de gastos existentes

La migración debe ser reversible y no bloquear el uso actual:

1. Agregar `category_id` permitiendo `NULL`.
2. Crear las categorías base para el hogar.
3. Ejecutar reglas de coincidencia sobre el campo `expenses.name`, por ejemplo:

```text
Wendy's   -> Comida
Cinépolis -> Entretenimiento
Uber      -> Transporte
Spotify   -> Suscripciones
```

4. Marcar o filtrar los gastos sin categoría para que puedan revisarse después.
5. No clasificar automáticamente entradas ambiguas, por ejemplo `Costco`, si no
   existe una regla acordada para el hogar.

## Reportes futuros

La primera pantalla de reportes puede incluir total gastado por categoría dentro
de un rango de fechas, comparación mensual, gastos por persona (si ese dato está
disponible) y comercios más frecuentes. Los filtros principales serán fecha,
categoría, comercio/detalle y responsable de pago.

Este documento es un borrador funcional; los nombres finales de tablas y campos
deben ajustarse al modelo de autenticación/propiedad que ya tenga el proyecto.
