<?php

namespace App\Http\Controllers;

use App\Models\Profession;
use App\Models\Qualification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserController extends Controller
{
  
    public function index(Request $request)
    {
        $user = $request->user(); // Retrieve the authenticated user
        return response()->json($user);
    }

    public function create()
    {
        
    }

    public function store(Request $request)
    {
        return $this->register($request);
    }

    
    public function show(string $id)
    {
        $user = User::find($id);
        if ($user) {
            return response()->json($user);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    
    public function edit(string $id)
    {
      
    }

    
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if ($user) {
            $user->update($request->all());
            return response()->json(['message' => 'User updated successfully', 'user' => $user]);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function user_delete($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

public function getProfessions()
    {
        $professions = Profession::all();
        return response()->json($professions, 200);
    }

    public function getQualifications()
    {
        $professions = Qualification::all();
        return response()->json($professions, 200);
    }


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




    // public function getProfile($userId)
    // {
    //     // Fetch the user by ID with their contact persons (assumes the relationship is set up)
    //     $user = User::with('contactPersons')->find($userId);

    //     // Check if the user exists
    //     if (!$user) {
    //         return response()->json(['message' => 'User not found'], 404);
    //     }

    //     // Prepare contact person details
    //     $contactPersons = $user->contactPersons->map(function($contact) {
    //         return [
    //             'Name' => $contact->contact_person_name,
    //             'Relation' => $contact->contact_person_relation,
    //             'Email' => $contact->contact_person_email,
    //             'Email 1' => $contact->contact_person_email_second,
    //             'Email 2' => $contact->contact_person_email_third,
    //             'Phone 1' => $contact->contact_person_phone_no,
    //             'Phone 2' => $contact->contact_person_mobile,
    //             'WhatsApp' => $contact->contact_person_whatsapp,
    //         ];
    //     });

    //     // Prepare the profile data (include other fields as necessary)
    //     $profileData = [
    //         'id' => $user->id,
    //         'name' => $user->name,
    //         'email' => $user->email,
    //         // Other profile fields...
    //         'contact_persons' => $contactPersons
    //     ];

    //     // Return the profile with contact person details
    //     return response()->json($profileData);
    // }



    public function getUserCount(Request $request)
    {
       
        $results = User::select('qualifications.qualification_name as qualification', 
                                 'users.user_marital_status', 
                                 DB::raw('COUNT(*) as total_users'))
            ->join('qualifications', 'users.user_qualification', '=', 'qualifications.qualification_id')
            ->groupBy('qualifications.qualification_name', 'users.user_marital_status')
            ->get();
    
 
        $qualificationStatusCount = [];
    
     
        foreach ($results as $result) {
            $qualification = $result->qualification; 
            $status = $result->user_marital_status; 
            $totalUsers = $result->total_users; 
    
            // Initialize structure for each qualification if not already set
            // if (!isset($qualificationStatusCount[$qualification])) {
            //     $qualificationStatusCount[$qualification] = [
            //         'Divorce' => 0,
            //         'Unmarried' => 0,
            //         'Widowed' => 0
            //     ];
            // }
    
   
            $qualificationStatusCount[$qualification][$status] = $totalUsers;
        }
    
      
        return response()->json($qualificationStatusCount);
    }
    
   

    public function getUserCountByLocation(Request $request)
    {
    
        $results = User::select(
                'cities.city_name as location', 
                'users.user_marital_status', 
                DB::raw('COUNT(*) as total_users')
            )
            ->join('cities', 'users.user_location_city', '=', 'city_id')
            ->groupBy('cities.city_name', 'users.user_marital_status')
            ->get();
    
        
        $locationStatusCount = [];
    
     
        foreach ($results as $result) {
            $location = $result->location; // Get the actual location name
            $status = $result->user_marital_status; // Get the marital status
            $totalUsers = $result->total_users; // Get the count of users
    
            // Initialize structure for each location if not already set
            // if (!isset($locationStatusCount[$location])) {
            //     $locationStatusCount[$location] = [
            //         'Divorce' => 0,
            //         'Unmarried' => 0,
            //         'Widowed' => 0
            //     ];
            // }
    
            $locationStatusCount[$location][$status] = $totalUsers;
        }

        return response()->json($locationStatusCount);
    }
    
    public function getUserCountByAgeGroup(Request $request)
    {
       
        $ageGroups = [
            '21 - 25' => [21, 25],
            '26 - 30' => [26, 30],
            '31 - 35' => [31, 35],
            '36 - 40' => [36, 40],
            '41 - 60' => [41, 60],
            '61 - 75' => [61, 75],
        ];
    
       
        $statuses = ['Divorce', 'Unmarried', 'Widowed'];
    
       
        $ageGroupStatusCount = [];
    
        foreach ($ageGroups as $group => $range) {
        
            if (!isset($ageGroupStatusCount[$group])) {
                foreach ($statuses as $status) {
                    $ageGroupStatusCount[$group][$status] = 0; 
                }
            }
    
    
            $results = User::select(
                    DB::raw("CASE WHEN users.age >= {$range[0]} AND users.age <= {$range[1]} THEN '{$group}' END AS age_group"),
                    'users.user_marital_status',
                    DB::raw('COUNT(*) as total_users')
                )
                ->whereBetween('users.age', $range)
                ->groupBy('age_group', 'users.user_marital_status')
                ->get();
    
          
            foreach ($results as $result) {
                $status = $result->user_marital_status; 
                $totalUsers = $result->total_users;    
    
        
                $ageGroupStatusCount[$group][$status] = $totalUsers;
            }
        }
        return response()->json($ageGroupStatusCount);
    }
    

    
    // public function getUserCountByAgeGroup(Request $request)
    // {
        
    //     $ageGroups = [
    //         '18 - 20' => [18, 20],
    //         '21 - 25' => [21, 25],
    //         '26 - 30' => [26, 30],
    //         '31 - 35' => [31, 35],
    //         '36 - 40' => [36, 40],
    //         '41 - 60' => [41, 60],
    //         '61 - 75' => [61, 75],
    //     ];
    
      
    //     $ageGroupStatusCount = [];
    
       
    //     foreach ($ageGroups as $group => $range) {
    //         $results = User::select(
    //                 DB::raw("CASE WHEN users.age >= {$range[0]} AND users.age <= {$range[1]} THEN '{$group}' END AS age_group"),
    //                 'users.user_marital_status',
    //                 DB::raw('COUNT(*) as total_users')
    //             )
    //             ->whereBetween('users.age', $range)
    //             ->groupBy('age_group', 'users.user_marital_status')
    //             ->get();
    
    //         foreach ($results as $result) {
    //             $status = $result->user_marital_status; 
    //             $totalUsers = $result->total_users; 
    
    //             // // Initialize structure for each age group if not already set
    //             // if (!isset($ageGroupStatusCount[$group])) {
    //             //     $ageGroupStatusCount[$group] = [
    //             //         'Divorce' => 0,
    //             //         'Unmarried' => 0,
    //             //         'Widowed' => 0
    //             //     ];
    //             // }
    
              
    //             $ageGroupStatusCount[$group][$status] = $totalUsers;
    //         }
    //     }
    
        
    //     return response()->json($ageGroupStatusCount);
    // }
    



// public function searchUsers(Request $request)
// {
//     // Initialize query
//     $query = User::query();

//     // Apply filters based on the request parameters
//     if ($request->has('profile_id') && $request->profile_id) {
//         $query->where('id', $request->profile_id); // Assuming the primary key is 'id'
//     }

//     if ($request->has('gender') && $request->gender) {
//         $query->where('gender', $request->gender); // Replace 'gender' with your actual column name
//     }

//     if ($request->has('marital_status') && $request->marital_status) {
//         $query->where('user_marital_status', $request->marital_status);
//     }

//     if ($request->has('education_qualification') && $request->education_qualification) {
//         $query->where('user_qualification', $request->education_qualification);
//     }

//     if ($request->has('maslak') && $request->maslak) {
//         $query->where('maslak', $request->maslak); // Replace 'maslak' with your actual column name
//     }

//     if ($request->has('height') && $request->height) {
//         $query->where('height', $request->height); // Replace 'height' with your actual column name
//     }

//     if ($request->has('age') && $request->age) {
//         // Assuming age is stored in a numeric format, adjust as needed
//         $query->where('age', $request->age);
//     }

//     if ($request->has('country') && $request->country) {
//         $query->where('country', $request->country); // Replace 'country' with your actual column name
//     }

//     if ($request->has('state') && $request->state) {
//         $query->where('state', $request->state); // Replace 'state' with your actual column name
//     }

//     if ($request->has('city') && $request->city) {
//         $query->where('city', $request->city); // Replace 'city' with your actual column name
//     }

//     $users = $query->get();

    
//     return response()->json($users);
// }


// public function getUserCountByAgeGroup(Request $request)
// {
//     // Define age groups
//     $ageGroups = [
//         '21 - 25' => [21, 25],
//         '26 - 30' => [26, 30],
//         '31 - 35' => [31, 35],
//         '36 - 40' => [36, 40],
//         '41 - 60' => [41, 60],
//         '61 - 75' => [61, 75],
//     ];

//     // Define all possible marital statuses
//     $statuses = ['Divorce', 'Unmarried', 'Widowed'];

//     // Initialize the final array
//     $ageGroupStatusCount = [];

//     // Loop through each age group
//     foreach ($ageGroups as $group => $range) {
//         // Initialize the array for the age group with default counts for all statuses
//         if (!isset($ageGroupStatusCount[$group])) {
//             foreach ($statuses as $status) {
//                 $ageGroupStatusCount[$group][$status] = 0;  // Default to 0 for all statuses
//             }
//         }

//         // Query to get user count by age group and marital status
//         $results = User::select(
//                 DB::raw("CASE WHEN users.age >= {$range[0]} AND users.age <= {$range[1]} THEN '{$group}' END AS age_group"),
//                 'users.user_marital_status',
//                 DB::raw('COUNT(*) as total_users')
//             )
//             ->whereBetween('users.age', $range)
//             ->groupBy('age_group', 'users.user_marital_status')
//             ->get();

//         // Update counts based on query results
//         foreach ($results as $result) {
//             $status = $result->user_marital_status; // Get marital status
//             $totalUsers = $result->total_users;     // Get count of users

//             // Update the count for the current status in the age group
//             $ageGroupStatusCount[$group][$status] = $totalUsers;
//         }
//     }

//     // Return the response as JSON
//     return response()->json($ageGroupStatusCount);
// }


public function searchUsers(Request $request)
{
    // Initialize query
    $query = User::query();

    // If profile_id is provided, get the related users based on that profile's data
    if ($request->has('profile_id') && $request->profile_id) {
        // Fetch the user with the given profile_id
        $profileUser = User::find($request->profile_id);

        if ($profileUser) {
            // Search for related users based on this profile's attributes
            $query->where('gender', $profileUser->gender)
                  ->where('user_marital_status', $profileUser->user_marital_status)
                  ->where('user_qualification', $profileUser->user_qualification)
                  ->where('maslak', $profileUser->maslak)
                  ->where('country', $profileUser->country)
                  ->where('state', $profileUser->state)
                  ->where('city', $profileUser->city);
        } else {
            return response()->json(['message' => 'Profile not found'], 404);
        }
    }

    // Apply filters based on the request parameters (if not searching by profile_id or additional filters provided)
    if ($request->has('gender') && $request->gender) {
        $query->where('gender', $request->gender);
    }

    if ($request->has('marital_status') && $request->marital_status) {
        $query->where('user_marital_status', $request->marital_status);
    }

    if ($request->has('education_qualification') && $request->education_qualification) {
        $query->where('user_qualification', $request->education_qualification);
    }

    if ($request->has('maslak') && $request->maslak) {
        $query->where('maslak', $request->maslak);
    }

    if ($request->has('height') && $request->height) {
        $query->where('height', $request->height);
    }

    if ($request->has('age') && $request->age) {
        $query->where('age', $request->age);
    }

    if ($request->has('country') && $request->country) {
        $query->where('country', $request->country);
    }

    if ($request->has('state') && $request->state) {
        $query->where('state', $request->state);
    }

    if ($request->has('city') && $request->city) {
        $query->where('city', $request->city);
    }

    // Get the result
    $users = $query->get();

    return response()->json($users);
}

}
