<?php

class TrackerTest extends SapphireTest {

	function testDefaultTrackingStore() {
		Tracker::init();

		// get 3 properties. none should exist.
		$props = Tracker::get_properties(array("a", "b", "c"));

		reset($props);
		$this->assertTrue(!isset($props["a"]), "Check not found property");

		// get 3 properties. none should exist, but null if not found is false, so no keys should be set.
		$props = Tracker::get_properties(array("a", "b", "c"), null, false);
		$this->assertEquals(count($props), 0);

		Tracker::track(array(
			"a" => "value a",
			"c" => "value c"
		));
$items = DefaultTrackingStoreItem::get()->toArray();
$idents = DefaultTrackingStoreIdentity::get()->toArray();

//echo "all idents" . print_r($idents, true);
//echo "all items: " . print_r($items,true);
//foreach ($items as $item) {
//	echo "item $item->Key has idents:" . print_r($item->Identities()->toArray());
//}
		// We should get two back, with b not being present.
		$props = Tracker::get_properties(array(
			new ContextPropertyRequest(array("name" => "a")),
			new ContextPropertyRequest(array("name" => "b")),
			new ContextPropertyRequest(array("name" => "c"))
		), null, false);

		$this->assertEquals(count($props), 2, "check there are 2");
		$this->assertTrue(isset($props["a"]), "check props[a] is set");
		$this->assertTrue(isset($props["c"]), "check props[c] is set");
		$this->assertTrue(!isset($props["b"]), "check props[b] is not set");
		$this->assertTrue(is_array($props["a"]), "props[a] is an array");
		$this->assertEquals(count($props["a"]), 1);
		$this->assertEquals($props["a"][0]->getValue(), "value a", "check props[a] is correct value");
	}
}
