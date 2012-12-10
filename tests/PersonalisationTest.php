<?php

class PersonalisationTest extends SapphireTest {

	function testDefaultContext() {
		$c = new DefaultContextProvider();
		$a = $c->getProperties(array("foo.bar" => array()));
		$this->assertEquals(count($a), 0, "foo.bar does not exist");

		$a = $c->getProperties("request.method");
		$this->assertEquals(count($a), 1, "request.method exists");
		$this->assertEquals($a["request.method"], "GET");
	}

	function testTextVariation() {
		// set up the default context provider with a custom handler.
		$c = new DefaultContextProvider();
		$c->register_handler(array($this, "customHandler"));

		// substitution of two vars
		$var = new TextVariation();
		$var->Text = "Hello {{member.name}} from {{profile.location}}";

		$this->assertEquals($var->render($c), "Hello fred from mars");

		// substitution at start of string
		$var->Text = "{{member.name}} woz here";
		$this->assertEquals($var->render($c), "fred woz here");

		// substitution at end of string
		$var->Text = "we like {{member.name}}";
		$this->assertEquals($var->render($c), "we like fred");

		// substitution with no matching property
		$var->Text = "foo {{profile.doesnt.exist}}";
		$this->assertEquals($var->render($c), "foo ");
	}

	/**
	 * A custom handler to facilitate test cases.
	 */
	function customHandler($properties) {
		$result = array();
		foreach ($properties as $name => $def) {
			if ($name == "member.name") $result[$name] = "fred";
			if ($name == "profile.location") $result[$name] = "mars";
		}

		return $result;
	}
}
