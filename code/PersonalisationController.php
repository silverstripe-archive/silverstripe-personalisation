<?php

/**
 * Controller for accessing personalisation externally, e.g. ajax requests
 */
class PersonalisationController extends Controller
{

    public static $allowed_actions = array(
        "fetch"
    );

    /**
     * Return the result of rendering a personalisation scheme. The scheme can be identified by
     * @return void
     */
    // @todo provide control over mime type of the result.
    public function fetch()
    {
        $id = isset($this->urlParams["ID"]) ? $this->urlParams["ID"] : null;
        if (!$id) {
            return $this->httpError("404", "Not found");
        }

        $scheme = PersonalisationScheme::get()->filter("ID", $id)->First();
        if (!$scheme) {
            return $this->httpError("404", "Not found");
        }

        $result = $scheme->personalise($this);
        if (is_object($result)) {
            $result = $result->forTemplate();
        }
        return $result;
    }

    /**
     * Generate a link that can be used by ajax requests that want to personalise using the specified
     * scheme.
     * @static
     * @param $scheme
     * @return void
     */
    public static function calc_ajax_link($scheme)
    {
        return Director::absoluteBaseURL() . "pers/fetch/{$scheme->ID}";
    }
}
