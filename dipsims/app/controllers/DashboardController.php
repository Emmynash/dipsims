<?php

class DashboardController extends \BaseController {

	public $data = array();
	public $panelInit ;
	public $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['User Settings'] = \URL::to('/dashboard/user');
		$this->data['users'] = \Auth::user();
	}

	public function index($method = "main")
	{
		$languages = languages::where('id',1)->first()->toArray();
		$languages['languagePhrases'] = json_decode($languages['languagePhrases'],true);

		if($this->data['users']->role == "admin" AND $this->panelInit->version != $this->panelInit->settingsArray['latestVersion']){
			$this->data['latestVersion'] = $this->panelInit->settingsArray['latestVersion'];
		}
		
		
		$this->data['role'] = $this->data['users']->role;

		$this->panelInit->viewop($this->layout,'welcome',$this->data);
	}

	public function baseUser()
	{
		return array("fullName"=>$this->data['users']->fullName,"username"=>$this->data['users']->username,"role"=>$this->data['users']->role);
	}

	public function dashboardData(){
		$toReturn = array();
		$toReturn['selectedAcYear'] = $this->panelInit->selectAcYear;
		$toReturn['language'] = $this->panelInit->language;
		$toReturn['languageUniversal'] = $this->panelInit->languageUniversal;
		$toReturn['role'] = $this->data['users']->role;

		$toReturn['stats'] = array();
		$toReturn['stats']['classes'] = classes::count();
		$toReturn['stats']['students'] = User::where('role','student')->where('activated',1)->count();
		$toReturn['stats']['teachers'] = User::where('role','teacher')->where('activated',1)->count();
		$toReturn['stats']['newMessages'] = messagesList::where('userId',$this->data['users']->id)->where('messageStatus',1)->count();

		$toReturn['messages'] = DB::select(DB::raw("SELECT messagesList.id as id,messagesList.lastMessageDate as lastMessageDate,messagesList.lastMessage as lastMessage,messagesList.messageStatus as messageStatus,users.fullName as fullName,users.id as userId FROM messagesList LEFT JOIN users ON users.id=IF(messagesList.userId = '".$this->data['users']->id."',messagesList.toId,messagesList.userId) where userId='".$this->data['users']->id."' order by id DESC limit 5" ));

		$toReturn['attendanceModel'] = $this->data['panelInit']->settingsArray['attendanceModel'];
		if($this->data['panelInit']->settingsArray['attendanceModel'] == "subject"){
			$subjects = subject::get();
			foreach ($subjects as $subject) {
				$toReturn['subjects'][$subject->id] = $subject->subjectTitle ;
			}
		}

		$date = date('m/Y');
		$date = explode("/", $date);
		if($this->data['users']->role == "student"){
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
				
				if(count($ids) > 0){
					$studentArray = User::where('role','student')->whereIn('id',$ids)->get();
					foreach ($studentArray as $stOne) {
						$students[$stOne->id] = array('name'=>$stOne->fullName,'studentRollId'=>$stOne->studentRollId);
					}

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

		$toReturn['teacherLeaderBoard'] = User::where('role','teacher')->where('isLeaderBoard','!=','')->where('isLeaderBoard','!=','0')->get()->toArray();
		$toReturn['studentLeaderBoard'] = User::where('role','student')->where('isLeaderBoard','!=','')->where('isLeaderBoard','!=','0')->get()->toArray();

		$toReturn['newsEvents'] = array();
		$newsboard = newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->orderBy('id','desc')->limit(5)->get();
		foreach ($newsboard as $event ) {
			$eventsArray['id'] =  $event->id;
			$eventsArray['title'] =  $event->newsTitle;
			$eventsArray['type'] =  "news";
	    	$eventsArray['start'] = date("F j, Y", strtotime($event->newsDate));
	    	$toReturn['newsEvents'][] = $eventsArray;
		}

		$events = events::orderBy('id','desc')->where('eventFor',$this->data['users']->role)->orWhere('eventFor','all')->limit(5)->get();
		foreach ($events as $event ) {
	    	$eventsArray['id'] =  $event->id;
			$eventsArray['title'] =  $event->eventTitle;
			$eventsArray['type'] =  "event";
		    $eventsArray['start'] = date("F j, Y", strtotime($event->eventDate));
		    $toReturn['newsEvents'][] = $eventsArray;
		}

		$toReturn['academicYear'] = academicYear::get()->toArray();

		$toReturn['baseUser'] = array("id"=>$this->data['users']->id,"fullName"=>$this->data['users']->fullName,"username"=>$this->data['users']->username);

		$polls = polls::where('pollTarget',$this->data['users']->role)->orWhere('pollTarget','all')->where('pollStatus','1')->first();
		if(count($polls) > 0){
			$toReturn['polls']['title'] = $polls->pollTitle;
			$toReturn['polls']['id'] = $polls->id;
			$toReturn['polls']['view'] = "vote";
			$userVoted = json_decode($polls->userVoted,true);
			if(is_array($userVoted) AND in_array($this->data['users']->id,$userVoted)){
				$toReturn['polls']['voted'] = true;
				$toReturn['polls']['view'] = "results";
			}
			$toReturn['polls']['items'] = json_decode($polls->pollOptions,true);
			$toReturn['polls']['totalCount'] = 0;
			if(is_array($toReturn['polls']['items']) AND count($toReturn['polls']['items']) > 0){
				while (list($key, $value) = each($toReturn['polls']['items'])) {
					if(isset($value['count'])){
						$toReturn['polls']['totalCount'] += $value['count'];
					}
					if(!isset($toReturn['polls']['items'][$key]['prec'])){
						$toReturn['polls']['items'][$key]['prec'] = 0;
					}
				}
			}

		}

		return json_encode($toReturn);
	}

	public function changeAcYear(){
		Session::put('selectAcYear', Input::get('year'));
		return "1";
	}

	public function profileImage($id){
		if(file_exists('uploads/profile/profile_'.$id.'.jpg')){
			header('Content-Type: image/jpeg');
			echo file_get_contents('uploads/profile/profile_'.$id.'.jpg');
		} elseif(file_exists('uploads/profile/profile_'.$id.'.png')){
			header('Content-Type: image/png');
			echo file_get_contents('uploads/profile/profile_'.$id.'.png');
		} elseif(file_exists('uploads/profile/profile_'.$id.'.gif')){
			header('Content-Type: image/gif');
			echo file_get_contents('uploads/profile/profile_'.$id.'.gif');
		} else {
			header('Content-Type: image/png');
			echo file_get_contents('uploads/profile/nodp.png');
		}
		exit;
	}

	public function classesList($academicYear = ""){
		$classesList = array();

		if($academicYear == ""){
			if(!Input::has('academicYear')){
				return $classesList;
			}
			$academicYear = Input::get('academicYear');
		}

		if(is_array($academicYear)){
			$classesList = classes::whereIn('classAcademicYear',$academicYear)->get()->toArray();
		}else{
			$classesList = classes::where('classAcademicYear',$academicYear)->get()->toArray();
		}

		return $classesList;
	}

	public function subjectList($classes = ""){
		$subjectList = array();
		$classesCount = 1;

		if($classes == ""){
			$classes = Input::get('classes');

			if(!Input::has('classes')){
				return $subjectList;
			}

		}

		if(is_array($classes)){
			$classes = classes::whereIn('id',$classes);
			$classesCount = count($classes);
		}else{
			$classes = classes::where('id',$classes);
		}

		$classes = $classes->get()->toArray();

		while (list(, $value) = each($classes)) {
			$value['classSubjects'] = json_decode($value['classSubjects'],true);
			if(is_array($value['classSubjects'])){
				while (list(, $value2) = each($value['classSubjects'])) {
					$subjectList[] = $value2;
				}
			}
		}

		if($classesCount == 1){
			$finalClasses = $subjectList;
		}else{
			$subjectList = array_count_values($subjectList);

			$finalClasses = array();
			while (list($key, $value) = each($subjectList)) {
				if($value == $classesCount){
					$finalClasses[] = $key;
				}
			}
		}

		if(count($finalClasses) > 0){
			if($this->data['users']->role == "teacher"){
				return subject::where('teacherId',$this->data['users']->id)->whereIn('id',$finalClasses)->get()->toArray();
			}else{
				return subject::whereIn('id',$finalClasses)->get()->toArray();
			}
		}

		return array();
	}

	public function savePolls(){
		$toReturn = array();

		$polls = polls::where('pollTarget',$this->data['users']->role)->orWhere('pollTarget','all')->where('pollStatus','1')->where('id',Input::get('id'))->first();
		if(count($polls) > 0){
			$userVoted = json_decode($polls->userVoted,true);
			if(!is_array($userVoted)){
				$userVoted = array();
			}
			if(is_array($userVoted) AND in_array($this->data['users']->id,$userVoted)){
				return json_encode(array("jsTitle"=>$this->panelInit->language['votePoll'],"jsMessage"=>$this->panelInit->language['alreadyvoted']));
				exit;
			}
			$userVoted[] = $this->data['users']->id;
			$polls->userVoted = json_encode($userVoted);


			$toReturn['polls']['items'] = json_decode($polls->pollOptions,true);
			$toReturn['polls']['totalCount'] = 0;
			while (list($key, $value) = each($toReturn['polls']['items'])) {
				if($value['title'] == Input::get('selected')){
					if(!isset($toReturn['polls']['items'][$key]['count'])) $toReturn['polls']['items'][$key]['count'] = 0;
					$toReturn['polls']['items'][$key]['count']++;
				}
				if(isset($toReturn['polls']['items'][$key]['count'])){
					$toReturn['polls']['totalCount'] += $toReturn['polls']['items'][$key]['count'];
				}
			}
			reset($toReturn['polls']['items']);
			while (list($key, $value) = each($toReturn['polls']['items'])) {
				if(isset($toReturn['polls']['items'][$key]['count'])){
					$toReturn['polls']['items'][$key]['perc'] = ($toReturn['polls']['items'][$key]['count'] * 100) / $toReturn['polls']['totalCount'];
				}
			}
			$polls->pollOptions = json_encode($toReturn['polls']['items']);
			$polls->save();

			$toReturn['polls']['title'] = $polls->pollTitle;
			$toReturn['polls']['id'] = $polls->id;
			$toReturn['polls']['view'] = "results";
			$toReturn['polls']['voted'] = true;
		}

		return $toReturn['polls'];
		exit;
	}


	public function calender()
	{
		$StartDateArray = strptime(Input::get('start'), '%Y-%m-%d');
		$sStartDateTimeStamp = mktime(0, 0, 0, $StartDateArray['tm_mon']+1, $StartDateArray['tm_mday'], $StartDateArray['tm_year']+1900);

		$EndDateArray = strptime(Input::get('end'), '%Y-%m-%d');
		$sEndDateTimeStamp = mktime(0, 0, 0, $EndDateArray['tm_mon']+1, $EndDateArray['tm_mday'], $EndDateArray['tm_year']+1900);

		$daysArray = $this->GetDays(Input::get('start'),Input::get('end'));

		$toReturn = array();

		if($this->data['users']->role == "admin"){
			$assignments = assignments::whereIn('AssignDeadLine',$daysArray)->get();
		}elseif($this->data['users']->role == "teacher"){
			$assignments = assignments::whereIn('AssignDeadLine',$daysArray)->where('teacherId',$this->data['users']->id)->get();
		}elseif($this->data['users']->role == "student"){
			$assignments = assignments::whereIn('AssignDeadLine',$daysArray)->where('classId','like','%"'.$this->data['users']->studentClass.'"%')->get();
		}
		if(isset($assignments)){
			foreach ($assignments as $event ) {
				$eventsArray['id'] =  $event->id;
				$eventsArray['title'] =  "Assignment : ".$event->AssignTitle;
			    $eventsArray['start'] = date("c", strtotime($event->AssignDeadLine));
			    $eventsArray['backgroundColor'] = 'green';
			    $eventsArray['textColor'] = '#fff';
				$eventsArray['url'] = "#assignments";
			    $eventsArray['allDay'] = true;
			    $toReturn[] = $eventsArray;
			}
		}

		$events = events::where('eventFor',$this->data['users']->role)->orWhere('eventFor','all')->whereIn('eventDate',$daysArray)->get();
		foreach ($events as $event ) {
			$eventsArray['id'] =  $event->id;
			$eventsArray['title'] =  "Event : ".$event->eventTitle;
		    $eventsArray['start'] = date("c", strtotime($event->eventDate));
		    $eventsArray['backgroundColor'] = 'blue';
			$eventsArray['url'] = "#events/".$event->id;
		    $eventsArray['textColor'] = '#fff';
		    $eventsArray['allDay'] = true;
		    $toReturn[] = $eventsArray;
		}

		$examsList = examsList::whereIn('examDate',$daysArray)->get();
		foreach ($examsList as $event ) {
			$eventsArray['id'] =  $event->id;
			$eventsArray['title'] =  "Exam : ".$event->examTitle;
		    $eventsArray['start'] = date("c", strtotime($event->examDate));
		    $eventsArray['backgroundColor'] = 'red';
			$eventsArray['url'] = "#examsList";
		    $eventsArray['textColor'] = '#fff';
		    $eventsArray['allDay'] = true;
		    $toReturn[] = $eventsArray;
		}

		$newsboard = newsboard::where('newsFor',$this->data['users']->role)->orWhere('newsFor','all')->where('creationDate','>=',$sStartDateTimeStamp)->where('creationDate','<=',$sEndDateTimeStamp)->get();
		foreach ($newsboard as $event ) {
			$eventsArray['id'] =  $event->id;
			$eventsArray['title'] =  "News : ".$event->newsTitle;
		    $eventsArray['start'] = date("c", $event->creationDate);
			$eventsArray['url'] = "#newsboard/".$event->id;
		    $eventsArray['backgroundColor'] = 'white';
		    $eventsArray['textColor'] = '#000';
		    $eventsArray['allDay'] = true;
		    $toReturn[] = $eventsArray;
		}

		if($this->data['users']->role == "admin"){
			$onlineExams = onlineExams::where('examDate','>=',$sStartDateTimeStamp)->where('ExamEndDate','<=',$sEndDateTimeStamp)->get();
		}elseif($this->data['users']->role == "teacher"){
			$onlineExams = onlineExams::where('examDate','>=',$sStartDateTimeStamp)->where('ExamEndDate','<=',$sEndDateTimeStamp)->where('examTeacher',$this->data['users']->id)->get();
		}elseif($this->data['users']->role == "student"){
			$onlineExams = onlineExams::where('examDate','>=',$sStartDateTimeStamp)->where('ExamEndDate','<=',$sEndDateTimeStamp)->where('examClass','like','%"'.$this->data['users']->studentClass.'"%')->get();
		}
		if(isset($onlineExams)){
			foreach ($onlineExams as $event ) {
				$eventsArray['id'] =  $event->id;
				$eventsArray['title'] =  "Online Exam : ".$event->examTitle;
			    $eventsArray['start'] = date("c", strtotime($event->examDate));
			    $eventsArray['backgroundColor'] = 'red';
				$eventsArray['url'] = "#onlineExams";
			    $eventsArray['textColor'] = '#000';
			    $eventsArray['allDay'] = true;
			    $toReturn[] = $eventsArray;
			}
		}

		return $toReturn;
	}

	public function image($section,$image){
		if(!file_exists("uploads/".$section."/".$image)){
			$ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
			if ($ext == "jpg" || $ext == "jpeg") {
	    	header('Content-type: image/jpg');
	    } elseif ($ext == "png") {
	      header('Content-type: image/png');
	    } elseif ($ext == "gif") {
	      header('Content-type: image/gif');
	    }
	    if($section == "profile"){
				echo file_get_contents("uploads/".$section."/user.png");
			}
			if($section == "media"){
				echo file_get_contents("uploads/".$section."/default.png");
			}
		}
		exit;
	}

	public function readNewsEvent($type,$id){
		if($type == "news"){
			return newsboard::where('id',$id)->first();
		}
		if($type == "event"){
			return events::where('id',$id)->first();
		}
	}

	function GetDays($sStartDate, $sEndDate){


      $StartDateArray = strptime($sStartDate, '%Y-%m-%d');
      $EndDateArray = strptime($sEndDate, '%Y-%m-%d');

      $sStartDate = gmdate("Y-m-d", mktime(0, 0, 0, $StartDateArray['tm_mon']+1, $StartDateArray['tm_mday'], $StartDateArray['tm_year']+1900));
      $sEndDate = gmdate("Y-m-d", mktime(0, 0, 0, $EndDateArray['tm_mon']+1, $EndDateArray['tm_mday'], $EndDateArray['tm_year']+1900));

      // Start the variable off with the start date

	  $aDays[] = $saveDate = gmdate("m/d/Y", strtotime($sStartDate) );

      // Set a 'temp' variable, sCurrentDate, with
      // the start date - before beginning the loop
      $sCurrentDate = $sStartDate;

      // While the current date is less than the end date
      while($sCurrentDate < $sEndDate){
        // Add a day to the current date
        $nextDay = strtotime("+1 day", strtotime($sCurrentDate));
        $saveDate = gmdate("m/d/Y", strtotime("+1 day",strtotime($sCurrentDate) ));
        $sCurrentDate = gmdate("Y-m-d",$nextDay );

        $aDays[] = $saveDate;
      }

      // Once the loop has finished, return the
      // array of days.
      return $aDays;
    }
}
