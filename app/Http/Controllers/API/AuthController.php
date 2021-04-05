<?php

namespace App\Http\Controllers\API;
use App\AccreditorDegree;
use App\AccreditorProfile;
use App\AccreditorSpecialization;
use App\Campus;
use App\CampusUser;
use App\Office;
use App\OtherOfficeUser;
use App\Program;
use App\Role;
use App\UserRole;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

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
        $roles = UserRole::where('user_id', auth()->user()->id)->get();
        if(!(is_null($campus))) {
            $collection_1 = new Collection();
            $user_roles = DB::table('roles')
                ->join('users_roles', 'users_roles.role_id', '=', 'roles.id')
                ->where('users_roles.user_id', auth()->user()->id)
                ->get();
            foreach ($user_roles as $user_role){
                $office = DB::table('offices')
                    ->join('offices_users', 'offices_users.office_id', '=', 'offices.id')
                    ->where('offices_users.user_role_id', $user_role->id)
                    ->first();
                if(!(is_null($office))) {
                    $collection_1->push([
                        'user_role_id' => $user_role->id,
                        'role_id' => $user_role->role_id,
                        'role' => $user_role->role,
                        'office_user_id' => $office->id,
                        'office_id' => $office->office_id,
                        'office_name' => $office->office_name
                    ]);
                }
            }
        }
        return response()->json(['user' => auth()->user(), 'role' => $roles, 'campus'=>$campus, 'office' => $collection_1]);
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

    public function changePassword(request $request,$id){
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|min:6',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|min:6'
        ]);
        if ($validator->fails())
            return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);

        $current_user = User::where('id', $id)->first();
        if(Hash::check($request->current_password, $current_user->password)){
            if($request->confirm_password == $request->new_password){
                $current_user->update(['password'=> bcrypt($request->new_password)]);
                return response()->json(['status' => true, 'message' => 'Successfully changed the password.']);
            }
            else return response()->json(['status' => false, 'message' => 'New and confirm password does not match!']);
        }
        else{
            return response()->json(['status' => false, 'message' => 'Old password does not match!']);
        }
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
            $check = User::where([
                ['first_name', $request->first_name],['last_name', $request->last_name], ['name_extension', $request->name_extension]
            ])->first();
            if(is_null($check)){
                $user = new User;
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->contact_no = $request->contact_no;
                $user->password = bcrypt($request->input('password'));
                $user->status = 'active';
                $user->middle_initial = $request->middle_initial;
                $user->name_extension = $request->name_extension;
                $user->save();
                $campus= Campus::where('id',$id)->first();
                $user->campuses()->attach($campus);
                $role = Role::where('role', $request->role)->first();
                if($request->role == 'support head'){
                    $users = CampusUser::where('office_id',  $request->office_id)->get();
                    foreach ($users as $u)
                    {
                        $user_role = UserRole::where([
                            ['user_id', $u->user_id], ['role_id', 3]
                        ])->first();
                        if(!(is_null($user_role))){
                            $role = Role::where('role', 'support staff')->first();
                            break;
                        }
                    }
                }
                $role->users()->attach($user->id);
                $user_office = CampusUser::where('user_id', $user->id)->first();
                $user_office->office_id = $request->office_id;
                $user_office->save();
                $roles = DB::table('users_roles')
                    ->join('roles', 'roles.id', '=', 'users_roles.role_id')
                    ->where('user_id', $user->id)
                    ->get();
                return response()->json(['status' => true, 'message' => 'Successfully added to User', 'user' => $user, 'roles' => $roles]);
            }

            $user_role = UserRole::where([
                ['user_id', $check->id],['role_id', 8]
            ])->first();
            if(!(is_null($user_role))){
                $campus_user = CampusUser::where([
                    ['campus_id', $id], ['user_id',$user_role->user_id]
                ])->first();
                if(is_null($campus_user)){
                    return response()->json(['status' => false, 'message' => 'Accreditor was not registered to this campus.']);
                }
                return response()->json(['status' => true, 'message' => 'Accreditor', 'user' => $check]);
            }
            return response()->json(['status' => false, 'message' => 'User already registered']);
        }

        $user_role = UserRole::where([
            ['user_id', $check->id],['role_id', 8]
        ])->first();
        if(!(is_null($user_role))){
            $campus_user = CampusUser::where([
                ['campus_id', $id], ['user_id',$user_role->user_id]
            ])->first();
            if(is_null($campus_user)) return response()->json(['status' => false, 'message' => 'Accreditor was not registered to this campus.']);
            return response()->json(['status' => true, 'message' => 'Accreditor', 'user' => $check]);
        }
        return response()->json(['status' => false, 'message' => 'Email already registered']);
    }

