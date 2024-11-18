<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $table = 'payments';
    protected $fillable = [
        'user_id',
        'id_customer',
        'id_imk',
        'pay_date',
        'amount_pay',
        'status_pay',
        'name_pay',
        'redirect_url',
        'order_id',
        'note_pay',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function process_imk()
    {
        return $this->belongsTo(ProcessImk::class, 'id_imk');
    }
}
