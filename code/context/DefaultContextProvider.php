<?php

class DefaultContextProvider implements ContextProvider {

	/**
	 * A cache that maps cache keys derived from a property request to the value or values from fetching that
	 * request. The value or values are ContextProperty objects.
	 * @var array
	 */
	protected $propertyCache = array();

	/**
	 * A list of handlers that the context provider uses to determine values that are requested. Each of these is
	 * an instance that implements ContextProvider. This is initialised on demand by getHandlers().
	 * @var array
	 */
	protected $handlers = array();

	/**
	 * Gets the list of handlers. The handler list is populated on demand from the Config system. It uses
	 * register_handler() to register the handlers that are defined.
	 * @return void
	 */
	protected function getHandlers() {
		if (!$this->handlers) {
			$config = Config::inst();
			$h  = $config->get("DefaultContextProvider", "ContextHandlers");
			if (!$h) throw Exception("Personalisation module is mis-configured. There must be at least one context handler.");
			foreach ($h as $class => $enabled) {
				if ($enabled) {
					$hi = new $class();
					$this->register_handler($hi);
				}
			}
		}
		return $this->handlers;
	}

	/**
	 * Given a set of properties, return their values where known.
	 * For each property that needs to be fetched, the property is also cached for this request.
	 * The cache key includes a hash of the property defs (e.g. if multiple items requested).
	 * Handlers are invited to return all properties, one at a time.
	 * @returns array	Returns a map of property names to values. See TrackingStore::getProperties
	 */
	function getProperties($properties, $useCache = true) {
		//print_r($properties);
		if (!is_array($properties)) $properties = array($properties);

		$result = array();

		// Normalise properties, makes things easier later. We make it a map of property name to ContextPropertyRequest.
		$np = array();
		foreach ($properties as $property) {
			if (is_string($property)) {
				$np[$property] = new ContextPropertyRequest(array("name" => $property));
			}
			else if (is_object($property) && $property instanceof ContextPropertyRequest)
				$np[$property->getName()] = $property;
			else {
				die(print_r($properties,true));
				throw new Exception("DefaultContextProvider::getProperties(): each property must be either a string or ContextPropertyRequest");
			}
		}

		// First, determine if any of the properties requested are in the cache, if caching is being used.
		if ($useCache) {
			foreach ($np as $name => $req) {
				$cacheKey = $this->getCacheKey($name, $req);

				if (isset($this->propertyCache[$cacheKey])) $result[$name] = $this->propertyCache[$cacheKey];
			}
		}

		// Iterate over the handlers to fetch any properties that are not in result. When we get some back, we
		// add to results, and the next handler will be asked for only what remains. Also, for each result we get
		// back, we add it to the cache.
		foreach ($this->getHandlers() as $h) {
			// get the properties we haven't already got.
			$request = array_diff_key($np, $result);

			if (count($request) == 0) break; // we have all properties requested, no need to keep looking.

			$v = $h->getProperties($request);

			// Add responses from handler to result and to cache
			$result = array_merge($result, $v);
			if ($useCache) {
				foreach ($v as $propertyName => $value) {
					$cacheKey = $this->getCacheKey($name, $np[$propertyName]);
					$this->propertyCache[$cacheKey] = $value;
				}
			}
		}

		return $result;
	}

	function getCacheKey($name, $def) {
		return sha1($name . ":" . json_encode($def));
	}

	// Return a map of properties that are understood and their type.
	function getMetadata($namespaces = null) {
		$result = array();

		if (!$namespaces) $namespaces = "*";
		if (!is_array($namespaces)) $namespaces = array($namespaces);

		foreach ($this->getHandlers() as $h) {
			$result = array_merge($result, $h->getMetadata($namespaces));
		}

		return $result;
	}

	/**
	 * Register a context handler. Generally this is not called directly, but from getHandlers() which reads
	 * the config on demand and registers the handlers that way. However, this function can also be used by
	 * unit tests to bypass the config system.
	 * @param $handler
	 * @param string $place
	 * @return void
	 */
	function register_handler($handler, $place = "end") {
		if ($place == "end") $this->handlers[] = $handler;
		else array_unshift($this->handlers, $handler);
	}
}

class DefaultContextHandler implements ContextProvider {

	function getProperties($properties) {
		$result = array();
		foreach ($properties as $name => $def) {
			$parts = explode(".", $name);

			if (count($parts) < 1) throw Exception("invalid property name: " . $name);
			switch ($parts[0]) {
				case "browser":
					$v = $this->getBrowserProperty($parts);
					break;

				case "request":
					$v = $this->getRequestProperty($parts);
					break;

				case "cookie":
					$v = $this->getRequestCookie($parts);
					break;

				case "get":
					$v = $this->getQueryVar($parts);
					break;

				case "location":
					$v = $this->getLocationProperty($parts);
					break;

				default:
					$v = null;
			}

			if ($v !== null) {
				$v = new ContextProperty(array(
					"name" => $name,
					"value" => $v,
					"confidence" => 100  // we're completely sure of the request.
				));
				$v = array($v);			// always an array, even for single values
				$result[$name] = $v;
			}
		}

		return $result;
	}

