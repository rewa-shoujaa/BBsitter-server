<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Parent_user;
use App\Models\Babysitter;
use App\Models\Notification;

use Auth;

use Illuminate\Http\Request;

class WebNotificationController extends Controller
{

    //Save FCM token in users table
    public function saveDeviceToken(Request $request)
    {
        $user = Auth::user();
        $id = $user->id;
        $user->update(['device_key' => $request->token]);
        return response()->json(['Token stored.']);
    }


    //Send push notification from Laravel to firebase

    public function sendNotification($device_token, $message)
    {
        $SERVER_API_KEY = 'AAAA9SYcpQo:APA91bEATec8oeYCYTeqz45gd82p01Cl0AjOtLlYwZ_qI87Ip8IwQw8KjZhlf82b1625BeiosR5V7P5cAVBti7WK0Tfy_xq6TZdvn1iLwHJV1e2jmwZ1POdYzl57dxOUdZ_VpvttRiQG';


        // payload data, it will vary according to requirement
        $data = [
            "to" => $device_token, // for single device id
            "data" => [
                "title" => 'notification',
                "body" => $message,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    //Add Notification to local table in database

    public function sendlocalNotification($notification_body, $targetID)
    {

        $noti = new Notification;
        $noti->target_id = $targetID;
        $noti->is_read = 0;
        $noti->text = $notification_body;
        $noti->save();

        return response()->json(['success']);
    }

    public function setRead()
    {
        $user = Auth::user();
        $id = $user->id;
        $noti = Notification::where('target_id', $id)
            ->update(['is_read' => 1]);
        return response()->json(['set Read']);
    }

    public function getNotifications()
    {
        $user = Auth::user();
        $id = $user->id;
        $noti = Notification::where('target_id', $id)->where('is_read', 0)->get()->toArray();
        return json_encode($noti);
    }




}
