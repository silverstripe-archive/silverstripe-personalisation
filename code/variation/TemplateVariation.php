<?php

class TemplateVariation extends PersonalisationVariation{

	static $db = array(
		"TemplateName" => "Varchar"
	);

	function getCMSFields() {
		$fields = parent::getCMSFields();

		$tempManifest = new SS_TemplateManifest(THEMES_PATH);
		$templates = $tempManifest->getTemplates();

		$tf = array();
		foreach($templates as $k => $v){
			if(isset($v['themes'])) array_push($tf, $k);
		}
		$templateField = new DropDownField("TemplateName", "Template Name", $tf);

		$fields->push($templateField);

		return $fields;
	}

	function render(ContextProvider $context, Controller $controller = null) {
		$templateName = $this->TemplateName;

		if(!is_null($controller) && SSViewer::hasTemplate(array($templateName))){
			return $controller->renderWith(array($templateName));
		}else{
			return null;
		}
	}
}