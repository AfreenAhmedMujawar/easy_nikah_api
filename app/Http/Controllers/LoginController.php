<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LoginController extends Controller
{
//     public function login(Request $request)
// {
  
//     $request->validate([
//         'email' => 'required|email',
//         'password' => 'required|min:6',
//     ]);

//     try {
      
//         $user = User::where('email', $request->email)->first();

//         if (!$user) {
//             throw ValidationException::withMessages([
//                 'email' => ['Invalid email or password'],
//             ]);
//         }

 
//         if (md5($request->password) !== $user->password) {
//             throw ValidationException::withMessages([
//                 'email' => ['Invalid email or password'],
//             ]);
//         }

//         $token = $user->createToken('authToken')->plainTextToken;

//         return response()->json([
//             'message' => 'Login successful',
//             // 'user' => $user,
//             'token' => $token,
//             "success"=>true
//         ], 200);

//     } catch (ValidationException $e) {
//         return response()->json(['message' => $e->errors()], 400);
//     } catch (\Exception $e) {
      
//         return response()->json(['message' => 'Something went wrong: '  ,"success"=>false. $e->getMessage()], 500);
//     }
// }



public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    try {
        // Fetch the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        // Check if the user account is active
        if ($user->user_status !== 'active') { // Assuming 'active' indicates a valid status
            return response()->json([
                'message' => 'Please activate your account. An OTP has been sent to your email.',
                'success' => false,
            ], 403);
        }

        // Check password
        if (md5($request->password) !== $user->password) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        // Generate authentication token
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'success' => true,
        ], 200);
    } catch (ValidationException $e) {
        return response()->json(['message' => $e->errors(), 'success' => false], 400);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Something went wrong: ' . $e->getMessage(), 'success' => false], 500);
    }
}


}
