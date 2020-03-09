<?php
$username = $_POST['username'] ?? '';
$passwd = $_POST['passwd'] ?? '';
$url = $_POST['backurl'] ?? '';

if (!$url) {
    echo 'url error';
    exit();
}
if ($username == 'demo' && $passwd == 'demo') {
    //全局会话
    $session_id = substr(md5(rand().$username.time()), 0, 10);
    $session_value = $username.'~'.time();
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->auth('admin');
    $redis->select(1);
    $redis->set($session_id, $session_value);
    $redis->expireAt($session_id, time() + 600);

    //创建ticket
    $ticket = 'st00'.'.'.substr(md5(time().$username), 0, 8).'.'.substr(sha1(rand(1000,9999).$username), 0, 16);
    $redis->auth('admin');
    $redis->select(1);
    $redis->set($ticket, $username);
    $redis->expireAt($ticket, time() + 60);

    //sso域下的session cookie
    //setcookie('user_id', $username, $cookie_expire, $cookie_path, $cookie_domain);
    //setcookie('sso_token', $token, $cookie_expire, $cookie_path, $cookie_domain);
    $cookie_expire = time()+3600;
    $cookie_path = '/';
    $cookie_domain = 'sso.com';
    setcookie('sso_cookie', $session_id, $cookie_expire, $cookie_path, $cookie_domain);

    //签发ticket
    $url = $url.'?ticket='.$ticket;
    header('Location: '.$url);
}
else {
    echo 'Authentication Failed!';
}

