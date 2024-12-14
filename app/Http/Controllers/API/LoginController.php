<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'correo_electronico' => 'required|email',
            'contraseña' => 'required',
        ]);

        // Buscar el usuario por correo electrónico
        $usuario = Usuario::where('correo_electronico', $request->correo_electronico)->first();

        // Validar las credenciales
        if (!$usuario || !Hash::check($request->contraseña, $usuario->contraseña)) {
            throw ValidationException::withMessages([
                'correo_electronico' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Generar el token de acceso
        $token = $usuario->createToken('auth_token')->plainTextToken;

        // Responder con el token y los datos del usuario
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'usuario' => $usuario,
        ]);
    }

    public function logout(Request $request)
    {
        // Eliminar el token de acceso actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function user(Request $request)
    {
        // Devolver la información del usuario autenticado
        return response()->json($request->user());
    }
}
