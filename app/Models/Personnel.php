<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;
    protected $table = 'personnels';
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function processImk()
    {
        return $this->belongsTo(ProcessImk::class, 'id_imk', 'id');
    }
}
