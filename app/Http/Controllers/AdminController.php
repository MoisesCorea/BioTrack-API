<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admins;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Lista todos los administradores.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = Admins::with('roles')->get();
        return $this->successResponse($users);
    }

    /**
     * Muestra la información de un administrador específico.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Admins::find($id);
    
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }
        return $this->successResponse($user);
    }

    /**
     * Crea un nuevo administrador.
     */
 public function store(\App\Http\Requests\StoreAdminRequest $request)
    {
        $user = Admins::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'alias' => $request->alias,
            'password' => Hash::make($request->password),
        ]); 
        
        if ($request->has('rol_id')) {
            $role = \Spatie\Permission\Models\Role::find($request->rol_id);
            if ($role) {
                $user->assignRole($role);
            }
        }
    
        return $this->successResponse($user, 'User created successfully', 201);
    }

    /**
     * Actualiza la información de un administrador existente.
     */
    public function update(\App\Http\Requests\UpdateAdminRequest $request, $id)
    {
        $user = Admins::find($id);

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->alias = $request->alias;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->has('rol_id')) {
            $role = \Spatie\Permission\Models\Role::find($request->rol_id);
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }

        return $this->successResponse($user, 'User updated successfully');
    }

    /**
     * Elimina un administrador de la base de datos.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = Admins::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $rowsAffected = Admins::destroy($id);

        return $this->successResponse(['affected' => $rowsAffected], 'User deleted successfully');
    }
}
