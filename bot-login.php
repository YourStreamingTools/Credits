<?php
// Set your Twitch application credentials
$clientID = ''; // CHANGE TO MAKE THIS WORK
$redirectURI = ''; // CHANGE TO MAKE THIS WORK
$clientSecret = ''; // CHANGE TO MAKE THIS WORK

// Database credentials
require_once "db_connect.php";

// Start PHP session
session_start();

$access_token = $_SESSION['access_token'];

// Fetch the user's Twitch username and profile image URL
$userInfoURL = 'https://api.twitch.tv/helix/users';
$curl = curl_init($userInfoURL);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Client-ID: ' . $clientID
]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$userInfoResponse = curl_exec($curl);

if ($userInfoResponse === false) {
    // Handle cURL error
    echo 'cURL error: ' . curl_error($curl);
    exit;
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if ($httpCode !== 200) {
    // Handle non-successful HTTP response
    echo 'HTTP error: ' . $httpCode;
    exit;
}

curl_close($curl);

$userInfo = json_decode($userInfoResponse, true);

if (isset($userInfo['data']) && count($userInfo['data']) > 0) {
    $twitchUsername = $userInfo['data'][0]['login'];
    $twitchDisplayName = $userInfo['data'][0]['display_name'];
    $profileImageUrl = $userInfo['data'][0]['profile_image_url'];
    $twitchUserId = $userInfo['data'][0]['id'];
    
    // Check if the user already exists in the 'bot' table
    $checkQuery = "SELECT * FROM bot WHERE user_id = ?";
    $stmtCheck = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmtCheck, 's', $twitchUserId);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);

    if ($resultCheck->num_rows > 0) {
        // User already exists, update the information
        $updateQuery = "UPDATE bot SET username = ?, display_name = ?, access_token = ?, profile_image = ? WHERE user_id = ?";
        $stmtUpdate = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmtUpdate, 'sssss', $twitchUsername, $twitchDisplayName, $access_token, $profileImageUrl, $twitchUserId);
        mysqli_stmt_execute($stmtUpdate);
    } else {
        // User doesn't exist, insert the information
        $insertQuery = "INSERT INTO bot (username, display_name, access_token, user_id, profile_image) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmtInsert, 'sssss', $twitchUsername, $twitchDisplayName, $access_token, $twitchUserId, $profileImageUrl);
        mysqli_stmt_execute($stmtInsert);
    }

    // Redirect the user to the dashboard
    header('Location: bot.php');
    exit;
} else {
    // Failed to fetch user information from Twitch
    echo "Failed to fetch user information from Twitch.";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>YourStreamingTools - Twitch Login</title>
    <link rel="icon" href="https://cdn.yourstreaming.tools/img/logo.jpeg" sizes="32x32" />
    <link rel="icon" href="https://cdn.yourstreaming.tools/img/logo.jpeg" sizes="192x192" />
    <link rel="apple-touch-icon" href="https://cdn.yourstreaming.tools/img/logo.jpeg" />
    <meta name="msapplication-TileImage" content="https://cdn.yourstreaming.tools/img/logo.jpeg" />
</head>
<body>
    <p>Please wait while we redirect you to Twitch for authorization...</p>
</body>
</html>