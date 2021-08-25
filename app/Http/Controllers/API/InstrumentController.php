<?php

namespace App\Http\Controllers\API;

use App\AreaInstrumentTag;
use App\AreaMandatory;
use App\Http\Controllers\Controller;
use App\AreaInstrument;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\Parameter;
use App\ParameterStatement;
use App\ProgramInstrument;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
class InstrumentController extends Controller
{
/*
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }
*/

    //CREATE INSTRUMENT FUNCTION
    public function createInstrument(request $request){
        $validator = Validator::make($request->all(), [
            'intended_program' => 'required',
            'type_of_instrument' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process instrument. Required data']);

        $test = ProgramInstrument::where([
            [strtolower('intended_program'), strtolower($request->intended_program)], [strtolower('type_of_instrument'), strtolower($request->type_of_instrument)]
        ])->first();

        $area_name = ["AREA I: VISION, MISSION, GOALS AND OBJECTIVES",
            "AREA II: FACULTY",
            "AREA III: CURRICULUM AND INSTRUCTION",
            "AREA IV: SUPPORT TO STUDENTS",
            "AREA V: RESEARCH",
            "AREA VI: EXTENSION AND COMMUNITY INVOLVEMENT",
            "AREA VII: LIBRARY",
            "AREA VIII: PHYSICAL PLANT AND FACILITIES",
            "AREA IX: LABORATORIES",
            "AREA X: ADMINISTRATION"];
        if(is_null($test)){
            $intendedProgram = new ProgramInstrument();
            $intendedProgram->intended_program = $request->intended_program;
            $intendedProgram->type_of_instrument = $request->type_of_instrument;
            $intendedProgram->save();

            for($x=0;$x<10;$x++){
                $areaInstrument = new AreaInstrument();
                $areaInstrument->intended_program_id = $intendedProgram->id;
                $areaInstrument->area_number = $x+1;
                $areaInstrument->area_name = $area_name[$x];
                $areaInstrument->version = "version 1";
                $areaInstrument->save();
            }
            return response()->json(['status' => true, 'message' => 'Successfully added instrument!','instrument' => $intendedProgram]);
        }
        return response()->json(['status' => true, 'message' => 'Instrument already exist!']);

    }

    public function createCriteriaInstrument(){
        $intendedProgram = new ProgramInstrument();
        $intendedProgram->intended_program = 'Criteria Form';
        $intendedProgram->type_of_instrument = null;
        $intendedProgram->save();

        $area_name = [
            "INSTRUCTION",
            "EXTENSION",
            "RESEARCH",
            "FACULTY",
            "LICENSURE EXAM",
            "CONSORTIA OR LINKAGE ",
            "LIBRARY"];

        for($x=0;$x<7;$x++){
            $areaInstrument = new AreaInstrument();
            $areaInstrument->intended_program_id = $intendedProgram->id;
            $areaInstrument->area_number = $x+1;
            $areaInstrument->area_name = $area_name[$x];
            $areaInstrument->version = "version 1";
            $areaInstrument->save();

            $parameter = new Parameter();
            $parameter->parameter = 'PARAMETER FOR AREA '.($x+1);
            $parameter->save();
            $parameter->areaInstruments()->attach($areaInstrument);
        }
        return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
    }

    public function createCriteriaInstrumentLevelIV(){
        $intendedProgram = new ProgramInstrument();
        $intendedProgram->intended_program = 'Criteria Form Level IV';
        $intendedProgram->type_of_instrument = null;
        $intendedProgram->save();

        $area_name = [
            "RESEARCH",
            "PERFORMANCE OF GRADUATES",
            "COMMUNITY SERVICE",
            "INTERNATIONAL LINKAGES AND CONSORTIA",
            "PLANNING"];

        for($x=0;$x<5;$x++){
            $areaInstrument = new AreaInstrument();
            $areaInstrument->intended_program_id = $intendedProgram->id;
            $areaInstrument->area_number = $x+1;
            $areaInstrument->area_name = $area_name[$x];
            $areaInstrument->version = "version 1";
            $areaInstrument->save();

            $parameter = new Parameter();
            $parameter->parameter = 'PARAMETER FOR AREA '.($x+1);
            $parameter->save();
            $parameter->areaInstruments()->attach($areaInstrument);
        }
        return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
    }

    public function setInitialAreaMandatory(){
        for($x=372; $x<379; $x++){
            $areamandatory = new AreaMandatory();
            $areamandatory->area_instrument_id = $x;
            $areamandatory->type = 'Optional';
            $areamandatory->program_status = 'Graduate';
            $areamandatory->save();

            $areamandatory = new AreaMandatory();
            $areamandatory->area_instrument_id = $x;
            $areamandatory->type = 'Optional';
            $areamandatory->program_status = 'Undergraduate';
            $areamandatory->save();
        }
        for($x=419; $x<424; $x++){
            $areamandatory = new AreaMandatory();
            $areamandatory->area_instrument_id = $x;
            $areamandatory->type = 'Mandatory';
            $areamandatory->program_status = 'Graduate';
            $areamandatory->save();

            $areamandatory = new AreaMandatory();
            $areamandatory->area_instrument_id = $x;
            $areamandatory->type = 'Mandatory';
            $areamandatory->program_status = 'Undergraduate';
            $areamandatory->save();
        }
    }

    public function setAreaMandatory(request $request, $id){
        $area_1 = AreaMandatory::where([
           ['area_instrument_id', $id],['program_status', 'Graduate']
        ])->first();
        if (!(is_null($area_1))){
            $area_1->type = $request->graduate;
            $area_1->save();
        }
        $area_2 = AreaMandatory::where([
            ['area_instrument_id', $id],['program_status', 'Undergraduate']
        ])->first();
        if (!(is_null($area_2))){
            $area_2->type = $request->undergraduate;
            $area_2->save();
        }

        if($area_1 && $area_2) return response()->json(['status' => true, 'message' => 'Successfully set instrument type!']);
        else return response()->json(['status' => false, 'message' => 'Unsuccessfully set instrument type!']);
//        if(is_null($area)){
//            $area = new AreaMandatory();
//            $area->area_instrument_id = $id;
//            $area->type = $request->type;
//            $area->program_status = $request->program_status;
//            $success = $area->save();
//            if($success) return response()->json(['status' => true, 'message' => 'Successfully set instrument type!']);
//            else return response()->json(['status' => false, 'message' => 'Error saving the type!']);
//        }

    }

    public function removeAreaMandatory($id){
        $area = AreaMandatory::where('id', $id);
        $success = $area->delete();
        if($success) return response()->json(['status' => true, 'message' => 'Successfully removed instrument type!']);
        return response()->json(['status' => false, 'message' => 'Unsuccessfully removed instrument type!']);
    }

    public function showProgram(){
        $collection = new Collection();
        $intendedPrograms = ProgramInstrument::all();
        foreach ($intendedPrograms as $intendedProgram){
            if($intendedProgram->id != 42 && $intendedProgram->id != 47 && $intendedProgram->id != 48){
                $collection->push([
                    'id' => $intendedProgram->id,
                    'intended_program' => $intendedProgram->intended_program,
                    'type_of_instrument' => $intendedProgram->type_of_instrument,
                    'created_at' => $intendedProgram->created_at,
                    'updated_at' => $intendedProgram->updated_at,
                ]);
            }
        }
        return response()->json($collection);
    }
//    public function showProgram($id){
//        $collection = new Collection();
//        $instruments = AreaInstrument::where('intended_program_id', $id)->get();
//        foreach ($instruments as $instrument){
//            $area_mandatory = AreaMandatory::where('id', $instrument->id)->get();
//            $collection->push([
//                'id' => $instrument->id,
//                'intended_program_id' => $instrument->intended_program_id,
//                'area_number' => $instrument->area_number,
//                'area_name' => $instrument->area_name,
//                'version' => $instrument->version,
//                'created_at' => $instrument->created_at,
//                'updated_at' => $instrument->updated_at,
//                'status' => $area_mandatory
//            ]);
//        }
//        return response()->json($collection);
//    }

    public function showInstrument($id){
        $collection = new Collection();
        $instruments = AreaInstrument::where('intended_program_id', $id)->get();
        foreach ($instruments as $instrument){
            $tags = AreaInstrumentTag::where('area_instrument_id', $instrument->id)->get();
            $collection->push([
                'id' =>  $instrument->id,
                'intended_program_id' =>  $instrument->intended_program_id,
                'area_number' =>  $instrument->area_number,
                'area_name' =>  $instrument->area_name,
                'version' =>  $instrument->version,
                'created_at' =>  $instrument->created_at,
                'updated_at' =>  $instrument->updated_at,
                'tags' =>  $tags,
            ]);
        }
        return response()->json(['instrument' => $collection]);
    }

    public function deleteProgram($id){
        $intendedProgram = ProgramInstrument::where('id', $id)->first();
        $instruments = AreaInstrument::where('intended_program_id', $intendedProgram->id)->get();
        foreach ($instruments as $instrument){
            $check = InstrumentProgram::where('area_instrument_id', $instrument->id)->get();
            if($check->count() > 0) return response()->json(['status' => false, 'message' => 'Instrument is being used by a program.']);
        }
        $intendedProgram->delete();
        return response()->json(['status' => true, 'message' => 'Instrument successfully deleted!']);
    }

    public function editProgram(request $request, $id){
        $intendedProgram = ProgramInstrument::where('id',$id)->first();
        $intendedProgram->intended_program = $request->intended_program;
        $intendedProgram->type_of_instrument = $request->type_of_instrument;
        $intendedProgram->save();
        return response()->json(['status' => true, 'message' => 'Successfully updated the instrument!', 'instrument' => $intendedProgram]);
    }

    public function editInstrument(request $request, $id){
        $instrument = AreaInstrument::where('id',$id)->first();
        $instrument->area_name = $request->instrument;
        $instrument->save();
        return response()->json(['status' => true, 'message' => 'Successfully updated the instrument!']);
    }

    public function cloneInstrument(request $request,$id){
        $validator = Validator::make($request->all(), [
            'intended_program' => 'required',
            'type_of_instrument' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process instrument. Required data']);

        $test = ProgramInstrument::where([
            [strtolower('intended_program'), strtolower($request->intended_program)], [strtolower('type_of_instrument'), strtolower($request->type_of_instrument)]
        ])->first();

        if(!(is_null($test))) return response()->json(['status' => false, 'message' => 'Instrument already exist']);

//        $area_name = ["AREA I: VISION, MISSION, GOALS AND OBJECTIVES",
//            "AREA II: FACULTY",
//            "AREA III: Curriculum and Instruction",
//            "AREA IV: SUPPORT TO STUDENTS",
//            "AREA V: RESEARCH",
//            "AREA VI: EXTENSION AND COMMUNITY INVOLVEMENT",
//            "AREA VII: LIBRARY",
//            "AREA VIII: PHYSICAL PLANT AND FACILITIES",
//            "AREA IX: LABORATORIES",
//            "AREA X: ADMINISTRATION"];

        $intendedProgram = new ProgramInstrument();
        $intendedProgram->intended_program = $request->intended_program;
        $intendedProgram->type_of_instrument = $request->type_of_instrument;
        $intendedProgram->save();

        $instruments = AreaInstrument::where('intended_program_id', $id)->get();
        foreach ($instruments as $instrument){
            $areaInstrument = new AreaInstrument();
            $areaInstrument->intended_program_id = $intendedProgram->id;
            $areaInstrument->area_number = $instrument->area_number;
            $areaInstrument->area_name = $instrument->area_name;
            $areaInstrument->version = "version 1";
            $areaInstrument->save();
            $parameters = InstrumentParameter::where('area_instrument_id', $instrument->id)->get();
            foreach ($parameters as $parameter){
                $instrumentParameter = new InstrumentParameter();
                $instrumentParameter->area_instrument_id = $areaInstrument->id;
                $instrumentParameter->parameter_id = $parameter->parameter_id;
                $instrumentParameter->save();

                $statements = InstrumentStatement::where('instrument_parameter_id',$parameter->id)->get();
                foreach ($statements as $statement){
                    $instrumentStatement = new InstrumentStatement();
                    $instrumentStatement->instrument_parameter_id = $instrumentParameter->id;
                    $instrumentStatement->benchmark_statement_id = $statement->benchmark_statement_id;
                    $instrumentStatement->parent_statement_id = $statement->parent_statement_id;
                    $instrumentStatement->save();
                }
           }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added instrument!','instrument' => $areaInstrument]);
    }

    public function addInstrumentTag(request $request,$id){
        $tag = AreaInstrumentTag::where([
            ['area_instrument_id', $id], ['tag', $request->tag]
        ])->first();
        if(is_null($tag)){
            $tag = new AreaInstrumentTag();
            $tag->tag = $request->tag;
            $tag->area_instrument_id = $id;
            $success = $tag->save();
            if($success) return response()->json(['status' => true, 'message' => 'Successfully added tag']);
            else return response()->json(['status' => false, 'message' => 'Unsuccessfully added tag']);
        }
        return response()->json(['status' => true, 'message' => 'Already exist!']);
    }

    public function removeInstrumentTag($id){
        $tag = AreaInstrumentTag::where('id', $id);
        $success = $tag->delete();
        if($success) return response()->json(['status' => true, 'message' => 'Successfully removed tag']);
        else return response()->json(['status' => false, 'message' => 'Unsuccessfully removed tag']);
    }

    public function showInstrumentTag($id){
        $tags = AreaInstrumentTag::where('area_instrument_id', $id)->get();
        return response()->json($tags);
    }
}
