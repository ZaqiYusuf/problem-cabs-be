<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessImk extends Model
{
    use HasFactory;
    protected $table = 'process_imk';
    protected $guarded = ['id'];

    // public function periode()
    // {
    //     return $this->belongsTo(Periode::class, 'id_periode');
    // }

    // public function customer()
    // {
    //     return $this->belongsTo(Customer::class, 'customer_id', 'id');
    // }

    // public function customer()
    // {
    //     return $this->hasMany(Customer::class, 'id');
    // }

    public function customer()
{
    return $this->belongsTo(Customer::class, 'customer_id', 'id'); // Sesuaikan foreign key
}

public function payment()
{
    return $this->hasMany(Payment::class, 'id_imk', 'id');
}


    public function vehicles()
    {
        return $this->hasMany(PermittedVehicle::class, 'id_imk', 'id');
    }

    // public function permittedVehicle()
    // {
    //     return $this->hasMany(PermittedVehicle::class, 'vehicle_id', 'id');
    // }

    public function personnels()
    {
        return $this->hasMany(Personnel::class, 'id_imk', 'id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'id');
    }


}
