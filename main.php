<?php

require_once 'session.php';

ob_start('ob_gzhandler');
$timecheck1 = time();
srand(time());

require_once 'include.php';

// establish connection to MySQL database or output error message.
$link = mysql_connect ($dbHost, $dbUser, $dbPassword);
if (!mysql_select_db($dbName, $link)) {
    echo mysql_errno() . ': ' . mysql_error() . '<br>';
}

// Default to my userID for the testing
if ( !isset( $_SESSION['sessionUserId'] ) ) {
    $_SESSION['sessionUserId'] = -1;
}

// if user is not logged in, show message and login inputs
if ( $_SESSION['sessionUserId'] == -1 ) {
    header ('Location: index.php');
}

$sessionUserId = $_SESSION['sessionUserId'];

$txtPageSize = array_key_exists( 'txtPageSize', $_REQUEST) ? $_REQUEST[ 'txtPageSize' ] : 50;
if ( $txtPageSize > 500 ) {
    $txtPageSize = 500;
}

$MyLastCommentQuery = 'SELECT i_CommentId FROM Users WHERE Users.i_UID = ' . $sessionUserId;
$MyLastCommentResultId = mysql_query ($MyLastCommentQuery, $link);
$MyLastCommentResult = mysql_fetch_object($MyLastCommentResultId);

$MaxCmtQuery = 'SELECT MAX(Comment.i_CommentID) AS MaxCmt FROM Comment';
$MaxCmtResId = mysql_query ($MaxCmtQuery, $link);
$MaxCmtRes = mysql_fetch_object($MaxCmtResId);
// Start at the first comment if there's none already set in the database.
$hdnCurrentRecord = array_key_exists( 'hdnCurrentRecord', $_REQUEST ) ? $_REQUEST[ 'hdnCurrentRecord' ] : '';
if ( ('' === $hdnCurrentRecord ) && !Empty( $MyLastCommentResult->i_CommentId )) {
    $hdnCurrentRecord = $MyLastCommentResult->i_CommentId;
} else if ( Empty($hdnCurrentRecord) ) {
    $hdnCurrentRecord = 0;
}

// Start at the post before the clicked link.
$CommentTable = 'Comment';

if (!Empty( $_REQUEST['ViewPost'] )) {
    $hdnCurrentRecord = $_REQUEST['ViewPost'];
}

// increment or decrement by page size.
$btnNextPage = array_key_exists( 'btnNextPage', $_REQUEST ) ? $_REQUEST[ 'btnNextPage' ] : null;
$btnPrevPage = array_key_exists( 'btnNextPage', $_REQUEST ) ? $_REQUEST[ 'btnPrevPage' ] : null;
if ( null !== $btnNextPage ) {
    $hdnCurrentRecord += $txtPageSize;
}
if ( null !== $btnPrevPage ) {
    $hdnCurrentRecord -= $txtPageSize;
}

