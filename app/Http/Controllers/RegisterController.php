<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Notifications\SendOtpNotification;

class RegisterController extends Controller
{

   


    public function store(Request $request)
    {
        return $this->register($request);
    }

    
    // public function show(string $id)
    // {
    //     $user = User::find($id);
    //     if ($user) {
    //         return response()->json($user);
    //     } else {
    //         return response()->json(['message' => 'User not found'], 404);
    //     }
    // }


    

    
    public function update(Request $request, $id)
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:registers,email,' . $id,
            'password' => 'required|string|min:8',
            'user_height' => 'nullable|string|max:50',
            'user_namaz_type' => 'nullable|string|max:50',
            'age' => 'nullable|integer|min:0|max:150',
            'user_fasting_type' => 'nullable|string|max:50',
            'user_hijab' => 'nullable|string|max:50',
            'user_marital_status' => 'nullable|string|max:50',
            'user_children' => 'nullable|integer|min:0',
            'user_qualification' => 'nullable|string|max:100',
            'user_profession' => 'nullable|string|max:100',
            'user_father_name' => 'nullable|string|max:255',
            'user_mother_name' => 'nullable|string|max:255',
            'user_father_profession' => 'nullable|string|max:255',
            'user_mother_profession' => 'nullable|string|max:255',
            'user_brothers' => 'nullable|integer|min:0',
            'user_married_brothers' => 'nullable|integer|min:0',
            'user_sisters' => 'nullable|integer|min:0',
            'user_married_sisters' => 'nullable|integer|min:0',
            'user_location_country' => 'nullable|string|max:100',
            'user_location_state' => 'nullable|string|max:100',
            'user_location_city' => 'nullable|string|max:100',
            'pan_card' => 'nullable|string|max:20',
            'aadhar_card' => 'nullable|string|max:20',
            'driving_license' => 'nullable|string|max:20',
            'maslak' => 'nullable|string|max:100',
            'facebook_profile_link' => 'nullable|url|max:255',
            'linkedin_profile_link' => 'nullable|url|max:255',
            'twitter_profile_link' => 'nullable|url|max:255',
            'instagram_profile_link' => 'nullable|url|max:255',
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Find the existing record
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update the user fields
        $user->update([
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'password' => md5($request->input('password')), 
            'user_height' => $request->input('user_height'),
            'user_namaz_type' => $request->input('user_namaz_type'),
            'age' => $request->input('age'),
            'user_fasting_type' => $request->input('user_fasting_type'),
            'user_hijab' => $request->input('user_hijab'),
            'user_marital_status' => $request->input('user_marital_status'),
            'user_children' => $request->input('user_children'),
            'user_qualification' => $request->input('user_qualification'),
            'user_profession' => $request->input('user_profession'),
            'user_father_name' => $request->input('user_father_name'),
            'user_mother_name' => $request->input('user_mother_name'),
            'user_father_profession' => $request->input('user_father_profession'),
            'user_mother_profession' => $request->input('user_mother_profession'),
            'user_brothers' => $request->input('user_brothers'),
            'user_married_brothers' => $request->input('user_married_brothers'),
            'user_sisters' => $request->input('user_sisters'),
            'user_married_sisters' => $request->input('user_married_sisters'),
            'user_location_country' => $request->input('user_location_country'),
            'user_location_state' => $request->input('user_location_state'),
            'user_location_city' => $request->input('user_location_city'),
            'pan_card' => $request->input('pan_card'),
            'aadhar_card' => $request->input('aadhar_card'),
            'driving_license' => $request->input('driving_license'),
            'maslak' => $request->input('maslak'),
            'facebook_profile_link' => $request->input('facebook_profile_link'),
            'linkedin_profile_link' => $request->input('linkedin_profile_link'),
            'twitter_profile_link' => $request->input('twitter_profile_link'),
            'instagram_profile_link' => $request->input('instagram_profile_link'),
            'user_updated_at' => now(),
        ]);
        $otp = $user->generateOtp();
        return response()->json(['message' => 'Data updated successfully', 'data' => $user], 200);
    }




    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'full_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'profile_id' => 'nullable|string|max:100',
        'user_height' => 'nullable|string|max:50',
        'user_namaz_type' => 'nullable|string|max:50',
        'age' => 'nullable|integer|min:0|max:150',
        'user_fasting_type' => 'nullable|string|max:50',
        'user_hijab' => 'nullable|string|max:50',
        'user_marital_status' => 'nullable|string|max:50',
        'user_children' => 'nullable|integer|min:0',
        'user_qualification' => 'nullable|string|max:100',
        'user_profession' => 'nullable|string|max:100',
        'user_father_name' => 'nullable|string|max:255',
        'user_mother_name' => 'nullable|string|max:255',
        'user_father_profession' => 'nullable|string|max:255',
        'user_mother_profession' => 'nullable|string|max:255',
        'user_brothers' => 'nullable|integer|min:0',
        'user_married_brothers' => 'nullable|integer|min:0',
        'user_sisters' => 'nullable|integer|min:0',
        'user_married_sisters' => 'nullable|integer|min:0',
        'user_location_country' => 'nullable|string|max:100',
        'user_location_state' => 'nullable|string|max:100',
        'user_location_city' => 'nullable|string|max:100',
        'pan_card' => 'nullable|string|max:20',
        'aadhar_card' => 'nullable|string|max:20',
        'driving_license' => 'nullable|string|max:20',
        'maslak' => 'nullable|string|max:100',
        'facebook_profile_link' => 'nullable|url|max:255',
        'linkedin_profile_link' => 'nullable|url|max:255',
        'twitter_profile_link' => 'nullable|url|max:255',
        'instagram_profile_link' => 'nullable|url|max:255',

        'user_native_location'=> 'nullable|string|max:100',
        'user_work_location'=> 'nullable|string|max:100',
        'user_partner_age_group' => 'nullable|integer|min:0|max:150',
        'user_partner_current_location'=> 'nullable|string|max:100',
        'user_partner_native_location'=> 'nullable|string|max:100',
        'part_pref_maritial_status'=> 'nullable|string|max:50',
        'part_pref_edu_quali'=> 'nullable|string|max:100',
        'part_pref_height'=> 'nullable|string|max:50',
        'part_pref_maslak'=> 'nullable|string|max:100',
    ]);

 
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

   
    $user = User::create([
        'full_name' => $request->input('full_name'),
        'email' => $request->input('email'),
        'password' => md5($request->input('password')), // Encrypt password using MD5
        
        'user_height' => $request->input('user_height'),
        'user_namaz_type' => $request->input('user_namaz_type'),
        'age' => $request->input('age'),
        'user_fasting_type' => $request->input('user_fasting_type'),
        'user_hijab' => $request->input('user_hijab'),
        'user_marital_status' => $request->input('user_marital_status'),
        'user_children' => $request->input('user_children'),
        'user_qualification' => $request->input('user_qualification'),
        'user_profession' => $request->input('user_profession'),
        'user_father_name' => $request->input('user_father_name'),
        'user_mother_name' => $request->input('user_mother_name'),
        'user_father_profession' => $request->input('user_father_profession'),
        'user_mother_profession' => $request->input('user_mother_profession'),
        'user_brothers' => $request->input('user_brothers'),
        'user_married_brothers' => $request->input('user_married_brothers'),
        'user_sisters' => $request->input('user_sisters'),
        'user_married_sisters' => $request->input('user_married_sisters'),
        'user_location_country' => $request->input('user_location_country'),
        'user_location_state' => $request->input('user_location_state'),
        'user_location_city' => $request->input('user_location_city'),
        'pan_card' => $request->input('pan_card'),
        'aadhar_card' => $request->input('aadhar_card'),
        'driving_license' => $request->input('driving_license'),
        'maslak' => $request->input('maslak'),
        'facebook_profile_link' => $request->input('facebook_profile_link'),
        'linkedin_profile_link' => $request->input('linkedin_profile_link'),
        'twitter_profile_link' => $request->input('twitter_profile_link'),
        'instagram_profile_link' => $request->input('instagram_profile_link'),

        'user_native_location'=> $request->input('user_native_location'),
        'user_work_location'=> $request->input('user_work_location'),
        'user_partner_age_group' => $request->input('user_partner_age_group'),
        'user_partner_current_location'=> $request->input('user_partner_current_location'),
        'user_partner_native_location'=> $request->input('user_partner_native_location'),
        'part_pref_maritial_status'=> $request->input('part_pref_maritial_status'),
        'part_pref_edu_quali'=> $request->input('part_pref_edu_quali'),
        'part_pref_height'=> $request->input('part_pref_height'),
        'part_pref_maslak'=> $request->input('part_pref_maslak'),
     
        'profile_id' => $request->input('profile_id'),
        'verification_id' => $request->input('verification_id'),
        'email_verification_status' => 0
    ]);

    
    $otp = $user->generateOtp();


    $user->notify(new SendOtpNotification($otp));
    $token = $user->createToken('auth_token')->plainTextToken;
    
    return response()->json([
        'message' => 'User registered successfully',
        // 'user' => $user,
        // 'otp' => $otp,
        'token' => $token  // Include the generated token in the response
    ], 201);
}



