<?php

namespace AwsCloudSearch\Response;

class SearchResponse extends AbstractResponse
{
    private $searchedFields;

    private $hits;

    public function __construct(Array $data)
    {
        parent::__construct($data);

        if (isset($data['returnFields'])) {
            $this->searchedFields = $data['returnFields'];
        }
        else {
            $this->searchedFields = array('id');
        }
    }

    public function parseResponse()
    {
        parent::parseResponse();

        if (!$this->wasSuccessful()) {
            return;
        }

        $hits = $this->parsedData->hits->hit;

        $returnHits = array();
        foreach ($hits as $hit) {
            if (isset($hit->data)) {
                $returnHits[$hit->id] = $hit->data;
            }
            else {
                $returnHits[$hit->id] = $hit->id;
            }
        }

        foreach ($hits as $key => $hit) {
            if (is_object($hit)) {
                foreach ($hit as $dataKey => $data) {
                    $hits[$key]->{$dataKey} = $data[0];
                }
            }
        }

        $this->hits = $hits;
    }

    public function getHitDocuments()
    {
        if (!$this->wasSuccessful()) {
            throw new \Exception('Unsuccessful search can not return an array');
        }

        return $this->hits;
    }

    public function __toString()
    {
        if ($this->wasSuccessful()) {
            $returnString = '';

            $searchField = $this->searchedFields[0];

            foreach ($this->parsedData->hits->hit as $hit) {
                $returnString .= $hit->data->{$searchField} . ', ';
            }
            $returnString = rtrim($returnString, ', ');
            return $returnString;
        }
        else {
            return 'No search results';
        }
    }
}