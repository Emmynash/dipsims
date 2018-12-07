<?php

class reportsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('Reports')){
			exit;
		}
	}

	public function report(){
		if($this->data['users']->role != "admin") exit;
        if(Input::get('stats') == 'usersStats'){
            return $this->usersStats();
        }
        if(Input::get('stats') == 'stdAttendance'){
            return $this->stdAttendance(Input::get('data'));
        }
        if(Input::get('stats') == 'stfAttendance'){
            return $this->stfAttendance(Input::get('data'));
        }
		if(Input::get('stats') == 'stdVacation'){
            return $this->stdVacation(Input::get('data'));
        }
		if(Input::get('stats') == 'stfVacation'){
            return $this->stfVacation(Input::get('data'));
        }
		if(Input::get('stats') == 'payments'){
            return $this->reports(Input::get('data'));
        }
		if(Input::get('stats') == 'marksheetGenerationPrepare'){
            return $this->marksheetGenerationPrepare();
        }

	}

    public function usersStats(){
        $toReturn = array();
        $toReturn['admins'] = array();
        $toReturn['admins']['activated'] = User::where('role','admin')->where('activated','1')->count();
        $toReturn['admins']['inactivated'] = User::where('role','admin')->where('activated','0')->count();
        $toReturn['admins']['total'] = $toReturn['admins']['activated'] + $toReturn['admins']['inactivated'];

        $toReturn['teachers'] = array();
        $toReturn['teachers']['activated'] = User::where('role','teacher')->where('activated','1')->count();
        $toReturn['teachers']['inactivated'] = User::where('role','teacher')->where('activated','0')->count();
        $toReturn['teachers']['total'] = $toReturn['teachers']['activated'] + $toReturn['teachers']['inactivated'];

        $toReturn['students'] = array();
        $toReturn['students']['activated'] = User::where('role','student')->where('activated','1')->count();
        $toReturn['students']['inactivated'] = User::where('role','student')->where('activated','0')->count();
        $toReturn['students']['total'] = $toReturn['students']['activated'] + $toReturn['students']['inactivated'];

        $toReturn['parents'] = array();
        $toReturn['parents']['activated'] = User::where('role','parent')->where('activated','1')->count();
        $toReturn['parents']['inactivated'] = User::where('role','parent')->where('activated','0')->count();
        $toReturn['parents']['total'] = $toReturn['parents']['activated'] + $toReturn['parents']['inactivated'];

        return $toReturn;
    }

    public function preAttendaceStats(){
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
			$toReturn['classes'][$class['id']] = $class['className'] ;
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

        return $toReturn;
    }

    public function stdAttendance($data){
        $sql = "select * from attendance where ";
		$sqlArray = array();
		$toReturn = array();

		$students = array();
		$studentArray = User::where('role','student')->get();
		foreach ($studentArray as $stOne) {
			$stOne = (object) $stOne;
			$students[$stOne->id] = array('name'=>$stOne->fullName,'studentRollId'=>$stOne->studentRollId,'attendance'=>'');
		}

		$subjectsArray = subject::get();
		$subjects = array();
		foreach ($subjectsArray as $subject) {
			$subject = (object) $subject;
			$subjects[$subject->id] = $subject->subjectTitle ;
		}

		if(isset($data['classId']) AND $data['classId'] != "" ){
			$sqlArray[] = "classId='".$data['classId']."'";
		}
		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject" AND isset($data['subjectId']) AND $data['subjectId'] != ""){
			$sqlArray[] = "subjectId='".$data['subjectId']."'";
		}
		if(isset($data['status']) AND $data['status'] != "All"){
			$sqlArray[] = "status='".$data['status']."'";
		}

		if(isset($data['attendanceDayFrom']) AND $data['attendanceDayFrom'] != "" AND isset($data['attendanceDayTo']) AND $data['attendanceDayTo'] != ""){
			$days = $this->panelInit->GetDays($data['attendanceDayFrom'],$data['attendanceDayTo'],true);
			$days = implode(",",$days);
			$sqlArray[] = "date IN ($days) ";
		}

		$sql = $sql . implode(" AND ", $sqlArray);
		$attendanceArray = DB::select( DB::raw($sql) );

		foreach ($attendanceArray as $stAttendance) {
			$toReturn[$stAttendance['id']] = $stAttendance;
			if(isset($students[$stAttendance['studentId']])){
				$toReturn[$stAttendance['id']]['studentName'] = $students[$stAttendance['studentId']]['name'];
				if($stAttendance['subjectId'] != ""){
					$toReturn[$stAttendance['id']]['studentSubject'] = $subjects[$stAttendance['subjectId']];
				}
				$toReturn[$stAttendance['id']]['studentRollId'] = $students[$stAttendance['studentId']]['studentRollId'];
			}
		}

		if(isset($data['exportType']) AND $data['exportType'] == "excel"){
			$data = array(1 => array ('Date','Roll Id', 'Full Name','Subject','Status'));

			foreach ($toReturn as $value) {
				if($value['status'] == 0){
					$value['status'] = $this->panelInit->language['Absent'];
				}elseif ($value['status'] == 1) {
					$value['status'] = $this->panelInit->language['Present'];
				}elseif ($value['status'] == 2) {
					$value['status'] = $this->panelInit->language['Late'];
				}elseif ($value['status'] == 3) {
					$value['status'] = $this->panelInit->language['LateExecuse'];
				}
				$data[] = array ($value['date'], (isset($value['studentRollId'])?$value['studentRollId']:""),(isset($value['studentName'])?$value['studentName']:""),(isset($value['studentSubject'])?$value['studentSubject']:""),$value['status']);
			}

			$xls = new Excel_XML('UTF-8', false, 'Students Atendance Report');
			$xls->addArray($data);
			$xls->generateXML('Students Atendance Report');
			exit;
		}

		if(isset($data['exportType']) AND $data['exportType'] == "pdf"){
			$header = array ('Date','Roll Id', 'Full Name','Subject','Status');
			$data = array();
			foreach ($toReturn as $value) {
				$value = (object) $value;
				if($value->status == 0){
					$value->status = $this->panelInit->language['Absent'];
				}elseif ($value->status == 1) {
					$value->status = $this->panelInit->language['Present'];
				}elseif ($value->status == 2) {
					$value->status = $this->panelInit->language['Late'];
				}elseif ($value->status == 3) {
					$value->status = $this->panelInit->language['LateExecuse'];
				}
				$data[] = array ($value->date, (isset($value->studentRollId)?$value->studentRollId:""),(isset($value->studentName)?$value->studentName:""),(isset($value->studentSubject)?$value->studentSubject:""),$value->status);
			}

			$pdf = new FPDF();
			$pdf->SetFont('Arial','',10);
			$pdf->AddPage();
			// Header
			foreach($header as $col){
				$pdf->Cell(40,7,$col,1);
			}
			$pdf->Ln();
			// Data
			foreach($data as $row)
			{
				foreach($row as $col){
					$pdf->Cell(40,6,$col,1);
				}
				$pdf->Ln();
			}
			$pdf->Output();
			exit;
		}

		return $toReturn;
    }

    public function stfAttendance($data){
        $sql = "select * from attendance where ";
		$sqlArray = array();
		$toReturn = array();

		$teachers = array();
		$studentArray = User::where('role','teacher')->get();
		foreach ($studentArray as $stOne) {
			$teachers[$stOne->id] = array('name'=>$stOne->fullName,'attendance'=>'');
		}

		if(isset($data['status']) AND $data['status'] != "All"){
			$sqlArray[] = "status='".$data['status']."'";
		}


		if(isset($data['attendanceDayFrom']) AND $data['attendanceDayFrom'] != "" AND isset($data['attendanceDayTo']) AND $data['attendanceDayTo'] != ""){
			$days = $this->panelInit->GetDays($data['attendanceDayFrom'],$data['attendanceDayTo'],true);
			$days = implode(",",$days);
			$sqlArray[] = "date IN ($days) ";
		}

        $sqlArray[] = "classId = '0'";

		$sql = $sql . implode(" AND ", $sqlArray);
		$attendanceArray = DB::select( DB::raw($sql) );
		

		foreach ($attendanceArray as $stAttendance) {
			$toReturn[$stAttendance['id']] = $stAttendance;
			if(isset($teachers[$stAttendance['studentId']])){
				$toReturn[$stAttendance['id']]['studentName'] = $teachers[$stAttendance['studentId']]['name'];
			}
		}

		if(isset($data['exportType']) AND $data['exportType'] == "excel"){
			$data = array(1 => array ('Date', 'Full Name','Status'));
			foreach ($toReturn as $value) {
				if($value['status'] == 0){
					$value['status'] = $this->panelInit->language['Absent'];
				}elseif ($value['status'] == 1) {
					$value['status'] = $this->panelInit->language['Present'];
				}elseif ($value['status'] == 2) {
					$value['status'] = $this->panelInit->language['Late'];
				}elseif ($value['status'] == 3) {
					$value['status'] = $this->panelInit->language['LateExecuse'];
				}
				$data[] = array ($value['date'], $value['studentName'],$value['status']);
			}

			$xls = new Excel_XML('UTF-8', false, 'Staff Atendance Report');
			$xls->addArray($data);
			$xls->generateXML('Staff Atendance Report');
			exit;
		}

		if(isset($data['exportType']) AND $data['exportType'] == "pdf"){
			$header = array ('Date', 'Full Name','Status');
			$data = array();
			foreach ($toReturn as $value) {
				if($value['status'] == 0){
					$value['tatus'] = $this->panelInit->language['Absent'];
				}elseif ($value['status'] == 1) {
					$value['status'] = $this->panelInit->language['Present'];
				}elseif ($value['status'] == 2) {
					$value['status'] = $this->panelInit->language['Late'];
				}elseif ($value['status'] == 3) {
					$value['status'] = $this->panelInit->language['LateExecuse'];
				}
				$data[] = array ($value['date'], $value['studentName'],$value['status']);
			}

			$pdf = new FPDF();
			$pdf->SetFont('Arial','',10);
			$pdf->AddPage();
			// Header
			foreach($header as $col){
				$pdf->Cell(40,7,$col,1);
			}
			$pdf->Ln();
			// Data
			foreach($data as $row)
			{
				foreach($row as $col){
					$pdf->Cell(40,6,$col,1);
				}
				$pdf->Ln();
			}
			$pdf->Output();
			exit;
		}

		return $toReturn;
    }

	public function stdVacation($data){
		$datesList = $this->GetDays($data['fromDate'],$data['toDate']);

		if(count($datesList) > 0){
			$vacationList = \DB::table('vacation')
						->leftJoin('users', 'users.id', '=', 'vacation.userid')
						->select('vacation.id as id',
						'vacation.userid as userid',
						'vacation.vacDate as vacDate',
						'vacation.acceptedVacation as acceptedVacation',
						'users.fullName as fullName')
						->where('vacation.acYear',$this->panelInit->selectAcYear)
						->where('vacation.role','student')
						->whereIn('vacation.vacDate',$datesList['days'])
						->get();

			return $vacationList;
		}

		return array();
	}

	public function stfVacation($data){
		$datesList = $this->GetDays($data['fromDate'],$data['toDate']);

		if(count($datesList) > 0){
			$vacationList = \DB::table('vacation')
						->leftJoin('users', 'users.id', '=', 'vacation.userid')
						->select('vacation.id as id',
						'vacation.userid as userid',
						'vacation.vacDate as vacDate',
						'vacation.acceptedVacation as acceptedVacation',
						'users.fullName as fullName')
						->where('vacation.acYear',$this->panelInit->selectAcYear)
						->where('vacation.role','teacher')
						->whereIn('vacation.vacDate',$datesList['days'])
						->get();

			return $vacationList;
		}

		return array();
	}

	public function reports($data){
		$datesList = $this->GetDays($data['fromDate'],$data['toDate'],1);

		$payments = \DB::table('payments')
					->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
					->select('payments.id as id',
					'payments.paymentTitle as paymentTitle',
					'payments.paymentDescription as paymentDescription',
					'payments.paymentAmount as paymentAmount',
					'payments.paymentStatus as paymentStatus',
					'payments.paymentDate as paymentDate',
					'payments.paymentStudent as studentId',
					'users.fullName as fullName');

		if($data['status'] != "All"){
			$payments = $payments->where('paymentStatus',$data['status']);
		}
		$payments = $payments->whereIn('paymentDate',$datesList['days'])->orderBy('id','DESC')->get();

		return $payments;
	}

	public function marksheetGenerationPrepare(){
		$toReturn = array();
		$toReturn['classes'] = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		$toReturn['exams'] = examsList::where('examAcYear',$this->panelInit->selectAcYear)->get()->toArray();
		return $toReturn;
	}

	function GetDays($sStartDate, $sEndDate,$include=0){
      $returnDates = array();
      $vacations = array();

      $aDays[] = $sStartDate;

      $StartDateArray = strptime($sStartDate, '%m/%d/%Y');
      $EndDateArray = strptime($sEndDate, '%m/%d/%Y');

      $sStartDate = gmdate("Y-m-d", mktime(0, 0, 0, $StartDateArray['tm_mon']+1, $StartDateArray['tm_mday'], $StartDateArray['tm_year']+1900));
      $sEndDate = gmdate("Y-m-d", mktime(0, 0, 0, $EndDateArray['tm_mon']+1, $EndDateArray['tm_mday'], $EndDateArray['tm_year']+1900));

      // Start the variable off with the start date

      // Set a 'temp' variable, sCurrentDate, with
      // the start date - before beginning the loop
      $sCurrentDate = $sStartDate;

	  if($include == 0){
	      $daysWeekOff = json_decode($this->panelInit->settingsArray['daysWeekOff'],true);
	      $officialVacationDay = json_decode($this->panelInit->settingsArray['officialVacationDay'],true);
	  }
      // While the current date is less than the end date
      while($sCurrentDate < $sEndDate){
        // Add a day to the current date
        $nextDay = strtotime("+1 day", strtotime($sCurrentDate));

		if($include == 0){
	        if(in_array(date('N',$nextDay),$daysWeekOff)){
	            $sCurrentDate = gmdate("Y-m-d",$nextDay );
	            continue;
	        }
		}

        $saveDate = gmdate("m/d/Y", strtotime("+1 day",strtotime($sCurrentDate) ));

		if($include == 0){
	        if(in_array($saveDate,$officialVacationDay)){
	            $sCurrentDate = gmdate("Y-m-d",$nextDay );
	            $vacations[] = gmdate("m/d/Y", strtotime("+1 day",strtotime($sCurrentDate) ));
	            continue;
	        }
		}

        $sCurrentDate = gmdate("Y-m-d",$nextDay );

        $aDays[] = $saveDate;
      }

      // Once the loop has finished, return the
      // array of days.
      $returnDates['days'] = $aDays;
      $returnDates['vacations'] = $vacations;
      return $returnDates;
    }

}
