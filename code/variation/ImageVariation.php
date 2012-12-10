<?php

class ImageVariation extends PersonalisationVariation {

	static $has_one = array(
		"Image" => "Image"
	);

	function render(ContextProvider $context) {
		return $this->Image();
	}
}
