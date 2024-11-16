<?php
$encdec_key = "dedcZNGK768mlPPsxsx";
$ciphering = "AES-128-CTR";

function _decrypt($r, $iv) {
    global    $encdec_key;
    global    $ciphering;   
    
    $ivb = hex2bin($iv);

    error_log("iv: $iv - ".strlen($iv)."\n");
    error_log( "iv(bin): ".$ivb. " - ".strlen($ivb));

    return openssl_decrypt( hex2bin($r),
                                $ciphering,
                                $encdec_key,
                                0,
                                hex2bin($iv));
   
}

function _encrypt($username) {
    global    $encdec_key;
    global    $ciphering;

    $iv_length = openssl_cipher_iv_length($ciphering); //16 
    $iv = random_bytes($iv_length);
    $username_enc = openssl_encrypt( $username,
                                $ciphering,
                                $encdec_key,
                                0,
                                $iv);
    return [bin2hex($username_enc), bin2hex($iv)];
}
?>
