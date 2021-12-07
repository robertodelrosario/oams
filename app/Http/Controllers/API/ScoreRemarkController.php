<?php

namespace App\Http\Controllers\API;

use App\BenchmarkStatement;
use App\Http\Controllers\Controller;
use App\ParameterProgram;
use App\ProgramStatement;
use App\ScoreRemark;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ScoreRemarkController extends Controller
{
    public function sendRemark(request $request, $applied_program_id, $statement_id){
        $remark = new ScoreRemark();
        $remark->application_program_id = $applied_program_id;
        $remark->program_statement_id = $statement_id;
        $remark->sender_id = auth()->user()->id;
        $remark->status = 'unread';
        $remark->message = $request->message;
        $success = $remark->save();
        if($success) return response()->json(['status' => true, 'message' => 'Message Sent!']);
        else return response()->json(['status' => false, 'message' => 'Error sending message.']);
    }

    public function showRemark($applied_program_id, $statement_id){
        $messages = new Collection();
        $remarks = ScoreRemark::where([
            ['application_program_id', $applied_program_id], ['program_statement_id', $statement_id]
        ])->get();
        foreach ($remarks as $remark){
            $user = User::where('id', $remark->sender_id)->first();
            $change_message_status = ScoreRemark::where('id', $remark->id)->first();
            $change_message_status->status = 'read';
            $change_message_status->save();
            $messages->push([
                'id' => $remark->id,
                'user_id' => $user->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'message' => $remark->message,
                'status' => $remark->status,
                'application_program_id' =>$remark->application_program_id,
                'program_statement_id' =>$remark->program_statement_id,
            ]);
        }
        return response()->json($messages);
    }

    public function showUnreadMessage($applied_program_id){
        $remarks = ScoreRemark::where([
            ['application_program_id', $applied_program_id],['status', 'unread']
        ])->get();
        $notification = new Collection();
        foreach ($remarks as $remark){
            if($remark->sender_id != auth()->user()->id){
                $user = User::where('id', $remark->sender_id)->first();
                $program_statement = ProgramStatement::where('id', $remark->program_statement_id)->first();
                $benchmark_statement = BenchmarkStatement::where('id', $program_statement->benchmark_statement_id)->first();
                $program_parameter = ParameterProgram::where('id',$program_statement->program_parameter_id)->first();
                $message = 'New message sent by '.$user->first_name.' '.$user->last_name.' for area ID '.$program_parameter->program_instrument_id.'['.$benchmark_statement->benchmark_statement.'].';
                $notification->push([
                    'id' => $remark->id,
                    'message' => $message
                ]);
            }
        }
        return response()->json($notification);
    }

    public function removeUnreadMessage($id){
        $remark = ScoreRemark::where('id', $id)->first();
        $remark->status = 'read';
        $remark->save();
    }
}
