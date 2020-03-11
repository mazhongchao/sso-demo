<?php
$service_url = 'http://www.b.com/index.php';

/**
 * ===CAS===
 * ST(Service Ticket)
**/
$ticket = $_GET['ticket'] ?? '';

/**
 * ===CAS===
 * Verifying ST at sso-domain(CAS Server).
 * Actually, HTTPS should be used when validating ST at server.
**/
if($ticket){
    $verify_url = 'http://www.sso.com/verify.php';
    $ch = curl_init($verify_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Authorization: Bearer '. $ticket]);

    //POST
    // $post_data = ['ticket'=>$ticket];
    // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

    //GET
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_TIMEOUT, 500);
    // curl_setopt($ch, CURLOPT_URL, $verify_url.'?ticket='.$ticket);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "CURL ERROR: ".curl_error($ch);
        curl_close($ch);
        exit();
    }
    curl_close($ch);

    $ret = json_decode($response, true);
    if ($ret['ticket_verify'] != true) {
        header('Location: http://www.sso.com/login.php?service='.urlencode($service_url));
        exit();
    }
    //设置登录a-app-domain的局部会话
    session_start();
    $_SESSION['a_domain_session'] = $ret['user_name'];
    header('Location: http://www.b.com/index.php');
    exit();
}
else {
    //对于PHP默认的session实现方式，session名(即session_name()的返回值)为PHPSESSID。
    $session_id = $_COOKIE[session_name()] ?? '';
    if (!$session_id) {
        header('Location: http://www.sso.com/login.php?service='.urlencode($service_url));
        exit();
    }
    echo session_name().'  '.$session_id;
}
echo '<pre>';
echo 'Login b.com success!<br/><br/>';
echo '$_SESSION:<br/>';
print_r($_SESSION);
echo '<br/>$_COOKIE:<br/>';
print_r($_COOKIE);
echo "<a href=\"http://www.b.com/logout.php\">退出</a>";

