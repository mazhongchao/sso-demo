<?php
$token = $_GET['token'] ?? '';
$url = $_GET['url'] ?? urlencode('http://www.a.com/');

if($token && $url){
    $session_string = '';
    $session_id = hash('sha256', 'SSO'.$token.'y8I1/49Sr*06Pg');
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->auth('admin');
    $redis->select(1);
    if ($redis->exists($session_id)) {
        $session_string = $redis->get($session_id);
        $redis ->del($session_id);
    }
    else {
        //这里是否应该直接跳登录
        header('Location: http://www.sso.com/login.php?url='.$url);
        exit();
        //$session_string = $_COOKIE['user_id'].'~'.$_COOKIE['sso_token'];
    }

    $app_sessid = substr(md5(substr($session_string, 1, 19)),1,16);

    setcookie('user_id', '', time()-42000, '/', 'sso.com');
    setcookie('sso_token', '', time()-42000, '/', 'sso.com');
    setcookie('sso_session', '', time()-42000, '/', 'sso.com');

    $http_header = ['Accept: application/json'];
    $auth_url = 'http://www.a.com/a-logout.php?sess='.$app_sessid;

    $ch = curl_init($auth_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Authorization: Bearer '. $token]);

    $response = curl_exec($ch);
    echo $response;exit();
    if (curl_errno($ch)) {
        echo '--- '.curl_error($ch);
        curl_close($ch);
        exit();
    }
    curl_close($ch);

    $ret = json_decode($response, true);
    echo $ret;
}
else {
    echo "Invalid Access";
    exit();
}


