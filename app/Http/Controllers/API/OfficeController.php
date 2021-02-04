<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfficeController extends Controller
{
    public function createOffice(request $request, $id){
        $validator = Validator::make($request->all(), [
            'office_name' => 'required',
            'email' => 'required',
            'contact' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $office = Office::where([
            ['campus_id', $id], [strtolower('office_name'), strtolower($request->office_name)]
        ])->first();
        if(is_null($office)){
            $office = new Office();
            $office->office_name = $request->office_name;
            $office->email = $request->email;
            $office->contact = $request->contact;
            $office->campus_id = $id;
            $office->save();
            return response()->json(['status' => true, 'message' => 'Successfully created office', 'office' => $office]);
        }
        return response()->json(['status' => false, 'message' => 'Office already exist!']);
    }

    public function showOffice($id){
        $offices = Office::where('campus_id', $id)->get();
        return response()->json(['office' => $offices]);
    }

    public function deleteOffice($id){
        $office = Office::where('id', $id)->first();

        $office->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted!']);
    }

    public function editOffice(request $request, $id){
        $office = Office::where('id', $id)->first();
        $office->office_name = $request->office_name;
        $office->email = $request->email;
        $office->contact = $request->contact;
        $office->save();
        return response()->json(['status' => true, 'message' => 'Successfully Edited!', 'office' => $office]);
    }
}
