<?php

class PollsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('Polls')){
			exit;
		}
	}

	public function listAll()
	{
		return polls::get();
	}

	public function delete($id){
		if ( $postDelete = polls::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delPoll'],$this->panelInit->language['pollDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delPoll'],$this->panelInit->language['pollNotExist']);
        }
	}

	public function create(){
		$polls = new polls();
		$polls->pollTitle = Input::get('pollTitle');
		$polls->pollOptions = json_encode(Input::get('pollOptions'));
		$polls->pollTarget = Input::get('pollTarget');
		$polls->pollStatus = '0';
		$polls->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addPoll'],$this->panelInit->language['pollCreated'],$polls->toArray() );
	}

	function fetch($id){
		$polls = polls::where('id',$id)->first();
		$polls->pollOptions = json_decode($polls->pollOptions,true);
		if(!is_array($polls->pollOptions)){
			$polls->pollOptions = array();
		}
		return $polls;
	}

	function makeActive($id){
		$polls = polls::where('id',$id)->first();

		$pollOptions = json_decode($polls->pollOptions,true);
		if(count($pollOptions) == 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['activatePoll'],"Poll has no options");
		}

		polls::where('pollStatus','1')->update(array('pollStatus' => '0'));

		$polls->pollStatus = 1;
		$polls->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['activatePoll'],$this->panelInit->language['pollActivated'],array("id"=>$polls->id));
	}

	function edit($id){
		$polls = polls::find($id);
		$polls->pollTitle = Input::get('pollTitle');
		$polls->pollOptions = json_encode(Input::get('pollOptions'));
		$polls->pollTarget = Input::get('pollTarget');
		$polls->pollStatus = Input::get('pollStatus');
		$polls->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editPoll'],$this->panelInit->language['pollUpdated'],$polls->toArray() );
	}
}