/* Hiding archive behaviour for now.
if ( $hdnCurrentRecord < 1450000 ) {
    if ( $hdnCurrentRecord < 50000 ) {
        $CommentTable = 'CommentArchive1';
    } else if  ( $hdnCurrentRecord < 100000 ) {
        $CommentTable = 'CommentArchive2';
    } else if  ( $hdnCurrentRecord < 150000 ) {
        $CommentTable = 'CommentArchive3';
    } else if  ( $hdnCurrentRecord < 200000 ) {
        $CommentTable = 'CommentArchive4';
    } else if  ( $hdnCurrentRecord < 250000 ) {
        $CommentTable = 'CommentArchive5';
    } else if ( $hdnCurrentRecord < 300000 ) {
        $CommentTable = 'CommentArchive6';
    } else if ( $hdnCurrentRecord < 350000 ) {
        $CommentTable = 'CommentArchive7';
    } else if ( $hdnCurrentRecord < 400000) {
        $CommentTable = 'CommentArchive8';
    } else if ( $hdnCurrentRecord < 450000) {
        $CommentTable = 'CommentArchive9';
    } else if ( $hdnCurrentRecord < 500000 ) {
        $CommentTable = 'CommentArchive10';
    } else if ( $hdnCurrentRecord < 550000 ) {
        $CommentTable = 'CommentArchive11';
    } else if ( $hdnCurrentRecord < 600000 ) {
        $CommentTable = 'CommentArchive12';
    } else if ( $hdnCurrentRecord < 650000 ) {
        $CommentTable = 'CommentArchive13';
    } else if ( $hdnCurrentRecord < 700000 ) {
        $CommentTable = 'CommentArchive14';
    } else if ( $hdnCurrentRecord < 750000 ) {
        $CommentTable = 'CommentArchive15';
    } else if ( $hdnCurrentRecord < 800000 ) {
        $CommentTable = 'CommentArchive16';
    } else if ( $hdnCurrentRecord < 850000 ) {
        $CommentTable = 'CommentArchive17';
    } else if ( $hdnCurrentRecord < 900000 ) {
        $CommentTable = 'CommentArchive18';
    } else if ( $hdnCurrentRecord < 950000 ) {
        $CommentTable = 'CommentArchive19';
    } else if ( $hdnCurrentRecord < 1000000 ) {
        $CommentTable = 'CommentArchive20';
    } else if ( $hdnCurrentRecord < 1050000 ) {
        $CommentTable = 'CommentArchive21';
    } else if ( $hdnCurrentRecord < 1100000 ) {
        $CommentTable = 'CommentArchive22';
    } else if ( $hdnCurrentRecord < 1150000 ) {
        $CommentTable = 'CommentArchive23';
    } else if ( $hdnCurrentRecord < 1200000 ) {
        $CommentTable = 'CommentArchive24';
    } else if ( $hdnCurrentRecord < 1250000 ) {
        $CommentTable = 'CommentArchive25';
    } else if ( $hdnCurrentRecord < 1300000 ) {
        $CommentTable = 'CommentArchive26';
    } else if ( $hdnCurrentRecord < 1350000 ) {
        $CommentTable = 'CommentArchive27';
    } else if ( $hdnCurrentRecord < 1400000 ) {
        $CommentTable = 'CommentArchive28';
    } else if ( $hdnCurrentRecord < 1450000 ) {
        $CommentTable = 'CommentArchive29';
    }
}
*/

// Make the viewport local
if ($hdnCurrentRecord > $MaxCmtRes->MaxCmt) {
    $hdnCurrentRecord = $MaxCmtRes->MaxCmt - 5;
}
if ($hdnCurrentRecord < 0) {
    $hdnCurrentRecord = 0;
}

// If the user clicked a comment button update last viewed to the
// comment before it was pressed.
//  Previous comment because CommentsQuery starts at that id and mySQL apparently
//  starts array-type things at 1, rather than 0.
$btnUpdateMyLastComment = array_key_exists( 'btnUpdateMyLastComment', $_REQUEST ) ? $_REQUEST[ 'btnUpdateMyLastComment' ] : null;
if ( null !== $btnUpdateMyLastComment ) {
//    if ( $btnUpdateMyLastComment > 1 ) {
//        $btnUpdateMyLastComment -= 1;
//    }
    $UpdateMyLastCommentQuery = "UPDATE Users SET i_CommentId = $btnUpdateMyLastComment WHERE Users.i_UID = $sessionUserId";

    $UpdateMyLastCommentResultId = mysql_query ($UpdateMyLastCommentQuery, $link);
    $hdnCurrentRecord = $btnUpdateMyLastComment;
}

// Get the user select template
// Added Outer If/Else structure for $TemplateID
$TemplateID = array_key_exists( 'TemplateID', $_REQUEST ) ? $_REQUEST[ 'TemplateID' ] : null;
if ( null === $TemplateID ) {
    $UserTemplateQuery = 'SELECT i_TemplateID FROM UserTemplate WHERE UserTemplate.i_UID = ' . $sessionUserId;
    $TemplateResId = mysql_query ($UserTemplateQuery, $link);
    $TemplateRes = mysql_fetch_object($TemplateResId);

    if (!Empty($TemplateRes->i_TemplateID)) {
        $TemplateQuery = 'SELECT t_TemplateHdr, t_TemplateCmt, t_TemplateFtr FROM Template WHERE i_TemplateID = ' . $TemplateRes->i_TemplateID;
    } else {
        $TemplateQuery = 'SELECT t_TemplateHdr, t_TemplateCmt, t_TemplateFtr FROM Template WHERE i_TemplateID = 1';
    }
} else {
    $TemplateQuery = 'SELECT t_TemplateHdr, t_TemplateCmt, t_TemplateFtr FROM Template WHERE i_TemplateID = ' . $TemplateID;
}
//---------------------------------------End Mods Dec 5, 2001------------------------------------

