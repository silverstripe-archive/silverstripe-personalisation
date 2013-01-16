<?php

class ImageVariation extends PersonalisationVariation {

	static $db = array(
		"VariationURL" => "Varchar(255)"
	);

	static $has_one = array(
		"Image" => "Image"
	);

	function render(ContextProvider $context) {
		return $this->Image();
	}

	static function addExtraFields(){
		$fields = new FieldList();
		$url = new TextField("VariationURL", "Variation URL");
		$fields->push($url);
		$imageField = new UploadField("ImageID", "Image");
		$imageField->getValidator()->setAllowedExtensions(array('jpg', 'gif', 'png'));
		$fields->push($imageField);
		return $fields;
	}

	/**
	 *
	 * @return null|string
	 */
	function getURL(){
		if($this->VariationURL){
			$url = $this->VariationURL;
			if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
				$url = "http://" . $url;
			}
			return $url;
		}
	}
}



