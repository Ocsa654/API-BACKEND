<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MasterTokenController extends Controller
{
    public function generateMasterToken(Request $request)
    {
        Log::info('Iniciando generateMasterToken');

        $request->validate([
            'secret_key' => 'required|string',
        ]);

        Log::info('Secret key recibida', ['secret_key' => $request->secret_key]);

        if ($request->secret_key !== config('app.master_secret_key')) {
            Log::warning('Secret key inválida');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $masterEmail = env('MASTER_USER_EMAIL');
        $masterName = env('MASTER_USER_NAME');
        $masterPassword = env('MASTER_USER_PASSWORD');

        Log::info('Variables de entorno', [
            'MASTER_USER_EMAIL' => $masterEmail,
            'MASTER_USER_NAME' => $masterName,
            'MASTER_USER_PASSWORD' => 'REDACTED'
        ]);

        if (!$masterEmail || !$masterName || !$masterPassword) {
            Log::error('Faltan variables de entorno necesarias');
            return response()->json(['error' => 'Configuración incompleta'], 500);
        }

        try {
            $masterUser = Usuario::updateOrCreate(
                ['correo_electronico' => $masterEmail],
                [
                    'nombre' => $masterName,
                    'apellido' => 'Master',
                    'contraseña' => Hash::make($masterPassword),
                    'fecha_nacimiento' => now()->subYears(30),
                ]
            );

            Log::info('Usuario maestro creado/actualizado', ['id' => $masterUser->id]);

            $token = $masterUser->createToken('master_token', ['*'])->plainTextToken;

            Log::info('Token maestro generado');

            return response()->json(['master_token' => $token]);
        } catch (\Exception $e) {
            Log::error('Error al crear/actualizar usuario maestro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}

