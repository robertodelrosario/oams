<?php

namespace App\Http\Controllers\API;

use App\AccreditorRequest;
use App\AssignedUser;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ProgramStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class AccreditorController extends Controller
{
    public function viewRequest($id){
        $req = DB::table('accreditor_requests')
            ->join('applications_programs', 'applications_programs.id', '=', 'accreditor_requests.application_program_id')
            ->join('applications', 'applications.id', '=', 'applications_programs.application_id')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->join('programs', 'programs.id', '=', 'applications_programs.program_id')
            ->join('instruments_programs', 'instruments_programs.id', '=', 'accreditor_requests.instrument_program_id')
            ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
            ->where('accreditor_requests.accreditor_id', $id)
            ->where('accreditor_requests.status', '=', 'pending')
            ->select( 'accreditor_requests.id','sucs.institution_name' ,'programs.program_name','area_instruments.area_name','area_instruments.area_number', 'applications_programs.approved_start_date', 'applications_programs.approved_end_date')
            ->get();
        return response()->json(['requests' => $req]);
    }

    public function acceptRequest(request $request,$id){
        $req = AccreditorRequest::where('id', $id)->first();
        $req->status = 'accepted';
        $req->save();
        return response()->json(['status' => true, 'message' => 'Successfully accepted request']);
    }

    public function rejectRequest($id){
        $req = AccreditorRequest::where('id', $id)->first();
        $req->status = 'rejected';
        $req->save();
        return response()->json(['status' => true, 'message' => 'Successfully rejected request']);
    }





    public function viewRemark($id){
        $area = InstrumentProgram::where('id', $id)->first();
        $remarks = DB::table('programs_statements')
            ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
            ->join('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'programs_statements.benchmark_statement_id')
            ->join('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
            ->join('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
            ->join('instruments_scores', 'instruments_scores.item_id', '=', 'programs_statements.id')
            ->join('users', 'users.id', '=', 'instruments_scores.assigned_user_id')
            ->where('instruments_parameters.area_instrument_id',$area->area_instrument_id)
            ->where('programs_statements.program_instrument_id', $area->id)
            ->where('instruments_scores.remark', '!=', null)
            ->orderBy('benchmark_statements.id')
            ->get();
        $pdf = PDF::loadView('report', ['remarks' => $remarks]);
        return $pdf->download('pdf_file.pdf');
  //      return response()->json(['data' => $data]);
    }
}
