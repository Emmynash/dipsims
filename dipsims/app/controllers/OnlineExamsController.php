<?php

class OnlineExamsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('onlineExams')){
			exit;
		}
	}

	public function listAll()
	{
		$toReturn = array();
		$toReturn['classes'] = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		$classesArray = array();
		while (list(, $class) = each($toReturn['classes'])) {
			$classesArray[$class['id']] = array("classTitle"=>$class['className'],"subjects"=>json_decode($class['classSubjects']));
		}

		if($this->data['users']->role == "teacher"){
			$subjects = subject::where('teacherId',$this->data['users']->id)->get()->toArray();
		}else{
			$subjects = subject::get()->toArray();
		}
		$subjectArray = array();
		while (list(, $subject) = each($subjects)) {
			$subjectArray[$subject['id']] = $subject['subjectTitle'];
		}

		$toReturn['onlineExams'] = array();
		$onlineExams = new onlineExams();

		if($this->data['users']->role == "teacher"){
			$onlineExams = $onlineExams->where('examTeacher',$this->data['users']->id);
		}

		if($this->data['users']->role == "student"){
			$onlineExams = $onlineExams->where('examClass','LIKE','%"'.$this->data['users']->studentClass.'"%');
		}

		$onlineExams = $onlineExams->where('exAcYear',$this->panelInit->selectAcYear);
		$onlineExams = $onlineExams->get();
		foreach ($onlineExams as $key => $onlineExam) {
			$classId = json_decode($onlineExam->examClass);
			if($this->data['users']->role == "student" AND !in_array($this->data['users']->studentClass, $classId)){
				continue;
			}
			$toReturn['onlineExams'][$key]['id'] = $onlineExam->id;
			$toReturn['onlineExams'][$key]['examTitle'] = $onlineExam->examTitle;
			$toReturn['onlineExams'][$key]['examDescription'] = $onlineExam->examDescription;
			if(isset($subjectArray[$onlineExam->examSubject])){
				$toReturn['onlineExams'][$key]['examSubject'] = $subjectArray[$onlineExam->examSubject];
			}
			$toReturn['onlineExams'][$key]['ExamEndDate'] = date("F j, Y",$onlineExam->ExamEndDate);
			$toReturn['onlineExams'][$key]['ExamShowGrade'] = $onlineExam->ExamShowGrade;
			$toReturn['onlineExams'][$key]['classes'] = "";

			while (list(, $value) = each($classId)) {
				if(isset($classesArray[$value])){
					$toReturn['onlineExams'][$key]['classes'] .= $classesArray[$value]['classTitle'].", ";
				}
			}
		}
		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
	}

	public function delete($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		if ( $postDelete = onlineExams::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delExam'],$this->panelInit->language['exDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delExam'],$this->panelInit->language['exNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$onlineExams = new onlineExams();
		$onlineExams->examTitle = Input::get('examTitle');
		$onlineExams->examDescription = Input::get('examDescription');
		$onlineExams->examClass = json_encode(Input::get('examClass'));
		$onlineExams->examTeacher = $this->data['users']->id;
		$onlineExams->examSubject = Input::get('examSubject');
		$onlineExams->examDate = strtotime(Input::get('examDate'));
		$onlineExams->exAcYear = $this->panelInit->selectAcYear;
		$onlineExams->ExamEndDate = strtotime(Input::get('ExamEndDate'));
		if(Input::has('ExamShowGrade')){
			$onlineExams->ExamShowGrade = Input::get('ExamShowGrade');
		}
		$onlineExams->examTimeMinutes = Input::get('examTimeMinutes');
		$onlineExams->examDegreeSuccess = Input::get('examDegreeSuccess');
		$onlineExams->examQuestion = json_encode(Input::get('examQuestion'));
		$onlineExams->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addExam'],$this->panelInit->language['examCreated'],$onlineExams->toArray() );
	}

	function fetch($id){
		$istook = onlineExamsGrades::where('examId',$id)->where('studentId',$this->data['users']->id)->count();

		$onlineExams = onlineExams::where('id',$id)->first()->toArray();
		$onlineExams['examClass'] = json_decode($onlineExams['examClass']);
		$onlineExams['examQuestion'] = json_decode($onlineExams['examQuestion']);
		if(time() > $onlineExams['ExamEndDate'] || time() < $onlineExams['examDate']){
			$onlineExams['finished'] = true;
		}
		if($istook > 0){
			$onlineExams['taken'] = true;
		}
		$onlineExams['examDate'] = date("m/d/Y",$onlineExams['examDate']);
		$onlineExams['ExamEndDate'] = date("m/d/Y",$onlineExams['ExamEndDate']);

		$DashboardController = new DashboardController();
		$onlineExams['subject'] = $DashboardController->subjectList($onlineExams['examClass']);
		return $onlineExams;
	}

	function marks($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$return = array();

		$exam = onlineExams::where('id',$id)->first();
		$return['examDegreeSuccess'] = $exam->examDegreeSuccess;

		$return['grade'] = \DB::table('onlineExamsGrades')
					->where('examId',$id)
					->leftJoin('users', 'users.id', '=', 'onlineExamsGrades.studentId')
					->select('onlineExamsGrades.id as id',
					'onlineExamsGrades.examGrade as examGrade',
					'onlineExamsGrades.examDate as examDate',
					'users.fullName as fullName',
					'users.id as studentId')
					->get();

		return json_encode($return);
	}

	function edit($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$onlineExams = onlineExams::find($id);
		$onlineExams->examTitle = Input::get('examTitle');
		$onlineExams->examDescription = Input::get('examDescription');
		$onlineExams->examClass = json_encode(Input::get('examClass'));
		$onlineExams->examTeacher = $this->data['users']->id;
		$onlineExams->examSubject = Input::get('examSubject');
		$onlineExams->examDate = strtotime(Input::get('examDate'));
		$onlineExams->ExamEndDate = strtotime(Input::get('ExamEndDate'));
		if(Input::has('ExamShowGrade')){
			$onlineExams->ExamShowGrade = Input::get('ExamShowGrade');
		}
		$onlineExams->examTimeMinutes = Input::get('examTimeMinutes');
		$onlineExams->examDegreeSuccess = Input::get('examDegreeSuccess');
		$onlineExams->examQuestion = json_encode(Input::get('examQuestion'));
		$onlineExams->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editExam'],$this->panelInit->language['examModified'],$onlineExams->toArray() );
	}

	function take($id){
		$istook = onlineExamsGrades::where('examId',$id)->where('studentId',$this->data['users']->id);
		$istookFinish = $istook->first();
		$istook = $istook->count();

		if($istook == 0){
			$onlineExamsGrades = new onlineExamsGrades();
			$onlineExamsGrades->examId = $id;
			$onlineExamsGrades->studentId = $this->data['users']->id;
			$onlineExamsGrades->examDate = time() ;
			$onlineExamsGrades->save();
		}

		$onlineExams = onlineExams::where('id',$id)->first()->toArray();
		$onlineExams['examClass'] = json_decode($onlineExams['examClass']);
		$onlineExams['examQuestion'] = json_decode($onlineExams['examQuestion'],true);
		while (list($key, $value) = each($onlineExams['examQuestion'])) {
			if(isset($onlineExams['examQuestion'][$key]['Tans'])){
				unset($onlineExams['examQuestion'][$key]['Tans']);
			}
			if(isset($onlineExams['examQuestion'][$key]['Tans1'])){
				unset($onlineExams['examQuestion'][$key]['Tans1']);
			}
			if(isset($onlineExams['examQuestion'][$key]['Tans2'])){
				unset($onlineExams['examQuestion'][$key]['Tans2']);
			}
			if(isset($onlineExams['examQuestion'][$key]['Tans3'])){
				unset($onlineExams['examQuestion'][$key]['Tans3']);
			}
			if(isset($onlineExams['examQuestion'][$key]['Tans4'])){
				unset($onlineExams['examQuestion'][$key]['Tans4']);
			}
		}
		if(time() > $onlineExams['ExamEndDate'] || time() < $onlineExams['examDate']){
			$onlineExams['finished'] = true;
		}

		if($istook > 0 AND $istookFinish['examQuestionsAnswers'] != null){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['takeExam'],$this->panelInit->language['exAlreadyTook']);
		}

		if($onlineExams['examTimeMinutes'] != 0 AND $istook > 0){
			if( (time() - $istookFinish['examDate']) > $onlineExams['examTimeMinutes']*60){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['takeExam'],$this->panelInit->language['examTimedOut']);
			}
		}

		if($onlineExams['examTimeMinutes'] == 0){
			$onlineExams['timeLeft'] = 0;
		}else{
			if($istook == 0){
				$onlineExams['timeLeft'] = $onlineExams['examTimeMinutes'] * 60;
			}
			if($istook > 0){
				$onlineExams['timeLeft'] = $onlineExams['examTimeMinutes']*60 - (time() - $istookFinish['examDate']);
			}
		}

		$onlineExams['examDate'] = date("m/d/Y",$onlineExams['examDate']);
		$onlineExams['ExamEndDate'] = date("m/d/Y",$onlineExams['ExamEndDate']);
		return $onlineExams;
	}

	function took($id){
		$onlineExams = onlineExams::where('id',$id)->first()->toArray();
		$onlineExams['examQuestion'] = json_decode($onlineExams['examQuestion'],true);

		$toReturn = array();
		$answers = Input::get('examQuestion');
		$score = 0;
		while (list($key, $value) = each($answers)) {
			if( !isset($onlineExams['examQuestion'][$key]['type']) || (isset($onlineExams['examQuestion'][$key]['type']) AND $onlineExams['examQuestion'][$key]['type'] == "radio")){
				if($value['answer'] == $onlineExams['examQuestion'][$key]['Tans']){
					if(isset($onlineExams['examQuestion'][$key]['questionMark'])){
						$score += $onlineExams['examQuestion'][$key]['questionMark'];
					}else{
						$score++;
					}
				}
			}
			if(isset($onlineExams['examQuestion'][$key]['type']) AND $onlineExams['examQuestion'][$key]['type'] == "check"){
				$pass = true;
				if(isset($onlineExams['examQuestion'][$key]['Tans1'])){
					if(isset($value['answer1']) AND $value['answer1'] != true){
						$pass = false;
					}
				}
				if(isset($onlineExams['examQuestion'][$key]['Tans2'])){
					if(isset($value['answer2']) AND $value['answer2'] != true){
						$pass = false;
					}
				}
				if(isset($onlineExams['examQuestion'][$key]['Tans3'])){
					if(isset($value['answer3']) AND $value['answer3'] != true){
						$pass = false;
					}
				}
				if(isset($onlineExams['examQuestion'][$key]['Tans4'])){
					if(isset($value['answer4']) AND $value['answer4'] != true){
						$pass = false;
					}
				}
				if($pass == true){
					if(isset($onlineExams['examQuestion'][$key]['questionMark'])){
						$score += $onlineExams['examQuestion'][$key]['questionMark'];
					}else{
						$score++;
					}
				}
				unset($pass);
			}
			if(isset($onlineExams['examQuestion'][$key]['type']) AND $onlineExams['examQuestion'][$key]['type'] == "text"){
				$onlineExams['examQuestion'][$key]['ans1'] = explode(",",$onlineExams['examQuestion'][$key]['ans1']);
				if(in_array($value['answer'],$onlineExams['examQuestion'][$key]['ans1'])){
					if(isset($onlineExams['examQuestion'][$key]['questionMark'])){
						$score += $onlineExams['examQuestion'][$key]['questionMark'];
					}else{
						$score++;
					}
				}
			}
		}
		$onlineExamsGrades = onlineExamsGrades::where('examId',$id)->where('studentId',$this->data['users']->id)->first();
		$onlineExamsGrades->examId = Input::get('id') ;
		$onlineExamsGrades->studentId = $this->data['users']->id ;
		$onlineExamsGrades->examQuestionsAnswers = json_encode($answers) ;
		$onlineExamsGrades->examGrade = $score ;
		$onlineExamsGrades->examDate = time() ;
		$onlineExamsGrades->save();

		if($onlineExams['ExamShowGrade'] == 1){
			if($onlineExams['examDegreeSuccess'] != "0"){
				if($onlineExams['examDegreeSuccess'] <= $score){
					$score .= " - Succeeded";
				}else{
					$score .= " - Failed";
				}
			}
			$toReturn['grade'] = $score;
		}
		$toReturn['finish'] = true;
		return json_encode($toReturn);
	}

	function export($id,$type){
		if($this->data['users']->role != "admin") exit;
		if($type == "excel"){
			$classArray = array();
			$classes = classes::get();
			foreach ($classes as $class) {
				$classArray[$class->id] = $class->className;
			}

			$data = array(1 => array ('Student Roll','Full Name','Date took','Exam Grade'));
			$grades = \DB::table('onlineExamsGrades')
					->where('examId',$id)
					->leftJoin('users', 'users.id', '=', 'onlineExamsGrades.studentId')
					->select('onlineExamsGrades.id as id',
					'onlineExamsGrades.examGrade as examGrade',
					'onlineExamsGrades.examDate as examDate',
					'users.fullName as fullName',
					'users.id as studentId',
					'users.studentRollId as studentRollId')
					->get();
			foreach ($grades as $value) {
				$data[] = array ($value->studentRollId,$value->fullName,date("m/d/Y",$value->examDate) , $value->examGrade );
			}

			$xls = new Excel_XML('UTF-8', false, 'Exam grades Sheet');
			$xls->addArray($data);
			$xls->generateXML('Exam grades Sheet');
		}elseif ($type == "pdf") {
			$classArray = array();
			$classes = classes::get();
			foreach ($classes as $class) {
				$classArray[$class->id] = $class->className;
			}

			$header = array ('Student Roll','Full Name','Date took','Exam Grade');
			$data = array();
			$grades = \DB::table('onlineExamsGrades')
					->where('examId',$id)
					->leftJoin('users', 'users.id', '=', 'onlineExamsGrades.studentId')
					->select('onlineExamsGrades.id as id',
					'onlineExamsGrades.examGrade as examGrade',
					'onlineExamsGrades.examDate as examDate',
					'users.fullName as fullName',
					'users.id as studentId',
					'users.studentRollId as studentRollId')
					->get();
			foreach ($grades as $value) {
				$data[] = array ($value->studentRollId,$value->fullName,date("m/d/y",$value->examDate) , $value->examGrade );
			}

			$pdf = new FPDF();
			$pdf->SetFont('Arial','',10);
			$pdf->AddPage();
			// Header
			foreach($header as $col)
				$pdf->Cell(60,7,$col,1);
			$pdf->Ln();
			// Data
			foreach($data as $row)
			{
				foreach($row as $col)
					$pdf->Cell(60,6,$col,1);
				$pdf->Ln();
			}
			$pdf->Output();
		}
		exit;
	}
}
