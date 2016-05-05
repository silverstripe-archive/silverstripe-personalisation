<?php

class MemberTrackingIdentityFinder implements TrackingIdentityFinder
{

    /**
     * Member tracking will create an identity if the user is logged in only. If logged out, returns null.
     * @return null
     */
    public function findOrCreate()
    {
        $user = Member::currentUser();
        if (!$user) {
            return null;
        }   // not logged in, so we can't find anything.

        // if we can find a tracking identity that has already been associated with this user,
        // then we return it. Otherwise we don't know.
        $ident = TrackingIdentity::get_identity($this->getType(), $user->ID);
        if (!$ident) {
            $ident = TrackingIdentity::create_identity($this->getType(), $user->ID);
        }

        return $ident;
    }

    public function getType()
    {
        return "member";
    }
}
