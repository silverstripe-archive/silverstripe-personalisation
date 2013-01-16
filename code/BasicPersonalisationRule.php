<?php

class BasicPersonalisationRule extends DataObject {

	static $db = array(
		'Title' => 'Varchar(255)',
		// json encoded object represents an array of BasicPersonalisationCondition objects.
		"EncodedCondition" => "Text"
	);

	static $has_one = array(
		"Parent" => "BasicPersonalisation",
		"Variation" => "PersonalisationVariation"
	);

	static $summary_fields = array(
		'NiceDecodedCondition'
	);

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('EncodedCondition');
		$fields->removeByName('ParentID');
		$fields->removeByName('VariationID');
		$fields->addFieldToTab('Root.Main', new RuleEditField('EditEncodedCondition', 'Conditions', $this->EncodedCondition));
		$fields->addFieldToTab('Root.Main', new HiddenField('EncodedCondition', 'EncodedCondition', $this->EncodedCondition));
		return $fields;
	}

	function NiceDecodedCondition() {
		$rules = BasicPersonalisationRule::json_decode_typed($this->EncodedCondition);
		return $this->generateRuleHTML($rules);
	}

	function generateRuleHTML($rules) {

		$rulesList = new ArrayList();
		if ($rules) foreach($rules as $rule) {
			$rulesList->push(new ArrayData(array(
				'Operator' => $rule->operator, 
				'ParamOne' => isset($rule->param1->value) ? $rule->param1->value : null,
				'ParamTwo' => isset($rule->param2->value) ? $rule->param2->value : null
			)));
		}
		
		$html = $this->customise(array(
				'Title' => $this->Title,
				'Rules' => $rulesList
		))->renderWith('GetCmsFieldRule');
		$htmlContent = new SS_HTMLValue($html);
		return $htmlContent;
	}

	/**
	 * Retrieve the condition for this rule. The condition will actually be an array of BasicPersonalisationCondition
	 * objects that have been serialised into EncodedCondition. If there are no conditions, an empty array is returned.
	 * @return void
	 */
	function getCondition() {
		if (!$this->EncodedCondition) return array();
		return self::json_decode_typed($this->EncodedCondition);
	}

	/**
	 * Set the endcoded condition from the given structure. Writes the data object.
	 * @param array $cond		Array of BasicPersonalisationCondition objects.
	 * @return void
	 */
	function setCondition($cond, $write = true) {
		if (!is_array($cond)) throw new Exception("BasicPersonalisationRule::setCondition expects an array of conditions");
		$this->EncodedCondition = self::json_encode_typed($cond);
		if ($write) $this->write();
	}

	/**
	 * Return the properties that we will want to read when evaluating this rule. Result is a map of property names
	 * to property defs as understood by the context provider and tracking stores.
	 * @return array
	 */
	function getRequiredProperties() {
		$result = array();
		$conds = $this->getCondition();
		foreach ($conds as $cond)
			$result = array_merge($result, $cond->getRequiredProperties());
		return $result;
	}

	// If the condition on the rule matches, return the variation.
	function variationOnMatch($context) {
		$conds = $this->getCondition();
		$b = true;
		foreach ($conds as $cond) {
			if (!$cond->IsTrue($context)) {
				$b = false;
				break;
			}
		}

		return $b ? $this->Variation() : null;
	}

	static function _escape($s) {
		return addcslashes($s, "\v\t\n\r\f\"\\/");
	}

	static function json_encode_typed($val) {
//		$_escape = function($s) {
//			return addcslashes($s, "\v\t\n\r\f\"\\/");
//		};

		if (is_null($val)) return "null";
		if (is_bool($val)) return $val ? "true" : "false";
		if (is_string($val)) return "\"" . self::_escape($val) . "\"";
		if (is_object($val)) {
			$vars = get_object_vars($val);
			$a = array();
			$class = get_class($val);
			$a[] = "\"_className\":\"{$class}\"";
			foreach ($vars as $key => $val) {
				$a[] = "\"" . self::_escape($key) . "\":" . self::json_encode_typed($val);
			}
			return "{" . implode($a, ",") . "}";
		}
		if (is_array($val)) {
			$obj = false;
			$a = array();
			foreach($val as $key => $value) {
				if (!is_numeric($key)) $obj = true;
				$a[$key] = self::json_encode_typed($value);
			}
			if ($obj) {
				foreach ($a as $k => $v) {
					$a[$k] = "\"" . self::_escape($k) . "\":" . $v;
				}
				return "{" . implode($a, ",") . "}";
			}
			else {
				return "[" . implode($a, ",") . "]";
			}
		}
		return $val;
	}

	static function json_decode_typed($s) {
		// decode using json_decode, which gives us stdClass for all objects.
		$o = json_decode($s);
		return self::json_decode_typed_normalise($o);
	}

	static function json_decode_typed_normalise($o) {
		if (is_object($o)) {
			// create a new instance
			if (!isset($o->_className)) return $o; // cannot deal with untyped
			$class = $o->_className;
			$new = new $class();
			foreach ($o as $k => $v) {
				$new->$k = self::json_decode_typed_normalise($v);
			}
			return $new;
		}
		if (is_array($o)) {
			$a = array();
			foreach ($o as $item) {
				$a[] = self::json_decode_typed_normalise($item);
			}
			return $a;
		}
		return $o;
	}
}

