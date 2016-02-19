<?php

class XMLParser
{
    /**
     * This function parses an XML file from a given file.
     * @param $fileName
     * @return bool|\SimpleXMLElement|string
     */
    public static function parseXMLFromFile($fileName)
    {
            if (!$xml = simplexml_load_string($fileName))
                throw new \Exception('Something went wrong with the status api call. Please check!');
            return $xml;
    }
}
