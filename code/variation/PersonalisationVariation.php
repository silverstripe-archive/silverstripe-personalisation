<?php

class PersonalisationVariation extends DataObject
{

    public static $db = array(
        "Name" => "Varchar(255)",
        "Description" => "Text"
    );

    public static $has_one = array(
        "Parent" => "VaryingPersonalisationScheme"
    );

    public static $summary_fields = array(
        "Name",
        "Description",
        "NiceClassName"
    );

    public function NiceClassName()
    {
        $cn = preg_replace('/(?!^)[[:upper:]]+/', ' \0', $this->ClassName);
        return $cn;
    }

    public function render(ContextProvider $context, Controller $controller = null)
    {
        // should be overridden by subclasses.
    }

    public static $called = false;

    /**
     * Get fields. This is a bit hacky to get around ModelAdmin limitations. Basically, if we are adding a new
     * record, we actually want to return the fields of the specific subclass not PersonalisationVariation itself.
     * So we look for that condition, and if we get it, we create a subclass instance and return it's fields. It will
     * in turn call this function, so we need to protect against multiple calls.
     * @return FieldList
     */
    public function getCMSFields()
    {
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

        if ($helperText = $this->helperText()) {
            $helperText = "  - " . $helperText;
        }

        if (isset($_REQUEST["sc"])) {
            $subclass = $_REQUEST["sc"];
            if (ClassInfo::exists($subclass)) {
                $className = preg_replace('/(?!^)[[:upper:]]+/', ' \0', $subclass);
                $fields->addFieldToTab("Root.Main", new HiddenField("SubClass", "SubClass", $subclass));
                if (isset($_REQUEST["id"])) {
                    $fields->addFieldToTab("Root.Main", new HiddenField("ParentID", "ParentID", $_REQUEST["id"]));
                }
                $fields->addFieldToTab("Root.Main", new ReadonlyField("Class", "Variation Type", $className . $helperText), "Name");
            }
        } else {
            $className = preg_replace('/(?!^)[[:upper:]]+/', ' \0', $this->ClassName);
            $fields->addFieldToTab("Root.Main", new ReadonlyField("Class", "Variation Type", $className . $helperText), "Name");
        }

        return $fields;
    }

    /**
     * This returns helper text that is displayed at the top of the editor form for a personaliation variation.
     * Subclasses can override this function if they want to provide helper text.
     * @return string
     */
    public function helperText()
    {
        return "";
    }

    //do some stuff to save subclass information
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (isset($_REQUEST["ParentID"])) {
            $this->ParentID = $_REQUEST["ParentID"];
        }
        if (isset($_REQUEST['SubClass']) && ClassInfo::exists($_REQUEST['SubClass'])) {
            $this->setClassName($_REQUEST['SubClass']);
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if (isset($_REQUEST['SubClass']) && ClassInfo::exists($_REQUEST['SubClass']) && !isset($written)) {
            $subclass = $this->newClassInstance($_REQUEST['SubClass']);
            foreach ($subclass->db() as $k => $v) {
                if (isset($_REQUEST[$k])) {
                    $subclass->$k = $_REQUEST[$k];
                }
            }
            $written = $subclass->write();
        }
    }
}
