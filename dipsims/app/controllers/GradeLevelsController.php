<?php

class GradeLevelsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('gradeLevels')){
			exit;
		}
	}

	public function listAll()
	{
		return gradeLevels::get();
	}

	public function delete($id){
		if ( $postDelete = gradeLevels::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delGradeLevel'],$this->panelInit->language['gradeDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delGradeLevel'],$this->panelInit->language['gradeNotExist']);
        }
	}

	public function create(){
		$gradeLevels = new gradeLevels();
		$gradeLevels->gradeName = Input::get('gradeName');
		$gradeLevels->gradeDescription = Input::get('gradeDescription');
		$gradeLevels->gradePoints = Input::get('gradePoints');
		$gradeLevels->gradeFrom = Input::get('gradeFrom');
		$gradeLevels->gradeTo = Input::get('gradeTo');
		$gradeLevels->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addLevel'],$this->panelInit->language['gradeCreated'],$gradeLevels->toArray() );
	}

	function fetch($id){
		return gradeLevels::where('id',$id)->first();
	}

	function edit($id){
		$gradeLevels = gradeLevels::find($id);
		$gradeLevels->gradeName = Input::get('gradeName');
		$gradeLevels->gradeDescription = Input::get('gradeDescription');
		$gradeLevels->gradePoints = Input::get('gradePoints');
		$gradeLevels->gradeFrom = Input::get('gradeFrom');
		$gradeLevels->gradeTo = Input::get('gradeTo');
		$gradeLevels->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editGrade'],$this->panelInit->language['gradeUpdated'],$gradeLevels->toArray() );
	}
}
