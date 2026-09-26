# CLAUDE.md — Gestión Comercial Aides

Memoria persistente del proyecto. Documenta lo que **existe** en el código, no lo aspiracional.
Idioma del proyecto y del dominio: **español**.

---

## Propósito del proyecto

Sistema de **gestión comercial** para "Casa Aides": ventas, compras, stock/inventario,
facturación electrónica AFIP/ARCA (Argentina), caja, cobros y pagos. Aplicación web monolítica
con interfaz administrativa renderizada en servidor (Twig).

---

## Stack y versiones

| Componente | Versión / Detalle |
|---|---|
| PHP | **5.6.40** en ejecución (CLI). `composer.json` declara `>=5.3.9` y `config.platform.php=5.3.9` |
| Symfony | **2.7.\*** (Standard Edition, estructura legacy `app/` + `src/` + `web/`) |
| Doctrine ORM | `^2.4.8` · doctrine-bundle `~1.4` · driver `pdo_mysql` |
| Base de datos | **MySQL** (XAMPP). BD por defecto: `aides_0306` |
| Twig | `^1.0 || ^2.0` |
| Plantillas/Assets | TwigBundle + **AsseticBundle** (`~2.3`). Sin Webpack/Encore/AssetMapper/npm |
| Extensiones Doctrine | **stof/doctrine-extensions-bundle** (Gedmo): Loggable, Timestampable, Blameable, Sluggable; SoftDeleteable disponible pero **deshabilitado** |
| PDF | **psliwa/pdf-bundle** (`PsPdfBundle`, `~1.0`) |
| Excel | **liuggio/excelbundle** (`dev-master`) |
| QR | **endroid/qr-code** `^1.7` (QR de AFIP en comprobantes) |
| Email | swiftmailer-bundle (`~2.3`), spool en memoria |
| Logs | monolog-bundle (`^3.0.2`) |
| Routing/Atajos | sensio/framework-extra-bundle `^3.0.2` (`@Route`, `@Method`, `@Template` como anotaciones) |
| Tests | phpunit-bridge (`~2.7`); config en `app/phpunit.xml.dist` |

> No hay `.env` (Symfony 2.7): la configuración por máquina va en `app/config/parameters.yml`
> (gitignored), generado desde `app/config/parameters.yml.dist`.

---

## Estructura del proyecto

```
app/                         Kernel, config y caché/logs (estructura Symfony 2.x)
  AppKernel.php              Registro de bundles
  config/
    config.yml               Config principal (framework, doctrine, twig, assetic, gedmo, servicios)
    config_dev|prod|test.yml  Config por entorno (importan config.yml)
    parameters.yml(.dist)    Parámetros por máquina (BD, mailer, AFIP, tickeadora) — el .yml es gitignored
    routing.yml              Carga rutas por anotación desde los 4 bundles (prefix /)
    security.yml             Firewall, provider de usuarios, encoder bcrypt, role_hierarchy
    services.yml             Stub vacío (NO se importa; servicios reales van en config.yml)
  Resources/views/           base.html.twig, login, notificacion, páginas de error
src/
  AppBundle/                 Productos, precios, stock, depósitos, despachos, pedidos interdepósito
    DoctrineFunctions/       Funciones DQL custom: ROUND, DATE_FORMAT, REPLACE
    EventListener/           CajaListener (resuelve caja por hostname en cada request)
  ConfigBundle/              Núcleo: usuarios, roles/permisos, unidades de negocio, empresa,
                             cajas, bancos/cheques, formas de pago, monedas, geografía,
                             tablas AFIP, escalas, parámetros, utilidades (UtilsController)
  ComprasBundle/             Compras: facturas, notas déb/créd, proveedores, pedidos de compra,
                             pagos a proveedor, retenciones, lotes, centros de costo
  VentasBundle/              Ventas: ventas, presupuestos, facturas, factura electrónica,
                             notas déb/créd, clientes, cobros, pagos de cliente, apertura de caja
    Afip/                    SDK AFIP + WSDLs + certificados (gitignored, ver "Puntos de atención")
    Service/                 FacturaElectronicaWebservice (autorización de comprobantes ante AFIP)
web/                         Document root. app.php (prod) / app_dev.php (dev)
  assets/                    CSS/JS estáticos (jQuery 1.7, select2, fancybox) — mayormente gitignored
  uploads/                   Subidas + uploads/import/system/*.sql (semillas de roles/permisos)
docs/                        CSVs de importación y notas (gitignored)
```

