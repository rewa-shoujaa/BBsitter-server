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


class BabysitterController extends Controller
{
    function test()
    {
        $user = Auth::user();
        $id = $user->id;
        return json_encode(Auth::user());
    }


    function setBabySitterDetails(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;

        if ($user->has_details == 0) {
            $user->has_details = 1;
            $user->save();
            $babysitter = new Babysitter;
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
            $babysitter->address_id = $last_address;
            $babysitter->gender = $request->gender;
            $babysitter->date_of_birth = $request->DoB;
            $babysitter->is_approved = 0;
            $babysitter->is_available = 1;
            $babysitter->qualifications = $request->qualifications;
            $babysitter->experience = $request->experience;
            $babysitter->user_id = $id;
            $babysitter->phone_number = $request->phone_number;
            $babysitter->picture = $request->picture;
            $babysitter->save();
            $last_parent = $babysitter->id;

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

    function AcceptAppointment(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;
        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->where('appointment_details.id', '=', $request->reqID);
        $users->is_approved = 1;
        $users->is_scheduled = 1;
        $users->update();


        return json_encode(Auth::user());
    }

    function getAppointmentRequests()
    {
        $user = Auth::user();
        $mytime = Carbon\Carbon::now();
        $id = $user->id;
        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->select('appointments.*', 'appointment_details.*')
            ->where('appointment_details.babysitter_id', '=', $id)
            ->where('appointments.is_scheduled', '=', 0)
            ->Where('appointment_details.start_time', '>', $mytime)
            ->orderBy('appointment_details.start_time', 'ASC')
            ->get();
    //$appointmentRequest=Appointment_detail::where('babysitter_id',$id)->get();
    //$appointment=Appointment::where('id',$appointmentRequest->appointment_ID)->where('is_scheduled',0)->where('start_time','>',)
    //return json_encode(Auth::user());
    }

    function setNotAvailable()
    {
        $user = Auth::user();
        $id = $user->id;

        $babysitter = Babysitter::where('user_id', $id)->get();
        $babysitter->is_available = 0;

        return json_encode(Auth::user());
    }




}
