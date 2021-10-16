<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Babysitter;
use App\Models\Parent_user;

use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use Auth;

class AdminController extends Controller
{
    function test()
    {
        $user = Auth::user();
        $id = $user->id;
        return json_encode(Auth::user());
    }

    function getAllParents()
    {
        $AllPArents = User::where("user_type", 2)->where("has_details", 1)->with(['parent'])->get()->toArray();
        return json_encode($AllPArents);
    }



    function getAllBabysitters()
    {
        $AllBabysitter = User::where("user_type", 3)->where("has_details", 1)->with(['babysitter'])->get()->toArray();
        return json_encode($AllBabysitter);

    }

    function activate_parent($id)
    {
        $user_parent = Parent_user::where('user_id', $id)->first();
        $user_parent->is_approved = 1;
        $user_parent->save();
        return ($user_parent);

    }

    function deactivate_parent($id)
    {
        $user_parent = Parent_user::where('user_id', $id)->first();
        $user_parent->is_approved = 0;
        $user_parent->save();
        return ($user_parent);
    }
    function activate_babysitter($id)
    {
        $user_parent = Babysitter::where('user_id', $id)->first();
        $user_parent->is_approved = 1;
        $user_parent->save();
        return ($user_parent);
    }
    function deactivate_babysitter($id)
    {
        $user_parent = Babysitter::where('user_id', $id)->first();
        $user_parent->is_approved = 0;
        $user_parent->save();
        return ($user_parent);
    }
}
