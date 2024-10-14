<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viewed extends Model
{
    use HasFactory;
    protected $fillable = ['from_id', 'to_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'from_id');
    }
}
