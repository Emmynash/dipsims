<?php

class MailSmsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('mailsms')){
			exit;
		}
	}

	public function listAll()
	{
		$return = array();
		$mailsms = mailsms::get()->toArray();
		foreach ($mailsms as $value) {
			$value['messageData'] = htmlspecialchars_decode($value['messageData'],ENT_QUOTES);
			if(strlen($value['mailTo']) > 10){
				$value['mailTo'] = "Custom users list";
			}
			$return[] = $value;
		}
		return $return;
	}

	public function create(){
		$mailsms = new mailsms();

		if(Input::get('userType') == "users"){
			$mailsms->mailTo = json_encode(Input::get('selectedUsers'));
		}else{
			$mailsms->mailTo = Input::get('userType');
		}

		$mailsms->mailType = Input::get('sendForm');

		$messageData = " ";
		if(Input::get('messageTitle') != ""){
			$messageData .= Input::get('messageTitle');
		}
		if(Input::get('messageContent') != ""){
			$messageData .= htmlspecialchars(Input::get('messageContent'),ENT_QUOTES);
		}
		$mailsms->messageData = $messageData;

		$mailsms->messageDate = date("F j, Y, g:i a");
		$mailsms->messageSender = $this->data['users']->fullName . " [ " . $this->data['users']->id . " ] ";
		$mailsms->save();

		if(Input::get('userType') == "teachers"){
			$sedList = User::where('role','teacher')->get();
		}
		if(Input::get('userType') == "students"){
			if(!Input::get('classId') || Input::get('classId') == "" || Input::get('classId') == "0"){
				$sedList = User::where('role','student')->get();
			}else{
				$sedList = User::where('role','student')->where('studentClass',Input::get('classId'))->get();
			}
		}
		if(Input::get('userType') == "parents"){
			$sedList = User::where('role','parent')->get();
		}
		if(Input::get('userType') == "users"){
			$usersList = array();
			$selectedUsers = Input::get('selectedUsers');
			foreach ($selectedUsers as $user) {
				$usersList[] = $user['id'];
			}

			$sedList = User::whereIn('id',$usersList)->get();
		}

		$SmsHandler = new MailSmsHandler();

		if(Input::get('sendForm') == "email"){
			foreach ($sedList as $user) {
				$SmsHandler->mail($user->email,Input::get('messageTitle'),Input::get('messageContent'),$user->fullName);
			}
		}

		if(Input::get('sendForm') == "sms"){
			foreach ($sedList as $user) {
				if($user->mobileNo != ""){
					$SmsHandler->sms($user->mobileNo,strip_tags(Input::get('messageContent')));
				}
			}
		}

		return $this->listAll();
	}

	public function settings(){
		$toReturn = array();
		$toReturn['sms'] = json_decode($this->panelInit->settingsArray['smsProvider']);
		$toReturn['mail'] = json_decode($this->panelInit->settingsArray['mailProvider']);
		return $toReturn;
	}

	public function settingsSave(){
		if(Input::get('mailProvider')){
			$settings = settings::where('fieldName','mailProvider')->first();
			$settings->fieldValue = json_encode(Input::all());
			$settings->save();
		}else{
			$settings = settings::where('fieldName','smsProvider')->first();
			$settings->fieldValue = json_encode(Input::all());
			$settings->save();
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['saveSettings'],$this->panelInit->language['settSaved'] );
	}
}
