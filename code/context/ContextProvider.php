<?php

/**
 * A ContextProvider is an object that can provide the decision making context for a selection provider. It
 * lets the selection provider retrieve information from the request and possibly other components such as a tracker.
 * It does not directly understand identity; that is handled by a tracker. The representation of data returned from
 * a ContextProvider is the same as a tracking store; a namespaced property-value pair.
 * A ContextProvider can also provide metadata about the properties that are understood, so admin interfaces can
 * introspect what is queryable.
 */
interface ContextProvider
{

    /**
     * Given a set of property names, return their values. Behaviour is the same as for TrackingStore::getProperties,
     * except that the tracker works out the identity. If a property is unknown or a value cannot be found that satisfies
     * constraints of the request, no key of that property should be returned. If none of the properties can be found,
     * an empty array is returned.
     * @abstract
     * @param $properties array of mixed - properties to fetch. Elements of this array can either just be
     * 					string value, or ContextPropertyRequest value.
     * @return map of property names to an array ContextProperty objects.
     */
    public function getProperties($properties);

    /**
     * Return metadata about what's known. Returns a map associating property name to a DBField instance. The instance
     * only has a default value, but it's class can be used to determine info about the class. This is esp the case with
     * enums, which will have values enumerated. Instances are typically from this set: Boolean, Date, Datetime, Enum,
     * Float, Int, Text (not Varchar)
     * 
     * @abstract
     * @param mixed $namespaces		If provided, can be a name or array of names that are used to filter the total
     * 								list of name spaces. The values in $namespace are assumed to be a complete word or
     * 								words of a name space. e.g. to limit the metadata to just "request.*" fields, pass in
     * 								"request". However, if properties exist in "request_foo.*, these are not returned. i.e.
     * 								the dot is significant.
     * @return map of property names to DBField instances.
     */
    public function getMetadata($namespaces = null);
}
