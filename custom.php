<?php

$SelectedCategory = $_REQUEST[ 'Category' ];

require_once 'include.php';

// establish connection to MySQL database or output error message.
$link = mysql_connect ($dbHost, $dbUser, $dbPassword);
if (!mysql_select_db($dbName, $link)) {
    echo mysql_errno() . ': ' . mysql_error() . '<br>';
}

$Title = 'Search';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Comment Search</title>
</head>
<body>

<?php
    $CatOptions = '<option value="-1">search all categories</option>';
    $CategoryListQuery = 'SELECT i_CategoryId
                               , vc_Name
                            FROM Category
                        ORDER BY vc_Name';
    $CategoryListResultId =  mysql_query ($CategoryListQuery, $link);

    while ( $CategoryListResult = mysql_fetch_object($CategoryListResultId) ) {
        $CatOptions .= "<option value=\"$CategoryListResult->i_CategoryId\"";

        if ( $SelectedCategory == $CategoryListResult->i_CategoryId ) {
            $CatOptions .= ' selected';
        }

        $CatOptions .= ">$CategoryListResult->vc_Name</option>";
    }

    $intMinCommentArchive = 1;
    $intTrueMaxCommentArchive = 29;
    $intMaxCommentArchive = $intTrueMaxCommentArchive;
    $strCurrentArchive = '';

    $intFirstCommentId = 0;
    $intLastCommentId = -1;
    $strCommentRange = '';

    $LookFor = $_REQUEST[ 'LookFor' ];
    $ArchiveToSearch = $_REQUEST[ 'ArchiveToSearch' ];
    $ByUser = $_REQUEST[ 'ByUser' ];

    $Total = 0;
    echo '<h1>' . $Title . '</h1>';
    if ( !Empty( $LookFor ) ) {
        echo "Results for <em>$LookFor</em>";

        if ( $ByUser != '' ) {
            echo " posted by user <em>$ByUser</em>";
        }

        if ( $ArchiveToSearch > 0 ) {
            echo " in CommentArchive$ArchiveToSearch ";
            $intMinCommentArchive = $ArchiveToSearch;
            $intMaxCommentArchive = $ArchiveToSearch;
        } else if ( $ArchiveToSearch == 0 ) {
            $intMinCommentArchive = 1001;
            $intMaxCommentArchive = 0;
        }

        echo '<small>';

        for ( $intCurrentCA = $intMinCommentArchive; $intCurrentCA <= $intMaxCommentArchive; $intCurrentCA++ ) {
            $strCurrentArchive = "CommentArchive$intCurrentCA";

            $Query = "SELECT count(t_Comment) as counter
                        FROM $strCurrentArchive ";
            if ( $ByUser != '' ) {
                $Query .= "JOIN Users On Users.i_UID = $strCurrentArchive.i_UID
                AND Users.vc_Username = \"$ByUser\" ";
            }

            $Query .= "   WHERE t_Comment LIKE '%$LookFor%'";

            if ( $SelectedCategory > 0 ) {
              $Query .= " AND i_CategoryId = $SelectedCategory";
            }

            $Q2 = "     SELECT $strCurrentArchive.i_CommentId AS
                  LABELLER FROM $strCurrentArchive ";

            if ( $ByUser != '' ) {
                $Q2 .= "JOIN Users On Users.i_UID =
                        $strCurrentArchive.i_UID
                    AND Users.vc_Username = \"$ByUser\" ";
            }

            $Q2 .= " WHERE t_Comment LIKE '%$LookFor%' ";

            if ( $SelectedCategory > 0 ) {
                $Q2 .= " AND i_CategoryId = $SelectedCategory";
            }
            $Q2 .= " ORDER BY $strCurrentArchive.i_CommentId ASC";

            $UInfId = mysql_query ($Query, $link);
            $UInfRes = mysql_fetch_object($UInfId);

            $intFirstCommentId = ( ($intCurrentCA-1) * 50000) + 1;
            $intLastCommentId = $intFirstCommentId + 49999;

            $strCommentRange = "$intFirstCommentId - $intLastCommentId";

            if (!Empty($UInfRes->COUNTER)) {
                echo "<hr>$UInfRes->COUNTER Results in $strCommentRange<br>"; $Total += $UInfRes->COUNTER;
            } else {
                echo "<hr>0 Results in $strCommentRange<br>";
            }
            $UInfId2 = mysql_query ($Q2, $link);
            while ($UInfRes = mysql_fetch_object($UInfId2)) {
                echo "<a href=\"main.php?ViewPost=$UInfRes->LABELLER\">p$UInfRes->LABELLER</a> | ";
            }
        }

        // search comment table
        if ( $ArchiveToSearch < 1 ) {
            $strCurrentArchive = 'Comment';

            $Query = "  SELECT COUNT(t_Comment) AS COUNTER
                    FROM $strCurrentArchive ";
            if ( $ByUser != '' ) {
                $Query .= "JOIN Users on Users.i_UID = Comment.i_UID
                    AND Users.vc_Username = \"$ByUser\" ";
            }
            $Query .= "   WHERE t_Comment LIKE '%$LookFor%' ";

            if ( $SelectedCategory > 0 ) {
                $Query .= " AND i_CategoryId = $SelectedCategory";
            }

            $Q2 = "     SELECT $strCurrentArchive.i_CommentId AS LABELLER
                    FROM $strCurrentArchive ";
            if ( $ByUser != '' ) {
               $Q2 .= "JOIN Users on Users.i_UID = Comment.i_UID
                   AND Users.vc_Username = \"$ByUser\" ";
            }
            $Q2 .= " WHERE t_Comment LIKE '%$LookFor%' ";

            if ( $SelectedCategory > 0 ) {
                $Q2 .= " AND i_CategoryId = $SelectedCategory";
            }
            $Q2 .= " ORDER BY $strCurrentArchive.i_CommentId ASC";

            $Query = $Q2;
            $UInfId = mysql_query ($Query, $link);
            $UInfRes = mysql_fetch_object($UInfId);

            $intFirstCommentId = ($intTrueMaxCommentId * 50000) + 1;
            $intLastCommentId = $intFirstCommentId + 49999;

            $strCommentRange = 'Current Comment Table';

            if (!Empty($UInfRes->COUNTER)) {
                echo "<hr>$UInfRes->COUNTER Results in $strCommentRange<br>"; $Total += $UInfRes->COUNTER;
            } else {
                echo "<hr>0 Results in $strCommentRange<br>";
            }
            $UInfId2 = mysql_query ($Q2, $link);
            while ($UInfRes = mysql_fetch_object($UInfId2)) {
                echo "<a href=\"main.php?ViewPost=$UInfRes->LABELLER\">p$UInfRes->LABELLER</a> | ";
            }
        }
    }
