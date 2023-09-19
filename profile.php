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
$signup_date = $user['signup_date'];
$last_login = $user['last_login'];
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedTimeZone = $_POST["timezone"];

    // Update the user's time zone in the database
    $stmt = $conn->prepare("UPDATE users SET timezone = ? WHERE id = ?");
    $stmt->bind_param("si", $selectedTimeZone, $user_id);
    $stmt->execute();

    // Update the user's time zone in the current session
    $user_timezone = $selectedTimeZone;
}

$timezones_query = $conn->query("SELECT * FROM timezones");
$timezones = [];

while ($row = $timezones_query->fetch_assoc()) {
    $timezones[] = $row['name'];
}

$signup_date_utc = date_create_from_format('Y-m-d H:i:s', $signup_date)->setTimezone(new DateTimeZone('UTC'))->format('F j, Y g:i A');
$last_login_utc = date_create_from_format('Y-m-d H:i:s', $last_login)->setTimezone(new DateTimeZone('UTC'))->format('F j, Y g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>YourStreamingTools - Profile</title>
    <link rel="stylesheet" href="https://dhbhdrzi4tiry.cloudfront.net/cdn/sites/foundation.min.css">
    <link rel="stylesheet" href="https://cdn.yourlist.online/css/custom.css">
    <link rel="icon" href="https://cdn.yourlist.online/img/logo.png" type="image/png" />
    <link rel="apple-touch-icon" href="https://cdn.yourlist.online/img/logo.png">
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
      <li><a href="bot.php">Bot</a></li>
      <li>
        <a>Profile</a>
        <ul class="vertical menu" data-dropdown-menu>
          <li><a href="profile.php">View Profile</a></li>
          <li><a>Twitch Data</a>
          <ul class="vertical menu" data-dropdown-menu>
            <li><a href="mods.php">View Mods</a></li>
            <li><a href="followers.php">View Followers</a></li>
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
    <h1><?php echo "$greeting, $twitchDisplayName!"; ?></h1>
    <h2>Your Profile</h2>
    <img src="<?php echo $twitch_profile_image_url; ?>" width="150px" height="150px" alt="Twitch Profile Image for <?php echo $username; ?>">
    <br><br>
    <p><strong>Your Username:</strong> <?php echo $username; ?></p>
    <p><strong>Display Name:</strong> <?php echo $twitchDisplayName; ?></p>
    <p><strong>You Joined:</strong> <span id="localSignupDate"></span></p>
    <p><strong>Your Last Login:</strong> <span id="localLastLogin"></span></p>
    <p><strong>Your Time Zone:</strong> <?php echo $user_timezone; ?></p>
    <p>Choose your time zone:</p>
    <form action="" method="post">
        <select name="timezone">
            <?php foreach ($timezones as $timezone) {
                $selected = ($timezone == $defaultTimeZone) ? 'selected' : '';
                echo "<option value='$timezone' $selected>$timezone</option>
                ";
            } ?>
        </select>
        <input type="submit" value="Submit" class="button">
    </form>
    <br>
    <a href="logout.php" type="button" class="logout-button">Logout</a>
</div>
<!-- Include the JavaScript files -->
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://cdn.yourlist.online/js/profile.js"></script>
<script src="https://cdn.yourlist.online/js/about.js" defer></script>
<script src="https://cdn.yourlist.online/js/obsbutton.js" defer></script>
<script src="https://cdn.yourlist.online/js/darkmode.js"></script>
<script src="https://dhbhdrzi4tiry.cloudfront.net/cdn/sites/foundation.js"></script>
<script>$(document).foundation();</script>
<script src="https://cdn.yourlist.online/js/timezone.js"></script>

<!-- JavaScript code to convert and display the dates -->
<script>
  // Function to convert UTC date to local date in the desired format
  function convertUTCToLocalFormatted(utcDateStr) {
    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric',
      hour12: true,
      timeZoneName: 'short'
    };
    const utcDate = new Date(utcDateStr + ' UTC');
    const localDate = new Date(utcDate.toLocaleString('en-US', { timeZone: 'Australia/Sydney' }));
    const dateTimeFormatter = new Intl.DateTimeFormat('en-US', options);
    return dateTimeFormatter.format(localDate);
  }

  // PHP variables holding the UTC date and time
  const signupDateUTC = "<?php echo $signup_date_utc; ?>";
  const lastLoginUTC = "<?php echo $last_login_utc; ?>";

  // Display the dates in the user's local time zone
  document.getElementById('localSignupDate').innerText = convertUTCToLocalFormatted(signupDateUTC);
  document.getElementById('localLastLogin').innerText = convertUTCToLocalFormatted(lastLoginUTC);
</script>
</body>
</html>