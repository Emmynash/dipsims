<?php

class ParentsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('parents')){
			exit;
		}
	}

	function waitingApproval(){
		return User::where('role','parent')->where('activated','0')->orderBy('id','DESC')->get();
	}

	function approveOne($id){
		$user = User::find($id);
		$user->activated = 1;
		$user->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['approveTeacher'],$this->panelInit->language['teacherApproved'],array("user"=>$user->id));
	}

	function listAllData($page = 1){
		$toReturn = array();
		$toReturn['parents'] = User::where('role','parent')->where('activated','1')->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
		$toReturn['totalItems'] = User::where('role','parent')->where('activated','1')->count();
		return $toReturn;
	}

	function search($keyword,$page = 1){
		$toReturn = array();
		$toReturn['parents'] = User::where('role','parent')->where('activated','1')->where('fullName','like','%'.$keyword.'%')->orWhere('username','like','%'.$keyword.'%')->orWhere('email','like','%'.$keyword.'%')->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
		$toReturn['totalItems'] = User::where('role','parent')->where('activated','1')->where('fullName','like','%'.$keyword.'%')->orWhere('username','like','%'.$keyword.'%')->orWhere('email','like','%'.$keyword.'%')->count();
		return $toReturn;
	}

	public function listAll($page = 1)
	{
		return $this->listAllData($page);
	}

	public function export(){
		if($this->data['users']->role != "admin") exit;
		$data = array(1 => array ( 'Full Name','User Name','E-mail','Gender','Address','Phone No','Mobile No','birthday','Profession','password','ParentOf'));
		$student = User::where('role','parent')->get();
		foreach ($student as $value) {
			$parentOf = json_decode($value->parentOf,true);
			if(count($parentOf) > 0){
				$ids = array();
				while (list(, $value_) = each($parentOf)) {
					$ids[] = $value_['id'];
				}
				$users = User::whereIn('id',$ids)->select('username')->get();
				$ids = array();
				foreach ($users as $value_) {
					$ids[] = $value_->username;
				}
				$value->parentOf = implode(",",$ids);
			}else{
				$value->parentOf = "";
			}
			$data[] = array ($value->fullName,$value->username,$value->email,$value->gender,$value->address,$value->phoneNo,$value->mobileNo,date('m/d/Y',$value->birthday),$value->parentProfession,"",$value->parentOf);
		}

		$xls = new Excel_XML('UTF-8', false, 'Parents Sheet');
		$xls->addArray($data);
		$xls->generateXML('Parents-Sheet');
		exit;
	}

	public function exportpdf(){
		if($this->data['users']->role != "admin") exit;
		$header = array ('Full Name','User Name','E-mail','Gender','Address','Phone No','Mobile No');
		$data = array();
		$student = User::where('role','parent')->get();
		foreach ($student as $value) {
			$data[] = array ($value->fullName,$value->username ,$value->email,$value->gender,$value->address,$value->phoneNo,$value->mobileNo);
		}

		$pdf = new FPDF();
		$pdf->SetFont('Arial','',10);
		$pdf->AddPage();

		foreach($header as $col)
			$pdf->Cell(40,7,$col,1);
		$pdf->Ln();

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
							  $importItem['fullName'] = $row[1];
						  }
						  if(isset($row[2])){
							  $importItem['username'] = $row[2];
						  }
						  if(isset($row[3])){
							  $importItem['email'] = $row[3];
						  }
						  if(isset($row[4])){
							  $importItem['gender'] = $row[4];
						  }
						  if(isset($row[5])){
							  $importItem['address'] = $row[5];
						  }
						  if(isset($row[6])){
							  $importItem['phoneNo'] = $row[6];
						  }
						  if(isset($row[7])){
							  $importItem['mobileNo'] = $row[7];
						  }
						  if(isset($row[8])){
							  if($row[8] == ""){
								  $importItem['birthday'] = "";
							  }else{
								  $newsDate = explode("/", $row[8]);
    							  if(is_array($newsDate) AND count($newsDate) == 3){
    								  $newsDate = mktime(0,0,0,$newsDate['0'],$newsDate['1'],$newsDate['2']);
    								  $importItem['birthday'] = $newsDate;
    							  }
							  }
						  }
						  if(isset($row[9])){
							  $importItem['parentProfession'] = $row[9];
						  }
						  if(isset($row[10])){
							  $importItem['password'] = $row[10];
						  }
						  if(isset($row[11])){
							  $importItem['parentOf'] = $row[11];
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
					if(isset($value['password'])){
						$User->password = Hash::make($value['password']);
					}
					$User->role = "parent";
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
					if(isset($value['parentProfession'])){
						$User->parentProfession = $value['parentProfession'];
					}
					if(isset($value['parentOf']) AND $value['parentOf'] != ""){
						$value['parentOf'] = explode(",",$value['parentOf']);
						if(count($value['parentOf']) > 0){
							$studetsL = User::whereIn('username',$value['parentOf'])->select('id','fullName')->get();

							$studentsArray = array();
							foreach ($studetsL as $student) {
								$studentsArray[] = array("student"=>$student->fullName,"relation"=>"","id"=>'"'.$student->id.'"');
							}
							$value['parentOf'] = json_encode($studentsArray);
						}
						$User->parentOf = $value['parentOf'];
					}
					$User->save();
				}
				return $this->panelInit->apiOutput(true,$this->panelInit->language['Import'],$this->panelInit->language['dataImported']);
			}
		}else{
			return $this->panelInit->apiOutput(true,$this->panelInit->language['Import'],$this->panelInit->language['noDataImport']);
			exit;
		}
		exit;
	}

	public function delete($id){
		if ( $postDelete = User::where('role','parent')->where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delParent'],$this->panelInit->language['parentDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delParent'],$this->panelInit->language['parentNotExist']);
        }
	}

	public function searchStudents($student){
		$students = User::where('role','student')->where('fullName','like','%'.$student.'%')->orWhere('username','like','%'.$student.'%')->orWhere('email','like','%'.$student.'%')->get();
		$retArray = array();
		foreach ($students as $student) {
			$retArray[$student->id] = array("id"=>$student->id,"name"=>$student->fullName,"email"=>$student->email);
		}
		return json_encode($retArray);
	}

	public function create(){
		if(User::where('username',trim(Input::get('username')))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['AddParent'],$this->panelInit->language['usernameUsed']);
		}
		if(User::where('email',Input::get('email'))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['AddParent'],$this->panelInit->language['mailUsed']);
		}
		$User = new User();
		$User->username = Input::get('username');
		$User->email = Input::get('email');
		$User->fullName = Input::get('fullName');
		$User->password = Hash::make(Input::get('password'));
		$User->role = "parent";
		$User->gender = Input::get('gender');
		$User->address = Input::get('address');
		$User->phoneNo = Input::get('phoneNo');
		$User->mobileNo = Input::get('mobileNo');
		if(Input::get('birthday') != ""){
			$birthday = explode("/", Input::get('birthday'));
			$birthday = mktime(0,0,0,$birthday['0'],$birthday['1'],$birthday['2']);
			$User->birthday = $birthday;
		}
		$User->parentProfession = Input::get('parentProfession');
		$User->parentOf = json_encode(Input::get('studentInfo'));
		$User->save();

		if (Input::hasFile('photo')) {
			$fileInstance = Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['AddParent'],$this->panelInit->language['AddParent'],$User->toArray());
	}

	function fetch($id){
		$data = User::where('role','parent')->where('id',$id)->first()->toArray();
		$data['birthday'] = date('m/d/Y',$data['birthday']);
		$data['parentOf'] = json_decode($data['parentOf']);
		return json_encode($data);
	}

	function edit($id){
		if(User::where('username',trim(Input::get('username')))->where('id','<>',$id)->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['AddParent'],$this->panelInit->language['usernameUsed']);
		}
		if(User::where('email',Input::get('email'))->where('id','<>',$id)->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['AddParent'],$this->panelInit->language['mailUsed']);
		}
		$User = User::find($id);
		$User->username = Input::get('username');
		$User->email = Input::get('email');
		$User->fullName = Input::get('fullName');
		if(Input::get('password') != ""){
			$User->password = Hash::make(Input::get('password'));
		}
		$User->gender = Input::get('gender');
		$User->address = Input::get('address');
		$User->phoneNo = Input::get('phoneNo');
		$User->mobileNo = Input::get('mobileNo');
		if(Input::get('birthday') != ""){
			$birthday = explode("/", Input::get('birthday'));
			$birthday = mktime(0,0,0,$birthday['0'],$birthday['1'],$birthday['2']);
			$User->birthday = $birthday;
		}
		$User->parentProfession = Input::get('parentProfession');
		$User->parentOf = Input::get('linkedStudentsSer');
		$User->save();

		if (Input::hasFile('photo')) {
			$fileInstance = Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editParent'],$this->panelInit->language['parentModified'],$User->toArray());
	}

	function profile($id){
		$data = User::where('role','parent')->where('id',$id)->first()->toArray();
		$data['birthday'] = date('m/d/Y',$data['birthday']);
		$data['parentOf'] = json_decode($data['parentOf'],true);

		$return = array();
		$return['title'] = $data['fullName']." ".$this->panelInit->language['Profile'];

		$return['content'] = "<div class='text-center'>";

		$return['content'] .= "<img alt='".$data['fullName']."' class='user-image img-circle' style='width:70px; height:70px;' ng-src='dashboard/profileImage/".$data['id']."'>";

		$return['content'] .= "</div>";

		$return['content'] .= "<h4>".$this->panelInit->language['parentInfo']."</h4>";

		$return['content'] .= "<table class='table table-bordered'><tbody>
                          <tr>
                              <td>".$this->panelInit->language['FullName']."</td>
                              <td>".$data['fullName']."</td>
                          </tr>
                          <tr>
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
                              <td>".$this->panelInit->language['students']."</td>
                              <td>";
							  if(is_array($data['parentOf'])){
								  while (list(, $value) = each($data['parentOf'])) {
									   $return['content'] .= $value['student']."<br/>";
								  }
							  }

		$return['content'] .= "</td>
                          </tr>

                          </tbody></table>";

		return $return;
	}
}
