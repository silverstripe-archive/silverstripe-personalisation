<?php

class CookieTrackingIdentityFinder implements TrackingIdentityFinder {

	static $cookie_name = "SSTC";

	static function set_tracking_cookie_name($name) {
		self::$cookie_name = $name;
	}

	static function get_tracking_cookie_name() {
		return self::$cookie_name;
	}

	function find() {
		if (!isset($_COOKIE[self::$cookie_name])) return null;

		// See if there is an alias in the cookie domain for this session ID
		$ident = TrackingIdentityAlias::get()
			->filter("IdentityDomain", TrackingIdentityAlias::$id_domain__cookie)
			->filter("IdentityValue", $_COOKIE[self::$cookie_name])
			->First();
		return $ident;
	}

	function onCreate(TrackingIdentity $ident) {
		if (isset($_COOKIE[self::$cookie_name])) {
			$value = $_COOKIE[self::$cookie_name];
		}
		else {
			// the cookie doesn't exist, so create the cookie with a value, and use that value
			$value = $this->generateCookieValue();
			if (!SapphireTest::is_running_test()) {
				setcookie(self::$cookie_name, $value, time() + 60 * 60 * 24 * 90, "/");
			}
		}

		// Create an alias against the identity in the session domain with current session ID.
		$alias = new TrackingIdentityAlias();
		$alias->IdentityDomain = TrackingIdentityAlias::$id_domain__cookie;
		$alias->IdentityValue = $value;
		$alias->TrackingIdentityID = $ident->ID;
		$alias->write();
	}

	function generateCookieValue() {
		$generator = new RandomGenerator();
		return $generator->generateHash('sha1');

	}
}
