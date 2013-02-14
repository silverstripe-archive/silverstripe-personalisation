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

	function render(ContextProvider $context, Controller $controller = null) {
		// should be overridden by subclasses.
	}

	static $called = false;

	/**
	 * Get fields. This is a bit hacky to get around ModelAdmin limitations. Basically, if we are adding a new
	 * record, we actually want to return the fields of the specific subclass not PersonalisationVariation itself.
	 * So we look for that condition, and if we get it, we create a subclass instance and return it's fields. It will
	 * in turn call this function, so we need to protect against multiple calls.
	 * @return FieldList
	 */
	function getCMSFields() {
		if (isset($_REQUEST["sc"]) && !self::$called) {
			// just create an instance and return getCMSFields on that
			$subclass = $_REQUEST["sc"];
			$inst = new $subclass;

			// prevent handling this case again.
			self::$called = true;

			// Get the fields from the subclass. It will in turn self::getCMSFields, but that one will return the actual
			// fields of this class.
			return $inst->getCMSFields();
		}

		$fields = parent::getCMSFields();

		$fields->removeByName("ParentID");

		if(isset($_REQUEST["sc"])) {
			$subclass = $_REQUEST["sc"];
			if(ClassInfo::exists($subclass)){
				$className = preg_replace('/(?!^)[[:upper:]]+/',' \0',$subclass);
				$fields->addFieldToTab("Root.Main", new HiddenField("SubClass", "SubClass", $subclass));
				if(isset($_REQUEST["id"])) $fields->addFieldToTab("Root.Main", new HiddenField("ParentID", "ParentID", $_REQUEST["id"]));
				$fields->addFieldToTab("Root.Main", new ReadonlyField("Class", "Variation Type", $className), "Name");
			}
		}
		else {
			$className = preg_replace('/(?!^)[[:upper:]]+/',' \0',$this->ClassName);
			$fields->addFieldToTab("Root.Main", new ReadonlyField("Class", "Variation Type", $className), "Name");
		}
		return $fields;
	}

	//do some stuff to save subclass information
	function onBeforeWrite(){
		parent::onBeforeWrite();
		if(isset($_REQUEST["ParentID"])) $this->ParentID = $_REQUEST["ParentID"];
		if(isset($_REQUEST['SubClass']) && ClassInfo::exists($_REQUEST['SubClass'])){
			$this->setClassName($_REQUEST['SubClass']);
		}
	}

	function onAfterWrite(){
		parent::onAfterWrite();
		if(isset($_REQUEST['SubClass']) && ClassInfo::exists($_REQUEST['SubClass']) && !isset($written)){
			$subclass = $this->newClassInstance($_REQUEST['SubClass']);
			foreach($subclass->db() as $k => $v){
				if(isset($_REQUEST[$k])){
					$subclass->$k = $_REQUEST[$k];
				}
			}
			$written = $subclass->write();
		}
	}
}


