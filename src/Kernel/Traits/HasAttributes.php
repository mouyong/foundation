<?php

namespace Yan\Foundation\Kernel\Traits;

use Yan\Foundation\Kernel\Exceptions\InvalidArgumentException;
use Yan\Foundation\Kernel\Support\Arr;
use Yan\Foundation\Kernel\Support\Str;

trait HasAttributes
{

    protected $attributes = [];

    protected $snakeable = true;

    public function setAttributes(array $attributes = [])
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setAttribute($attribute, $value)
    {
        Arr::set($this->attributes, $attribute, $value);
        return $this;
    }

    public function getAttribute($attribute, $default = null)
    {
        return Arr::get($this->attributes, $attribute, $default);
    }

    public function isRequired($attribute)
    {
        return in_array($attribute, $this->getRequired(), true);
    }

    public function getRequired()
    {
        return property_exists($this, 'required') ? $this->required : [];
    }

    public function with($attribute, $value)
    {
        $this->snakeable && $attribute = Str::snake($attribute);
        $this->setAttribute($attribute, $value);
        return $this;
    }

    public function set($attribute, $value)
    {
        $this->setAttribute($attribute, $value);
        return $this;
    }

    public function get($attribute, $default = null)
    {
        return $this->getAttribute($attribute, $default);
    }

    public function has(string $key)
    {
        return Arr::has($this->attributes, $key);
    }

    public function merge(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function only($keys)
    {
        return Arr::only($this->attributes, $keys);
    }

    public function all()
    {
        $this->checkRequiredAttributes();
        return $this->attributes;
    }

    public function __call($method, $args)
    {
        if (0 === stripos($method, 'with')) {
            return $this->with(substr($method, 4), array_shift($args));
        }
        throw new \BadMethodCallException(sprintf('Method "%s" does not exists.', $method));
    }

    public function __get($property)
    {
        return $this->get($property);
    }

    public function __set($property, $value)
    {
        return $this->with($property, $value);
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    protected function checkRequiredAttributes()
    {
        foreach ($this->getRequired() as $attribute) {
            if (is_null($this->get($attribute))) {
                throw new InvalidArgumentException(sprintf('"%s" cannot be empty.', $attribute));
            }
        }
    }
}