<?php

class ImageVariation extends PersonalisationVariation {

	static $db = array(
		"VariationURL" => "Varchar(255)"
	);

	static $has_one = array(
		"Image" => "Image"
	);

	function render(ContextProvider $context, Controller $controller = null) {
		return $this->Image();
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();

		$url = new TextField("VariationURL", "Variation URL");
		$fields->push($url);

		if (!$this->ID) {
			$fields->removeByName("Image");
			$imageField = new ReadonlyField('Variation', 'Variation', 'Images can be added after you have saved for the first time');
			$fields->push($imageField);
		}

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



