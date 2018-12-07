<?php

class feeTypesController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('accounting')){
			exit;
		}
	}

	public function listAll()
	{
		return feeType::get();
	}

	public function delete($id){
		if ( $postDelete = feeType::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delFeeType'],$this->panelInit->language['feeDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delFeeType'],$this->panelInit->language['feeNotExist']);
        }
	}

	public function create(){
		$feeType = new feeType();
		$feeType->feeTitle = Input::get('feeTitle');
		if(Input::has('feeDefault')){
			$feeType->feeDefault = Input::get('feeDefault');
		}
		if(Input::has('feeNotes')){
			$feeType->feeNotes = Input::get('feeNotes');
		}
		$feeType->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addFeeType'],$this->panelInit->language['feeAdded'],$feeType->toArray() );
	}

	function fetch($id){
		return feeType::where('id',$id)->first();
	}

	function edit($id){
		$feeType = feeType::find($id);
		$feeType->feeTitle = Input::get('feeTitle');
		$feeType->feeDefault = Input::get('feeDefault');
		$feeType->feeNotes = Input::get('feeNotes');
		$feeType->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editFeeType'],$this->panelInit->language['feeUpdated'],$feeType->toArray() );
	}
}
