<?php

class Email {
    function send($to, $subject, $message) {
        $header = 'From: ' . Constants::EMAIL_SENDER_NAME . ' <' . Constants::EMAIL_SENDER_ADDRESS . '>\r\n' .
'X-Mailer: PHP/' . phpversion() . '\r\n' .
'Content-Type: text/plain; charset=UTF-8';
        if (Constants::WRITE_MAILS_TO_DISK_INSTEAD_OF_SENDING) {
		    return file_put_contents(Constants::MAIL_TO_DISK_PATH, 'Debug mail dump, mail was not sent!' . PHP_EOL . $to . PHP_EOL . $subject . PHP_EOL . $message . PHP_EOL . $header . PHP_EOL . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX) != false;
        }
        return mail($to, $subject, $message, $header);
    }
}
?>
