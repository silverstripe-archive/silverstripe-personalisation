<?php

/**
 * Controller for accessing personalisation externally, e.g. ajax requests
 */
class PersonalisationController extends Controller {

	static $allowed_actions = array(
		"get"
	);

	/**
	 * Return the result of rendering a personalisation scheme. The scheme can be identified by
	 * @return void
	 */
	// @todo provide control over mime type of the result.
	public function get() {
		$id = $this->getRequest()->getVar("ID");
		if (!$id) {
			return $this->httpError("404", "Not found");
		}

		$scheme = PersonalisationScheme::get()->filter("ID", $id)->First();
		if (!$scheme) {
			return $this->httpError("404", "Not found");
		}

		return $scheme->personalise($this);
	}

	/**
	 * Generate a link that can be used by ajax requests that want to personalise using the specified
	 * scheme.
	 * @static
	 * @param $scheme
	 * @return void
	 */
	public static function calc_ajax_link($scheme) {
		return Director::baseURL() . "/pers/get/{$scheme->ID}";
	}
}
