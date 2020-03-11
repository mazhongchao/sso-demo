<?php
$username = $_POST['username'] ?? '';
$passwd = $_POST['passwd'] ?? '';
$service_url = $_POST['service'] ?? '';

if (!$service_url) {
    echo '<pre>error: no service url';
    exit();
}
if ($username != 'demo' || $passwd != 'demo') {
    echo '<pre>Authentication Failed!';
}

//创建SSO域(全局)会话
/* ===CAS===
 * TGT: $session_value, TGC: $session_id
 */
$session_id = substr(md5(rand(100, 10000).$username.time()), 0, 10);
$session_value = $username.'~'.time();
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->auth('admin');
$redis->select(1);
$redis->set($session_id, $session_value);
$redis->expireAt($session_id, time() + 600);

//创建ticket
/* ===CAS===
 * ST(Service Ticket)
 */
$ticket = 'ST00'.'.'.substr(md5(time().$username), 0, 8).'.'.substr(sha1(rand(1000,9999).$username), 0, 16);
$redis->set($ticket, $username);
$redis->expireAt($ticket, time() + 60);

//创建SSO域(全局)下的session cookie
/* ===CAS===
 * session cookie name: sso_cookie
 * session cookie value: TGC($session_id)
 */
$cookie_expire = time() + 600;
$cookie_path = '/';
$cookie_domain = 'sso.com';
setcookie('sso_cookie', $session_id, $cookie_expire, $cookie_path, $cookie_domain);

$url = "{$service_url}?ticket={$ticket}";
header('Location: '.$url);
