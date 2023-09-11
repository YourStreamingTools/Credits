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
include 'database.php';

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

$stmt = $conn->prepare("SELECT * FROM users WHERE access_token = ?");
$stmt->bind_param("s", $access_token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$username = $user['username'];
$twitchDisplayName = $user['twitch_display_name'];
$twitch_profile_image_url = $user['profile_image'];
$is_admin = ($user['is_admin'] == 1);
$webhookURL = $user['webhook_url'];
$stmt->close();

$database_name = "{$username}.db";
// Create the SQLite database if it doesn't exist
if (!file_exists($database_name)) {
    createTables($database_name);
}
if (isset($_POST['resetDatabase'])) {
    resetDatabase($database_name);
}

$totalRaidersToday = 0;
$totalViewersFromRaids = 0;
$conn = new SQLite3($database_name);
$followerResults = $conn->query("SELECT follower_name, timestamp FROM followers ORDER BY timestamp DESC");
$subscriberResults = $conn->query("SELECT subscriber_name, subscriber_tier, subscription_months, timestamp FROM subscribers ORDER BY timestamp DESC");
$cheerResults = $conn->query("SELECT username, cheer_amount, timestamp FROM cheers ORDER BY timestamp DESC");
$raidResults = $conn->query("SELECT raider_name, timestamp FROM raids ORDER BY timestamp DESC");

$followerData = array();
while ($row = $followerResults->fetchArray(SQLITE3_ASSOC)) {
  $followerData[] = $row;
}

$subscriberData = array();
while ($row = $subscriberResults->fetchArray(SQLITE3_ASSOC)) {
  $subscriberData[] = $row;
}

$cheerData = array();
while ($row = $cheerResults->fetchArray(SQLITE3_ASSOC)) {
  $cheerData[] = $row;
}

$raidData = array();
while ($row = $raidResults->fetchArray(SQLITE3_ASSOC)) {
  $raidData[] = $row;
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
    <script src="sendtodiscord.js"></script>
    <script>var webhookURL = "<?php echo $webhookURL; ?>";</script>
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
      <li class="is-active"><a href="index.php">Dashboard</a></li>
      <li><a href="bot.php">Bot</a></li>
      <li>
        <a>Profile</a>
        <ul class="vertical menu" data-dropdown-menu>
          <li><a href="profile.php">View Profile</a></li>
          <li><a href="mods.php">View Mods</a></li>
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
<!--<form method="post"><button type="submit" name="deleteDatabase" class="button alert">Reset Data</button></form>-->
<br>
<!-- Display sections for each data type -->
<?php
// Fetch and display recent followers data from the SQLite database
echo "<div class='data-section'>";
echo "<h4>Recent Followers";
echo "<button class='button float-right' onclick='sendToDiscord(\"followers\", this.id)'>Send to Discord</button></h4>";
echo "<table class='custom-table'>";
echo "<tr><th>Follower</th>";
echo "<th>Timestamp</th></tr>";

while ($row = $followerResults->fetchArray(SQLITE3_ASSOC)) {
  echo "<tr><td>{$row['follower_name']}</td>";
  echo "<td>{$row['timestamp']}</td></tr>";
}

echo "</table>";
echo "</div>";

// Fetch and display recent subscribers data from the SQLite database
echo "<div class='data-section'>";
echo "<h4>Recent Subscribers";
echo "<button class='button float-right' onclick='sendToDiscord(\"subscribers\")'>Send to Discord</button></h4>";
echo "<table class='custom-table'>";
echo "<tr><th>Subscriber Name</th>";
echo "<th>Tier</th>";
echo "<th>Months</th>";
echo "<th>Timestamp</th></tr>";

while ($row = $subscriberResults->fetchArray(SQLITE3_ASSOC)) {
  $tier = ($row['subscriber_name'] === 'Tier 1') ? '1' : (($row['subscriber_name'] === 'Tier 2') ? '2' : '3');
  $months = getMonthsFromTimestamp($row['timestamp']);
  
  echo "<tr><td>{$row['subscriber_name']}</td>";
  echo "<td>{$tier}</td>";
  echo "<td>{$months}</td>";
  echo "<td>{$row['timestamp']}</td></tr>";
}

echo "</table>";
echo "</div>";

// Fetch and display recent cheers data from the SQLite database
echo "<div class='data-section'>";
echo "<h4>Recent Cheers";
echo "<button class='button float-right' onclick='sendToDiscord(\"cheers\")'>Send to Discord</button></h4>";
echo "<table class='custom-table'>";
echo "<tr><th>Username</th>";
echo "<th>Cheer Amount</th>";
echo "<th>Timestamp</th></tr>";

while ($row = $cheerResults->fetchArray(SQLITE3_ASSOC)) {
  echo "<tr><td>{$row['username']}</td>";
  echo "<td>{$row['cheer_amount']} bits</td>";
  echo "<td>{$row['timestamp']}</td></tr>";
}
echo "</table>";
echo "</div>";

// Fetch and display recent raid data from the SQLite database
echo "<div class='data-section'>";
echo "<h4>Recent Raids";
echo "<button class='button float-right' onclick='sendToDiscord(\"raids\")'>Send to Discord</button></h4>";
echo "<table class='custom-table'>";
echo "<tr><th>Raider</th>";
echo "<th>Viewers</th></tr>";

while ($row = $raidResults->fetchArray(SQLITE3_ASSOC)) {
  echo "<tr><td>{$row['raider_name']}</td>";
  echo "<td>Viewers: {$row['viewers']}</td></tr>";
}
echo "<tr><td>Total ($totalRaidersToday)</td>";
echo "<td>$totalViewersFromRaids</td></tr>";

echo "</table>";
echo "</div>";

$conn->close();
?>
</div>
</div>

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://dhbhdrzi4tiry.cloudfront.net/cdn/sites/foundation.js"></script>
<script>$(document).foundation();</script>
</body>
</html>