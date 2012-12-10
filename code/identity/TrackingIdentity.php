<?php

/**
 * This represents the identity of a user, to which we can attach whatever we know about that person.
 * Assumptions about identity:
 * - over time, multiple tracking identities may exist for the same person. e.g. accessing a site in non-logged in state
 *   from different devices.
 * - at points in time, such as if a user logs in, we may recognise that two identities are the same, in which case
 *   we can transfer what we know to one identity.
 * - Identities can have aliases, represented in TrackingIdentityAlias.
 */
class TrackingIdentity extends DataObject {

	static $db = array(
	);

	static $has_one = array(
		"Member" => "Member"
	);

	static $has_many = array(
		"Aliases" => "TrackingIdentityAlias"
	);

	// @todo method to clear out old identities that have not been used, except where the member is known,
	// @todo      and might only be to purge certain identities.
}
