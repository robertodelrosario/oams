<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    public function application(request $request){
        $validator = Validator::make($request->all(), [
            'application_letter' => 'required',

        ]);

    }
}
