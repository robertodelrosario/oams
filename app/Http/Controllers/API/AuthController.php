<?php

namespace App\Http\Controllers\API;
use App\Role;
use App\SUC;
use App\UserSUCModel;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */

    public function login(Request $request)
    {
        $credentials = $request->only('email','password');
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails())
            return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->input('password'));
        $user->save();
        $suc= SUC::where('id',$id)->first();
        $user->sucs()->attach($suc);
        return response()->json(['user' => $user]);
    }

    public function showUser($id){
        $user = DB::table('users_sucs')
            ->join('users', 'users.id', '=', 'users_sucs.user_id')
            ->where('users_sucs.suc_id', $id)
            ->get();
        return response()->json(['users' => $user]);
    }

    public function setRole(Request $request){
        $user = User::where('id', $request->id)->first();
        if(is_null($user)) return response()->json(['status' => false, 'message' => 'Profile not found']);
        $role = new Role;
        $role->users()->attach($user->id);
        return response()->json(['status' => true, 'message' => 'Role successfully added to User']);
    }
}
