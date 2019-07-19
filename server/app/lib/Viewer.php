<?php

class Viewer extends RestServer
{

    /**
     * Method to give data with requested extensions
     * .json, .txt, .xhtml, .xml
     * @param $data
     * @return mixed|string
     */
    public function encodedData($data)
    {
        switch ($this->encode)
        {
            case '.json':
                header('Content-Type: application/json');
                return json_encode($data);
                break;
            case '.txt':
                header("Content-type: text/javascript");
                return print_r($data, true);
                break;
            case '.xhtml':
                header('Content-Type: text/html; charset=utf-8');
                $str = '<head></head><body><pre>';
                $str .= print_r($data, true);
                $str .= '</pre></body>';
                return $str;
                break;
            case '.xml':
                header("Content-type: text/xml");
                $xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
                $this->toXml($data, $xml);
                return $xml->asXML();
                break;
        }
    }

    /**
     * Method to convert data to xml format
     * @param $data
     * @param $xml
     */
    
    private function toXml($data, $xml)
    {
        foreach($data as $key=>$val)
        {
        if(is_numeric($key))
        {
            $key = 'book'.$key;
        }
        if(is_array($val))
        {
            $subnode = $xml->addChild($key);
            $this->toXml($val, $subnode);
        }
        else
        {
            $xml->addChild("$key",htmlspecialchars("$val"));
        }
        }
    }
}
