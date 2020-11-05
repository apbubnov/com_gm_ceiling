<?php

require_once __DIR__.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.''.DIRECTORY_SEPARATOR'lib'.DIRECTORY_SEPARATOR.'Client.php';
/**
 * @param string $paramsStr
 * @param string $method
 * @param string $secret
 *
 * @return string
 */
function makeSign($paramsStr, $method, $secret)
{
    return base64_encode(
        hash_hmac(
            'sha1',
            $method . $paramsStr . md5($paramsStr),
            $secret
        )
    );
}
?>