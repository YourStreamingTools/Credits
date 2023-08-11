import socket
import sqlite3
import re

# Twitch bot settings
BOT_USERNAME = "your_bot_username"
CHANNEL_NAME = "target_channel"
OAUTH_TOKEN = "your_oauth_token"  # Generate from Twitch Developer Dashboard

# Connect to IRC server
server = "irc.twitch.tv"
port = 6667
irc = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
irc.connect((server, port))

irc.send(f"PASS {OAUTH_TOKEN}\n".encode("utf-8"))
irc.send(f"NICK {BOT_USERNAME}\n".encode("utf-8"))
irc.send(f"JOIN #{CHANNEL_NAME}\n".encode("utf-8"))

# SQLite database settings
database_name = f"{CHANNEL_NAME.lower()}.db"
conn = sqlite3.connect(database_name)
cursor = conn.cursor()

# Chat interaction loop
while True:
    data = irc.recv(2048).decode("utf-8")

    if data.startswith("PING"):
        irc.send("PONG\n".encode("utf-8"))

    # Check for new follower notifications
    elif "PRIVMSG" not in data and "NOTICE" in data and f"#{CHANNEL_NAME}" in data:
        follower_match = re.search(r":(\w+)!\w+@\w+\.tmi\.twitch\.tv PRIVMSG #\w+ :(.+) has just followed!", data)
        if follower_match:
            follower_name = follower_match.group(1)
            cursor.execute("INSERT INTO followers (follower_name) VALUES (?)", (follower_name,))
            conn.commit()

    # Check for new subscriber notifications
    elif "USERNOTICE" in data and f"#{CHANNEL_NAME}" in data:
        subscriber_match = re.search(r"msg-id=subscriber [^ ]+ :(\w+)", data)
        if subscriber_match:
            subscriber_name = subscriber_match.group(1)
            cursor.execute("INSERT INTO subscribers (subscriber_name) VALUES (?)", (subscriber_name,))
            conn.commit()

    # Check for new cheer notifications
    elif "PRIVMSG" in data and "bits" in data:
        cheer_match = re.search(r":(\w+)!\w+@\w+\.tmi\.twitch\.tv PRIVMSG #\w+ :Cheers (\d+)", data)
        if cheer_match:
            username = cheer_match.group(1)
            cheer_amount = int(cheer_match.group(2))
            cursor.execute("INSERT INTO cheers (username, cheer_amount) VALUES (?, ?)", (username, cheer_amount))
            conn.commit()

    # Check for new raid notifications
    elif "PRIVMSG" in data and "Raiders" in data:
        raid_match = re.search(r":(\w+)!\w+@\w+\.tmi\.twitch\.tv PRIVMSG #\w+ :We're raiding with a party of (\d+)", data)
        if raid_match:
            raider_name = raid_match.group(1)
            viewers = int(raid_match.group(2))
            cursor.execute("INSERT INTO raids (raider_name, viewers) VALUES (?, ?)", (raider_name, viewers))
            conn.commit()

    time.sleep(1)