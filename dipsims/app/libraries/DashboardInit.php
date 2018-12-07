<?php


class DashboardInit {

	public $panelItems;
	public $settingsArray = array();
	public $language;
	public $version = "2.2";
	public $teacherClasses = array();
	public $isRTL;
	public $languageUniversal;
	public $selectAcYear;
	public $defTheme;
	public $baseURL;

	public function __construct(){
		$this->panelItems = array(
									"dashboard"=>array("title"=>"dashboard","icon"=>"fa fa-dashboard","url"=> URL::to('#/'),"permissions"=>array('admin','teacher','student','parent') ),
									"staticContent"=>array("title"=>"staticPages","icon"=>"fa fa-file-text","activated"=>"staticpagesAct","cusPerm"=>"staticPages","permissions"=>array('admin','teacher','student','parent'),
													"children"=>array(
															"controlStatic"=>array("title"=>"controlPages","url"=>URL::to('#/static'),"icon"=>"fa fa-cog","permissions"=>array('admin') )
														)
									),
									"messages"=>array("title"=>"Messages","url"=>URL::to('#/messages'),"icon"=>"fa fa-envelope","permissions"=>array('admin','teacher','student','parent') ),
									"calender"=>array("title"=>"Calender","url"=>URL::to('#/calender'),"icon"=>"fa fa-calendar","permissions"=>array('admin','teacher','student','parent') ),
									"classSchedule"=>array("title"=>"classSch","url"=>URL::to('#/classschedule'),"icon"=>"fa fa-list","cusPerm"=>"classSch","permissions"=>array('admin','teacher','student','parent') ),
									"attendance"=>array("title"=>"Attendance","icon"=>"fa fa-bar-chart","activated"=>"attendanceAct","cusPerm"=>"Attendance","permissions"=>array('admin','teacher'),
														"children"=>array(
															"controlAttendance"=>array("title"=>"Attendance","url"=>URL::to('#/attendance'),"icon"=>"fa fa-check","permissions"=>array('admin','teacher') ),
															"statsAttendance"=>array("title"=>"attendanceStats","url"=>URL::to('#/attendanceStats'),"icon"=>"fa fa-bar-chart","permissions"=>array('admin','teacher') ),
														)
									),
									// "vacation"=>array("title"=>"Vacation","url"=>URL::to('#/vacation'),"icon"=>"fa fa-coffee","activated"=>"vacationAct","permissions"=>array('teacher','student') ),
									"myAttendance"=>array("title"=>"Attendance","url"=>URL::to('#/attendanceStats'),"icon"=>"fa fa-bar-chart","activated"=>"attendanceAct","permissions"=>array('student','parent') ),
									// "staffAttendance"=>array("title"=>"staffAttendance","url"=>URL::to('#/staffAttendance'),"icon"=>"fa fa-check","activated"=>"staffAttendanceAct","cusPerm"=>"staffAttendance","permissions"=>array('admin') ),

									// "hostel"=>array("title"=>"HostelManage","icon"=>"fa fa-bar-chart","activated"=>"attendanceAct","cusPerm"=>"HostelManage","permissions"=>array('admin'),
									// 					"children"=>array(
									// 						"controlHostel"=>array("title"=>"Hostel","url"=>URL::to('#/hostel'),"icon"=>"fa fa-check","permissions"=>array('admin') ),
									// 						"hostelCat"=>array("title"=>"HostelCat","url"=>URL::to('#/hostelCat'),"icon"=>"fa fa-bar-chart","permissions"=>array('admin') ),
									// 					)
									// ),

									"library"=>array("title"=>"Library","url"=>URL::to('#/library'),"icon"=>"fa fa-folder-open","activated"=>"bookslibraryAct","cusPerm"=>"Library","permissions"=>array('admin','teacher','student','parent') ),
									"media"=>array("title"=>"mediaCenter","url"=>URL::to('#/media'),"icon"=>"fa fa-video-camera","activated"=>"mediaAct","cusPerm"=>"mediaCenter","permissions"=>array('admin','teacher','student','parent') ),

									"staff"=>array("title"=>"Staff","url"=>URL::to('#/staff'),"icon"=>"fa fa-suitcase","cusPerm"=>"teachers","permissions"=>array('admin') ),
									"students"=>array("title"=>"students","url"=>URL::to('#/students'),"icon"=>"fa fa-users","cusPerm"=>"students","permissions"=>array('admin','teacher','parent') ),
									"parents"=>array("title"=>"parents","url"=>URL::to('#/parents'),"icon"=>"fa fa-user","cusPerm"=>"parents","permissions"=>array('admin') ),

									"studentsMarksheet"=>array("title"=>"Marksheet","url"=>URL::to('#/students/marksheet'),"icon"=>"fa fa-table","permissions"=>array('student') ),

									"gradelevels"=>array("title"=>"gradeLevels","url"=>URL::to('#/gradeLevels'),"icon"=>"fa fa-check-square-o","cusPerm"=>"gradeLevels","permissions"=>array('admin') ),
									"materials"=>array("title"=>"studyMaterial","url"=>URL::to('#/materials'),"icon"=>"fa fa-book","activated"=>"materialsAct","cusPerm"=>"studyMaterial","permissions"=>array('admin','teacher','student') ),
									// "assignments"=>array("title"=>"Assignments","url"=>URL::to('#/assignments'),"icon"=>"fa fa-file-pdf-o","activated"=>"assignmentsAct","cusPerm"=>"Assignments","permissions"=>array('admin','teacher','student') ),
									"examslist"=>array("title"=>"examsList","url"=>URL::to('#/examsList'),"icon"=>"fa fa-graduation-cap","cusPerm"=>"examsList","permissions"=>array('admin','teacher','student','parent') ),
									"onlineexams"=>array("title"=>"onlineExams","url"=>URL::to('#/onlineExams'),"icon"=>"fa fa-graduation-cap","activated"=>"onlineexamsAct","cusPerm"=>"onlineExams","permissions"=>array('admin','teacher','student') ),

									"newsboard"=>array("title"=>"newsboard","url"=>URL::to('#/newsboard'),"icon"=>"fa fa-bullhorn","activated"=>"newsboardAct","cusPerm"=>"newsboard","permissions"=>array('admin','teacher','student','parent') ),
									"events"=>array("title"=>"events","url"=>URL::to('#/events'),"icon"=>"fa fa-clock-o","activated"=>"eventsAct","cusPerm"=>"events","permissions"=>array('admin','teacher','student','parent') ),

									// "accounting"=>array("title"=>"accounting","icon"=>"fa fa-bar-chart","activated"=>"attendanceAct","cusPerm"=>"accounting","permissions"=>array('admin','student','parent'),
									// 					"children"=>array(
									// 						"controlFeeTypes"=>array("title"=>"FeeTypes","url"=>URL::to('#/feeType'),"icon"=>"fa fa-money","activated"=>"paymentsAct","permissions"=>array('admin') ),
									// 						"controlFeeAllocation"=>array("title"=>"FeeAllocation","url"=>URL::to('#/feeAllocation'),"icon"=>"fa fa-money","activated"=>"paymentsAct","permissions"=>array('admin') ),
									// 						"controlPayments"=>array("title"=>"Payments","url"=>URL::to('#/payments'),"icon"=>"fa fa-money","activated"=>"paymentsAct","permissions"=>array('admin','student','parent') ),
									// 						"expenses"=>array("title"=>"Expenses","url"=>URL::to('#/expenses'),"icon"=>"fa fa-bar-chart","permissions"=>array('admin') ),
									// 					)
									// ),

									// "transportations"=>array("title"=>"Transportation","url"=>URL::to('#/transports'),"icon"=>"fa fa-bus","activated"=>"transportAct","cusPerm"=>"Transportation","permissions"=>array('admin','teacher','student','parent') ),

									"classes"=>array("title"=>"classes","icon"=>"fa fa-sitemap","activated"=>"attendanceAct","cusPerm"=>"classes","permissions"=>array('admin'),
														"children"=>array(
															"classes"=>array("title"=>"classes","url"=>URL::to('#/classes'),"icon"=>"fa fa-sitemap","permissions"=>array('admin') ),
															// "sections"=>array("title"=>"sections","url"=>URL::to('#/sections'),"icon"=>"fa fa-sitemap","permissions"=>array('admin') ),
														)
									),

									"subjects"=>array("title"=>"Subjects","url"=>URL::to('#/subjects'),"icon"=>"fa fa-book","cusPerm"=>"Subjects","permissions"=>array('admin') ),
									"reports"=>array("title"=>"Reports","url"=>URL::to('#/reports'),"icon"=>"fa fa-pie-chart","activated"=>"reportsAct","cusPerm"=>"Reports","permissions"=>array('admin') ),

									"adminTasks"=>array("title"=>"adminTasks","icon"=>"fa fa-cog","permissions"=>array('admin'),
																				"children"=>array(
																						"academicyear"=>array("title"=>"academicyears","url"=>URL::to('#/academicYear'),"icon"=>"fa fa-calendar-check-o","cusPerm"=>"academicyears","permissions"=>array('admin') ),
																						"promotion"=>array("title"=>"Promotion","url"=>URL::to('#/promotion'),"icon"=>"fa fa-arrow-up","cusPerm"=>"Promotion","permissions"=>array('admin') ),
																						"mailsms"=>array("title"=>"mailsms","url"=>URL::to('#/mailsms'),"icon"=>"fa fa-send","cusPerm"=>"mailsms","permissions"=>array('admin') ),
																						"mailsmsTemplates"=>array("title"=>"mailsmsTemplates","url"=>URL::to('#/mailsmsTemplates'),"icon"=>"fa fa-envelope-o","cusPerm"=>"mailsmsTemplates","permissions"=>array('admin') ),
																						// "polls"=>array("title"=>"Polls","url"=>URL::to('#/polls'),"icon"=>"fa fa-tasks","activated"=>"pollsAct","cusPerm"=>"Polls","permissions"=>array('admin') ),
																						// "dormitories"=>array("title"=>"Hostels","url"=>URL::to('#/dormitories'),"icon"=>"fa fa-building-o","cusPerm"=>"Dormitories","permissions"=>array('admin') ),
																						"siteSettings" => array("title"=>"generalSettings","url"=>URL::to('#/settings'),"icon"=>"fa fa-cog","cusPerm"=>"generalSettings","permissions"=>array('admin') ),
																						"languages" => array("title"=>"Languages","url"=>URL::to('#/languages'),"icon"=>"fa fa-font","cusPerm"=>"Languages","permissions"=>array('admin') ),
																						"admins"=>array("title"=>"Administrators","url"=>URL::to('#/admins'),"icon"=>"fa fa-gears","cusPerm"=>"Administrators","permissions"=>array('admin') ),
																						"terms"=>array("title"=>"schoolTerms","url"=>URL::to('#/terms'),"icon"=>"fa fa-file-text-o","cusPerm"=>"generalSettings","permissions"=>array('admin') ),
																						"logo"=>array("title"=>"logoSettings","url"=>URL::to('#/logo'),"icon"=>"fa fa fa-upload","cusPerm"=>"generalSettings","permissions"=>array('admin') ),
																					)
																				)
					);

		$settings = settings::get();
		foreach ($settings as $setting) {
			$this->settingsArray[$setting->fieldName] = $setting->fieldValue;
		}

		if($this->settingsArray['lastUpdateCheck']+86400 < time() ){
			$sb = $this->sbApi();
			if($sb == "err"){
			//	exit;
			}
			$latestUpdate = @file_get_contents("http://dipsims.com/latest");
			$latestUpdate = @json_decode($latestUpdate,true);

			$settings = settings::where('fieldName','lastUpdateCheck')->first();
			$settings->fieldValue = time();
			$settings->save();

			if(is_array($latestUpdate)){
				$settings = settings::where('fieldName','latestVersion')->first();
				$settings->fieldValue = $latestUpdate['v'];
				$settings->save();
			}
		}

		$staticPages = staticPages::where('pageActive','1')->get();
		foreach ($staticPages as $pages) {
			$this->panelItems['staticContent']['children'][md5(uniqid())] = array("title"=>$pages->pageTitle,"url"=>URL::to('#/static')."/".$pages->id,"icon"=>"fa fa-file-text","permissions"=>array('admin','teacher','student','parent') );
		}

		//Languages
		$defLang = $defLang_ = $this->settingsArray['languageDef'];
		if(isset($this->settingsArray['languageAllow']) AND $this->settingsArray['languageAllow'] == "1" AND !Auth::guest() AND \Auth::user()->defLang != 0){
			$defLang = \Auth::user()->defLang;
		}

		//Theme
		$this->defTheme = $this->settingsArray['layoutColor'];
		if(isset($this->settingsArray['layoutColorUserChange']) AND $this->settingsArray['layoutColorUserChange'] == "1" AND !Auth::guest() AND \Auth::user()->defTheme != ""){
			$this->defTheme = \Auth::user()->defTheme;
		}

		$language = languages::whereIn('id',array($defLang,1))->get();
		if(count($language) == 0){
			$language = languages::whereIn('id',array($defLang_,1))->get();
		}

		foreach ($language as $value) {
			if($value->id == 1){
				$this->language = json_decode($value->languagePhrases,true);
			}else{
				$this->languageUniversal = $value->languageUniversal;
				$this->isRTL = $value->isRTL;
				$phrases = json_decode($value->languagePhrases,true);
				while (list($key, $value) = each($phrases)) {
					$this->language[$key] = $value;
				}
			}
		}

		//Selected academicYear
		if (Session::has('selectAcYear')){
			$this->selectAcYear = Session::get('selectAcYear');
		}else{
			$currentAcademicYear = academicYear::where('isDefault','1')->first();
			$this->selectAcYear = $currentAcademicYear->id;
			Session::put('selectAcYear', $this->selectAcYear);
		}

		$this->baseURL = URL::to('/');
	}

