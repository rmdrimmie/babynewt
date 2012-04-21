<?php

require_once 'session.php';
require_once 'include.php';

// establish connection to MySQL database or output error message.
$link = mysql_connect ($dbHost, $dbUser, $dbPassword);
if (!mysql_select_db($dbName, $link)) {
    echo mysql_errno() . ': ' . mysql_error() . '<br>';
}

require_once 'login.php';

if ( !isset($_SESSION['sessionUserId']) ) {
    $_SESSION['sessionUserId'] = -1;
} else {
    $sessionUserId = $_SESSION['sessionUserId'];
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?php echo $siteTitle ?></title>
</head>
<body style="text-align:justify;margin-left:20px;margin-top:20px;">
<div style="width:300px;">
<?php
    echo $welcomeMsg;

    // if user is not logged in, show message and login inputs
    if ( $_SESSION['sessionUserId'] == -1 ) {
        OutputLoginForm( 'index.php' );
    } else {
        echo 'You are already logged in.  <a href="main.php">Go to the main site</a>';
    }
    ?>
</div>
</body>
</html>
<?php
// close connection to MySQL Database
mysql_close($link);
?>
