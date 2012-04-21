<?php

require_once 'session.php';
require_once 'include.php';

// establish connection to MySQL database or output error message.
$link = mysql_connect ($dbHost, $dbUser, $dbPassword);
if (!mysql_select_db($dbName, $link)) {
    echo mysql_errno() . ': ' . mysql_error() . '<br>';
}

$sessionUserId = $_SESSION[ 'sessionUserId' ];

// if user is not logged in, show message and login inputs
if ( $_SESSION['sessionUserId'] == -1 ) {
    echo 'An error related to session occured.  Please make sure you\'re loading favorites.php.';
    exit;
} else {
    $this_user = $_SESSION['sessionUserId'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
         "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?php $siteTitle ?> - Favorites</title>
  </head>
  <body lang="en">
<?php
    if ( array_key_exists( 'username', $_REQUEST ) ) {
        $username = sql_quote( $_REQUEST[ 'username' ] );
        $findusername_query = "SELECT Users.i_UID FROM Users WHERE vc_Username='{$username}'";

        $findusername_resultid = mysql_query ($findusername_query, $link);
        $findusername = mysql_fetch_object( $findusername_resultid );
        $find_userid = $findusername->i_UID;
    }

    if ( array_key_exists( 'add', $_REQUEST ) ) {
        if ( is_numeric( $_REQUEST[ 'add' ] ) ) {
            $favorite_id = $_REQUEST[ 'add' ];
            $favorite_exists = "SELECT COUNT(*) as count_fav
                                FROM Favorites
                                WHERE i_UID = {$this_user}
                                AND i_CommentId = {$favorite_id}";

            $favorite_exists_result = mysql_query( $favorite_exists, $link );
            $favorite_obj = mysql_fetch_object( $favorite_exists_result );
            if ( $favorite_obj->count_fav == 0 ) {
                $add_favorite_query = "INSERT INTO Favorites
                                        (i_UID, i_CommentId)
                                       VALUES ({$this_user}, {$favorite_id})";
                mysql_query( $add_favorite_query );
                echo 'Favorite Added!';
            }
        }
    }

    if ( array_key_exists( 'delete', $_REQUEST ) ) {
        if ( is_numeric( $_REQUEST[ 'delete' ] ) ) {
            $favorite_id = $_REQUEST[ 'delete' ];

            $add_favorite_query = "DELETE FROM  Favorites
                                   WHERE i_UID = {$this_user}
                                   AND i_CommentId = {$favorite_id}";
            mysql_query( $add_favorite_query );
            echo 'Favorite Deleted';
        }
    }

    if ( array_key_exists( 'yourfavoritecount', $_REQUEST ) ) {
        if ( is_numeric( $_REQUEST[ 'yourfavoritecount' ] ) ) {
            for ( $favcount = 0; $favcount < $_REQUEST[ 'yourfavoritecount' ]; $favcount++ ) {
                $annotation = sql_quote( $_REQUEST[ "annotation_{$favcount}" ] );
                $commentid = $_REQUEST[ "commentid_{$favcount}" ];
                $update_favorite_query = "UPDATE Favorites
                                          SET vc_Annotation = '{$annotation}'
                                           WHERE i_UID = {$this_user}
                                           AND i_CommentId = {$commentid}";
                mysql_query( $update_favorite_query );
            }
        }
    }
?>
    <div name="userfavorites" id="userfavorites">
      <form name="viewfavorites" id="viewfavorites" action="favorites.php" method="post">
        <label for="username">username to view favorites of:</label>
        <input type="text" name="username" id="username" value="<?php
          echo $username;
        ?>" />
        <input type="submit" name="chooseuser" id="chooseuser" value="go" />
      </form>

      <?php
      if ( array_key_exists( 'chooseuser', $_REQUEST ) ) {
          echo '<dl name="userfavoriteslist" id="userfavoriteslist">';
          $fav_query = "SELECT i_CommentId, vc_Annotation FROM Favorites WHERE i_UID = {$find_userid} ORDER BY i_CommentId ASC";
          $fav_result = mysql_query( $fav_query, $link );
          while ( $favorite = mysql_fetch_object( $fav_result ) ) {
              echo '<dt><a href="main.php?ViewPost=' . $favorite->i_CommentId . '">' . $favorite->i_CommentId . '</a></dt>';
              echo '<dd>' . $favorite->vc_Annotation . '</dd>';
          }
          echo '</dl>';
      }
      ?>
    </div>

    <div name="yourfavorites" id="yourfavorites">
    <h3>Your favorites</h3>
    <?php
        $fav_query = "SELECT i_CommentId, vc_Annotation FROM Favorites WHERE i_UID = {$this_user} ORDER BY i_CommentId ASC";
        $fav_result = mysql_query( $fav_query, $link );
        echo '<form name="yourfavoritesform" id="yourfavoritesform" action="favorites.php" method="post">';
        echo '<dl name="yourfavoriteslist" id="yourfavoriteslist">';
        $yourfavoritecount = 0;
        while ( $favorite = mysql_fetch_object( $fav_result ) ) {
            echo '<dt><a href="main.php?ViewPost=' . $favorite->i_CommentId . '">' . $favorite->i_CommentId . '</a>';
            echo "<input type=\"hidden\" name=\"commentid_{$yourfavoritecount}\" id=\"commentid_{$yourfavoritecount}\" value=\"{$favorite->i_CommentId}\" /></dt>";
            echo '<dd>(<a href="favorites.php?delete=' . $favorite->i_CommentId . '">del</a>)</dd>';
            echo "<dd><input type=\"text\" name=\"annotation_{$yourfavoritecount}\" value=\"{$favorite->vc_Annotation}\" />";
            $yourfavoritecount++;
        }
        echo '</dl>';
        echo '<input type="hidden" name="yourfavoritecount" id="yourfavoritecount" value="' . $yourfavoritecount . '" />';
        echo '<input type="submit" name="submitannotation" id="submitannotation" value="save annotations" />';
        echo '</form>';
    ?>
    </div>

    <div name="popularity" id="popularity">
    <h3>Popularity</h3>
    <?php
        $fav_query = 'SELECT i_CommentId, COUNT( i_CommentId ) as efts
                      FROM Favorites
                      GROUP BY i_CommentId
                      ORDER BY efts DESC, i_CommentId ASC';
        $fav_result = mysql_query( $fav_query, $link );
        echo '<dl>';
        while ( $favorite = mysql_fetch_object( $fav_result ) ) {
            echo '<dt><a href="main.php?ViewPost=' . $favorite->i_CommentId . '">' . $favorite->i_CommentId . '</a>';
            echo '(' . $favorite->efts . ')</dt>';
            $annotation_query = 'SELECT vc_Annotation
                                        , Users.vc_Username
                                 FROM Favorites
                                 INNER JOIN Users on Users.i_UID = Favorites.i_UID
                                 WHERE Favorites.i_CommentId = ' . $favorite->i_CommentId;
            $annotation_result = mysql_query( $annotation_query );
            echo mysql_error();
            while ( $annotation = mysql_fetch_object( $annotation_result ) ) {
                if ( strlen( $annotation->vc_Annotation ) > 0 ) {
                    echo "<dd>{$annotation->vc_Username}: ";
                    echo stripslashes($annotation->vc_Annotation);
                    echo '</dd>';
                }
            }
        }
        echo '</dl>';
    ?>
    <hr />
    How to use:<br />
    This page takes a request variable (POST or GET) named "add", which is the id of the post to add to the favorites list of the logged in user.
    For example, to add post #1234 as a favorite, you could create a url:  favorites.php?add=1234.
    <br /><br />To delete a comment, use the "delete" command, ie: favorite.php?delete=1234.  Delete links are available in the list of your favorites
    <br /><br />To view a specific user's favorites, enter their username into the field above.
  </body>
</html>
