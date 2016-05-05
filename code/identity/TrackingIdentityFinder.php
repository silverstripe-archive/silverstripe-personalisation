<?php

interface TrackingIdentityFinder
{

    /**
     * Find the identity given the request as a context. If the identity can't be found but there is enough
     * context to create it, do so.
     * @abstract
     * @return TrackingIdentity		Returns null if identity could not be established
     */
    public function findOrCreate();

    /**
     * Return an identifier for this type of identity. This could use class name, but generally we want something
     * shorter. This value is sent to tracking stores.
     */
    public function getType();
}
