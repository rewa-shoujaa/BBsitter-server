<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\API\WebNotificationController;

use App\Models\Parent_user;
use App\Models\Address;
use App\Models\Children;
use App\Models\Alternative_Contact;
use App\Models\Appointment_detail;
use App\Models\Appointment;
use App\Models\Babysitter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
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

    function register_babysitter(Request $request)
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
        $lastUserID = $user->id;

        $babysitter = new Babysitter;
        $address = new Address;

        //////Add Address
        $address->address_latitude = $request->latitude;
        $address->address_longitude = $request->longitude;
        $address->city_id = $request->city;
        $address->country = $request->country;
        $address->is_active = 1;
        $address->save();
        $last_address = $address->id;

        ///////BabySitter
        $babysitter->address_id = $last_address;
        $babysitter->gender = $request->gender;
        $babysitter->date_of_birth = $request->DoB;
        $babysitter->is_approved = 1;
        $babysitter->is_available = 1;
        $babysitter->qualifications = $request->qualifications;
        $babysitter->about_me = $request->aboutme;
        $babysitter->user_id = $lastUserID;
        $babysitter->phone_number = $request->phone_number;
        $babysitter->rate = $request->rate;
        if ($request->picture != null) {
            $babysitter->picture = strval($this->Profile_picture($request->picture));
        }
        if ($request->cv != null) {
            $babysitter->qualifications = strval($this->CV_upload($request->cv));
        }
        $babysitter->save();
        $last_parent = $babysitter->id;

        return json_encode("Hello");
    }

    // decode pdf CV from base 64 and put it in CV folder 

    public function CV_upload($cv)
    {
        $path = public_path();

        $image = str_replace('application/pdf;base64,', '', $cv);
        $image = str_replace(' ', '+', $image);
        $imageName = "str_random(" . rand(10, 1000) . ")" . "." . "pdf";
        \File::put($path . '/CV/' . $imageName, base64_decode($image));
        $pathDB = '/CV/' . $imageName;
        return ($pathDB);

    }


    // decode picture from base 64 and put it in image folder 
    public function Profile_picture($img)
    {
        $path = public_path();

        $imagedata = explode(",", $img);
        $image = $imagedata[1];
        $image = str_replace(' ', '+', $image);
        $imageName = "str_random(" . rand(10, 1000) . ")" . "." . "jpeg";
        \File::put($path . '/image/' . $imageName, base64_decode($image));
        $pathDB = '/image/' . $imageName;
        return ($pathDB);

    }

    function getBabysitterDetails()
    {
        $user = Auth::user();
        $id = $user->id;
        $AllBabysitterDetails = [];

        $UserDetails = User::where("id", $id)->get()->toArray();
        $babysitterDetails = Babysitter::where('user_id', $id)->get();
        $Address = Address::where('id', $babysitterDetails[0]->address_id)->get()->toArray();
        $babysitterDetails = $babysitterDetails->toArray();
        $AllBabysitterDetails = array_merge($UserDetails, $babysitterDetails, $Address);

        return json_encode($AllBabysitterDetails);
    //return json_encode($parentDetails[0]->address_id);

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

    function AcceptAppointment($id)
    {
        $main_user = Auth::user();
        $main_id = $main_user->id;

        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->where('appointment_details.id', '=', $id)
            ->where('appointments.is_scheduled', '=', 0)
            ->where('appointments.is_canceled', '=', 0)
            ->where('appointment_details.is_declined', '=', 0)
            ->where('appointment_details.is_approved', '=', 0);
        $check = $users->get()->toArray();
        if (count($check) > 0) {
            $users->update([
                'is_approved' => 1,
                'is_scheduled' => 1,
            ]);
            $user_approved = DB::table('appointments')
                ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
                ->where('appointment_details.id', '=', $id)
                ->where('appointments.is_scheduled', '=', 1)
                ->where('appointments.is_canceled', '=', 0)
                ->where('appointment_details.is_declined', '=', 0)
                ->where('appointment_details.is_approved', '=', 1);

            $Parent_ID = $user_approved->get('parent_id');
            $ParentUserID = Parent_user::where('id', $Parent_ID[0]->parent_id)->get('user_id');
            $token = User::where('id', $ParentUserID[0]->user_id)->get('device_key');
            $name = $main_user->first_name . " " . $main_user->last_name;
            $message = $name . " accepted your appointment request";
            $notificationController = new WebNotificationController;
            if ($token[0]->device_key !== null) {
                $notificationController->sendNotification($token[0]->device_key, $message);
            }

            $notificationController->sendlocalNotification($message, $ParentUserID[0]->user_id);
            //return ("Success");
            return ("Success");

        }
        else {
            return ("failed");
        }
    //return ($users);

    }

    function DeclineAppointment($id)
    {

        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->where('appointment_details.id', '=', $id)
            ->where('appointments.is_scheduled', '=', 0)
            ->where('appointments.is_canceled', '=', 0)
            ->where('appointment_details.is_declined', '=', 0)
            ->where('appointment_details.is_approved', '=', 0);
        $check = $users->get()->toArray();
        if (count($check) > 0) {
            $users->update([
                'is_declined' => 1,
            ]);
            //$users->is_declined = 1;
            //$users->update();
            return ("Success");
        }
        else {
            return ("failed");
        }

    }


    // get Pending appointments requests for babysitter

    function getAppointmentRequests()
    {
        $user = Auth::user();
        $mytime = Carbon::now();
        $id = $user->id;
        $BB = Babysitter::where('user_id', $id)->first();
        $BB_ID = $BB->id;
        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->join('addresses', 'addresses.id', '=', 'appointments.address_id')
            ->join('parents', 'parents.id', '=', 'appointments.parent_id')
            ->join('users', 'users.id', '=', 'parents.user_id')
            ->select('appointments.*', 'appointment_details.*', 'addresses.*', 'users.*', 'parents.*', 'appointment_details.id AS AppDetailsID')
            ->where('appointment_details.babysitter_id', '=', $BB_ID)
            ->where('appointments.is_scheduled', '=', 0)
            ->where('appointments.is_canceled', '=', 0)
            ->where('appointment_details.is_declined', '=', 0)
            ->where('appointment_details.is_approved', '=', 0)
            ->Where('appointments.start_time', '>', $mytime)
            ->orderBy('appointments.start_time', 'ASC')
            ->get()
            ->toArray();
        return ($users);

    //return json_encode($users);

    }


    // Get Scheduled appointments for babysitter 

    function getAppointmentScheduled()
    {
        $user = Auth::user();
        $mytime = Carbon::now();
        $id = $user->id;
        $BB = Babysitter::where('user_id', $id)->first();
        $BB_ID = $BB->id;
        $users = DB::table('appointments')
            ->join('appointment_details', 'appointments.id', '=', 'appointment_details.appointment_ID')
            ->join('addresses', 'addresses.id', '=', 'appointments.address_id')
            ->join('parents', 'parents.id', '=', 'appointments.parent_id')
            ->join('users', 'users.id', '=', 'parents.user_id')
            ->select('appointments.*', 'appointment_details.*', 'addresses.*', 'users.*', 'parents.*', 'appointment_details.id AS AppDetailsID')
            ->where('appointment_details.babysitter_id', '=', $BB_ID)
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

    function setNotAvailable()
    {
        $user = Auth::user();
        $id = $user->id;

        $babysitter = Babysitter::where('user_id', $id)->get();
        $babysitter->is_available = 0;

        return json_encode(Auth::user());
    }




}
