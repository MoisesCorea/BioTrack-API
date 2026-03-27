<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    public function index()
    {
        // Spatie Permission model
        $permissions = Permission::all();
        return $this->successResponse($permissions);
    }
}
