<?php
$timezones_query = $conn->query("SELECT * FROM timezones");
$timezones = [];

while ($row = $timezones_query->fetch_assoc()) {
    $timezones[] = $row['name'];
}

// Default time zone
$defaultTimeZone = 'Etc/UTC';

// Fetch the user's time zone from the database
$user_timezone = $defaultTimeZone; // Default to UTC

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedTimeZone = $_POST["timezone"];
    $user_id = $_SESSION['user_id']; // Use your user identifier here

    // Update the user's time zone in the database
    $stmt = $conn->prepare("UPDATE users SET timezone = ? WHERE id = ?");
    $stmt->bind_param("si", $selectedTimeZone, $user_id);
    $stmt->execute();

    // Update the user's time zone in the current session
    $user_timezone = $selectedTimeZone;
}

// Set the user's time zone for date and time functions
date_default_timezone_set($user_timezone);

// Determine the greeting based on the user's local time
$currentHour = date('G');
$greeting = '';

if ($currentHour < 12) {
    $greeting = "Good morning";
} else {
    $greeting = "Good afternoon";
}
?>