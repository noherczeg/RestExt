<?php

namespace Noherczeg\RestExt\Http;


use Illuminate\Support\Facades\Request;

class QueryStringOperations {

    /**
     * Returns with the full Query String but with the given parameter updated by the value we provided if it already
     * existed. If there were no values which match the given one, then it inserts it.
     *
     * @param string $key                  Query String parameter name
     * @param string|int$value                Q. S. param. value
     * @param bool $dontOverwrite   If set to false it will overwrite a param with the same name
     * @return string
     */
    public function setQueryStringParam($key, $value, $dontOverwrite = false)
    {
        $source = $this->getValues();

        $results = [];

        // if there are no other params besides what we would like to set
        if (count($source) == 0) {
            $results[] = $key . '=' . $value;

        } else {

            foreach ($source as $segment) {
                if ($segment['key'] === $key && $dontOverwrite === false)
                    $results[] = $key . '=' . $value;
                else
                    $results[] = $segment['key'] . '=' . $segment['value'];
            }

        }

        return '?' . implode('&', $results);
    }

    /**
     * Creates an array representation (Map) of the Query String parameters present.
     *
     * @return array
     */
    public function getValues()
    {
        $qs = Request::getQueryString();

        $result = [];

        // if we don't have any params at all
        if(strpos($qs, '=') === FALSE)
            return $result;

        // if we have multiple params set
        if(strpos($qs, '&') !== FALSE) {
            foreach (explode('&', $qs) as $segment) {
                $result[] = $this->splitSegment($segment);
            }

            return $result;
        }

        // if we only have one set
        return [$this->splitSegment($qs)];
    }

    /**
     * Splits a Query String parameter into a key and value pair.
     *
     * @param $segment
     * @return array
     */
    private function splitSegment($segment)
    {
        $segmentSplitted = explode('=', $segment);
        return [ 'key' => $segmentSplitted[0], 'value' => $segmentSplitted[1] ];
    }

} 