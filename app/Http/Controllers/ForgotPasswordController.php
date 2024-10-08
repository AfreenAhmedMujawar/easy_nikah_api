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

        return response()->json(['message' => 'OTP verified successfully.'], 200);
    } else {
      
        return response()->json(['message' => 'Invalid OTP.'], 400);
    }
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

    return response()->json(['message' => 'Password has been reset successfully.'], 200);
}

}
