<?php

class Client {

    /**
     * THis makes the call to the API using CURL
     * @param $host
     * @param $username
     * @param $password
     */
    public static function makeCall($url)
    {
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/text'));
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

        $output = curl_exec($process);
        $httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE);

        $errorNumber = curl_errno($process);

        //If fetch is unsuccessful
        if ($errorNumber !== 0 || $httpCode !== 200)
            return false;
        curl_close($process);
        return $output;
    }
}
