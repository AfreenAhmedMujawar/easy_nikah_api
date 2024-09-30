<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactPersonProfileController extends Controller
{
    public function getContactPersons($userId): JsonResponse
    {
        // Find the user with the related contactPersons
        $user = User::with('contactPersons')->find($userId);

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Map the contact persons to the desired format
        $contactPersons = $user->contactPersons->map(function ($contact) {
            return [
                'ID' => $contact->id,
                'Name' => $contact->contact_person_name,
                'Email' => $contact->contact_person_email,
                'Phone' => $contact->contact_person_phone_no,
                'Relation' => $contact->contact_person_relation,
                'Email 1' => $contact->contact_person_email_second,
                'Email 2' => $contact->contact_person_email_third,
                'Mobile' => $contact->contact_person_mobile,
                'WhatsApp' => $contact->contact_person_whatsapp,
                'Email Verification Status' => $contact->email_verification_status,
                'Mobile Verification Status' => $contact->mobile_verification_status,
            ];
        });

        // Return the response as JSON
        return response()->json($contactPersons);
    }
}
