<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public $timestamps = false;
    protected $guarded= false;
    
    // protected $fillable = ['user_status'];
    protected $fillable = [
        'full_name', 'email', 'password', 'profile_id', 'user_height', 'user_namaz_type', 
        'age', 'user_fasting_type', 'user_hijab', 'user_marital_status', 'user_children', 
        'user_qualification', 'user_profession', 'user_father_name', 'user_mother_name', 
        'user_father_profession', 'user_mother_profession', 'user_brothers', 
        'user_married_brothers', 'user_sisters', 'user_married_sisters', 
        'user_location_country', 'user_location_state', 'user_location_city', 'pan_card', 
        'aadhar_card', 'driving_license', 'maslak', 'facebook_profile_link', 
        'linkedin_profile_link', 'twitter_profile_link', 'instagram_profile_link', 
        'user_native_location', 'user_work_location', 'user_partner_age_group', 
        'user_partner_current_location', 'user_partner_native_location', 
        'part_pref_maritial_status', 'part_pref_edu_quali', 'part_pref_height', 
        'part_pref_maslak', 'verification_id', 'email_verification_status'
    ];
    
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];
    // protected $fillable = [
    //     'email', 'full_name', 'password', 'email_verified', 'password_reset_token'
    // ];

    
    
    protected $hidden = [
        'password',
        'remember_token',
    ];

   
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

   

    public function generateOtp()
    {
        $otp = rand(1000, 9999); // 6-digit OTP
        $this->otp = $otp;
        $this->otp_expires_at = Carbon::now()->addMinutes(10); // OTP valid for 10 minutes
        $this->save();
    
        return $otp;
    }
    
    public function verifyOtp($inputOtp)
    {
        if ($this->otp !== $inputOtp) {
            return false; // Invalid OTP
        }

        if (Carbon::now()->greaterThan($this->otp_expires_at)) {
            return false; // OTP has expired
        }

        // OTP is valid and not expired
        return true;
    }

 
    
    public function contactPersons()
    {
        return $this->hasMany(ContactPerson::class, 'user_id');
    }
    
   
    // In User model
public function lastLoginTime() {
    return Login::where('user_id', $this->id)->orderBy('created_at', 'desc')->first()->login_time ?? null;
}

public function city()
{
    return $this->belongsTo(City::class, 'user_location_city', 'city_id'); // Assuming city_id is stored in user_location_city
}

public function state()
{
    return $this->belongsTo(State::class, 'user_location_state', 'state_id'); // Assuming state_id is stored in user_location_state
}

public function country()
{
    return $this->belongsTo(Country::class, 'user_location_country', 'country_id'); // Assuming country_id is stored in user_location_country
}

public function qualification()
{
    return $this->belongsTo(Qualification::class, 'user_qualification', 'qualification_id'); // Assuming country_id is stored in user_location_country
}

public function get_new_registrations($fromUserId)
    {
        // This is where your database query would go to fetch new registrations
        $this->db->select('users.*, cities.city_name, states.state_name, countries.country_name');
        $this->db->from('users');
        $this->db->join('cities', 'users.user_location_city = cities.id', 'left');
        $this->db->join('states', 'users.user_location_state = states.id', 'left');
        $this->db->join('countries', 'users.user_location_country = countries.id', 'left');
        
        // Add any additional filters based on your requirements
        // Example: $this->db->where('users.some_column', 'value');
        
        $query = $this->db->get();
        return $query->result_array(); // Returns an array of user data
    }

}