$TemplateResId = mysql_query ($TemplateQuery, $link);

$TemplateRes = mysql_fetch_object($TemplateResId);

// Get the max tagline to make a better query
// Add the taglines
$TaglineQuery = 'SELECT max(i_TaglineId) as i_TaglineId FROM Tagline';
$TaglineResId = mysql_query ($TaglineQuery, $link);
$TaglineRes = mysql_fetch_object($TaglineResId);

if ( null === $TaglineRes->i_TaglineId ) {
    $TaglineRes->i_TaglineId = 1;
}
$TaglineId = (rand()%$TaglineRes->i_TaglineId) + 1;

// Add the taglines
$TaglineQuery = 'SELECT vc_Tagline FROM Tagline
                WHERE i_TaglineId = ' . $TaglineId;
$TaglineResId = mysql_query ($TaglineQuery, $link);
$TaglineRes = mysql_fetch_object($TaglineResId);

// Get a tagline prefix
$TaglinePrefixQuery = 'SELECT max(i_TaglinePrefixId) as
        i_TaglinePrefixId FROM TaglinePrefix';
$TaglinePrefixResId = mysql_query ($TaglinePrefixQuery, $link);
$TaglinePrefixRes = mysql_fetch_object($TaglinePrefixResId);

if ( null === $TaglinePrefixRes->i_TaglinePrefixId ) {
    $TaglinePrefixRes->i_TaglinePrefixId = 1;
}
$TaglinePrefixId = (rand()%$TaglinePrefixRes->i_TaglinePrefixId) + 1;
$TaglinePrefixQuery = 'SELECT vc_TaglinePrefix,
                            vc_TaglineSuffix
    FROM TaglinePrefix
            WHERE i_TaglinePrefixId = ' . $TaglinePrefixId;
$TaglinePrefixResId = mysql_query ($TaglinePrefixQuery, $link);
$TaglinePrefixRes = mysql_fetch_object($TaglinePrefixResId);

$strTagline = '';
if ( $TaglinePrefixRes ) {
      /***
       *  Tagline scheme - prefix, suffix, rand keyword
       *
       *  table stores prefix and suffix, retrieve both, apply both.
       *  string may contain indicator for random number generation in
       *  the following pattern:
       *    $RANDx.y*z$
       *    $RAND  - start indicator
       *    a  - lowest allowable number (positive integer)
       *    .  - delimiter
       *    b  - highest allowable number (positive integer)
       *    *  - delimiter
       *    z  - multiplier.
       */
      $strTagline = stripslashes( $TaglinePrefixRes->vc_TaglinePrefix )
                       .
                     $strTagline.$TaglineRes->vc_Tagline
                       .
                     stripslashes( $TaglinePrefixRes->vc_TaglineSuffix );
}

$rand_start = strpos(  $strTagline, '$RAND' );
while ( $rand_start ) {
    $rand_end = strpos( $strTagline, '$', $rand_start + 1 );
    $dot_pos = strpos( $strTagline, '.', $rand_start + 1);
    $ast_pos = strpos( $strTagline, '*', $dot_pos + 1 );

    $low_post = substr( $strTagline, $rand_start + 5, $dot_pos - ($rand_start + 5) );
    $high_post = substr( $strTagline, $dot_pos + 1, $ast_pos - ($dot_pos + 1 ) );
    $multiplier = substr( $strTagline, $ast_pos + 1, $rand_end - ($ast_pos + 1 ) );

    $number = ((rand()%($high_post - $low_post)) + $low_post) * $multiplier;

    $to_replace = "\$RAND$low_post.$high_post*$multiplier\$";

    $strTagline = str_replace( $to_replace, $number, $strTagline );
    $rand_start = strpos(  $strTagline, '$RAND' );
}

if ( $TemplateRes ) {
    $Header = str_replace('[$TAGLINE$]',
        $strTagline,
        $TemplateRes->t_TemplateHdr);
    $Comment = str_replace('[$TAGLINE$]',
        $strTagline,
        $TemplateRes->t_TemplateCmt);
    $Footer = str_replace('[$TAGLINE$]',
        $strTagline,
        $TemplateRes->t_TemplateFtr);
} else {
    $Header = '[Header] No Templates exist.';
    $Comment = '[Comment] No Templates exist.';
    $Footer = '[Footer] No Templates exist.';
}

