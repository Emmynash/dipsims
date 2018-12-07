<?php

class EventsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('events')){
			exit;
		}
	}

	public function listAll()
	{
		$toReturn = array();
		if($this->data['users']->role == "admin" ){
			$toReturn['events'] = events::get()->toArray();
		}else{
			$toReturn['events'] = events::where('eventFor',$this->data['users']->role)->orWhere('eventFor','all')->get()->toArray();
		}

		foreach ($toReturn['events'] as $key => $item) {
			$toReturn['events'][$key]['eventDescription'] = strip_tags(htmlspecialchars_decode($toReturn['events'][$key]['eventDescription'],ENT_QUOTES));
		}

		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = events::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delEvent'],$this->panelInit->language['eventDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delEvent'],$this->panelInit->language['eventNotEist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$events = new events();
		$events->eventTitle = Input::get('eventTitle');
		$events->eventDescription = htmlspecialchars(Input::get('eventDescription'),ENT_QUOTES);
		$events->eventFor = Input::get('eventFor');
		$events->enentPlace = Input::get('enentPlace');
		$events->eventDate = Input::get('eventDate');
		$events->save();

		$events->eventDescription = strip_tags(htmlspecialchars_decode($events->eventDescription));

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addEvent'],$this->panelInit->language['eventCreated'],$events->toArray() );
	}

	function fetch($id){
		$data = events::where('id',$id)->first()->toArray();
		$data['eventDescription'] = htmlspecialchars_decode($data['eventDescription'],ENT_QUOTES);
		return json_encode($data);
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$events = events::find($id);
		$events->eventTitle = Input::get('eventTitle');
		$events->eventDescription = htmlspecialchars(Input::get('eventDescription'),ENT_QUOTES);
		$events->eventFor = Input::get('eventFor');
		$events->enentPlace = Input::get('enentPlace');
		$events->eventDate = Input::get('eventDate');
		$events->save();

		$events->eventDescription = strip_tags(htmlspecialchars_decode($events->eventDescription));

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editEvent'],$this->panelInit->language['eventModified'],$events->toArray() );
	}
}
