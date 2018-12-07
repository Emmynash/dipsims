<?php

class AccountSettingsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
	}

	public function listAll()
	{
		$data = User::where('id',Auth::user()->id)->first()->toArray();
		$data['birthday'] = date('m/d/Y',$data['birthday']);
		return $data;
	}

	public function langs()
	{
		$settingsArray = array();

		$languages = languages::get();
		foreach ($languages as $language) {
			$settingsArray['languages'][$language->id] = $language->languageTitle;
		}

		$settingsArray['languageAllow'] = $this->panelInit->settingsArray['languageAllow'];
		$settingsArray['layoutColorUserChange'] = $this->panelInit->settingsArray['layoutColorUserChange'];

		return $settingsArray;
	}

	function saveProfile(){
		$User = User::where('id',Auth::user()->id)->first();
		$User->fullName = Input::get('fullName');
		$User->gender = Input::get('gender');
		$User->address = Input::get('address');
		$User->phoneNo = Input::get('phoneNo');
		$User->mobileNo = Input::get('mobileNo');
		$User->defLang = Input::get('defLang');
		$User->defTheme = Input::get('defTheme');
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

		$data = User::where('id',Auth::user()->id)->first()->toArray();
		$data['birthday'] = date('m/d/Y',$data['birthday']);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['ChgProfileData'],$this->panelInit->language['profileUpdated'],$data);
	}

	function saveEmail(){
		if(User::where('email',Input::get('email'))->count() > 0){
			return $this->panelInit->apiOutput(false,"Update profile",$this->panelInit->language['mailAlreadyUsed']);
		}
		if (!Hash::check(Input::get('password'), $this->data['users']->password)) {
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editPassword'],$this->panelInit->language['oldPwdDontMatch']);
		}
		$User = User::where('id',Auth::user()->id)->first();
		$User->email = Input::get('email');
		$User->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['ChgProfileData'],$this->panelInit->language['profileUpdated']);
	}

	function savePassword(){
		if (Hash::check(Input::get('password'), $this->data['users']->password)) {
			$User = User::where('id',Auth::user()->id)->first();
			$User->password = Hash::make(Input::get('newPassword'));
			$User->save();

			return $this->panelInit->apiOutput(true,$this->panelInit->language['editPassword'],$this->panelInit->language['pwdChangedSuccess']);
		}else{
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editPassword'],$this->panelInit->language['oldPwdDontMatch']);
		}
	}

}
