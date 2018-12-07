<?php

class AttendanceController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('Attendance')){
			exit;
		}
	}

	public function listAll()
	{
		if($this->data['users']->role != "admin" AND $this->data['users']->role != "teacher") exit;
		$toReturn = array();
		$toReturn['attendanceModel'] = $this->data['panelInit']->settingsArray['attendanceModel'];

		if($this->data['users']->role == "teacher"){
			$toReturn['classes'] = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->where('classTeacher','LIKE','%"'.$this->data['users']->id.'"%')->get()->toArray();
		}else{
			$toReturn['classes'] = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		}

		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
	}

	public function listAttendance(){
		if($this->data['users']->role != "admin" AND $this->data['users']->role != "teacher") exit;
		$toReturn = array();
		$toReturn['class'] = classes::where('id',Input::get('classId'))->first()->toArray();
		if(Input::get('subjectId')){
			$toReturn['subject'] = subject::where('id',Input::get('subjectId'))->first()->toArray();
		}

		$toReturn['students'] = array();
		$studentArray = User::where('role','student')->where('studentClass',Input::get('classId'))->get();
		foreach ($studentArray as $stOne) {
			$toReturn['students'][$stOne->id] = array('name'=>$stOne->fullName,'studentRollId'=>$stOne->studentRollId,'attendance'=>'');
		}
		$vacationList = vacation::where('vacDate',Input::get('attendanceDay'))->where('acYear',$this->panelInit->selectAcYear)->where('role','student')->get();

		if(Input::get('subjectId')){
			$attendanceArray = attendance::where('classId',Input::get('classId'))->where('subjectId',Input::get('subjectId'))->where('date',Input::get('attendanceDay'))->get();
		}else{
			$attendanceArray = attendance::where('classId',Input::get('classId'))->where('date',Input::get('attendanceDay'))->get();
		}
		foreach ($attendanceArray as $stAttendance) {
			if(isset($toReturn['students'][$stAttendance->studentId])){
				$toReturn['students'][$stAttendance->studentId]['attendance'] = $stAttendance->status;
			}
		}
		foreach ($vacationList as $vacation) {
			if(isset($toReturn['students'][$vacation->userid])){
				$toReturn['students'][$vacation->userid]['vacation'] = true;
				$toReturn['students'][$vacation->userid]['vacationStat'] = $vacation->acceptedVacation;
			}
		}

		return json_encode($toReturn);
	}

	public function saveAttendance(){
		if($this->data['users']->role != "admin" AND $this->data['users']->role != "teacher") exit;
		$attendanceList = array();
		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
			$attendanceArray = attendance::where('classId',Input::get('classId'))->where('subjectId',Input::get('subject'))->where('date',Input::get('attendanceDay'))->get();
		}else{
			$attendanceArray = attendance::where('classId',Input::get('classId'))->where('date',Input::get('attendanceDay'))->get();
		}
		foreach ($attendanceArray as $stAttendance) {
			$attendanceList[$stAttendance->studentId] = $stAttendance->status;
		}

		$vacationArray = array();
		$vacationList = vacation::where('vacDate',Input::get('attendanceDay'))->where('acYear',$this->panelInit->selectAcYear)->where('role','student')->get();
		foreach ($vacationList as $vacation) {
			$vacationArray[$vacation->userid] = $vacation->id;
		}

		if($this->panelInit->settingsArray['absentNotif'] == "mail" || $this->panelInit->settingsArray['absentNotif'] == "mailsms"){
			$mail = true;
		}
		if($this->panelInit->settingsArray['absentNotif'] == "sms" || $this->panelInit->settingsArray['absentNotif'] == "mailsms"){
			$sms = true;
		}
		if(isset($mail) || isset($sms)){
			$mailTemplate = mailsmsTemplates::where('templateTitle','Student Absent')->first();
		}

		$stAttendance = Input::get('stAttendance');
		while (list($key, $value) = each($stAttendance)) {
			if(isset($vacationArray[$key])){
				$vacationEdit = vacation::where('id',$vacationArray[$key])->first();
				$vacationEdit->acceptedVacation = $value['vacationStat'];
				$vacationEdit->save();
				if($value['vacationStat'] == 1){
					$value['attendance'] = "9";
				}
			}
			if(isset($value['attendance']) AND strlen($value['attendance']) > 0){
				if(!isset($attendanceList[$key])){
					$attendanceN = new attendance;
					$attendanceN->classId = Input::get('classId');
					$attendanceN->date = Input::get('attendanceDay');
					$attendanceN->studentId = $key;
					$attendanceN->status = $value['attendance'];
					if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
						$attendanceN->subjectId = Input::get('subject');
					}
					$attendanceN->save();

					if($value['attendance'] != "1" AND $this->panelInit->settingsArray['absentNotif'] != "0"){
						$parents = User::where('parentOf','like','%"'.$key.'"%')->orWhere('parentOf','like','%:'.$key.'}%')->get();
						$student = User::where('id',$key)->first();

						$absentStatus = "";
						switch ($value['attendance']) {
							case '0':
								$absentStatus = $this->panelInit->language['Absent'];
								break;
							case '2':
								$absentStatus = $this->panelInit->language['Late'];
								break;
							case '3':
								$absentStatus = $this->panelInit->language['LateExecuse'];
								break;
							case '4':
								$absentStatus = $this->panelInit->language['earlyDismissal'];
								break;
							case '9':
								$absentStatus = $this->panelInit->language['acceptedVacation'];
								break;
						}
						$MailSmsHandler = new MailSmsHandler();
						foreach ($parents as $parent) {
							if(isset($mail)){
								$studentTemplate = $mailTemplate->templateMail;
								$examGradesTable = "";
								$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{parentName}","{parentEmail}","{absentDate}","{absentStatus}","{schoolTitle}");
								$replaceArray = array($student->fullName,$student->studentRollId,$student->email,$student->username,$parent->fullName,$parent->email,Input::get('attendanceDay'),$absentStatus,$this->panelInit->settingsArray['siteTitle']);
								$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
								$MailSmsHandler->mail($parent->email,$this->panelInit->language['absentReport'],$studentTemplate);
							}
							if(isset($sms) AND $parent->mobileNo != ""){
								$studentTemplate = $mailTemplate->templateSMS;
								$examGradesTable = "";
								$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{parentName}","{parentEmail}","{absentDate}","{absentStatus}","{schoolTitle}");
								$replaceArray = array($student->fullName,$student->studentRollId,$student->email,$student->username,$parent->fullName,$parent->email,Input::get('attendanceDay'),$absentStatus,$this->panelInit->settingsArray['siteTitle']);
								$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
								$MailSmsHandler->sms($parent->mobileNo,$studentTemplate);
							}
						}
					}

				}else{
					if($attendanceList[$key] != $value['attendance']){
						$attendanceN = attendance::where('studentId',$key)->where('date',Input::get('attendanceDay'))->where('classId',Input::get('classId'))->first();
						$attendanceN->status = $value['attendance'];
						if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
							$attendanceN->subjectId = Input::get('subject');
						}
						$attendanceN->save();

						if($value['attendance'] != "1" AND $this->panelInit->settingsArray['absentNotif'] != "0"){
							$parents = User::where('parentOf','like','%"'.$key.'"%')->orWhere('parentOf','like','%:'.$key.'}%')->get();
							$student = User::where('id',$key)->first();

							$absentStatus = "";
							switch ($value['attendance']) {
								case '0':
									$absentStatus = $this->panelInit->language['Absent'];
									break;
								case '2':
									$absentStatus = $this->panelInit->language['Late'];
									break;
								case '3':
									$absentStatus = $this->panelInit->language['LateExecuse'];
									break;
								case '4':
									$absentStatus = $this->panelInit->language['earlyDismissal'];
									break;
								case '9':
									$absentStatus = $this->panelInit->language['acceptedVacation'];
									break;
							}

							$MailSmsHandler = new MailSmsHandler();
							foreach ($parents as $parent) {
								if(isset($mail)){
									$studentTemplate = $mailTemplate->templateMail;
									$examGradesTable = "";
									$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{parentName}","{parentEmail}","{absentDate}","{absentStatus}","{schoolTitle}");
									$replaceArray = array($student->fullName,$student->studentRollId,$student->email,$student->username,$parent->fullName,$parent->email,Input::get('attendanceDay'),$absentStatus,$this->panelInit->settingsArray['siteTitle']);
									$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
									$MailSmsHandler->mail($parent->email,$this->panelInit->language['absentReport'],$studentTemplate);
								}
								if(isset($sms) AND $parent->mobileNo != ""){
									$studentTemplate = $mailTemplate->templateSMS;
									$examGradesTable = "";
									$searchArray = array("{studentName}","{studentRoll}","{studentEmail}","{studentUsername}","{parentName}","{parentEmail}","{absentDate}","{absentStatus}","{schoolTitle}");
									$replaceArray = array($student->fullName,$student->studentRollId,$student->email,$student->username,$parent->fullName,$parent->email,Input::get('attendanceDay'),$absentStatus,$this->panelInit->settingsArray['siteTitle']);
									$studentTemplate = str_replace($searchArray, $replaceArray, $studentTemplate);
									$MailSmsHandler->sms($parent->mobileNo,$studentTemplate);
								}
							}
						}
					}
				}
			}
		}

		return $this->panelInit->apiOutput(true,"Attendance",$this->panelInit->language['attendanceSaved'] );
	}

	public function getStats($date = ""){
		if($date == ""){
			$date = date('m/Y');
		}
		$date = explode("/", $date);

		$toReturn = array();
		$classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		$subjList = array();
		foreach ($classes as $class) {
			$class['classSubjects'] = json_decode($class['classSubjects'],true);
			if(is_array($class['classSubjects'])){
				foreach ($class['classSubjects'] as $subject) {
					$subjList[] = $subject;
				}
			}
			$toReturn['classes'][$class->id] = $class->className ;
		}

		$subjList = array_unique($subjList);
		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
			$toReturn['subjects'] = array();
			if(count($subjList) > 0){
				$subjects = subject::whereIN('id',$subjList)->get();
				foreach ($subjects as $subject) {
					$toReturn['subjects'][$subject->id] = $subject->subjectTitle ;
				}
			}
		}

		$toReturn['role'] = $this->data['users']->role;
		$toReturn['attendanceModel'] = $this->data['panelInit']->settingsArray['attendanceModel'];

		if($this->data['users']->role == "admin" || $this->data['users']->role == "teacher"){
			$attendanceArray = attendance::where('date','like',$date[0]."%")->where('date','like',"%".$date[1])->orderBy('date')->get();

			foreach ($attendanceArray as $value) {
				$dateHere = str_replace("/".$date[1], "", $value->date);
				$dateHere = str_replace($date[0]."/", "", $dateHere);
				$dateHere = preg_replace('/^0/', "", $dateHere);
				if(!isset($toReturn['attendance'][$dateHere][0])){
					$toReturn['attendance'][$dateHere][0] = 0;
					$toReturn['attendance'][$dateHere][1] = 0;
					$toReturn['attendance'][$dateHere][2] = 0;
					$toReturn['attendance'][$dateHere][3] = 0;
					$toReturn['attendance'][$dateHere][4] = 0;
					$toReturn['attendance'][$dateHere][9] = 0;
				}
				$toReturn['attendance'][$dateHere][$value->status]++;
			}

			$attendanceArrayToday = attendance::where('date',date('m/d/Y'))->get();
			if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
				foreach ($attendanceArrayToday as $value) {
					if(isset($toReturn['subjects'][$value->subjectId])){
						if(!isset($toReturn['attendanceDay'][$toReturn['subjects'][$value->subjectId]])){
							$toReturn['attendanceDay'][$toReturn['subjects'][$value->subjectId]] = 0;
						}
						$toReturn['attendanceDay'][$toReturn['subjects'][$value->subjectId]] ++;
					}
				}
			}else{
				foreach ($attendanceArrayToday as $value) {
					if(isset($toReturn['classes'][$value->classId])){
						if(!isset($toReturn['attendanceDay'][$toReturn['classes'][$value->classId]])){
							$toReturn['attendanceDay'][$toReturn['classes'][$value->classId]] = 0;
						}
						$toReturn['attendanceDay'][$toReturn['classes'][$value->classId]] ++;
					}
				}
			}
		}elseif($this->data['users']->role == "student"){
			$attendanceArray = attendance::where('studentId',$this->data['users']->id)->where('date','like',$date[0]."%")->where('date','like',"%".$date[1])->get();
			foreach ($attendanceArray as $value) {
				$toReturn['studentAttendance'][] = array("date"=>$value->date,"status"=>$value->status,"subject"=>isset($toReturn['subjects'][$value->subjectId])?$toReturn['subjects'][$value->subjectId]:"" ) ;
			}
		}elseif($this->data['users']->role == "parent"){
			if($this->data['users']->parentOf != ""){
				$parentOf = json_decode($this->data['users']->parentOf,true);
				$ids = array();
				while (list(, $value) = each($parentOf)) {
					$ids[] = $value['id'];
				}

				$studentArray = User::where('role','student')->whereIn('id',$ids)->get();
				foreach ($studentArray as $stOne) {
					$students[$stOne->id] = array('name'=>$stOne->fullName,'studentRollId'=>$stOne->studentRollId);
				}

				if(count($ids) > 0){
					$attendanceArray = attendance::whereIn('studentId',$ids)->where('date','like',$date[0]."%")->where('date','like',"%".$date[1])->get();
					foreach ($attendanceArray as $value) {
						if(!isset($toReturn['studentAttendance'][$value->studentId])){
							$toReturn['studentAttendance'][$value->studentId]['n'] = $students[$value->studentId];
							$toReturn['studentAttendance'][$value->studentId]['d'] = array();
						}
						$toReturn['studentAttendance'][$value->studentId]['d'][] = array("date"=>$value->date,"status"=>$value->status,"subject"=>$value->subjectId);
					}
				}
			}
		}
		return $toReturn;
	}

	public function search(){
		$sql = "select * from attendance where ";
		$sqlArray = array();
		$toReturn = array();

		$students = array();
		$studentArray = User::where('role','student')->get();
		foreach ($studentArray as $stOne) {
			$students[$stOne->id] = array('name'=>$stOne->fullName,'studentRollId'=>$stOne->studentRollId,'attendance'=>'');
		}

		$subjectsArray = subject::get();
		$subjects = array();
		foreach ($subjectsArray as $subject) {
			$subjects[$subject->id] = $subject->subjectTitle ;
		}

		if(Input::get('classId') AND Input::get('classId') != "" ){
			$sqlArray[] = "classId='".Input::get('classId')."'";
		}
		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject" AND Input::get('subjectId') AND Input::get('subjectId') != ""){
			$sqlArray[] = "subjectId='".Input::get('subjectId')."'";
		}
		if(Input::get('status') AND Input::get('status') != "All"){
			$sqlArray[] = "status='".Input::get('status')."'";
		}

		if(Input::get('attendanceDay') AND Input::get('attendanceDay') != ""){
			$sqlArray[] = "date='".Input::get('attendanceDay')."'";
		}

		$sql = $sql . implode(" AND ", $sqlArray);
		$attendanceArray = DB::select( DB::raw($sql) );

		foreach ($attendanceArray as $stAttendance) {
			$toReturn[$stAttendance['id']] = $stAttendance;
			if(isset($students[$stAttendance['studentId']]) AND isset($subjects[$stAttendance['subjectId']])){
				$toReturn[$stAttendance['id']]['studentName'] = $students[$stAttendance['studentId']]['name'];
				if($stAttendance['subjectId'] != ""){
					$toReturn[$stAttendance['id']]['studentSubject'] = $subjects[$stAttendance['subjectId']];
				}
				$toReturn[$stAttendance['id']]['studentRollId'] = $students[$stAttendance['studentId']]['studentRollId'];
			}
		}

		return $toReturn;
	}
}
