<?php

/**
 * This variation lets the web site admin nest a scheme. The scenario is perhaps a personalised home page,
 * where A/B testing is required for one of the personalised options only (e.g. testing response a customer segment).
 * So for one of the variations, an A/B testing variation invoked.
 */
class NestedVariation extends PersonalisationVariation
{

    public static $has_one = array(
        "Scheme" => "PersonalisationScheme"
    );

    public function render(ContextProvider $context, Controller $controller = null)
    {
        if (!$this->SchemeID) {
            return "";
        }
        return $this->Scheme()->personalise($controller);
    }

    public function helperText()
    {
        return "lets you use another scheme as the output, e.g. to A/B test one of your variations.";
    }
}