	public static function globalXssClean()
	{
	  $sanitized = static::arrayStripTags(Input::get());
	  Input::merge($sanitized);
	}

	public static function arrayStripTags($array)
	{
	    $result = array();

	    foreach ($array as $key => $value) {
	        // Don't allow tags on key either, maybe useful for dynamic forms.
	        $key = strip_tags($key);

	        // If the value is an array, we will just recurse back into the
	        // function to keep stripping the tags out of the array,
	        // otherwise we will set the stripped value.
	        if (is_array($value)) {
	            $result[$key] = static::arrayStripTags($value);
	        } else {
	            // I am using strip_tags(), you may use htmlentities(),
	            // also I am doing trim() here, you may remove it, if you wish.
	            $result[$key] = trim(strip_tags($value));
	        }
	    }

	    return $result;
	}

	public function viewop($layout,$view,&$data,$div=""){
		if(Request::ajax()){
			if($div != ""){
				echo "DBArea('".htmlspecialchars($this->sanitize_output( View::make($view, $data) ),ENT_QUOTES)."','".$div."');";
			}else{
				echo "DBArea('".htmlspecialchars($this->sanitize_output( View::make($view, $data) ),ENT_QUOTES)."');";
			}
			exit;
		}else{
			$data['content'] = View::make($view, $data);
			$layout->with($data);
		}
	}