//    public function registerLocalAccreditor(Request $request, $id){
//        $validator = Validator::make($request->all(), [
//            'first_name' => 'required',
//            'last_name' => 'required',
//            'email' => 'required',
//            'password' => 'required|min:6',
//            'contact_no' => 'required',
//        ]);
//
//        if ($validator->fails())
//            return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);
//
//        $check = User::where('email', $request->email)->first();
//        if(is_null($check)){
//            $user = new User;
//            $user->first_name = $request->first_name;
//            $user->last_name = $request->last_name;
//            $user->email = $request->email;
//            $user->contact_no = $request->contact_no;
//            $user->password = bcrypt($request->input('password'));
//            $user->status = 'active';
//            $user->save();
//            $campus= Campus::where('id',$id)->first();
//            $user->campuses()->attach($campus);
//            $role = Role::where('role', $request->role)->first();
//            $role->users()->attach($user->id);
//
//            $region = new AccreditorProfile();
//            $region->user_id = $user->id;
//            $region->region = $request->region;
//            $region->campus_id = $id;
//            $region->save();
//
//            $specialization = new AccreditorSpecialization();
//            $specialization->accreditor_id = $user->id;
//            $specialization->specialization = $request->specialization;
//            $specialization->save();
//
//            $roles = DB::table('users_roles')
//                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
//                ->where('user_id', $user->id)
//                ->get();
//            return response()->json(['status' => true, 'message' => 'Successfully added to User', 'user' => $user, 'roles' => $roles]);
//        }
//        else{
//
//            $region = AccreditorProfile::where('user_id',$check->id )->first();
//            $region->region = $request->region;
//            $region->campus_id = $id;
//            $region->save();
//
//            $specialization = AccreditorSpecialization::where('accreditor_id', $check->id)->first();
//            $specialization->specialization = $request->specialization;
//            $specialization->save();
//
//            return response()->json(['status' => true, 'message' => 'Successfully added to User', 'user' => $check]);
//        }
//    }