> Cada bundle tiene `Controller/`, `Entity/` (entidades + repositorios juntos), `Form/`,
> `Resources/views/` y `Resources/config/services.xml` (este último es un **stub vacío**).

---

## Comandos esenciales

Symfony 2.7 usa `app/console` (no `bin/console`). En XAMPP/Windows, PHP es 5.6.

```bash
# Servidor: se sirve vía Apache de XAMPP apuntando a web/ (http://localhost/.../web/app_dev.php)
# (No hay symfony serve ni Docker configurados)

# Dependencias
composer install

# Caché
php app/console cache:clear --env=dev
php app/console cache:clear --env=prod

# Esquema de BD (NO hay carpeta migrations/ — ver "Base de datos")
php app/console doctrine:schema:update --dump-sql      # previsualizar
php app/console doctrine:schema:update --force         # aplicar (¡con cuidado en prod!)

# Assets (Assetic / instalación de assets de bundles)
php app/console assets:install web --symlink
php app/console assetic:dump

# Tests
php bin/phpunit -c app                                 # config en app/phpunit.xml.dist

# Listar rutas / depurar contenedor
php app/console debug:router
php app/console debug:container
```

> No existen comandos Console personalizados (`src/**/Command`). La carga inicial de datos se hace
> por controladores web: `InitialDataLoadController` e `ImportDataLoadController` (ConfigBundle),
> rutas bajo `^/dataload` (acceso anónimo permitido, protegido por `key_dataload`).

---

## Base de datos y entidades

- **Motor:** MySQL vía `pdo_mysql`. Charset UTF8. Naming strategy: `underscore`. `auto_mapping: true`.
- **Migraciones:** **no hay DoctrineMigrationsBundle ni carpeta `migrations/`.** El esquema se
  gestiona manualmente (`doctrine:schema:update`) y/o con dumps SQL. Semillas de sistema
  (roles/permisos/usuarios) en `web/uploads/import/system/*.sql`.
- **Versionado/auditoría:** muchas entidades usan `@Gedmo\Loggable` + `@Gedmo\Versioned` en campos
  clave (tabla de log de Gedmo mapeada en config.yml). Timestampable/Blameable activos por defecto.

### Entidades principales por dominio

- **AppBundle (producto/stock):** `Producto`, `Precio`, `PrecioLista`, `PrecioActualizacion`,
  `Stock`, `StockMovimiento`, `StockAjuste(+Detalle)`, `Deposito`, `Despacho(+Detalle)`,
  `Pedido(+Detalle)` (pedidos interdepósito).
- **VentasBundle:** `Venta(+Detalle)`, `Presupuesto(+Detalle)`, `Factura(+Detalle)`,
  `FacturaElectronica`, `NotaDebCred(+Detalle)`, `Cliente`, `Cobro(+Detalle, +DetalleTarjeta)`,
  `PagoCliente(+Comprobante, +Recibo)`, `CajaApertura`, `ImpresoraFiscal`.
- **ComprasBundle:** `Factura(+Detalle, +Alicuota)`, `NotaDebCred(+Detalle, +Alicuota)`,
  `Proveedor`, `Pedido(+Detalle)` (compra), `PagoProveedor`, `RetencionGanancia`, `LoteProducto`,
  `CentroCostoDetalle`.
- **ConfigBundle (núcleo/maestros):** `Usuario`, `Rol`, `Permiso`, `RolUnidadNegocio`,
  `UnidadNegocio`, `Empresa`, `Equipo`, `Caja`, `Banco(+Movimiento, +TipoMovimiento)`,
  `Cheque`, `TitularCheque`, `CuentaBancaria`, `FormaPago`, `Tarjeta`, `Moneda`, `Escalas`,
  `Pais`/`Provincia`/`Localidad`, `CentroCosto`, `RubroCompras`, `ActividadComercial`,
  `TipoCliente`, `Transporte`, `Parametro`/`Parametrizacion`, y tablas AFIP
  (`AfipAlicuota`, `AfipComprobante`, `AfipCondicionIvaReceptor`, `AfipOperacion`).

### Relaciones y patrones de mapeo

- Entidades mapeadas con **anotaciones** (`@ORM\...`). Repositorios en el mismo `Entity/`
  (p.ej. `VentaRepository`), referenciados con `repositoryClass`.
