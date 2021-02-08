<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notification;
use App\NotificationContent;
use App\NotificationProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function showAllNotification($id){
        $notification = DB::table('notifications')
            ->join('notification_contents', 'notification_contents.id', '=', 'notifications.notification_id')
            ->join('users', 'users.id', '=', 'notifications.sender_id')
            ->join('notifications_programs', 'notifications_programs.notification_id','=','notifications.id')
            ->where('notifications.recipient_id', $id)
            ->select('notifications.*', 'notification_contents.content','notification_contents.notif_type', 'users.first_name', 'users.last_name', 'users.email', 'notifications_programs.applied_program_id')
            ->get();
        return response()->json($notification);
    }
    public function viewNotication($id){
        $content = DB::table('notifications')
            ->join('notification_contents', 'notification_contents.id', '=', 'notifications.notification_id')
            ->join('users', 'users.id', '=', 'notifications.sender_id')
            ->join('notifications_programs', 'notifications_programs.notification_id', '=', 'notifications.id')
            ->where('notifications.id', $id)
            ->select('notifications.*', 'notification_contents.content','notification_contents.notif_type', 'users.first_name', 'users.last_name', 'users.email', 'notifications_programs.applied_program_id')
            ->first();
        $application = DB::table('applications_programs')
            ->join('programs', 'programs.id', '=', 'applications_programs.program_id')
            ->join('applications', 'applications.id', '=', 'applications_programs.application_id')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->where('applications_programs.id',$content->applied_program_id)
            ->first();
        $notification = Notification::where('id', $id)->first();
        $notification->status = 1;
        $notification->save();
        return response()->json(['notification' => $content, 'details' => $application]);
    }
    public function deleteNotification($id){
        $notification = Notification::where('id', $id)->first();
        $notification->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted notification']);
    }

}
