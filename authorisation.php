<head>
<link href="shedow_style.css" rel="stylesheet" type="text/css">
<meta charset="utf-8">
</head>
<div id="header">
<?php
/**
*  Main authorization page included in every page of the site.
*/
include "db.php";  //connecting to data base
// if no connect, error and exit()
if(!($db)) { 
  echo "Unable to connect to data base server";
  exit();
}
session_start();
//  ***********  LANGUAGE CHOOSING  ******************
// if we have lang variable in query string, choose this language
if (isset($_GET['lang'])) {
  $lang = $_GET['lang'];
  $_SESSION['lang'] = $lang;
}
// else if we have language in our session
elseif (isset($_SESSION['lang'])) {
  $lang = $_SESSION['lang'];
}
// if not choose english by default
else {
    $lang = 'en';
    $_SESSION['lang'] = $lang;
  }
// including right language file
if ($lang == 'en') {
  $lang_file = 'lang_en.php';
}
else {
  $lang_file = 'lang_ua.php';
}
include_once $lang_file;
?>

<span id='flags'>
<a href='index.php?lang=en'><img src='images/uk.gif'></a>
&#x00a0; &#x00a0;
<a href='index.php?lang=ua'><img src='images/ua.gif'></a><br>
</span>

<?php
//  *************************  AUTHORIZATION  **********************
// if we're not authorized
if (!isset($_SESSION['user'])) {
  // if we're logging in
  if (isset($_POST['submit'])) {
   	// looking the user with the same user_name
    $stmt = $db->query("SELECT user_name, user_password, user_role FROM users WHERE user_name='" . $_POST['login'] . "'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if(($user['user_password'] === md5($_POST['pass'])) && ($user['user_name'] === $_POST['login'])) {
      // if success authorization:
      $_SESSION['user'] = $user['user_name'];
      $_SESSION['user_role'] = $user['user_role'];
      // User roles:
      // 4 - admin
      // 3 - moder
      // 2 - user
      // 1 - banned
      // authorization and updated last visit information:
      $visit = $db->prepare("UPDATE users SET user_last_visit= :visit WHERE user_name='" . $_SESSION['user'] . "'");
      $visit->bindParam(':visit', date("d-m-Y H:i"));
      $visit->execute();
      if ($_SESSION['user_role'] == 1) {
        session_destroy();
        header("Location: index.php?ban=true");
      }
      else {
?>

<span id='large_text'><?=$language['welcome']?>, <?=$_SESSION['user']?> 
<a href='profile.php?user=<?=$_SESSION['user']?>'><?=$language['profile']?></a></span>

<?php 
        include "welcome.php";
        if ($_SESSION['user_role'] == 4) {
?>

<span id='large_text'><span id='admin'><a href='admin.php'>
<?=$language['admin_menu']?></a></span></span>";

<?php 
        }
        //header("Location: index.php");
      }
    }
    else {
      include "check.php";
      echo "<span id='white'>Wrong user name/password</span>";
    }
  }
  else {
    include "check.php";
  }
}
else {
  if ($_SESSION['user_role'] == 1) {
    echo "You are banned!";
    include "welcome.php";
  }
  else {
    echo "<span id='large_text'>" . $language['welcome'] . ", " . $_SESSION['user'] .
    " <a href='profile.php?user=" . $_SESSION['user'] . "'>" . $language['profile'] . "</a><span>";
    include "welcome.php";
    if ($_SESSION['user_role'] == 4) {
      echo "<span id='large_text'><span id='admin'><a href='admin.php'>" .
      $language['admin_menu'] . "</a></span></span>";
    }
  }
}
?>
</div>
