<?php
//authentication = 验证
//如果sso域下没有登录态session，显示登录页面

$url = $_GET['url'] ? urlencode($_GET['url']):'';

if(!$url) {
    echo 'error';
    exit();
}

//sso域下的session cookie
//$token = sso_cookie
$sso_cookie = $_COOKIE['sso_cookie'] ?? null;

if ($sso_cookie) {
    //$session_id = hash('sha256', 'SSO'.$token.'y8I1/49Sr*06Pg');

    $session_id = $sso_cookie;
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->auth('admin');
    $redis->select(1);
    $sso_session = $redis->exists($session_id) ? $redis->get($session_id) : null;
    if ($sso_session) {
        //登录过且登录态有效，生成ticket并下发.

        //另外的方式?
        $sess = explode('~', $sso_session);
        $token_from_session = $sess[1] ?? '';
        // 登录过且有效
        if ($sso_cookie == $token_from_session) {
            $url = urldecode($url).'?token='.$sso_cookie;
            header('Location: '.$url);
            exit();
        }
        else {
            header('Location: http://www.sso.com/login.php?url='.$url);
            exit();
        }
    }
    else {
        header('Location: http://www.sso.com/login.php?url='.$url);
        exit();
    }
}
else {
    header('Location: http://www.sso.com/login.php?url='.$url);
}
