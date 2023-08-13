<?php
// Set your Twitch application credentials
$clientID = ''; // CHANGE TO MAKE THIS WORK
$redirectURI = ''; // CHANGE TO MAKE THIS WORK

if (isset($_GET['access_token'])) {
    // Store the bot's OAuth token
    $botToken = $_GET['access_token'];
    // You can save the $botToken to your database or configuration file
    
    // Redirect back to the bot page
    header('Location: bot.php');
    exit;
} else {
    // Define the desired scope
    $scope = urlencode('bot:chat:edit bot:chat:read moderator:manage:announcements moderator:manage:banned_users moderator:manage:chat_messages moderator:manage:chat_settings user:manage:whispers whispers:read broadcaster:bits:read channel:manage:broadcast channel:edit:commercial channel:manage:predictions channel:manage:raids channel:manage:redemptions channel:moderate channel:read:hype_train channel:read:polls channel:read:predictions channel:read:redemptions channel:read:subscriptions moderation:read moderator:manage:shield_mode moderator:manage:shoutouts moderator:read:chatters moderator:read:followers');

    // Redirect the user to Twitch authorization page
    header('Location: https://id.twitch.tv/oauth2/authorize' .
        '?client_id=' . $clientID .
        '&redirect_uri=' . $redirectURI .
        '&response_type=token' .
        '&scope=' . $scope);
    exit;
}
?>