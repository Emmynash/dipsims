<?php

class LibraryController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('Library')){
			exit;
		}
	}

	public function listAll($page = 1)
	{
		$toReturn = array();
		$toReturn['bookLibrary'] = bookLibrary::orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
		$toReturn['totalItems'] = bookLibrary::count();
		$toReturn['userRole'] = $this->data['users']->role;
		return $toReturn;
	}

	public function search($keyword,$page = 1)
	{
		$toReturn = array();
		$toReturn['bookLibrary'] = bookLibrary::where('bookName','like','%'.$keyword.'%')->orWhere('bookDescription','like','%'.$keyword.'%')->orWhere('bookAuthor','like','%'.$keyword.'%')->orderBy('id','DESC')->take('20')->skip(20* ($page - 1) )->get()->toArray();
		$toReturn['totalItems'] = bookLibrary::where('bookName','like','%'.$keyword.'%')->orWhere('bookDescription','like','%'.$keyword.'%')->orWhere('bookAuthor','like','%'.$keyword.'%')->count();
		return $toReturn;
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = bookLibrary::where('id', $id)->first() )
        {
			@unlink('uploads/books/'.$postDelete->bookFile);
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delLibrary'],$this->panelInit->language['itemdel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delLibrary'],$this->panelInit->language['itemNotExist']);
        }
	}

	public function download($id){
		$toReturn = bookLibrary::where('id',$id)->first();
		if(file_exists('uploads/books/'.$toReturn->bookFile)){
			$fileName = preg_replace('/[^a-zA-Z0-9-_\.]/','-',$toReturn->bookName). "." .pathinfo($toReturn->bookFile, PATHINFO_EXTENSION);
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=" . $fileName);
			echo file_get_contents('uploads/books/'.$toReturn->bookFile);
		}
		exit;
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$bookLibrary = new bookLibrary();
		$bookLibrary->bookName = Input::get('bookName');
		$bookLibrary->bookDescription = Input::get('bookDescription');
		$bookLibrary->bookAuthor = Input::get('bookAuthor');
		$bookLibrary->bookType = Input::get('bookType');
		$bookLibrary->bookPrice = Input::get('bookPrice');
		$bookLibrary->bookState = Input::get('bookState');
		$bookLibrary->save();

		if (Input::hasFile('bookFile')) {
			$fileInstance = Input::file('bookFile');
			$newFileName = "book_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/books/',$newFileName);

			$bookLibrary->bookFile = $newFileName;
			$bookLibrary->save();
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addBook'],$this->panelInit->language['bookAdded'],$bookLibrary->toArray() );
	}

	function fetch($id){
		$data = bookLibrary::where('id',$id)->first()->toArray();
		return json_encode($data);
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$bookLibrary = bookLibrary::find($id);
		$bookLibrary->bookName = Input::get('bookName');
		$bookLibrary->bookDescription = Input::get('bookDescription');
		$bookLibrary->bookAuthor = Input::get('bookAuthor');
		$bookLibrary->bookType = Input::get('bookType');
		$bookLibrary->bookPrice = Input::get('bookPrice');
		$bookLibrary->bookState = Input::get('bookState');
		if (Input::hasFile('bookFile')) {
			@unlink("uploads/books/".$bookLibrary->bookFile);
			$fileInstance = Input::file('bookFile');
			$newFileName = "book_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$fileInstance->move('uploads/books/',$newFileName);

			$bookLibrary->bookFile = $newFileName;
		}
		$bookLibrary->save();

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editBook'],$this->panelInit->language['bookModified'],$bookLibrary->toArray() );
	}
}
