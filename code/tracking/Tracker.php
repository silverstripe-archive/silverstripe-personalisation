<?php

/**
 * Tracker class
 *
 * This may be used by external scripts, so should not directly contain framework references.
 * @throws Exception
 *
 */
class Tracker {

	/**
	 * A map of store names to TrackingStoreDef objects.
	 * @var array
	 */
	static $tracking_stores = array();

	/**
	 * A map of finders, which are applied in sequence in order to identify the user.
	 * @var array
	 */
	static $finders = array();

	/**
	 * A map from store names to instances of the store, which are created on demand.
	 * @var array
	 */
	static $store_instances = array();

	/**
	 * Store the TrackingIdentity we find in here.
	 * @var null
	 */
	static $identity_cache = null;

	static function clear_stores() {
		self::$tracking_stores = array();
	}

	/**
	 * Add a tracking store.
	 * @static
	 * @param $class	Class name of tracking store
	 * @param $params	Parameters passed to init on the store.
	 * @return void
	 */
	static function add_store($name, $class, $params = null) {
		self::$tracking_stores[$name] = array(
			"class" => $class,
			"params" => $params
		);
	}

	static function clear_finders() {
		self::$finders = array();
	}

	static function add_finder($name, $finder) {
		self::$finders[$name] = $finder;
	}

	/**
	 * Call this method in mysite/_config.php before setting any Tracker config. Calling without parameters will
	 * set up the tracker with a default tracking store for simplest use.
	 * @static
	 * @param boolean $addDefault	If true, an instance of DefaultTrackingStore will be added.
	 * @return void
	 */
	static function init($addDefault = true) {
		self::clear_stores();
		self::clear_finders();
		if ($addDefault) {
			self::add_finder("member", new MemberTrackingIdentityFinder());
			self::add_store("default", "DefaultTrackingStore");
		}
	}

	/**
	 * Get the TrackingIdentity for this request. This works by iterating over all the identity finders in
	 * order to get the known identities. If more than one identity is returned, then they are assumed to be
	 * the same identity, and we tell the tracking stores this new matching.
	 * @static
	 * @return void
	 */
	static function find_identity() {
		if (!self::$identity_cache) {
			$identities = array();
			foreach (self::$finders as $name => $finder) {
				$identity = $finder->find();
				if ($identity) $identities[$name] = $identity;  // record all the identities we find, associated to the finder name
			}

			switch (count($identities)) {
				case 0:
					// no identities were found
					$identity = new TrackingIdentity();
					$identity->write();

					foreach (self::$finders as $finder) {
						$finder->onCreate($identity);
					}

					self::$identity_cache = $identity;
					break;

				case 1:
					// we found exactly one identity, so use it
					reset($identities);
					$k = key($identities);
					self::$identity_cache = $identities[$k];
					break;

				default:
					// we found multiple identities, so we need to merge them. We keep the top-most identity,
					// as the first identity finder is assumed to be the most specific and reliable.
					// We need to tell each tracking store of this change.
					$masterIdentity = null;
					$mergeIdentities = array();
					foreach ($identities as $name => $identity) {
						if (!$masterIdentity)
							$masterIdentity = $identity;
						else
							$mergeIdentities[] = $identity;
					}

					foreach (self::$tracking_stores as $name => $def) {
						$storeInst = self::get_store_inst($name);
						$storeInst->mergeIdentities($masterIdentity, $mergeIdentities);
					}

					break;
			}
		}
		return self::$identity_cache;
	}

	/**
	 * Track some properties. This adds the properties to a store.
	 * @static
	 * @param $properties		Set of properties to set. A map of property names to values.
	 * @param null $storeName	Name of tracking store to send to. If not provided, will store to the first in the list.
	 * @return void
	 */
	static function track($properties, $storeName = null) {
		if (count(self::$tracking_stores) == 0)
			throw new Exception("Attempting to add to tracking store, but none are configured.");

		if ($storeName) {
			if (!isset(self::$tracking_stores[$storeName]))
				throw new Exception("Attempting to add to tracking store called '$storeName' which is not configured.");
		}
		else {
			reset(self::$tracking_stores);
			$storeName = key(self::$tracking_stores);
		}

		$inst = self::get_store_inst($storeName);
		$trackingId = self::find_identity();
		$inst->setProperties($trackingId, $properties);
	}

	/**
	 * Get the instance of the named tracking store. This is created and initialised on demand.
	 * @static
	 * @param $name
	 * @return void
	 */
	static function get_store_inst($name) {
		if (!isset(self::$store_instances[$name])) {
			// Create and initialise the store instance
			if (!isset(self::$tracking_stores[$name]))
				throw new Exception("Trying to get a tracking store instance called '$name', but it is not configured.");
			$def = self::$tracking_stores[$name];
			$c = $def["class"];
			$inst = new $c();
			$inst->init($def["params"]);
			self::$store_instances[$name] = $inst;
		}

		return self::$store_instances[$name];
	}

	/**
	 * Retrieve a set of properties from tracking stores. If a store is named, then properties are only retrieved
	 * from that store. If not, the stores are attempted in order.
	 * @todo Consider having the properties having a weight, and using that in preference.
	 * @static
	 * @param $properties
	 * @param null $store
	 * @return void
	 */
	static function get_properties($properties, $store = null, $notFoundNulled = false) {
		if (!is_array($properties)) $properties = array($properties);

		$trackingId = self::find_identity();

		$result = array();
		foreach (self::$tracking_stores as $name => $def) {
			if ($store && $store != $name) continue;

			$storeInst = self::get_store_inst($name);
			$r = $storeInst->getProperties($trackingId, $properties);
			$result = array_merge($result, $r);
		}

		if ($notFoundNulled) {
			// for any properties that we couldn't determine, provide null.
			foreach ($properties as $prop) {
				if (!isset($result[$prop])) $result[$prop] = null;
			}
		}
		return $result;
	}

	/**
	 * Require tracking js. The JS needs to work in statically cached environment,
	 * @param $properties array				A map of property value pairs that should be tracked
	 * @param $store string					Store identifier (optional)
	 * @param $includeIdentity boolean      If true, the JS will contain the identity of the current user.
	 * 										If false, it doesn't include it (default). Identity will be established
	 * 										by identity finders. If using tracking cookies, the identifying cookie
	 * 										will be present on the tracking request. Must be false is statically
	 * 										cached environments.
	 */
	static function trackingJS($properties, $store = null, $includeIdentity = false) {
		// @todo implement: generate a property bag and encode it, put in tracker URL
		// @todo implement: need to know the URL of the controller that processes the tracking request. Is this
		// @todo implement:    just a script?
	}
}
