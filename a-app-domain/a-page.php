<?php
$url = urlencode('http://www.a.com/a-page.php');

/* CAS: ST(Service Ticket) */
$ticket = isset($_GET['ticket']) ? $_GET['ticket'] : '';

/* CAS: Verifying ST at sso-domain(CAS Server). Actually, HTTPS should be used when validating ST at server. */
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
    if (isset($ret['ticket_verify']) && $ret['ticket_verify'] == true) {
        //设置a-app-domain的局部会话
        session_id();
        session_start();
        $_SESSION['user_name'] = $ret['user'];
        header('Location: http://www.a.com/a-page.php');
        exit();
    }
    else {
        header('Location: http://www.sso.com/index.php?url='.$url);
        exit();
    }
}
else {
    $session_id = $_COOKIE[session_name()] ?? '';
    if ($session_id) {
        session_id($session_id);
        session_start();
        if (!$_SESSION['user_name']) {
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

echo 'login a.com success!<br/>';
echo 'login user: '.$_SESSION['user_name'].'<br/><br/>';
echo '<a href="http://www.sso.com/logout.php?ticket='.$sso_ticket.'">退出</a>';

