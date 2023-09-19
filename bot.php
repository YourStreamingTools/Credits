<?php
// Initialize the session
session_start();

// check if user is logged in
if (!isset($_SESSION['access_token'])) {
    header('Location: login.php');
    exit();
}

// Connect to database
require_once "db_connect.php";

// Default Timezone Settings
$defaultTimeZone = 'Etc/UTC';
$user_timezone = $defaultTimeZone;

$webhookURL = '';
// Fetch the user's data from the database based on the access_token
$access_token = $_SESSION['access_token'];
$userSTMT = $conn->prepare("SELECT * FROM users WHERE access_token = ?");
$userSTMT->bind_param("s", $access_token);
$userSTMT->execute();
$userResult = $userSTMT->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'];
$username = $user['username'];
$twitchDisplayName = $user['twitch_display_name'];
$twitch_profile_image_url = $user['profile_image'];
$is_admin = ($user['is_admin'] == 1);
$twitchUserId = $user['twitch_user_id'];
$authToken = $access_token;
$user_timezone = $user['timezone'];
date_default_timezone_set($user_timezone);

// Determine the greeting based on the user's local time
$currentHour = date('G');
$greeting = '';

if ($currentHour < 12) {
    $greeting = "Good morning";
} else {
    $greeting = "Good afternoon";
}

$botSTMT = $conn->prepare("SELECT * FROM bot");
$botSTMT->execute();
$botResult = $botSTMT->get_result();
$botData = $botResult->fetch_assoc();
$botToken = $botData['access_token'];
$botUsername = $botData['username'];
$botDisplayName = $botData['display_name'];
$botUserId = $botData['user_id'];
$botProfileImageUrl = $botData['profile_image'];
$botSTMT->close();
$statusOutput = 'Bot Status: Unkown';
$pid = '';
include 'bot_control.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>YourStreamingTools - Dashboard</title>
    <link rel="stylesheet" href="https://dhbhdrzi4tiry.cloudfront.net/cdn/sites/foundation.min.css">
    <link rel="stylesheet" href="https://cdn.yourstreaming.tools/css/custom.css">
    <script src="https://cdn.yourstreaming.tools/js/about.js"></script>
  	<link rel="icon" href="https://cdn.yourstreaming.tools/img/logo.jpeg">
  	<link rel="apple-touch-icon" href="https://cdn.yourstreaming.tools/img/logo.jpeg">
    <!-- <?php echo "User: $username | $twitchUserId | $authToken"; ?> -->
  </head>
<body>
<!-- Navigation -->
<div class="title-bar" data-responsive-toggle="mobile-menu" data-hide-for="medium">
  <button class="menu-icon" type="button" data-toggle="mobile-menu"></button>
  <div class="title-bar-title">Menu</div>
</div>
<nav class="top-bar stacked-for-medium" id="mobile-menu">
  <div class="top-bar-left">
    <ul class="dropdown vertical medium-horizontal menu" data-responsive-menu="drilldown medium-dropdown hinge-in-from-top hinge-out-from-top">
      <li class="menu-text">YourStreamingTools</li>
      <li><a href="index.php">Dashboard</a></li>
      <li class="is-active"><a href="bot.php">Bot</a></li>
      <li>
        <a>Profile</a>
        <ul class="vertical menu" data-dropdown-menu>
          <li><a href="profile.php">View Profile</a></li>
          <li><a>Twitch Data</a>
          <ul class="vertical menu" data-dropdown-menu>
            <li><a href="mods.php">View Mods</a></li>
            <li><a href="followers.php">View Followers</a></li>
            <li><a href="subscribers.php">View Subscribers</a></li>
            <li><a href="vips.php">View VIPs</a></li>
          </ul>
          </li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </li>
    </ul>
  </div>
  <div class="top-bar-right">
    <ul class="menu">
      <li><a class="popup-link" onclick="showPopup()">&copy; 2023 YourStreamingTools. All rights reserved.</a></li>
    </ul>
  </div>
</nav>
<!-- /Navigation -->

<div class="row column">
<br>
<h1><?php echo "$greeting, <img id='profile-image' src='$twitch_profile_image_url' width='50px' height='50px' alt='$twitchDisplayName Profile Image'>$twitchDisplayName!"; ?></h1>
<br>
<h3><?php echo $statusOutput; ?></h3>
<br>
<table style="border: none !important;">
  <tr>
    <td><form action="" method="post"><button class="defult-button" type="submit" name="runBot">Run Bot</button></form></td>
    <td><form action="" method="post"><button class="defult-button" type="submit" name="botStatus">Check Bot Status</button></form></td>
    <td><form action="" method="post"><button class="defult-button" type="submit" name="killBot">Stop Bot</button></form></td>
    <td><form action="" method="post"><button class="defult-button" type="submit" name="restartBot">Restart Bot</button></form></td>
  </tr>
</table>
<?php if ($is_admin) { ?><br><a href="bot-login.php"><button class="defult-button"name="BotLogin">Bot Loin</button></a><?php } ?>
</div>
</div>

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://dhbhdrzi4tiry.cloudfront.net/cdn/sites/foundation.js"></script>
<script>$(document).foundation();</script>
</body>
</html>