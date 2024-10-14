<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; 

class ForgotPasswordController extends Controller
{
   
    public function generateOtp(Request $request)
    {
        



        $email = $request->input('email');
        $user = User::where('email', $email)->first();


        // dd($email);
        if ($user) {
           
            $otp = rand(1000, 9999);
            $user->otp = $otp;
            $user->save();

          
            $subject = 'Easy Nikah Email Verification OTP';
            $message = "As salaamu alaikum, {$user->full_name}. <br><br>Your OTP for email verification is: <strong>{$otp}</strong>.<br><br>Best Regards,<br>Easy Nikah Team";

      
            Mail::send([], [], function ($mail) use ($email, $subject, $message) {
                $mail->to($email)
                     ->subject($subject)
                     ->setBody($message, 'text/html');
            });

            return response()->json(['message' => 'OTP has been sent to your email.'], 200);
        } else {
            return response()->json(['message' => 'Email not found.'], 404);
        }
    }

    
  
public function verifyOtp(Request $request)
{
    $user = User::where('otp', $request->otp)->first();

    if ($user) {

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
        
            $this->resendOtp($user);

            return response()->json(['message' => 'OTP expired. A new OTP has been sent.'], 200);
        }

        // Check if the email is already verified
        // if ($user->email_verification_status == 1) {
        //     return response()->json(['message' => 'Your email is already verified.'], 200);
        // }


        $user->email_verification_status = 1;
        // $user->otp = null; // Clear the OTP after successful verification
        $user->otp_expires_at = null; 
        $user->save();

        return response()->json(['message' => 'OTP verified successfully.' ,"success"=>true], 200);
    } else {
      
        return response()->json(['message' => 'Invalid OTP.'], 400);
    }
}













// public function verifyOtpActive(Request $request)
// {
//     // Validate the OTP input
//     $request->validate([
//         'otp' => 'required|integer',
//     ]);

//     // Find the user by the OTP
//     $user = User::where('otp', $request->otp)->first();

//     // Check if a user with this OTP exists
//     if ($user) {
//         // Check if the OTP has expired
//         if (Carbon::now()->greaterThan($user->otp_expires_at)) {
//             // OTP has expired, resend a new OTP
//             $this->resendOtp($user);

//             return response()->json([
//                 'message' => 'OTP expired. A new OTP has been sent to your email.',
//                 'success' => false,
//                 'otp_expires_at' => $user->otp_expires_at,  // Debug info
//                 'current_time' => Carbon::now(),  // Debug info
//             ], 200);
//         }

//         // If OTP is valid and not expired, activate the account
//         $user->email_verification_status = 1; // Assuming 1 indicates verified
//         $user->user_status = 'active'; // Update the user_status to 'active'
//         $user->otp_expires_at = null; // Clear OTP expiration
//         // $user->otp = null; // Optional: Clear OTP after successful verification
//         $user->save();

//         // Return success message with debug info
//         return response()->json([
//             'message' => 'OTP verified successfully. Your account is now active.',
//             'success' => true,
//             'user_status' => $user->user_status,  // Debug info
//             'email_verification_status' => $user->email_verification_status,  // Debug info
//         ], 200);
//     } else {
//         // If no user is found with the OTP, return an error with debug info
//         return response()->json([
//             'message' => 'Invalid OTP.',
//             'success' => false,
//             'input_otp' => $request->otp,  // Debug info
//         ], 400);
//     }
// }





public function verifyOtpActive(Request $request)
{
    // Ensure request is JSON
    if ($request->header('Content-Type') !== 'application/json') {
        return response()->json([
            'message' => 'Invalid content type. Use application/json.'
        ], 415); // Unsupported Media Type
    }

    // Step 1: Validate OTP input
    $validatedData = $request->validate([
        'otp' => 'required|integer',
    ]);

    // Step 2: Find user by OTP
    $user = User::where('otp', $validatedData['otp'])->first();

    // Step 3: Handle OTP validation scenarios
    if ($user) {
        // Check if OTP has expired
        if (now()->greaterThan($user->otp_expires_at)) {
            $this->resendOtp($user); // Resend OTP if expired

            return response()->json([
                'message' => 'OTP expired. A new OTP has been sent to your email.',
                'success' => false,
                'otp_expires_at' => $user->otp_expires_at, // Debug info
                'current_time' => now(), // Debug info
            ], 200);
        }

        // OTP is valid, activate user
        $user->email_verification_status = 1; // Set verified
        $user->user_status = 'active'; // Activate user
        $user->otp_expires_at = null; // Clear OTP expiration
        // $user->otp = null; // Optional: Clear OTP
        $user->save();

        return response()->json([
            'message' => 'OTP verified successfully. Your account is now active.',
            'success' => true,
            'user_status' => $user->user_status, // Debug info
            'email_verification_status' => $user->email_verification_status, // Debug info
        ], 200);
    }

    // Invalid OTP case
    return response()->json([
        'message' => 'Invalid OTP.',
        'success' => false,
        'input_otp' => $validatedData['otp'], // Debug info
    ], 400);
}


public function resendOtp($user)
{

    $newOtp = rand(1000, 9999);

    
    $user->otp = $newOtp;
    $user->otp_expires_at = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes
    $user->save();


    // Mail::to($user->email)->send(new OtpMail($newOtp));

    Log::info("Resent OTP to user {$user->id}: {$newOtp}");
}

    public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|string',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

   
    $user = User::where('email', $request->email)->first();


    if ($user->otp !== $request->otp) {
        return response()->json(['message' => 'Invalid OTP'], 400);
    }

    if (Carbon::now()->greaterThan($user->otp_expires_at)) {
        return response()->json(['message' => 'OTP has expired'], 400);
    }

  
    $user->password = md5($request->password); 

 
    $user->otp = null;
    $user->otp_expires_at = null;

  
    $user->save();

    return response()->json(['message' => 'Password has been reset successfully.',"success"=>true], 200);
}








}
