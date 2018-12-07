<?php

class feeAllocationController extends \BaseController {

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
		$toReturn['classes'] = array();
		$toReturn['classAllocation'] = array();
		$classesIn = array();

		$classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->select('id','className')->get();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className;
			$classesIn[] = $class->id;
		}

		if(count($classesIn) > 0){
			$toReturn['classAllocation'] = feeAllocation::where('allocationType','class')->whereIn('allocationId',$classesIn)->get()->toArray();
		}

		$toReturn['StudentAllocation'] = \DB::table('feeAllocation')
								->leftJoin('users', 'users.id', '=', 'feeAllocation.allocationId')
								->select('feeAllocation.id as id',
								'feeAllocation.allocationId as userId',
								'users.fullName as fullName')
								->where('feeAllocation.allocationType','student')->get();

		$toReturn['feeType'] = feeType::get()->toArray();
		return $toReturn;
	}

	public function delete($id){
		if ( $postDelete = feeAllocation::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delFeeAllocation'],$this->panelInit->language['feeAllocationDeleted']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delFeeAllocation'],$this->panelInit->language['feeAllocationNotExist']);
        }
	}

	public function create(){
		if(Input::get('allocationType') == "student" AND ( !is_array(Input::get('paymentStudent')) OR count(Input::get('paymentStudent')) == 0 ) ){
			return $this->panelInit->apiOutput(false,"Add fee allocation","No students selected" );
		}
		if(Input::get('allocationType') == "student" AND  count(Input::get('paymentStudent')) > 0 ){
			$studentsIds = array();
			$alreadyExists = array();
			$paymentStudent = Input::get('paymentStudent');
			foreach ($paymentStudent as $key => $item) {
				$studentsIds[] = $item['id'];

				if(feeAllocation::where('allocationType','student')->where('allocationId',$item['id'])->count() > 0){
					$foundStudent = User::where('id',$item['id'])->first();
					$alreadyExists[] = $foundStudent->fullName;
				}
			}

			if(count($alreadyExists) > 0){
				return $this->panelInit->apiOutput(false,$this->panelInit->language['addFeeAllocation'],$this->panelInit->language['students'] . " : ".implode(', ',$alreadyExists).$this->panelInit->language['alreadyHasAllFee'] );
			}
		}

		if(Input::get('allocationType') == "class" AND feeAllocation::where('allocationType','class')->where('allocationId',Input::get('allocationId'))->count() > 0){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['addFeeAllocation'],$this->panelInit->language['selStudentAlreadyHasAllFee'] );
		}

		$allocationValues = Input::get('allocationValues');
		if(count($allocationValues) == 0){
			return;
		}else{
			$dbAllocationValues = array();
			foreach ($allocationValues as $allocation) {
				$dbAllocationValues[$allocation['id']] = $allocation['feeDefault'];
			}
		}

		if(Input::get('allocationType') == "student" AND count($studentsIds) > 0){
			foreach ($studentsIds as $key => $item) {
				$feeAllocation = new feeAllocation();
				$feeAllocation->allocationType = Input::get('allocationType');
				$feeAllocation->allocationId = $item;
				$feeAllocation->allocationValues = json_encode($dbAllocationValues);
				$feeAllocation->save();
			}
		}
		if(Input::get('allocationType') == "class"){
			$feeAllocation = new feeAllocation();
			$feeAllocation->allocationType = Input::get('allocationType');
			$feeAllocation->allocationId = Input::get('allocationId');
			$feeAllocation->allocationValues = json_encode($dbAllocationValues);
			$feeAllocation->save();
		}
		return $this->panelInit->apiOutput(true,$this->panelInit->language['addFeeAllocation'],$this->panelInit->language['feeAllocationAdded'] );
	}

	function fetch($id){
		$feeAllocation = feeAllocation::where('id',$id)->first();
		if($feeAllocation->allocationType == "student"){
			$user = User::where('id',$feeAllocation->allocationId)->first();
			$feeAllocation->fullName = $user->fullName;
		}
		$feeAllocation->allocationValues = json_decode($feeAllocation->allocationValues,true);
		return $feeAllocation;
	}

	function edit($id){
		$feeAllocation = feeAllocation::find($id);
		$feeAllocation->allocationId = Input::get('allocationId');
		$feeAllocation->allocationValues = json_encode(Input::get('allocationValues'));
		$feeAllocation->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editFeeAllocation'],$this->panelInit->language['feeAllocationUpdated'],$feeAllocation->toArray() );
	}
}
