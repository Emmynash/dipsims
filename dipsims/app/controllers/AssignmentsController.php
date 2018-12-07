<?php

class AssignmentsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('Assignments')){
			exit;
		}
	}

	public function listAll()
	{
		$toReturn = array();
		$toReturn['classes'] = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		$classesArray = array();
		while (list(, $class) = each($toReturn['classes'])) {
			$classesArray[$class['id']] = $class['className'];
		}

		$toReturn['assignments'] = array();
		if(count($classesArray) > 0){
			$assignments = new assignments();

			if($this->data['users']->role == "student"){
				$assignments = $assignments->where('classId','LIKE','%"'.$this->data['users']->studentClass.'"%');
			}else{
				while (list($key, ) = each($classesArray)) {
					$assignments = $assignments->orWhere('classId','LIKE','%"'.$key.'"%');
				}
			}

			if($this->data['users']->role == "teacher"){
				$assignments = $assignments->where('teacherId',$this->data['users']->id);
			}

			$assignments = $assignments->get();

			foreach ($assignments as $key => $assignment) {
				$classId = json_decode($assignment->classId);
				if($this->data['users']->role == "student" AND !in_array($this->data['users']->studentClass, $classId)){
					continue;
				}
				$toReturn['assignments'][$key]['id'] = $assignment->id;
				$toReturn['assignments'][$key]['subjectId'] = $assignment->subjectId;
				$toReturn['assignments'][$key]['AssignTitle'] = $assignment->AssignTitle;
				$toReturn['assignments'][$key]['AssignDescription'] = $assignment->AssignDescription;
				$toReturn['assignments'][$key]['AssignFile'] = $assignment->AssignFile;
				$toReturn['assignments'][$key]['AssignDeadLine'] = $assignment->AssignDeadLine;
				$toReturn['assignments'][$key]['classes'] = "";

				while (list(, $value) = each($classId)) {
					if(isset($classesArray[$value])) {
						$toReturn['assignments'][$key]['classes'] .= $classesArray[$value].", ";
					}
				}
			}
		}

		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
	}

	public function download($id){
		$toReturn = assignments::where('id',$id)->first();
		if(file_exists('uploads/assignments/'.$toReturn->AssignFile)){
			$fileName = preg_replace('/[^a-zA-Z0-9-_\.]/','-',$toReturn->AssignTitle). "." .pathinfo($toReturn->AssignFile, PATHINFO_EXTENSION);
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=" . $fileName);
			echo file_get_contents('uploads/assignments/'.$toReturn->AssignFile);
		}
		exit;
	}

	public function delete($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		if ( $postDelete = assignments::where('id', $id)->first() )
        {
			@unlink("uploads/assignments/".$postDelete->AssignFile);
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delAssignment'],$this->panelInit->language['assignemntDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delAssignment'],$this->panelInit->language['assignemntNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$assignments = new assignments();
		$assignments->classId = json_encode(Input::get('classId'));
		$assignments->subjectId = Input::get('subjectId');
		$assignments->teacherId = Input::get('teacherId');
		$assignments->AssignTitle = Input::get('AssignTitle');
		$assignments->AssignDescription = Input::get('AssignDescription');
		$assignments->AssignDeadLine = Input::get('AssignDeadLine');
		$assignments->teacherId = $this->data['users']->id;
		$assignments->save();
		if (Input::hasFile('AssignFile')) {
			$fileInstance = Input::file('AssignFile');
			$newFileName = "assignments_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/assignments/',$newFileName);

			$assignments->AssignFile = $newFileName;
			$assignments->save();
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['AddAssignments'],$this->panelInit->language['assignmentCreated'],$assignments->toArray());
	}

	function fetch($id){
		$toReturn = assignments::where('id',$id)->first();
		$DashboardController = new DashboardController();
		$toReturn['subject'] = $DashboardController->subjectList(json_decode($toReturn->classId,true));
		return $toReturn;
	}

	function edit($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$assignments = assignments::find($id);
		$assignments->classId = json_encode(Input::get('classId'));
		$assignments->subjectId = Input::get('subjectId');
		$assignments->teacherId = Input::get('teacherId');
		$assignments->AssignTitle = Input::get('AssignTitle');
		$assignments->AssignDescription = Input::get('AssignDescription');
		$assignments->AssignDeadLine = Input::get('AssignDeadLine');
		if (Input::hasFile('AssignFile')) {
			@unlink("uploads/assignments/".$assignments->AssignFile);
			$fileInstance = Input::file('AssignFile');
			$newFileName = "assignments_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/assignments/',$newFileName);

			$assignments->AssignFile = $newFileName;
			$assignments->save();
		}
		$assignments->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editAssignment'],$this->panelInit->language['assignmentModified'],$assignments->toArray());
	}

	function checkUpload(){
		$toReturn = assignments::where('id',Input::get('assignmentId'))->first();

		$StartDateArray = strptime($toReturn->AssignDeadLine, '%m/%d/%Y');
		$sStartDateTimeStamp = mktime(0, 0, 0, $StartDateArray['tm_mon']+1, $StartDateArray['tm_mday'], $StartDateArray['tm_year']+1900);

		if($sStartDateTimeStamp < time()){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['applyAssAnswer'],$this->panelInit->language['assDeadTime']);
		}

		$assignmentsAnswers = assignmentsAnswers::where('assignmentId',Input::get('assignmentId'))->where('userId',$this->data['users']->id)->count();
		if($assignmentsAnswers > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['applyAssAnswer'],$this->panelInit->language['assAlreadySub']);
		}
		return array("canApply"=>"true");
	}

	function upload($id){
		if($this->data['users']->role == "admin" || $this->data['users']->role == "teacher") exit;
		$assignmentsAnswers = new assignmentsAnswers();
		$assignmentsAnswers->assignmentId = $id;
		$assignmentsAnswers->userId = $this->data['users']->id;
		$assignmentsAnswers->userNotes = Input::get('userNotes');
		$assignmentsAnswers->userTime = time();
		if (!Input::hasFile('fileName')) {
			return $this->panelInit->apiOutput(false,$this->panelInit->language['applyAssAnswer'],$this->panelInit->language['assNoFilesUploaded']);
		}elseif (Input::hasFile('fileName')) {
			$fileInstance = Input::file('fileName');
			$newFileName = "assignments_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/assignmentsAnswers/',$newFileName);

			$assignmentsAnswers->fileName = $newFileName;
			$assignmentsAnswers->save();
		}
		$assignmentsAnswers->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['applyAssAnswer'],$this->panelInit->language['assUploadedSucc']);
	}

	function listAnswers($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;

		$assignmentsAnswers = \DB::table('assignmentsAnswers')
								->leftJoin('users', 'users.id', '=', 'assignmentsAnswers.userId')
								->leftJoin('classes', 'classes.id', '=', 'users.studentClass')
								->select('assignmentsAnswers.id as id',
								'assignmentsAnswers.userId as userId',
								'assignmentsAnswers.userNotes as userNotes',
								'assignmentsAnswers.userTime as userTime',
								'users.fullName as fullName',
								'classes.className as className')
								->where('assignmentId',$id)
								->get();

		return $assignmentsAnswers;
	}

	public function downloadAnswer($id){
		$toReturn = assignmentsAnswers::where('id',$id)->first();
		$user = User::where('id',$toReturn->userId)->first();
		if(file_exists('uploads/assignmentsAnswers/'.$toReturn->fileName)){
			$fileName = preg_replace('/[^a-zA-Z0-9-_\.]/','-',$user->fullName). "." .pathinfo($toReturn->fileName, PATHINFO_EXTENSION);
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=" . $fileName);
			echo file_get_contents('uploads/assignmentsAnswers/'.$toReturn->fileName);
		}
		exit;
	}

}
