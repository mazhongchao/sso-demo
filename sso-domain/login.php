<?php
//如果SSO域(全局)下没有session，显示登录页面

/* ===CAS===
 * service(service url)
 */
$service_url = $_GET['service'] ?? '';
if(!$service_url) {
    echo '<pre>error: no service url';
    exit();
}

//SSO域(全局)的session cookie
/* ===CAS===
 * session cookie name: CASTGC. It is 'sso_cookie' here.
 * session cookie value: TGT, the TGT is the session key for the users sso session. It is ID of the sso session here.
 */
$sso_cookie = $_COOKIE['sso_cookie'] ?? null;
if ($sso_cookie) {
    $session_id = $sso_cookie;
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->auth('admin');
    $redis->select(1);
    $sso_session = $redis->exists($session_id) ? $redis->get($session_id) : null;
    if ($sso_session && strpos($sso_session, '~') !== false) {
        $username = explode('~', $sso_session)[0];

        //存在全局session，全局登录态有效，生成ticket并下发
        /* ===CAS===
         * ST(Service Ticket)
         */
        $ticket = 'ST00'.'.'.substr(md5(time().$username), 0, 8).'.'.substr(sha1(rand(1000,9999).$username), 0, 16);
        $redis->set($ticket, $username);
        $redis->expireAt($ticket, time() + 60);

        $url = "{$service_url}?ticket={$ticket}";
        header("Location: {$url}");
        exit();
    }
}
?>
<html>
<body>
    <form name="" action="auth.php" method="POST">
        名：<input type="text" name="username">
        密：<input type="password" name="passwd">
        <input type="hidden" name="service" value="<?php echo $service_url;?>">
        <input type="submit" value="登录" />
    </form>
</body>
</html>
