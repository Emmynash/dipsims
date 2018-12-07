<?php

class LanguagesController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('Languages')){
			exit;
		}
	}

	public function listAll()
	{
		return languages::get();
	}

	public function delete($id){
		if ( $postDelete = languages::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delLanguage'],$this->panelInit->language['languageDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delLanguage'],$this->panelInit->language['languageNotExist']);
        }
	}

	public function create(){
		$languages = new languages();
		$languages->languageTitle = Input::get('languageTitle');
		$languages->languageUniversal = Input::get('languageUniversal');
		$languages->isRTL = Input::get('isRTL');
		$languages->languagePhrases = json_encode(Input::get('languagePhrases'));
		$languages->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addLanguage'],$this->panelInit->language['langCreated'],$languages->toArray() );
	}

	function fetch($id){
		$languages = languages::where('id',$id)->first()->toArray();
		$languages['languagePhrases'] = json_decode($languages['languagePhrases'],true);
		return $languages;
	}

	function edit($id){
		$languages = languages::find($id);
		$languages->languageTitle = Input::get('languageTitle');
		$languages->languageUniversal = Input::get('languageUniversal');
		$languages->isRTL = Input::get('isRTL');
		$languages->languagePhrases = json_encode(Input::get('languagePhrases'));
		$languages->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editLanguage'],$this->panelInit->language['langModified'],$languages->toArray() );
	}
}