$userStyle = array_key_exists( 'StyleSheet', $_REQUEST ) ? $_REQUEST[ 'StyleSheet' ] : null;
if ( null !== $userStyle ) {
    $UserStyleQuery = 'SELECT t_StyleSheet
                         FROM DBStyleSheet
                        WHERE DBStyleSheet.i_StyleSheetId = ' . $userStyle;
} else {
    // Add the stylesheet
    $UserStyleQuery = 'SELECT t_StyleSheet
                                        FROM UserStyleSheet, DBStyleSheet
                                        WHERE DBStyleSheet.i_StyleSheetId = UserStyleSheet.i_StyleSheetId
                                        AND UserStyleSheet.i_UID = ' . $sessionUserId;
}

$StyleResId = mysql_query ($UserStyleQuery, $link);
if ( $StyleRes = mysql_fetch_object($StyleResId) ) {
    // if a stylesheet for this user exists,
    // do nothing here.
} else {
    // this user does not have a stylesheet.  Fetch the default (id = 1)
    $UserStyleQuery = 'SELECT t_StyleSheet
                                        FROM DBStyleSheet
                                        WHERE DBStyleSheet.i_StyleSheetId = 1';

    $StyleResId = mysql_query ($UserStyleQuery, $link);
    $StyleRes = mysql_fetch_object($StyleResId);
}

$ssheet = '<style type="text/css">';
$ssheet .= '@import url(/essl.css);';
if ( $StyleRes ) {
    $ssheet .= "\n$StyleRes->t_StyleSheet\n";
}
$ssheet .= "\n</style>";

$Header = str_replace('[$STYLESHEET$]', $ssheet, $Header);

$UserStyleQuery = 'SELECT vc_UserName, vc_UserId
                                    FROM Users
                                    WHERE Users.i_UID = ' . $sessionUserId;

$StyleResId = mysql_query ($UserStyleQuery, $link);
$StyleRes = mysql_fetch_object($StyleResId);

// Replace all additional Current User
$Header = str_replace('[$NAME$]', $StyleRes->vc_UserName, $Header);
$Comment = str_replace('[$NAME$]', $StyleRes->vc_UserName, $Comment);
$Footer = str_replace('[$NAME$]', $StyleRes->vc_UserName, $Footer);

// Replace current user number
$Header = str_replace('[$UNUMBER$]', $StyleRes->vc_UserId, $Header);
$Comment = str_replace('[$UNUMBER$]', $StyleRes->vc_UserId, $Comment);
$Footer = str_replace('[$UNUMBER$]', $StyleRes->vc_UserId, $Footer);

$Header = str_replace('hdnUID', 'hdhUID', $Header);
$Comment = str_replace('hdnUID','hdhUID', $Comment);
$Footer = str_replace('hdnUID', 'hdhUID', $Footer);

// Do-wop the category option list
$CatOptions = '';
$CategoryListQuery = 'SELECT i_CategoryId, vc_Name FROM Category ORDER BY vc_Name';
$CategoryListResultId =  mysql_query ($CategoryListQuery, $link);

while ( $CategoryListResult = mysql_fetch_object($CategoryListResultId)) {
    $CatOptions .= "<option value=\"$CategoryListResult->i_CategoryId\"";
    if ( $CategoryListResult->i_CategoryId == 1 ) {
        $CatOptions .=  ' selected';
    }
    $CatOptions .= ">$CategoryListResult->vc_Name</option>";
}

$Header = str_replace('[$OPTIONCATLIST$]', $CatOptions, $Header);
$Comment = str_replace('[$OPTIONCATLIST$]', $CatOptions, $Comment);
$Footer = str_replace('[$OPTIONCATLIST$]', $CatOptions, $Footer);

// Replace all additional mods
$PostBoxStr = '<textarea name="txtComment" class="CommentBox" cols="80" rows="15" wrap="virtual"></textarea><br><input type="hidden" name="hdnUID" value="$sessionUserId">';
$Header = str_replace('[$POSTBOX$]', $PostBoxStr, $Header);
$Comment = str_replace('[$POSTBOX$]', $PostBoxStr, $Comment);
$Footer = str_replace('[$POSTBOX$]', $PostBoxStr, $Footer);

