<?php

class SiteSettingsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';
	

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('generalSettings')){
			exit;
		}
	}
	
	// // save uploaded logo
	// public function fileUpload() {
 //   // $this->validate($request, [
 //   //     'siteLogoImage' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
 //   // ]);

 //   if (Input::hasFile('siteLogoImage')) {
 //       $image = Input::hasFile('siteLogoImage');
 //       $name = "Logo.jpg";
 //       $destinationPath = public_path('uploads/profile/');
 //       $image->move($destinationPath, $name);
 //       $this->save();

 //       return back()->with('success','Image Upload successfully');
 //   	}
	// }


	public function listAll($title)
	{
		if($title == "siteSettings"){
			$settingsArray = array();

			$languages = languages::get();
			foreach ($languages as $language) {
				$settingsArray['languages'][$language->id] = $language->languageTitle;
			}

			$settings = settings::get();
			foreach ($settings as $setting) {
				$settingsArray['settings'][$setting->fieldName] = $setting->fieldValue;
			}
			$settingsArray['settings']['activatedModules'] = json_decode($settingsArray['settings']['activatedModules'],true);
			if(!is_array($settingsArray['settings']['activatedModules'])){
				$settingsArray['settings']['activatedModules'] = array();
			}
			$settingsArray['settings']['officialVacationDay'] = json_decode($settingsArray['settings']['officialVacationDay'],true);
			if(!is_array($settingsArray['settings']['officialVacationDay'])){
				$settingsArray['settings']['officialVacationDay'] = array();
			}
			$settingsArray['settings']['daysWeekOff'] = json_decode($settingsArray['settings']['daysWeekOff'],true);
			if(!is_array($settingsArray['settings']['daysWeekOff'])){
				$settingsArray['settings']['daysWeekOff'] = array();
			}
			$settingsArray['smsProvider'] = json_decode($this->panelInit->settingsArray['smsProvider']);
			$settingsArray['mailProvider'] = json_decode($this->panelInit->settingsArray['mailProvider']);
			return $settingsArray;
		}
		if($title == "terms"){
			$settings = settings::where('fieldName','schoolTerms')->first()->toArray();
			$settings['fieldValue'] = htmlspecialchars_decode($settings['fieldValue'],ENT_QUOTES);
			return $settings;
		}
		
		if($title == "logo"){
			$settings = settings::where('fieldName','siteLogo')->first();
			$settings->fieldValue = Input::get('fieldValue');
			$settings = settings::where('fieldName','siteLogoAdditional')->first();
			$settings->fieldValue = Input::get('fieldValue');
			return $settings;
		}

	}

	public function langs()
	{

		$settingsArray = array();

		$languages = languages::get();
		foreach ($languages as $language) {
			$settingsArray['languages'][$language->id] = $language->languageTitle;
		}
		return $settingsArray;

	}
	

	public function save($title){
		if($title == "siteSettings"){
			$settingsData = Input::All();
			
			// $settingsData['siteLogoImage'] = Input::file('siteLogoImage');
			// dd($settingsData['siteLogoImage']);
			// if (Input::hasFile('siteLoggo')) {
			// 			$fileInstance = Input::file('siteLoggo');
			// 			$newFileName = "Logo_.jpg";
			// 			$file = $fileInstance->move('uploads/logo/',$newFileName);
			
			// 			$settings->photo = "Logo_.jpg";
			// 			$settings->save();
			// 		}
				
			while (list($key, $value) = each($settingsData)) {

				$settings = settings::where('fieldName',$key)->first();
				if($key == "activatedModules"){
					$settings->fieldValue = json_encode($value);
				}elseif ($key == "officialVacationDay") {
					$settings->fieldValue = json_encode($value);
				}
				// elseif ($key == "schoolfeesurl") {
				// 	$settings->fieldValue = $value;
				// }elseif($key == "resumptionDate"){
				// 	$settings->fieldValue =$value;
				// }
				elseif ($key == "daysWeekOff") {
					$settings->fieldValue = json_encode($value);
				}elseif ($key == "smsProvider") {
					$settings->fieldValue = json_encode($value);
				}elseif ($key == "mailProvider") {
					$settings->fieldValue = json_encode($value);
				}
				elseif ($key == "siteLoggo") {
					$settings->fieldValue = $value;
					
					if (Input::hasFile('siteLoggo')) {
						$fileInstance = Input::file('siteLoggo');
						$newFileName = "Logo_.jpg";
						$file = $fileInstance->move('uploads/logo/',$newFileName);
			
						$settings->photo = "Logo_.jpg";
						$settings->save();
					}
				}
				
				else{
					$settings->fieldValue = $value;
				}
				$settings->save();

			}
			
			
		return $this->panelInit->apiOutput(true,$this->panelInit->language['editSettings'],$this->panelInit->language['settSaved']);
		}
			
	
		
		if($title == "terms"){
			$settings = settings::where('fieldName','schoolTerms')->first();
			$settings->fieldValue = htmlspecialchars(Input::get('fieldValue'),ENT_QUOTES);
			$settings->save();

			return $this->panelInit->apiOutput(true,$this->panelInit->language['editSettings'],$this->panelInit->language['settSaved']);
		}
		
		// logoSettings
		if($title == "logo"){
			$settings = settings::where('fieldName','siteLogo')->first();
			$settings->fieldValue = Input::get('fieldValue');
			$settings = settings::where('fieldName','siteLogoAdditional')->first();
			$settings->fieldValue = Input::get('fieldValue');
			
			if (Input::hasFile('photo')) {
				$fileInstance = Input::file('photo');
				$newFileName = "Logo_.jpg";
				$file = $fileInstance->move('uploads/logo/',$newFileName);
	
				$settings->photo = "Logo_.jpg";
				$settings->save();
			}
			
			$settings->save();

			return $this->panelInit->apiOutput(true,$this->panelInit->language['editSettings'],$this->panelInit->language['settSaved']);
		}
	}
}