- Relaciones cross-bundle frecuentes (p.ej. `Venta` → `ConfigBundle\Entity\UnidadNegocio`,
  `FormaPago`; `Venta` ↔ `Cliente`). Se referencian repositorios con la notación
  `Bundle:Entidad` (`getRepository('ConfigBundle:Caja')`).
- Estados como strings en la propia entidad (p.ej. `Venta.estado`:
  `PENDIENTE | COBRADO | FACTURADO | ANULADO`).

---

## Arquitectura y decisiones

- **Flujo:** request → `@Route`/`@Method`/`@Template` en el controlador → lógica inline en el
  controlador (extiende `Controller` de FrameworkBundle) → `getDoctrine()->getManager()` /
  repositorios. **La mayor parte de la lógica de negocio vive en los controladores**, no en una
  capa de servicios. Excepción notable: `FacturaElectronicaWebservice`.
- **Helpers estáticos:** `ConfigBundle\Controller\UtilsController` concentra utilidades estáticas
  (fechas, CUIT, padding, CSV→tabla, percepciones, control de acceso). Se llama de forma estática
  desde otros controladores (`UtilsController::haveAccess(...)`).
- **Multi-tenant por Unidad de Negocio:** el usuario tiene roles por `UnidadNegocio`
  (`RolUnidadNegocio`). La unidad activa se guarda en sesión (`unidneg_id`).
- **Autorización (custom, NO voters):** el control de acceso es por **slug de ruta**:
  `UtilsController::haveAccess($user, $unidneg, 'nombre_ruta')` → `Usuario::getAccess(unidneg, slug)`
  → recorre `RolUnidadNegocio` → `Rol::getAccess(slug)`. Lanza `AccessDeniedException` si no hay
  permiso. **Cada acción protegida debe llamar a `haveAccess` con el slug de su ruta.**
- **Seguridad Symfony:** firewall `secured_area` (`^/`) con `form_login` (rutas `usuario_login` /
  `usuario_login_check`), `remember_me` (1 semana) y `logout`. Provider: entidad `Usuario` por
  `username`. Encoder **bcrypt**. `role_hierarchy`: `ROLE_SUPER_ADMIN` agrupa OPERATOR/USER/ADMIN.
  Anónimo permitido en `^/usuario/login` y `^/dataload`.
- **Event listener:** `app.caja_listener` (`kernel.request`) resuelve la **caja** por hostname
  (`gethostbyaddr` de la IP del cliente) y guarda en sesión `caja` + apertura sin cerrar. Lo usa,
  entre otros, `FacturaElectronicaWebservice` para el punto de venta.
- **Integración AFIP/ARCA:** `FacturaElectronicaWebservice` (definido en `config.yml`, args:
  EntityManager, session, `iibb_percent`, `cuit_afip`) usa el SDK en `VentasBundle/Afip/` con
  certificados, modo producción/homologación y catálogo de errores AFIP. Genera QR (endroid).
- **DQL custom:** `ROUND`, `DATE_FORMAT`, `REPLACE` registradas en config.yml
  (`AppBundle\DoctrineFunctions\*`).
- **Sin Messenger / colas / API Platform.** Mailer con spool en memoria. Translator **deshabilitado**.

---

## Configuración e integraciones

Parámetros relevantes en `app/config/parameters.yml` (valores reales son por máquina; **sin secretos** aquí):

| Parámetro | Rol |
|---|---|
| `database_host` / `database_port` / `database_name` / `database_user` / `database_password` | Conexión MySQL (dev: `127.0.0.1:3306`, BD `aides_0306`, `root`) |
| `mailer_transport` / `mailer_host` / `mailer_user` / `mailer_password` | SMTP (Swiftmailer) |
| `cuit_afip` | CUIT del emisor para facturación electrónica |
| `iibb_percent` | Porcentaje IIBB (default 3.5), expuesto como global de Twig |
| `url_qr_afip` | Base del QR ARCA (`https://www.arca.gob.ar/fe/qr?p=`) |
| `modelo_tickeadora` / `puerto_tickeadora` / `baudios_tickeadora` / `host_tickeadora` | Impresora fiscal / tickeadora (globals de Twig) |
| `billing_folder` | Carpeta de facturación |
| `secret` | Secret de Symfony (CSRF, etc.) |
| `key_dataload` | Clave para endpoints públicos `^/dataload` |

- **Entornos:** `dev` carga DebugBundle + WebProfiler + SensioDistribution/Generator;
  `prod` usa monolog `fingers_crossed` (escribe ante `error`). Caché/proxies en `app/cache/`.
- **Locale:** `es` (translator deshabilitado).

