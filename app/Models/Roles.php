<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Roles extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'guard_name'
    ];

    public function admins()
    {
        return $this->hasMany(Admins::class, 'rol_id');
    }

}
