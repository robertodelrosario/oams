<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /*public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }*/

    public function addUser(request $request, $id){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',

        ]);
    }
}
