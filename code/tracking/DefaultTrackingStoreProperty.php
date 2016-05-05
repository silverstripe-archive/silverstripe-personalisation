<?php

/**
 * Each object of this class represents a property that can exist in the tracking store. The contents of this table
 * are used to execute metadata queries.
 */
class DefaultTrackingStoreProperty extends DataObject
{

    public static $db = array(
        "PropertyName" => "Varchar(255)",
        "DataType" => "Text"
    );

    /**
     * Given a property name, return the ID of the property if it's defined, or null if it's not.
     * @static
     * @param string $propertyName
     * @return void
     */
    public static function get_id_from_name($propertyName)
    {
        $p = DefaultTrackingStoreProperty::get()->filter("PropertyName", $propertyName)->First();
        if ($p) {
            return $p->ID;
        }
        return null;
    }

    /**
     * Given a propertyName, create a property for it and return it's ID.
     * @static
     * @param string $propertyName
     * @return void
     */
    public static function create_from_name($propertyName)
    {
        $p = new DefaultTrackingStoreProperty();
        $p->PropertyName = $propertyName;
        $p->DataType = "Text";
        $p->write();
        return $p->ID;
    }
}