---

## Convenciones

- **PHP 5.6 / Symfony 2.7:** sin tipado escalar de PHP 7+, sin atributos PHP 8 (rutas por
  **anotación** docblock). Mantener compatibilidad con PHP 5.6.
- **Sin linters configurados** (no hay PHP-CS-Fixer, PHPStan ni Psalm). Estilo real observado:
  llaves de apertura en la misma línea, indentación de 4 espacios, `protected $propiedad` en
  entidades, anotaciones Doctrine/Gedmo en docblocks.
- **Nomenclatura del dominio en español** (entidades, propiedades, rutas, slugs de permiso).
  Tablas con prefijo de bundle (p.ej. `ventas_venta`).
- **Rutas:** nombre de ruta = slug de permiso (p.ej. `ventas_venta`). Convención
  `<bundle>_<entidad>[_accion]`.
- **Dónde va cada cosa:** controladores en `<Bundle>/Controller`, entidades **y** repositorios en
  `<Bundle>/Entity`, formularios en `<Bundle>/Form`, vistas en `<Bundle>/Resources/views`.
  Servicios reales se declaran en `app/config/config.yml` (los `services.xml` de bundle están vacíos).
- **Lógica nueva:** seguir el patrón existente salvo que se acuerde lo contrario; para integraciones
  externas o lógica reutilizable, preferir un servicio (como `FacturaElectronicaWebservice`).

---

## Puntos de atención (deuda técnica / gotchas)

- **Stack legacy y EOL:** Symfony 2.7 y PHP 5.6 están fuera de soporte. Cualquier dependencia nueva
  debe ser compatible. `composer.lock` está gitignored.
- **AFIP gitignored:** `src/VentasBundle/Afip/` (SDK, **certificados** y **claves**) está en
  `.gitignore`. No está versionado: no asumir su contenido sin inspeccionarlo; nunca commitear
  certificados/keys.
- **Lógica en controladores:** acciones largas con reglas de negocio (stock, cobros, facturación)
  embebidas. Cambios requieren leer la acción completa; cuidado con efectos colaterales en stock/caja.
- **Autorización manual:** si una acción nueva no llama a `UtilsController::haveAccess(...)`, queda
  **sin proteger** a nivel de permisos de negocio. Verificar siempre el slug.
- **CajaListener depende de DNS inverso:** `gethostbyaddr` por request; si el hostname no resuelve o
  la `Caja` no está `activo`, no hay caja en sesión y la factura electrónica usa el punto de venta
  por defecto (`ptovtaWsFactura = 12`).
- **Sin migraciones formales:** los cambios de esquema no quedan registrados como migraciones.
  Coordinar `doctrine:schema:update` con dumps; revisar `--dump-sql` antes de `--force`.
- **`config/services.yml` no se usa:** está comentado en `config.yml`; los servicios viven en
  `config.yml`. Los `Resources/config/services.xml` de cada bundle son stubs vacíos.
- **AFIP / ARCA:** la URL de QR ya apunta a `arca.gob.ar` (ex-AFIP). Hay WSDLs de producción y
  homologación; el modo se decide por configuración del CUIT/`production` en el servicio.
- **Caché de prueba:** `php app/console cache:clear` puede requerir permisos de escritura en
  `app/cache` (Windows/XAMPP).

---

## Registro de cambios de contexto

> Orden cronológico inverso. Anotá cada incorporación o cambio relevante con fecha y descripción breve.

