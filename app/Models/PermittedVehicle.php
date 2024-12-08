<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermittedVehicle extends Model
{
    use HasFactory;
    protected $table = 'permitted_vehicle';
    protected $guarded = ['id'];

    // public function vehicle()
    // {
    //     return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    // }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function processImk()
    {
        return $this->belongsTo(ProcessImk::class, 'id_imk', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }
}
