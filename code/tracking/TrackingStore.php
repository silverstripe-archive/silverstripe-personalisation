<?php

/**
 * Tracker relies on an implementer of this interface to handle the actual storage and retrieval of tracking data.
 */
interface TrackingStore {

	/**
	 * Initialise the store with parameters. This is called immediately after constructing the store.
	 * @abstract
	 * @param $params
	 * @return void
	 */
	function init($params);

	/**
	 * Given an array of property names or ContextPropertyRequests, return a map of property->ContextProperty pairs.
	 * If the store doesn't have a value, it should not return a key for that property; only the properties it has values for.
	 *
	 * For convenience:
	 *	*	A value in the $names array can be just a string, in which case the defaults for properties are used.
	 *	*	If $names is a string, it is treated as an array with a single property, using defaults for the
	 *		property.
	 *
	 * @todo the following examples are not correct now that we've switched to ContextProperty as the return type.
	 * Examples:
	 * 	getProperties("profile.segment")
	 *		might return:
	 * 			array(
	 *				"profile.segment" => "wealthy-rural"
	 * 			)
	 *
	 *  getProperties(array("profile.segment", "profile.region"))
	 * 		might return:
	 * 			array(
	 *				"profile.segment" => "wealth-rural",
	 * 				"profile.region" => "taranaki"
	 * 			)
	 * 		(assuming that the store contains values for both properties.)
	 *
	 * 	getProperties(
	 * 		array("profile.region" => array(
	 *			"multiple" => true
	 * 		))
	 * 	)
	 * 		might return:
	 * 			array(
	 * 				"profile.region" => array(
	 *					"taranaki",	 // most recent first
	 * 					"manawatu",
	 * 					"canterbury"
	 * 				)
	 * 			)
	 *
	 * 	getProperties(
	 * 		array("profile.region" => array(
	 *			"multiple" => true,
	 * 			"metadata" => true
	 * 		))
	 * 	)
	 * 		might return:
	 * 			array(
	 * 				"profile.region" => array(
	 * 					array(
	 *						"value" => "taranaki",
	 * 						"timestamp" => 342423424
	 * 					),
	 * 					array(
	 * 						"value" => "manawatu",
	 * 						"timestamp" => 324134534
	 *					),
	 * 					array(
	 *	 					"value" => "canterbury",
	 * 						"timestamp" => 312532243
	 * 					)
	 *				)
	 * 			)
	 *
	 * Metadata properties may vary by tracking store. The only defined key in the result item is "value".
	 * 
	 * @abstract
	 * @param TrackingIdentity $id  The identity that we want info for
	 * @param mixed $names			A property or array of properties that we want to fetch. Each property is a
	 * 								ContextPropertyRequest. If a string is passed, it is converted to ContextPropertyRequest
	 * 								with default parameters.
	 * @return map
	 */
	function getProperties(TrackingIdentity $id, $names);

	/**
	 * @abstract
	 * @param TrackingIdentity $id		Identity we want to store properties for
	 * @param $properties				Map of property names and values to store for the given identity
	 * @return void
	 */
	function setProperties(TrackingIdentity $id, $properties);

	/**
	 * Merge multiple identities. A one or more identities are merged with a nominated master identity.
	 * @abstract
	 * @param TrackingIdentity $masterIdentity
	 * @param array   array of TrackingIdentity objects to merge with the master identity.
	 */
	function mergeIdentities($masterIdentity, $mergeIdentities);
}
