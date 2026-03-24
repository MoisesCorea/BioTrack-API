<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Roles;
use Illuminate\Support\Facades\Auth;



class VerifyRol

{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     public function handle(Request $request, Closure $next, ...$roles)
     {
         // Verificar si el usuario está autenticado con Sanctum
         if (Auth::guard('sanctum')->check()) {
             // Obtener el usuario autenticado
             $user = Auth::guard('sanctum')->user();
             
             // Verificar si el usuario tiene alguno de los roles especificados usando Spatie
             if ($user->hasAnyRole($roles)) {
                 return $next($request);
             }
             
             // Si el usuario no tiene el rol requerido, responder con un error
             return response()->json([
                 'message' => 'No tienes permiso para acceder a esta ruta',
                 'statusCode' => 403
             ], 403);
         }
         
         // Si el usuario no está autenticado, responder con un error
         return response()->json([
             'message' => 'Debe iniciar sesión para acceder a esta ruta',
             'statusCode' => 401
         ], 401);
     }
    
}
