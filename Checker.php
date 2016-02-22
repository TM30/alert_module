<?php

require_once "Querier.php";
require_once "XMLParser.php";
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
    public static function checkStatus($platformName)
    {
        //This fetches the details for this platform.
        $platformData = Querier::getInstance()->getPlatformDetails($platformName);

        $appStatus = self::getAppStatus($platformData['sev_app']);
        $tempBindStatus = self::getBindStatusFromXML($platformName);

        $sevasState = 0;
        $otherStatuses = array(
            'bl_flag' => 0,
            "bc_flag" => 0
        );

        if ( ! $appStatus) {
            //CHECK if each a flag already EXISTS for this platform
            //If a FLAG already exists for this counter
            //Send notification and CLEAR flags....
            //else CREATE a flag for this platform
            //Note 1 - Admin, 2 - Technical 3 - Operational. Messages can thus be customized appropriately.

            //This sends alert based on the present state of sevas..
            if ($platformData['sev_flag'] > 0) {
                EmailNotifier::notify(array(
                    "recipients" => array($platformData['gen_admin_email'], $platformData['tech_admin_email']),
                    "subject" => "Sevas is Down",
                    "message" => "Sevas is currently down for {$platformName}.. Please kindly look into this.."
                ));
                echo "Mail sent1...";
            } else {
                $sevasState = 1;
            }
        }

        //This sends alert based on the present state of broadcast..
        if (! empty($tempBindStatus['broadcastIsDown'])) {
            if ($platformData['bc_flag'] > 0) {
                $binds = implode(", ", $tempBindStatus['broadcastIsDown']);
                EmailNotifier::notify(array(
                    "recipients" => array($platformData['gen_admin_email'], $platformData['tech_admin_email']),
                    "subject" => "Broadcast is Down",
                    "message" => "Broadcast is currently down for {$binds} on {$platformName}.. Please kindly look into this.."
                ));
                echo "Mail sent 2...";
            } else  {
                $otherStatuses['bc_flag'] = 1;
            }
        }

        //This sends alert based on the present state of billing..
        if (! empty($tempBindStatus['billingIsDown'])) {
            if ($platformData['bl_flag'] > 0) {
                $binds = implode(", ", $tempBindStatus['billingIsDown']);
                EmailNotifier::notify(array(
                    "recipients" => array($platformData['gen_admin_email'], $platformData['tech_admin_email']),
                    "subject" => "Billing  is Down",
                    "message" => "Billing is currently down for {$binds} on {$platformName}.. Please kindly look into this.."
                ));
                echo "Mail sent 3...";
            } else  {
                $otherStatuses['bl_flag'] = 1;
            }
        }

        //This sends alert based on the present state of broadcast..
        if (! empty($tempBindStatus['broadcastIsInActive'])) {
            $binds = implode(", ", $tempBindStatus['broadcastIsInActive']);
            EmailNotifier::notify(array(
                "recipients" => array($platformData['gen_admin_email'], $platformData['ops_admin_email']),
                "subject" => "INactive Broadcast",
                "message" => "Broadcast is currently inactive for {$binds} on {$platformName}.. Please kindly look into this.."
            ));
            echo "Mail sent 4...";
        }

        Querier::getInstance()->updateFlag($otherStatuses['bl_flag'], $otherStatuses['bc_flag'], $sevasState, $platformName);
    }

    /**
     * THis function prepares the status data.
     * @param array $networks
     * @param array $networkBinds
     * @param array $statuses
     * @return array
     */
    private static function prepareStatusData(array $networks, array $networkBinds, array $statuses)
    {
        $broadcastIsDown = array();
        $billingIsDown = array();
        $broadcastIsInActive = array();
        $contentIsInActive = array();
        $allNetworkTags = array();

        $networksLength = count($networks);

        for($i = 0; $i < $networksLength; $i++) {
            $allNetworkTags[$networks[$i]['tag']] = $networks[$i]['type'];
        }

        if( ! empty($networkBinds)) {
            foreach($networkBinds as $networkBind) {
                if (array_key_exists($networkBind, $allNetworkTags)) {

                    $type = str_replace(" ", "", $allNetworkTags[$networkBind]);

                    if ($type == "billing") {
                        if ($statuses[$networkBind]['status'] == "offline")
                            $billingIsDown[] = $networkBind;
                    }

                    if ($type == "broadcast") {
                        if ($statuses[$networkBind]['status'] == "offline")
                            $broadcastIsDown[] = $networkBind;
                        elseif($statuses[$networkBind]['status'] == "online" && $statuses[$networkBind]['queued'] == "0")
                            $broadcastIsInActive[] = $networkBind;
                    }

                    if ($type == "content") {
                        if ($statuses[$networkBind]['queued'] == "0")
                            $contentIsInActive[] = $networkBind;
                    }
                }
            }
        }

        return array(
            "broadcastIsDown" => $broadcastIsDown,
            "broadcastIsInActive" => $broadcastIsInActive,
            "billingIsDown" => $billingIsDown,
            "contentIsInActive" => $contentIsInActive
        );
    }

    /**
     * This calls the applicatioiin status URL for this platform.
     * @param $appURL
     * @return bool|mixed
     */
    private static function getAppStatus($appURL)
    {
        return self::makeCall($appURL);
    }

    private static function getTempBillingBroadcastBindStatus($url)
    {}

    /**
     * This function gets billing/broadast statuses for this platform via the status.XML API.
     * @return array
     */
    private static function getBindStatusFromXML($platformName)
    {
        try {
            $xmlObject = XMLParser::parseXMLFromFile(self::getXML($platformName));
            $smscs = array();
            $smscStatus = array();
            foreach ($xmlObject->smscs->smsc as $sm) {
                $smscId = (string) $sm->id;
                if ( ! in_array($smscId, $smscs)) {
                    $smscs[] = $smscId;
                    $smscStatus[$smscId]['status'] = substr((string) $sm->status, 0, 2) === "on" ? "online":"offline";
                    $smscStatus[$smscId]['queued'] = (int) $sm->queued;
                } else {
                    $smscStatus[$smscId]['status'] = substr((string) $sm->status, 0, 2) === "on" ? "online":"offline";
                    $smscStatus[$smscId]['queued'] =  $smscStatus[$smscId]['queued'] + (int) $sm->queued;
                }
            }

            if ( ! empty($smscs)) {
                $networkTypeTags = Querier::getInstance()->fetchNetworkTags();
                return self::prepareStatusData($networkTypeTags, $smscs, $smscStatus);
            }

            return array();

        } catch(\Exception $e) {
            echo "Something went wrong";
            exit;
        }
    }

    /**
     * This queries the status.xml of this platform,. creates a file if it does not already exists.
     * @param $platform
     * @return bool|string
     */
    private static function getXML($platform)
    {
        $url = "http://{$platform}.atp-sevas.com:13013/status.xml";
        if ($output = Client::makeCall($url))
            return $output;
        return false;
    }

    /**
     * This method makes a call to the given url and returns a result.
     * @param $callURL
     * @return bool|mixed
     */
    private static function makeCall($callURL)
    {
        if ($uptime = Client::makeCall($callURL))
            return $uptime;
        return false;
    }

}

//Checker::checkStatus('freesia2');




