<?php

if ( array_key_exists( 'btnExpireSession', $_REQUEST ) && $expireSession == '1' ) {
    // break cookies
    setcookie( "cookieUsername", FALSE, mktime(0,0,0,12,1,2015));
    setcookie( "cookiePassword", FALSE, mktime(0,0,0,12,1,1999));
    $_SESSION[ 'sessionUserId' ] = -1;
    header( "Location: main.php");
}

if ( ( !Empty( $_POST['userLoginSubmit'] ) ) || ( !Empty($_COOKIE['cookieUsername']) && !Empty($_COOKIE['cookiePassword']) ) ) {
    $txtUsername = $_POST['txtUsername'];
    $txtPassword = $_POST['txtPassword'];

    if ( Empty( $_POST['btnExpireSession'] ) ) {
        // don't fetch the cookie information if the user logs out.
        if ( !Empty($_COOKIE['cookieUsername']) && !Empty($_COOKIE['cookiePassword'])) {
            $txtUsername = $_COOKIE['cookieUsername'];
            $txtPassword = $_COOKIE['cookiePassword'];
        }
    }

    $UserLoginQuery = "SELECT i_UID
                         FROM Users
                        WHERE vc_Username=\"$txtUsername\"
                        AND vc_Password = substr( md5( \"$txtPassword\"), 1, 20 )";
    $UserLoginResultId = mysql_query ($UserLoginQuery, $link);
    $UserLoginResults = mysql_fetch_object($UserLoginResultId);

    if ( Empty( $UserLoginResults->i_UID ) ) {
        $_SESSION['sessionUserId'] = -1;
        if ( !Empty( $_POST['userLoginSubmit'] ) ) {
            echo "That username password combination was not found.<br><br>";
        }
    } else {
        $_SESSION['sessionUserId'] = $UserLoginResults->i_UID;

        $UpdateLastVisitQuery = "UPDATE Users SET dt_LastVisit = NOW(),";
        $UpdateLastVisitQuery .= " WHERE i_UID = $UserLoginResults->i_UID";

        $UpdateLastVisitResultId = mysql_query ($UpdateLastVisitQuery, $link);
        $_SESSION['sessionLastVisit'] = time();

        if ( $_REQUEST[ 'chkRemember' ] == "on" ) {
            setcookie( "cookieUsername", $txtUsername, mktime(0,0,0,12,1,2015));
            setcookie( "cookiePassword", $txtPassword, mktime(0,0,0,12,1,2015));
        }

        // redirect to main area on login success.
        header( "Location: main.php");
    }
}

function OutputLoginForm( $UserLoginFormAction )
{
    echo '<form name="UserLoginForm" action="', $UserLoginFormAction, '" method="post">';
    //  <a href="editprofile.php">Click here to register</a><br><br>
    echo <<<FORM
    <table>
        <tr>
            <td>
                Username:
            </td>
            <td>
                <input type="text" name="txtUsername">
            </td>
        </tr>
        <tr>
            <td>
                Password:
            </td>
            <td>
                <input type="password" name="txtPassword">
            </td>
        </tr>
        <tr>
            <td>
                Remember me
            </td>
            <td>
                <input type="checkbox" name="chkRemember">
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
            <td>
                <input type="submit" value="submit" name="userLoginSubmit">
            </td>
        </tr>
    </table>
    </form>
FORM;
}

/* end of session.php */