// User's secret Identity
$IdentityStr = "<input type=\"hidden\" name=\"hdnUID\" value=\"$sessionUserId\">";
$Header = str_replace('[$IDENTITY$]', $IdentityStr, $Header);
$Comment = str_replace('[$IDENTITY$]', $IdentityStr, $Comment);
$Footer = str_replace('[$IDENTITY$]', $IdentityStr, $Footer);

//Textbox to textarea patch
$Header = str_replace('[$TEXTBOX$]', 'TEXTAREA', $Header);
$Comment = str_replace('[$TEXTBOX$]', 'TEXTAREA', $Comment);
$Footer = str_replace('[$TEXTBOX$]', 'TEXTAREA', $Footer);

// Replace all additional mods
$BookmarkStr = "<input type=\"hidden\" name=\"hdnCurrentRecord\" value=\"$hdnCurrentRecord\">";
$Header = str_replace('[$BOOKMARK$]', $BookmarkStr, $Header);
$Comment = str_replace('[$BOOKMARK$]', $BookmarkStr, $Comment);
$Footer = str_replace('[$BOOKMARK$]', $BookmarkStr, $Footer);

// offset time.
$GMTOffset = '0';

$GMTOffsetQuery =   'SELECT vc_GMTOffset
                                        FROM Users
                                        WHERE i_UID = ' . $sessionUserId;
$GMTOffsetResultId = mysql_query ( $GMTOffsetQuery, $link );
while ( $GMTOffsetResult = mysql_fetch_object( $GMTOffsetResultId ) ) {
    $GMTOffset = $GMTOffsetResult->vc_GMTOffset;
}

date_default_timezone_set('America/New_York');
if ( date('H') + $GMTOffset < 0 ) {
    $relative_day = date('d') -1;
} else {
    $relative_day = date('d');
}

$relative_timestamp = mktime(date('H') + $GMTOffset, date('i'), date('s'), date('m'),  date('d'),  date('Y'));

// Date
$Date = date('m.d.y', $relative_timestamp);
$Header = str_replace('[$CURDATE$]', $Date, $Header);
$Comment = str_replace('[$CURDATE$]', $Date, $Comment);
$Footer = str_replace('[$CURDATE$]', $Date, $Footer);

// Time
$Time = date('H:i:s', $relative_timestamp);
$Header = str_replace('[$CURTIME$]', $Time, $Header);
$Comment = str_replace('[$CURTIME$]', $Time, $Comment);
$Footer = str_replace('[$CURTIME$]', $Time, $Footer);

// Bug Report Code
$BRScrStr = "function openpopup(){\nvar popurl=\"BugReport.php?Usr=$sessionUserId&CurRec=$hdnCurrentRecord&PgSz=$txtPageSize&VP=$hdnCurrentRecord&bNP=$btnNextPage&bPP=$btnPrevPage&bULC=$btnUpdateMyLastComment\"\n winpops=window.open(popurl,\"BugReport\", \"width=400, height=338, scrollbars, resizable,\") \n}";
$Header = str_replace('[$BUGREPORTSCRIPT$]', $BRScrStr, $Header);
$Comment = str_replace('[$BUGREPORTSCRIPT$]', $BRScrStr, $Comment);
$Footer = str_replace('[$BUGREPORTSCRIPT$]', $BRScrStr, $Footer);

$BugReport = '<a href="javascript:openpopup()">Bug Report</a>';
$Header = str_replace('[$BUGREPORTLINK$]', $BugReport, $Header);
$Comment = str_replace('[$BUGREPORTLINK$]', $BugReport, $Comment);
$Footer = str_replace('[$BUGREPORTLINK$]', $BugReport, $Footer);

// Link to the present
$PresentPost = $MaxCmtRes->MaxCmt - 10;
$PresStr = 'Present';
// Allows formatting of the comment date into any allowed by mySQL
$PresStmp = strstr($Header, '[$PRESENTLINKSTR=');
if ($PresStmp) {
    $PresSE = strpos($PresStmp, '"$]');
    $PresStr = substr($PresStmp, 18, $PresSE-18);
}

$PresStr = "<a href=\"main.php?ViewPost=$PresentPost\">$PresStr</a>";
$Comment = str_replace('[$PRESENT$]', $PresStr, $Comment);
$Footer = str_replace('[$PRESENT$]', $PresStr, $Footer);
$Header = str_replace('[$PRESENT$]', $PresStr, $Header);

