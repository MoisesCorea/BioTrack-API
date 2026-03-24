<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use Illuminate\Support\Facades\Validator; 

class RolesController extends Controller
{
    public function index()
    {
        $roles = Roles::all();
        return $this->successResponse($roles);
    }

    public function show($id){
        $rol = Roles::find($id);

        if (!$rol) {
            return $this->errorResponse('Role not found', 404);
        }

        return $this->successResponse($rol);
    }

    public function store(\App\Http\Requests\StoreRoleRequest $request)
    {
        $rol = Roles::create([
            'name' => $request->name,
            'description'=> $request->description,
            'guard_name' => 'web'
        ]);

        return $this->successResponse($rol, 'Role created successfully', 201);
    }

    public function update(\App\Http\Requests\UpdateRoleRequest $request, $id)
    {
        $rol = Roles::find($id);

        if (!$rol) {
            return $this->errorResponse('Role not found', 404);
        }

        $rol->name = $request->name;
        $rol->description =$request->description;
        $rol->save();

        return $this->successResponse($rol, 'Role updated successfully');
    }

    public function destroy($id)
    {
        $rol = Roles::find($id);

        if (!$rol) {
            return $this->errorResponse('Role not found', 404);
        }
        
        $rowsAffected = Roles::destroy($id);

        return $this->successResponse(['affected' => $rowsAffected], 'Role deleted successfully');
    }

    
}
