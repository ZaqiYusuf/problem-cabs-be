<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;
    protected $table = 'tenants';
    protected $fillable = [
        'name_tenant',
    ];

    public function processimk()
    {
        return $this->hasMany(ProcessImk::class, 'tenant_id', 'id');
    }

    public function customer()
    {
        return $this->hasMany(Customer::class, 'tenant_id', 'id');
    }
    
}
