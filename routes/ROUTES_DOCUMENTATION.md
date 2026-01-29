# Documentación del Sistema de Rutas API

## 📋 Índice

1. [Estructura General](#estructura-general)
2. [Archivo Principal](#archivo-principal)
3. [Carpetas de Rutas](#carpetas-de-rutas)
4. [Organización de Archivos](#organización-de-archivos)
5. [Ejemplos de Uso](#ejemplos-de-uso)
6. [Mejores Prácticas](#mejores-prácticas)

---

## 🏗️ Estructura General

El sistema de rutas está organizado de manera modular en la siguiente estructura:

```
routes/
├── api.php                    # Archivo principal que carga todas las rutas
└── api/
    ├── public/                # Rutas públicas (sin autenticación)
    │   └── auto.php          # Archivo principal de rutas públicas
    ├── semiauth/             # Rutas con autenticación opcional
    │   └── auto.php          # Archivo principal de rutas semiauth
    ├── auth/                 # Rutas que requieren autenticación
    │   └── auto.php          # Archivo principal de rutas autenticadas
    └── admin/                # Rutas administrativas (requieren rol admin)
        └── auto.php          # Archivo principal de rutas admin
```

---

## 📄 Archivo Principal

### `routes/api.php`

Este es el archivo principal que Laravel carga automáticamente. Utiliza `glob()` para cargar dinámicamente todos los archivos PHP de cada carpeta.

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Carga todas las rutas públicas (sin autenticación)
    foreach (glob(__DIR__.'/api/public/*.php') as $file) { 
        require $file; 
    }

    // Carga rutas con autenticación opcional
    Route::middleware('optional.auth')->group(function () {
        foreach (glob(__DIR__.'/api/semiauth/*.php') as $file) { 
            require $file; 
        }
    });

    // Carga rutas que requieren autenticación
    Route::middleware('auth:sanctum')->group(function () {
        foreach (glob(__DIR__.'/api/auth/*.php') as $file) { 
            require $file; 
        }
    });

    // Carga rutas administrativas (requieren autenticación + rol admin)
    Route::middleware(['auth:sanctum', 'can:admin'])
        ->prefix('admin')
        ->as('admin.')
        ->group(function () {
            foreach (glob(__DIR__.'/api/admin/*.php') as $file) { 
                require $file; 
            }
        });
});
```

### Características Clave:

- **Carga automática**: Todos los archivos `.php` dentro de cada carpeta se cargan automáticamente
- **Orden de carga**: Las rutas se cargan en el orden especificado (public → semiauth → auth → admin)
- **Prefijos**: Las rutas admin tienen el prefijo `/admin` automáticamente
- **Nombres de rutas**: Las rutas admin tienen el prefijo `admin.` en sus nombres

---

## 📁 Carpetas de Rutas

### 1. `routes/api/public/` - Rutas Públicas

**Middleware**: Ninguno (públicas)

**Uso**: Rutas accesibles sin autenticación.

**Ejemplos de rutas que deben estar aquí:**
- Login (`POST /login`)
- Registro (`POST /register`)
- Recuperación de contraseña (`GET /recover-pass`)
- Verificación de email (`POST /email-verification/code`)
- Búsqueda de tours públicos
- Información de destinos públicos

**Estructura actual:**
```
public/
└── auto.php    # Contiene todas las rutas públicas organizadas por funcionalidad
```

### 2. `routes/api/semiauth/` - Rutas con Autenticación Opcional

**Middleware**: `optional.auth`

**Uso**: Rutas que funcionan tanto para usuarios autenticados como no autenticados, pero pueden comportarse diferente según el estado de autenticación.

**Ejemplos:**
- Búsquedas que muestran más información si el usuario está autenticado
- Listados que personalizan contenido según el usuario
- Recursos que permiten acceso limitado sin autenticación

**Estructura actual:**
```
semiauth/
└── auto.php    # Contiene rutas con autenticación opcional
```

### 3. `routes/api/auth/` - Rutas Autenticadas

**Middleware**: `auth:sanctum`

**Uso**: Rutas que requieren que el usuario esté autenticado.

**Ejemplos de rutas que deben estar aquí:**
- Logout (`POST /logout`)
- Cambio de contraseña (`POST /change-password`)
- Perfil de usuario (`GET /users`)
- Wishlist (`GET /wishlists`)
- Órdenes del usuario (`GET /orders`)
- Gestión de viajeros (`POST /write-travelers`)

**Estructura actual:**
```
auth/
└── auto.php    # Contiene rutas que requieren autenticación
```

### 4. `routes/api/admin/` - Rutas Administrativas

**Middleware**: `auth:sanctum` + `can:admin`

**Prefijo de URL**: `/admin`

**Prefijo de nombre**: `admin.`

**Uso**: Rutas exclusivas para administradores del sistema.

**Ejemplos de rutas que deben estar aquí:**
- Gestión de usuarios del sistema (`POST /admin/add-users`)
- Reportes administrativos (`POST /admin/admin-reports`)
- Gestión de operadores (`GET /admin/operators`)
- Logs del sistema (`GET /admin/action-logs`)

**Nota**: Todas las rutas en esta carpeta automáticamente tienen:
- Prefijo `/admin` en la URL (ej: `/admin/users` en lugar de `/users`)
- Prefijo `admin.` en el nombre de la ruta (ej: `admin.users.index`)

**Estructura actual:**
```
admin/
└── auto.php    # Contiene rutas administrativas
```

---

## 📂 Organización de Archivos

### Separación por Funcionalidad

Puedes crear múltiples archivos dentro de cada carpeta para organizar mejor las rutas. El sistema carga **automáticamente todos los archivos `.php`** dentro de cada carpeta.

#### Ejemplo de Estructura Recomendada:

```
routes/api/
├── public/
│   ├── auth.php          # Rutas de autenticación y registro
│   ├── destinations.php  # Rutas de destinos públicos
│   ├── tours.php         # Rutas de tours públicos
│   └── flights.php       # Rutas de vuelos públicos
│
├── semiauth/
│   └── search.php        # Búsquedas con autenticación opcional
│
├── auth/
│   ├── profile.php       # Perfil de usuario
│   ├── orders.php        # Órdenes del usuario
│   ├── wishlist.php      # Lista de deseos
│   └── travelers.php     # Gestión de viajeros
│
└── admin/
    ├── users.php         # Gestión de usuarios
    ├── reports.php       # Reportes administrativos
    └── system.php        # Configuración del sistema
```

### Ventajas de Separar en Múltiples Archivos:

1. **Organización**: Cada archivo se enfoca en una funcionalidad específica
2. **Mantenibilidad**: Más fácil encontrar y modificar rutas relacionadas
3. **Colaboración**: Múltiples desarrolladores pueden trabajar en diferentes archivos sin conflictos
4. **Escalabilidad**: El proyecto puede crecer sin que un solo archivo se vuelva enorme

### Ejemplo de Archivo Separado:

**`routes/api/public/auth.php`**
```php
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// AUTENTICACIÓN Y REGISTRO
// ============================================================================

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('google-register', [AuthController::class, 'googleRegister']);
Route::get('/recover-pass', [AuthController::class, 'recoverPass']);
Route::get('/check-token-pass', [AuthController::class, 'checkToken']);
```

**`routes/api/public/destinations.php`**
```php
<?php

use App\Http\Controllers\Citycontroller;
use App\Http\Controllers\CountryController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// DESTINOS Y UBICACIONES
// ============================================================================

Route::resource('cities', Citycontroller::class);
Route::resource('countries', CountryController::class);
Route::get('get-destinations', [Citycontroller::class, 'destinations']);
```

---

## 💡 Ejemplos de Uso

### Ejemplo 1: Agregar una Nueva Ruta Pública

**Opción A: Agregar al archivo existente**
```php
// routes/api/public/auto.php
Route::get('/nueva-ruta-publica', [Controller::class, 'metodo']);
```

**Opción B: Crear un nuevo archivo**
```php
// routes/api/public/nuevas-rutas.php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NuevoController;

Route::get('/nueva-ruta-publica', [NuevoController::class, 'metodo']);
```

### Ejemplo 2: Agregar una Nueva Ruta Autenticada

```php
// routes/api/auth/profile.php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/mi-perfil', [UserController::class, 'miPerfil']);
Route::put('/actualizar-perfil', [UserController::class, 'actualizarPerfil']);
```

### Ejemplo 3: Agregar una Nueva Ruta Administrativa

```php
// routes/api/admin/users.php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemUserController;

// Esta ruta será accesible en: /admin/gestionar-usuarios
// Y tendrá el nombre: admin.gestionar-usuarios
Route::get('/gestionar-usuarios', [SystemUserController::class, 'gestionar']);
```

---

## ✅ Mejores Prácticas

### 1. Organización por Funcionalidad

Agrupa las rutas relacionadas en el mismo archivo o sección:

```php
// ✅ Bien - Rutas agrupadas por funcionalidad
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// ❌ Evitar - Rutas mezcladas sin organización
Route::post('login', [AuthController::class, 'login']);
Route::get('tours', [TourController::class, 'index']);
Route::post('register', [AuthController::class, 'register']);
```

### 2. Uso de Comentarios

Utiliza comentarios para separar secciones y mejorar la legibilidad:

```php
// ============================================================================
// AUTENTICACIÓN Y REGISTRO
// ============================================================================

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
```

### 3. Nombres Descriptivos

Usa nombres de archivos que describan su contenido:

```
✅ auth.php
✅ destinations.php
✅ orders.php

❌ routes1.php
❌ misc.php
❌ temp.php
```

### 4. Ubicación Correcta de Rutas

Asegúrate de colocar las rutas en la carpeta correcta según su nivel de acceso:

| Tipo de Ruta | Carpeta | Requiere Auth | Requiere Admin |
|--------------|---------|---------------|----------------|
| Login, Registro | `public/` | ❌ | ❌ |
| Búsquedas públicas | `public/` | ❌ | ❌ |
| Contenido personalizado | `semiauth/` | ⚠️ Opcional | ❌ |
| Perfil de usuario | `auth/` | ✅ | ❌ |
| Órdenes del usuario | `auth/` | ✅ | ❌ |
| Gestión de usuarios | `admin/` | ✅ | ✅ |
| Reportes administrativos | `admin/` | ✅ | ✅ |

### 5. Evitar Duplicación

No dupliques rutas en múltiples archivos. Si una ruta necesita estar en múltiples contextos, considera usar `semiauth/` o crear rutas específicas.

### 6. Mantener Archivos Pequeños

Si un archivo supera las 200-300 líneas, considera dividirlo en múltiples archivos por funcionalidad.

---

## 🔍 Cómo Funciona la Carga Automática

1. Laravel carga `routes/api.php` automáticamente
2. `api.php` ejecuta `glob()` para encontrar todos los archivos `.php` en cada carpeta
3. Cada archivo encontrado se carga con `require`
4. Las rutas se registran en el orden en que se encuentran los archivos
5. Los middlewares se aplican según la carpeta donde está el archivo

### Orden de Carga:

```
1. routes/api/public/*.php      (sin middleware de auth)
2. routes/api/semiauth/*.php     (con optional.auth)
3. routes/api/auth/*.php         (con auth:sanctum)
4. routes/api/admin/*.php         (con auth:sanctum + can:admin)
```

---

## 🚀 Migración de Rutas Existentes

Si tienes rutas en `routes/api/public/auto.php` y quieres separarlas:

1. **Identifica las funcionalidades** (auth, destinations, tours, etc.)
2. **Crea nuevos archivos** por funcionalidad
3. **Mueve las rutas** del archivo grande a los nuevos archivos
4. **Mantén los imports** necesarios en cada archivo
5. **Prueba** que todas las rutas funcionen correctamente

### Ejemplo de Migración:

**Antes:**
```
routes/api/public/auto.php (500+ líneas)
```

**Después:**
```
routes/api/public/
├── auth.php (50 líneas)
├── destinations.php (80 líneas)
├── tours.php (100 líneas)
├── flights.php (70 líneas)
└── ...
```

---

## 📝 Notas Importantes

1. **Todos los archivos PHP se cargan**: Cualquier archivo `.php` dentro de una carpeta será cargado automáticamente
2. **Orden alfabético**: Los archivos se cargan en orden alfabético dentro de cada carpeta
3. **No hay límite de archivos**: Puedes crear tantos archivos como necesites
4. **Imports necesarios**: Cada archivo debe tener sus propios `use` statements
5. **Prefijos automáticos**: Las rutas en `admin/` tienen prefijo `/admin` automáticamente

---

## 🐛 Troubleshooting

### Problema: Las rutas no se cargan

**Solución**: Verifica que:
- El archivo tiene extensión `.php`
- El archivo está dentro de la carpeta correcta
- No hay errores de sintaxis en el archivo
- Los imports están correctos

### Problema: Middleware no se aplica

**Solución**: Verifica que:
- El archivo está en la carpeta correcta (`auth/` para autenticación, `admin/` para admin)
- El middleware está definido en `routes/api.php`

### Problema: Rutas duplicadas

**Solución**: Busca si la misma ruta está definida en múltiples archivos y elimina las duplicadas.

---

## 📚 Recursos Adicionales

- [Documentación de Laravel Routing](https://laravel.com/docs/routing)
- [Laravel Sanctum Authentication](https://laravel.com/docs/sanctum)
- [Laravel Authorization](https://laravel.com/docs/authorization)

---

**Última actualización**: Diciembre 2024
