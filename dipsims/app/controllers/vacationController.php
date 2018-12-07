<?php

class vacationController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();
	}

	public function getVacation(){
		if($this->data['users']->role == "admin" || $this->data['users']->role == "parent") exit;

        $currentUserVacations = vacation::where('userid',$this->data['users']->id)->where('acYear',$this->panelInit->selectAcYear)->count();
        $daysList = $this->GetDays(Input::get('fromDate'),Input::get('toDate'));

        if($this->data['users']->role == "teacher" AND (count($daysList['days']) + $currentUserVacations) > $this->panelInit->settingsArray['teacherVacationDays'] ){
            return $this->panelInit->apiOutput(false,"Request Vacation","You Don't have enough balance for vacation");
        }

        if($this->data['users']->role == "student" AND (count($daysList['days']) + $currentUserVacations) > $this->panelInit->settingsArray['studentVacationDays'] ){
            return $this->panelInit->apiOutput(false,"Request Vacation","You Don't have enough balance for vacation");
        }

        return $this->panelInit->apiOutput(true,$this->panelInit->language['getVacation'],$this->panelInit->language['confirmVacation'],$daysList);
	}

    public function saveVacation(){
        if($this->data['users']->role == "admin" || $this->data['users']->role == "parent") exit;

        $daysList = Input::get('days');
        $currentUserVacations = vacation::where('userid',$this->data['users']->id)->where('acYear',$this->panelInit->selectAcYear)->count();

        if($this->data['users']->role == "teacher" AND (count($daysList) + $currentUserVacations) > $this->panelInit->settingsArray['teacherVacationDays'] ){
            return $this->panelInit->apiOutput(false,"Request Vacation","You Don't have enough balance for vacation");
        }

        if($this->data['users']->role == "student" AND (count($daysList) + $currentUserVacations) > $this->panelInit->settingsArray['studentVacationDays'] ){
            return $this->panelInit->apiOutput(false,"Request Vacation","You Don't have enough balance for vacation");
        }

        while (list(, $value) = each($daysList)) {
            $vacation = new vacation();
            $vacation->userid = $this->data['users']->id;
            $vacation->vacDate = $value;
            $vacation->acYear = $this->panelInit->selectAcYear;
			$vacation->role = $this->data['users']->role;
            $vacation->save();
        }

        return $this->panelInit->apiOutput(true,$this->panelInit->language['getVacation'],$this->panelInit->language['vacSubmitted']);
    }

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = vacation::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delVacation'],$this->panelInit->language['vacDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delVacation'],$this->panelInit->language['vacNotExist']);
        }
	}

    function GetDays($sStartDate, $sEndDate){
      $returnDates = array();
      $vacations = array();

      $aDays[] = $sStartDate;

      $StartDateArray = strptime($sStartDate, '%m/%d/%Y');
      $EndDateArray = strptime($sEndDate, '%m/%d/%Y');

      $sStartDate = gmdate("Y-m-d", mktime(0, 0, 0, $StartDateArray['tm_mon']+1, $StartDateArray['tm_mday'], $StartDateArray['tm_year']+1900));
      $sEndDate = gmdate("Y-m-d", mktime(0, 0, 0, $EndDateArray['tm_mon']+1, $EndDateArray['tm_mday'], $EndDateArray['tm_year']+1900));

      // Start the variable off with the start date

      // Set a 'temp' variable, sCurrentDate, with
      // the start date - before beginning the loop
      $sCurrentDate = $sStartDate;

      $daysWeekOff = json_decode($this->panelInit->settingsArray['daysWeekOff'],true);
      $officialVacationDay = json_decode($this->panelInit->settingsArray['officialVacationDay'],true);

      // While the current date is less than the end date
      while($sCurrentDate < $sEndDate){
        // Add a day to the current date
        $nextDay = strtotime("+1 day", strtotime($sCurrentDate));

        if(in_array(date('N',$nextDay),$daysWeekOff)){
            $sCurrentDate = gmdate("Y-m-d",$nextDay );
            continue;
        }

        $saveDate = gmdate("m/d/Y", strtotime("+1 day",strtotime($sCurrentDate) ));

        if(in_array($saveDate,$officialVacationDay)){
            $sCurrentDate = gmdate("Y-m-d",$nextDay );
            $vacations[] = gmdate("m/d/Y", strtotime("+1 day",strtotime($sCurrentDate) ));
            continue;
        }

        $sCurrentDate = gmdate("Y-m-d",$nextDay );

        $aDays[] = $saveDate;
      }

      // Once the loop has finished, return the
      // array of days.
      $returnDates['days'] = $aDays;
      $returnDates['vacations'] = $vacations;
      return $returnDates;
    }

}
