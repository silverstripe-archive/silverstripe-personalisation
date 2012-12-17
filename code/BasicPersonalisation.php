<?php

class BasicPersonalisation extends VaryingPersonalisationScheme implements SelectionProvider {

	static $has_many = array(
		"Rules" => "BasicPersonalisationRule"
	);

	function getCMSFields() {
		Requirements::css('personalisation/css/personalisationAdmin.css');
		Requirements::javascript('personalisation/javascript/personalisationAdmin.js');
		$fields = parent::getCMSFields();

		$fields->removeByName('Rules');

		$rules = $this->generateRulesList();
		$gridFieldConfig = GridFieldConfig::create()->addComponents(
			new GridFieldToolbarHeader(),
			new GridFieldAddNewButton(),
			new GridFieldDataColumns(),
			new GridFieldPaginator(15),
			new GridFieldEditButton(),
			new GridFieldDeleteAction(),
			new GridFieldDetailForm()
			
		);
		$rulesField = new GridField('DecodedRules', 'Rules', $this->Rules(), $gridFieldConfig);
		$fields->addFieldToTab('Root.Rules', $rulesField);
		return $fields;
	}

	function generateRulesList() {
		$rules = $this->Rules();

		$html = 'RULE';
		foreach($rules as $rule) {
			$html .= $this->generateRuleHTML(BasicPersonalisationRule::json_decode_typed($rule->EncodedCondition));
			$html .= 'RULE';
		}
		return $html;
	}

	function generateRuleHTML($rules) {

		$rulesList = new ArrayList();
		foreach($rules as $rule) {
			
			$rulesList->push(new ArrayData(array(
				'Operator' => $rule->operator, 
				'ParamOne' => $rule->param1->value,
				'ParamTwo' => $rule->param2->value
			)));
		}


		return $this->customise(array(
				'Rules' => $rulesList
		))->renderWith('GetCmsFieldRule');
	}

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

