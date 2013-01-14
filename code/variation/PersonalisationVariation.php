<?php

class PersonalisationVariation extends DataObject {

	static $db = array(
		"Name" => "Varchar(255)",
		"Description" => "Text"
	);

	static $has_one = array(
		"Parent" => "VaryingPersonalisationScheme"
	);

	static $summary_fields = array(
		"Name",
		"Description",
		"NiceClassName"
	);

	function NiceClassName(){
		$cn = preg_replace('/(?!^)[[:upper:]]+/',' \0', $this->ClassName);
		return $cn;
	}

	function render(ContextProvider $context) {
		// should be overridden by subclasses.
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		if(isset($_REQUEST["sc"])){
			$subclass = $_REQUEST["sc"];
			if(ClassInfo::exists($subclass)){
				$className = preg_replace('/(?!^)[[:upper:]]+/',' \0',$subclass);
				$fields->addFieldToTab("Root.Main", new HiddenField("SubClass", "SubClass", $subclass));
				$fields->addFieldToTab("Root.Main", new ReadonlyField("Class", "Variation Type", $className), "Name");
				$extraFields = $subclass::addExtraFields();
				$fields->merge($extraFields);
			}
		}else{
			$className = preg_replace('/(?!^)[[:upper:]]+/',' \0',$this->ClassName);
			$fields->addFieldToTab("Root.Main", new ReadonlyField("Class", "Variation Type", $className), "Name");
		}
		return $fields;
	}


	function onBeforeWrite(){
		parent::onBeforeWrite();
		if(isset($_REQUEST['SubClass'])){
			$this->ClassName = $_REQUEST['SubClass'];
		}
	}
}
