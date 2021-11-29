<?php

namespace App\Http\Controllers\API;
use App\AccreditorDegree;
use App\AccreditorProfile;
use App\AccreditorSpecialization;
use App\Campus;
use App\CampusOffice;
use App\CampusUser;
use App\Office;
use App\OfficeUser;
use App\OtherOfficeUser;
use App\Program;
use App\Role;
use App\SUC;
use App\UserLog;
use App\UserRole;
use App\UserSuc;
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
        else{
            $user_log = new UserLog();
            $user_log->user_id = auth()->user()->id;
            $user_log->activity = 'login';
            $user_log->address = $request->ip();
            $user_log->device = $request->header('User-Agent');
            $user_log->save();
        }
//        if(auth()->user()->status == 'inactive')
//            return response()->json(['error' => 'Inactive User!'], 401);
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function showPassword(){

    }

    public function me()
    {
        $campuses = new Collection();
        $user_campuses = CampusUser::where('user_id', auth()->user()->id)->get();
        foreach ($user_campuses as $user_campus){
            $campus = Campus::where('id', $user_campus->campus_id)->first();
            $suc = SUC::where('id', $campus->suc_id)->first();
            $campuses->push([
                'id' => $suc->id,
                'campus_id' => $campus->id,
                'user_id' => auth()->user()->id,
                'suc_id' => $suc->id,
                'campus_name' => $campus->campus_name,
                'address' => $campus->address,
                'region' => $campus->region,
                'province' => $campus->province,
                'municipality' => $campus->municipality,
                'email' => $campus->email,
                'contact_no' => $campus->contact_no,
                'institution_name' => $suc->institution_name,
                'suc_level' => $suc->suc_level,
            ]);
        }
//        $campuses = DB::table('campuses_users')
//            ->join('campuses', 'campuses.id', '=', 'campuses_users.campus_id')
//            ->join('sucs', 'sucs.id', '=', 'campuses.suc_id')
//            ->where('campuses_users.user_id',auth()->user()->id)
//            ->get();
        $office = null;
        $roles = UserRole::where('user_id', auth()->user()->id)->get();
        $collection_1 = new Collection();
        $user_roles = DB::table('roles')
            ->join('users_roles', 'users_roles.role_id', '=', 'roles.id')
            ->where('users_roles.user_id', auth()->user()->id)
            ->get();
        foreach ($user_roles as $user_role){
            $office_users = OfficeUser::where('user_role_id', $user_role->id)->get();
            foreach ($office_users as $office_user){
                foreach($user_campuses as $user_campus){
                    $campus_offices = CampusOffice::where('office_id', $office_user->office_id)->get();
                    foreach ($campus_offices as $campus_office) {
                        if($user_campus->campus_id == $campus_office->campus_id) {
                            $office = Office::where('id', $office_user->office_id)->first();
                            if (!($collection_1->contains('id', $office_user->id))) {
                                $collection_1->push([
                                    'id' => $office_user->id,
                                    'user_role_id' => $user_role->id,
                                    'role_id' => $user_role->role_id,
                                    'role' => $user_role->role,
                                    'office_user_id' => $office->id,
                                    'office_id' => $office->id,
                                    'office_name' => $office->office_name,
                                    'campus_id' => $campus_office->campus_id
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return response()->json(['user' => auth()->user(), 'role' => $roles, 'campus'=>$campuses, 'office' => $collection_1]);
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
                $message = '';
                if($id == auth()->user()->id){
                    if($current_user->gender == 'Female') $message = 'User '.$current_user->id.' changed her password';
                    elseif ($current_user->gender == 'Male')$message = 'User '.$current_user->id.' changed his password';
                    elseif(is_null($current_user->gender)) $message = 'User '.$current_user->id.' changed his/her password';
                }
                else{
                    $message = 'User '.auth()->user()->id.' changed the password of user '.$current_user->id;
                }
                $user_log = new UserLog();
                $user_log->user_id = auth()->user()->id;
                $user_log->activity = $message;
                $user_log->address = $request->ip();
                $user_log->device = $request->header('User-Agent');
                $user_log->save();
                return response()->json(['status' => true, 'message' => 'Successfully changed the password.']);
            }
            else return response()->json(['status' => false, 'message' => 'New and confirm password does not match!']);
        }
        else{
            return response()->json(['status' => false, 'message' => 'Old password does not match!']);
        }
    }

    public function registerSucUser(Request $request, $id){
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
        $role = Role::where('role', $request->role)->first();
        if(is_null($check)){
            $check = User::where([
                ['first_name', $request->first_name],['last_name', $request->last_name], ['name_extension', $request->name_extension]
            ])->first();
            if(is_null($check)){
                if($request->role == 'support head'){
                    $users = OfficeUser::where('office_id',  $request->office_id)->get();
                    foreach ($users as $u)
                    {
                        $user_role = UserRole::where('id', $u->user_role_id)->first();
                        if($user_role->role_id == 3){
                            $role = Role::where('role', 'support staff')->first();
                            break;
                        }
                    }
                }
                elseif($request->role == 'QA director'){
                    $users = OfficeUser::where('office_id',  $request->office_id)->get();
                    foreach ($users as $u)
                    {
                        $user_role = UserRole::where('id', $u->user_role_id)->first();
                        if($user_role->role_id == 5){
                            $role = Role::where('role', 'QA staff')->first();
                            break;
                        }
                    }
                }
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
                $role->users()->attach($user->id);
                if($role->id == 8) {
                    $region = new AccreditorProfile();
                    $region->user_id = $user->id;
                    $region->region = $request->region;
                    $region->accreditor_status = $request->accreditor_status;
                    $region->save();

                    $specialization = new AccreditorSpecialization();
                    $specialization->accreditor_id = $user->id;
                    $specialization->specialization = $request->specialization;
                    $specialization->save();
                    return response()->json(['status' => true, 'message' => 'Accreditor successfully registered']);
                }
                $userRole = UserRole::where([
                    ['user_id', $user->id], ['role_id', $role->id]
                ])->first();
                $office_user = new OfficeUser();
                $office_user->user_role_id = $userRole->id;
                $office_user->office_id = $request->office_id;
                $office_user->save();
                return response()->json(['status' => true, 'message' => 'User successfully registered']);
            }
        }
        if($role->id == 8){
            $campus_user = CampusUser::where([
                ['campus_id', $id], ['user_id', $check->id]
            ])->first();
            if(is_null($campus_user)) return response()->json(['status' => false, 'message' => 'Accreditor was not registered in this campus']);
            $user_roles = UserRole::where('user_id', $check->id)->get();
            foreach ($user_roles as $user_role) {
                if ($user_role->id == 8) return response()->json(['status' => false, 'message' => 'Accreditor was already registered to this campus']);
            }
            $user_role = new UserRole();
            $user_role->role_id = 8;
            $user_role->user_id = $check->id;
            $user_role->save();

            $region = new AccreditorProfile();
            $region->user_id = $check->id;
            $region->region = $request->region;
            $region->accreditor_status = $request->accreditor_status;
            $region->save();

            $specialization = new AccreditorSpecialization();
            $specialization->accreditor_id = $check->id;
            $specialization->specialization = $request->specialization;
            $specialization->save();
            return response()->json(['status' => true, 'message' => 'Accreditor successfully registered']);
        }
        $campus_users = CampusUser::where('user_id', $check->id)->get();
        $collection = new Collection();
        if(count($campus_users) > 0) {
            foreach ($campus_users as $campus_user) {
                $campus_1 = Campus::where('id', $campus_user->campus_id)->first();
                $campus_2 = Campus::where('id', $id)->first();
                if ($campus_1->suc_id == $campus_2->suc_id) {
                    if ($campus_1->id == $campus_2->id) return response()->json(['status' => false, 'message' => 'already registered to this campus']);
                    else $collection->push([
                        'campus_name' => $campus_1->campus_name
                    ]);
                } else return response()->json(['status' => false, 'message' => 'User was registered to other SUC']);
            }
            return response()->json(['status' => true, 'message' => 'campus', 'campuses' => $collection, 'user' => $check]);
        }
        return response()->json(['status' => false, 'message' => 'User was already registered!']);
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
        $collection_qa = new Collection();
        $collection_head = new Collection();
        $collection_chairman = new Collection();
        $campus_users = CampusUser::where('campus_id', $id)->get();
        $user_roles = UserRole::where('user_id', auth()->user()->id)->get();
        $is_active_qa = false;
        $is_active_head = false;
        $is_active_chairman = false;
        $chairman_office_id = 0;
        $head_office_id = 0;
        foreach ($user_roles as $user_role){
            if($user_role->role_id == 5 || $user_role->role_id == 6) $is_active_qa = true;
            elseif($user_role->role_id == 2) {
                $is_active_chairman = true;
                $user_offices = OfficeUser::where('user_role_id', $user_role->id)->get();
                foreach ($user_offices as $user_office){
                    $chairman_office = Office::where('id', $user_office->office_id)->first();
                    $chairman_office_id = $chairman_office->id;
                }
            }
            elseif($user_role->role_id == 11){
                $is_active_head = true;
                $user_offices = OfficeUser::where('user_role_id', $user_role->id)->get();
                foreach ($user_offices as $user_office){
                    $head_office = Office::where('id', $user_office->office_id)->first();
                    $head_office_id = $head_office->id;
                }
            }
        }
        foreach ($campus_users as $campus_user){
            $collection_1 = new Collection();
            $user = User::where('id', $campus_user->user_id)->first();
            $roles = DB::table('roles')
                ->join('users_roles', 'users_roles.role_id', '=', 'roles.id')
                ->where('users_roles.user_id', $user->id)
                ->get();
            foreach ($roles as $role){
                $offices = DB::table('offices')
                    ->join('offices_users', 'offices_users.office_id', '=', 'offices.id')
                    ->where('offices_users.user_role_id', $role->id)
                    ->get();
                foreach ($offices as $office) {
                    $campus_office = CampusOffice::where([
                        ['campus_id', $id], ['office_id', $office->office_id]
                    ])->first();
                    if(!(is_null($campus_office))) {
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
                if($role->role_id == 7 || $role->role_id == 8){
                    $collection_1->push([
                        'user_role_id' => $role->id,
                        'role_id' => $role->role_id,
                        'role' => $role->role,
                        'office_user_id' => null,
                        'office_id' => null,
                        'office_name' => null
                    ]);
                }
            }
            if($is_active_qa) {
                $collection_qa->push([
                    'id' => $campus_user->id,
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
            if($is_active_chairman){
                foreach ($collection_1 as $col){
                    if($col['role_id'] == 1 && $col['office_id'] == $chairman_office_id){
                        $collection_chairman->push([
                            'id' => $campus_user->id,
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
                }
            }
            if($is_active_head){
                foreach ($collection_1 as $col){
                    if($col['role_id'] == 1 || $col['role_id'] == 2){
                        $sub_offices = Office::where('parent_office_id', $head_office_id)->get();
                        foreach ($sub_offices as $sub_office){
                            if($sub_office->id == $col['office_id']){
                                $collection_head->push([
                                    'id' => $campus_user->id,
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
                        }
                    }
                }
            }
        }
        return response()->json(['users' => $collection_qa, 'college' => $collection_head, 'department' => $collection_chairman]);
    }

    public function showTF($id){
        $collection = new Collection();
        $office = Office::where('id', $id)->first();
        $office_users = OfficeUser::where('office_id', $id)->get();
        $role = null;
        foreach ($office_users as $office_user){
            $user_role = UserRole::where('id', $office_user->user_role_id)->first();
            if($user_role->role_id == 2){
                foreach ($office_users as $officeUser){
                    $user_role = UserRole::where('id', $officeUser->user_role_id)->first();
                    $user = User::where('id', $user_role->user_id)->first();
                    if($user->status == 'active') {
                        $collection->push([
                            'user_id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'role_id' => $user_role->role_id,
                            'office' => $office->office_name
                        ]);
                    }
                }
            }
            elseif($user_role->role_id == 11 ){
                $user_role = UserRole::where('id', $office_user->user_role_id)->first();
                $user = User::where('id', $user_role->user_id)->first();
                if($user->status == 'active') {
                    $collection->push([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'role_id' => $user_role->role_id,
                        'office' => $office->office_name
                    ]);
                }
                $offices = Office::where('parent_office_id', $office->id)->get();
                foreach ($offices as $office) {
                    $office_users = OfficeUser::where('office_id', $office->id)->get();
                    foreach ($office_users as $office_user){
                        $user_role = UserRole::where('id', $office_user->user_role_id)->first();
                        $user = User::where('id', $user_role->user_id)->first();
                        if($user->status == 'active') {
                            $collection->push([
                                'user_id' => $user->id,
                                'first_name' => $user->first_name,
                                'last_name' => $user->last_name,
                                'role_id' => $user_role->role_id,
                                'office' => $office->office_name
                            ]);
                        }
                }
            }
            }
        }
        return response()->json(['users' => $collection]);
    }

    public function showLocalAccreditor($id){
        $campuses = Campus::where('suc_id', $id)->get();
        $accreditor_list = new Collection();
        foreach ($campuses as $campus){
            $users = CampusUser::where('campus_id', $campus->id)->get();
            foreach ($users as $u){
                $user_role = UserRole::where([
                    ['user_id', $u->user_id], ['role_id', 8]
                ])->first();
                if(!(is_null($user_role))){
                    $user = User::where('id', $u->user_id)->first();
                    $accreditor = AccreditorProfile::where('user_id', $u->user_id)->first();
                    if(is_null($accreditor)) continue;
                    else {
                        if (!($accreditor_list->contains('user_id', $user->id))) {
                            $accreditor_list->push([
                                'user_id' => $user->id,
                                'first_name' => $user->first_name,
                                'last_name' => $user->last_name,
                                'email' => $user->email,
                                'contact_no' => $user->contact_no,
                                'accreditor_status' => $accreditor->accreditor_status,
                                'region' => $accreditor->region,
                                'suc_status' => $accreditor->suc_status,
                                'designation' => $accreditor->designation,
                                'academic_rank' => $accreditor->academic_rank,
                                'campus_id' => $accreditor->campus_id,
                                'status' => $user->status,
                            ]);
                        }
                    }
                }
            }
        }
        return response()->json(['users' => $accreditor_list]);
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

    public function deleteUser(request $request,$id){
        $user = User::where('id', $id)->first();
        $user->status = 'inactive';
        $user->save();

        $user_log = new UserLog();
        $user_log->user_id = auth()->user()->id;
        $user_log->activity = 'User '.auth()->user()->id.' deactivated user '.$id;
        $user_log->address = $request->ip();
        $user_log->device = $request->header('User-Agent');
        $user_log->save();
        return response()->json(['status' => true, 'message' => 'Successfully disabled user account']);
    }

    public function activateUser(request $request,$id){
        $user = User::where('id', $id)->first();
        $user->status = 'active';
        $user->save();
        $user_log = new UserLog();
        $user_log->user_id = auth()->user()->id;
        $user_log->activity = 'User '.auth()->user()->id.' activated user '.$id;
        $user_log->address = $request->ip();
        $user_log->device = $request->header('User-Agent');
        $user_log->save();
        return response()->json(['status' => true, 'message' => 'Successfully activated user account']);
    }


    public function removeUser($id){
        $user = User::where('id', $id);
        $user->delete();
    }

    public function removeToCampus($campusID, $userID){
        $campus_user = CampusUser::where([
            ['campus_id', $campusID],['user_id', $userID]
        ])->first();
        $user_roles = UserRole::where('user_id', $userID)->get();
        foreach ($user_roles as $user_role){
           $office_users = OfficeUser::where('user_role_id', $user_role->id)->get();
           foreach ($office_users as $office_user){
               $campus_office = CampusOffice::where([
                   ['campus_id', $campusID], ['office_id', $office_user->office_id]
               ])->first();
               if(!(is_null($campus_office))){
                   $officeUser= OfficeUser::where('id', $office_user->id);
                   $officeUser->delete();
               }
           }
       }
        if(!(is_null($campus_user))){
            $campus_user->delete();
            return response()->json(['status' => true, 'message' => 'Successfully removed user to this campus']);
        }
    }


    public function addToCampus($campusID, $userID){
        $campus_user = CampusUser::where([
            ['user_id', $userID], ['campus_id', $campusID]
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
        $user->middle_initial = $request->middle_initial;
        $user->name_extension = $request->name_extension;
        $user->email = $request->email;
        $user->save();
        return response()->json(["status" => true, "message" => "Successfully edited user info."]);
    }

    public function resetPassword($id){
        $user = User::where('id', $id)->first();
        if(!(is_null($user))){
            $user->update(['password'=> bcrypt('password')]);
            return response()->json(['status' => true, 'message' => 'Your password has successfully reset to default.']);
        }
        return response()->json(['status' => false, 'message' => 'User not found.']);
    }
}
