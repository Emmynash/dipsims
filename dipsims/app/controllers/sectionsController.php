<?php

class sectionsController extends \BaseController {

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

		$classesIn = array();
		$classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		foreach ($classes as $value) {
			$toReturn['classes'][$value->id] = $value->className;
			$classesIn[] = $value->id;
		}

		$toReturn['sections'] = array();
		if(count($classesIn) > 0){
			$sections = \DB::table('sections')
						->select('sections.id as id',
						'sections.sectionName as sectionName',
						'sections.sectionTitle as sectionTitle',
						'sections.classId as classId',
						'sections.teacherId as teacherId')
						->whereIn('sections.classId',$classesIn)
						->get();

			foreach ($sections as $key => $section) {
				$sections[$key]->teacherId = json_decode($sections[$key]->teacherId,true);
				if(isset($toReturn['classes'][$section->classId])){
					$toReturn['sections'][$toReturn['classes'][$section->classId]][] = $section;
				}
			}
		}




		$toReturn['teachers'] = array();
		$teachers = User::where('role','teacher')->get();
		foreach ($teachers as $value) {
			$toReturn['teachers'][$value->id] = $value->fullName;
		}

		return $toReturn;
	}

	public function delete($id){
		if ( $postDelete = sections::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delSection'],$this->panelInit->language['sectionDeleted'] );
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delSection'],$this->panelInit->language['sectionNotExist'] );
        }
	}

	public function create(){
		$sections = new sections();
		$sections->sectionName = Input::get('sectionName');
		$sections->sectionTitle = Input::get('sectionTitle');
		$sections->classId = Input::get('classId');
		$sections->teacherId = json_encode(Input::get('teacherId'));
		$sections->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addSection'],$this->panelInit->language['sectionAdded']);
	}

	function fetch($id){
		$sections = sections::where('id',$id)->first()->toArray();
		$sections['teacherId'] = json_decode($sections['teacherId'],true);
		return $sections;
	}

	function edit($id){
		$sections = sections::find($id);
		$sections->sectionName = Input::get('sectionName');
		$sections->sectionTitle = Input::get('sectionTitle');
		$sections->classId = Input::get('classId');
		$sections->teacherId = json_encode(Input::get('teacherId'));
		$sections->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editSection'],$this->panelInit->language['sectionUpdated']);
	}

}
