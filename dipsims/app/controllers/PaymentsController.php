<?php

class PaymentsController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('accounting')){
			exit;
		}
	}

	public function listAll($page = 1)
	{
		$toReturn = array();

		if($this->data['users']->role == "admin"){
			$toReturn['payments'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )
						->get();
			$toReturn['totalItems'] = payments::count();
		}elseif($this->data['users']->role == "student"){
			$toReturn['payments'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )
						->get();
			$toReturn['totalItems'] = payments::count();
		}elseif($this->data['users']->role == "parent"){
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				while (list($key, $value) = each($parentOf)) {
					$studentId[] = $value['id'];
				}
			}
			$toReturn['payments'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )
						->get();
			$toReturn['totalItems'] = payments::count();
		}

		$classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className ;
		}

		return $toReturn;
	}

	public function search($keyword,$page = 1)
	{
		$toReturn = array();

		if($this->data['users']->role == "admin"){
			$toReturn['payments'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->where('payments.paymentTitle','LIKE','%'.$keyword.'%')
						->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%')
						->orWhere('fullName','LIKE','%'.$keyword.'%')
						->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )
						->get();
			$toReturn['totalItems'] = \DB::table('payments')
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->where('payments.paymentTitle','LIKE','%'.$keyword.'%')
						->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%')
						->orWhere('fullName','LIKE','%'.$keyword.'%')->count();

		}elseif($this->data['users']->role == "student"){
			$toReturn['payments'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->where('payments.paymentTitle','LIKE','%'.$keyword.'%')
						->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%')
						->orWhere('users.fullName','LIKE','%'.$keyword.'%')
						->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )
						->get();
			$toReturn['totalItems'] = \DB::table('payments')
						->where('paymentStudent',$this->data['users']->id)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->where('payments.paymentTitle','LIKE','%'.$keyword.'%')
						->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%')
						->orWhere('users.fullName','LIKE','%'.$keyword.'%')->count();

		}elseif($this->data['users']->role == "parent"){
			$studentId = array();
			$parentOf = json_decode($this->data['users']->parentOf,true);
			if(is_array($parentOf)){
				while (list($key, $value) = each($parentOf)) {
					$studentId[] = $value['id'];
				}
			}
			$toReturn['payments'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->where('payments.paymentTitle','LIKE','%'.$keyword.'%')
						->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%')
						->orWhere('users.fullName','LIKE','%'.$keyword.'%')
						->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )
						->get();
			$toReturn['totalItems'] = \DB::table('payments')
						->whereIn('paymentStudent',$studentId)
						->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
						->select('payments.id as id',
						'payments.paymentTitle as paymentTitle',
						'payments.paymentDescription as paymentDescription',
						'payments.paymentAmount as paymentAmount',
						'payments.paymentStatus as paymentStatus',
						'payments.paymentDate as paymentDate',
						'payments.paymentStudent as studentId',
						'users.fullName as fullName')
						->where('payments.paymentTitle','LIKE','%'.$keyword.'%')
						->orWhere('payments.paymentDescription','LIKE','%'.$keyword.'%')
						->orWhere('users.fullName','LIKE','%'.$keyword.'%')->count();
		}

		$classes = classes::where('classAcademicYear',$this->panelInit->selectAcYear)->get();
		$toReturn['classes'] = array();
		foreach ($classes as $class) {
			$toReturn['classes'][$class->id] = $class->className ;
		}

		$toReturn['students'] = User::where('role','student')->where('activated','1')->get()->toArray();
		return $toReturn;
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = payments::where('id', $id)->first() )
        {
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delPayment'],$this->panelInit->language['paymentDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delPayment'],$this->panelInit->language['paymentNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$craetedPayments = array();
		$studentClass = Input::get('paymentStudent');
		while (list($key, $value) = each($studentClass)) {
			if($value['id'] == "" || $value['id'] == "0"){
				continue;
			}
			$payments = new payments();
			$payments->paymentTitle = Input::get('paymentTitle');
			$payments->paymentDescription = Input::get('paymentDescription');
			$payments->paymentStudent = $value['id'];
			$payments->paymentAmount = Input::get('paymentAmount');
			$payments->paymentStatus = Input::get('paymentStatus');
			$payments->paymentDate = Input::get('paymentDate');
			$payments->paymentUniqid = uniqid();
			$payments->save();

			$craetedPayments[] = $payments->toArray();
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addPayment'],$this->panelInit->language['paymentCreated'],$craetedPayments );
	}

	function invoice($id){
		$return = array();
		$return['payment'] = payments::where('id',$id)->first()->toArray();
		$return['siteTitle'] = $this->panelInit->settingsArray['siteTitle'];
		$return['baseUrl'] = URL::to('/');
		$return['address'] = $this->panelInit->settingsArray['address'];
		$return['address2'] = $this->panelInit->settingsArray['address2'];
		$return['systemEmail'] = $this->panelInit->settingsArray['systemEmail'];
		$return['phoneNo'] = $this->panelInit->settingsArray['phoneNo'];
		// $return['paypalPayment'] = $this->panelInit->settingsArray['paypalPayment'];
		$return['currency_code'] = $this->panelInit->settingsArray['currency_code'];
		$return['currency_symbol'] = $this->panelInit->settingsArray['currency_symbol'];
		$return['paymentTax'] = $this->panelInit->settingsArray['paymentTax'];
		$return['amountTax'] = ($this->panelInit->settingsArray['paymentTax']*$return['payment']['paymentAmount']) /100;
		$return['totalWithTax'] = $return['payment']['paymentAmount'] + $return['amountTax'];
		// $return['allowPaystack'] = $this->panelInit->settingsArray['allowPaystack'];
		// $return['paystackSK'] = $this->panelInit->settingsArray['paystackSK'];
		// $return['paystackPK'] = $this->panelInit->settingsArray['paystackPK'];
		$return['user'] = User::where('id',$return['payment']['paymentStudent'])->first()->toArray();

		return $return;
	}

	function fetch($id){
		return payments::where('id',$id)->first();
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$payments = payments::find($id);
		$payments->paymentTitle = Input::get('paymentTitle');
		$payments->paymentDescription = Input::get('paymentDescription');
		$payments->paymentAmount = Input::get('paymentAmount');
		$payments->paymentStatus = Input::get('paymentStatus');
		$payments->paymentDate = Input::get('paymentDate');
		$payments->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editPayment'],$this->panelInit->language['paymentModified'],$payments->toArray() );
	}

	function paymentSuccess($uniqid){
		$payments = payments::where('paymentUniqid',$uniqid)->first();
		if(Input::get('verify_sign')){
			$payments->paymentStatus = 1;
			$payments->paymentSuccessDetails = json_encode(Input::all());
			$payments->save();
		}
		return Redirect::to('/#/payments');
	}

	function PaymentData($id){
		if($this->data['users']->role != "admin") exit;
		$payments = payments::where('id',$id)->first();
		if($payments->paymentSuccessDetails == ""){
			return $this->panelInit->apiOutput(false,$this->panelInit->language['paymentDetails'],$this->panelInit->language['noPaymentDetails'] );
		}else{
			return $this->panelInit->apiOutput(true,null,null,json_decode($payments->paymentSuccessDetails,true) );
		}
	}

	function paymentFailed(){
		return Redirect::to('/#/payments');
	}

	public function searchStudents($student){
		$students = User::where('role','student')->where('fullName','like','%'.$student.'%')->orWhere('username','like','%'.$student.'%')->orWhere('email','like','%'.$student.'%')->get();
		$retArray = array();
		foreach ($students as $student) {
			$retArray[$student->id] = array("id"=>$student->id,"name"=>$student->fullName,"email"=>$student->email);
		}
		return json_encode($retArray);
	}

	function export($type){
		if($this->data['users']->role != "admin") exit;
		if($type == "excel"){
			$classArray = array();
			$classes = classes::get();
			foreach ($classes as $class) {
				$classArray[$class->id] = $class->className;
			}

			$data = array(1 => array ('Title','Description','Student','Amount','Date','Status'));
			$payments = \DB::table('payments')
					->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
					->select('payments.id as id',
					'payments.paymentTitle as paymentTitle',
					'payments.paymentDescription as paymentDescription',
					'payments.paymentAmount as paymentAmount',
					'payments.paymentStatus as paymentStatus',
					'payments.paymentDate as paymentDate',
					'payments.paymentStudent as studentId',
					'users.fullName as fullName')
					->get();
			foreach ($payments as $value) {
				if($value->paymentStatus == 1){
					$paymentStatus = "PAID";
				}else{
					$paymentStatus = "UNPAID";
				}
				$data[] = array ($value->paymentTitle,$value->paymentDescription , $value->fullName,$value->paymentAmount,$value->paymentDate,$paymentStatus );
			}

			$xls = new Excel_XML('UTF-8', false, 'Students Sheet');
			$xls->addArray($data);
			$xls->generateXML('Students-Sheet');
		}elseif ($type == "pdf") {
			$classArray = array();
			$classes = classes::get();
			foreach ($classes as $class) {
				$classArray[$class->id] = $class->className;
			}

			$header = array ('Title','Description','Student','Amount','Date','Status');
			$data = array();
			$payments = \DB::table('payments')
					->leftJoin('users', 'users.id', '=', 'payments.paymentStudent')
					->select('payments.id as id',
					'payments.paymentTitle as paymentTitle',
					'payments.paymentDescription as paymentDescription',
					'payments.paymentAmount as paymentAmount',
					'payments.paymentStatus as paymentStatus',
					'payments.paymentDate as paymentDate',
					'payments.paymentStudent as studentId',
					'users.fullName as fullName')
					->get();
			foreach ($payments as $value) {
				if($value->paymentStatus == 1){
					$paymentStatus = "PAID";
				}else{
					$paymentStatus = "UNPAID";
				}
				$data[] = array ($value->paymentTitle,$value->paymentDescription , $value->fullName,$value->paymentAmount,$value->paymentDate,$paymentStatus );
			}

			$pdf = new FPDF();
			$pdf->SetFont('Arial','',10);
			$pdf->AddPage();
			// Header
			foreach($header as $col)
				$pdf->Cell(40,7,$col,1);
			$pdf->Ln();
			// Data
			foreach($data as $row)
			{
				foreach($row as $col)
					$pdf->Cell(40,6,$col,1);
				$pdf->Ln();
			}
			$pdf->Output();
		}
		exit;
	}

	public static function generateInvoice($user){
		if($user->studentClass == "" || $user->studentClass == "0"){
			return;
		}

		$feeAllocationUser = feeAllocation::where('allocationType','student')->where('allocationId',$user->id);
		if($feeAllocationUser->count() > 0){
			$feeAllocationUser = $feeAllocationUser->first();
		}

		if(!isset($feeAllocationUser->allocationValues)){
			$feeAllocationUser = feeAllocation::where('allocationType','class')->where('allocationId',$user->studentClass);
		}
		if($feeAllocationUser->count() > 0){
			$feeAllocationUser = $feeAllocationUser->first();
		}

		if(!isset($feeAllocationUser->allocationValues)){
			return;
		}

		$feeTypesArray = array();
		$feeTypes = feeType::get();
		foreach($feeTypes as $type){
			$feeTypesArray[$type->id] = $type->feeTitle;
		}

		$paymentDescription = array();
		$paymentAmount = 0;
		$allocationValues = json_decode($feeAllocationUser->allocationValues,true);
		while (list($key, $value) = each($allocationValues)) {
			if(isset($feeTypesArray[$key])){
				$paymentDescription[] = $feeTypesArray[$key];
				$paymentAmount += $value;
			}
		}

		$payments = new payments();
		$payments->paymentTitle = "School Fees";
		$payments->paymentDescription = implode(", ",$paymentDescription);
		$payments->paymentStudent = $user->id;
		$payments->paymentAmount = $paymentAmount;
		$payments->paymentStatus = "0";
		$payments->paymentDate = date('m/d/Y');
		$payments->paymentUniqid = uniqid();
		$payments->save();
	}
}
