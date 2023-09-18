<?php
if (isset($_POST['runBot'])) {
// Check if the bot is already running
if (isBotRunning($username)) {
  $statusOutput = shell_exec("python status.py -channel $username");
  $pid = intval(preg_replace('/\D/', '', $statusOutput));
  $statusOutput = "Bot is already running. Process ID: $pid";
} else {
  // Execute the Python script with the channel name as an argument
  $output = shell_exec("python bot.py -channel $username -channelid $twitchUserId -token $authToken > /dev/null 2>&1 &");

  // Sleep for a few seconds to allow the process to start
  sleep(3);

  // Fetch the bot's PID from status.py
  $statusOutput = shell_exec("python status.py -channel $username");
  $pid = intval(trim($statusOutput));
  }
}

if (isset($_POST['botStatus'])) {
  $statusOutput = shell_exec("python status.py -channel $username");
  $pid = intval(preg_replace('/\D/', '', $statusOutput));
}

if (isset($_POST['killBot'])) {
  $statusOutput = shell_exec("python status.py -channel $username");
  $pid = intval(preg_replace('/\D/', '', $statusOutput));

  // Kill the bot's process
  $killprocess = shell_exec("kill $pid > /dev/null 2>&1 &");
    
  // Sleep for a few seconds to allow the process to start
  sleep(3);

  // Remove the bot's PID from the session
  $pid = '';
    
  $statusOutput = "Bot Status: Stopped.";
}

if (isset($_POST['restartBot'])) {
  // Check if the bot is running and stop it
  if (isBotRunning($username)) {
    $statusOutput = shell_exec("python status.py -channel $username");
    $pid = intval(preg_replace('/\D/', '', $statusOutput));

    // Kill the bot's process
    $killprocess = shell_exec("kill $pid > /dev/null 2>&1 &");
    
    // Sleep for a few seconds to allow the process to start
    sleep(3);

    // Start the bot
    $output = shell_exec("python bot.py -channel $username -channelid $twitchUserId -token $authToken > /dev/null 2>&1 &");
  
    // Sleep for a few seconds to allow the process to start
    sleep(3);

    // Fetch the bot's PID from status.py
    $statusOutput = shell_exec("python status.py -channel $username");
    $pid = intval(preg_replace('/\D/', '', $statusOutput));
    $statusOutput = "Bot restarted successfully. Process ID: $pid";
  } else {
    $statusOutput = "Can't restart bot, bot is not running.";
  }
}

function isBotRunning($username) {
  $statusOutput = shell_exec("python status.py -channel $username");
  $pid = intval(preg_replace('/\D/', '', $statusOutput));
  return ($pid > 0);
}
?>