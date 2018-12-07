<?php

class StaticPagesController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('staticPages')){
			exit;
		}
	}

	public function listAll()
	{
		if($this->data['users']->role != "admin") exit;
		return staticPages::get();
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = staticPages::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delPage'],$this->panelInit->language['pageDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delPage'],$this->panelInit->language['delNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$staticPages = new staticPages();
		$staticPages->pageTitle = Input::get('pageTitle');
		$staticPages->pageContent = Input::get('pageContent');
		$staticPages->pageActive = Input::get('pageActive');
		$staticPages->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addPage'],$this->panelInit->language['pageCreated'],$staticPages->toArray() );
	}

	function fetch($id){
		return staticPages::where('id',$id)->first();
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$staticPages = staticPages::find($id);
		$staticPages->pageTitle = Input::get('pageTitle');
		$staticPages->pageContent = Input::get('pageContent');
		$staticPages->pageActive = Input::get('pageActive');
		$staticPages->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editPage'],$this->panelInit->language['pageModified'],$staticPages->toArray() );
	}

	function active($id){
		if($this->data['users']->role != "admin") exit;
		$staticPagesData = staticPages::find($id)->first();

		$staticPages = staticPages::find($id);
		if($staticPages->pageActive == 1){
			$staticPages->pageActive = 0;
		}else{
			$staticPages->pageActive = 1;
		}
		$staticPages->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['activeInactivePage'],$this->panelInit->language['pageChanged'],array("id"=>$staticPages->id) );
	}
}
