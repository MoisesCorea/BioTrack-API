<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Users;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Str;


class UsersController extends Controller
{
    public function index()
    {
        $users = Users::all()->map(function ($user) {
            $user->profile_image = asset('/storage/images/profiles/' . $user->profile_image); 
            return $user;
        });
        return $this->successResponse($users);
    }

    public function show(string $id) {
        $user = Users::find($id);
       
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $user->profile_image = asset('/storage/images/profiles/' . $user->profile_image); 
      
        return $this->successResponse($user);
      }
    

      public function store(\App\Http\Requests\StoreUserRequest $request)
      {
          do {
              $id = 'qr-' . Str::random(7);
          } while (Users::where('id', $id)->exists());
          
          $profileImage = $request->file('profile_image');
          $extension = $profileImage->getClientOriginalExtension();
          $profileImageName = 'img-'.$id. '.'. $extension;
      
          $user = Users::create([
              'id' =>  $id,
              'name' =>  $request->name,
              'last_name' =>  $request->last_name,
              'age' =>  $request->age,
              'gender' =>  $request->gender,
              'email' =>  $request->email,
              'address' =>  $request->address,
              'phone_number' =>  $request->phone_number,
              'profile_image' => $profileImageName,
              'shift_id' => $request->shift_id,
              'department_id' =>  $request->department_id,
              'status' => $request->status
          ]);
      
          $profileImage->storeAs('public/images/profiles', $profileImageName);
          return $this->successResponse($user, 'User created successfully', 201);
      }
      

       
        public function update(\App\Http\Requests\UpdateUserRequest $request, $id)
    {
        $user = Users::where('id', $id)->first();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if($request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image');
            $extension = $profileImage->getClientOriginalExtension();
            $profileImageName = 'img-'.$user->id. '.'. $extension;

            Storage::disk('public')->delete('images/profiles/' . $user->profile_image);
            $profileImage->storeAs('public/images/profiles', $profileImageName);
            $user->profile_image = $profileImageName;
        }

        $user->name = $request->name;
        $user->last_name  = $request->last_name;
        $user->age  = $request->age;
        $user->gender  = $request->gender;
        $user->email  = $request->email;
        $user->address  = $request->address;
        $user->phone_number  = $request->phone_number;
        $user->shift_id  = $request->shift_id;
        $user->department_id  = $request->department_id ;
        $user->status  = $request->status;
        $user->save();

        return $this->successResponse($user, 'User updated successfully', 201);
    }

 


    public function destroy($id)
    {
        $user = Users::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $rowsAffected = Users::destroy($id);

        $profileImageName = $user->profile_image;

        Storage::disk('public')->delete('images/profiles/' . $profileImageName);

        return $this->successResponse(['affected' => $rowsAffected], 'User deleted successfully', 201);
    }
}
