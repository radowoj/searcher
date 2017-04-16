<?php

namespace Radowoj\Searcher\SearchResult;

use InvalidArgumentException;

class Item implements IItem
{
    protected $url = '';

    protected $title = null;

    protected $description = null;

    public function __construct(array $data)
    {
        $this->fromArray($data);
    }


    protected function fromArray(array $data)
    {
        foreach($data as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException("Invalid search result item property: {$key}");
            }

            $validatorMethod = 'validate' . ucfirst($key);
            if (method_exists($this, $validatorMethod)) {
                $this->{$validatorMethod}($value);
            }

            $this->{$key} = $value;
        }
    }


    public function toArray()
    {
        return [
            'url'           => $this->url,
            'title'         => $this->title,
            'description'   => $this->description,
        ];
    }



    protected function validateUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException("Invalid URL given: {$url}");
        }
    }


    public function __toString()
    {
        return $this->url;
    }



}
