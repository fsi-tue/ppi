<?php
class HashUtil {
    function generateRandomString() {
        return hash('sha256', openssl_random_pseudo_bytes(100));
    }
    
    function hashPasswordWithSaltIncluded($password) {
        return password_hash($password, PASSWORD_DEFAULT, array('cost' => Constants::PASSWORD_COST));
    }
    
    function checkPasswordHashWithSaltIncluded($passwordClearText, $passwordHash) {
        return password_verify($passwordClearText, $passwordHash);
    }
}
?>
