<?php

/**
 * Objects of this type represent aliases to an identity.
 */
class TrackingIdentityAlias extends DataObject {

	static $id_domain__self = "SELF";
	static $id_domain__session = "session";   // built-in domain based on PHP session ID
	static $id_domain__cookie = "cookie";     // built-in domain based on tracking cookie

	static $db = array(
		// An identifier of the domain of the identity, which is typically an external system that generated the
		// identity value (e.g. perhaps a google tracking ID, if we know it), but can be the SilverStripe instance
		// itself. Logically this is an enum, but we don't know values ahead of time. The $id_domain__self constant
		// defines the domain for the SilverStripe's tracking ID domain. Any tracking ID's created by the SilverStripe
		// instance are represented this way.
		"IdentityDomain" => "Varchar(50)",
		"IdentityValue" => "Varchar(255)"
	);

	static $has_one = array(
		"TrackingIdentity" => "TrackingIdentity"
	);
}
