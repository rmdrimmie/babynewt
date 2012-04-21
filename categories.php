<?php

require_once 'session.php';
require_once 'include.php';

// establish connection to MySQL database or output error message.
$link = mysql_connect ($dbHost, $dbUser, $dbPassword);
if (!mysql_select_db($dbName, $link)) {
    echo mysql_errno() . ': ' .mysql_error() . '<br>';
}

$CategoryList = 'SELECT i_CategoryId, vc_Name, vc_CSSName, t_Description';
$CategoryList .= ' FROM Category';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?php echo $siteTitle ?></title>
</head>
<body>
<?php
$sessionUserId = $_SESSION[ 'sessionUserId' ];

echo '<style type="text/css">';
echo 'div.bnLogo { position: absolute; top: 2px; left: 10px; font-size: 42px; font-weight: bold; color: #CCCCCC;}';
echo 'div.bnTagline { position: absolute; top: 21px; left: 70px; font-size: 16px; font-weight: bold; color: black;}';
echo 'div.bnContent { padding-top: 30px; }';

if ( !Empty( $_REQUEST[ 'StyleSheet' ] ) ) {
    // querystring param to force stylesheet
    $UserStyleQuery = 'SELECT t_StyleSheet
                         FROM DBStyleSheet
                        WHERE DBStyleSheet.i_StyleSheetId = '
                        . $_REQUEST[ 'StyleSheet' ];
} else {
    // the user's stylesheet
    $UserStyleQuery = 'SELECT t_StyleSheet
                         FROM UserStyleSheet, DBStyleSheet
                        WHERE DBStyleSheet.i_StyleSheetId = UserStyleSheet.i_StyleSheetId
                          AND UserStyleSheet.i_UID = ' . $sessionUserId;
}

$UserStyleQueryId = mysql_query ($UserStyleQuery, $link);
$UserStyleQueryResult = mysql_fetch_object($UserStyleQueryId);
echo $UserStyleQueryResult->t_StyleSheet;

echo '</style>';

$CategoryListResultId = mysql_query ($CategoryList, $link);

$TemplateID = $_REQUEST[ 'TemplateID' ];
if (Empty($TemplateID)) {
    $UserTemplateQuery = 'SELECT i_TemplateID
                            FROM UserTemplate
                           WHERE UserTemplate.i_UID = ' . $sessionUserId;
    $TemplateResId = mysql_query ($UserTemplateQuery, $link);
    $TemplateRes = mysql_fetch_object($TemplateResId);

    if (!Empty($TemplateRes->i_TemplateID)) {
        $TemplateQuery = 'SELECT t_TemplateHdr
                               , t_TemplateCmt
                               , t_TemplateFtr
                            FROM Template
                           WHERE i_TemplateID = ' . $TemplateRes->i_TemplateID;
    } else {
        $TemplateQuery = 'SELECT t_TemplateHdr
                               , t_TemplateCmt
                               , t_TemplateFtr
                            FROM Template
                           WHERE i_TemplateID = 1';
    }
} else {
    $TemplateQuery = 'SELECT t_TemplateHdr
                           , t_TemplateCmt
                           , t_TemplateFtr
                        FROM Template
                       WHERE i_TemplateID = ' . $TemplateID;
}

$TemplateResId = mysql_query ($TemplateQuery, $link);
$TemplateRes = mysql_fetch_object($TemplateResId);
$Comment = $TemplateRes->t_TemplateCmt;

while ( $CategoryListResult = mysql_fetch_object($CategoryListResultId)) {
    $category_info = 'Stylename:' . $CategoryListResult->vc_CSSName;
    $category_info .= '<br>Description: ' . $CategoryListResult->t_Description;

    $tComment = str_replace('[$COMMENTBUTTON$]', '<input class="[$CATCSSNAME$]LASTCMTBTN" type="submit" name="btnUpdateMyLastComment" value="[$COMMENTNUMBER$]">', $Comment);

    $tComment = str_replace('[$CATCSSNAME$]', $CategoryListResult->vc_CSSName, $tComment);
    $tComment = str_replace('[$COMMENTTEXT$]', $category_info, $tComment);
    $tComment = str_replace('[$CATEGORYNAME$]', $CategoryListResult->vc_Name, $tComment);
    $tComment = str_replace('[$CATNAMELINK$]', $CategoryListResult->vc_Name, $tComment);
    echo $tComment . "\n";
}
echo '<br>';
echo '<a href="index.php" title="return to front page">';
echo 'return to front page';
echo '</a>';
?>
