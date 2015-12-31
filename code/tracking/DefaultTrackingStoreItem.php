<?php

/**
 * Represents a single "fact", being a prooerty/value pair with a confidence level.
 */

class DefaultTrackingStoreItem extends DataObject
{

    public static $db = array(
        // The value of this item. This should be interpreted in the context of the data type of the property.
        "Value" => "Text",
        "Confidence" => "Float"
    );

    public static $has_one = array(
        // A reference to the property definition, which gives the property name and the data type. It also reduces
        // storage requirements compared to storing the property name in this table.
        "Property" => "DefaultTrackingStoreProperty"
    );

    public static $many_many = array(
        "Identities" => "DefaultTrackingStoreIdentity"
    );
}
