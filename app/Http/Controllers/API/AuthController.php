<?php

namespace App\Http\Controllers\API;
use App\AccreditorDegree;
use App\AccreditorProfile;
use App\AccreditorSpecialization;
use App\Campus;
use App\CampusUser;
use App\Office;
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
        if(auth()->user()->status == 'inactive')
            return response()->json(['error' => 'Unauthorized [1]'], 401);
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $campus = DB::table('campuses_users')
            ->join('campuses', 'campuses.id', '=', 'campuses_users.campus_id')
            ->join('sucs', 'sucs.id', '=', 'campuses.suc_id')
            ->where('campuses_users.user_id',auth()->user()->id)
            ->first();
        $office = null;
        if(!(is_null($campus)))  $office = Office::where('id', $campus->office_id)->first();
        $roles = UserRole::where('user_id', auth()->user()->id)->get();
        return response()->json(['user' => auth()->user(), 'role' => $roles, 'campus'=>$campus, 'office' => $office]);
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
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'contact_no' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails())
            return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);

        $check = User::where('email', $request->email)->first();
        if(is_null($check)){
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->contact_no = $request->contact_no;
            $user->password = bcrypt($request->input('password'));
            $user->status = 'active';
            $user->save();
            $campus= Campus::where('id',$id)->first();
            $user->campuses()->attach($campus);
            $role = Role::where('role', $request->role)->first();
            $role->users()->attach($user->id);
            $roles = DB::table('users_roles')
                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
                ->where('user_id', $user->id)
                ->get();
            return response()->json(['status' => true, 'message' => 'Successfully added to User', 'user' => $user, 'roles' => $roles]);
        }
        return response()->json(['status' => false, 'message' => 'Email already registered']);
    }

    public function registerLocalAccreditor(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'password' => 'required|min:6',
            'contact_no' => 'required',
        ]);

        if ($validator->fails())
            return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);

        $check = User::where('email', $request->email)->first();
        if(is_null($check)){
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->contact_no = $request->contact_no;
            $user->password = bcrypt($request->input('password'));
            $user->status = 'active';
            $user->save();
            $campus= Campus::where('id',$id)->first();
            $user->campuses()->attach($campus);
            $role = Role::where('role', $request->role)->first();
            $role->users()->attach($user->id);

            $region = new AccreditorProfile();
            $region->user_id = $user->id;
            $region->region = $request->region;
            $region->campus_id = $id;
            $region->save();

            $specialization = new AccreditorSpecialization();
            $specialization->accreditor_id = $user->id;
            $specialization->specialization = $request->specialization;
            $specialization->save();

            $roles = DB::table('users_roles')
                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
                ->where('user_id', $user->id)
                ->get();
            return response()->json(['status' => true, 'message' => 'Successfully added to User', 'user' => $user, 'roles' => $roles]);
        }
        else{

            $region = AccreditorProfile::where('user_id',$check->id )->first();
            $region->region = $request->region;
            $region->campus_id = $id;
            $region->save();

            $specialization = AccreditorSpecialization::where('accreditor_id', $check->id)->first();
            $specialization->specialization = $request->specialization;
            $specialization->save();

            return response()->json(['status' => true, 'message' => 'Successfully added to User', 'user' => $check]);
        }
    }

    public function addToOffice($id, $office_id){
        $user = CampusUser::where('id', $id)->first();
        $user->office_id = $office_id;
        $user->save();

        $office = DB::table('offices')
            ->join('campuses_users', 'campuses_users.office_id', '=', 'offices.id')
            ->where('campuses_users.id', $id)
            ->first();
        return response()->json(['status' => true, 'message' => 'Successfully added to office', 'office' => $office]);
    }

    public function removeFromOffice($id){
        $user = CampusUser::where('id', $id)->first();
        $user->office_id = null;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully remove from office']);
    }
    public function registerAaccupAccreditor(request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'password' => 'required|min:6',
            'contact_no' => 'required',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);
        $check = User::where('email', $request->email)->first();
        if(is_null($check)){
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->input('password'));
            $user->contact_no = $request->contact_no;
            $user->status = 'active';
            $user->save();

            $role = Role::where('role', $request->role)->first();
            if($role->id == 8){
                $region = new AccreditorProfile();
                $region->user_id = $user->id;
                $region->region = $request->region;
                if ($request->campus_id != null) $region->campus_id = $request->campus_id;
                $region->save();

                $specialization = new AccreditorSpecialization();
                $specialization->accreditor_id = $user->id;
                $specialization->specialization = $request->specialization;
                $specialization->save();
            }
            $role->users()->attach($user->id);
            $roles = DB::table('users_roles')
                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
                ->where('user_id', $user->id)
                ->get();
            return response()->json(['status' => true, 'message' => 'Successfully added to User', 'user' => $user, 'roles' => $roles]);
        }
        return response()->json(['status' => false, 'message' => 'Email already registered']);
    }

    public function showCampusUser($id){
        $users = DB::table('campuses_users')
            ->join('users', 'users.id', '=', 'campuses_users.user_id')
            ->where('campuses_users.campus_id', $id)
            ->select('campuses_users.id','campuses_users.user_id', 'users.first_name', 'users.last_name', 'users.email', 'users.password', 'users.status', 'users.contact_no')
            ->get();
        $office = DB::table('campuses_users')
            ->join('offices', 'offices.id', '=', 'campuses_users.office_id')
            ->where('campuses_users.campus_id', $id)
            ->get();
        $user_roles =array();
        foreach($users as $user){
            $roles = UserRole::where('user_id', $user->user_id)->get();
            foreach ($roles as $role){
                $rol = Role::where('id', $role->role_id)->first();
                $user_roles = Arr::prepend($user_roles,['user_id' => $user->user_id, 'role_id' => $role->role_id, 'role' => $rol->role]);
            }
        }
        return response()->json(['users' => $users,'office' =>  $office,'roles' => $user_roles]);
    }

    public function showLocalAccreditor($id){
        $campuses = Campus::where('suc_id', $id)->get();
        $accreditor_array = array();
        $specializations = array();
        $degrees_arr = array();
        foreach ($campuses as $campus){
            $accreditors = DB::table('users_roles')
                ->join('users', 'users.id', '=', 'users_roles.user_id')
                ->join('accreditors_profiles', 'accreditors_profiles.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
                ->join('campuses', 'campuses.id', '=','accreditors_profiles.campus_id' )
                ->where('users_roles.role_id', 8)
                ->where('accreditors_profiles.campus_id', $campus->id)
                ->get();
            foreach ($accreditors as $accreditor){
                $accreditor_array = Arr::prepend($accreditor_array,$accreditor);

                $specials = AccreditorSpecialization::where('accreditor_id', $accreditor->user_id)->get();
                foreach ($specials as $special){
                    $specializations = Arr::prepend($specializations, $special);
                }

                $degrees = AccreditorDegree::where('user_id', $accreditor->user_id)->get();
                foreach ($degrees as $degree){
                    $degrees_arr = Arr::prepend($degrees_arr, $degree);
                }
            }
        }
        return response()->json(['users' => $accreditors, 'specializations' => $specializations, 'degrees' => $degrees_arr]);
    }

    public function showAaccup(){
        $aaccupStaff = DB::table('users_roles')
            ->join('users', 'users.id', '=', 'users_roles.user_id')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_roles.role_id', 9)
            ->get();
        $aaccupBoardmember = DB::table('users_roles')
            ->join('users', 'users.id', '=', 'users_roles.user_id')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_roles.role_id', 10)
            ->get();
        $aaccup =  $aaccupStaff->merge($aaccupBoardmember);
        return response()->json(['users' => $aaccup]);
    }

    public function showAccreditor(){
        $accreditors = DB::table('users_roles')
            ->join('users', 'users.id', '=', 'users_roles.user_id')
            ->join('accreditors_profiles', 'accreditors_profiles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_roles.role_id', 8)
            ->get();

        $specializations = array();
        foreach($accreditors as $accreditor){
            $specials = AccreditorSpecialization::where('accreditor_id', $accreditor->user_id)->get();
            foreach ($specials as $special){
                $specializations = Arr::prepend($specializations, $special);
            }
        }

        $degrees_arr = array();
        foreach($accreditors as $accreditor){
            $degrees = AccreditorDegree::where('user_id', $accreditor->user_id)->get();
            foreach ($degrees as $degree){
                $degrees_arr = Arr::prepend($degrees_arr, $degree);
            }
        }
        return response()->json(['users' => $accreditors, 'specializations' => $specializations, 'degrees' => $degrees_arr]);
    }

    public function showAllUser(){
        return response()->json(User::all());
    }

    public function deleteUser($id){
        $user = User::where('id', $id)->first();
        $user->delete();
//        $user->status = 'inactive';
//        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully disabled user account']);
    }

    public function activateUser($id){
        $user = User::where('id', $id)->first();
        $user->status = 'active';
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully activated user account']);
    }

    public function setRole(request $request,$userID){
        $role = Role::where('role', $request->role)->first();
        $check = UserRole::where([
            ['user_id', $userID], ['role_id', $role->id]
        ])->first();
        if (is_null($check)){
            $user = User::where('id', $userID)->first();
            if(is_null($user)) return response()->json(['status' => false, 'message' => 'Profile not found']);
            $role = Role::where('id', $role->id)->first();
            $role->users()->attach($user->id);
            $roles = DB::table('users_roles')
                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
                ->where('user_id', $userID)
                ->get();
            return response()->json(['status' => true, 'message' => 'Role successfully added to User', 'roles' => $roles]);
        }
        return response()->json(['status' => false, 'message' => 'Role already added to User']);
    }

    public function deleteSetRole($userID, $roleID){
        $role = UserRole::where([
            ['user_id', $userID], ['role_id', $roleID]
        ]);
        $role->delete();
        return response()->json(['status' => true, 'message' => 'Successfully remove role']);
    }
}
