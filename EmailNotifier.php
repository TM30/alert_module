<?php

require_once "Notifier.php";
require_once "vendor/autoload.php";

class EmailNotifier implements Notifier {

    public static function notify(array $notificationParameters)
    {
        self::sendMail($notificationParameters);
    }

    /**
     * This function sends mail to a recipient and
     * outputs logs where necessary.
     */
    private static function sendMail(array $parameters)
    {
        $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername('sevasnoreply@gmail.com')
            ->setPassword('fileopen');

        // Create the message
        $message = Swift_Message::newInstance();
        $messageRecipients = array();

        foreach($parameters['recipients'] as $recipient)
            $messageRecipients[] = $recipient;

        $subject = $parameters['subject'];
        $body = $parameters['message'];

        if (! empty($messageRecipients)) {
            $message->setTo($messageRecipients);
            $message->setSubject($subject);
            $message->setBody($body);
            $message->setFrom("thrustinv@gmail.com", "TM30..");

            // Send the email
            $mailer = Swift_Mailer::newInstance($transport);
            $mailer->send($message);

            if ( ! $mailer)
                file_put_contents('mail.log', date('Y-m-d H:i:s') . ' Email Failed to ' . implode(",", $messageRecipients) . '. Reason:' . $subject.PHP_EOL, FILE_APPEND );
            else
                file_put_contents('mail.log', date('Y-m-d H:i:s') . ' Email Successfully Sent to' . implode(",", $messageRecipients).PHP_EOL , FILE_APPEND );
        }
    }
}
