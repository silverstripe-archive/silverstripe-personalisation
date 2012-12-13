<?php

class BasicPersonalisation extends VaryingPersonalisationScheme implements SelectionProvider {

	static $has_many = array(
		"Rules" => "BasicPersonalisationRule"
	);

	/**
	 * 
	 * @param ContextProvider $context
	 * @param PersonalisationSource $source
	 * @return void
	 */
	function getVariation(ContextProvider $context, PersonalisationSource $source) {
		$rules = $this->Rules();

		// First, aggregate together the properties we'll check. We do this so we can fetch them in one call,
		// which is more efficient when fetching from a tracking store, especially if there is an external
		// request. These will be cached in session, so when the rule fetches them, they'll be very fast.
		$properties = array();
		foreach ($rules as $rule)
			$properties = array_merge($properties, $rule->getRequiredProperties());

		// We fetch all the properties in one go. These will be cached so that calls within each rule are read from
		// cache. While we might get more properties than we need, we're also reducing the latency to the tracking
		// store.
		$v = $context->getProperties($properties);

		foreach ($rules as $rule) {
			$var = $rule->variationOnMatch($context);
			if ($var) return $var; // found a match
		}

		// @todo identify the default variation or the first, or something.
		$var = PersonalisationVariation::get()
				->filter("ParentID", $this->ID)
				->First();
		return $var;
	}

	function personalise() {
		$cp = $this->getContextProvider();

		$var = $this->getVariation($cp, $this);

		return $var->render();
	}

	function canCreate($member = null) {
		return true;
	}

}
