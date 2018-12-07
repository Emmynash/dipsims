<?php

class academicYearController extends \BaseController {

	public $data = array();
	public $panelInit ;
	public $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['users'] = \Auth::user();
		if($this->data['users']->role != "admin") exit;

		if(!$this->data['users']->hasThePerm('academicyears')){
			exit;
		}
	}

	public function listAll()
	{
		return academicYear::get()->toArray();
	}

	public function delete($id){
        if ( $postDelete = academicYear::where('id', $id)->first() )
        {
            if($postDelete->isDefault == 1){
                return $this->panelInit->apiOutput(false,$this->panelInit->language['delAcademicYears'],$this->panelInit->language['cannotDelDefAcademicYears']);
            }
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delAcademicYears'],$this->panelInit->language['acYearDelSuc']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delAcademicYears'],$this->panelInit->language['acYearNotExist']);
        }
	}

	public function create(){
        if(Input::has('isDefault') AND Input::get('isDefault') == 1){
            DB::table('academicYear')->update(array('isDefault' => 0));
            $isDefault = 1;
        }else{
            $isDefault = 0;
        }

		$academicYear = new academicYear();
		$academicYear->yearTitle = Input::get('yearTitle');
		$academicYear->isDefault = $isDefault;
		$academicYear->save();

        return $this->panelInit->apiOutput(true,$this->panelInit->language['addAcademicyear'],$this->panelInit->language['acYearAddSuc'],array("id"=>$academicYear->id,"yearTitle"=>Input::get('yearTitle'),"isDefault"=>$isDefault));
	}

	function fetch($id){
		$academicYear = academicYear::where('id',$id)->first()->toArray();
		return $academicYear;
	}

	function edit($id){
		$academicYear = academicYear::find($id);
		$academicYear->yearTitle = Input::get('yearTitle');
		$academicYear->save();

        return $this->panelInit->apiOutput(true,$this->panelInit->language['editAcademicYears'],$this->panelInit->language['acYearModSuc'],array("id"=>$academicYear->id,"yearTitle"=>Input::get('yearTitle'),"isDefault"=>$academicYear->isDefault));
	}

    function active($id){
        DB::table('academicYear')->update(array('isDefault' => 0));

        $academicYear = academicYear::find($id);
		$academicYear->isDefault = "1";
		$academicYear->save();
        return $this->panelInit->apiOutput(true,$this->panelInit->language['editAcademicYears'],$this->panelInit->language['acYearNowDef'],array("id"=>$academicYear->id));
    }

}
