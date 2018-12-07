<?php

class expensesController extends \BaseController {

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
		$toReturn = array();
		$expenses = expenses::get()->toArray();

		while (list(, $value) = each($expenses)) {
			$toReturn[date('F',$value['expenseDate'])][] = $value;
		}

		return $toReturn;
	}

	public function delete($id){
		if ( $postDelete = expenses::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delExpense'],$this->panelInit->language['expenseDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delExpense'],$this->panelInit->language['expenseNotExist']);
        }
	}

	public function create(){
		$expenses = new expenses();
		if(Input::has('expenseDate')){
			$newsDate = explode("/", Input::get('expenseDate'));
			if(is_array($newsDate) AND count($newsDate) == 3){
				$newsDate = mktime(0,0,0,$newsDate['0'],$newsDate['1'],$newsDate['2']);
				$expenses->expenseDate = $newsDate;
			}
		}
		$expenses->expenseTitle = Input::get('expenseTitle');
		$expenses->expenseAmount = Input::get('expenseAmount');
		$expenses->expenseNotes = Input::get('expenseNotes');
		$expenses->save();

		$expenses->month = date('F',$expenses->expenseDate);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addExpense'],$this->panelInit->language['expenseAdded'],$expenses->toArray() );
	}

	function fetch($id){
		$expenses = expenses::where('id',$id)->first();
		$expenses->expenseDate = date('m/d/Y',$expenses->expenseDate);
		return $expenses;
	}

	function edit($id){
		$expenses = expenses::find($id);
		if(Input::has('expenseDate')){
			$newsDate = explode("/", Input::get('expenseDate'));
			if(is_array($newsDate) AND count($newsDate) == 3){
				$newsDate = mktime(0,0,0,$newsDate['0'],$newsDate['1'],$newsDate['2']);
				$expenses->expenseDate = $newsDate;
			}
		}
		$expenses->expenseTitle = Input::get('expenseTitle');
		$expenses->expenseAmount = Input::get('expenseAmount');
		$expenses->expenseNotes = Input::get('expenseNotes');
		$expenses->save();

		$expenses->month = date('F',$expenses->expenseDate);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editExpense'],$this->panelInit->language['expenseUpdated'],$expenses->toArray() );
	}
}
