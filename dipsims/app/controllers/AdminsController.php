<?php

class AdminsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('Administrators')){
			exit;
		}
	}

	public function listAll()
	{
		return User::where('role','admin')->get();
	}

	public function delete($id){
		if ( $postDelete = User::where('role','admin')->where('id',$id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delAdministrator'],$this->panelInit->language['adminDeletedSucc']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delAdministrator'],$this->panelInit->language['adminNotExist']);
        }
	}

	public function create(){
		if(User::where('username',trim(Input::get('username')))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['addAdministrator'],$this->panelInit->language['usernameAlreadyUsed']);
		}
		if(User::where('email',Input::get('email'))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['addAdministrator'],$this->panelInit->language['emailAlreadyUsed']);
		}
		$User = new User();
		$User->username = Input::get('username');
		$User->email = Input::get('email');
		$User->fullName = Input::get('fullName');
		$User->password = Hash::make(Input::get('password'));
		$User->customPermissionsType = Input::get('customPermissionsType');
		$User->customPermissions = json_encode(Input::get('customPermissions'));
		$User->role = "admin";
		$User->save();

		if (Input::hasFile('photo')) {
			$fileInstance = Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);

			$User->photo = "profile_".$User->id.".jpg";
			$User->save();
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addAdministrator'],$this->panelInit->language['adminUpdated'],$User->toArray());
	}

	function fetch($id){
		$user = User::where('role','admin')->where('id',$id)->first();
		$user->customPermissions = json_decode($user->customPermissions,true);
		return $user;
	}

	function edit($id){
		if(User::where('username',trim(Input::get('username')))->where('id','<>',$id)->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editAdministrator'],$this->panelInit->language['usernameAlreadyUsed']);
		}
		if(User::where('email',Input::get('email'))->where('id','<>',$id)->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['editAdministrator'],$this->panelInit->language['emailAlreadyUsed']);
		}
		$User = User::find($id);
		$User->username = Input::get('username');
		$User->email = Input::get('email');
		$User->fullName = Input::get('fullName');
		if(Input::get('password') != ""){
			$User->password = Hash::make(Input::get('password'));
		}
		if (Input::hasFile('photo')) {
			$fileInstance = Input::file('photo');
			$newFileName = "profile_".$User->id.".jpg";
			$file = $fileInstance->move('uploads/profile/',$newFileName);

			$User->photo = "profile_".$User->id.".jpg";
		}
		$User->customPermissionsType = Input::get('customPermissionsType');
		$User->customPermissions = json_encode(Input::get('customPermissions'));
		$User->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editAdministrator'],$this->panelInit->language['adminUpdated'],$User->toArray());
	}
}