// Total Comments
$Comment = str_replace('[$MAXCOMMENT$]', $MaxCmtRes->MaxCmt, $Comment);
$Footer = str_replace('[$MAXCOMMENT$]', $MaxCmtRes->MaxCmt, $Footer);
$Header = str_replace('[$MAXCOMMENT$]', $MaxCmtRes->MaxCmt, $Header);

// Logout Link

$LoutStr = 'Logout';
// Allows formatting of the comment date into any allowed by mySQL
$LoutStmp = strstr($Header, '[$LOGOUTLINKSTR=');
if ($LoutStmp) {
    $LoutSE = strpos($LoutStmp, '"$]');
    $LoutStr = substr($LoutStmp, 17, $LoutSE-17);
}

$Header = str_replace('[$LOGOUT$]', "<a href=\"logout.php\">$LoutStr</a>", $Header);
$Comment = str_replace('[$LOGOUT$]', "<a href=\"logout.php\">$LoutStr</a>", $Comment);
$Footer = str_replace('[$LOGOUT$]', "<a href=\"logout.php\">$LoutStr</a>", $Footer);

//Poster in the last 15 minutes
$PostersPrefix = ' ';
$PostersSuffix = '  |';

// Allows formatting of the 15 Minutes posters list prefix
$PreStmp = strstr($Header, '[$15POSTPREFIX=');
if ($PreStmp) {
    $PreSE = strpos($PreStmp, '"$]');
    $PostersPrefix = substr($PreStmp, 16, $PreSE-16);
}
$SufStmp = strstr($Header, '[$15POSTSUFFIX=');
if ($SufStmp) {
    $SufSE = strpos($SufStmp, '"$]');
    $PostersSuffix = substr($SufStmp, 16, $SufSE-16);
}

if (preg_match('[$15POSTERS$]', $Header) || preg_match('[$15POSTERS$]', $Footer)) {
    $PosterQuery = 'SELECT Users.vc_Username
                                    FROM Users
                                    WHERE ( NOW() - Users.dt_LastPosted ) < 900
                                    ORDER BY Users.dt_LastPosted DESC';

    // Get the posters
    $PosterResultId = mysql_query ($PosterQuery, $link);

    // output comments
    while (  $PosterResult = mysql_fetch_object($PosterResultId) ) {
        $PosterStr .=  "$PostersPrefix$PosterResult->vc_Username$PostersSuffix";
    }
    $Header = str_replace('[$15POSTERS$]', $PosterStr, $Header);
    $Comment = str_replace('[$15POSTERS$]', $PosterStr, $Comment);
    $Footer = str_replace('[$15POSTERS$]', $PosterStr, $Footer);
}

//Visitors in the last 15 minutes
$VisitorPrefix = ' ';
$VisitorSuffix = '  |';

// Allows formatting of the 15 Minutes visitors list prefix
$PreStmp = strstr($Header, '[$15VISITPREFIX=');
if ($PreStmp) {
    $PreSE = strpos($PreStmp, '"$]');
    $VisitorPrefix = substr($PreStmp, 17, $PreSE-17);
}
$SufStmp = strstr($Header, '[$15VISITSUFFIX=');
if ($SufStmp) {
    $SufSE = strpos($SufStmp, '"$]');
    $VisitorSuffix = substr($SufStmp, 17, $SufSE-17);
}

if (preg_match('[$15LURKERS$]', $Header) || preg_match('[$15LURKERS$]', $Footer)) {
    $PosterQuery = ' SELECT  Users.vc_Username FROM Users WHERE DATE_ADD(dt_LastVisit, INTERVAL 15 MINUTE) > now() ORDER BY Users.dt_LastVisit DESC';
    // Get the posters
    $PosterResultId = mysql_query ($PosterQuery, $link);
     // output comments
    while (  $PosterResult = mysql_fetch_object($PosterResultId) ) {
        $LurkerStr .=  "$VisitorPrefix$PosterResult->vc_Username$VisitorSuffix";
    }
    $Header = str_replace('[$15LURKERS$]', $LurkerStr, $Header);
    $Comment = str_replace('[$15LURKERS$]', $LurkerStr, $Comment);
    $Footer = str_replace('[$15LURKERS$]', $LurkerStr, $Footer);
}

$DateFormat = '"%Y-%m-%e %T"';

