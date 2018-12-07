<?php

class StudentsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('students')){
			exit;
		}
	}

	function waitingApproval(){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;

		if($this->data['users']->role == "teacher"){
			$classArray = array();
			$classes = classes::where('classTeacher', 'like', '%"'.$this->data['users']->id.'"%')->get();
			foreach ($classes as $value) {
				$classArray[] = $value->id;
			}
			if(count($classArray) == 0){
				return array();
			}
			return User::where('role','student')->where('activated','0')->whereIn('studentClass',$classArray)->orderBy('id','DESC')->get();
		}
		return User::where('role','student')->where('activated','0')->orderBy('id','DESC')->get();
	}

	function approveOne($id){
		if($this->data['users']->role == "student" || $this->data['users']->role == "parent") exit;
		$user = User::find($id);
		$user->activated = 1;
		$user->save();

		if($this->data['panelInit']->settingsArray['invoiceGenStudentCreated'] == 1){
			PaymentsController::generateInvoice($user);
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['approveStudent'],$this->panelInit->language['stdApproved'],array("user"=>$user->id));
	}

	public function listAll($page = 1)
	{
		if($this->data['users']->role == "student") exit;

		$toReturn = array();
		if($this->data['users']->role == "parent" ){
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				while (list($key, $value) = each($parentOf)) {
					$studentId[] = $value['id'];
				}
			}
			if(count($studentId) > 0){
				$students = User::where('role','student')->where('activated','1')->whereIn('id', $studentId)->orderBy('studentRollId','ASC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
				$toReturn['totalItems'] = User::where('role','student')->whereIn('id', $studentId)->where('activated','1')->count();
			}else{
				$students = array();
				$toReturn['totalItems'] = 0;
			}
		}elseif($this->data['users']->role == "teacher" ){
			$classesList = array();
			$classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->where('classTeacher','LIKE','%"'.$this->data['users']->id.'"%')->get()->toArray();
			while (list(, $value) = each($classes)) {
				$classesList[] = $value['id'];
			}

			if(count($classesList) > 0){
				$students = User::where('role','student')->whereIn('studentClass',$classesList)->where('activated','1')->orderBy('studentRollId','ASC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
				$toReturn['totalItems'] = User::where('role','student')->whereIn('studentClass',$classesList)->where('activated','1')->count();
			}else{
				$students = array();
				$toReturn['totalItems'] = 0;
			}
		}else{
			$students = User::where('role','student')->where('activated','1')->orderBy('studentRollId','ASC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
			$toReturn['totalItems'] = User::where('role','student')->where('activated','1')->count();
		}

		$toReturn['classes'] = $classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		$classArray = array();
		while (list(, $value) = each($classes)) {
			$classArray[$value['id']] = $value['className'];
		}
		$toReturn['transports'] =  transportation::get()->toArray();

		$toReturn['hostel'] = array();
		$hostel = hostel::get()->toArray();
		$hostelCat = hostelCat::get()->toArray();
		$hostelMail = array();

		foreach ($hostel as $value) {
			$hostelMail[$value['id']] = $value['hostelTitle'];
		}

		foreach ($hostelCat as $value) {
			if(isset($hostelMail[$value['catTypeId']])){
				$toReturn['hostel'][$value['id']] =  $hostelMail[$value['catTypeId']] . " - " . $value['catTitle'];
			}
		}

		$toReturn['userRole'] = $this->data['users']->role;

		$toReturn['students'] = array();
		while (list(, $student) = each($students)) {
			$toReturn['students'][] = array('id'=>$student['id'],"studentRollId"=>$student['studentRollId'],"fullName"=>$student['fullName'],"username"=>$student['username'],"email"=>$student['email'],"isLeaderBoard"=>$student['isLeaderBoard'],"studentClass"=>isset($classArray[$student['studentClass']]) ? $classArray[$student['studentClass']] : "");
		}

		return $toReturn;
	}

	public function search($keyword,$page = 1){
		if($this->data['users']->role == "student") exit;

		if (strpos($keyword, '||') !== false) {
			$keyword = explode("||",$keyword);
			$searchQ = $keyword[0];
			$searchC = $keyword[1];
		}else{
			$searchQ = $keyword;
		}

		$toReturn = array();
		if($this->data['users']->role == "parent" ){
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				while (list($key, $value) = each($parentOf)) {
					$studentId[] = $value['id'];
				}
			}
			if(count($studentId) > 0){
				$students = User::where('role','student')->where('activated','1')->whereIn('id', $studentId);
				if(isset($searchQ) AND $searchQ != "" AND $searchQ != "undefined"){
					$students = $students->where(function($query) use ($searchQ){
							                 $query->where('fullName','like','%'.$searchQ.'%')->orWhere('username','like','%'.$searchQ.'%')->orWhere('email','like','%'.$searchQ.'%');
							             });
				}
				if(isset($searchC) AND $searchC != "" AND $searchC != "undefined" AND $searchC != 0){
					$students = $students->where('studentClass',$searchC);
				}
				$toReturn['totalItems'] = $students->count();
				$students = $students->orderBy('studentRollId','ASC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
			}else{
				$students = array();
				$toReturn['totalItems'] = 0;
			}
		}elseif($this->data['users']->role == "teacher" ){
			$classesList = array();
			$classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->where('classTeacher','LIKE','%"'.$this->data['users']->id.'"%')->get()->toArray();
			while (list(, $value) = each($classes)) {
				$classesList[] = $value['id'];
			}

			if(count($classesList) > 0){
				$students = User::where('role','student')->whereIn('studentClass',$classesList)->where('activated','1');
				if(isset($searchQ) AND $searchQ != "" AND $searchQ != "undefined"){
					$students = $students->where(function($query) use ($searchQ){
							                 $query->where('fullName','like','%'.$searchQ.'%')->orWhere('username','like','%'.$searchQ.'%')->orWhere('email','like','%'.$searchQ.'%');
							             });
				}
				if(isset($searchC) AND $searchC != "" AND $searchC != "undefined" AND $searchC != 0){
					$students = $students->where('studentClass',$searchC);
				}
				$toReturn['totalItems'] = $students->count();
				$students = $students->orderBy('studentRollId','ASC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
			}else{
				$students = array();
				$toReturn['totalItems'] = 0;
			}
		}else{
			$students = User::where('role','student')->where('activated','1');
			if(isset($searchQ) AND $searchQ != "" AND $searchQ != "undefined"){
				$students = $students->where(function($query) use ($searchQ){
										 $query->where('fullName','like','%'.$searchQ.'%')->orWhere('username','like','%'.$searchQ.'%')->orWhere('email','like','%'.$searchQ.'%');
									 });
			}
			if(isset($searchC) AND $searchC != "" AND $searchC != "undefined" AND $searchC != 0){
				$students = $students->where('studentClass',$searchC);
			}
			$toReturn['totalItems'] = $students->count();
			$students = $students->orderBy('studentRollId','ASC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
		}

		$toReturn['classes'] = $classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		$classArray = array();
		while (list(, $value) = each($classes)) {
			$classArray[$value['id']] = $value['className'];
		}

		$toReturn['students'] = array();
		while (list(, $student) = each($students)) {
			$toReturn['students'][] = array('id'=>$student['id'],"studentRollId"=>$student['studentRollId'],"fullName"=>$student['fullName'],"username"=>$student['username'],"email"=>$student['email'],"isLeaderBoard"=>$student['isLeaderBoard'],"studentClass"=>isset($classArray[$student['studentClass']]) ? $classArray[$student['studentClass']] : "");
		}

		return $toReturn;
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = User::where('role','student')->where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delStudent'],$this->panelInit->language['stdDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delStudent'],$this->panelInit->language['stdNotExist']);
        }
	}

	public function acYearRemove($student,$id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = studentAcademicYears::where('studentId',$student)->where('academicYearId', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delAcademicYears'],$this->panelInit->language['acYearDelSuc']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delAcademicYears'],$this->panelInit->language['acYearNotExist']);
        }
	}

	public function export(){
		if($this->data['users']->role != "admin") exit;
		$classArray = array();
		$classes = classes::get();
		foreach ($classes as $class) {
			$classArray[$class->id] = $class->className;
		}

		$data = array(1 => array ('Roll', 'Full Name','User Name','E-mail','Gender','Address','Phone No','Mobile No','birthday','Class','password','Blood Group','Genotype'));
		$student = User::where('role','student')->get();
		foreach ($student as $value) {
			$birthday = "";
			if($value->birthday != 0){
				$birthday = date('m/d/Y',$value->birthday);
			}
			$data[] = array ($value->studentRollId, $value->fullName,$value->username,$value->email,$value->gender,$value->address,$value->phoneNo,$value->mobileNo,$birthday,isset($classArray[$value->studentClass]) ? $classArray[$value->studentClass] : "","",$value->bloodGroup,$value->genotype);
		}

		$xls = new Excel_XML('UTF-8', false, 'Payments Sheet');
		$xls->addArray($data);
		$xls->generateXML('Students Sheet');
		exit;
	}

	public function exportpdf(){
		if($this->data['users']->role != "admin") exit;
		$classArray = array();
		$classes = classes::get();
		foreach ($classes as $class) {
			$classArray[$class->id] = $class->className;
		}

		$header = array ('Full Name','User Name','E-mail','Gender','Address','Mobile No','Class','Blood Group','Genotype');
		$data = array();
		$student = User::where('role','student')->get();
		foreach ($student as $value) {
			$data[] = array ($value->fullName,$value->username . "(".$value->studentRollId.")",$value->email,$value->gender,$value->address,$value->mobileNo, isset($classArray[$value->studentClass]) ? $classArray[$value->studentClass] : "",$value->bloodGroup,$value->genotype );
		}

		$pdf = new FPDF();
		$pdf->SetFont('Arial','',10);
		$pdf->AddPage();
		// Header
		foreach($header as $col)
			$pdf->Cell(40,7,$col,1);
		$pdf->Ln();
		// Data
		foreach($data as $row)
		{
			foreach($row as $col)
				$pdf->Cell(40,6,$col,1);
			$pdf->Ln();
		}
		$pdf->Output();
		exit;
	}

	public function import($type){
		if($this->data['users']->role != "admin") exit;

		if (Input::hasFile('excelcsv')) {

				$classArray = array();
				$classes = classes::get();
				foreach ($classes as $class) {
					$classArray[$class->className] = $class->id;
				}

			  if ( $_FILES['excelcsv']['tmp_name'] )
			  {
				  $data = new Spreadsheet_Excel_Reader();
				  $data->setOutputEncoding('CP1251');
				  $readExcel = $data->read( $_FILES['excelcsv']['tmp_name']);

				  if(!is_array($readExcel)){
					  $readExcel = $data->sheets[0]['cells'];
				  }

				  $first_row = true;

				  $dataImport = array("ready"=>array(),"revise"=>array());
				  foreach ($readExcel as $row)
				  {
					  if ( !$first_row )
					  {
						  $importItem = array();
						  if(isset($row[1])){
							  $importItem['studentRollId'] = $row[1];
						  }
						  if(isset($row[2])){
							  $importItem['fullName'] = $row[2];
						  }
						  if(isset($row[3])){
							  $importItem['username'] = $row[3];
						  }
						  if(isset($row[4])){
							  $importItem['email'] = $row[4];
						  }
						  if(isset($row[5])){
							  $importItem['gender'] = $row[5];
						  }
						  if(isset($row[6])){
							  $importItem['address'] = $row[6];
						  }
						  if(isset($row[7])){
							  $importItem['phoneNo'] = $row[7];
						  }
						  if(isset($row[8])){
							  $importItem['mobileNo'] = $row[8];
						  }
						  if(isset($row[9])){
							  if($row[9] == ""){
								  $importItem['birthday'] = "";
							  }else{
								  $newsDate = explode("/", $row[9]);
    							  if(is_array($newsDate) AND count($newsDate) == 3){
    								  $newsDate = mktime(0,0,0,$newsDate['0'],$newsDate['1'],$newsDate['2']);
    								  $importItem['birthday'] = $newsDate;
    							  }
							  }
						  }
						  if(isset($row[10])){
							  $importItem['class'] = $row[10];
							  $importItem['studentClass'] = (isset($classArray[$row[10]]))?$classArray[$row[10]]:'';
						  }
						  if(isset($row[11])){
							  $importItem['password'] = $row[11];
						  }
						   if(isset($row[12])){
							  $importItem['bloodGroup'] = $row[12];
						  }
						   if(isset($row[13])){
							  $importItem['genotype'] = $row[13];
						  }

						  $checkUser = User::where('username',$importItem['username'])->orWhere('email',$importItem['email']);
						  if($checkUser->count() > 0){
							  $checkUser = $checkUser->first();
							  if($checkUser->username == $importItem['username']){
								  $importItem['error'][] = "username";
							  }
							  if($checkUser->email == $importItem['email']){
								  $importItem['error'][] = "email";
							  }
							  if(!isset($classArray[$importItem['class']])){
								  $importItem['error'][] = "class";
							  }
							  $dataImport['revise'][] = $importItem;
						  }else{
							  $dataImport['ready'][] = $importItem;
						  }

					  }
					  $first_row = false;
				  }

				  return $dataImport;
			  }
		}else{
			return json_encode(array("jsTitle"=>$this->panelInit->language['Import'],"jsStatus"=>"0","jsMessage"=>$this->panelInit->language['specifyFileToImport'] ));
			exit;
		}
		exit;
	}

	public function reviewImport(){
		if($this->data['users']->role != "admin") exit;

		$classArray = array();
		$classes = classes::get();
		foreach ($classes as $class) {
			$classArray[$class->id] = $class->className;
		}

		if(input::has('importReview')){
			$importReview = input::get('importReview');
			$importReview = array_merge($importReview['ready'], $importReview['revise']);

			$dataImport = array("ready"=>array(),"revise"=>array());
			while (list(, $row) = each($importReview)) {
				unset($row['error']);
				$checkUser = User::where('username',$row['username'])->orWhere('email',$row['email']);
				if($checkUser->count() > 0){
					$checkUser = $checkUser->first();
					if($checkUser->username == $row['username']){
						$row['error'][] = "username";
					}
					if($checkUser->email == $row['email']){
						$row['error'][] = "email";
					}
				}

				if($row['studentClass'] == "" OR !isset($classArray[$row['studentClass']])){
					$row['error'][] = "class";
				}

				if(isset($row['error']) AND count($row['error']) > 0){
					$dataImport['revise'][] = $row;
				}else{
					$dataImport['ready'][] = $row;
				}
			}

			if(count($dataImport['revise']) > 0){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['Import'],$this->panelInit->language['reviseImportData'],$dataImport);
			}else{
				while (list(, $value) = each($dataImport['ready'])) {
					$User = new User();
					if(isset($value['email'])){
						$User->email = $value['email'];
					}
					if(isset($value['username'])){
						$User->username = $value['username'];
					}
					if(isset($value['fullName'])){
						$User->fullName = $value['fullName'];
					}
					if(isset($value['bloodGroup'])){
						$User->bloodGroup = $value['bloodGroup'];
					}
					if(isset($value['genotype'])){
						$User->genotype = $value['genotype'];
					}
					if(isset($value['password']) AND $value['password'] != ""){
						$User->password = Hash::make($value['password']);
					}
					$User->role = "student";
					if(isset($value['studentRollId'])){
						$User->studentRollId = $value['studentRollId'];
					}
					if(isset($value['gender'])){
						$User->gender = $value['gender'];
					}
					if(isset($value['address'])){
						$User->address = $value['address'];
					}
					if(isset($value['phoneNo'])){
						$User->phoneNo = $value['phoneNo'];
					}
					if(isset($value['mobileNo'])){
						$User->mobileNo = $value['mobileNo'];
					}
					if(isset($value['birthday'])){
						$User->birthday = $value['birthday'];
					}
					$User->studentAcademicYear = $this->panelInit->selectAcYear;
					if(isset($value['studentClass'])){
						$User->studentClass = $value['studentClass'];
					}
					$User->save();

					$studentAcademicYears = new studentAcademicYears();
					$studentAcademicYears->studentId = $User->id;
					$studentAcademicYears->academicYearId = $this->panelInit->selectAcYear;
					$studentAcademicYears->classId = $value['studentClass'];
					$studentAcademicYears->save();

				}
				return $this->panelInit->apiOutput(true,$this->panelInit->language['Import'],$this->panelInit->language['dataImported']);
			}
		}else{
			return $this->panelInit->apiOutput(true,$this->panelInit->language['Import'],$this->panelInit->language['noDataImport']);
			exit;
		}
		exit;
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		if(User::where('username',trim(Input::get('username')))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['addStudent'],$this->panelInit->language['usernameUsed']);
		}
		if(User::where('email',Input::get('email'))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['addStudent'],$this->panelInit->language['mailUsed']);
		}
		$User = new User();
		$User->email = Input::get('email');
		$User->username = Input::get('username');
		$User->fullName = Input::get('fullName');
		$User->password = Hash::make(Input::get('password'));
		$User->role = "student";
		$User->studentRollId = Input::get('studentRollId');
		$User->gender = Input::get('gender');
		$User->address = Input::get('address');
		$User->phoneNo = Input::get('phoneNo');
		$User->bloodGroup = Input::get('bloodGroup');
		$User->genotype = Input::get('genotype');
		$User->mobileNo = Input::get('mobileNo');
		$User->studentAcademicYear = $this->panelInit->selectAcYear;
		$User->studentClass = Input::get('studentClass');
		$User->transport = Input::get('transport');
		if(input::has('hostel')){
			$User->hostel = Input::get('hostel');
		}
		if(Input::get('birthday') != ""){
			$birthday = explode("/", Input::get('birthday'));
			$birthday = mktime(0,0,0,$birthday['0'],$birthday['1'],$birthday['2']);
			$User->birthday = $birthday;
		}
		// $User->isLeaderBoard = "";
		 $User->save();
	
		if (Input::hasFile('photo')) {
			$fileInstance = Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);

			$User->photo = "profile_".$User->id.".jpg";
			$User->save();
		}

		if($this->data['panelInit']->settingsArray['invoiceGenStudentCreated'] == 1){
			PaymentsController::generateInvoice($User);
		}

		$studentAcademicYears = new studentAcademicYears();
		$studentAcademicYears->studentId = $User->id;
		$studentAcademicYears->academicYearId = $this->panelInit->selectAcYear;
		$studentAcademicYears->classId = Input::get('studentClass');
		$studentAcademicYears->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addStudent'],$this->panelInit->language['studentCreatedSuccess'],$User->toArray());
	}

	function fetch($id){
		$data = User::where('role','student')->where('id',$id)->first()->toArray();
		$data['birthday'] = date('m/d/Y',$data['birthday']);

		$data['academicYear'] = array();
		$academicYear = academicYear::get();
		foreach ($academicYear as $value) {
			$data['academicYear'][$value->id] = $value->yearTitle;
		}

		$data['studentAcademicYears'] = array();
		$academicYear = studentAcademicYears::where('studentId',$id)->orderBy('id','ASC')->get();
		foreach ($academicYear as $value) {
			$data['studentAcademicYears'][] = array("id"=>$value->academicYearId,"default"=>$value->classId,"classes"=>classes::where('classAcademicYear',$value->academicYearId)->get()->toArray() );
		}

		return json_encode($data);
	}

	// function leaderboard($id){
	// 	if($this->data['users']->role != "admin") exit;

	// 	$user = User::where('id',$id)->first();
	// 	$user->isLeaderBoard = Input::get('isLeaderBoard');
	// 	$user->save();

	// 	return $this->panelInit->apiOutput(true,$this->panelInit->language['stdLeaderBoard'],$this->panelInit->language['stdNowLeaderBoard']);
	// }

	// function leaderboardRemove($id){
	// 	if($this->data['users']->role != "admin") exit;
	// 	if ( $postDelete = User::where('role','student')->where('id', $id)->where('isLeaderBoard','!=','')->first() )
 //       {
 //           User::where('role','student')->where('id', $id)->update(array('isLeaderBoard' => ''));
 //           return $this->panelInit->apiOutput(true,$this->panelInit->language['stdLeaderBoard'],$this->panelInit->language['stdLeaderRem']);
 //       }else{
 //           return $this->panelInit->apiOutput(false,$this->panelInit->language['stdLeaderBoard'],$this->panelInit->language['stdNotLeader']);
 //       }
	// }

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		if(User::where('username',trim(Input::get('username')))->where('id','<>',$id)->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editStudent'],$this->panelInit->language['usernameUsed']);
		}
		if(User::where('email',Input::get('email'))->where('id','<>',$id)->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editStudent'],$this->panelInit->language['mailUsed']);
		}
		$User = User::find($id);
		$User->email = Input::get('email');
		$User->username = Input::get('username');
		$User->fullName = Input::get('fullName');
		$User->gender = Input::get('gender');
		$User->bloodGroup = Input::get('bloodGroup');
		if(Input::get('password') != ""){
			$User->password = Hash::make(Input::get('password'));
		}
		$User->studentRollId = Input::get('studentRollId');
		$User->genotype = Input::get('genotype');
		$User->address = Input::get('address');
		$User->phoneNo = Input::get('phoneNo');
		$User->mobileNo = Input::get('mobileNo');
		$User->studentClass = Input::get('studentClass');
		$User->transport = Input::get('transport');
		if(input::has('hostel')){
			$User->hostel = Input::get('hostel');
		}
		if(Input::get('birthday') != ""){
			$birthday = explode("/", Input::get('birthday'));
			$birthday = mktime(0,0,0,$birthday['0'],$birthday['1'],$birthday['2']);
			$User->birthday = $birthday;
		}

		if (Input::hasFile('photo')) {
			$fileInstance = Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);

			$User->photo = "profile_".$User->id.".jpg";
		}
		$User->save();

		if(input::has('academicYear')){
			$studentAcademicYears = input::get('academicYear');
			$academicYear = studentAcademicYears::where('studentId',$id)->get();
			foreach ($academicYear as $value) {
				if(isset($studentAcademicYears[$value->academicYearId]) AND $studentAcademicYears[$value->academicYearId] != $value->classId){
					$studentAcademicYearsUpdate = studentAcademicYears::where('studentId',$User->id)->where('academicYearId',$value->academicYearId)->first();
					$studentAcademicYearsUpdate->classId = $studentAcademicYears[$value->academicYearId];
					$studentAcademicYearsUpdate->save();

					attendance::where('classId',$value->classId)->where('studentId',$User->id)->update(array('classId' => $studentAcademicYears[$value->academicYearId]));
					examMarks::where('classId',$value->classId)->where('studentId',$User->id)->update(array('classId' => $studentAcademicYears[$value->academicYearId]));

				}
				if($value->academicYearId == $User->studentAcademicYear){
					$User->studentClass = $studentAcademicYears[$value->academicYearId];
					$User->save();
				}
			}
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editStudent'],$this->panelInit->language['studentModified'],$User->toArray());
	}

	function marksheet($id){
		if($id == 0){
			$id = \Auth::user()->id;
		}
		$marks = array();
		$examIds = array();
		$examsList = examsList::where('examAcYear',$this->panelInit->selectAcYear)->get();
		foreach ($examsList as $exam) {
			$marks[$exam->id] = array("title"=>$exam->examTitle,"examId"=>$exam->id,"studentId"=>$id);
			$examIds[] = $exam->id;
		}

		if(count($examIds) == 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['Marksheet'],$this->panelInit->language['studentHaveNoMarks']);
			exit;
		}

		$examMarks = examMarks::where('studentId',$id)->whereIn('examId',$examIds)->get();
		if(count($examMarks) == 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['Marksheet'],$this->panelInit->language['studentHaveNoMarks']);
			exit;
		}
		$subject = subject::get();
		$gradeLevels = gradeLevels::get();

		$subjectArray = array();
		foreach ($subject as $sub) {
			$subjectArray[$sub->id] = array('subjectTitle'=>$sub->subjectTitle,'passGrade'=>$sub->passGrade,'finalGrade'=>$sub->finalGrade);
		}

		$gradeLevelsArray = array();
		foreach ($gradeLevels as $grade) {
			$gradeLevelsArray[$grade->gradeName] = array('from'=>$grade->gradeFrom,"to"=>$grade->gradeTo,"points"=>$grade->gradePoints);
		}

		foreach ($examMarks as $mark) {
			if(!isset($marks[$mark->examId]['counter'])){
				$marks[$mark->examId]['counter'] = 0;
				$marks[$mark->examId]['points'] = 0;
				$marks[$mark->examId]['totalMarks'] = 0;
			}
			$marks[$mark->examId]['counter'] ++;
			$counter = $marks[$mark->examId]['counter'];
			$marks[$mark->examId]['data'][$mark->id]['subjectName'] = $subjectArray[$mark->subjectId]['subjectTitle'];
			$marks[$mark->examId]['data'][$mark->id]['subjectId'] = $mark->subjectId;
			$marks[$mark->examId]['data'][$mark->id]['examMark'] = $mark->examMark;
			$marks[$mark->examId]['data'][$mark->id]['firstCA'] = $mark->firstCA;
			$marks[$mark->examId]['data'][$mark->id]['secondCA'] = $mark->secondCA;
			$marks[$mark->examId]['data'][$mark->id]['thirdCA'] = $mark->thirdCA;
			$marks[$mark->examId]['data'][$mark->id]['fourthCA'] = $mark->fourthCA;
			$marks[$mark->examId]['data'][$mark->id]['markComments'] = $mark->markComments;
			$marks[$mark->examId]['data'][$mark->id]['passGrade'] = $subjectArray[$mark->subjectId]['passGrade'];
			$marks[$mark->examId]['data'][$mark->id]['finalGrade'] = $subjectArray[$mark->subjectId]['finalGrade'];
			if($marks[$mark->examId]['data'][$mark->id]['passGrade'] != ""){
				if($marks[$mark->examId]['data'][$mark->id]['examMark'] + $marks[$mark->examId]['data'][$mark->id]['firstCA'] + $marks[$mark->examId]['data'][$mark->id]['secondCA'] + $marks[$mark->examId]['data'][$mark->id]['thirdCA'] + $marks[$mark->examId]['data'][$mark->id]['fourthCA'] >= $marks[$mark->examId]['data'][$mark->id]['passGrade']){
					$marks[$mark->examId]['data'][$mark->id]['examState'] = "Pass";
				}else{
					$marks[$mark->examId]['data'][$mark->id]['examState'] = "Failed";
				}
			}

			reset($gradeLevelsArray);
			$allMarks = $mark->examMark + $mark->firstCA + $mark->secondCA + $mark->thirdCA + $mark->fourthCA;
			while (list($key, $value) = each($gradeLevelsArray)) {
				if($allMarks >= $value['from'] AND $allMarks <= $value['to']){
					$marks[$mark->examId]['points'] += $value['points'];
					$marks[$mark->examId]['data'][$mark->id]['grade'] = $key;
					$marks[$mark->examId]['totalMarks'] += $allMarks;
					if($this->data['panelInit']->settingsArray['attendanceOnMarksheet'] == 1){
						$marks[$mark->examId]['totalMarks'] += $mark->attendanceMark;
					}
					break;
				}
			}
		}
		
			$marks[$mark->examId]['points'] = $allMarks;
		while (list($key, $value) = each($marks)) {
			// if(isset($value['points']) AND $value['counter']){
			// 	$marks[$key]['pointsAvg'] = $value['points'] / $value['counter'];
			// }
				// if(isset($allMarks AND $counter)){
				$marks[$key]['pointsAvg'] = $allMarks / $counter;
		}

		return $marks;
		exit;
	}

	function marksheetPDF($studentId,$exam){
		if(\Auth::user()->role == "student"){
			$studentId = \Auth::user()->id;
		}
		
		$student = User::where('id',$studentId)->first();
		$examsList = examsList::where('id',$exam)->first();
		$studentMarks = $this->marksheet($studentId);

		if(!isset($studentMarks[$exam]['data'])){
			echo $this->panelInit->language['noMarksheetAv'];
		}

		$content = "<page backtop='10mm' backbottom='10mm' backleft='10mm' backright='10mm' footer='date;heure;page'>
		<br/>
		<table cellspacing='0' style='padding: 1px; width: 100%; font-size: 11pt; margin-bottom: 200px;  '>
			
			<tr>
					<td style='width: 50%;text-align: left;'>

							<table cellspacing='0' style='width: 100%;text-align: left;'>
						        
						       <tr>
						           <td style='width: 15%;  text-align: left; margin-left: -20%;'><img style='width: 200px; height: 100px' src=".HTML::image('assets/images/New_Schools.jpg')."></td>
						           <th style='width: 57%; text-align: right; margin-left: -20%; '>".$this->data['panelInit']->settingsArray['siteTitle']."</th>
						        </tr>
						         <tr>
						        	<th style='width: 85%; text-align: right; '> ".$this->panelInit->language['Marksheet']." </th>  
						        </tr>
						        
						    </table>
				   </td>
				   <td style='width: 50%;text-align: left;'>

							<table cellspacing='0' style='width: 100%;text-align: left;'>
						        
						        <tr>
						           
						        </tr>
						        <tr>
						         
						        </tr>
						    </table>
					</td>
			       
			</tr>
			
        </table>
		<br/>
		<table cellspacing='5' style='width: 100%; font-size: 12px;'>
		        <tr>
		            <td style='width: 50%;text-align: left;'>

						<table cellspacing='0' style='width: 100%;text-align: left;'>
					        <tr>
					            
					        </tr>
					        <tr>
					            <td style='width:30%; '>".$this->panelInit->language['Marksheet']." :</td>
					            <td style='width:70%'>
					                ".$this->data['panelInit']->settingsArray['address']."<br>
					                ".$this->data['panelInit']->settingsArray['address2']."
					            </td>
					        </tr>
					        <tr>
					            <td style='width:30%; '>".$this->panelInit->language['email']." :</td>
					            <td style='width:70%'>".$this->data['panelInit']->settingsArray['systemEmail']."</td>
					        </tr>
					        <tr>
					            <td style='width:30%; '>".$this->panelInit->language['phoneNo']." :</td>
					            <td style='width:70%'>".$this->data['panelInit']->settingsArray['phoneNo']."</td>
					        </tr>
					        <tr>
					            <td style='width:30%; '>No. in class:</td>
					            <td style='width:70%'>" . User::where('role','student')->where('studentClass', $student->studentClass)->get()->count() . " </td>
					        </tr>
					        <tr>
					            <td style='width:30%; '>Position:</td>
					            <td style='width:70%'>No Position</td>
					        </tr>
					    </table>

		            </td>
		            <td style='width: 50%; color: #444444;text-align: left;'>


						<table cellspacing='0' style='width: 100%;text-align: left;'>
							<tr>
								<td style='width:30%; '>".$this->panelInit->language['student']." :</td>
								<td style='width:70%'>".$student->fullName."</td>
							</tr>
							<tr>
								<td style='width:30%; '>".$this->panelInit->language['Marksheet']." :</td>
								<td style='width:70%'>".$student->address."</td>
							</tr>
							<tr>
								<td style='width:30%; '>".$this->panelInit->language['email']." :</td>
								<td style='width:70%'>".$student->email."</td>
							</tr>
							<tr>
									<td style='width:30%; '>Student class :</td>
									<td style='width:70%'>" .classes::where('id',$student->studentClass)->first()->className. "</td>
							</tr>
							<tr>
								<td style='width:30%; '>".$this->panelInit->language['phoneNo']." :</td>
								<td style='width:70%'>".$student->phoneNo." - ".$student->mobileNo."</td>
							</tr>
						</table>


					</td>
		        </tr>
		    </table>

			<br/>
			<table cellspacing='0' style='padding: 1px; width: 100%; font-size: 11pt; '>
	            <tr>
	                <th style='width: 100%; text-align: center; '> ".$examsList->examTitle." </th>
	            </tr>
			</table>
			<br/>

            <table cellspacing='0' style='padding: 0px;margin:0px; width: 92%; border: solid 1px black; '>
                <tbody><tr>
                    <th style='width:15%;border: solid 1px #000000; padding:2px;'>".$this->panelInit->language['Subject']."</th>
                    <th style='width:6%;border: solid 1px #000000; padding:2px;'>1st  C.A</th>
                    <th style='width:6%;border: solid 1px #000000; padding:2px;'>2nd C.A</th>
                    <th style='width:6%;border: solid 1px #000000; padding:2px;'>3rd C.A</th>
                    <th style='width:6%;border: solid 1px #000000; padding:2px;'>4th C.A</th>
                    <th style='width:8%;border: solid 1px #000000; padding:2px;'>".$this->panelInit->language['mark']."</th>
                    <th style='width:7%;border: solid 1px #000000; padding:2px;'>".$this->panelInit->language['Grade']."</th>
                    <th style='width:14%;border: solid 1px #000000; padding:2px;'>".$this->panelInit->language['passGrade']."</th>
                    <th style='width:12%;border: solid 1px #000000; padding:2px;'>".$this->panelInit->language['finalGrade']."</th>
                    <th style='width:9%;border: solid 1px #000000; padding:2px;'>".$this->panelInit->language['Status']."</th>
                    <th style='width:12%;border: solid 1px #000000; padding:2px;'>".$this->panelInit->language['Comments']."</th>
                </tr>";

				foreach ($studentMarks[$exam]['data'] as $value) {
	                $content .= "<tr>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['subjectName']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['firstCA']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['secondCA']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['thirdCA']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['fourthCA']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['examMark']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['grade']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['passGrade']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['finalGrade']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['examState']."</td>
	                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['markComments']."</td>
	                </tr>";
				}
            $content .= "</tbody></table>

			<br/><br/>
			<br/><br/>
			<table cellspacing='0' style='padding: 1px; width: 100%; font-size: 10pt; '>
			<table>
	            <tr>
	                <th style='width: 100%; text-align: center; '> Total Marks : ".$studentMarks[$exam]['totalMarks']." - ".$this->panelInit->language['AveragePoints']." : ".$studentMarks[$exam]['pointsAvg']." </th>
	            </tr>
	        </table>
	        
	        <br/><br/>
	        <table>
	            <tr>
	            	<td style='text-align: right; '>
	            		
	            	</td>
	            </tr>
	       </table>
	            
	           <br/><br/>
	        <table>
	            <tr>
	            	<th>
	            		<b>Class Teacher's Remark:</b>
	            	</th>
	            </tr>
	            
	            <br/><br/>
	        </table>
	        
	        <br/><br/>
	        <table>
	            <tr>
	            	<td style='text-align: right; '>
	            		<b>____________________________________________________________________________________________ </b>
	            	</td>
	            </tr>
	       </table>
	        
	       
	        <table style='width: 50%;text-align: left;>
	        	<tr>
	        		<th style='width: 30%; text-align: left; '>
	            		<b>                                                                                 </b>
	            	</th>
	            	<th style='width: 50%; text-align: left; '>
	            		<b>Sign & Date</b>
	            	</th>
	            </tr>
	        </table>
	   
	            
	            <br/><br/>
	        <table>
	            <tr>
	            	<th>
	            		<b>Head Teacher's/Principal Remark:</b>
	            	</th>
	            </tr>
	            <br/><br/>
	        </table>
	        
	          
	        <br/><br/>
	        <table>
	            <tr>
	            	<td style='text-align: right; '>
	            		<b>____________________________________________________________________________________________ </b>
	            	</td>
	            </tr>
	       </table>
	        
	        <table style='width: 50%;text-align: left;>
	        	<tr>
	        		<th style='width: 30%; text-align: left; '>
	            		<b></b>
	            	</th>
	            	<th style='width: 50%; text-align: left; '>
	            		<b>Sign & Stamp</b>
	            	</th>
	            </tr>
			</table>

	           <br/><br/>
	        <table>
	            <tr>
	            	<th>
	            		<b></b>
	            	</th>
	            </tr>
	        </table>
	        
	            <br/><br/>
	        <table  style='text-align: right; '>
	            <tr>
	            	<th  style='text-align: right; ' >
	            		School resumes on: <b> ".$this->data['panelInit']->settingsArray['resumptionDate']." </b>
	            	</th>
	            </tr>
	        </table>
			</table>
			<br/><br/>

		</page>";

	    $html2pdf = new HTML2PDF('P','A4','en');
		$html2pdf->pdf->SetAuthor($this->data['panelInit']->settingsArray['siteTitle']);
		$html2pdf->pdf->SetTitle($this->panelInit->language['Marksheet']);
		$html2pdf->pdf->SetSubject($this->panelInit->language['Marksheet']);
	    $html2pdf->WriteHTML($content);
	    $html2pdf->Output('exemple.pdf');
	}

	function marksheetBulkPDF(){
		$users = User::where('studentClass',Input::get('classId'))->get();
		$examsList = examsList::where('id',Input::get('examId'))->first();

		$content = "";
		foreach ($users as $student) {
			$studentMarks = $this->marksheet($student->id);

			$content .= "<page backtop='10mm' backbottom='10mm' backleft='10mm' backright='10mm' footer='date;heure;page'>
			<br/>
			<table cellspacing='0' style='padding: 1px; width: 100%; font-size: 11pt;  margin-bottom: 200px; '>
			
				<tr>
					<td style='width: 50%;text-align: left;'>

							<table cellspacing='0' style='width: 100%;text-align: left;'>
						       
						        <tr>
						           <td style='width: 15%;  text-align: left; margin-left: -20%;'><img style='width: 200px; height: 100px' src=".HTML::image('assets/images/New_Schools.jpg')."></td>
						           <th style='width: 57%; text-align: right; margin-left: -20%; '>".$this->data['panelInit']->settingsArray['siteTitle']."</th>
						        </tr>
						         <tr>
						        	<th style='width: 85%; text-align: right; '> ".$this->panelInit->language['Marksheet']." </th>  
						        </tr>
						        
						    </table>
					</td>
					<td style='width: 50%;text-align: left;'>

							<table cellspacing='0' style='width: 100%;text-align: left;'>
						        
						        <tr>
						        
						        </tr>
						        <tr>
						          
						        </tr>
						    </table>
					</td>
			       
			</tr>
			
			</table>
			<br/>
			<table cellspacing='5' style='width: 100%; margin-left: 20px; font-size: 12px;'>
			        <tr>
			            <td style='width: 50%;text-align: left;'>

							<table cellspacing='0' style='width: 100%;text-align: left;'>
						        
						        <tr>
						            <td style='width:30%; '>Address :</td>
						            <td style='width:70%'>
						                ".$this->data['panelInit']->settingsArray['address']."<br>
						                ".$this->data['panelInit']->settingsArray['address2']."
						            </td>
						        </tr>
						        <tr>
						            <td style='width:30%; '>email :</td>
						            <td style='width:70%'>".$this->data['panelInit']->settingsArray['systemEmail']."</td>
						        </tr>
						        <tr>
						            <td style='width:30%; '>Phone Number :</td>
						            <td style='width:70%'>".$this->data['panelInit']->settingsArray['phoneNo']."</td>
						        </tr>
						        <tr>
						            <td style='width:30%; '>No. in class :</td>
						            <td style='width:70%'>" . User::where('role','student')->where('studentClass', $student->studentClass)->get()->count() . " </td>
						        </tr>
						        <tr>
						            <td style='width:30%; '>Position :</td>
						            <td style='width:70%'>No position</td>
						        </tr>
						    </table>

			            </td>
			            <td style='width: 50%; color: #444444;text-align: left;'>


							<table cellspacing='0' style='width: 100%;text-align: left;'>
								<tr>
									<td style='width:30%; '>Student :</td>
									<td style='width:70%'>".$student->fullName."</td>
								</tr>
								<tr>
									<td style='width:30%; '>Address :</td>
									<td style='width:70%'>".$student->address."</td>
								</tr>
								<tr>
									<td style='width:30%; '>E-mail address :</td>
									<td style='width:70%'>".$student->email."</td>
								</tr>
								<tr>
									<td style='width:30%; '>Student class :</td>
									<td style='width:70%'>" .classes::where('id',$student->studentClass)->first()->className. "</td>
								</tr>
								<tr>
									<td style='width:30%; '>Phone number :</td>
									<td style='width:70%'>".$student->phoneNo." - ".$student->mobileNo."</td>
								</tr>
							</table>


						</td>
			        </tr>
			    </table>

				<br/>
				<table cellspacing='0' style='padding: 1px; width: 100%; font-size: 11pt; '>
		            <tr>
		                <th style='width: 100%; text-align: center; '> ".$examsList->examTitle." </th>
		            </tr>
				</table>
				<br/>";

				if(!isset($studentMarks[Input::get('examId')]['data'])){
					$content .= $this->panelInit->language['noMarksheetAv'];
				}else{

		            $content .= "<table cellspacing='0' style='padding: 0px;margin:0px; width: 92%; border: solid 1px black; '>
		                <tbody><tr>
		                    <th style='width:15%;border: solid 1px #000000; padding:2px;'>Subject</th>
		                    <th style='width:6%;border: solid 1px #000000; padding:2px;'>1st  C.A</th>
                    		<th style='width:6%;border: solid 1px #000000; padding:2px;'>2nd C.A</th>
                    		<th style='width:6%;border: solid 1px #000000; padding:2px;'>3rd C.A</th>
                    		<th style='width:6%;border: solid 1px #000000; padding:2px;'>4th C.A</th>
		                    <th style='width:8%;border: solid 1px #000000; padding:2px;'>Exams Mark</th>
		                    <th style='width:7%;border: solid 1px #000000; padding:2px;'>Grade</th>
							<th style='width:14%;border: solid 1px #000000; padding:2px;'>Pass grade</th>
		                    <th style='width:12%;border: solid 1px #000000; padding:2px;'>Final grade</th>
		                    <th style='width:9%;border: solid 1px #000000; padding:2px;'>Status</th>
		                    <th style='width:12%;border: solid 1px #000000; padding:2px;'>Comments</th>
		                </tr>";

						foreach ($studentMarks[Input::get('examId')]['data'] as $value) {
			                $content .= "<tr>
			                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['subjectName']."</td>
			                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['firstCA']."</td>
	                    		<td style='border: solid 1px #000000;padding:2px;'>".@$value['secondCA']."</td>
	                    		<td style='border: solid 1px #000000;padding:2px;'>".@$value['thirdCA']."</td>
	                    		<td style='border: solid 1px #000000;padding:2px;'>".@$value['fourthCA']."</td>
			                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['examMark']."</td>
			                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['grade']."</td>
			                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['passGrade']."</td>
			                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['finalGrade']."</td>
			                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['examState']."</td>
			                    <td style='border: solid 1px #000000;padding:2px;'>".@$value['markComments']."</td>
			                </tr>";
						}
		            $content .= "</tbody></table>";
					$content .= "<br/><br/>
					<table cellspacing='0' style='padding: 1px; width: 100%; font-size: 10pt; '>
			            <tr>
			                <th style='width: 100%; text-align: center; '> Total marks : ".$studentMarks[Input::get('examId')]['totalMarks']." - Average points : ".$studentMarks[Input::get('examId')]['pointsAvg']." </th>
			            </tr>
			            
			           <br/><br/>
	        		<table>
	            		<tr>
	            			<td style='text-align: right; '>
	            			</td>
	            		</tr>
	    			</table>
			          <br/><br/>
			          <table>
						    <tr>
				            	<th>
				            		<b>Class Teacher's Remark:</b>
				            	</th>
				            </tr>
				            <tr></tr>
				       </table>
				       
				       <br/><br/>
				        <table>
				            <tr>
				            	<td style='text-align: right; '>
				            			<b>____________________________________________________________________________________________ </b>
				            	</td>
				            </tr>
				         </table>
				       
				    
				        <table style='width: 50%;text-align: left;>
				        	<tr>
				        		<th style='width: 30%; text-align: left; '>
				            		<b>                                                                                 </b>
				            	</th>
				            	<th style='width: 50%; text-align: left; '>
				            		<b>Sign & Date</b>
				            	</th>
				            </tr>
	        			</table>
	   
				            
				           <br/><br/>
				       <table>
				            <tr>
				            	<th>
				            		<b>Head Teacher's/Principal Remark:</b>
				            	</th>
				            </tr>
				            <tr></tr>
				        </table>
				        
				        <br/><br/>
				        <table>
				            <tr>
				            	<td style='text-align: right; '>
				            		<b>____________________________________________________________________________________________ </b>
				            	</td>
				            </tr>
				         </table>
				         
				        
				        <table style='width: 50%;text-align: left;>
				        	<tr>
				        		<th style='width: 30%; text-align: left; '>
				            		<b>                                                                                 </b>
				            	</th>
				            	<th style='width: 50%; text-align: left; '>
				            		<b>Sign & Stamp</b>
				            	</th>
				            </tr>
	        			</table>
				            
				        <br/><br/>
				        <table>
				            <tr>
				            	<td style='text-align: right; '>
				            		<b></b>
				            	</td>
				            </tr>
				         </table>
				         
				          <br/><br/>
				        <table>
				            <tr>
				            	<td style='text-align: right; '>
				            		School resumes on: <b> ".$this->data['panelInit']->settingsArray['resumptionDate']." </b>
				            	</td>
				            </tr>
				         </table>
				         
					</table>
					<br/><br/>";
				}



			$content .= "</page>";
		}

		$html2pdf = new HTML2PDF('P','A4','en');
		$html2pdf->pdf->SetAuthor($this->data['panelInit']->settingsArray['siteTitle']);
		$html2pdf->pdf->SetTitle($this->panelInit->language['Marksheet']);
		$html2pdf->pdf->SetSubject($this->panelInit->language['Marksheet']);
	    $html2pdf->WriteHTML($content);
	    $html2pdf->Output('exemple.pdf');

		exit;
	}

	function attendance($id){
		$toReturn = array();
		$toReturn['attendanceModel'] = $this->data['panelInit']->settingsArray['attendanceModel'];
		$toReturn['attendance'] = attendance::where('studentId',$id)->get()->toArray();

		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
			$subjects = subject::get();
			$toReturn['subjects'] = array();
			foreach ($subjects as $subject) {
				$toReturn['subjects'][$subject->id] = $subject->subjectTitle ;
			}
		}
		return $toReturn;
	}

	function profile($id){
		$data = User::where('role','student')->where('id',$id)->first()->toArray();
		$data['birthday'] = date('m/d/Y',$data['birthday']);

		if($data['studentClass'] != "" AND $data['studentClass'] != "0"){
			$class = classes::where('id',$data['studentClass'])->first();
		}

		$parents = User::where('parentOf','like','%"'.$id.'"%')->orWhere('parentOf','like','%:'.$id.'}%')->get();

		$return = array();
		$return['title'] = $data['fullName']." ".$this->panelInit->language['Profile'];

		$return['content'] = "<div class='text-center'>";

		$return['content'] .= "<img alt='".$data['fullName']."' class='user-image img-circle' style='width:70px; height:70px;' ng-src='dashboard/profileImage/".$data['id']."'>";

		$return['content'] .= "</div>";

		$return['content'] .= "<h4>".$this->panelInit->language['studentInfo']."</h4>";

		$return['content'] .= "<table class='table table-bordered'><tbody>
                          <tr>
                              <td>".$this->panelInit->language['FullName']."</td>
                              <td>".$data['fullName']."</td>
                          </tr>
                          <tr>
                              <td>".$this->panelInit->language['rollid']."</td>
                              <td>".$data['studentRollId']."</td>
                          </tr>";
                          if(isset($class)){
	                          $return['content'] .= "<tr>
	                              <td>".$this->panelInit->language['class']."</td>
	                              <td>".$class->className."</td>
	                          </tr>";
	                        }
                          $return['content'] .= "<tr>
                              <td>".$this->panelInit->language['username']."</td>
                              <td>".$data['username']."</td>
                          </tr>
                          <tr>
                              <td>".$this->panelInit->language['email']."</td>
                              <td>".$data['email']."</td>
                          </tr>
                          <tr>
                              <td>".$this->panelInit->language['Birthday']."</td>
                              <td>".$data['birthday']."</td>
                          </tr>
                          <tr>
                              <td>".$this->panelInit->language['Gender']."</td>
                              <td>".$data['gender']."</td>
                          </tr>
                          <tr>
                              <td>".$this->panelInit->language['Address']."</td>
                              <td>".$data['address']."</td>
                          </tr>
                          <tr>
                              <td>".$this->panelInit->language['phoneNo']."</td>
                              <td>".$data['phoneNo']."</td>
                          </tr>
                          <tr>
                              <td>".$this->panelInit->language['mobileNo']."</td>
                              <td>".$data['mobileNo']."</td>
                          </tr>
						  <tr>
                              <td>".$this->panelInit->language['parent']."</td>
                              <td>";
							  foreach ($parents as $value) {
								  $return['content'] .= $value->fullName . "<br/>";
							  }
		$return['content'] .= "</td>
                          </tr>

                          </tbody></table>";

		return $return;
	}
}
