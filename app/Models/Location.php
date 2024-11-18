<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table = 'locations';
    protected $guarded = ['id'];

    public function process_imk()
    {
        return $this->hasMany(ProcessImk::class, 'location_id');
    }

    public function permitted_vehicle()
    {
        return $this->hasMany(PermittedVehicle::class, 'id');
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class, 'location_id');
    }
}