//    public function addToOffice($id, $office_id){
//        $check_office = Office::where('id', $office_id)->first();
////        if($check_office->parent_office_id == null){
////            $user = CampusUser::where('id', $id)->first();
////            $user->office_id = $office_id;
////            $user->save();
////        }
////        else{
////            $user = new OtherOfficeUser();
////            $user->office_id = $office_id;
////
////        }
//
//            $user = CampusUser::where('id', $id)->first();
//            $user->office_id = $office_id;
//            $user->save();
//
//
//        $office = DB::table('offices')
//            ->join('campuses_users', 'campuses_users.office_id', '=', 'offices.id')
//            ->where('campuses_users.id', $id)
//            ->first();
//
//        $user_role = UserRole::where([
//            ['user_id', $user->user_id], ['role_id', 4]
//        ])->first();
//        if(is_null($user_role)){
//            $u = new UserRole();
//            $u->user_id = $user->user_id;
//            $u->role_id = 4;
//            $u->save();
//        }
//        return response()->json(['status' => true, 'message' => 'Successfully added to office', 'office' => $office]);
//    }
//
//    public function removeFromOffice($id){
//        $user = CampusUser::where('id', $id)->first();
//        $user->office_id = null;
//        $user->save();
//        $user_role = UserRole::where([
//            ['user_id', $user->user_id], ['role_id', 4]
//        ]);
//        $user_role->delete();
//        return response()->json(['status' => true, 'message' => 'Successfully remove from office']);
//    }
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
            $check = User::where([
                ['first_name', $request->first_name],['last_name', $request->last_name], ['name_extension', $request->name_extension]
            ])->first();
            if(is_null($check)){
                $user = new User;
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->password = bcrypt($request->input('password'));
                $user->contact_no = $request->contact_no;
                $user->status = 'active';
                $user->middle_initial = $request->middle_initial;
                $user->name_extension = $request->name_extension;
                $user->save();

                $role = Role::where('role', $request->role)->first();
                if($role->id == 8){
                    $region = new AccreditorProfile();
                    $region->user_id = $user->id;
                    $region->region = $request->region;
                    $region->accreditor_status = $request->accreditor_status;
                    if ($request->campus_id != null){
                        $region->campus_id = $request->campus_id;
                        $campus_user = new CampusUser();
                        $campus_user->campus_id = $request->campus_id;
                        $campus_user->user_id = $user->id;
                        $campus_user->save();
                    }
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

            $roles = UserRole::where('user_id', $check->id)->get();
            foreach ($roles as $role){
                if($role->role_id == 8){
                    $accreditor = AccreditorProfile::where('user_id', $role->user_id)->first();
                    if($accreditor->accreditor_status == 'Unregistered'){
                        return response()->json(['status' => false, 'message' => 'Unregistered', 'user' => $check]);
                    }
                    else
                        return response()->json(['status' => false, 'message' => 'Registered']);
                }
            }

            return response()->json(['status' => false, 'message' => 'User already registered']);
        }
        $roles = UserRole::where('user_id', $check->id)->get();
        foreach ($roles as $role){
            if($role->role_id == 8){
                $accreditor = AccreditorProfile::where('user_id', $role->user_id)->first();
                if($accreditor->accreditor_status == 'Unregistered'){
                    return response()->json(['status' => false, 'message' => 'Unregistered', 'user' => $check]);
                }
                else
                    return response()->json(['status' => false, 'message' => 'Registered', 'user' => $check]);
            }

        }
        return response()->json(['status' => false, 'message' => 'Email already registered']);
    }

    public function showCampusUser($id){
        $collection = new Collection();
        $campus_users = CampusUser::where('id', $id)->get();
        foreach ($campus_users as $campus_user){
            $collection_1 = new Collection();
            $user = User::where('id', $campus_user->user_id)->first();
            $roles = DB::table('roles')
                ->join('users_roles', 'users_roles.role_id', '=', 'roles.id')
                ->where('users_roles.user_id', $user->id)
                ->get();
            foreach ($roles as $role){
                $office = DB::table('offices')
                    ->join('offices_users', 'offices_users.office_id', '=', 'offices.id')
                    ->where('offices_users.user_role_id', $role->id)
                    ->first();
                if(!(is_null($office))) {
                    $collection_1->push([
                        'user_role_id' => $role->id,
                        'role_id' => $role->role_id,
                        'role' => $role->role,
                        'office_user_id' => $office->id,
                        'office_id' => $office->office_id,
                        'office_name' => $office->office_name
                    ]);
                }
            }
            $collection->push([
                'id' =>  $campus_user->id,
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'status' => $user->status,
                'middle_initial' => $user->middle_initial,
                'name_extension' => $user->name_extension,
                'contact_no' => $user->contact_no,
                'office_roles' => $collection_1
            ]);
        }
        return response()->json(['users' => $collection]);


//        $users = DB::table('campuses_users')
//            ->join('users', 'users.id', '=', 'campuses_users.user_id')
//            ->where('campuses_users.campus_id', $id)
//            ->select('campuses_users.id','campuses_users.user_id', 'users.first_name', 'users.last_name', 'users.email', 'users.password', 'users.status', 'users.contact_no')
//            ->get();
//        $office = DB::table('campuses_users')
//            ->join('offices', 'offices.id', '=', 'campuses_users.office_id')
//            ->where('campuses_users.campus_id', $id)
//            ->get();
//        $user_roles =array();
//        foreach($users as $user){
//            $roles = UserRole::where('user_id', $user->user_id)->get();
//            foreach ($roles as $role){
//                $rol = Role::where('id', $role->role_id)->first();
//                $user_roles = Arr::prepend($user_roles,['user_id' => $user->user_id, 'role_id' => $role->role_id, 'role' => $rol->role]);
//            }
//        }
//        return response()->json(['users' => $users,'office' =>  $office,'roles' => $user_roles]);
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

    public function changeAccreditorStatus(request $request, $id){
        $accreditor = AccreditorProfile::where('user_id', $id)->first();
        if(!(is_null($accreditor))){
            $accreditor->accreditor_status = $request->status;
            $accreditor->save();
            return response()->json(['status' => true, 'message' => 'Successfully changed status.']);
        }
        return response()->json(['status' => false, 'message' => 'Id as accreditor does not exist!']);
    }

    public function showAllUser(){
        return response()->json(User::all());
    }

    public function deleteUser($id){
        $user = User::where('id', $id)->first();
        $user->status = 'inactive';
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully disabled user account']);
    }

    public function activateUser($id){
        $user = User::where('id', $id)->first();
        $user->status = 'active';
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully activated user account']);
    }

//    public function setRole(request $request,$userID){
//        $role = Role::where('role', $request->role)->first();
//        if($request->role == 'support head'){
//            $users = CampusUser::where('office_id',  $request->office_id)->get();
//            foreach ($users as $user)
//            {
//                $user_role = UserRole::where([
//                    ['user_id', $user->user_id], ['role_id', 3]
//                ])->first();
//                if(!(is_null($user_role))){
//                    $role = Role::where('role', 'support staff')->first();
//                    break;
//                }
//            }
//        }
//        $user_office = CampusUser::where('user_id', $userID)->first();
//        $user_office->office_id = $request->office_id;
//        $user_office->save();
//
//        $check = UserRole::where([
//            ['user_id', $userID], ['role_id', $role->id]
//        ])->first();
//        if (is_null($check)){
//            $user = User::where('id', $userID)->first();
//            if(is_null($user)) return response()->json(['status' => false, 'message' => 'Profile not found']);
//            $role = Role::where('id', $role->id)->first();
//            $role->users()->attach($user->id);
//            $roles = DB::table('users_roles')
//                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
//                ->where('user_id', $userID)
//                ->get();
//            return response()->json(['status' => true, 'message' => 'Role successfully added to User', 'roles' => $roles]);
//        }
//        return response()->json(['status' => false, 'message' => 'Role already added to User']);
//    }

//    public function deleteSetRole($userID, $roleID){
//        if($roleID == 5){
//            $campus_user = CampusUser::where('user_id', $userID)->first();
//            $users = CampusUser::where('campus_id', $campus_user->campus_id)->get();
//            $count = 0;
//            foreach ($users as $user){
//                $role = UserRole::where([
//                    ['user_id', $user->user_id], ['role_id', 5]
//                ])->first();
//                if(!(is_null($role))) $count++;
//            }
//            if($count == 1) return response()->json(['status' => false, 'message' => 'Cannot delete role. Need to assign another QA Director first.']);
//        }
//        $role = UserRole::where([
//            ['user_id', $userID], ['role_id', $roleID]
//        ]);
//        $role->delete();
////        if($roleID == 3 || $roleID == 4){
////            $user = CampusUser::where('user_id', $userID)->first();
////            $user->office_id = null;
////            $user->save();
////        }
//        return response()->json(['status' => true, 'message' => 'Successfully remove role']);
//    }

    public function removeUser($id){
        $user = User::where('id', $id);
        $user->delete();
    }


    public function addToCampus($campusID, $userID){
        $campus_user = CampusUser::where([
            ['user_id', $userID], ['campus_ID', $campusID]
        ])->first();

        if(!(is_null($campus_user))) return response()->json(['status' => false, 'message' => 'Already added to this campus.']);
        else{
            $user = new CampusUser();
            $user->user_id = $userID;
            $user->campus_id = $campusID;
            $user->save();

            $accreditor = AccreditorProfile::where('user_id', $userID)->first();
            $accreditor->campus_id = $campusID;
            $accreditor->save();
            return response()->json(['status' => true, 'message' => 'Successful.']);
        }
//        $accreditors = AccreditorProfile::where('campus_id', $id)->get();
//        foreach ($accreditors as $accreditor){
//            $campus_user = new CampusUser();
//            $campus_user->campus_id = $id;
//            $campus_user->user_id = $accreditor->user_id;
//            $campus_user->save();
//        }
    }

    public function showAllCampusUser(){
        $users = CampusUser::all();
        return response()->json($users);
    }

    public function editUser(request $request, $id){
        $user = User::where('id', $id)->first();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->save();
        return response()->json(["status" => true, "message" => "Successfully edited user info."]);
    }
}
