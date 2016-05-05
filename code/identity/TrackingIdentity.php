<?php

/**
 * This represents an identity. This effectively maps (IdentityDomain, IdentityValue) => ID, and is used by
 * identity finders to isolate the physical tracking values (member ID, tracking cookie value) from the tracker,
 * which just sees the integer ID values.
 * Note that a site user may have more than one of these objects,. e.g. one for Member if they log on, one for tracking
 * cookie. These are not merged at this level, and are not understood to be equivalent here. The equivalence is
 * represented in tracking stores.
 */
class TrackingIdentity extends DataObject
{

    public static $db = array(
        // An identifier of the domain of the identity, which is typically an external system that generated the
        // identity value (e.g. perhaps a google tracking ID, if we know it), but can be the SilverStripe instance
        // itself. Logically this is an enum, but we don't know values ahead of time. The values come from getType on
        // the TrackingIdentityFinder instances.
        "IdentityDomain" => "Varchar(50)",
        "IdentityValue" => "Varchar(255)"
    );

    public function getType()
    {
        return $this->IdentityDomain;
    }

    public function getIdentifier()
    {
        return $this->ID;
    }

    /**
     * Get an identity. Return null if there is no match.
     * @static
     * @param $domain
     * @param $id
     * @return
     */
    public static function get_identity($domain, $id)
    {
        return TrackingIdentity::get()
            ->filter("IdentityDomain", $domain)
            ->filter("IdentityValue", $id)
            ->First();
    }

    /**
     * Create an identity and return it.
     * @static
     * @param $domain
     * @param $id
     * @return void
     */
    public static function create_identity($domain, $id)
    {
        $item = new TrackingIdentity();
        $item->IdentityDomain = $domain;
        $item->IdentityValue = $id;
        $item->write();
        return $item;
    }
}
