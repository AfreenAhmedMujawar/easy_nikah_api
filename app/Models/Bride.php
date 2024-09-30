<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bride extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name', 'email', 'user_height', 'user_namaz_type', 'dob', 
        'qualification_id', 'profession_id', 'location_id', 'about', 
        'facebook_profile_link', 'twitter_profile_link', 'instagram_profile_link', 
        'user_status'
    ];

    // public function location()
    // {
    //     return $this->belongsTo(Location::class, 'location_id');
    // }

    public function qualification()
    {
        return $this->belongsTo(Qualification::class, 'qualification_id');
    }

    public function profession()
    {
        return $this->belongsTo(Profession::class, 'profession_id');
    }

    // public function family()
    // {
    //     return $this->hasMany(UserFamily::class, 'user_id');
    // }

    // public function partnerPreferences()
    // {
    //     return $this->hasOne(PartnerPreference::class, 'bride_id');
    // }
}
