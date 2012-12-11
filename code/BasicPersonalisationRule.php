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
		$result = array(new ContextPropertyRequest(array("name" => $this->Property)));
		return $result;
	}

	// If the condition on the rule matches, return the variation.
	function variationOnMatch($context) {
		$props = $context->getProperties($this->getRequiredProperties());

		// If the property is not known in the context, the rule doesn't match, as we have nothing to compare.
		if (!isset($props[$this->Property]) || !is_array($props[$this->Property]) || count($props[$this->Property]) == 0) return null;

		// we just take the first.
		$v = $props[$this->Property][0];

		// check for invalid property. Shouldn't happen, but neither should war or policitians and yet we have to put up with them.
		if (!is_object($v) || !($v instanceof ContextProperty)) return null;

		$v = $v->getValue();
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
