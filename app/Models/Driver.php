<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;
    protected $table = 'drivers';
    protected $fillable = [
        'name_driver',
        'sim'
    ];

    public function permittedVehicles()
    {
        return $this->hasMany(PermittedVehicle::class);
    }
}
