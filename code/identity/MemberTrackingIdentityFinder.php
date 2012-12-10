<?php

class MemberTrackingIdentityFinder implements TrackingIdentityFinder {

	function find() {
		$user = Member::currentUser();
		if (!$user) return null;   // not logged in, so we can't find anything.

		// if we can find a tracking identity that has already been associated with this user,
		// then we return it. Otherwise we don't know.
		$ident = TrackingIdentity::get()
			->filter("MemberID", $user->ID)
			->First();

		return $ident;
	}

	function onCreate(TrackingIdentity $ident) {
		$user = Member::currentUser();
		if (!$user) return;   // not logged in, so we can't associate.

		$ident->MemberID = $user->ID;
		$ident->write();
	}

}
