<?php

namespace App\Http\Controllers\API;

use App\AccreditorProfile;
use App\AccreditorSpecialization;
use App\Campus;
use App\CampusUser;
use App\Http\Controllers\Controller;
use App\SUC;
use App\User;
use App\UserEducation;
use App\UserOtherInformation;
use App\UserWorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserProfileController extends Controller
{
    public function savePersonalInfo(request $request, $id){
        $user = User::where('id', $id)->first();
        if(is_null($user)) return response()->json(['status' => false, 'message' => 'User id does not exist'], 500);
        $user->gender = $request->gender;
        $user->civil_status = $request->civil_status;
        $user->place_of_birth = $request->place_of_birth;
        $user->nationality = $request->nationality;
        $user->birthdate = $request->birthdate;
        $user->current_address = $request->current_address;
        $user->home_address = $request->home_address;
        $user->telephone_no = $request->telephone_no;
        $user->contact_no = $request->contact_no;
        $user->government_id_type = $request->government_id_type;
        $user->id_number = $request->id_number;
        $user->date_of_issuance = $request->date_of_issuance;
        $user->place_of_issuance = $request->place_of_issuance;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully saved user information', 'user' =>$user]);
    }

    public function showPersonalInfo($id){
        $user = User::where('id', $id)->first();
        return response()->json(['user' =>$user]);
    }

    public function createEducationInfo(request $request, $id){
        $user = new UserEducation();
        $user->user_id = $id;
        $user->school_name = $request->school_name;
        $user->level = $request->level;
        $user->started_at = $request->started_at;
        $user->graduated_at = $request->graduated_at;
        $user->honor = $request->honor;
        $user->course = $request->course;
        $user->units_earned = $request->units_earned;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully saved user education.', 'education' =>$user]);
    }

    public function showEducationInfo($id){
        $user = UserEducation::where('user_id', $id)->get();
        return response()->json(['educations' =>$user]);
    }

    public function editEducationInfo(request $request, $id){
        $user = UserEducation::where('id', $id)->first();
        if(is_null($user)) return response()->json(['status' => false, 'message' => 'ID does not exist']);
        $user->school_name = $request->school_name;
        $user->level = $request->level;
        $user->started_at = $request->started_at;
        $user->graduated_at = $request->graduated_at;
        $user->honor = $request->honor;
        $user->course = $request->course;
        $user->units_earned = $request->units_earned;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully saved user education.', 'education' =>$user]);
    }

    public function deleteEducationInfo($id){
        $user = UserEducation::where('id', $id)->first();
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted user education.']);
    }

    public function createWorkExperience(request $request, $id){
        $user = new UserWorkExperience();
        $user->user_id = $id;
        $user->company_name = $request->company_name;
        $user->position = $request->position;
        $user->start = $request->start;
        $user->end = $request->end;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully saved user work information', 'work' =>$user]);
    }

    public function showWorkExperience($id){
        $user = UserWorkExperience::where('user_id', $id)->get();
        return response()->json(['works' =>$user]);
    }

    public function editWorkExperience(request $request, $id){
        $user = UserWorkExperience::where('id', $id)->first();
        $user->company_name = $request->company_name;
        $user->position = $request->position;
        $user->start = $request->start;
        $user->end = $request->end;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully saved user work information', 'work' =>$user]);
    }

    public function deleteWorkExperience($id){
        $user = UserWorkExperience::where('id', $id)->first();
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted user work information.']);
    }

    public function editOtherInformation(request $request, $id){
        $user = UserOtherInformation::where('user_id', $id)->first();
        if(is_null($user)) {
            $user = new UserOtherInformation();
            $user->user_id = $id;
            $user->skill_hobby = $request->skill_hobby;
            $user->distribution_recognition = $request->distribution_recognition;
            $user->association_organization = $request->association_organization;
            $user->save();
        }
        else{
            $user->skill_hobby = $request->skill_hobby;
            $user->distribution_recognition = $request->distribution_recognition;
            $user->association_organization = $request->association_organization;
            $user->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully saved user other information.', 'user' => $user]);
    }

    public function showOtherInformation($id){
        $user = UserOtherInformation::where('user_id', $id)->first();
        if(is_null($user)){
            $user = new UserOtherInformation();
            $user->user_id = $id;
            $user->skill_hobby = null;
            $user->distribution_recognition = null;
            $user->association_organization = null;
            $user->save();
        }
        return response()->json(['user' => $user]);
    }

    public function editAccreditorProfile(request $request, $id){
        $campus_user = CampusUser::where('user_id', $id)->first();
        if(!(is_null($campus_user))){
            $campus_user->campus_id = $request->campus_id;
            $campus_user->save();
        }
        $user = AccreditorProfile::where('user_id', $id)->first();
        $user->academic_rank = $request->academic_rank;
        $user->designation = $request->designation;
        $user->region = $request->region;
        $user->campus_id = $request->campus_id;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully saved accreditor information.', 'accreditor' => $user]);
    }

    public function showAccreditorProfile($id){
        $collection = new Collection();
        $user = AccreditorProfile::where('user_id', $id)->first();
        if($user->campus_id == null){
            $collection->push([
                'suc_name' => null,
                'campus_name' => null,
                'campus_id' => null,
                'campus_region' => null,
                'user_id' => $user->user_id,
                'academic_rank' => $user->academic_rank,
                'designation' => $user->designation,
                'region' => $user->region,
                'status' => $user->status,
                'accreditor_status' => $user->accreditor_status
            ]);
        }
        else{
            $campus = Campus::where('id',$user->campus_id)->first();
            $suc = SUC::where('id', $campus->suc_id)->first();
            $collection->push([
                'suc_name' => $suc->institution_name,
                'campus_name' => $campus->campus_name,
                'campus_id' => $campus->id,
                'campus_region' => $campus->region,
                'user_id' => $user->user_id,
                'academic_rank' => $user->academic_rank,
                'designation' => $user->designation,
                'region' => $user->region,
                'status' => $user->status,
                'accreditor_status' => $user->accreditor_status
            ]);
        }
        return response()->json(['accreditor' => $collection]);
    }

    public function addAccreditorSpecialization(request $request, $id){
        $user = new AccreditorSpecialization();
        $user->accreditor_id = $id;
        $user->specialization = $request->specialization;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Successfully added accreditor specialization.', 'accreditor' => $user]);
    }

    public function showAccreditorSpecialization($id){
        $user = AccreditorSpecialization::where('accreditor_id', $id)->get();
        return response()->json(['specializations' => $user]);
    }

    public function deleteAccreditorSpecialization($id){
        $user = AccreditorSpecialization::where('id', $id)->first();
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted accreditor specialization']);
    }
}