- **2026-09-22** — **Precio unitario y total editables en Presupuesto y Venta + total = suma de líneas**:
  - **Cálculo oficial (precio final, clientes no `I`/`M`):** unitario final =
    `round(precio × (1+IVA) × (1+dto%) / cotización, 2)` (`*Detalle::getPrecioFinalItem()`), línea =
    `round(unitario × cant, 2)` (`getTotalFinalItem()`), `getMontoTotal()` = Σ líneas y
    `getTotalDescuentoRecargo()` = total − subtotal. Reemplaza "descuento sobre el subtotal" en `Venta` y
    `Presupuesto` (NotaDebCred sin cambios). Facturas A (`I`/`M`) mantienen su cálculo.
  - **AFIP B:** `FacturaElectronicaWebservice` arma los totales por alícuota desde `getTotalFinalItem()`
    (`totalesPorAlicuotaDeLineas`); NDC sigue con `totalesPorAlicuotaProrrateados`.
  - **Esquema:** `precio` de `ventas_venta_detalle` y `ventas_presupuesto_detalle` pasa a
    `DECIMAL(20,4)` para poder llegar exacto a cualquier unitario editado. Script:
    `database/2026-09-22_precio_detalle_4_decimales.sql` (incluye consulta de control de pendientes).
  - **UI (`moduloVentas.js`):** `actualizarImportes()` replica exactamente las fórmulas PHP
    (`calcularTotales`, redondeo tipo PHP). En venta/presupuesto (flag Twig `editarPrecios`) la columna
    "Precio Unit." es un input (`handlePrecioFinalChange` → `precioParaFinal`) y el TOTAL también
    (`handleTotalChange` → `ajustarPreciosATotal`: prorrateo + ajuste exacto en centavos sobre 1–2 líneas;
    si las cantidades no lo permiten avisa el total más cercano). La NDC usa
    `actualizarImportesNotaDebCred()` (la función anterior, intacta). Sin permiso: lo pueden usar todos.
  - PDFs de presupuesto usan `precioFinalItem`/`totalFinalItem` (antes la línea usaba el unitario sin
    redondear y no coincidía con unitario × cantidad).
  - **Ticket fiscal** (`FacturaElectronicaController::getTicketAction`): la IFU recibe el descuento como
    monto global (`imprimirDescuentoGeneral`) y lo reparte por alícuota a su criterio. El unitario se
    envía `round(...,2)` para **precio final** (es el que forma el subtotal) y **sin redondear para A**
    (así la IFU arma la misma base imponible por línea que el comprobante). `ivaContenido` (Ley 27.743)
    sale de `FacturaElectronicaWebservice::getIvaContenidoPrecioFinal()`, el mismo IVA del comprobante.
    Verificado con tickets reales en emulador Hasar: B (venta 139135) coincide exacto; en **A la IFU
    recalcula el IVA por su cuenta y puede diferir ~1 centavo** — el ticket A no es el flujo normal
    (el check "Emitir ticket" solo se tilda para consumidor final; 10 TICK-A vs 22.973 TICK-B en 2026).
  - **Coherencia app ↔ comprobante en facturas A** (salió a la luz al editar precios con 4 decimales):
    `*Detalle::getTotalItem()` para `I`/`M` ahora usa el neto por línea (`getBaseImponibleItem`, igual que
    la base que se informa) en vez de `round(unitario,2) × cant`; `getTotalDescuentoRecargo()` (`I`/`M`) se
    ajusta para que `subTotal + descuento` sea exactamente el neto del comprobante; `getTotalIibb()`
    redondea a 2 como el tributo informado; y en el servicio, el `ImpTotal` de la rama A se arma sumando
    los importes **ya redondeados** (ImpNeto + ImpIVA + ImpOpEx), como exige AFIP. Con precios de 2
    decimales estas diferencias no existían. Verificado sobre 294 cobros reales (A, B, C, E, M):
    total de pantalla = total del comprobante en todos.

- **2026-09-21** — **Total de factura B alineado al centavo con presupuesto/venta**:
  `FacturaElectronicaWebservice::setDataFacturaElectronica` calculaba ImpTotal/ImpNeto/ImpIVA con
  neto + descuento + IVA por ítem sin redondear, mientras que `Presupuesto`/`Venta`/`NotaDebCred::getMontoTotal()`
  (precio final) redondean el unitario con IVA antes de multiplicar por la cantidad. Resultado: ~1/3 de
  las facturas B de 2026 difería en centavos del presupuesto/cobro (caso: pres. #64955 $337.971,65 vs
  FAC-B 0012-00028933 $337.971,64). Para clientes **no** `I`/`M` (FAC y NDC) ahora usa
  `calcularImportesPrecioFinal()`: toma el mismo total que `getMontoTotal()`, lo reparte por alícuota
  (residuo al grupo mayor) y desglosa neto/IVA contenido de cada una. Facturas A sin cambios.
  Comprobantes ya autorizados no se modifican (el CAE manda). Sin cambios de esquema ni SQL.

