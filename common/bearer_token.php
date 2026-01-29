<?php
    // use Firebase\JWT\JWT;

    // Function to get the Authorization header
    function getAuthorizationHeader() {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for a bug in old PHP versions (a nice side-effect of case-insensitive array access)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    
    function getZDAuthorizationHeader($token) {
        global $g_jtg_3party_token;
        $headers = null;
        
        // $secret_key = "secret";
        // $token_expire_time = 3600; // 1 hour
    
        // $payload = [
        //     'db' => 'SPS_EV_MS',
        //     'id' => $userid,
        //     'exp' => time() + $token_expire_time
        // ];
    
        // $Signature = JWT::encode($payload, $secret_key, 'HS256');
        $Signature = $token;
        // echo $Signature."\n";
        $headers = array(
            'Authorization: Bearer '.$Signature,
            'Content-Type: application/json',
            'Allow: '.$g_jtg_3party_token,
            'Accept: application/json',
            'Cookie: session=eyJfcGVybWFuZW50Ijp0cnVlfQ.ZtASzQ.fZcfmGddHJUclmQJItKd1id9ZOc'
        );
        return $headers;
    }
    function getZDBearerToken($userid) {
        return getZDAuthorizationHeader($userid);
    }
    function getHuananHeader() {
        global $g_jtg_key_id, $g_verify_code;
        $headers = [
            'Content-Type: application/json;charset=utf-8',
            'User-Agent: POSAPI',
            'X-KeyID: ' . $g_jtg_key_id,
        ];
        return $headers;
    }
    
    // Function to get the Bearer token from the header
    function getBearerToken() {
        $headers = getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
?>