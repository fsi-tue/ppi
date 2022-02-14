<?php
class HashUtil {
    /**
     * Generates a random string of variable length (default and max 64). Possible characters are [0-9] and [a-f].
     */
    function generateRandomString($length = 64) {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randString = hash('sha256', openssl_random_pseudo_bytes(100));
        }
        else {
            $randString = hash('sha256', md5(rand()));
        }
        return substr($randString, 0, $length);
    }
    
    /**
     * Hashes the given password with a secure PHP method that also magically includes a salt.
     */
    function hashPasswordWithSaltIncluded($password) {
        return password_hash($password, PASSWORD_DEFAULT, array('cost' => Constants::PASSWORD_COST));
    }
    
    /**
     * Verifies if the given clear text password matches the hash.
     * The hash was previously created using a secure PHP method that also magically includes a salt.
     */
    function checkPasswordHashWithSaltIncluded($passwordClearText, $passwordHash) {
        return password_verify($passwordClearText, $passwordHash);
    }
}
?>
