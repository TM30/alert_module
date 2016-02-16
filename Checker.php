<?php

require_once "Querier.php";
require_once "EmailNotifier.php";
require_once "SMSNotifier.php";
require_once "Client.php";

class Checker {

    /**
     * This function checks the status of a given platform and sends notifications where and to whom
     * is appropriate.
     * @param $platform
     * @param $port
     */
    public static function checkStatus($platform, $port)
    {
        $status = self::getPlatformStatus($platform, $port);

        if ( ! $status) {
            //CHECK if each a flag already EXISTS for this platform
            //If a FLAG already exists for this counter
            //Send notification and CLEAR flags....
            //else CREATE a flag for this platform
            //Note 1 - Admin, 2 - Technical 3 - Operational. Messages can thus be customized appropriately.

            $platformStatus = Querier::getInstance()->getStateWithId($platform);

            if ($platformStatus[1] == 1) {

                //Platform Id from table
                $platformId = $platformStatus[0];
                $managers = Querier::getInstance()->getManagers($platformId);

                if($managers) {
                    $sortManagers = array();

                    foreach($managers as $manager) {
                        if ($manager['role'] == 1)
                            $sortManagers['admin'][] = $manager['email'];

                        if ($manager['role'] == 2)
                            $sortManagers['technical'][] = $manager['email'];

                        if ($manager['role'] == 3)
                            $sortManagers['operational'][] = $manager['email'];
                    }

                    //Notify All the administrative people involved.
                    if( ! empty($sortManagers['admin']))
                        EmailNotifier::notify(array(
                            "recipients" => $sortManagers['admin'],
                            "subject" => "{$platform} Is Down..",
                            "message" => "OOps! Looks like something went wrong with {$platform}"
                        ));

                    if( ! empty($sortManagers['technical']))
                        EmailNotifier::notify(array(
                            "recipients" => $sortManagers['technical'],
                            "subject" => "{$platform} Is Down..",
                            "message" => "OOps! Looks like something went wrong with {$platform}"
                        ));

                    if( ! empty($sortManagers['operational']))
                        EmailNotifier::notify(array(
                            "recipients" => $sortManagers['operational'],
                            "subject" => "{$platform} Is Down..",
                            "message" => "OOps! Looks like something went wrong with {$platform}"
                        ));
                }

                Querier::getInstance()->clearFailure($platform);
                echo "Sevas is still off and a notification has been sent to those concerned..";
                return;
            }

            Querier::getInstance()->setFirstFail($platform);
            echo "Sevas is off for the first time...";
            return;
        }
        echo "Sevas is On..";
    }

    /**
     * This application gets Sevas Application Status
     * @return bool|mixed|string
     */
    private static function getPlatformStatus($platform, $port)
    {
        $url = "http://{$platform}.atp-sevas.com:{$port}/sevas/upm";
        if ($uptime = Client::makeCall($url))
            return $uptime;
        return false;
    }
}