public function register2(Request $request)
{
    // Step 1: Validation
    $validator = Validator::make($request->all(), [
        'full_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'profile_id' => 'nullable|string|max:100',
        'user_height' => 'nullable|string|max:50',
        'user_namaz_type' => 'nullable|string|max:50',
        'age' => 'nullable|integer|min:0|max:150',
        'user_fasting_type' => 'nullable|string|max:50',
        'user_hijab' => 'nullable|string|max:50',
        'user_marital_status' => 'nullable|string|max:50',
        'user_children' => 'nullable|integer|min:0',
        'user_qualification' => 'nullable|string|max:100',
        'user_profession' => 'nullable|string|max:100',
        'user_father_name' => 'nullable|string|max:255',
        'user_mother_name' => 'nullable|string|max:255',
        'user_father_profession' => 'nullable|string|max:255',
        'user_mother_profession' => 'nullable|string|max:255',
        'user_brothers' => 'nullable|integer|min:0',
        'user_married_brothers' => 'nullable|integer|min:0',
        'user_sisters' => 'nullable|integer|min:0',
        'user_married_sisters' => 'nullable|integer|min:0',
        'user_location_country' => 'nullable|string|max:100',
        'user_location_state' => 'nullable|string|max:100',
        'user_location_city' => 'nullable|string|max:100',
        'pan_card' => 'nullable|string|max:20',
        'aadhar_card' => 'nullable|string|max:20',
        'driving_license' => 'nullable|string|max:20',
        'maslak' => 'nullable|string|max:100',
        'facebook_profile_link' => 'nullable|url|max:255',
        'linkedin_profile_link' => 'nullable|url|max:255',
        'twitter_profile_link' => 'nullable|url|max:255',
        'instagram_profile_link' => 'nullable|url|max:255',
        'user_native_location' => 'nullable|string|max:100',
        'user_work_location' => 'nullable|string|max:100',
        'user_partner_age_group' => 'nullable|integer|min:0|max:150',
        'user_partner_current_location' => 'nullable|string|max:100',
        'user_partner_native_location' => 'nullable|string|max:100',
        'part_pref_maritial_status' => 'nullable|string|max:50',
        'part_pref_edu_quali' => 'nullable|string|max:100',
        'part_pref_height' => 'nullable|string|max:50',
        'part_pref_maslak' => 'nullable|string|max:100',
    ]);

    // Step 2: Handle Validation Errors
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Step 3: Create the User
    $user = User::create([
        'full_name' => $request->input('full_name'),
        'email' => $request->input('email'),
        'password' => md5($request->input('password')), // Encrypt password using MD5
        'user_height' => $request->input('user_height'),
        'user_namaz_type' => $request->input('user_namaz_type'),
        'age' => $request->input('age'),
        'user_fasting_type' => $request->input('user_fasting_type'),
        'user_hijab' => $request->input('user_hijab'),
        'user_marital_status' => $request->input('user_marital_status'),
        'user_children' => $request->input('user_children'),
        'user_qualification' => $request->input('user_qualification'),
        'user_profession' => $request->input('user_profession'),
        'user_father_name' => $request->input('user_father_name'),
        'user_mother_name' => $request->input('user_mother_name'),
        'user_father_profession' => $request->input('user_father_profession'),
        'user_mother_profession' => $request->input('user_mother_profession'),
        'user_brothers' => $request->input('user_brothers'),
        'user_married_brothers' => $request->input('user_married_brothers'),
        'user_sisters' => $request->input('user_sisters'),
        'user_married_sisters' => $request->input('user_married_sisters'),
        'user_location_country' => $request->input('user_location_country'),
        'user_location_state' => $request->input('user_location_state'),
        'user_location_city' => $request->input('user_location_city'),
        'pan_card' => $request->input('pan_card'),
        'aadhar_card' => $request->input('aadhar_card'),
        'driving_license' => $request->input('driving_license'),
        'maslak' => $request->input('maslak'),
        'facebook_profile_link' => $request->input('facebook_profile_link'),
        'linkedin_profile_link' => $request->input('linkedin_profile_link'),
        'twitter_profile_link' => $request->input('twitter_profile_link'),
        'instagram_profile_link' => $request->input('instagram_profile_link'),
        'user_native_location' => $request->input('user_native_location'),
        'user_work_location' => $request->input('user_work_location'),
        'user_partner_age_group' => $request->input('user_partner_age_group'),
        'user_partner_current_location' => $request->input('user_partner_current_location'),
        'user_partner_native_location' => $request->input('user_partner_native_location'),
        'part_pref_maritial_status' => $request->input('part_pref_maritial_status'),
        'part_pref_edu_quali' => $request->input('part_pref_edu_quali'),
        'part_pref_height' => $request->input('part_pref_height'),
        'part_pref_maslak' => $request->input('part_pref_maslak'),
        'profile_id' => $request->input('profile_id'),
        'verification_id' => $request->input('verification_id'),
        'email_verification_status' => 0,
    ]);

    // Step 4: Generate OTP and Send Notification
    $otp = $user->generateOtp();
    $user->notify(new SendOtpNotification($otp));

    // Step 5: Generate Token for Authentication
    $token = $user->createToken('auth_token')->plainTextToken;

    // Step 6: Return Response
    return response()->json([
        'message' => 'User registered successfully',
        'token' => $token  // Include the generated token in the response
    ], 201);
}

}
