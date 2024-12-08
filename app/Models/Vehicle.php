<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\FuncCall;

class Vehicle extends Model
{
    use HasFactory;
    protected $table = 'vehicles';
    protected $fillable = [
        'category_id',
        'customer_id',
        'plate_number',
        'no_lambung',
        'number_stiker',
        'stnk',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // public function permittedVehicle()
    // {
    //     return $this->hasMany(PermittedVehicle::class, 'vehicle_id', 'id');
    // }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
