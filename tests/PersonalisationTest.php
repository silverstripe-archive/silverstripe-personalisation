<?php

class PersonalisationTest extends SapphireTest {

	function testDefaultContext() {
		$c = new DefaultContextProvider();
		$a = $c->getProperties(array("foo.bar"));
		$this->assertEquals(count($a), 0, "foo.bar does not exist");

		$a = $c->getProperties("request.method");
		$this->assertEquals(count($a), 1, "request.method exists");
		$this->assertEquals($a["request.method"][0]->getValue(), "GET");
	}

	function testTextVariation() {
		// set up the default context provider with a custom handler.
		$c = new DefaultContextProvider();
		$custom = new PersonalisationTestCustomHandler();
		$c->register_handler($custom);

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

	function testBasicRule() {
		$variation = new TextVariation();
		$variation->Text = "foo";
		$variation->write();

		$context = new DefaultContextProvider();

		// Test that a basic rule with op__always will always return that variation.
		$rule = new BasicPersonalisationRule();
		$rule->VariationID = $variation->ID;
		$cond = new BasicPersonalisationCondition(BasicPersonalisationCondition::$op__always);
		$rule->setCondition(array($cond), true); // writes as well

		$var = $rule->variationOnMatch($context);
		$this->assertTrue($var != null, "got a variation");
		$this->assertEquals($var->render($context), "foo");

		// Test that a single property comparison with a value in the context returns that variation.

		$cond = new BasicPersonalisationCondition(
			BasicPersonalisationCondition::$op__equals,
			BasicPersonalisationValue::make_property("request.method"),
			BasicPersonalisationValue::make_const("GET")
		);
		$rule->setCondition(array($cond), true);

		$var = $rule->variationOnMatch($context);
		$this->assertTrue($var != null, "got a variation for request.method=GET");
		$this->assertEquals($var->render($context), "foo");

		// Test that a single property comparison with a value NOT in the context returns no variation

		$cond = new BasicPersonalisationCondition(
			BasicPersonalisationCondition::$op__equals,
			BasicPersonalisationValue::make_property("request.randomname"),
			BasicPersonalisationValue::make_const("randomvalue")
		);
		$rule->setCondition(array($cond), true);

		$var = $rule->variationOnMatch($context);
		$this->assertTrue($var == null, "got no variation for a non-existent property");
	}
}

class PersonalisationTestCustomHandler implements ContextProvider {

	function getProperties($properties) {
		$result = array();
		foreach ($properties as $name => $def) {
			if ($name == "member.name") $result[$name] = "fred";
			if ($name == "profile.location") $result[$name] = "mars";
		}

		return $result;
	}

	function getMetadata($namespaces = null) {
	}
}
