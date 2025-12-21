<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class shift extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'nama_shift',
        'jam_mulai',
        'jam_selesai',
    ];
    
}
