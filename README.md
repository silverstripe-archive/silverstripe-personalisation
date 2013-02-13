# Personalisation Module

Maintainer contacts:

	* Mark Stephens (mark@silverstripe.com)
	* Carlos Barberis (carlos@silverstripe.com)


## Overview

This module provides personalisation services to a SilverStripe application. **Personalisation schemes** can be set
up in the CMS, and these can be rendered into templates or other UI as desired. Each scheme typically defines
**variations** and **rules** that determine which variation is presented in a given circumstance.

Variations embody presentation behaviours. Some of the built-in variations include:

 *	Render a clickable image
 *	Render text
 *	Render markup from a SilverStripe template

New variations can be defined by the developer.

Rules are executed within a **context**. This is an extendable set of properties with values that the admin user
can use to define the rules. The properties are organised into a namespace. Some properties are predefined by the module,
but typically a site will introduce new properties, via custom context handlers or via the tracker.

The module implements basic **tracking**, and can be made to use the session, a member login or a tracking cookie
to track a user's actions on the site. The tracker exposes a simple API that lets the developer manipulate and
query the information stored in the tracker.

The key interfaces and classes are:

 *	ContextProvider - the key interface that supports retrieving property values for personalisation decision making.
 *	DefaultContextProvider - a default implementor of ContextProvider, and designed to be good for a wide range of uses.
 		It is anticipated that this will be used on all sites that have the module. It's behaviour can be extended
 		with handlers.
 *	Tracker - the key interface for tracking behaviour. Generally this is called for writing only; reading is usually
 		performed through DefaultContextProvider, which asks the tracker for properties when it needs them.
 *	TrackingStore - the key interface that needs to be implemented by anything that can store tracking information.
 		This includes rquerying, storing and getting metadata.
 *	DefaultTrackingStore - a simple implementor of TrackingStore that is suitable for tracking on smaller scale
	(e.g.


## Status

The module should be considered alpha state. Features that are current present include:

 *	A CMS interface for defining basic personalisation schemes.
 *	A set of common properties that can be queried.
 *	An API for adding new properties programmatically.
 *	A tracker that can be configured to use different types of identity for a site user.
 *	A tracking store that can hold tracking information.

Planned features that are not currently present include:

 *	Extra rule base for automatic derivation of new properties in a tracking store, and a process to support that.
 *	Statistics gathering and reporting for variation rendering / clicks.
 *	More sophisticated querying of tracker.
 *	Support for aging of tracker data and varying confidence levels


## Installation

You'll need SilverStripe 3.0 or higher. Put the module directory into the top-level directory of your project as usual,
and perform a dev/build.

A Personalisation tab will appear in the CMS interface. By default it will enable the default tracker, which uses the
SilverStripe database for storage.


## Configuration

The module uses the SilverStripe 3 configuration system, and provides default values for personalisation config items,
which can be overridden by project as required.


### Default Context Provider

DefaultContextProvider, which is the default implementer of ContextProvider, maintains a list of handlers (which
themselves implement ContextProvider). There are two handlers built in, both of which are enabled:

 *	DefaultContextHandler implements a set of properties that expose the current request.
 *	TrackerContextHandler extends this namespace with the Tracker functionality.

The default configuration is in personalisation/_config/default.yml

To define your own context handler, you can

### Tracker

Tracking is performed by the Tracker class. This maintains a list of one or more objects that implement the
TrackingStore interface.

The tracker needs to be initialised before use. This is done by calling:

	Tracker::init();

This will initialise the Tracker and add DefaultTrackingStore as the sole tracking store.

Alternatively, if you want to implement your own TrackingStore, you call initialise the tracker as follows:

	Tracker::init();
	Tracker::self::add_store("mytracker", "MyTrackingStoreClass");



## Module Design

Key information structures are:
 *	**context**, which is a set of namespace property/values.

The key classes and interfaces in the module are as follows:

 *	Personalisation scheme - a holder for personalisation configuration that can be used to generate render output.
 *	ContextProvider - an interface that defines methods for retrieving properties that can be used in determining
 		personalised output. This includes a method for retrieving one or more properties, as well as retrieving
 		metadata about those properties. Notable implementors include DefaultContextProvider and Tracker.
 *  DefaultContextProvider - a class that can pro

The module is built to a set of principles:
 *	Core components have interfaces, so that different components can be replaced if required.
 *	Default implementations of these interfaces are provided and configured so there is reasonable behaviour out of the
    box.
 *	Wherever possible, data and state is provisioned on-demand to minimise overhead.
 *	The set of state that is known about the user is derived from each request.

 *	Each request can carry one or more pieces of state that are used by a **tracking identity finder**
	(TrackingIdentityFinder implementor) in order to understand the **identity** (TrackingIdentity) of the user.
 *	A **tracker** (Tracker) manages state recorded against an identity using one or more **tracking stores**
	(TrackingStore implementers).
 *	When a personalised spot needs to be derived, a **selection provider** (SelectionProvider implementer) is invoked
	to determine the output, which is typically an identifier for an **output variation**.
 *	The selection provider may have arbitrary rules or business logic to make it's output decision.
 *	The selection provider is given a **context provider** which it uses to query what is known about the user.
 *	The context provider in turn derives the data from one or more handlers.
 *	Handlers exists that lets the context provider retrieve data from: the tracker; the raw request; the Member. Custom
  	handlers can also be provided.
 *	If the output of the selection provider identifies an output variation, then the variation is rendered, and returned
 	to the initiating context (e.g. Page where personalised item will appear).


## Custom Context Handlers

A custom handler is a class that implements ContextProvider. It's purpose is to introduce new values into the
property namespace programmatically. For example, you may have a function that maps geolocation info in the request
to a regional office of a company.

The interface has two methods:
 *	getProperties($properties) gets the values of properties that are handled by this handler. The handler only returns
 	values for the properties it understands.
 *	getMetadata($namespaces) returns metadata for the properties that the handler understands.

For example, lets say our project requires personalisation on regional office. We want to introduce a property called
request.regional-office that we can use when we're generating variations. The value will end up being a string.

	class OfficeContextHandler implements ContextProvider {

		static $office_property = "request.regional-office";

		function getProperties($properties) {
			$result = array();
			if (isset($properties[self::$office_property])) {
				$v = $this->getRegionalOfficeFromRequest();

				if ($v) {
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

		// Return the office name, or null if we can't figure it out. We use ipinfodb.com to get lat/lng from IP,
		// and then find the regional office closest to this location. This assumes you have a RegionalOffice
		// class with Lat and Lng properties.
		function getRegionalOfficeFromRequest() {
			// http://api.ipinfodb.com/v3/ip-city/?key=<your_api_key>&ip=74.125.45.100&format=json
			$req = new RestfulService("http://api.ipinfodb.com/v3/ip-city/");
			$req->setQueryString(array('key' => self::get_ip_info_db_key(), 'ip' => $_SERVER['REMOTE_ADDR'], 'format' => 'json'));

			$response = $req->request();
			$data = json_decode($response->getBody());

			$lng = $data->longitude;
			$lat = $data->latitude;

			$sql = "SELECT DISTINCT \"RegionalOffice\".\"ID\", (3959*acos(cos(radians($lat))*cos(radians(\"Lat\"))*cos(radians(\"Lng\")-radians($lng))+sin(radians($lat))*sin(radians(\"Lat\")))) AS distance FROM \"RegionalOffice\" ORDER BY \"distance\" ASC";
			$sqlResult = DB::query($sql);
			$results = $sqlResult->column("ID");

			if(is_array($results)) {
				$obj = DataObject::get_by_id("RegionalOffice", (int)$results[0]);

				$method = self::get_output_method();
				$content = $obj->$method();
				return $content;
			}
			return null; // didn't find one
		}

		function getMetadata($namespaces = null) {
			return array(
				self::$office_property => "Text"
			);
		}
	}

Then, in mysite/_config.php:


	$this->register_handler(new OfficeContextHandler());

Now, whenever personalisation is invoked, and a rule references the property request.regional-office,
getRegionalOfficeFromRequest() will be called and will calculate the value as required. Note that the calculation is
performed on demand only.

## Tracking
