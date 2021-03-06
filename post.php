<?php

ini_set('display_errors', 1);

require_once( "session.php" );
require_once( "include.php" );
require_once( "Comment.php" );

// if user is not logged in, show message and login inputs
if ( $_SESSION[ "sessionUserId" ] == -1 ) {
    header ("Location: index.php");
}

if ( null ===  $_SESSION[ "sessionUserId" ] ) {
    echo "<br>Unmatched ID";
    print_r( $_SESSION );
    echo "<br>SESSION:".$_SESSION[ "sessionUserId" ];
    echo "<br>REQUEST:".$_REQUEST[ "hdnUID" ];
    echo "<br>An error occured, generally of the sort that occurs when
                sessions timeout.  Please copy your comment and try hitting
                back and posting again.";

    echo "<br>";
    echo "your post was roughly:";
    echo "<textarea>".stripslashes( $_REQUEST[ "txtComment" ] )."</textarea>";
    exit;
}

if (!Empty( $_REQUEST[ "btnSubmitPreviewedComment" ] )) {
    if ( !Empty( $_REQUEST[ "txtComment" ] ) ) {
        $txtComment = $_REQUEST[ "txtComment" ];

        // escape characters.
        $txtComment = str_replace("\<", "&lt;", $txtComment);

        // remove loop-causing
        $txtComment = str_replace("[\$COMMENTTEXT\$]", "[ \$COMMENTTEXT \$ ]", $txtComment);

        // convert new lines to br tags
        if ( array_key_exists( "nobr", $_REQUEST ) && $_REQUEST[ "nobr" ] == "nobr" ) {
        }
        else {
          $txtComment = nl2br($txtComment);
        }


        // get the rest of the form variables:
        $hdnUID = $_SESSION['sessionUserId'];
        $selCategoryId = $_REQUEST[ "selCategoryId" ];

        $comment = new Comment( $db );
        $comment->create( $txtComment, $hdnUID, $selCategoryId );

        $UpdateLastPostedQuery = "UPDATE Users SET dt_LastPosted = NOW() WHERE i_UID = $hdnUID";
        $UpdateLastPostedQuery = mysqli_query ($link, $UpdateLastPostedQuery );

        header ("Location: main.php");
        exit;
    }
    else {
        echo "No empty comments, please.";
    }
}
else {
    // Preview the comment instead of submitting it
    $txtComment = $_REQUEST[ "txtComment" ];
    $txtComment = stripslashes($txtComment);
    $txtComment = nl2br($txtComment);

    $CategoryListQuery = "SELECT i_CategoryId, vc_Name, vc_CSSName FROM Category  ORDER BY vc_Name";
    $CategoryListResultId = mysqli_query ($link, $CategoryListQuery);

    echo "<html><head><title><?php $siteTitle; ?> - post preview</title>";

    // Add the stylesheet
    $UserStyleQuery = "SELECT t_StyleSheet
                        FROM UserStyleSheet, DBStyleSheet
                        WHERE DBStyleSheet.i_StyleSheetId = UserStyleSheet.i_StyleSheetId
                        AND UserStyleSheet.i_UID = ".$_REQUEST[ "hdnUID" ];

    $StyleResId = mysqli_query ($link, $UserStyleQuery);
    if ( $StyleRes = mysqli_fetch_object($StyleResId) ) {
        // If a stylesheet for this user exists, do nothing here.
    }
    else {
        // This user does not have a stylesheet.  Fetch the default (id = 1)
        $UserStyleQuery = "SELECT t_StyleSheet
                                                FROM DBStyleSheet
                                                WHERE DBStyleSheet.i_StyleSheetId = 1";

        $StyleResId = mysqli_query ($link, $UserStyleQuery);
        $StyleRes = mysqli_fetch_object($StyleResId);
    }

    $StyleResId = mysqli_query ($link, $UserStyleQuery);
    $StyleRes = mysqli_fetch_object($StyleResId);

    echo "\n<style>";
    echo "\n@import url(/essl.css);";
    echo "\n";
    echo $StyleRes->t_StyleSheet;
    echo "\n</style>";
    echo "\n";
    echo "\n\n</head>";
    echo "\n";
    echo "<body>";

    // Inputs for a new comment
    echo "<form name=\"frmPreview\" action=\"post.php\" method=\"post\">";
    echo "<textarea name=\"txtComment\" cols=\"60\" rows=\"10\" wrap=\"virtual\">";
    echo stripslashes( $_REQUEST[ "txtComment" ] );
    echo "</textarea><br>";
    echo "<input type=\"hidden\" name=\"hdnUID\" value=\"";
    echo $_REQUEST[ "hdnUID" ];
    echo "\">";
    echo "<input type=\"submit\" name=\"btnSubmitComment\" value=\"Preview\" onclick=\"QuoteReplace(document.frmPreview);\">";
    echo "<input type=\"submit\" name=\"btnSubmitPreviewedComment\" value=\"Post\" onclick=\"QuoteReplace(document.frmPreview);\">";
    echo " to category: ";
    echo "<select name=\"selCategoryId\">";
    $selCategoryId = $_REQUEST[ "selCategoryId" ];

    while ( $CategoryListResult = mysqli_fetch_object($CategoryListResultId)) {
        echo "<option value=\"$CategoryListResult->i_CategoryId\"";
        if ( $CategoryListResult->i_CategoryId == $selCategoryId ) {
            $hdnCSSName = $CategoryListResult->vc_CSSName;
            echo " selected";
        }
        echo ">";
        echo $CategoryListResult->vc_Name;
        echo "</option>";
    }

    echo "</select>";
    echo "<br><a href=\"categories.php\">category explanations</a>";
    echo "</form>";

    // output comments
    $UserTemplateQuery = "SELECT i_TemplateID FROM UserTemplate WHERE UserTemplate.i_UID = ".$_REQUEST[ "hdnUID" ];
    $TemplateResId = mysqli_query ($link, $UserTemplateQuery);
    $TemplateRes = mysqli_fetch_object($TemplateResId);

    if (!Empty($TemplateRes->i_TemplateID)) {
        $TemplateQuery = "SELECT t_TemplateHdr, t_TemplateCmt, t_TemplateFtr FROM Template WHERE i_TemplateID = $TemplateRes->i_TemplateID";
    }
    else {
        $TemplateQuery = "SELECT t_TemplateHdr, t_TemplateCmt, t_TemplateFtr FROM Template WHERE i_TemplateID = 1";
    }

    $TemplateResId = mysqli_query ($link, $TemplateQuery);

    $TemplateRes = mysqli_fetch_object($TemplateResId);

    $tComment = str_replace("[\$COMMENTBUTTON\$]","<input class=\"[\$CATCSSNAME\$]LASTCMTBTN\" type=\"submit\" name=\"btnUpdateMyLastComment\" value=\"[\$COMMENTNUMBER\$]\">", $TemplateRes->t_TemplateCmt);
    $tComment = str_replace("[\$CATNAMELINK\$]", "[\$CATEGORYNAME\$]", $tComment);

    $tComment = str_replace("[\$COMMENTNUMBER\$]", "######", $tComment);
    $tComment = str_replace("[\$CATCSSNAME\$]", $hdnCSSName, $tComment);
    $tComment = str_replace("[\$USERNAME\$]", "Username", $tComment);
    $tComment = str_replace("[\$USERNUMBER\$]", "Usernumber", $tComment);
    $tComment = str_replace("[\$COMMENTTEXT\$]", $txtComment, $tComment);
    $tComment = str_replace("[\$CATEGORYNAME\$]", "Category Name", $tComment);
    $tComment = str_replace("[\$COMMENTDATETIME\$]", "Date/time", $tComment);
    echo $tComment."\n";
    echo "</body></html>";
}

?>
