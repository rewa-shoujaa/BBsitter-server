<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\WebNotificationController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Parent_user;
use App\Models\Address;
use App\Models\Children;
use App\Models\Alternative_Contact;
use App\Models\Appointment_detail;
use App\Models\Appointment;
use App\Models\Babysitter;
use App\Models\City;
use App\Models\Rating;




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

    function register_user(Request $request)
    {
        $user = new User;
        //$user->id = uniqid();
        $user->user_type = $request->user_type;
        $user->has_details = 1;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        $last_user = $user->id;

        $parent = new Parent_user;
        $address = new Address;
        //////Add Address
        //$address->address = $request->address;
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
        $parent->is_approved = 1;
        $parent->user_id = $last_user;
        $parent->phone_number = $request->phone_number;
        if ($request->picture != null) {
            $parent->picture = strval($this->Profile_picture($request->picture));
        }
        $parent->save();
        $last_parent = $parent->id;

        return ($last_parent);
    }

    public function Profile_picture($img)
    {
        $path = public_path();

        $imagedata = explode(",", $img);
        $image = $imagedata[1];
        //$image = str_replace('data:image/png;base64,', '', $img);
        $image = str_replace(' ', '+', $image);
        $imageName = "str_random(" . rand(10, 1000) . ")" . "." . "jpeg";
        \File::put($path . '/image/' . $imageName, base64_decode($image));
        $pathDB = '/image/' . $imageName;
        return ($pathDB);

    }



    function getParentDetails()
    {
        $user = Auth::user();
        $id = $user->id;
        $AllParentDetails = [];

        $UserDetails = User::where("id", $id)->get()->toArray();
        $parentDetails = Parent_user::where('user_id', $id)->get();
        $Address = Address::where('id', $parentDetails[0]->address_id)->get()->toArray();
        $parentDetails = $parentDetails->toArray();
        $AllParentDetails = array_merge($UserDetails, $parentDetails, $Address);

        return json_encode($AllParentDetails);
    //return json_encode($parentDetails[0]->address_id);

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

        $appointment = new Appointment;
        $appointment->address_id = $request->address_ID;
        $appointment->end_time = $request->end_time;
        $appointment->start_time = $request->start_time;
        $appointment->details = $request->details;
        $appointment->is_canceled = 0;
        $appointment->is_scheduled = 0;
        $appointment->parent_id = $request->Parentid;
        $appointment->save();

        $appointmentRequest = new Appointment_detail;
        $appointmentRequest->appointment_ID = $appointment->id;
        $appointmentRequest->babysitter_id = $request->babysitter_id;
        $appointmentRequest->is_approved = 0;
        $appointmentRequest->is_declined = 0;
        $appointmentRequest->save();

        $targetID = Babysitter::where('id', $request->babysitter_id)->get('user_id');
        $token = User::where('id', $targetID[0]->user_id)->get('device_key');
        $name = $user->first_name . " " . $user->last_name;
        $message = $name . " requested an appointment";
        $notificationController = new WebNotificationController;
        if ($token[0]->device_key !== null) {
            $notificationController->sendNotification($token[0]->device_key, $message);
        }

        $notificationController->sendlocalNotification($message, $targetID[0]->user_id);


        return ($token[0]->device_key);
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

    function getCountries()
    {
        $countries = City::select('country')->distinct('country')->get()->toArray();
        //$countries = $Allcountries->distinct('country')->get()->toArray();
        return json_encode($countries);

    }

    function getCities($Country)
    {
        $cities = City::select()->where('country', $Country)->get()->toArray();
        return json_encode($cities);

    }

    function getBabysittersinCity($CityID)
    {
        $user = Auth::user();
        $id = $user->id;
        $AllBabysitter = DB::table('users')
            ->join('babysitter', 'users.id', '=', 'babysitter.user_id')
            ->join('addresses', 'addresses.id', '=', 'babysitter.address_id')
            ->select('babysitter.*', 'addresses.*', 'users.*')
            ->where('addresses.city_id', '=', $CityID)
            ->where('babysitter.is_available', '=', 1)
            ->where('users.user_type', '=', 3)
            ->get()
            ->toArray();
        return json_encode($AllBabysitter);

    }

    function getAllBabysitters()
    {
        $user = Auth::user();
        $id = $user->id;
        $AllBabysitter = DB::table('users')
            ->join('babysitter', 'users.id', '=', 'babysitter.user_id')
            ->join('addresses', 'addresses.id', '=', 'babysitter.address_id')
            ->select('babysitter.*', 'addresses.*', 'users.*')
            ->where('babysitter.is_available', '=', 1)
            ->where('users.user_type', '=', 3)
            ->get()
            ->toArray();
        return json_encode($AllBabysitter);

    }

    function getBabysitterDetails($id)
    {
        $AllDetails = [];

        $UserDetails = User::where("id", $id)->get()->toArray();
        $babysitterDetails = Babysitter::where('user_id', $id)->get();
        $Address = Address::where('id', $babysitterDetails[0]->address_id)->get()->toArray();
        $babysitterDetails = $babysitterDetails->toArray();
        $AllDetails = array_merge($UserDetails, $babysitterDetails, $Address);

        return json_encode($AllDetails);
    //return json_encode($parentDetails[0]->address_id);

    }
    function Search(Request $request)
    {
        $CityID = $request->city;

        $user = Auth::user();
        $id = $user->id;
        $AllBabysitter = DB::table('users')
            ->join('babysitter', 'users.id', '=', 'babysitter.user_id')
            ->join('addresses', 'addresses.id', '=', 'babysitter.address_id')
            ->select('babysitter.*', 'addresses.*', 'users.*')
            ->where('addresses.city_id', '=', $CityID)
            ->where('babysitter.is_available', '=', 1)
            ->where('users.user_type', '=', 3);

        $searchBabysitter = $AllBabysitter->where('first_name', 'LIKE', '%' . $request->name . '%')
            ->orWhere('last_name', 'LIKE', '%' . $request->name . '%')
            ->get()
            ->toArray();

        return json_encode($searchBabysitter);
    }

    function getScheduled()
    {
        $user = Auth::user();
        $mytime = Carbon::now();
        $id = $user->id;
        $Parent = Parent_user::where('user_id', $id)->first();
        $Parent_ID = $Parent->id;
        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->join('addresses', 'addresses.id', '=', 'appointments.address_id')
            ->join('babysitter', 'babysitter.id', '=', 'appointment_details.babysitter_id')
            ->join('users', 'users.id', '=', 'babysitter.user_id')
            ->select('appointments.*', 'appointment_details.*', 'addresses.*', 'users.*', 'babysitter.*', 'appointment_details.id AS AppDetailsID')
            ->where('appointments.parent_id', '=', $Parent_ID)
            ->where('appointments.is_scheduled', '=', 1)
            ->where('appointments.is_canceled', '=', 0)
            ->where('appointment_details.is_declined', '=', 0)
            ->where('appointment_details.is_approved', '=', 1)
            ->Where('appointments.start_time', '>', $mytime)
            ->orderBy('appointments.start_time', 'ASC')
            ->get()
            ->toArray();

        return json_encode($users);
    }

    function getPending()
    {
        $user = Auth::user();
        $mytime = Carbon::now();
        $id = $user->id;
        $Parent = Parent_user::where('user_id', $id)->first();
        $Parent_ID = $Parent->id;
        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->join('addresses', 'addresses.id', '=', 'appointments.address_id')
            ->join('babysitter', 'babysitter.id', '=', 'appointment_details.babysitter_id')
            ->join('users', 'users.id', '=', 'babysitter.user_id')
            ->select('appointments.*', 'appointment_details.*', 'addresses.*', 'users.*', 'babysitter.*', 'appointment_details.id AS AppDetailsID')
            ->where('appointments.parent_id', '=', $Parent_ID)
            ->where('appointments.is_scheduled', '=', 0)
            ->where('appointments.is_canceled', '=', 0)
            ->where('appointment_details.is_declined', '=', 0)
            ->where('appointment_details.is_approved', '=', 0)
            ->Where('appointments.start_time', '>', $mytime)
            ->orderBy('appointments.start_time', 'ASC')
            ->get()
            ->toArray();

        return json_encode($users);
    }

    function Cancel($id)
    {
        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->where('appointment_details.id', '=', $id)
            ->where('appointments.is_scheduled', '=', 0)
            ->where('appointments.is_canceled', '=', 0)
            ->where('appointment_details.is_approved', '=', 0);
        $check = $users->get()->toArray();
        if (count($check) > 0) {
            $users->update([
                'is_canceled' => 1,
            ]);
            return ("Success");
        }
        else {
            return ("failed");
        }

    }

    function FeelingLucky(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;

        $CityID = $request->city;

        $AllBabysitter = DB::table('babysitter')
            ->join('addresses', 'addresses.id', '=', 'babysitter.address_id')
            ->select('babysitter.*')
            ->where('addresses.city_id', '=', $CityID)
            ->where('babysitter.is_available', '=', 1)
            ->get()
            ->toArray();


        $appointment = new Appointment;
        $appointment->address_id = $request->address_ID;
        $appointment->end_time = $request->end_time;
        $appointment->start_time = $request->start_time;
        $appointment->details = $request->details;
        $appointment->is_canceled = 0;
        $appointment->is_scheduled = 0;
        $appointment->parent_id = $request->Parentid;
        $appointment->save();
        foreach ($AllBabysitter as $babysitter) {
            $appointmentRequest = new Appointment_detail;
            $appointmentRequest->appointment_ID = $appointment->id;
            $appointmentRequest->babysitter_id = $babysitter->id;
            $appointmentRequest->is_approved = 0;
            $appointmentRequest->is_declined = 0;
            $appointmentRequest->save();
        }
        return ($AllBabysitter);
    }

    function AddRating(Request $request)
    {
        $BabysitterID = $request->babysitterID;
        $ParentID = $request->ParentID;
        $rating = Rating::where('source_user_id', $ParentID)->where('target_user_id', $BabysitterID);
        $ratingget = $rating->first();
        if ($ratingget === null) {
            $AddRating = new Rating;
            $AddRating->source_user_id = $ParentID;
            $AddRating->target_user_id = $BabysitterID;
            $AddRating->rating = $request->rating;
            $AddRating->comment = $request->comment;
            $AddRating->save();

        }
        else {
            $rating->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

        }
    }

    function getBabysitterDetailswithRatings($id)
    {
        $AllDetails = [];
        $avg = [];

        $UserDetails = User::where("id", $id)->get()->toArray();
        $babysitterDetails = Babysitter::where('user_id', $id)->get();
        $Address = Address::where('id', $babysitterDetails[0]->address_id)->get()->toArray();
        $Rating = Rating::where('target_user_id', $babysitterDetails[0]->id)->pluck('rating')->toArray();
        $average = 0;
        if (count($Rating) > 0) {
            $average = array_sum($Rating) / count($Rating);
            round($average, 1);

        }
        array_push($avg, $average);
        $babysitterDetails = $babysitterDetails->toArray();
        $AllDetails = array_merge($UserDetails, $babysitterDetails, $Address, $avg);

        return json_encode($AllDetails);
    //return json_encode($parentDetails[0]->address_id);

    }
}
