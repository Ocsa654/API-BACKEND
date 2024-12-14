
## API DE USUARIOS Y MUSICA CCOA -EBDE
Este proyecto es una API RESTful desarrollada con Laravel para la gestión de usuarios y canciones. Ofrece tokens  para la autenticación de usuarios, administración igual un CRUD de perfiles y operaciones CRUD relacionadas con canciones.

## AUTENTICAZION  DE LA  API

- `/api/MasterTokenController`: Generar un token maestro

- Entrada: El cliente envía una secret_key a este endpoint.
- Validación: Verifica si la secret_key es válida.
- Usuario maestro:
- Crea o actualiza un usuario maestro utilizando datos predefinidos en las variables de entorno.
- Token: Genera un token de acceso para este usuario maestro.
- Respuesta: Devuelve el token maestro en caso de éxito o un mensaje de error en caso de fallo.


### SONG CONTROLLER

Gestiona las operaciones CRUD para la parte de las canciones.

#### Funciones principales:
- `index()`: Recupera y devuelve todas las canciones almacenadas en la base de datos.
            Si la operación es exitosa, responde con los datos de las canciones en formato JSON con un código HTTP 200.
            Si ocurre un error, registra el problema y devuelve un código HTTP 500.

```public function index()
{
    try {
        Log::info('Fetching all songs');
        $songs = Song::all();
        return response()->json($songs, 200);
    } catch (Exception $e) {
        Log::error('Error fetching songs: ' . $e->getMessage());
        return response()->json(['error' => 'Error fetching songs'], 500);
    }
}
```


### UsuarioController

Maneja las operaciones de gestión de usuarios.

#### Funciones principales:

- **`index()`**
  Lista todos los usuarios.

  ```php
  public function index()
  {
      $usuarios = Usuario::all();
      return response()->json($usuarios);
  }
  ```

- **`store(Request $request)`**
  Crea un nuevo usuario.

  ```php
  public function store(Request $request)
  {
      $request->validate([
          'nombre' => 'required|string|max:255',
          'apellido' => 'required|string|max:255',
          'correo_electronico' => 'required|string|email|max:255|unique:usuarios',
          'contraseña' => 'required|string|min:8',
          'fecha_nacimiento' => 'required|date',
      ]);

      $usuario = new Usuario($request->except('contraseña'));
      $usuario->contraseña = Hash::make($request->contraseña);

      $usuario->save();

      return response()->json($usuario, 201);
  }
  ```

- **`show(Usuario $usuario)`**
  Obtiene detalles de un usuario específico.

  ```php
  public function show(Usuario $usuario)
  {
      return response()->json($usuario);
  }
  ```

- **`update(Request $request, Usuario $usuario)`**
  Actualiza un usuario.

  ```php
  public function update(Request $request, Usuario $usuario)
  {
      $request->validate([
          'nombre' => 'sometimes|string|max:255',
          'apellido' => 'sometimes|string|max:255',
          'correo_electronico' => 'sometimes|string|email|max:255|unique:usuarios,correo_electronico,' . $usuario->id,
          'contraseña' => 'nullable|string|min:8',
          'fecha_nacimiento' => 'sometimes|date',
      ]);

      $data = $request->except('contraseña');
      if ($request->has('contraseña')) {
          $data['contraseña'] = Hash::make($request->contraseña);
      }

      $usuario->update($data);

      return response()->json($usuario);
  }
  ```

- **`destroy(Usuario $usuario)`**
  Elimina un usuario.

  ```php
  public function destroy(Usuario $usuario)
  {
      $usuario->delete();
      return response()->json(null, 204);
  }
  ```

### LoginController

Gestiona la autenticación de usuarios.

#### Funciones principales:

- **`login(Request $request)`**
  Autentica un usuario y devuelve un token.

  ```php
  public function login(Request $request)
  {
      $request->validate([
          'correo_electronico' => 'required|string|email',
          'contraseña' => 'required|string',
      ]);

      $usuario = Usuario::where('correo_electronico', $request->correo_electronico)->first();

      if (!$usuario || !Hash::check($request->contraseña, $usuario->contraseña)) {
          return response()->json(['error' => 'Credenciales inválidas'], 401);
      }

      $token = $usuario->createToken('auth_token')->plainTextToken;

      return response()->json(['token' => $token]);
  }
  ```

- **`logout(Request $request)`**
  Cierra sesión de un usuario.

  ```php
  public function logout(Request $request)
  {
      $request->user()->currentAccessToken()->delete();
      return response()->json(['message' => 'Sesión cerrada correctamente']);
  }
  ```

- **`user(Request $request)`**
  Obtiene información del usuario autenticado.

  ```php
  public function user(Request $request)
  {
      return response()->json($request->user());
  }
  


## Variables de Entorno

Asegúrate de configurar las siguientes variables de entorno en tu archivo `.env`:

- `DB_CONNECTION`: Tipo de conexión de base de datos (ej. mysql)
- `DB_HOST`: Host de la base de datos
- `DB_PORT`: Puerto de la base de datos
- `DB_DATABASE`: Nombre de la base de datos
- `DB_USERNAME`: Nombre de usuario de la base de datos
- `DB_PASSWORD`: Contraseña de la base de datos
- `MASTER_USER_EMAIL`: Correo electrónico para el usuario maestro
- `MASTER_USER_NAME`: Nombre para el usuario maestro
- `MASTER_USER_PASSWORD`: Contraseña para el usuario maestro
- `MASTER_SECRET_KEY`: Clave secreta para generar el token maestro

## Detalles de Implementación

### Rutas API (api.php)

Las rutas principales de la API están definidas en el archivo `routes/api.php`:

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UsuarioController;
use App\Http\Controllers\API\MasterTokenController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\PokemonController;

Route::post('/generate-master-token', [MasterTokenController::class, 'generateMasterToken']);
Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:sanctum')->get('/user', [LoginController::class, 'user']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('pokemonoes', PokemonController::class);
});
```

### Conclusión

Esta API RESTful basada en Laravel ofrece una solución robusta y escalable para la gestión de usuarios y datos de Pokémon. Con características como autenticación mediante tokens JWT, carga de archivos, y un sistema de gestión de datos bien estructurado, proporciona una base sólida para proyectos futuros. 

El diseño modular de los controladores, combinado con el uso de migraciones y variables de entorno, asegura una fácil personalización y adaptabilidad a diferentes necesidades. Este proyecto demuestra el poder de Laravel como un framework versátil para construir aplicaciones web modernas y seguras.