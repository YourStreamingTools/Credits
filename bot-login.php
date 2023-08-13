<?php
// Set your Twitch application credentials
$clientID = ''; // CHANGE TO MAKE THIS WORK
$redirectURI = ''; // CHANGE TO MAKE THIS WORK

if (isset($_GET['access_token'])) {
    // Store the bot's OAuth token
    $botToken = $_GET['access_token'];
    
    // Get user information using the token
    $userInfoURL = 'https://api.twitch.tv/helix/users';
    $curl = curl_init($userInfoURL);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $botToken,
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
        $botUsername = $userInfo['data'][0]['login'];
        $botDisplayName = $userInfo['data'][0]['display_name'];
        $botAccessToken = $botToken;
        $botUserId = $userInfo['data'][0]['id'];
        $botProfileImage = $userInfo['data'][0]['profile_image_url'];
        
        // Insert bot information into the 'bot' table
        require_once "db_connect.php";
        $insertQuery = "INSERT INTO bot (username, display_name, access_token, user_id, profile_image) VALUES ('$botUsername', '$botDisplayName', '$botAccessToken', '$botUserId', '$botProfileImage')";
        $insertResult = mysqli_query($conn, $insertQuery);

        if ($insertResult) {
            // Redirect back to the bot page
            header('Location: bot_page.php');
            exit;
        } else {
            // Handle the case where the insertion failed
            echo "Failed to save bot information.";
            exit;
        }
    } else {
        // Failed to fetch bot information from Twitch
        echo "Failed to fetch bot information from Twitch.";
        exit;
    }
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