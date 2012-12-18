<?php

class CookieTrackingIdentityFinder implements TrackingIdentityFinder {

	static $cookie_name = "SSTC";

	static function set_tracking_cookie_name($name) {
		self::$cookie_name = $name;
	}

	static function get_tracking_cookie_name() {
		return self::$cookie_name;
	}

	/**
	 * The cookie tracker will always create an identity. If one doesn't exist, it creates the cookie and returns
	 * a new ID for it.
	 * @return null|void
	 */
	function findOrCreate() {
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

		$ident = TrackingIdentity::get_identity($this->getType(), $value);
		if (!$ident)
			$ident = TrackingIdentity::create_identity($this->getType(), $value);

		return $ident;
	}

	function generateCookieValue() {
		$generator = new RandomGenerator();
		return $generator->generateHash('sha1');

	}

	function getType() {
		return "cookie";
	}
}
