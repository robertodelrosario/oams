<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssignTaskController extends Controller
{
    public function assignTask(request $request, $id){  // TRANSACTION AREA INSTRUMENT ID
        $assignUser = new AssignedUser();
        $assignUser->transaction_id = $id;
        $assignUser->user_id = $request->user_id;
        $assignUser->role = $request->role;
        $assignUser->save();
        return response()->json(['status' => true, 'message' => 'Successfully added task!', 'users' => $assignUser]);
    }

    public function deleteAssignedUser($userID, $transactionID){
        $user = AssignedUser::where([
            ['transaction_id', $transactionID], ['user_id', $userID]
        ]);
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted']);
    }
}
