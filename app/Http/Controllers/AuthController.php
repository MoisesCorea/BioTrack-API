<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admins; 
use Illuminate\Support\Facades\Validator; 

class AuthController extends Controller
{
    /**
     * Inicia sesión emitiendo un token y devolviendo datos del usuario junto con su rol.
     */
    public function login(\App\Http\Requests\LoginRequest $request)
    {
        $user = Admins::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken("auth_token");
            
            // Usamos Spatie para obtener el nombre del primer rol asignado
            $roleName = $user->getRoleNames()->first();
            
            return $this->successResponse([
                'user_id' => $user->id,
                'access_token' => $token->plainTextToken,
                'rol' => $roleName
            ], 'Successful login');
        }

        return $this->errorResponse('Invalid login credentials', 401);
    }

    /**
     * Cierra la sesión activa actual revocando el token utilizado.
     */
    public function logout(Request $request) 
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Successful logout');
    }

    /**
     * Cambia la contraseña del usuario autenticado en sesión.
     */
    public function changePassword(\App\Http\Requests\ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect', 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->successResponse(null, 'Password updated successfully');
    }
}
