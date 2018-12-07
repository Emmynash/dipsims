<?php

class SubjectsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('Subjects')){
			exit;
		}
	}

	public function listAll()
	{
		$toReturn = array();
		$toReturn['subjects'] = \DB::table('subject')
					->leftJoin('users', 'users.id', '=', 'subject.teacherId')
					->select('subject.id as id',
					'subject.subjectTitle as subjectTitle',
					'subject.passGrade as passGrade',
					'subject.finalGrade as finalGrade',
					'subject.teacherId as teacherId',
					'users.fullName as teacherName')
					->get();
		$toReturn['teachers'] = User::where('role','teacher')->get()->toArray();
		return $toReturn;
	}

	public function delete($id){
		if ( $postDelete = subject::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delSubject'],$this->panelInit->language['subjectDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delSubject'],$this->panelInit->language['subjectNotExist']);
        }
	}

	public function create(){
		$subject = new subject();
		$subject->subjectTitle = Input::get('subjectTitle');
		$subject->teacherId = Input::get('teacherId');
		$subject->passGrade = Input::get('passGrade');
		$subject->finalGrade = Input::get('finalGrade');
		$subject->save();

		$teacher = User::where('id',$subject->teacherId)->first();
		$subject->teacherName = $teacher->fullName;

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addSubject'],$this->panelInit->language['subjectCreated'],$subject->toArray() );
	}

	function fetch($id){
		return subject::where('id',$id)->first();
	}

	function edit($id){
		$subject = subject::find($id);
		$subject->subjectTitle = Input::get('subjectTitle');
		$subject->teacherId = Input::get('teacherId');
		$subject->passGrade = Input::get('passGrade');
		$subject->finalGrade = Input::get('finalGrade');
		$subject->save();

		$teacher = User::where('id',$subject->teacherId)->first();
		$subject->teacherName = $teacher->fullName;

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editSubject'],$this->panelInit->language['subjectEdited'],$subject->toArray() );
	}

}
