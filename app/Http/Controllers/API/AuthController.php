<?php

namespace App\Http\Controllers\API;
use App\Role;
use App\SUC;
use App\UserRole;
use App\UserSuc;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Arr;
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

    public function registerSucUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails())
            return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);

        $check = User::where('email', $request->email)->first();
        if(is_null($check)){
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->input('password'));
            $user->save();
            $suc= SUC::where('id',$id)->first();
            $user->sucs()->attach($suc);
            $department = UserSuc::where([
                ['suc_id', $id], ['user_id',$user->id]
            ])->first();
            $department->department = $request->department;
            $department->save();
            $role = Role::where('role', $request->role)->first();
            $role->users()->attach($user->id);
            return response()->json(['user' => $user]);
        }
        return response()->json(['status' => false, 'message' => 'Email already registered']);
    }

    public function registerAaccupAccreditor(request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);
        $check = User::where('email', $request->email)->first();
        if(is_null($check)){
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->input('password'));
            $user->save();
            $role = Role::where('role', $request->role)->first();
            $role->users()->attach($user->id);
            return response()->json(['user' => $user]);
        }
        return response()->json(['status' => false, 'message' => 'Email already registered']);
    }

    public function showSucUser($id){
        $users = DB::table('users_sucs')
            ->join('users', 'users.id', '=', 'users_sucs.user_id')
         //   ->join('users_roles', 'users_roles.user_id', '=', 'users.id')
         //   ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_sucs.suc_id', $id)
            ->get();
        $user_roles =array();
        foreach($users as $user){
            $roles = UserRole::where('user_id', $user->user_id)->get();
            foreach ($roles as $role){
                $rol = Role::where('id', $role->role_id)->first();
                $user_roles = Arr::prepend($user_roles,['user_id' => $user->user_id, 'role' => $rol->role]);
            }
        }
        return response()->json(['users' => $users, 'roles' => $user_roles]);
    }

    public function showAaccupAccreditor(request $request){
        $role = Role::where('role', $request->role)->first();
        $user = DB::table('users_roles')
            ->join('users', 'users.id', '=', 'users_roles.user_id')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_roles.role_id', $role->id)
            ->get();
        return response()->json(['users' => $user]);
    }

    public function showAllUser(){
        return response()->json(User::all());
    }

    public function deleteUser($id){
        $user = User::where('id', $id);
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted to User']);
    }
    public function setRole($userID, $roleID){
        $check = UserRole::where([
            ['user_id', $userID], ['role_id', $roleID]
        ])->first();
        if (is_null($check)){
            $user = User::where('id', $userID)->first();
            if(is_null($user)) return response()->json(['status' => false, 'message' => 'Profile not found']);
            $role = Role::where('id', $roleID)->first();
            $role->users()->attach($user->id);
            return response()->json(['status' => true, 'message' => 'Role successfully added to User']);
        }
        return response()->json(['status' => false, 'message' => 'Role already added to User']);

    }
}
