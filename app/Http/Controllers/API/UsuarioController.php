<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::all();
        return response()->json($usuarios);
    }

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

        if ($request->hasFile('imagen_perfil')) {
            $path = $request->file('imagen_perfil')->store('perfiles', 'public');
            $usuario->url_imagenPerfil = Storage::url($path);
            Log::info('Nueva imagen de perfil guardada: ' . $usuario->url_imagenPerfil);
        }

        $usuario->save();

        return response()->json($usuario, 201);
    }

    public function show(Usuario $usuario)
    {
        return response()->json($usuario);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'apellido' => 'sometimes|string|max:255',
            'correo_electronico' => 'sometimes|string|email|max:255|unique:usuarios,correo_electronico,' . $usuario->id,
            'contraseña' => 'nullable|string|min:8',
            'fecha_nacimiento' => 'sometimes|date',
        ]);

        $data = $request->except(['contraseña', 'imagen_perfil']);

        

        if ($request->hasFile('imagen_perfil')) {
            Log::info('Actualizando imagen de perfil para usuario ID: ' . $usuario->id);

            // Eliminar la imagen antigua
            $this->deleteOldImage($usuario->url_imagenPerfil);

            // Subir la nueva imagen
            $path = $request->file('imagen_perfil')->store('perfiles', 'public');
            $data['url_imagenPerfil'] = Storage::url($path);
            Log::info('Nueva imagen subida: ' . $data['url_imagenPerfil']);
        }

        $usuario->fill($data);
        $usuario->save();

        Log::info('Usuario actualizado', ['id' => $usuario->id, 'data' => $data]);

        return response()->json($usuario);
    }

    public function destroy(Usuario $usuario)
    {
        Log::info('Eliminando usuario ID: ' . $usuario->id);

        // Eliminar la imagen de perfil
        $this->deleteOldImage($usuario->url_imagenPerfil);

        // Eliminar el usuario
        $usuario->delete();
        Log::info('Usuario eliminado de la base de datos');

        return response()->json(null, 204);
    }

    private function deleteOldImage($imagePath)
    {
        if ($imagePath) {
            // Extraer solo la parte del path que necesitamos, eliminando la URL base
            $oldPath = parse_url($imagePath, PHP_URL_PATH); // Esto extraerá solo la parte del path
            $oldPath = str_replace('/storage/', '', $oldPath); // Eliminar /storage/ del inicio

            Log::info('Intentando eliminar imagen con path limpio: ' . $oldPath);

            try {
                if (Storage::disk('public')->exists($oldPath)) {
                    $deleted = Storage::disk('public')->delete($oldPath);
                    if ($deleted) {
                        Log::info('Imagen eliminada con éxito: ' . $oldPath);
                    } else {
                        Log::error('No se pudo eliminar la imagen: ' . $oldPath);
                    }
                } else {
                    Log::warning('Imagen no encontrada para eliminar: ' . $oldPath);
                }
            } catch (\Exception $e) {
                Log::error('Error al eliminar la imagen: ' . $e->getMessage());
                Log::error('Traza de la excepción: ' . $e->getTraceAsString());
            }
        }
    }
}