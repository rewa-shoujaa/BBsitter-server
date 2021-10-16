<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\User;

use App\Models\Parent_user;
use App\Models\Address;
use App\Models\Children;
use App\Models\Alternative_Contact;
use App\Models\Appointment_detail;
use App\Models\Appointment;
use App\Models\Babysitter;



use Illuminate\Support\Facades\Hash;


use Illuminate\Http\Request;
use Auth;

class UserController extends Controller
{

    function login(Request $request)
    {
        $data = $request->only("email", "password");

        if (Auth::attempt($data)) {
            return redirect()->route("home");
        }

        return redirect()->route("index");

    }

    function test()
    {
        $user = Auth::user();
        $id = $user->id;
        return json_encode(Auth::user());
    }

    function getParentDetails()
    {
        $user = Auth::user();
        $id = $user->id;
        $AllParentDetails = [];

        $UserDetails = User::where("id", $id)->get()->toArray();
        $parentDetails = Parent_user::where('user_id', $id)->with(['address'])->get()->toArray();
        $AllParentDetails = array_merge($UserDetails, $parentDetails);

        return json_encode($AllParentDetails);

    }

    function setParentDetails(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;

        if ($user->has_details == 0) {
            $user->has_details = 1;
            $user->save();
            $parent = new Parent_user;
            $alternativeContact = new Alternative_Contact;
            $address = new Address;
            $child = new Children;
            //////Add Address
            $address->address = $request->address;
            $address->address_latitude = $request->latitude;
            $address->address_longitude = $request->longitude;
            $address->city_id = $request->city;
            $address->country = $request->country;
            $address->is_active = 1;
            $address->save();
            $last_address = $address->id;

            /////////Add Parent Details
            $parent->address_id = $last_address;
            $parent->gender = $request->gender;
            $parent->date_of_birth = $request->DoB;
            $parent->is_approved = 0;
            $parent->user_id = $id;
            $parent->phone_number = $request->phone_number;
            $parent->picture = $request->picture;
            $parent->save();
            $last_parent = $parent->id;

            /////////Add Alternative Contact
            //$alternativeContact->details = $request->cntdetail;
            //$alternativeContact->first_name=$request->cntfirst_name;
            //$alternativeContact->last_name=$request->cntlast_name;
            //$alternativeContact->phone_number=$request->cntphone_number;
            //$alternativeContact->is_active=1;
            //$alternativeContact->parent_id=$last_parent;
            //$alternativeContact->save();

            /////////Add Children
            //$child
            return $last_parent;

        }
        else {
            $parent = Parent_user::where('user_id', $id)->first();
            $address_ID = $parent->address_id;
            $address = Address::where('id', $address_ID)->first();
            //////Update Address
            $address->address = $request->address;
            $address->address_latitude = $request->latitude;
            $address->address_longitude = $request->longitude;
            $address->city_id = $request->city;
            $address->country = $request->country;
            $address->save();

            /////////Update Parent Details
            $parent->gender = $request->gender;
            $parent->date_of_birth = $request->DoB;
            $parent->phone_number = $request->phone_number;
            $parent->picture = $request->picture;
            $parent->save();

        }

    // return json_encode($AllParentDetails);

    }

    function BookAppointment(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;
        if ($request->is_user_address == 1) {
            ////////get the address of the user
            $parent = Parent_user::where('user_id', $id)->first();
            $address_ID = $parent->address_id;
        }
        else {
            //////// Create new address
            $address = new Address;
            $address->address = $request->address;
            $address->address_latitude = $request->latitude;
            $address->address_longitude = $request->longitude;
            $address->city_id = $request->city;
            $address->country = $request->country;
            $address->is_active = 1;
            $address->save();
            $address_ID = $address->id;
        }
        $appointment = new Appointment;
        $appointment->address_id = $address_ID;
        $appointment->end_time = $request->end_time;
        $appointment->start_time = $request->start_time;
        $appointment->is_canceled = 0;
        $appointment->is_scheduled = 0;
        $appointment->parent_id = $parent->id;
        $appointment->save();
        return ($appointment->id);
    }

    function SendAppointmentRequest(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;
        $appointmentRequest = new Appointment_detail;
        $appointmentRequest->appointment_ID = $request->appointment_ID;
        $appointmentRequest->babysitter_id = $request->babysitter_id;
        $appointmentRequest->is_approved = 0;
        $appointmentRequest->is_canceled = 0;
        $appointmentRequest->save();
        return ($appointmentRequest->id);
    }

    function getBabySitterinCity(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;
        $appointment = Appointment::where('id', $request->appointment_id)->get();
        $addressID = $appointment->address_id;
        $address = Address::where('id', $addressID)->get();
        $cityID = $address->city_id;
        $Babysitters = Babysitter::with(['address'])->where('city_id', $cityID)->where('is_approved', 1)->where('is_approved', 1)->get('is_available', 1)->toArray();

        return json_encode($Babysitters);
    }



}
