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

// Get the current hour in 24-hour format (0-23)
$currentHour = date('G');
// Initialize the greeting variable
$greeting = '';
// Check if it's before 12 PM (noon)
if ($currentHour < 12) {
    $greeting = "Good morning";
} else {
    $greeting = "Good afternoon";
}

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
$userSTMT->close();

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

if (isset($_POST['runBot'])) {

  // Execute the Python script with the channel name as an argument
  $output = shell_exec("python bot.py -channel $username -channelid $twitchUserId -token $botToken > /dev/null 2>&1 &");
}
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
    <!-- <?php echo "User: $username with User ID: $twitchUserId"; ?> -->
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
<form action="" method="post">
    <button class="defult-button" type="submit" name="runBot">Run Bot</button>
</form>
<?php if ($is_admin) { ?><br><a href="bot-login.php"><button class="defult-button"name="BotLogin">Bot Loin</button></a><?php } ?>
</div>
</div>

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://dhbhdrzi4tiry.cloudfront.net/cdn/sites/foundation.js"></script>
<script>$(document).foundation();</script>
</body>
</html>