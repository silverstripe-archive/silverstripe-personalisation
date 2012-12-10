<?php

interface TrackingIdentityFinder {

	/**
	 * Find the identity give the request as a context.
	 * @abstract
	 * @return TrackingIdentity		Returns null if identity could not be established
	 */
	function find();

	/**
	 * If no finder can determine the identity, a new identity is created. Each finder is then given the
	 * opportunity to associate the state it uses for tracking with the new identity.
	 * @abstract
	 * @param TrackingIdentity $ident
	 * @return void
	 */
	function onCreate(TrackingIdentity $ident);
}
