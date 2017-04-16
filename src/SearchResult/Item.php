<?php

namespace Radowoj\Searcher\SearchResult;

class Item
{
    protected $url = null;

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

}
