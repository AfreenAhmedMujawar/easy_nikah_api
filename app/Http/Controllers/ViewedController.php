<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewedController extends Controller
{
    // 1. Get user viewed profiles
    public function getUserViewed($id, Request $request)
    {
        // Accepting status and switch as optional inputs from the request
        $having_status = $request->input('status', []);
        $switch = $request->input('switch', 0);

        // Building conditions based on inputs
        $status_condition = '';
        $switch_condition = '';

        if (!empty($having_status)) {
            $status_condition = ' AND uv.status IN ("' . implode('", "', $having_status) . '")';
        }

        if ($switch == 1) {
            $switch_condition = ' AND uv.to_id =' . intval($id);
        } elseif ($switch == 2) {
            $switch_condition = ' AND uv.from_id =' . intval($id);
        }

        // The query to fetch viewed user profiles
        $sql = "
            SELECT u.id, u.profile_id, u.full_name, q.qualification_name, p.profession_name,
            TIMESTAMPDIFF(YEAR, u.age, CURDATE()) as age, u.user_height, u.user_marital_status, u.gender, ur.status
            FROM users u
            LEFT JOIN user_relationships ur ON (ur.from_id = u.id OR ur.to_id = u.id)
            LEFT JOIN qualifications q ON q.qualification_id = u.user_qualification
            LEFT JOIN professions p ON p.id = u.user_profession
            LEFT JOIN user_view uv ON (uv.from_id = u.id OR uv.to_id = u.id)
            WHERE 1=1
            AND (ur.from_id = " . intval($id) . " OR ur.to_id = " . intval($id) . ")
            AND (uv.from_id = " . intval($id) . " OR uv.to_id = " . intval($id) . ")
            $status_condition
            $switch_condition
            AND u.id != " . intval($id) . "
            GROUP BY u.id
        ";

        $result = DB::select(DB::raw($sql));

        return response()->json($result);
    }

    // 2. Get specific viewed profile details
    public function getViewed($from_id, $to_id)
    {
        $sql = "
            SELECT * FROM user_view 
            WHERE (from_id = " . intval($from_id) . " AND to_id = " . intval($to_id) . ")
            OR (to_id = " . intval($from_id) . " AND from_id = " . intval($to_id) . ")
        ";

        $result = DB::select(DB::raw($sql));

        return response()->json($result);
    }

    // 3. Get viewed count for a specific user
    public function getViewedCount($id)
    {
        $count = DB::table('user_view')
            ->where('to_id', $id)
            ->count();

        return response()->json(['count' => $count]);
    }

    // 4. Add a new viewed profile entry
    public function addViewed(Request $request)
    {
        // Validating input
        $validated = $request->validate([
            'from_id' => 'required|integer',
            'to_id' => 'required|integer',
        ]);

        // Data to insert
        $add_data = [
            'from_id' => $validated['from_id'],
            'to_id' => $validated['to_id'],
            'status' => 1
        ];

        // Insert into DB
        DB::table('user_view')->insert($add_data);

        return response()->json(['message' => 'Viewed profile added successfully'], 201);
    }

    // 5. Edit an existing viewed profile entry
    public function editViewed(Request $request)
    {
        // Validating input
        $validated = $request->validate([
            'from_id' => 'required|integer',
            'to_id' => 'required|integer',
            'status' => 'required|integer',
        ]);

        // Data to update
        $edit_data = [
            'status' => $validated['status']
        ];

        // Update in the database
        DB::table('user_view')
            ->where(function ($query) use ($validated) {
                $query->where(['from_id' => $validated['from_id'], 'to_id' => $validated['to_id']])
                    ->orWhere(['to_id' => $validated['from_id'], 'from_id' => $validated['to_id']]);
            })
            ->update($edit_data);

        return response()->json(['message' => 'Viewed profile updated successfully'], 200);
    }







    public function incrementView($id, Request $request)
    {
        // Assuming you have logic to validate user authentication
        $userId = $request->user()->id; // Get the authenticated user's ID

        // Check if a view already exists for this user pair
        $view = UserView::where('from_id', $userId)
            ->where('to_id', $id)
            ->first();

        if ($view) {
            // If the view exists, you might want to update the status
            // $view->status = ...; // Set the appropriate status if needed
            $view->save(); // Save if you've made any updates
        } else {
            // If it doesn't exist, create a new entry
            UserView::create([
                'from_id' => $userId,
                'to_id' => $id,
                'status' => 1, // Set status to viewed
            ]);
        }

        // Return a success response
        return response()->json(['message' => 'View incremented successfully.'], 200);
    }
















    public function recordView($id)
    {
        $userId = Auth::id(); // Get current authenticated user
        $post = Post::findOrFail($id); // Find the post by ID

        // Check if the user already viewed this post
        $alreadyViewed = UserView::where('from_id', $userId)
                                  ->where('to_id', $post->user_id)
                                  ->exists();

        if (!$alreadyViewed) {
            // Create a new view entry
            UserView::create([
                'from_id' => $userId,
                'to_id' => $post->user_id,
                'status' => 'viewed',
            ]);

            // Increment the post's views count
            $post->increment('views');
        }

        return response()->json([
            'message' => $alreadyViewed ? 'Already viewed' : 'View recorded',
            'view_count' => $post->views,
        ], 200);
    }

    // List all posts with their view counts
    public function index()
    {
        $posts = Post::withCount('views')->get(); // Fetch posts with view counts

        return response()->json([
            'posts' => $posts,
        ], 200);
    }
}
