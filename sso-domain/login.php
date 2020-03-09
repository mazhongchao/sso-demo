<?php
// $url = $_GET['url'] ? urlencode($_GET['url']):'';
$url = $_GET['url'] ?? '';
?>
<html>
<body>
    <form name="" action="/dologin.php" method="POST">
        名：<input type="text" name="username">
        密：<input type="passwd" name="passwd">
        <input type="hidden" name="backurl" value="<?php echo $url;?>">
        <input type="submit" value="登录" />
    </form>
</body>
</html>


