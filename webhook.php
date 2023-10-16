<?php
$clientSecret = "";

$headers = getallheaders();
$body = file_get_contents('php://input');


//Verify the request signature
if(!verify_request($headers, $body)){
    http_response_code(400);
    die();
}

$msg_type = strtolower($headers["Twitch-Eventsub-Message-Type"]);
$body = json_decode($body);

switch($msg_type){
    //If Challenge Request, response with the hmac
    case "webhook_callback_verification":
        challenge_response($headers, $body);
        break;
    case "notification":
        //We got a notification!
        process_notification($headers, $body);
        break;
    case "revocation":
        //Subscription has been revoked
        process_revocation($headers, $body);
        break;
    default:
        //Unsupported Message Type
        http_response_code(400);
        die();
}

//Process a Twitch Notification
function process_notification($headers, $body){
    global $config;
    switch($body->subscription->type){
        case "channel.raid":
            //RAID!
            $raider_id = $body->event->from_broadcaster_user_id;
            $raider_name = $body->event->from_broadcaster_user_name;
            $viewers = $body->event->viewers;
            // Do stuff with the raid data
            break;
        case "stream.online":
            //Stream is Live
            $user = $body->event->broadcaster_user_name;
            $time = $body->event->started_at;
            // Do stuff with raid online notification
            break;
        case "stream.offline":
            //Stream is Offline
            $user = $body->event->broadcaster_user_name;
            // Do stuff with raid offline notification
            break;
    }
    die();
}
    


function process_revocation($headers, $body){
    $id = $body->subscription->id;
    $status = $body->subscription->status;
    $type = $body->subscription->type;
    eventsub_log("Subscription Revoked ($id) - Type: $type - Reason: $status");
    switch($status){
        case "user_removed":
            //TOTO
            break;
        case "authorization_revoked":
            //TODO
            break;
        case "notification_failures_exceeded":
            //TODO
            break;
        case "version_removed":
            //TODO
            break;
        default:
            //Unknown Revocation Reason
            //TODO
    }
    die();
}

//Verify the webhook signature
function verify_request($headers, $body){
    global $clientSecret;
    $hmac_data = $headers["Twitch-Eventsub-Message-Id"];
    $hmac_data .= $headers["Twitch-Eventsub-Message-Timestamp"];
    $hmac_data .= $body;
    $hmac = "sha256=".hash_hmac("sha256", $hmac_data, $clientSecret);
    if($hmac == $headers["Twitch-Eventsub-Message-Signature"]){
        return TRUE;
    }
    return FALSE;
}

//Response to a verification challenge
function challenge_response($headers, $body){
    header("Content-Type: text/plain");
    $challenge = $body->challenge;
    eventsub_log("Webook Verification Sent - Challenge [$challenge]");
    die($challenge);
}
?>