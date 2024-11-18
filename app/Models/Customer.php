<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customers';
    protected $fillable = [
        'user_id',
        'name_customer',
        'address',
        'email',
        'pic',
        'pic_number',
        'upload_file',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // public function process_imk()
    // {
    //     return $this->hasMany(ProcessImk::class);
    // }

    public function process_imk()
    {
        return $this->belongsTo(ProcessImk::class, 'customer_id', 'id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
