<?php

namespace Radowoj\Searcher\SearchResult;

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
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
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


    public function __toString()
    {
        return $this->url;
    }



}
