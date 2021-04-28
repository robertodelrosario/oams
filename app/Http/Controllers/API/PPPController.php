<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\PPPStatement;
use Illuminate\Http\Request;

class PPPController extends Controller
{
    public function addPPPStatement(request $request,$id){
        foreach ($request->statements as $statement) {
            $ppp_statement = PPPStatement::where([
                ['parameter_program_id', $id], ['statement', $statement], ['type', $request->type]
            ])->first();
            if(is_null($ppp_statement)){
                $ppp_statement = new PPPStatement();
                $ppp_statement->statement = $statement;
                $ppp_statement->parameter_program_id = $id;
                $ppp_statement->type = $request->type;
                $ppp_statement->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added statement/s!']);
    }

    public function editPPPStatement(request $request,$id){
        $ppp_statement = PPPStatement::where('id', $id)->first();
        if(is_null($ppp_statement)) return response()->json(['status' => false, 'message' => 'ID does not exist!']);
        $ppp_statement->statement = $request->statement;
        $ppp_statement->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited statement/s!']);
    }

    public function deletePPPStatement($id){
        $ppp_statement = PPPStatement::where('id', $id)->first();
        if(is_null($ppp_statement)) return response()->json(['status' => false, 'message' => 'ID does not exist!']);
        $ppp_statement->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted statement/s!']);
    }

    public function showPPPStatement($id){
        $ppp_statements = PPPStatement::where('parameter_program_id', $id)->get();
        if(count($ppp_statements) <= 0) return response()->json(['status' => true, 'message' => 'Empty']);
        return response()->json($ppp_statements);
    }


}
