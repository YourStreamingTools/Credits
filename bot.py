import socket
import sqlite3
import re
import time
import argparse
import requests

# Parse command-line arguments
parser = argparse.ArgumentParser(description="Twitch Chat Bot")
parser.add_argument("-channel", dest="target_channel", required=True, help="Target Twitch channel name")
parser.add_argument("-channelid", dest="twitch_user_id", required=True, help="Twitch user ID")
args = parser.parse_args()

# Twitch bot settings
BOT_USERNAME = ""  # CHANGE TO MAKE THIS WORK
OAUTH_TOKEN = "" # CHANGE TO MAKE THIS WORK
CHANNEL_NAME = args.target_channel
YOUR_CHANNEL_ID = args.twitch_user_id

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

    current_time = int(time.time())  # Get current UNIX timestamp

    # Your API request to get follower information
    follower_api_url = f"https://api.twitch.tv/helix/users/follows?to_id={YOUR_CHANNEL_ID}"
    headers = {
        'Client-ID': 'your_client_id',
        'Authorization': 'Bearer your_bot_oauth_token'
    }
    follower_response = requests.get(follower_api_url, headers=headers)
    follower_data = follower_response.json()  # Parse JSON response
    for follower in follower_data.get("data", []):
        follower_name = follower["from_name"]
        cursor.execute("INSERT INTO followers (follower_name, timestamp) VALUES (?, ?)", (follower_name, current_time))
        conn.commit()

    # Your API request to get subscriber information
    subscriber_api_url = f"https://api.twitch.tv/helix/subscriptions?broadcaster_id={YOUR_CHANNEL_ID}"
    subscriber_response = requests.get(subscriber_api_url, headers=headers)
    subscriber_data = subscriber_response.json()  # Parse JSON response
    for subscriber in subscriber_data.get("data", []):
        subscriber_name = subscriber["user_name"]
        subscriber_tier = int(subscriber["tier"])
        subscription_months = int(subscriber["cumulative_months"])
        cursor.execute("INSERT INTO subscribers (subscriber_name, subscriber_tier, subscription_months, timestamp) VALUES (?, ?, ?, ?)", (subscriber_name, subscriber_tier, subscription_months, current_time))
        conn.commit()

    # Your API request to get cheer information
    cheer_api_url = f"https://api.twitch.tv/helix/bits/leaderboard?user_id={YOUR_CHANNEL_ID}"
    cheer_response = requests.get(cheer_api_url, headers=headers)
    cheer_data = cheer_response.json()  # Parse JSON response
    for cheer in cheer_data.get("data", []):
        username = cheer["user_name"]
        cheer_amount = int(cheer["score"])
        cursor.execute("INSERT INTO cheers (username, cheer_amount, timestamp) VALUES (?, ?, ?)", (username, cheer_amount, current_time))
        conn.commit()

    # Your API request to get raid information
    raid_api_url = f"https://api.twitch.tv/helix/channels/raids?broadcaster_id={YOUR_CHANNEL_ID}"
    raid_response = requests.get(raid_api_url, headers=headers)
    raid_data = raid_response.json()  # Parse JSON response
    for raid in raid_data.get("data", []):
        raider_name = raid["from_name"]
        viewers = int(raid["viewer_count"])
        cursor.execute("INSERT INTO raids (raider_name, viewers, timestamp) VALUES (?, ?, ?)", (raider_name, viewers, current_time))
        conn.commit()

time.sleep(1)