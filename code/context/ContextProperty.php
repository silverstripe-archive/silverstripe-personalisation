<?php

/**
 * Raw PHP object representing a property value within a specific context.
 */
class ContextProperty
{

    /** @var string $name - Name of the property */
    public $name;

    /** @var mixed $value - value of property within the given context. */
    public $value;

    /** @var int $confidence - value from 0 to 100 indicating percentage confidence in this piece of information. */
    public $confidence;

    public $timestamp;

    public function __construct($a = null)
    {
        if ($a && !is_array($a)) {
            throw new Exception("ContentProperty::__construct expects a map if parameters are provided.");
        }

        $this->timestamp = time();

        foreach ($a as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getValue()
    {
        return $this->value;
    }
}

/**
 * An object that defines a request for data for a property.
 */
class ContextPropertyRequest
{

    /** @var string $name Name of property being requested. */
    public $name;

    /** @var int $minConfidence  	Minimum confidence level - don't return something less than this. */
    public $minConfidence;

    /** @var int $maxRequested 		If an integer greater than 1, return up to the number of values for this property,
     *								most recent first. May be an array of 1 item. Otherwise just returns a single value,
     * 								the most recent.
     */
    public $maxRequested;

    public function __construct($a = null)
    {
        if ($a && !is_array($a)) {
            throw new Exception("ContentPropertyRequest::__construct expects a map if parameters are provided.");
        }

        // Set defaults
        $this->minConfidence = -1;            // no specification; leave it up to the provider to determine.
        $this->maxRequested = 1;

        foreach ($a as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMaxRequested()
    {
        return $this->maxRequested;
    }
}
