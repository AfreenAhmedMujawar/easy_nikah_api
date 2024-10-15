<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viewed extends Model
{
    use HasFactory;
    protected $table = 'user_view'; 
    public $timestamps = false; 
    protected $fillable = ['from_id', 'to_id', 'status'];

    // Relationship with User (Viewer)
    public function user()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    // Relationship with User (Viewed Profile)
    public function viewedProfile()
    {
        return $this->belongsTo(User::class, 'to_id');
    }
}
