<?php

namespace App\Http\Controllers\API;

use App\AccreditorRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function acceptRequest($id){
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
}
