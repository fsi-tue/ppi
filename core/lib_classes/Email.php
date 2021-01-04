<?php
class Email {
    /**
     * Send an email to the given recipient with the given subject and message content.
     * If 'Constants::WRITE_MAILS_TO_DISK_INSTEAD_OF_SENDING' is set to true,
     * this function writes the mail content to afile on disk for debugging purposes.
     * Returns true if the operation was successful, false otherwise.
     */
    function send($to, $subject, $message) {
        $header = 'From: ' . Constants::EMAIL_SENDER_NAME . ' <' . Constants::EMAIL_SENDER_ADDRESS . '>' . "\r\n" .
'X-Mailer: PHP/' . phpversion() . "\r\n" .
'Content-Type: text/plain; charset=UTF-8';

        $subject = str_replace('ä', 'ae', $subject);
        $subject = str_replace('ü', 'ue', $subject);
        $subject = str_replace('ö', 'oe', $subject);
        $subject = str_replace('Ä', 'Ae', $subject);
        $subject = str_replace('Ü', 'Ue', $subject);
        $subject = str_replace('Ö', 'Oe', $subject);
        $subject = str_replace('ß', 'ss', $subject);
        $subject = str_replace('<br>', "\r\n", $subject);
        $message = str_replace('<br>', "\r\n", $message);

        if (Constants::WRITE_MAILS_TO_DISK_INSTEAD_OF_SENDING) {
		    return file_put_contents(Constants::MAIL_TO_DISK_PATH, 'Debug mail dump, mail was not sent!' . PHP_EOL . $to . PHP_EOL . $subject . PHP_EOL . $message . PHP_EOL . $header . PHP_EOL . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX) != false;
        }
        return mail($to, $subject, $message, $header);
    }
}
?>
