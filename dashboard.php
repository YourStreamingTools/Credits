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

$database_name = "{$username}.db";
$conn = new SQLite3($database_name);
$followerResults = $conn->query("SELECT follower_name, timestamp FROM followers ORDER BY timestamp DESC");
$subscriberResults = $conn->query("SELECT subscriber_name, timestamp FROM subscribers ORDER BY timestamp DESC");
$cheerResults = $conn->query("SELECT username, cheer_amount, timestamp FROM cheers ORDER BY timestamp DESC");
$raidResults = $conn->query("SELECT raider_name, timestamp FROM raids ORDER BY timestamp DESC");
$conn->close();
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
      <li class="is-active"><a href="dashboard.php">Dashboard</a></li>
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
<!-- Add sections to display fetched data using custom styling -->
<div class="data-section">
  <h2>Recent Followers</h2>
  <ul class="custom-list">
    <?php
      // Fetch and display recent follower data from the SQLite database
      while ($row = $followerResults->fetchArray(SQLITE3_ASSOC)) {
        echo "<li>{$row['follower_name']} - {$row['timestamp']}</li>";
      }
    ?>
  </ul>
</div>

<div class="data-section">
  <h2>Recent Subscribers</h2>
  <ul class="custom-list">
    <?php
      // Fetch and display recent subscriber data from the SQLite database
      while ($row = $subscriberResults->fetchArray(SQLITE3_ASSOC)) {
        echo "<li>{$row['subscriber_name']} - {$row['timestamp']}</li>";
      }
    ?>
  </ul>
</div>

<div class="data-section">
  <h2>Recent Cheers</h2>
  <ul class="custom-list">
    <?php
      // Fetch and display recent cheers data from the SQLite database
      while ($row = $cheerResults->fetchArray(SQLITE3_ASSOC)) {
        echo "<li>{$row['username']} cheered {$row['cheer_amount']} bits - {$row['timestamp']}</li>";
      }
    ?>
  </ul>
</div>

<div class="data-section">
  <h2>Recent Raids</h2>
  <ul class="custom-list">
    <?php
      // Fetch and display recent raid data from the SQLite database
      while ($row = $raidResults->fetchArray(SQLITE3_ASSOC)) {
        echo "<li>{$row['raider_name']} - {$row['timestamp']}</li>";
      }
    ?>
  </ul>
</div>
</div>

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://dhbhdrzi4tiry.cloudfront.net/cdn/sites/foundation.js"></script>
<script>$(document).foundation();</script>
<script>
  // JavaScript function to handle the category filter change
  document.getElementById("categoryFilter").addEventListener("change", function() {
    var selectedCategoryId = this.value;
    // Redirect to the page with the selected category filter
    window.location.href = "dashboard.php?category=" + selectedCategoryId;
  });
</script>
</body>
</html>