<?php

// ---------------------------------------------
// make edits here

// the inbox you want to check
$server = '';       // 'imap-mail.outlook.com'
$user = '';         // 'username@outlook.com'
$pass = '';         // your password
$port = 993;

// forwarding details
$adminEmail = '';       // the admin who should receive the email forwards. i.e. your secretary or account manager
$headerFrom = '';       // the email that will appear in the from line of the forward
$headerReplyTo = '';    // the email that will appear in the reply-to of the forward

// forwarding flag
// if the name of the sender is different than the forwarding flag it will be forwarded to the admin
// e.g. this would be your brand name,
$forwardingFlag = '';

// stop making edits
// ---------------------------------------------


// include EmailReader
include_once "src/EmailReader.php";

// fn: write a line of text
function pen($words) { echo "$words\n"; }


// ---------------------------------------------
// init reader
if ($emailReader = new EmailReader($server, $user, $pass, $port)) {
    // get message count
    if ($emailReader->getMessageCount() > 0) {
        pen("Reader found " . $emailReader->getMessageCount() . " messages on " . date('Y-m-d H:i:s'));
    }

    // load messages into an array
    $messages = [];
    for ($i = 0; $i < $emailReader->getMessageCount(); $i++) {
        array_push($messages, $emailReader->get($i));
    }

// end if
// error: reader did not init
} else {
    echo error_get_last();
}

// ---------------------------------------------
// end init reader


// ---------------------------------------------
// assemble message details for forwarding

// store the details of each message
$messagesDetails = [];

// loop through each message
// set each message's details to $messagesDetails array
for ($i = 0; $i < count($messages); $i++) {
    // get from address
    $from = $messages[$i]['header']->{'from'}[0]->{'mailbox'};
    $from .= "@";
    $from .= $messages[$i]['header']->{'from'}[0]->{'host'};

    // get important details
    $details = [];
    $details['id'] = $messages[$i]['header']->{'message_id'};
    $details['dateSent'] = $messages[$i]['header']->{'date'};
    $details['sentTo'] = $messages[$i]['header']->{'toaddress'};
    $details['dateReceived'] = $messages[$i]['header']->{'MailDate'};
    $details['sentFrom'] = $from;
    $details['subject'] = $messages[$i]['header']->{'subject'};
    $details['message'] = $emailReader->fetchBody($i + 1);

    // push details to messagesDetails array
    array_push($messagesDetails, $details);


}

// end assemble message details for forwarding
// ---------------------------------------------

// ++++++++++++++++++++++++++++++++++
// main if: 1 or more messages found
if (count($messagesDetails) > 0) {

    // ---------------------------------------------
    // forward/move messages
    foreach ($messagesDetails as $message) {

        // --------------------------------------------------------
        // move noreply emails to 'AutoResponder Emails' folder

        // counter for first message as message 1
        $msgNum = 1;

        $haystack = $message['sentFrom'];
        $needle = $forwardingFlag;
        $pos = strpos($haystack, $needle);
        if (!$pos) {

            // ++++++++++++++++++++++++++++++++++++++++++++++++++++++
            // main loop

            $to = $message['sentTo'];
            $subject = $message['subject'];
            $text = "-----------------\r\n";
            $text .= "FORWARD: \r\n";
            $text .= "-----------------\r\n";
            $text .= "Message sent from " . $message['sentFrom'] . " on " . $message['dateSent'] . "\r\n";
            $text .= "Message received by " . $message['sentTo'] . " on " . $message['dateReceived'] . "\r\n";
            $text .= "Message ID: " . $message['id'] . "\r\n";
            $text .= "\r\n";
            $text .= "Subject: " . $message['subject'] . "\r\n";
            $text .= "Message: " . "\r\n";
            $text .= $message['message'];

            // ---------------------------------------------
            // mail admin
            $headers = "From: $headerFrom" . "\r\n" .
                "Reply-To: $headerReplyTo" . "\r\n";

            if (imap_mail($adminEmail, $subject, $text, $headers)) {
                pen("forwarded message '$subject'");
                // move email from 'inbox' to 'forwarded'
                if (!$emailReader->move($msgNum)) {
                    pen("moved message to 'Forwarded' folder\n");
                    $msgNum++;
                } else {
                    pen("failed to move message to diff folder\n");
                }

            }

            // end main loop
            // ++++++++++++++++++++++++++++++++++++++++++++++++++++++


        // move no reply emails
        // brand found in $message['sentFrom']
        } else {
            // if (!$emailReader->moveNoReply($msgNum)) {
            //     pen("moved noreply message\n");
            //     $msgNum++;
            // } else {
            //     pen("failed to move noreply message\n");
            // }
        }

        // end move noreply emails to 'AutoResponder Emails' folder
        // --------------------------------------------------------
    }
    // ---------------------------------------------
    // end forward messages

// ---------------------------------------------
// close connection
    $emailReader->close();

    pen("\n======================================================================\n");

}   // end main if: 1 or more messages found
// ++++++++++++++++++++++++++++++++++++++++++

?>