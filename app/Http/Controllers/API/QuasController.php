<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notification;
use App\NotificationContent;
use App\NotificationProgram;
use Illuminate\Http\Request;

class QuasController extends Controller
{
    public function acceptDeclineReschedule(request $request, $id,$userID){
        $notif = Notification::where('id', $id)->first();
        $program = NotificationProgram::where('notification_id', $notif->id)->first();
        $content = new NotificationContent();
        $content->content = $request->message;
        if($request->decision  == 'accepted') $content->notif_type = 'accepted reschedule';
        else $content->notif_type = 'declined reschedule';
        $content->save();

        $notification = new Notification();
        $notification->recipient_id = $notif->sender_id;
        $notification->sender_id = $userID;
        $notification->notification_id = $content->id;
        $notification->status = 0;
        $notification->save();

        $notifProgram = new NotificationProgram();
        $notifProgram->notification_id = $notification->id;
        $notifProgram->applied_program_id = $program->applied_program_id;
        $notifProgram->save();

        if($request->decision  == 'accepted') return response()->json(['status' => true, 'message' => 'Successfully Accepted Schedule']);
        else return response()->json(['status' => true, 'message' => 'Declined Schedule']);
    }
}
