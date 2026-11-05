# Sistema Integral de Gestión — Restaurante "Las Delicias de Mary"

Aplicación web en **Laravel** para gestionar las mesas, el menú y los pedidos (comandas) del restaurante "Las Delicias de Mary", con control de acceso por rol (administrador, mesero y cocinero).

Proyecto integrador de **Ingeniería de Software II** y **Programación III**.
Institución Universitaria Antonio José Camacho, Tecnología en Sistemas de Información, grupo 512.

**Integrantes:** Santiago Pantoja Muñoz · Camila Rodríguez Ordoñez · Juan Manuel Daza

---

## Contenido

1. [Funcionalidades implementadas](#1-funcionalidades-implementadas)
2. [Requisitos](#2-requisitos)
3. [Instalación](#3-instalación)
4. [Base de datos](#4-base-de-datos)
5. [Ejecución](#5-ejecución)
6. [Usuarios de prueba](#6-usuarios-de-prueba)
7. [Cómo usar el sistema](#7-cómo-usar-el-sistema)
8. [Estructura del proyecto](#8-estructura-del-proyecto)
9. [Rutas](#9-rutas)

---

## 1. Funcionalidades implementadas

| Módulo | Qué hace | Requerimientos |
|---|---|---|
| Inicio de sesión | Valida usuario y contraseña cifrada, bloquea la cuenta tras 5 intentos fallidos y muestra solo las opciones del rol | RF-01, RF-09 |
| Gestión de usuarios (administrador) | Crear, editar, desactivar y desbloquear cuentas, y asignar roles | RF-11 |
| Tablero de mesas | Ver las mesas por estado (disponible, ocupada, reservada), reservarlas y liberarlas | RF-02 |
| Comanda (mesero) | Armar los platos de cada cliente (principio, proteína y acompañamientos, con el arroz incluido), agregar productos del menú fijo y enviar el pedido a la cocina | RF-03, RF-06, RF-07 |
| Ver y cancelar pedidos | Lista de pedidos de una mesa, con cancelación de los pedidos pendientes | RF-12 |

**Permisos por rol:**

| Función | Administrador | Mesero | Cocinero |
|---|:---:|:---:|:---:|
| Gestionar usuarios | Lo utiliza | | |
| Ver mesas, reservar y liberar | Lo utiliza | Lo utiliza | |
| Registrar comandas | | Lo utiliza | |
| Ver y cancelar pedidos pendientes | Lo utiliza | Lo utiliza | |

---

## 2. Requisitos

| Herramienta | Versión | Uso |
|---|---|---|
| PHP | 8.2 o superior | Lenguaje del proyecto (incluido en XAMPP) |
| Composer | 2.x | Instala Laravel y sus dependencias |
| MySQL | 8.0 o superior (o MariaDB de XAMPP) | Base de datos |
| XAMPP | 8.2 o superior | Trae PHP y MySQL para Windows |
| DBeaver | Cualquiera reciente | Ejecutar el script de la base de datos |
| Navegador | Chrome, Edge o Firefox | Usar la aplicación |

La interfaz usa **Bootstrap 5.3** cargado desde internet (CDN), así que el navegador necesita conexión para verse con estilos.

Para comprobar que tienes PHP y Composer, abre una terminal y ejecuta:

```bash
php -v
composer -V
```

Si Composer no se reconoce, descárgalo de <https://getcomposer.org>. El instalador detecta el PHP de XAMPP.

---

## 3. Instalación

### 3.1 Obtener el proyecto

Clona el repositorio o copia la carpeta del proyecto, y entra en ella:

```bash
git clone <url-del-repositorio> Proyecto_Las_Delicias_de_Mary
cd Proyecto_Las_Delicias_de_Mary
```

### 3.2 Instalar las dependencias

La carpeta `vendor` (Laravel y sus librerías) **no se sube al repositorio**, así que hay que crearla:

```bash
composer install
```

Si aparece un error por una extensión de PHP que falta (por ejemplo `ext-zip` o `ext-fileinfo`), abre `C:\xampp\php\php.ini`, quita el `;` del inicio de la línea correspondiente (`;extension=zip` → `extension=zip`), guarda y vuelve a ejecutar el comando.

### 3.3 Crear el archivo de configuración `.env`

```bash
copy .env.example .env        # Windows
# cp .env.example .env        # Linux / macOS
php artisan key:generate
```

> Laravel solo lee el archivo **`.env`**. El `.env.example` es una plantilla y no se usa.

### 3.4 Configurar la conexión

Abre `.env` y deja estas líneas así (sin `#` al inicio, en minúsculas y sin espacios después del `=`):

```env
APP_NAME="Las Delicias de Mary"
APP_DEBUG=true
APP_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=las_delicias_de_mary
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

- `DB_USERNAME` y `DB_PASSWORD` son los de tu MySQL. En XAMPP, por defecto, el usuario es `root` sin contraseña.
- `SESSION_DRIVER=file` y `CACHE_STORE=file` son **obligatorios**. Por defecto, Laravel 11 y 12 guardan la sesión en tablas (`sessions`, `cache`) que no existen en nuestra base de datos.

Después de cualquier cambio en `.env`, ejecuta:

```bash
php artisan config:clear
```

---

## 4. Base de datos

1. Enciende **MySQL** desde el panel de control de XAMPP.
2. En DBeaver, crea una conexión: **Base de datos → Nueva conexión → MySQL**, con servidor `localhost`, puerto `3306` y usuario `root`.
3. Clic derecho sobre la conexión → **Editor SQL → Abrir script SQL** y abre el archivo `las_delicias_de_mary_dbeaver.sql`.
4. Ejecuta **todo el script con `Alt+X`** (botón "Ejecutar script"). `Ctrl+Enter` solo ejecuta la sentencia donde está el cursor.
5. Presiona `F5` sobre la conexión para ver la base `las_delicias_de_mary`.

El script se puede ejecutar varias veces: borra y vuelve a crear las tablas, **y se pierden los datos que tuvieran**. Crea lo siguiente:

| Tabla | Contenido |
|---|---|
| `roles` | ADMINISTRADOR, MESERO, COCINERO |
| `usuarios` | Cuentas del personal (contraseña cifrada con bcrypt) |
| `mesas` | Número, capacidad, estado y si está activa |
| `insumos` | Ingredientes para armar platos: BASE (arroz), PRINCIPIO, PROTEINA, ACOMPANAMIENTO |
| `categorias` | Categorías del menú fijo: especiales del día, jugos, gaseosas, adicionales |
| `platos` | Productos del menú fijo, con su precio informativo |
| `pedidos` | Comandas: número, mesa, mesero, estado y fecha |
| `detalle_pedido` | Líneas de cada comanda: tipo ARMADO o MENU |
| `detalle_pedido_insumo` | Insumos elegidos para cada plato armado |

Además crea dos vistas de apoyo: `v_pedidos_en_curso` y `v_comandas_cocina`. La segunda muestra cada pedido como lo leería la cocina, por ejemplo *"Arroz + Frijoles + Carne de cerdo asada"*.

**Convención de nombres:** cada clave primaria se llama `id_` más el nombre de la tabla en singular (`id_mesa`, `id_pedido`…), y la clave foránea que la referencia usa exactamente el mismo nombre.

---

## 5. Ejecución

Con MySQL encendido, desde la carpeta del proyecto:

```bash
php artisan serve
```

Abre en el navegador: **<http://127.0.0.1:8000>**. Te lleva automáticamente a la pantalla de inicio de sesión.

Para detener el servidor, presiona `Ctrl+C` en la terminal.

Comandos útiles si algo no se actualiza:

```bash
php artisan config:clear     # después de cambiar .env
php artisan route:clear      # después de cambiar routes/web.php
php artisan view:clear       # después de cambiar vistas .blade.php
php artisan route:list       # ver todas las rutas registradas
composer dump-autoload       # si Laravel no encuentra un controlador o un modelo nuevo
```

---

## 6. Usuarios de prueba

El script crea tres usuarios, uno por rol:

| Usuario | Contraseña | Rol |
|---|---|---|
| `admin` | `Admin2026*` | Administrador |
| `mesero1` | `Admin2026*` | Mesero |
| `cocinero1` | `Admin2026*` | Cocinero |

> Si cambiaron la contraseña del administrador con el `UPDATE` de `admin123`, entren con `admin` / `admin123`.

Las contraseñas se guardan cifradas, como lo hace Laravel con `Hash::make()`. Por eso en la tabla `usuarios` se ve un texto largo que empieza por `$2y$12$` en lugar de la contraseña. Los demás usuarios se crean desde la interfaz, con la cuenta de administrador.

---

## 7. Cómo usar el sistema

### Administrador
1. Inicia sesión con `admin`.
2. **Gestionar usuarios → + Nuevo usuario.** Escribe nombre, usuario, contraseña (mínimo 6 caracteres) y rol.
3. En la lista de usuarios puedes **Editar** (incluye activar o desactivar la cuenta y desbloquearla) o **Desactivar**. Las cuentas no se borran.
4. **Ver mesas** muestra el tablero. Desde **Ver pedidos** se supervisan y cancelan los pedidos pendientes. El administrador no registra comandas.

### Mesero
1. Inicia sesión con una cuenta de rol Mesero → **Ir a mesas**.
2. En la tarjeta de una mesa, **Nueva comanda**:
   - En cada recuadro "Plato" (hay uno por puesto de la mesa) elige **principio** y **proteína**, y si quieres acompañamientos, cantidad y observaciones. El arroz se agrega solo. Los recuadros vacíos no se envían.
   - En el menú fijo, escribe la cantidad de jugos, especiales o adicionales.
   - Si la mesa estaba reservada, marca "El cliente de la reserva ya llegó".
3. **Enviar a cocina:** el pedido queda *pendiente* y la mesa pasa a *ocupada*.
4. **Ver pedidos** muestra los pedidos de la mesa. Un pedido *pendiente* se puede **cancelar**; al cancelar el último pedido sin entregar, la mesa vuelve sola a *disponible*.
5. **Liberar** deja la mesa disponible solo si no tiene pedidos sin entregar.

### Comprobar en la base de datos
```sql
SELECT * FROM v_comandas_cocina;
SELECT id_usuario, nombre, usuario, id_rol, activo, bloqueado FROM usuarios;
```

---

## 8. Estructura del proyecto

El proyecto sigue el patrón **MVC** de Laravel:

```
app/
├── Http/Controllers/
│   ├── LoginController.php      Inicio de sesión, inicio por rol y cierre de sesión
│   ├── UsuarioController.php    CRUD de usuarios (solo administrador)
│   ├── MesaController.php       Tablero de mesas, reservar y liberar
│   └── PedidoController.php     Comanda, ver pedidos y cancelar
└── Models/
    ├── Rol.php                  tabla roles
    ├── Usuario.php              tabla usuarios
    ├── Mesa.php                 tabla mesas
    ├── Insumo.php               tabla insumos
    ├── Categoria.php            tabla categorias
    ├── Plato.php                tabla platos
    ├── Pedido.php               tabla pedidos
    └── DetallePedido.php        tabla detalle_pedido (+ detalle_pedido_insumo)

resources/views/
├── auth/login.blade.php         Inicio de sesión
├── inicio.blade.php             Página de inicio según el rol
├── usuarios/
│   ├── index.blade.php          Lista de usuarios
│   ├── create.blade.php         Nuevo usuario
│   └── edit.blade.php           Editar usuario
├── mesas/index.blade.php        Tablero de mesas
└── pedidos/
    ├── create.blade.php         Comanda de una mesa
    └── index.blade.php          Pedidos de una mesa

routes/web.php                   Rutas de la aplicación
```

**Características del código:**
- Todos los modelos usan `$table`, `$primaryKey = 'id_...'` y `public $timestamps = false`, porque las tablas no tienen las columnas `created_at` ni `updated_at`.
- Las validaciones se hacen en cada controlador con `$request->validate([...])`.
- El rol del usuario se guarda en la sesión al iniciar sesión (`session('rol')`), y cada método del controlador lo verifica antes de ejecutarse.
- La comanda se guarda dentro de `DB::transaction()`: si algo falla, no queda ningún pedido a medias.
- Cada vista es una página HTML completa con Bootstrap.

---

## 9. Rutas

| Método | Dirección | Controlador@método | Nombre |
|---|---|---|---|
| GET | `/` | redirige a `/login` | |
| GET | `/login` | LoginController@create | `login` |
| POST | `/login` | LoginController@store | `login.store` |
| POST | `/logout` | LoginController@destroy | `logout` |
| GET | `/inicio` | LoginController@inicio | `inicio` |
| GET | `/usuarios` | UsuarioController@index | `usuarios.index` |
| GET | `/usuarios/create` | UsuarioController@create | `usuarios.create` |
| POST | `/usuarios` | UsuarioController@store | `usuarios.store` |
| GET | `/usuarios/{usuario}/edit` | UsuarioController@edit | `usuarios.edit` |
| PUT | `/usuarios/{usuario}` | UsuarioController@update | `usuarios.update` |
| DELETE | `/usuarios/{usuario}` | UsuarioController@destroy (desactiva) | `usuarios.destroy` |
| GET | `/mesas` | MesaController@index | `mesas.index` |
| POST | `/mesas/{mesa}/reservar` | MesaController@reservar | `mesas.reservar` |
| POST | `/mesas/{mesa}/liberar` | MesaController@liberar | `mesas.liberar` |
| GET | `/mesas/{mesa}/comanda` | PedidoController@create | `pedidos.create` |
| POST | `/mesas/{mesa}/comanda` | PedidoController@store | `pedidos.store` |
| GET | `/mesas/{mesa}/pedidos` | PedidoController@index | `pedidos.index` |
| POST | `/pedidos/{pedido}/cancelar` | PedidoController@cancelar | `pedidos.cancelar` |
