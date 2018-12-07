<?php

class ClassesController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('classes')){
			exit;
		}
	}

	public function listAll()
	{
		$toReturn = array();
		$teachers = User::where('role','teacher')->get()->toArray();
		$toReturn['dormitory'] =  dormitories::get()->toArray();

		$toReturn['subject'] = array();
		$subjects =  subject::get();
		foreach ($subjects as $value) {
		    $toReturn['subject'][$value->id] = $value->subjectTitle;
		}

		$toReturn['classes'] = array();
		
		$classes = \DB::table('classes')
					->leftJoin('dormitories', 'dormitories.id', '=', 'classes.dormitoryId')
					->select('classes.id as id',
					'classes.className as className',
					'classes.classTeacher as classTeacher',
					'classes.classSubjects as classSubjects',
					'dormitories.id as dormitory',
					'dormitories.dormitory as dormitoryName')
					->where('classAcademicYear',$this->panelInit->selectAcYear)
					->get();
					
		$toReturn['teachers'] = array();
		while (list($teacherKey, $teacherValue) = each($teachers)) {
			$toReturn['teachers'][$teacherValue['id']] = $teacherValue;
		}

		while (list($key, $class) = each($classes)) {
			$toReturn['classes'][$key] = $class;
			// print_r($toReturn['classes'][$key]['classSubjects']);
			$toReturn['classes'][$key]['classSubjects'] = 
			json_decode($toReturn['classes'][$key]['classSubjects']);
			if($toReturn['classes'][$key]['classTeacher'] != " "){
				$toReturn['classes'][$key]['classTeacher'] = json_decode($toReturn['classes'][$key]['classTeacher'],true);
				if(is_array($toReturn['classes'][$key]['classTeacher'])){
					while (list($teacherKey, $teacherID) = each($toReturn['classes'][$key]['classTeacher'])) {
						if(isset($toReturn['teachers'][$teacherID]['fullName'])){
							$toReturn['classes'][$key]['classTeacher'][$teacherKey] = $toReturn['teachers'][$teacherID]['fullName'];
						}else{
							unset($toReturn['classes'][$key]['classTeacher'][$teacherKey]) ;
						}
					}
					$toReturn['classes'][$key]['classTeacher'] = implode($toReturn['classes'][$key]['classTeacher'], ", ");
				}
			}
		}

		return $toReturn;
	}

	public function delete($id){
		if ( $postDelete = classes::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delClass'],$this->panelInit->language['classDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delClass'],$this->panelInit->language['classNotExist']);
        }
	}

	public function create(){
		$classes = new classes();
		$classes->className = Input::get('className');
		$classes->classTeacher = json_encode(Input::get('classTeacher'));
		$classes->classAcademicYear = $this->panelInit->selectAcYear;
		$classes->classSubjects = json_encode(Input::get('classSubjects'));
		if(Input::has('dormitoryId')){
			$classes->dormitoryId = Input::get('dormitoryId');
		}
		$classes->save();

		$classes->classTeacher = "";
		$teachersList = User::whereIn('id',Input::get('classTeacher'))->get();
		foreach ($teachersList as $teacher) {
			$classes->classTeacher .= $teacher->fullName.", ";
		}
		$classes->classSubjects = json_decode($classes->classSubjects);

		if(Input::has('enableFeeAllocation') AND Input::get('enableFeeAllocation')){
			$allocationValues = Input::get('allocationValues');
			if(count($allocationValues) != 0){

				$dbAllocationValues = array();
				foreach ($allocationValues as $allocation) {
					$dbAllocationValues[$allocation['id']] = $allocation['feeDefault'];
				}

				$feeAllocation = new feeAllocation();
				$feeAllocation->allocationType = "class";
				$feeAllocation->allocationId = $classes->id;
				$feeAllocation->allocationValues = json_encode($dbAllocationValues);
				$feeAllocation->save();
			}
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addClass'],$this->panelInit->language['classCreated'],$classes->toArray() );
	}

	function fetch($id){
		$classDetail = classes::where('id',$id)->first()->toArray();
		$classDetail['classTeacher'] = json_decode($classDetail['classTeacher']);
		$classDetail['classSubjects'] = json_decode($classDetail['classSubjects']);
		return $classDetail;
	}

	function edit($id){
		$classes = classes::find($id);
		$classes->className = Input::get('className');
		$classes->classTeacher = json_encode(Input::get('classTeacher'));
		$classes->classSubjects = json_encode(Input::get('classSubjects'));
		if(Input::has('dormitoryId')){
			$classes->dormitoryId = Input::get('dormitoryId');
		}
		$classes->save();

		$classes->classTeacher = "";
		$teachersList = User::whereIn('id',Input::get('classTeacher'))->get();
		foreach ($teachersList as $teacher) {
			$classes->classTeacher .= $teacher->fullName.", ";
		}
		$classes->classSubjects = json_decode($classes->classSubjects);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editClass'],$this->panelInit->language['classUpdated'],$classes->toArray() );
	}

}