	function sanitize_output($buffer) {
		$search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s','/\s\s+/');
		$replace = array('>','<',' ',' ');
		$buffer = preg_replace($search, $replace, $buffer);

		return $buffer;
	}

	public static function breadcrumb($breadcrumb){
		echo "<ol class='breadcrumb'>
					<li><a class='aj' href='".URL::to('/dashboard')."'><i class='fa fa-dashboard'></i> Home</a></li>";
		$i = 0;
		while (list($key, $value) = each($breadcrumb)) {
			$i++;
			if($i == count($breadcrumb)){
				echo "<li class='active'>".$key."</li>";
			}else{
				echo "<li class='bcItem'><a class='aj' href='$value' title='$key'>$key</a></li>";
			}
		}
		echo "</ol>";
	}

	public function truncate($text, $length = 100, $ending = '...', $exact = false, $considerHtml = false) {
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen ( preg_replace ( '/<.*?>/', '', $text ) ) <= $length) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all ( '/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER );
			$total_length = strlen ( $ending );
			$open_tags = array ( );
			$truncate = '';
			foreach ( $lines as $line_matchings ) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (! empty ( $line_matchings [1] )) {
					// if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
					if (preg_match ( '/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings [1] )) {
						// do nothing
					// if tag is a closing tag (f.e. </b>)
					} else if (preg_match ( '/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings [1], $tag_matchings )) {
						// delete tag from $open_tags list
						$pos = array_search ( $tag_matchings [1], $open_tags );
						if ($pos !== false) {
							unset ( $open_tags [$pos] );
						}
						// if tag is an opening tag (f.e. <b>)
					} else if (preg_match ( '/^<\s*([^\s>!]+).*?>$/s', $line_matchings [1], $tag_matchings )) {
						// add tag to the beginning of $open_tags list
						array_unshift ( $open_tags, strtolower ( $tag_matchings [1] ) );
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings [1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen ( preg_replace ( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings [2] ) );
				if ($total_length + $content_length > $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all ( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings [2], $entities, PREG_OFFSET_CAPTURE )) {
						// calculate the real length of all entities in the legal range
						foreach ( $entities [0] as $entity ) {
							if ($entity [1] + 1 - $entities_length <= $left) {
								$left --;
								$entities_length += strlen ( $entity [0] );
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr ( $line_matchings [2], 0, $left + $entities_length );
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings [2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if ($total_length >= $length) {
					break;
				}
			}
		} else {
			if (strlen ( $text ) <= $length) {
				return $text;
			} else {
				$truncate = substr ( $text, 0, $length - strlen ( $ending ) );
			}
		}
		// if the words shouldn't be cut in the middle...
		if (! $exact) {
			// ...search the last occurance of a space...
			$spacepos = strrpos ( $truncate, ' ' );
			if (isset ( $spacepos )) {
				// ...and cut the text in this position
				$truncate = substr ( $truncate, 0, $spacepos );
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if ($considerHtml) {
			// close all unclosed html-tags
			foreach ( $open_tags as $tag ) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}

	//Work with Date & Time
	public function ttime($time,$format='d-m-Y H:i a',$timeZone = "") {
		if($timeZone == ""){
			$timeZone = \Auth::user()->timezone;
		}
		$dd = DateTime::createFromFormat($format, $time, new DateTimeZone($timeZone));
		$dd->setTimeZone(new DateTimeZone('Europe/London'));
		return $dd->getTimestamp();
	}

	public function tdate($format,$timestamp = "",$timeZone = ""){
		if($timestamp == ""){
			$timestamp = time();
		}
		if($timeZone == ""){
			$timeZone = \Auth::user()->timezone;
		}
		$date = new DateTime("@".$timestamp);
		$date->setTimezone(new DateTimeZone($timeZone));
		return $date->format($format);
	}

	public function apiOutput($success,$title=null,$messages = null,$data=null){
		$returnArray = array("status"=>"");

		if($title != null){
			$returnArray['title'] = $title;
		}

		if($messages != null){
			$returnArray['message'] = $messages;
		}

		if($data != null){
			$returnArray['data'] = $data;
		}

		if($success){
			$returnArray['status'] = 'success';
			return $returnArray;
		}else{
			$returnArray['status'] = 'failed';
			return $returnArray;
		}
	}

	public function sbApi(){
		$url = "http://solutionsbricks.com/license";
		$pco = @file_get_contents('app/storage/meta/lc');
		if($pco == false){
			return "err";
		}
		$data = array("p"=>1,"n"=>$pco,"u"=>Request::url());
		if(function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$output = curl_exec($ch);
			curl_close($ch);
		}elseif(function_exists('file_get_contents')){
			$postdata = http_build_query($data);

			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);

			$context  = stream_context_create($opts);

			$output = file_get_contents($url, false, $context);
		}else{
			$stream = fopen($url, 'r', false, stream_context_create(array(
		          'http' => array(
		              'method' => 'POST',
		              'header' => 'Content-type: application/x-www-form-urlencoded',
		              'content' => http_build_query($data)
		          )
		      )));

		      $output = stream_get_contents($stream);
		      fclose($stream);
		}
		if($output == "err"){
			@unlink('app/storage/meta/lc');
		}
		return $output;
	}

	function GetDays($sStartDate, $sEndDate,$string=false){
      $returnDates = array();
      $vacations = array();

	  if($string){
		  $aDays[] = '"'.$sStartDate.'"';
	  }else{
		  $aDays[] = $sStartDate;
	  }

      $StartDateArray = strptime($sStartDate, '%m/%d/%Y');
      $EndDateArray = strptime($sEndDate, '%m/%d/%Y');

      $sStartDate = gmdate("Y-m-d", mktime(0, 0, 0, $StartDateArray['tm_mon']+1, $StartDateArray['tm_mday'], $StartDateArray['tm_year']+1900));
      $sEndDate = gmdate("Y-m-d", mktime(0, 0, 0, $EndDateArray['tm_mon']+1, $EndDateArray['tm_mday'], $EndDateArray['tm_year']+1900));

      // Start the variable off with the start date

      // Set a 'temp' variable, sCurrentDate, with
      // the start date - before beginning the loop
      $sCurrentDate = $sStartDate;

      // While the current date is less than the end date
      while($sCurrentDate < $sEndDate){
        // Add a day to the current date
        $nextDay = strtotime("+1 day", strtotime($sCurrentDate));

        $saveDate = gmdate("m/d/Y", strtotime("+1 day",strtotime($sCurrentDate) ));

        $sCurrentDate = gmdate("Y-m-d",$nextDay );

		if($string){
			$saveDate = '"'.$saveDate.'"';
		}
        $aDays[] = $saveDate;
      }

      // Once the loop has finished, return the
      // array of days.
      return $aDays;
    }
}
