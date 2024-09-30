<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bride;
use App\Models\Location;
use App\Models\Profession;
use App\Models\Qualification;
use App\Models\Proposal;
use App\Models\Viewed;
use App\Models\Groom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BrideController extends Controller
{
    public function __construct()
    {
        // Dependency Injection could also be used if needed
    }

    public function editProfile(Request $request, $id)
    {
        $bride = Bride::find($id);
        $india = Location::where('country_name', 'india')->first();

        if (Session::get('userid') == $id) {
            if ($bride) {

                $validated = $request->validate([
                    'full_name' => 'required|regex:/^([a-zA-Z]|\s)+$/|max:40',
                ]);

                if ($request->has('contact_person')) {
                    foreach ($request->input('contact_person') as $key => $contact) {
                        $required = '';
                        if ($key == 0) {
                            $required = 'required|';
                        }
                    }
                }

                if ($request->has('user_location_country') && $request->input('user_location_country') == $india->country_id) {
                    // Additional logic here
                }

                if ($validated) {
                    $age = strtotime($request->input('age-date') . '-' . $request->input('age-month') . '-' . $request->input('age-year'));
                    $partner_age = $request->input('age-range-from') . '~' . $request->input('age-range-to');
                    $partner_marital = $request->input('part_marital1') . '~' . $request->input('part_marital2') . '~' . $request->input('part_marital3');
                    $partner_height = $request->input('height-range-min') . '~' . $request->input('height-range-max');
                    $partner_maslak = implode('~', $request->input('part_maslak'));

                    $partner_quali = implode(',', $request->input('partqual'));
                    $partner_current_location = $request->input('part-curr-location-hidden');
                    $partner_native_location = $request->input('part-native-location-hidden');

                    $params = [
                        'full_name' => $request->input('full_name'),
                        'email' => $request->input('email'),
                        'user_height' => $request->input('user_height'),
                        'user_namaz_type' => $request->input('user_namaz_type'),
                        'age' => $age,
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
                    ];

                    if (!empty(trim($partner_quali))) {
                        $params['part_pref_edu_quali'] = $partner_quali;
                    }

                    Bride::where('id', $id)->update($params);

                    return redirect()->route('bride.viewProfile', ['id' => $id]);
                }
            } else {
                abort(404, 'Invalid bride.');
            }
        } else {
            abort(403, 'Not Authorized to access this page.');
        }
    }

    public function viewProfile($id)
    {
        $bride = Bride::find($id);
        $userid = Session::get('userid');

        if ($userid) {
            if ($bride && $bride->user_status == 'active') {
                return view('frontend.view_profile_bride', [
                    'bride' => $bride,
                    'bride_family' => User::find($bride->id)->family,
                    'qualifications' => Qualification::all(),
                ]);
            } else {
                Session::flash('message', 'The user has deactivated the profile.');
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('login', ['type' => 'bride', 'profile' => 'view_profile', 'id' => $id]);
        }
    }






    public function familyDetails($id)
    {
        $bride = Bride::find($id);
        if (!$bride) {
            return response()->json(['error' => 'Bride not found'], 404);
        }

        // Get family details
        $family = User::getUserFamily($bride->id);

        return response()->json([
            'bride' => $bride,
            'family' => $family,
        ]);
    }

    public function partnerPreference($id)
    {
        $bride = Bride::find($id);
        if (!$bride) {
            return response()->json(['error' => 'Bride not found'], 404);
        }

        // Get partner preference details
        return response()->json([
            'partner_preferences' => $bride->partnerPreferences,
        ]);
    }

    public function socialMedia($id)
    {
        $bride = Bride::find($id);
        if (!$bride) {
            return response()->json(['error' => 'Bride not found'], 404);
        }

        // Get social media details
        return response()->json([
            'facebook_profile_link' => $bride->facebook_profile_link,
            'twitter_profile_link' => $bride->twitter_profile_link,
            // Other social media links
        ]);
    }

    public function deleteProfile(Request $request, $id)
    {
        $bride = Bride::find($id);
        if (!$bride || $bride->id != Auth::id()) {
            return response()->json(['error' => 'Not authorized or bride not found'], 403);
        }

        // Handle deletion logic
        $bride->deactivateProfile();

        return response()->json(['message' => 'Profile deletion initiated, check your email for confirmation']);
    }

    public function deleteVerification(Request $request, $id)
    {
        $bride = Bride::find($id);
        if (!$bride || $bride->id != Auth::id()) {
            return response()->json(['error' => 'Not authorized or bride not found'], 403);
        }

        // Final deletion logic
        $bride->delete();

        return response()->json(['message' => 'Profile deleted successfully']);
    }

}
