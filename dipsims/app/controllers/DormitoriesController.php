<?php

class DormitoriesController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('Dormitories')){
			exit;
		}
	}

	public function listAll()
	{
		return dormitories::get();
	}

	public function delete($id){
		if ( $postDelete = dormitories::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delDorm'],$this->panelInit->language['dormDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delDorm'],$this->panelInit->language['dormNotExist']);
        }
	}

	public function create(){
		$dormitories = new dormitories();
		$dormitories->dormitory = Input::get('dormitory');
		$dormitories->dormDesc = Input::get('dormDesc');
		$dormitories->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addDormitories'],$this->panelInit->language['dormCreated'],$dormitories->toArray() );
	}

	function fetch($id){
		return dormitories::where('id',$id)->first();
	}

	function edit($id){
		$dormitories = dormitories::find($id);
		$dormitories->dormitory = Input::get('dormitory');
		$dormitories->dormDesc = Input::get('dormDesc');
		$dormitories->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editDorm'],$this->panelInit->language['dormUpdated'],$dormitories->toArray() );
	}
}
