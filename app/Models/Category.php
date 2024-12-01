<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $fillable = [
        'item',
        'type'
    ];

    // public function periode()
    // {
    //     return $this->hasMany(Periode::class, 'category_id', 'id');
    // }

    public function process_imk()
    {
        return $this->hasMany(ProcessImk::class, 'category_id', 'id');
    }
}
