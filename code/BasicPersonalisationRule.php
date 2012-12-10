<?php

class BasicPersonalisationRule extends DataObject {

	static $db = array(
		"Property" => "Varchar(255)",
		"Operator" => "Enum('Equals,NotEquals', 'Equals')",
		"Value" => "Varchar(255)"
	);

	static $has_one = array(
		"Parent" => "BasicPersonalisation",
		"Variation" => "PersonalisationVariation"
	);

	/**
	 * Return the properties that we will want to read when evaluating this rule. Result is a map of property names
	 * to property defs as understood by the context provider and tracking stores.
	 * @return array
	 */
	function getRequiredProperties() {
		$result = array();
		$result[$this->Property] = array("multiple" => false, "metadata" => false);
		return $result;
	}

	// If the condition on the rule matches, return the variation.
	function variationOnMatch($context) {
		$props = $context->getProperties($this->getRequiredProperties());

		// If the property is not known in the context, the rule doesn't match, as we have nothing to compare
		if (!isset($props[$this->Property])) return null;
		$v = $props[$this->Property];

		$b = false;
		switch ($this->Operator) {
			case "Equals":
				$b = ($v == $this->Value);
				break;

			case "NotEquals":
				$b = ($v != $this->Value);
				break;
		}

		return $b ? $this->Variation() : null;
	}
}
