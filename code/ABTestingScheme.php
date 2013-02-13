<?php

/**
 * An AB testing scheme is one where there are a predefined number of variations, and one is chosen at random.
 */
class ABTestingScheme extends VaryingPersonalisationScheme {

	static $db = array(
		// Name of a property in the property namespace (e.g. abtesting.pagetype.name) that we will use to choose
		// the variation.
		"VariationPropertyName" => "Varchar(255)"
	);

	function canCreate($member = null) {
		return true;
	}

	function personalise(Controller $controller = null) {
		$cp = $this->getContextProvider();

		$var = $this->getVariation($cp, $this);
		return $var->render(null, $controller);
	}

	/**
	 * Determine the variation that should be displayed.
	 * @param ContextProvider $context
	 * @param PersonalisationSource $source
	 * @return PersonalisationVariation
	 */
	function getVariation(ContextProvider $context, PersonalisationSource $source) {
		$v = null;
		if ($this->VariationPropertyName) {
			// Determine the existing property
			$vs = $context->getProperties(array($this->VariationPropertyName));
			if ($vs && isset($vs[$this->VariationPropertyName][0])) {
				$v = $vs[$this->VariationPropertyName][0]->getValue();
			}

			if (!$v || $v < 0) {
				// there is no value for this property, so create one
				$pick = $this->pickVariation();
				if ($pick) {
					$v = $pick->ID;
					Tracker::track(array($this->VariationPropertyName => $v));
				}
			}
		}

		// At this point, v should be the ID of one of the variations of this scheme, or should be null
		if ($v) {
			return PersonalisationVariation::get()
					->filter("ParentID", $this->ID)
					->filter("ID", $v)
					->First();
		}

		// We could not determine the variation for some reason, so just pick the first one.
		$var = PersonalisationVariation::get()
				->filter("ParentID", $this->ID)
				->First();
		return $var;
	}

	/**
	 * Pick one of the variations of this scheme at random.
	 * @return void
	 */
	function pickVariation() {
		// get all the variations in this scheme
		$vars = PersonalisationVariation::get()
				->filter("ParentID", $this->ID)
				->toArray();

		if (!$vars || count($vars) == 0) return null;
		$rand = mt_rand(0, count($vars) - 1);
		return $vars[$rand];
	}
}