- **2026-09-06** — **Fix de IIBB en Notas de Débito/Crédito de ventas**:
  `NotaDebCredController::createAction` calculaba la percepción de IIBB con
  `Cliente::getPercepcionRentas()` (retención "plana" de la categoría del cliente) en vez de la
  escala vigente a la fecha de la nota. Eso desalineaba el total interno del comprobante respecto
  del total autorizado por AFIP (el CAE siempre estuvo bien: se toma como fuente de verdad).
  Ahora usa el porcentaje de la propia nota (`NotaDebCred.percepcionRentas`) y, si viene vacío, lo
  resuelve con `UtilsController::getPercepcionRentasByClienteAndDate($cliente, $fecha, $em)` y lo
  persiste en el comprobante. `Cliente::getPercepcionRentas()` quedó marcado `@deprecated`.
  Origen del bug: commit `40209ed` (2026-06-03, "Escalas con versionado"). Reparación de datos
  históricos (detección + preflight + fix, con snapshot para rollback) en `database/2026-08-26_*.sql`
  — no versionados, `*.sql` está en `.gitignore`. Sin cambios de esquema.
- **2026-09-06** — **Aviso de descuento de stock en el popup de Presupuesto**: en
  `Presupuesto/new.html.twig`, la línea de aviso del modal de confirmación pasó a ser incondicional
  y cambia según el check `descuentaStock`: en gris "Se realizará el descuento de los productos en
  el stock!" cuando descuenta, en rojo "No se realizará descuento en el stock!" cuando no. La arma
  el helper `textoDescuentaStock()`, usado tanto al construir el mensaje inicial como en el `change`
  del combo de tipo (elegir Remito fuerza el descuento y devuelve el texto a gris). Solo cambio de
  vista (sin SQL).
- **2026-06-23** — **Filtros de Presupuestos persistidos en sesión**: `PresupuestoController::indexAction`
  guarda los criterios de búsqueda (`cliId`, `desde`, `hasta`, `descuentaStock`) en sesión
  (clave `ventas_presupuesto_filtro`) cuando llega un submit del formulario, y los restaura cuando
  el listado se recarga sin parámetros (p.ej. el redirect tras registrar un seguimiento). Así no se
  pierde la búsqueda al guardar un llamado. Solo cambio de código (sin SQL).
- **2026-06-23** — **Zona horaria fijada en la app**: `AppKernel::__construct()` ahora llama a
  `date_default_timezone_set('America/Argentina/Buenos_Aires')` antes de `parent::__construct()`.
  Motivo: en el hosting las fechas se guardaban 1 hora menos porque el `date.timezone` del php.ini
  del servidor estaba mal; la app dependía 100% del php.ini (no había `date_default_timezone_set`
  en ningún lado). Ahora `new \DateTime()` usa hora de Argentina en web y CLI sin importar el
  servidor. Solo cambio de código (sin SQL); desplegar y limpiar caché.
- **2026-06-23** — **Seguimiento de Presupuestos**: nueva entidad `PresupuestoSeguimiento`
  (tabla `ventas_presupuesto_seguimiento`; historial de llamados: fecha/usuario/estado/comentario) +
  campo `Presupuesto.estadoSeguimiento` (`PENDIENTE | VENDIDO | NEGOCIANDO | PERDIDO`, default
  `PENDIENTE`, `@Gedmo\Versioned`). En el listado, columna "Seguimiento" con ícono de teléfono
  (visible salvo `VENDIDO`/`ANULADO`) que abre un modal jQuery UI (`#popup` + `.load`) para registrar
  el llamado (estado + comentario obligatorio; el estado lo elige el usuario, sin transiciones
  automáticas). Acciones `ventas_presupuesto_seguimiento` (GET, modal) y `..._seguimiento_save` (POST)
  en `PresupuestoController`. Nuevo permiso `ventas_presupuesto_seguimiento` (en `permiso.csv` y BD).
  Script de migración para prod en `database/2026-06-23_presupuesto_seguimiento.sql` (NO se usó
  `schema:update --force` por drift preexistente en el esquema). `findByCriteria` hace fetch-join de
  seguimientos para evitar N+1.
- **2026-06-23** — Creación de `CLAUDE.md`. Análisis inicial del proyecto: Symfony 2.7 / PHP 5.6,
  4 bundles (App/Config/Compras/Ventas), MySQL sin migraciones, autorización custom por
  Unidad de Negocio, integración AFIP (`FacturaElectronicaWebservice`), `CajaListener` por hostname.
  Contexto reciente del repo: listado de productos con filtro "descuenta stock"; trabajo sobre
  **Escalas** (con versionado Gedmo) y ajustes en ventas/presupuestos.

---

> **Instrucción de mantenimiento:** Cada vez que se agregue una entidad, servicio, bundle,
> migración o decisión arquitectónica importante, actualizá la sección
> "Registro de cambios de contexto" con fecha y una descripción breve.
