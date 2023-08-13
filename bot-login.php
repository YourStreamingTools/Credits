<?php
// Set your Twitch application credentials
$clientID = ''; // CHANGE TO MAKE THIS WORK
$redirectURI = ''; // CHANGE TO MAKE THIS WORK
$clientSecret = ''; // CHANGE TO MAKE THIS WORK

if (isset($_GET['access_token'])) {
    // Store the bot's OAuth token
    $botToken = $_GET['access_token'];
    // You can save the $botToken to your database or configuration file
    
    // Redirect back to the bot page
    header('Location: bot.php');
    exit;
} else {
    // Redirect the user to Twitch authorization page
    header('Location: https://id.twitch.tv/oauth2/authorize' .
        '?client_id=' . $clientID .
        '&redirect_uri=' . $redirectURI .
        '&response_type=token' .
        '&scope=chat:read chat:edit');
    exit;
}
?>