// Allows formatting of the comment date into any allowed by mySQL
$DateFtmp = strstr($Header, '[$DATEFORMAT=');
if ($DateFtmp) {
    $DateFE = strpos($DateFtmp, '$]');
    $DateFormat = substr($DateFtmp, 13, $DateFE-13);
}

$comments_from_user=false;
if ( array_key_exists( 'username', $_REQUEST ) ) {
    $cfuquery = "select i_UID from Users where vc_Username like '" . urldecode($_REQUEST["username"]) . "'";
    $cfures = mysql_query( $cfuquery );
    if ( $cfu = mysql_fetch_object( $cfures ) ) {
        $comments_from_user = true;
        $cfu_id = $cfu->i_UID;
    }
}

// Get comments

$CommentsQuery = "SELECT    $CommentTable.i_CommentId,
        $CommentTable.t_Comment,
        DATE_FORMAT(DATE_ADD($CommentTable.dt_DatePosted, INTERVAL $GMTOffset HOUR), $DateFormat) as dt_DatePosted,
        Users.i_UID,
        Users.vc_Username,
        Users.vc_UserId,
        Category.vc_Name,
        Category.vc_CSSName
FROM    $CommentTable,
        Users,
        Category
WHERE   Users.i_UID = $CommentTable.i_UID
        AND Category.i_CategoryId = $CommentTable.i_CategoryId ";
if ( $comments_from_user ) {
    $CommentsQuery.=" AND Users.i_UID = {$cfu_id}
        ORDER BY i_CommentId DESC LIMIT {$txtPageSize} ";
} else {
    $CommentsQuery.=" AND $CommentTable.i_CommentId >=  $hdnCurrentRecord
        AND $CommentTable.i_CommentId <= ".($hdnCurrentRecord + $txtPageSize)."
        ORDER BY i_CommentId";
}
echo "<!-- cq: ".$CommentsQuery." -->";
$CommentsResultId = mysql_query ($CommentsQuery, $link);
$iCommentCount = 0;

// output header
echo $Header . "\n";

// output comments
while (  $CommentsResult = mysql_fetch_object($CommentsResultId)) {
    $iCommentCount = $CommentsResult->i_CommentId;
    $tComment = str_replace('[$COMMENTBUTTON$]', '<input class="[$CATCSSNAME$]LASTCMTBTN" type="submit" name="btnUpdateMyLastComment" value="[$COMMENTNUMBER$]">', $Comment);
    if ($CommentsResult->i_UID == $sessionUserId) {
        $tComment = str_replace('[$CATNAMELINK$]', '<a href="changecategory.php?id=[$COMMENTNUMBER$]" class="[$CATCSSNAME$]CHANGECATEGORYLINK"> [$CATEGORYNAME$]</a>', $tComment);
    } else {
        $tComment = str_replace('[$CATNAMELINK$]', '[$CATEGORYNAME$]', $tComment);
    }

    $tComment = str_replace('[$COMMENTNUMBER$]', $CommentsResult->i_CommentId, $tComment);
    $tComment = str_replace('[$CATCSSNAME$]', $CommentsResult->vc_CSSName, $tComment);
    $tComment = str_replace('[$USERNAME$]', $CommentsResult->vc_Username, $tComment);
    $tComment = str_replace('[$USERNUMBER$]', $CommentsResult->vc_UserId, $tComment);
    $tComment = str_replace('[$COMMENTTEXT$]', $CommentsResult->t_Comment, $tComment);
    $tComment = str_replace('[$CATEGORYNAME$]', $CommentsResult->vc_Name, $tComment);
    $tComment = str_replace('[$COMMENTDATETIME$]', $CommentsResult->dt_DatePosted, $tComment);
    echo $tComment . "\n";
}

echo $Footer;

// Update the DateLastVisit information
$UpdateLastCommentQuery = "UPDATE Users SET dt_LastVisit = NOW(), i_CommentId = $iCommentCount - 10 WHERE i_UID = $sessionUserId";
$UpdateLastCommentResultId = mysql_query ($UpdateLastCommentQuery, $link);

// close connection to MySQL Database
mysql_close($link);
$TimeCheck2 = time()- $timecheck1;

echo "<!-- Page Processing time: $TimeCheck2 Seconds -->";
echo "<!-- Tagline Id : $TaglineId -->";

/* end of main.php */
