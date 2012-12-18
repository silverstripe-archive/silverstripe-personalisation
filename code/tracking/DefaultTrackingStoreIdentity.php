<?php

class DefaultTrackingStoreIdentity extends DataObject {

	static $db = array(
		// These correspond to the Identity domain and ID of the TrackingIdentity.
		"IdentityDomain" => "Varchar(50)",
		"IdentityID" => "Int"
	);

	static $belongs_many_many = array(
		"Items" => "DefaultTrackingStoreItem"
	);

	static function find_identity(string $domain, int $id) {
		$item = DefaultTrackingStoreIdentity::get()
			->filter("IdentityDomain", $domain)
			->filter("IdentityID", $id)
			->First();
		return $item;
	}

	/**
	 * Given a map of domain to IDs, locate as many of them as possible and return their ID values in an array.
	 * @static
	 * @param array $ids		Map of domains to TrackingIdentity IDs.
	 * @param boolean $create	Create new identities if they don't exist. Consequently IDs are returned for all
	 * 							input identities
	 * @return IteratorAggregate	Array of int ID values, or an empty array, if $idsOnly is true. Of $idsOnly is false,
	 * 								returns a DataList of the identity objects.
	 */
	static function find_identities(array $ids, $create = false, $idsOnly = true) {
		$conds = array();
		foreach ($ids as $dom => $id) {
			$conds[] = "(\"IdentityDomain\"='$dom' and \"IdentityID\"=$id)";
		}
		$whereClause = implode(" or ", $conds);

		$items = DefaultTrackingStoreIdentity::get()
			->where($whereClause);

		if ($create) {
			// create a map from a hash of domain and identity to the item. We need this so we can identify
			// identities that are in the request but don't exist.
			$m = array();
			foreach ($items as $item) {
				$m[$item->IdentityDomain . ":" . $item->IdentityID] = $item;
			}
			foreach ($ids as $dom => $id) {
				$key = $dom . ":" . $id;
				if (!isset($m[$key])) {
					// identity doesn't current exist, so create it and add it to m
					$item = new DefaultTrackingStoreIdentity();
					$item->IdentityDomain = $dom;
					$item->IdentityID = $id;
					$item->write();
					$m[$key] = $item;
				}
			}

			// At this point, $m should have all properties represented, so drop the keys
			$items = array_values($m);
		}

		if ($idsOnly) {
			// We want only the IDs.
			return array_values($items->map('ID','ID')->toArray());
		}

		return $items;
	}
}
