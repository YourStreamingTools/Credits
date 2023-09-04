<?php
function createTables($database_name) {
    $conn = new SQLite3($database_name);

    // Create tables for different interactions (followers, subscribers, cheers, raids)
    $createFollowersTable = "CREATE TABLE IF NOT EXISTS followers (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      follower_name TEXT,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $createSubscribersTable = "CREATE TABLE IF NOT EXISTS subscribers (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      subscriber_name TEXT,
      subscriber_tier INTEGER,
      subscription_months INTEGER,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $createCheersTable = "CREATE TABLE IF NOT EXISTS cheers (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT,
      cheer_amount INTEGER,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $createRaidsTable = "CREATE TABLE IF NOT EXISTS raids (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      raider_name TEXT,
      viewers INTEGER,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    // Execute the table creation queries
    $conn->exec($createFollowersTable);
    $conn->exec($createSubscribersTable);
    $conn->exec($createCheersTable);
    $conn->exec($createRaidsTable);
    $conn->close();
};

function resetDatabase($database_name) {
    if (file_exists($database_name)) {
        unlink($database_name);
        sleep(3);
    }

    createTables($database_name);
};
?>