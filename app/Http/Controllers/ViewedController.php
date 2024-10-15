<?php

namespace App\Http\Controllers;

use App\Models\Viewed;
use Illuminate\Http\Request;

class ViewedController extends Controller
{
    // Increment view count
    public function incrementView(Request $request, $to_id)
    {
        $from_id = $request->input('from_id'); // Get viewer's ID

        // Check if the same viewer has already viewed the profile to avoid duplicates
        $existingView = Viewed::where('from_id', $from_id)
                              ->where('to_id', $to_id)
                              ->first();

        if (!$existingView) {
            Viewed::create([
                'from_id' => $from_id,
                'to_id' => $to_id,
                'status' => 1, // Mark as viewed (optional)
            ]);
        }

        return response()->json(['message' => 'View incremented'], 200);
    }






    // Get total views for a specific user profile
    public function getTotalViews($user_view_id)
    {
        $totalViews = Viewed::where('to_id', $user_view_id)->count();
        return response()->json(['total_views' => $totalViews], 200);
    }
}