	function getBrowserProperty($parts) {
		// @todo implement DefaultContextHandler::getBrowserProperty
	}

	function getRequestProperty($parts) {
		$serverProps = array(
			"method" => "REQUEST_METHOD",
			"referer" => "HTTP_REFERER",
			"referrer" => "HTTP_REFERER"
		);

		if (count($parts) < 2) return null;
		if (count($parts) == 2 && array_key_exists($parts[1], $serverProps)) {
			$k = $serverProps[$parts[1]];
			if (isset($_SERVER[$k])) return $_SERVER[$k];
		}

		return null;
	}

	function getRequestCookie($parts) {
		if (count($parts) < 2) return null;
		if (!isset($_COOKIE[$parts[1]])) return null;
		return $_COOKIE[$parts[1]];
	}

	function getQueryVar($parts) {
		if (count($parts) != 2) return null;
		if (!isset($_REQUEST[$parts[1]])) return null;
		return $_REQUEST[$parts[1]];
	}

	static $metadata = array(
		"request.method"	=> "Enum('GET,POST,PUT,DELETE','GET')",
		"request.referer"	=> "Text",
		"request.url"		=> "Text",
		"cookie.*"			=> "Text",
		"get.*"				=> "Text",
		"location.*"		=> "Text"			// @todo expand location.* properties
	);

	function getMetadata($namespaces = null) {
		$result = array();

		foreach ($namespaces as $ns) {
			// force match to full namespace components
			if (substr($ns, -1) != ".") $ns .= ".";

			foreach (self::$metadata as $property => $def) {
				if ($ns == "*." || substr($property, 0, strlen($ns)) == $ns) {
					// this property matches up to the length of the name space
					$inst = Object::create_from_string($def);
					$result[$property] = $inst;
				}
			}
		}
		return $result;
	}
}


class DefaultBrowserHandler implements ContextProvider{

	protected $userAgent;

	static $metadata = array(
		"browser.type" => "Text",
		"browser.form" => "Text",
		"browser.os" => "Text"
	);

	function getProperties($properties){
		$result = array();
		foreach ($properties as $name => $def) {
			switch ($name) {
				case "browser.type":
					$v = $this->getBrowserType();
					break;

				case "browser.form":
					$v = $this->getBrowserForm();
					break;

				case "browser.os":
					$v = $this->getOS();
					break;

				default:
					$v = null;
			}

			if ($v !== null) {
				$v = new ContextProperty(array(
					"name" => $name,
					"value" => $v,
					"confidence" => 100  // we're completely sure of the request.
				));
				$v = array($v);			// always an array, even for single values
				$result[$name] = $v;
			}
		}

		return $result;

	}


	function getMetadata($namespaces = null){
		$result = array();
		foreach ($namespaces as $ns) {
			// force match to full namespace components
			if (substr($ns, -1) != ".") $ns .= ".";

			foreach (self::$metadata as $property => $def) {
				if ($ns == "*." || substr($property, 0, strlen($ns)) == $ns) {
					// this property matches up to the length of the name space
					$inst = Object::create_from_string($def);
					$result[$property] = $inst;
				}
			}
		}
		return $result;
	}

	function getBrowserType(){
		if(!$this->userAgent){
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}

		$bh = new BrowserHelper();
		switch(true){
			case($bh::is_firefox($this->userAgent)): return "firefox";
			case($bh::is_msie($this->userAgent)): return "msie";
			case($bh::is_opera($this->userAgent)): return "opera";
			case($bh::is_safari($this->userAgent)): return "safari";
			default: return "unknown";
		}
	}

	function getBrowserForm(){
		if(!$this->userAgent){
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}

		$bh = new BrowserHelper();
		switch(true){
			case($bh::is_tablet($this->userAgent)): return "tablet";
			case($bh::is_mobile($this->userAgent)): return "mobile";
			case($bh::is_desktop($this->userAgent)): return "desktop";
			default: return "unknown";
		}
	}

	function getOS(){
		if(!$this->userAgent){
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}

		$bh = new BrowserHelper();
		switch(true){
			case($bh::is_iphone($this->userAgent)): return "iOS";
			case($bh::is_OSX($this->userAgent)): return "OSX";
			case($bh::is_android($this->userAgent)): return "android";
			case($bh::is_windows($this->userAgent)): return "windows";
			case($bh::is_win_phone($this->userAgent)): return "windows phone";
			default: return "unknown";

		}
	}
}

class TrackerContextHandler implements ContextProvider {

	function getProperties($properties) {
		return Tracker::get_properties($properties, $store = null);
	}

	function getMetadata($namespaces = null) {
		return Tracker::get_metadata($namespaces);
	}
}