?>
</small><br><br>
<?php
    echo "$Total Total Results Found";
?>
<br>
<br>
    <form action="custom.php" method="post">
    <table>
        <tr>
            <td width="20%">
                Search Phrase:
            </td>
            <td>
                <input type="text" name="LookFor"
value="<?php echo $LookFor; ?>">
            </td>
        </tr>
        <tr>
            <td>
                    Search by user
            </td>
            <td>
                <input type="text" name="ByUser"
value="<?php echo $ByUser ?>">
            </td>
        </tr>
        <tr>
            <td>
                Search comment archive:
            </td>
            <td>
                <select name="ArchiveToSearch">
                    <option value="-1"
<?php if ( $ArchiveToSearch == -1 ) { echo ' selected'; } ?>
                >Search all archives</option>
                    <option value="0"
<?php if ( $ArchiveToSearch == 0 ) { echo ' selected'; } ?>
                >Search current comment table</option>
<?php
for( $intCurrentCA = 1; $intCurrentCA <= $intTrueMaxCommentArchive; $intCurrentCA++ ) {
    echo "<option value=\"$intCurrentCA\"";
    if ( $intCurrentCA == $ArchiveToSearch ) {
        echo ' selected';
    }
    echo ">Comment Archive $intCurrentCA</option>";
}
?>
                </select>
            </td>
        </tr>
    <tr>
      <td>Select a category to search
      </td>
      <td>
        <select name="Category">
          <?php
            echo $CatOptions
          ?>
        </select>
      </td>
    </tr>
    </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit"></td>
        </tr>
    </table>
    </form>
<a href="/">Main</a> | <a href="Statistics.php">Re-Select</a> | <a href="UserInfo.php">Users</a> | <a href="oddball.php">Random Statistics</a>
</body>
</html>
<?php
// close connection to MySQL Database
mysql_close($link);
?>
