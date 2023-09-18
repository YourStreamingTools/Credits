<?php
function createTables($database) {
    $conn = new SQLite3($database);

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

function resetDatabase($database) {
  if (file_exists($database)) {
    try {
      $db = new SQLite3($database);
      // Execute SQL queries to delete all data from tables and reset auto-increment IDs
      $tables = $db->query("SELECT * FROM sqlite_master WHERE type='table'");
      while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {
          $table_name = $table['name'];
          $db->exec("DELETE FROM $table_name");
          $db->exec("DELETE FROM sqlite_sequence WHERE name='$table_name'");
      }
      // Close the database connection
      $db->close();
      
      // Recreate tables if needed
      createTables($database);
       
    } catch (Exception $e) {
          // Handle exceptions, e.g., log errors
          echo "Error: " . $e->getMessage();
    }
  }
}
?>