<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $table = 'packages';
    protected $guarded = ['id'];

    public function permitted_vehicle()
    {
        return $this->hasMany(PermittedVehicle::class, 'package_id');
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class, 'package_id');
    }
}
