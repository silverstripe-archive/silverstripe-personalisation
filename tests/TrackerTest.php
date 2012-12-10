<?php

class TrackerTest extends SapphireTest {

	function testDefaultTrackingStore() {
		Tracker::init();

		// get 3 properties. none should exist, but null if not found is set, so all should return the key with value null.
		$props = Tracker::get_properties(array("a", "b", "c"));
		reset($props);
		$this->assertEquals(key($props), "a", "Check not found property is returned as null");
		$this->assertEquals($props["a"], null);

		// get 3 properties. none should exist, but null if not found is false, so no keys should be set.
		$props = Tracker::get_properties(array("a", "b", "c"), null, false);
		$this->assertEquals(count($props), 0);

		Tracker::track(array(
			"a" => "value a",
			"c" => "value c"
		));

		// We should get two back, with b not being present.
		$props = Tracker::get_properties(array("a", "b", "c"), null, false);
		$this->assertEquals(count($props), 2, "check there are 2");
		$this->assertTrue(isset($props["a"]), "check props[a] is set");
		$this->assertTrue(isset($props["c"]), "check props[c] is set");
		$this->assertTrue(!isset($props["b"]), "check props[b] is not set");
		$this->assertEquals($props["a"], "value a", "check props[a] is correct value");
	}
}
