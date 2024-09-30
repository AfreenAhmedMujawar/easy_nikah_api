<?php

namespace App\Http\Controllers;




use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
  
    public function getCountries()
    {
        $countries = Country::all(); 
        return response()->json($countries);
    }

 
    public function getStates($country_id)
    {
        $states = State::where('country_id', $country_id)->get();
        return response()->json($states);
    }

   
    public function getCities($state_id)
    {
        $cities = City::where('state_id', $state_id)->get();
        return response()->json($cities);
    }

    public function getLocations()
    {
        $countries = Country::with('states.cities')->get(); 
        return response()->json($countries);
    }

public function getUserLocations($userId)
{
    
    $locations = Location::with(['country', 'state', 'city'])
        ->where('user_id', $userId)
        ->get();

    return response()->json($locations);
}

public function addUserLocation(Request $request, $userId)
{
    $location = Location::create([
        'user_id' => $userId,
        'country_id' => $request->country_id,
        'state_id' => $request->state_id,
        'city_id' => $request->city_id,
    ]);

    return response()->json($location);
}


}




