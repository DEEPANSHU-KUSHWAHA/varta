<?php
require 'vendor/autoload.php';
use OTPHP\TOTP;

function generateSecret() {
    return \ParagonIE\ConstantTime\Base32::encodeUpper(random_bytes(10));
}

function verifyTOTP($secret, $code) {
    $totp = TOTP::create($secret);
    return $totp->verify($code);
}
?>
