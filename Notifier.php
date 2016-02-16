<?php

interface Notifier
{
    /**
     * All Notification modules implements this single method.
     * @return mixed
     */
    public static function notify(array $notificationParameters);
}
