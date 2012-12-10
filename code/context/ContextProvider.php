<?php

/**
 * A ContextProvider is an object that can provide the decision making context for a selection provider. It
 * lets the selection provider retrieve information from the request and possibly other components such as a tracker.
 * It does not directly understand identity; that is handled by a tracker. The representation of data returned from
 * a ContextProvider is the same as a tracking store; a namespaced property-value pair.
 * A ContextProvider can also provide metadata about the properties that are understood, so admin interfaces can
 * introspect what is queryable.
 */
interface ContextProvider {

	// Given a set of property names, receive their values. Behaviour is the same as for TrackingStore::getProperties,
	// except that the tracker works out the identity.
	function getProperties($properties);

	// Return a map of properties that are understood and their type.
	function getMetadata();
}
