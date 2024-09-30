<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    protected $table = 'user_has_contact_person'; // Adjust if your table name is different

    protected $fillable = [
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone_no',
        'contact_person_relation',
        'user_id',
        'email_verification_status',
        'second_email_verification_status',
        'third_email_verification_status',
        'mobile_verification_status',
        'contact_person_email_second',
        'contact_person_email_third',
        'contact_person_mobile',
        'contact_person_whatsapp',
        'whatsapp_verification_status',
    ];

    // Define the inverse relationship back to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
