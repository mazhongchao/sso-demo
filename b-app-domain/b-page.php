<?php
$is_login = false;
$url = urlencode('http://www.b.com/b-page.php');

$token = isset($_GET['token']) ? $_GET['token'] : '';
if($token){
    $session_id = hash('sha256','SSO'.$token.'yI0v/49Sr9Pg');
    $auth_url = 'http://www.sso.com/auth.php';
    $data = ['token'=>$token];
    $ch = curl_init($auth_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Authorization: Bearer '. $session_id]);

    $post = is_string($data) ? $data : http_build_query($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    //echo "session_id = ".$session_id."<br/>";
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo curl_error($ch);
        curl_close($ch);
        exit();
        return;
    }
    $ret = json_decode($response, true);
    //echo 'response='.$response;
    curl_close($ch);
    if ($ret['is_login']) {
        //设置局部会话
        session_start();

        $_SESSION['user'] = $ret['user'];
        $_SESSION['sso_token'] = $ret['token'];
        setcookie('user', $ret['user'], time()+3600, '/', 'b.com');
        setcookie('sso_token', $ret['token'], time()+3600, '/', 'b.com');

        $is_login = true;
    }
    else {
        header('Location: http://www.sso.com/index.php?url='.$url);
        return;
    }
}
else {
    session_start();
    // if (!$_SESSION['sso_token']) {
    //     header('Location: http://www.sso.com/index.php?url='.$url);
    //     return;
    // }
    /* Or: */
    if (!$_COOKIE['user']){
        header('Location: http://www.sso.com/index.php?url='.$url);
        return;
    }
    else {
        $is_login = true;
    }
}
if ($is_login) {
    echo 'login success!<br/>';
    echo 'page 1 in b.com<br/>';
    echo 'login user:'.$_SESSION['user'].'<br/>';
}
