<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator; 
use App\Models\Departments;

class DepartmentsController extends Controller
{
    public function index()
    {
        $departments = Departments::all();
        return $this->successResponse($departments);
    }

    public function show($id){
        $department = Departments::find($id);

        if (!$department) {
            return $this->errorResponse('Department not found', 404);
        }

        return $this->successResponse($department);
    }

    public function store(\App\Http\Requests\StoreDepartmentRequest $request)
    {
        $department = Departments::create([
            'name' => $request->name,
            'description'=> $request->description
        ]);

        return $this->successResponse($department, 'Department created successfully', 201);
    }

    public function update(\App\Http\Requests\UpdateDepartmentRequest $request, $id)
    {
        $department = Departments::find($id);

        if (!$department) {
            return $this->errorResponse('Department not found', 404);
        }

        $department->name = $request->name;
        $department->description =$request->description;
        $department->save();

        return $this->successResponse($department, 'Department updated successfully');
    }

    public function destroy($id)
    {
        $department  = Departments::find($id);

        if (!$department ) {
            return $this->errorResponse('Department not found', 404);
        }
        
        $rowsAffected = Departments::destroy($id);

        return $this->successResponse(['affected' => $rowsAffected], 'Department deleted successfully');
    }

}
