<?php

 //encode contact number
// Store the cipher method 
$ciphering = "AES-128-CTR"; 

// Use OpenSSl Encryption method 
$iv_length = openssl_cipher_iv_length($ciphering); 
$options = 0; 

// Non-NULL Initialization Vector for encryption 
$encryption_iv = '1234567891011121'; 

// Store the encryption key 
$encryption_key = "Legal251AppApi";

?>