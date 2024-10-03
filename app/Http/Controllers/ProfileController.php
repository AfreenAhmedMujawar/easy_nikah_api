<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use CodeIgniter\RESTful\ResourceController;
class ProfileController extends Controller
{
    //Get single user profile by ID
    public function getProfile($id)
    {
        // Fetch user profile by ID
        // $user = User::find($id);
        $user = User::all();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Return the profile details
        return response()->json([
            'profile_id' => $user->profile_id,
            'full_name' => $user->full_name,
            'marital_status' => ucfirst($user->user_marital_status), // Check the correct field name here
            'age' => $user->age,
            'height' => $user->user_height,
            'qualification' => $user->user_qualification ?? '-', // Ensure the field is correct
            'location' => ($user->user_location_city ?? '-') . ' ' . ($user->user_location_state ?? '-') . ' ' . ($user->user_location_country ?? '-'),
        ]);
    }

    // public function getProfile($id)
    // {
    //     // Fetch user profile by ID
    //     $user = User::find($id);
    
    //     if (!$user) {
    //         return response()->json(['error' => 'User not found'], 404);
    //     }
    
    //     // Fetch all users with the same profile_id in ascending order
    //     $relatedUsers = User::where('profile_id', $user->profile_id)
    //         ->orderBy('id', 'asc') // Adjust the column name if necessary
    //         ->get();
    
    //     // Return the profile details along with related users
    //     return response()->json([
    //         'profile_id' => $user->profile_id,
    //         'full_name' => $user->full_name,
    //         'marital_status' => ucfirst($user->user_marital_status),
    //         'age' => $user->age,
    //         'height' => $user->user_height,
    //         'qualification' => $user->user_qualification ?? '-',
    //         'location' => ($user->user_location_city ?? '-') . ' ' . ($user->user_location_state ?? '-') . ' ' . ($user->user_location_country ?? '-'),
    //         'related_users' => $relatedUsers, // Include the related users
    //     ]);
    // }
    

    
       
    


    public function getUsersByMaritalStatus(Request $request)
{
    // Retrieve query parameters for marital statuses
    $statuses = $request->query('statuses', ['unmarried', 'widower', '2nd marriage']); // Default statuses

    // Query users based on the marital statuses and eager load city, state, and country
    $users = User::with(['city', 'state', 'country']) // Ensure these relationships are correctly defined
                 ->whereIn('user_marital_status', $statuses)
                 ->get();

    // Check if any users are found
    if ($users->isEmpty()) {
        return response()->json(['message' => 'No users found with the specified marital status'], 404);
    }

    // Format and return the user data
    $formattedUsers = $users->map(function ($user) {
        return [
            'profile_id' => $user->profile_id,
            'full_name' => $user->full_name,
            'marital_status' => ucfirst($user->user_marital_status), // Ensure correct field name here
            'age' => $user->age,
            'height' => $user->user_height,
            'qualification' => $user->qualification->qualification_name ?? '-', // Ensure the field is correct
            'location' => 
                ($user->city->city_name ?? '-') . ', ' .  // Get city name
                ($user->state->state_name ?? '-') . ', ' . // Get state name
                ($user->country->country_name ?? '-')        // Get country name
        ];
    });

    return response()->json($formattedUsers);
}






}
