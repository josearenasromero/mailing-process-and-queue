<?php

namespace App\Helpers;

class FunctionsHelper
{
    function encrypt_decrypt($action, $string)
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'This is my secret key';
        $secret_iv = 'This is my secret iv';
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } elseif ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    function  urlValidate($url){
        $regex = '((https?|ftp)://)|()?'; // SCHEME
        $regex .= '([a-z0-9+!*(),;?&=$_.-]+(:[a-z0-9+!*(),;?&=$_.-]+)?@)?'; // User and Pass
        $regex .= '([a-z0-9\-\.]*)\.(([a-z]{2,4})|([0-9]{1,3}\.([0-9]{1,3})\.([0-9]{1,3})))'; // Host or IP address
        $regex .= '(:[0-9]{2,5})?'; // Port
        $regex .= '(/([a-z0-9+$_%-]\.?)+)*/?'; // Path
        $regex .= '(\?[a-z+&\$_.-][a-z0-9;:@&%=+/$_.-]*)?'; // GET Query
        $regex .= '(#[a-z_.-][a-z0-9+$%_.-]*)?'; // Anchor
        $validate = preg_match("~^$regex$~i",$url);
        return $validate;
    }
}
