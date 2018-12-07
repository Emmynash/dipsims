<?php

class MediaController extends \BaseController {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = \Auth::user();

		if(!$this->data['users']->hasThePerm('mediaCenter')){
			exit;
		}
	}

	public function listAlbum()
	{
		return $this->listAlbumById();
	}

	public function listAlbumById($dir = 0)
	{
		$toReturn = array();
		if($dir != 0){
			$toReturn['current'] = mediaAlbums::where('id',$dir)->get()->first()->toArray();
		}
		$toReturn['albums'] = mediaAlbums::where('albumParent',$dir)->get()->toArray();
		$toReturn['media'] = mediaItems::where('albumId',$dir)->get()->toArray();
		while (list($key, $value) = each($toReturn['media'])) {
			if($value['mediaType'] != "0"){
				$toReturn['media'][$key]['v'] = $this->video_v($value['mediaURL']);
			}
		}
		return $toReturn;
	}

	public function newAlbum(){
		if($this->data['users']->role != "admin") exit;
		$newFileName = "";
		if (Input::hasFile('albumImage')) {
			$fileInstance = Input::file('albumImage');
			$newFileName = "album_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$file = $fileInstance->move('uploads/media/',$newFileName);
		}

		$mediaAlbums = new mediaAlbums();
		$mediaAlbums->albumTitle = Input::get('albumTitle');
		$mediaAlbums->albumDescription = Input::get('albumDescription');
		$mediaAlbums->albumImage = $newFileName;
		$mediaAlbums->albumParent = Input::get('albumParent');
		$mediaAlbums->save();


		return $this->panelInit->apiOutput(true,$this->panelInit->language['addAlbum'],$this->panelInit->language['albumCreated'],$mediaAlbums->toArray() );
	}

	public function image($image){
		header('Content-Type: image/jpeg');
		if(file_exists('uploads/media/'.$image)){
			echo file_get_contents('uploads/media/'.$image);
		}
		exit;
	}

	function resize($fileName,$w,$h){
		$file = "uploads/media/".$fileName;
		if(!file_exists($file)){
			header('Content-type: image/png');
			echo file_get_contents("uploads/media/default.png");
			exit;
		}
		$destination = "uploads/cache/".$w.$h.$fileName;

		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	    if ($ext == "jpg" || $ext == "jpeg") {
	    	header('Content-type: image/jpg');
	      $source_gdim=imagecreatefromjpeg($file);
	    } elseif ($ext == "png") {
	      header('Content-type: image/png');
	      $source_gdim=imagecreatefrompng($file);
	    } elseif ($ext == "gif") {
	      header('Content-type: image/gif');
	      $source_gdim=imagecreatefromgif($file);
	    } else {
	        //Invalid file type? Return.
	        return;
	    }

		if(!file_exists($destination)){
			//Get the original image dimensions + type
	    list($source_width, $source_height, $source_type) = getimagesize($file);

	    //If a width is supplied, but height is false, then we need to resize by width instead of cropping
	    if ($w && !$h) {
	        $ratio = $w / $source_width;
	        $temp_width = $w;
	        $temp_height = $source_height * $ratio;

	        $desired_gdim = imagecreatetruecolor($temp_width, $temp_height);
	        imagecopyresampled(
	            $desired_gdim,
	            $source_gdim,
	            0, 0,
	            0, 0,
	            $temp_width, $temp_height,
	            $source_width, $source_height
	        );
	    } else {
	        $source_aspect_ratio = $source_width / $source_height;
	        $desired_aspect_ratio = $w / $h;

	        if ($source_aspect_ratio > $desired_aspect_ratio) {
	            /*
	             * Triggered when source image is wider
	             */
	            $temp_height = $h;
	            $temp_width = ( int ) ($h * $source_aspect_ratio);
	        } else {
	            /*
	             * Triggered otherwise (i.e. source image is similar or taller)
	             */
	            $temp_width = $w;
	            $temp_height = ( int ) ($w / $source_aspect_ratio);
	        }

	        /*
	         * Resize the image into a temporary GD image
	         */

	        $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
	        imagecopyresampled(
	            $temp_gdim,
	            $source_gdim,
	            0, 0,
	            0, 0,
	            $temp_width, $temp_height,
	            $source_width, $source_height
	        );

	        /*
	         * Copy cropped region from temporary image into the desired GD image
	         */

	        $x0 = ($temp_width - $w) / 2;
	        $y0 = ($temp_height - $h) / 2;
	        $desired_gdim = imagecreatetruecolor($w, $h);
	        imagecopy(
	            $desired_gdim,
	            $temp_gdim,
	            0, 0,
	            $x0, $y0,
	            $w, $h
	        );
	    }

	    /*
	     * Render the image
	     * Alternatively, you can save the image in file-system or database
	     */

	    if ($ext == "jpg" || $ext == "jpeg") {
	        ImageJpeg($desired_gdim,$destination,100);
	    } elseif ($ext == "png") {
	        ImagePng($desired_gdim,$destination);
	    } elseif ($ext == "gif") {
	        ImageGif($desired_gdim,$destination);
	    } else {
	        return;
	    }

	    ImageDestroy ($desired_gdim);
	  }

	    echo file_get_contents($destination);
	    exit;
	}

	public function deleteAlbum($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = mediaAlbums::where('id', $id)->first() )
        {
			$mediaItems = mediaItems::where('albumId',$id)->get();
			foreach ($mediaItems as $item) {
			  @unlink('uploads/media/'.$item->mediaURL);
			}
			mediaItems::where('albumId',$id)->delete();

			$mediaAlbums = mediaAlbums::where('albumParent',$id)->get();
			foreach ($mediaAlbums as $item) {
			  @unlink('uploads/media/'.$item->albumImage);
			}
			mediaAlbums::where('albumParent',$id)->delete();

			@unlink('uploads/media/'.$postDelete->albumImage);
			$postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delAlbum'],$this->panelInit->language['albumDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delAlbum'],$this->panelInit->language['albumNotExist']);
        }
	}

	public function fetchAlbum($id){
		return mediaAlbums::where('id',$id)->first();
	}

	public function editAlbum($id){
		if($this->data['users']->role != "admin") exit;
		$album = mediaAlbums::where('id',$id)->first();

		$album->albumTitle = Input::get('albumTitle');
		$album->albumDescription = Input::get('albumDescription');

		$newFileName = "";
		if (Input::hasFile('albumImage')) {
			if($album->albumImage != ""){
				@unlink('uploads/media/'.$album->albumImage);
			}

			$fileInstance = Input::file('albumImage');
			$newFileName = "album_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$album->albumImage = $newFileName;
			$file = $fileInstance->move('uploads/media/',$newFileName);
		}

		$album->save();
		return $this->panelInit->apiOutput(true,$this->panelInit->language['editAlbum'],$this->panelInit->language['albumModified'],$album->toArray() );
	}

	public function delete($id){
		if($this->data['users']->role != "admin") exit;
		if ( $postDelete = mediaItems::where('id', $id)->first() )
        {
			@unlink('uploads/media/'.$postDelete->mediaURL);
            $postDelete->delete();
            return $this->panelInit->apiOutput(true,$this->panelInit->language['delMedia'],$this->panelInit->language['mediaDel']);
        }else{
            return $this->panelInit->apiOutput(false,$this->panelInit->language['delMedia'],$this->panelInit->language['mediaNotExist']);
        }
	}

	public function create(){
		if($this->data['users']->role != "admin") exit;
		$newFileName = "";
		if (Input::hasFile('mediaURL')) {
			$fileInstance = Input::file('mediaURL');
			$newFileName = "media_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$file = $fileInstance->move('uploads/media/',$newFileName);
		}

		$mediaItems = new mediaItems();
		$mediaItems->albumId = Input::get('albumId');
		if(Input::get('mediaType') == 0){
			$mediaItems->mediaURL = $newFileName;
		}else{
			$mediaItems->mediaURL = Input::get('mediaURL');

			$thumbImage = "video_".uniqid().".jpg";
			file_put_contents('uploads/media/'.$thumbImage,file_get_contents($this->video_image(Input::get('mediaURL'))));
			$mediaItems->mediaURLThumb = $thumbImage;
		}
		$mediaItems->mediaType = Input::get('mediaType');
		$mediaItems->mediaTitle = Input::get('mediaTitle');
		$mediaItems->mediaDescription = Input::get('mediaDescription');
		$mediaItems->mediaDate = time();
		$mediaItems->save();

		if(Input::get('mediaType') != 0){
			$mediaItems->v = $this->video_v(Input::get('mediaURL'));
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['addMedia'],$this->panelInit->language['mediaCreated'],$mediaItems->toArray() );
	}

	function fetch($id){
		return mediaItems::where('id',$id)->first();
	}

	function edit($id){
		if($this->data['users']->role != "admin") exit;
		$mediaItems = mediaItems::where('id',$id)->first();
		$mediaItems->albumId = Input::get('albumId');

		$newFileName = "";
		if (Input::hasFile('mediaURL')) {
			if($mediaItems->mediaURL != ""){
				@unlink('uploads/media/'.$mediaItems->mediaURL);
			}

			$fileInstance = Input::file('mediaURL');
			$newFileName = "album_".uniqid().".".$fileInstance->getClientOriginalExtension();
			$mediaItems->mediaURL = $newFileName;
			$file = $fileInstance->move('uploads/media/',$newFileName);
		}

		if(Input::get('mediaType') != 0){
			$mediaItems->mediaURL = Input::get('mediaURL');

			$thumbImage = "video_".uniqid().".jpg";
			file_put_contents('uploads/media/'.$thumbImage,file_get_contents($this->video_image(Input::get('mediaURL'))));
			$mediaItems->mediaURLThumb = $thumbImage;
		}

		$mediaItems->mediaTitle = Input::get('mediaTitle');
		$mediaItems->mediaDescription = Input::get('mediaDescription');
		$mediaItems->mediaDate = time();
		$mediaItems->save();

		if(Input::get('mediaType') != 0){
			$mediaItems->v = $this->video_v(Input::get('mediaURL'));
		}

		return $this->panelInit->apiOutput(true,$this->panelInit->language['editMedia'],$this->panelInit->language['mediaModified'],$mediaItems->toArray() );
	}

	function video_image($url,$size="large"){
		if($size=="thumb"){
			$size=1;
		}else{
			$size=0;
		}

		$image_url = parse_url($url);
		if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com'){
			$array = explode("&", $image_url['query']);
			return "http://img.youtube.com/vi/".substr($array[0], 2)."/" . $size . ".jpg";
		} elseif($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com'){
			$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".substr($image_url['path'], 1).".php"));
			return $hash[0]["thumbnail_large"];
		}
	}

	function video_v($url,$size="large"){
		if($size=="thumb"){
			$size=1;
		}else{
			$size=0;
		}

		$image_url = parse_url($url);
		if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com'){
			parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
			return $my_array_of_vars['v'];
		} elseif($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com'){
			$urlParts = explode("/", parse_url($url, PHP_URL_PATH));
			return (int)$urlParts[count($urlParts)-1];
		}
	}



}
