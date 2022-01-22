<?php
class HashUtil {
    /**
     * Generates a random string of length 64. Possible characters are [0-9] and [a-f].
     */
    function generateRandomString() {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return hash('sha256', openssl_random_pseudo_bytes(100));
        }
        return hash('sha256', md5(rand()));
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
