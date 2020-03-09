<?php
$is_login = false;
$url = urlencode('http://www.a.com/a-page.php');

$ticket = isset($_GET['ticket']) ? $_GET['ticket'] : '';
if($ticket){
    $verify_url = 'http://www.sso.com/verify.php';
    $ch = curl_init($verify_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Authorization: Bearer '. $ticket]);

    //$data = ['ticket'=>$ticket];
    //$post = is_string($data) ? $data : http_build_query($data);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo curl_error($ch);
        curl_close($ch);
        exit();
    }
    curl_close($ch);

    $ret = json_decode($response, true);
    if ( isset($ret['ticket_verify']) && $ret['ticket_verify'] == true) {
        //设置局部会话
        session_id($ret['app_sessid']);
        session_start();
        $_SESSION['ss_ticket'] = $ticket;
        $_SESSION['user_id'] = $ret['user_id'];
        $is_login = true;
    }
    else {
        header('Location: http://www.sso.com/index.php?url='.$url);
        exit();
    }
}
else {
    $app_sessid = $_COOKIE[session_name()] ?? '';
    if ($app_sessid) {
        session_id($app_sessid);
        session_start();
        if ($_SESSION['sso_ticket']) {
            $is_login = true;
        }
        else {
            session_destroy();
            $_SESSION = [];
            setcookie(session_name(), '', -1, '/');
            header('Location: http://www.sso.com/index.php?url='.$url);
            exit();
        }
    }
    else {
        header('Location: http://www.sso.com/index.php?url='.$url);
        exit();
    }
}
if ($is_login) {
    echo 'login a.com success!<br/>';
    echo 'login user: '.$_SESSION['user_id'].'<br/><br/>';
    $sso_ticket = $_SESSION['sso_ticket'];
    echo '<a href="http://www.sso.com/logout.php?ticket='.$sso_ticket.'">退出</a>';
}
