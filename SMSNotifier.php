<?php

require_once "Notifier.php";

class SMSNotifier implements Notifier {

    public static function notify(array $notificationParameters)
    {
        echo "SMS Notifying...";
    }
}
