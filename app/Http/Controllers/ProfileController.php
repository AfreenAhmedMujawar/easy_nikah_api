<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use CodeIgniter\RESTful\ResourceController;
class ProfileController extends Controller
{

    public function getNewProfiles()
    {
       
        // Fetch all users ordered by latest date and time (created_at) in descending order, selecting only the needed columns
        $relatedUsers = User::orderBy('user_created_at', 'desc')
            ->select('profile_id', 'full_name', 'user_marital_status', 'age', 'user_height', 'user_qualification', 'user_location_city', 'user_location_state', 'user_location_country')
            ->get();
    
        // Format the data to include location and other details
        $formattedUsers = $relatedUsers->map(function($user) {
            return [
                'profile_id' => $user->profile_id,
                'full_name' => $user->full_name,
                'marital_status' => ucfirst($user->user_marital_status),
                'age' => $user->age,
                'height' => $user->user_height,
                'qualification' => $user->user_qualification ?? '-',
                'location' => ($user->city->city_name ?? '-') . ', ' . ($user->state->state_name ?? '-') . ', ' . ($user->country->country_name ?? '-') // Adjust the field names as per your database schema


            ];
        });
    
        // Return the profile details along with related users
        return response()->json([
            'all_users' => $formattedUsers, // Related all users ordered by latest date and time with selected columns
        ]);

    }












//     public function getMostPopularProfiles(Request $request)
// {
//     // Sorting direction (default: ascending)
//     $sortDirection = $request->query('sort', 'asc');
    
//     // Fetch most popular profiles ordered by last login or other criteria
//     $popularProfiles = User::where('user_status', 1)
//         ->join('user_login', 'users.id', '=', 'user_login.user_id')  // Join with the login table
//         ->leftJoin('qualifications', 'users.user_qualification', '=', 'qualifications.qualification_id')  // Join with qualifications table
//         ->leftJoin('cities', 'users.user_location_city', '=', 'cities.city_id')  // Join with cities table
//         ->leftJoin('states', 'users.user_location_state', '=', 'states.state_id')  // Join with states table
//         ->leftJoin('countries', 'users.user_location_country', '=', 'countries.country_id')  // Join with countries table
//         ->orderBy('user_login.last_login', $sortDirection)  // Order by last login
//         ->select('users.id', 'users.profile_id', 'users.full_name', 'users.user_marital_status', 
//                  'users.age', 'users.user_height', 'qualifications.qualification_name as user_qualification',  // Fetch qualification name
//                  'cities.city_name', 'states.state_name', 'countries.country_name',  // Fetch location names
//                  'user_login.last_login')  // Specify fields explicitly
//         ->get();

//     // Format the popular profiles data
//     $formattedProfiles = $popularProfiles->map(function($user) {
//         return [
//             'profile_id' => $user->profile_id,
//             'full_name' => $user->full_name,
//             'marital_status' => ucfirst($user->user_marital_status),
//             'age' => $user->age,
//             'height' => $user->user_height,
//             'qualification' => $user->user_qualification ?? '-',  // Now returns qualification name instead of ID
//             'location' => ($user->city_name ?? '-') . ', ' . ($user->state_name ?? '-') . ', ' . ($user->country_name ?? '-'),  // Return city, state, and country names
//             'last_login' => $user->last_login,  // Include last login date
//         ];
//     });

//     // Return the profile details along with related users
//     return response()->json([
//         'success' => true,
//         'most_popular_profiles' => $formattedProfiles,  // Related popular profiles ordered by latest date and time with selected columns
//     ]);
// }



public function getMostPopularProfiles(Request $request)
{
    // Sorting direction (default: ascending)
    $sortDirection = $request->query('sort', 'asc');
    
    // Fetch most popular profiles ordered by last login or other criteria
    $popularProfiles = User::where('user_status', 1)
        ->join('user_login', 'users.id', '=', 'user_login.user_id')  // Join with the login table
        ->leftJoin('qualifications', 'users.user_qualification', '=', 'qualifications.qualification_id')  // Join with qualifications table
        ->leftJoin('cities', 'users.user_location_city', '=', 'cities.city_id')  // Join with cities table
        ->leftJoin('states', 'users.user_location_state', '=', 'states.state_id')  // Join with states table
        ->leftJoin('countries', 'users.user_location_country', '=', 'countries.country_id')  // Join with countries table
        ->orderBy('user_login.last_login', $sortDirection)  // Order by last login
        ->select('users.id', 'users.profile_id', 'users.full_name', 'users.user_marital_status', 
                 'users.age', 'users.user_height', 'qualifications.qualification_name as user_qualification',  // Fetch qualification name
                 'cities.city_name', 'states.state_name', 'countries.country_name',  // Fetch location names
                 'user_login.last_login')  // Specify fields explicitly
        ->get();

    // Format the popular profiles data
    $formattedProfiles = $popularProfiles->map(function($user) {
        return [
            'profile_id' => $user->profile_id,
            'full_name' => $user->full_name,
            'marital_status' => ucfirst($user->user_marital_status),
            'age'=> $user->age,
            'height' => $user->user_height,
            'qualification' => $user->user_qualification ?? '-',  // Now returns qualification name instead of ID
            'location' => ($user->city_name ?? '-') . ', ' . ($user->state_name ?? '-') . ', ' . ($user->country_name ?? '-'),  // Return city, state, and country names
            'last_login' => $user->last_login,  // Include last login date
        ];
    });

    // Return the profile details along with related users
    return response()->json([
        'success' => true,
        'most_popular_profiles' => $formattedProfiles,  // Related popular profiles ordered by latest date and time with selected columns
    ]);
}



    
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
 





public function getOnlineProfiles(Request $request)
{
    // Sorting direction (default: ascending)
    $sortDirection = $request->query('sort', 'asc');
    
    // Fetch users ordered by last login date in ascending or descending order
    $activeUsers = User::where('user_status', 1)
        ->join('user_login', 'users.id', '=', 'user_login.user_id')  // Join with the login table
        ->leftJoin('qualifications', 'users.user_qualification', '=', 'qualifications.qualification_id')  // Join with qualifications table
        ->leftJoin('cities', 'users.user_location_city', '=', 'cities.city_id')  // Join with cities table
        ->leftJoin('states', 'users.user_location_state', '=', 'states.state_id')  // Join with states table
        ->leftJoin('countries', 'users.user_location_country', '=', 'countries.country_id')  // Join with countries table
        ->orderBy('user_login.last_login', $sortDirection)  // Order by last login
        ->select('users.id', 'users.profile_id', 'users.full_name', 'users.user_marital_status', 
                 'users.age', 'users.user_height', 'qualifications.qualification_name as user_qualification',  // Fetch qualification name
                 'cities.city_name', 'states.state_name', 'countries.country_name',  // Fetch location names
                 'user_login.last_login')  // Specify fields explicitly
        ->get();

    // Format the users data
    $formattedUsers = $activeUsers->map(function($user) {
        return [
            'profile_id' => $user->profile_id,
            'full_name' => $user->full_name,
            'marital_status' => ucfirst($user->user_marital_status),
            'age' => $user->age,
            'height' => $user->user_height,
            'qualification' => $user->user_qualification ?? '-',  // Now returns qualification name instead of ID
            'location' => ($user->city_name ?? '-') . ', ' . ($user->state_name ?? '-') . ', ' . ($user->country_name ?? '-'),  // Return city, state, and country names
            'last_login' => $user->last_login,  // Include last login date
        ];
    });

    // Return the profile details along with related users
    return response()->json([
        'success' => true,
        'active_online_users' => $formattedUsers,  // Related all users ordered by latest date and time with selected columns
    ]);
}
       
    


//     public function getUsersByMaritalStatus(Request $request)
// {
//     // Retrieve query parameters for marital statuses
//     $statuses = $request->query('statuses', ['unmarried', 'widower', '2nd marriage']); // Default statuses

//     // Query users based on the marital statuses and eager load city, state, and country
//     $users = User::with(['city', 'state', 'country']) // Ensure these relationships are correctly defined
//                  ->whereIn('user_marital_status', $statuses)
//                  ->get();

//     // Check if any users are found
//     if ($users->isEmpty()) {
//         return response()->json(['message' => 'No users found with the specified marital status'], 404);
//     }

//     // Format and return the user data
//     $formattedUsers = $users->map(function ($user) {
//         return [
//             'profile_id' => $user->profile_id,
//             'full_name' => $user->full_name,
//             'marital_status' => ucfirst($user->user_marital_status), // Ensure correct field name here
//             'age' => $user->age,
//             'height' => $user->user_height,
//             'qualification' => $user->qualification->qualification_name ?? '-', // Ensure the field is correct
//             'location' => 
//                 ($user->city->city_name ?? '-') . ', ' .  // Get city name
//                 ($user->state->state_name ?? '-') . ', ' . // Get state name
//                 ($user->country->country_name ?? '-')        // Get country name
//         ];
//     });

//     return response()->json($formattedUsers);
// }










    
}
