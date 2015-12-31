<?php

class ImageVariation extends PersonalisationVariation
{

    public static $db = array(
        "VariationURL" => "Varchar(255)"
    );

    public static $has_one = array(
        "Image" => "Image"
    );

    public function render(ContextProvider $context, Controller $controller = null)
    {
        return $this->Image();
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $url = new TextField("VariationURL", "Variation URL");
        $fields->push($url);

        if (!$this->ID) {
            $fields->removeByName("Image");
            $imageField = new ReadonlyField('Variation', 'Variation', 'Images can be added after you have saved for the first time');
            $fields->push($imageField);
        }

        return $fields;
    }

    public function helperText()
    {
        return "lets you display an image as the output, with an optional URL to make it clickable.";
    }

    /**
     *
     * @return null|string
     */
    public function getURL()
    {
        if ($this->VariationURL) {
            $url = $this->VariationURL;
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $url = "http://" . $url;
            }
            return $url;
        }
    }
}
