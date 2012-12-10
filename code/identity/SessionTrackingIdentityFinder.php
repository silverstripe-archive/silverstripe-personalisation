<?php

class SessionTrackingIdentityFinder implements TrackingIdentityFinder {

	function find() {
		// See if there is an alias in the session domain for this session ID
		$ident = TrackingIdentityAlias::get()
			->filter("IdentityDomain", TrackingIdentityAlias::$id_domain__session)
			->filter("IdentityValue", session_id())
			->First();
		return $ident;
	}

	function onCreate(TrackingIdentity $ident) {
		// Create an alias against the identity in the session domain with current session ID.
		$alias = new TrackingIdentityAlias();
		$alias->IdentityDomain = TrackingIdentityAlias::$id_domain__session;
		$alias->IdentityValue = session_id();
		Debug::show("session id was " . $alias->IdentityValue);
		Debug::show("cookies are " . print_r($_COOKIE, true));
		$alias->TrackingIdentityID = $ident->ID;
		$alias->write();
	}

}
