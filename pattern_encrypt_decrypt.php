<?php

function encrpt($json_str)
{
    // Store the cipher method
    $ciphering = "AES-128-CTR";
      
    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;
      
    // Non-NULL Initialization Vector for encryption
    $encryption_iv = '1234567891011121';
      
    // Store the encryption key
    $encryption_key = "t1NP63m4wnBg6nyHYKfmc2TpCOGI4nss";
      
    // Use openssl_encrypt() function to encrypt the data
    $encryption = openssl_encrypt($json_str, $ciphering,
                $encryption_key, $options, $encryption_iv);
    return $encryption;
}

function decrpt($encrpt_str)
{
    // Store the cipher method
    $ciphering = "AES-128-CTR";
    // Non-NULL Initialization Vector for decryption
    $decryption_iv = '1234567891011121';
      
    // Store the decryption key
    $decryption_key = "t1NP63m4wnBg6nyHYKfmc2TpCOGI4nss";
      
    // Use openssl_decrypt() function to decrypt the data
    $decryption=openssl_decrypt ($encrpt_str, $ciphering, 
            $decryption_key, $options, $decryption_iv);
    return $decryption;
}

?>