class BasicPersonalisationCondition {
	static $op__always = "always";			// requires no parameters
	static $op__equals = "eq";				// requires 2 parameters
	static $op__notequals = "ne";			// requires 2 parameters
	static $op__contains = "contains";		// requires 2 string parameters

	// Operator, one of $op__* constants
	var $operator;

	// Up to two parameters to the operation, both are BasicPersonalisationValue objects.
	var $param1;
	var $param2;

	function __construct($operator = null, $param1 = null, $param2 = null) {
		$this->operator = $operator;
		$this->param1 = $param1;
		$this->param2 = $param2;
	}

	function getRequiredProperties() {
		$result = array();
		if ($this->param1) $this->param1->addPropertyRequest($result);
		if ($this->param2) $this->param2->addPropertyRequest($result);
		return $result;
	}

	/**
	 * Determien if this condition is true or not, given the context
	 * @param $context
	 * @return void
	 */
	function IsTrue($context) {
		if ($this->operator == self::$op__always) return true;

		// All other operators require both parameters, so evaluate them now.
		if (!$this->param1 || !$this->param2) throw new Exception("Trying to evaluate a rule that requires 2 parameters, but parameters are missing");
		$p1 = $this->param1->getValue($context);
		$p2 = $this->param2->getValue($context);

		switch ($this->operator) {
			case self::$op__equals:
				if ($p1 == $p2) return true;
				break;

			case self::$op__notequals:
				if ($p1 != $p2) return true;
				break;

			case self::$op__contains:
				if (stripos($p1, $p2) !== FALSE) return true;
				break;
		}
		return false;
	}
}

class BasicPersonalisationValue {
	static $op__literal = "L";
	static $op__property = "P";

	var $kind;
	var $value;

	static function make_const($const) {
		$v = new BasicPersonalisationValue();
		$v->kind = self::$op__literal;
		$v->value = $const;
		return $v;
	}

	static function make_property($property) {
		$v = new BasicPersonalisationValue();
		$v->kind = self::$op__property;
		$v->value = $property;
		return $v;
	}

	function getValue($context) {
		switch ($this->kind) {
			case self::$op__literal:
				return $this->value;
			case self::$op__property:
				$propReq = array(new ContextPropertyRequest(array("name" => $this->value)));
				$props = $context->getProperties($propReq);
				if (!isset($props[$this->value]) || !is_array($props[$this->value]) || count($props[$this->value]) == 0) return null;
				return $props[$this->value][0]->getValue();
		}
	}

	/**
	 * Adds a ContextPropertyRequest object to $array if kind is $op__property. If not, does nothing.
	 * @return void
	 */
	function addPropertyRequest(&$array) {
		if ($this->kind != self::$op__property) return;
		if (isset($array[$this->value])) return; // already set
		$array[$this->value] = new ContextPropertyRequest(array("name" => $this->value));
	}
}

class RuleEditField extends FormField {

	public function __construct($name, $title = null, $value = null) {
		$this->name = $name;

		$this->title = ($title === null) ? $name : $title;

		if($value !== NULL) $this->setValue(BasicPersonalisationRule::json_decode_typed($value));
		parent::__construct($name, $this->title, $value = null);
	}

	public function Field($properties = array()) {
		
		$rules = ($this->Value()) ? $this->Value() : $this->getValue();
		$rulesList = new ArrayList();

		$operatorOptions = array(
			BasicPersonalisationCondition::$op__equals => BasicPersonalisationCondition::$op__equals, 
			BasicPersonalisationCondition::$op__notequals => BasicPersonalisationCondition::$op__notequals, 
			BasicPersonalisationCondition::$op__contains => BasicPersonalisationCondition::$op__contains
		);

		$i = 1;
		if($rules) foreach($rules as $rule) {
			$rulesList->push(new ArrayData(array(
				'Operator' => new DropdownField('Operator_'.$i, '', $operatorOptions, $rule->operator), 
				'ParamOne' => new TextField('Param1_'.$i, '', $rule->param1->value),
				'ParamTwo' => new TextField('Param2_'.$i, '', $rule->param2->value)
			)));
			$i++;
		}
		$defaultCheckbox = new CheckboxField('DefaultOption', '');
		$html = $this->customise(array(
				'Title' => $this->Title,
				'Rules' => $rulesList,
				'DefaultOpt' => $defaultCheckbox
		))->renderWith('EditCMSFieldRule');
		$htmlContent = new SS_HTMLValue($html);
		
		return $htmlContent;
	}

	function getValue() {
		return  BasicPersonalisationRule::json_decode_typed($this->getForm()->getRecord()->EncodedCondition);
	}

}

