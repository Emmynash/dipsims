<?php

class ApiController extends \BaseController {

// 	var $data = array();
// 	var $panelInit ;
// 	var $layout = 'dashboard';

    public function students (){
        
		$toReturn = array();
		$toReturn['students'] = User::where('role','student')->where('activated','1')->orderBy('id','DESC')->get()->toArray();
		
		return json_encode($toReturn);
		// ('Access-Control-Allow-Origin', 'https://www.domain.com')->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
	}
    
    public function teachers (){
            
            $toReturn = array();
        	$toReturn['teachers'] = User::where('role','teacher')->where('activated','1')->orderBy('id','DESC')->get()->toArray();
        	
        	return json_encode($toReturn);
    }
    public function messages (){
            
	        $toReturn = array();
			$toReturn['messagesList'] = \DB::table('messagesList')->select('messagesList.id as id','messagesList.lastMessageDate as lastMessageDate','messagesList.lastMessage as lastMessage','messagesList.messageStatus as messageStatus')->get();
			// $toReturn['totalItems'] = messagesList::where('userId',$this->data['users']->id)->count();
			
        	return json_encode($toReturn);
    }
    public function parents (){
        $toReturn = array();
        $toReturn['parents'] = User::where('role','parent')->where('activated','1')->orderBy('id','DESC')->get()->toArray();
		// $toReturn['totalItems'] = User::where('role','parent')->where('activated','1')->count();
        return json_encode($toReturn);
    }
    public function exams (){
        $toReturn = array();
    	$toReturn['exams'] = examsList::where('examAcYear',$this->panelInit->selectAcYear)->get()->toArray();

		if($this->data['users']->role == "teacher"){
			$toReturn['classes'] = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->where('classTeacher','LIKE','%"'.$this->data['users']->id.'"%')->get()->toArray();
		}else{
			$toReturn['classes'] = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get()->toArray();
		}

		$toReturn['userRole'] = $this->data['users']->role;
	
		return json_encode($toReturn);
        
    }
    public function subjects (){
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
		// $toReturn['teachers'] = User::where('role','teacher')->get()->toArray();
        return json_encode($toReturn);
    }
    public function classes (){
       
		$toReturn = array();
		$teachers = User::where('role','teacher')->get()->toArray();
		$toReturn['dormitory'] =  dormitories::get()->toArray();

		$toReturn['subject'] = array();
		$subjects =  subject::get();
		foreach ($subjects as $value) {
		    $toReturn['subject'][$value->id] = $value->subjectTitle;
		}

		$toReturn['classes'] = array();
		
		$classes = \DB::table('classes')
					->leftJoin('dormitories', 'dormitories.id', '=', 'classes.dormitoryId')
					->select('classes.id as id',
					'classes.className as className',
					'classes.classTeacher as classTeacher',
					'classes.classSubjects as classSubjects',
					'dormitories.id as dormitory',
					'dormitories.dormitory as dormitoryName')
				// 	->where('classAcademicYear',$this->panelInit->selectAcYear)
					->get();
					
		$toReturn['teachers'] = array();
		while (list($teacherKey, $teacherValue) = each($teachers)) {
			$toReturn['teachers'][$teacherValue['id']] = $teacherValue;
		}

		while (list($key, $class) = each($classes)) {
			$toReturn['classes'][$key] = $class;
			// print_r($toReturn['classes'][$key]['classSubjects']);
			$toReturn['classes'][$key]['classSubjects'] = 
			json_decode($toReturn['classes'][$key]['classSubjects']);
			if($toReturn['classes'][$key]['classTeacher'] != " "){
				$toReturn['classes'][$key]['classTeacher'] = json_decode($toReturn['classes'][$key]['classTeacher'],true);
				if(is_array($toReturn['classes'][$key]['classTeacher'])){
					while (list($teacherKey, $teacherID) = each($toReturn['classes'][$key]['classTeacher'])) {
						if(isset($toReturn['teachers'][$teacherID]['fullName'])){
							$toReturn['classes'][$key]['classTeacher'][$teacherKey] = $toReturn['teachers'][$teacherID]['fullName'];
						}else{
							unset($toReturn['classes'][$key]['classTeacher'][$teacherKey]) ;
						}
					}
					$toReturn['classes'][$key]['classTeacher'] = implode($toReturn['classes'][$key]['classTeacher'], ", ");
				}
			}
		}

		return json_encode($toReturn);
	}
}