<?php

class VaryingPersonalisationScheme extends PersonalisationScheme {

	static $db = array(
		"ContextProviderClass" => "Varchar(255)",
		"SelectionProviderClass" => "Varchar(255)"
	);

	static $has_many = array(
		"Variations" => "PersonalisationVariation"
	);

	function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName("ContextProviderClass");
		$fields->removeByName("SelectionProviderClass");
		return $fields;
	}


	function personalise(Controller $controller = null) {
		$cp = $this->getContextProvider();
		$sp = $this->getSelectionProvider();

		$var = $sp->getVariation($cp, $this);

		if (is_numeric($var))
			// if we got a number, we're going to treat it as a personalisation variation ID
			$var = PersonalisationVariation::get()->filter("ID", $var)->First();
		else if (is_string($var))
			// if we got a name, we're going to treat it as a personalation variation Name
			$var = PersonalisationVariation::get()->filter("Name", $var)->First();

		if ($var instanceof PersonalisationVariation) {
			if ($var->ParentID != $this->ID) $var = null; // we got a variation but it is not for this scheme.
		}

		if ($var === null) {
			// if we can't determine a variation that is within this scheme, use the default for the scheme, or first.
			$var = $this->Variations()->First();
			if (!$var) return "";   // bah, give up. if the user can't be bothered configuring it, they can't expect magic.
		}

		$this->trackRender($var);

		return $var->render($cp);
	}

	function getContextProvider() {
		if ($this->ContextProviderClass) {
			$c = $this->ContextProviderClass;
			return new $c();
		}
		return new DefaultContextProvider();
	}

	function getSelectionProvider() {
		if ($this->SelectionProviderClass) {
			$c = $this->SelectionProviderClass;
			return new $c();
		}
		return new BasicSelectionProvider();
	}

	public function canCreate($member = null) {
		return false;
	}

	public function getRenderProperty() {
		return "personalisation.measure." . $this->ID . ".render";
	}

	/**
	 * If measurement is enabled for this scheme, then we need to track that this was rendered for the current
	 * identity.
	 * @return void
	 */
	protected function trackRender($var) {
		if (!$this->MeasurementEnabled) return;

		$prop = $this->getRenderProperty();
		Tracker::track(array($prop => $var->ID));
	}
}
