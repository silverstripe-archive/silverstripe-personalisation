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
		$fields->removeByName('Variations');

		$variationGridField = GridFieldConfig::create()->addComponents(
			new GridFieldToolbarHeader(),
			new GridFieldDataColumns(),
			new GridFieldPaginator(15),
			new GridFieldEditButton(),
			new GridFieldDeleteAction(),
			new GridFieldDetailForm()
		);

		$variations = PersonalisationAdmin::managed_variation_models();

		foreach($variations as $v){
			$button = new VariationGridFieldAddNewButton();
			$button->setButtonName($v);
			$variationGridField->addComponent($button);
		}

		$variationsField = new GridField('Variations', 'Variations', PersonalisationVariation::get()->filter(array("ParentID" => $this->ID)), $variationGridField);
		$fields->addFieldToTab('Root.Variations', $variationsField);

		$rules = $this->generateRulesList();
		$gridFieldConfig = GridFieldConfig::create()->addComponents(
			new GridFieldToolbarHeader(),
			new GridFieldAddNewButton(),
			new GridFieldDataColumns(),
			new GridFieldPaginator(30),
			new GridFieldEditButton(),
			new GridFieldDeleteAction(),
			new GridFieldDetailForm()
			
		);

		$rulesField = new GridField('DecodedRules', 'Rules', $this->getRules(), $gridFieldConfig);

		// Validation
		if(singleton('BasicPersonalisationRule')->hasMethod('getCMSValidator')) {
			$detailValidator = singleton('BasicPersonalisationRule')->getCMSValidator();
			$rulesField->getConfig()->getComponentByType('GridFieldDetailForm')->setValidator($detailValidator);
		}


		$fields->addFieldToTab('Root.Rules', $rulesField);
		return $fields;
	}

	function generateRulesList() {
		$rules = $this->getRules();
		$html = 'RULE';
		if ($rules) foreach($rules as $rule) {
			$html .= $this->generateRuleHTML(BasicPersonalisationRule::json_decode_typed($rule->EncodedCondition));
			$html .= 'RULE';
		}
		
		return $html;
	}

	/**
	 * this method sorts rules so Default will be always the last one to be displayed
	**/
	function getRules() {
		$arrayRules = new ArrayList();
		$sortedRules = new ArrayList();
		
		if($rules = $this->Rules()->sort('Priority ASC')) foreach($rules as $rule) {
			$arrayRules->push($rule);
		}
		if($arrayRules) foreach($arrayRules as $rule) {
			if($rule->isDefault() === false) {
				$sortedRules->push($rule);
				$arrayRules->remove($rule);
			}
		}
		if($arrayRules->Count() > 0) {
			$sortedRules->push($arrayRules->last());
		}

		return $sortedRules;
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

		$s = $this->customise(array(
				'Rules' => $rulesList
		))->renderWith('GetCmsFieldRule');
		return $s;
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

	function personalise(Controller $controller = null) {
		$cp = $this->getContextProvider();

		$var = $this->getVariation($cp, $this);

		return $var->render(null, $controller);
	}

	function canCreate($member = null) {
		return true;
	}

	function hasDefault() {
		foreach($this->Rules() as $rule) {
			$decodedRule = BasicPersonalisationRule::json_decode_typed($rule->EncodedCondition);
			if(($decodedRule[0]) && ($decodedRule[0]->operator == BasicPersonalisationCondition::$op__always)) return true;
		}
		return false;
	}

}

class VariationGridFieldAddNewButton extends GridFieldAddNewButton {

	public function getHTMLFragments($gridField){
		if(!$this->buttonName) {
			// provide a default button name, can be changed by calling {@link setButtonName()} on this component
			$objectName = singleton($gridField->getModelClass())->i18n_singular_name();
			$this->buttonName = _t('GridField.Add', 'Add {name}', array('name' => $objectName));
		}

		$params = Controller::curr()->request->allParams();

		$data = new ArrayData(array(
			'NewLink' => Controller::join_links($gridField->Link('item', $gridField), 'new') . "?sc=" . $this->buttonName . "&id=" . $params["ID"],
			'ButtonName' => "Add " . preg_replace('/(?!^)[[:upper:]]+/',' \0', $this->buttonName)
		));

		return array(
			$this->targetFragment => $data->renderWith('GridFieldAddNewbutton'),
		);

	}
}

