
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


- `store()`: Crea una nueva canción en la base de datos.
             Si se incluye una portada, la guarda en el almacenamiento público y asigna la URL al registro.
             Guarda los datos en la base de datos y devuelve la nueva canción en formato JSON con un código HTTP 201.
             Maneja errores de validación (HTTP 422) y errores generales (HTTP 500).

```
public function store(Request $request)
{
    Log::info('Starting creation of new song', $request->all());

    try {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'album' => 'required|string|max:255',
            'duration' => 'required|integer',
            'cover_art' => 'nullable|image|max:2048',
        ]);

        Log::info('Data validation successful');

        $song = new Song($validatedData);

        if ($request->hasFile('cover_art')) {
            Log::info('Processing cover art image');
            $path = $request->file('cover_art')->store('covers', 'public');
            $song->cover_art_url = Storage::url($path);
            Log::info('Cover art image saved', ['path' => $song->cover_art_url]);
        }

        $song->save();
        Log::info('Song saved successfully', ['song_id' => $song->id]);

        return response()->json($song, 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error', ['errors' => $e->errors()]);
        return response()->json(['error' => 'Validation error', 'details' => $e->errors()], 422);
    } catch (Exception $e) {
        Log::error('Error creating song', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Error creating song', 'details' => $e->getMessage()], 500);
    }
}
```

- `show()`:Busca y devuelve una canción específica por su ID en formato JSON.
           Si no encuentra la canción, devuelve un código HTTP 404 con un mensaje de error.

```
public function show($id)
{
    try {
        Log::info('Fetching song', ['id' => $id]);
        $song = Song::findOrFail($id);
        return response()->json($song);
    } catch (Exception $e) {
        Log::error('Error fetching song: ' . $e->getMessage(), ['id' => $id]);
        return response()->json(['error' => 'Song not found'], 404);
    }
}
```
- `update()`:Actualiza los datos de una canción y procesa la portada si es necesario.
           Si no encuentra la canción, devuelve un código HTTP 404 con un mensaje de error.

```
public function update(Request $request, $id)
{
    try {
        $song = Song::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'artist' => 'sometimes|string|max:255',
            'album' => 'sometimes|string|max:255',
            'duration' => 'sometimes|integer',
            'cover_art' => 'nullable|image|max:2048',
        ]);

        $song->fill($validatedData);

        if ($request->hasFile('cover_art')) {
            if ($song->cover_art_url) {
                Storage::delete(str_replace('/storage/', 'public/', $song->cover_art_url));
            }
            $path = $request->file('cover_art')->store('covers', 'public');
            $song->cover_art_url = Storage::url($path);
        }

        $song->save();
        return response()->json($song);
    } catch (Exception $e) {
        return response()->json(['error' => 'Error updating song'], 500);
    }
}
```
- `destroy()`:Elimina la canción y su portada.
```
public function destroy($id)
{
    try {
        $song = Song::findOrFail($id);

        if ($song->cover_art_url) {
            Storage::delete(str_replace('/storage/', 'public/', $song->cover_art_url));
        }

        $song->delete();
        return response()->json(null, 204);
    } catch (Exception $e) {
        return response()->json(['error' => 'Error deleting song'], 500);
    }
}
```

### CONTROLADOR DE USUARIOS

Maneja las operaciones de gestión de usuarios.

#### Funciones principales:

- **`index()`**
  Lista todos los usuarios.

  ```
  public function index()
  {
      $usuarios = Usuario::all();
      return response()->json($usuarios);
  }
  ```

- **`store()`**
  Crea un nuevo usuario.

  ```
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

- **`show()`**
  Obtiene detalles de un usuario específico.

  ```
  public function show(Usuario $usuario)
  {
      return response()->json($usuario);
  }
  ```

- **`update()`**
  Actualiza un usuario.

  ```
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

- **`destroy()`**
  Elimina un usuario.

  ```
  public function destroy(Usuario $usuario)
  {
      $usuario->delete();
      return response()->json(null, 204);
  }
  ```



## CONFIGURACION DE LA BASE DE DATOS

Asegúrate de configurar las siguientes variables de entorno en tu archivo `.env`:
- `DB_CONNECTION`=mysql el tipo de la conexion
- `DB_HOST`=127.0.0.1 el host de la base de datos
- `DB_PORT`=3306 puerto de la base de datos
- `DB_DATABASE`=apis  nombre de la base de datos
- `DB_USERNAME`=root  nombre del usuario de la base de datos
- `DB_PASSWORD`=123456 contrasena de la base de datos


## Configuracion para el uso de la API con la identificacion del token
- `MASTER_USER_EMAIL`: Correo electrónico para el usuario maestro
- `MASTER_USER_NAME`: Nombre para el usuario maestro
- `MASTER_USER_PASSWORD`: Contraseña para el usuario maestro
- `MASTER_SECRET_KEY`: Clave secreta para generar el token maestro


### Rutas

  ```
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UsuarioController;
use App\Http\Controllers\API\MasterTokenController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\SongController; 

Route::post('/generate-master-token', [MasterTokenController::class, 'generateMasterToken']);

Route::post('/login', [LoginController::class, 'login']);  // Ruta para login
Route::middleware('auth:sanctum')->get('/user', [LoginController::class, 'user']);  

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('musica', SongController::class);
});
  ```

Generar token maestro: Ruta POST /generate-master-token que llama al método generateMasterToken para crear un token maestro.
Login: Ruta POST /login para autenticar usuarios y GET /user (con autenticación) para obtener los datos del usuario autenticado.
Rutas protegidas: Las rutas usuarios y musica están protegidas por autenticación (auth:sanctum) y permiten realizar operaciones CRUD sobre usuarios y canciones, respectivamente.