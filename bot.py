import socket
import sqlite3
import re
import time
import argparse
import requests

# Parse command-line arguments
parser = argparse.ArgumentParser(description="Twitch Chat Bot")
parser.add_argument("-channel", dest="target_channel", required=True, help="Target Twitch channel name")
parser.add_argument("-channelid", dest="CHANNEL_ID", required=True, help="Twitch user ID")
parser.add_argument("-token", dest="bot_token", required=True, help="Bot Token for authentication")
args = parser.parse_args()

# Twitch bot settings
BOT_USERNAME = ""  # CHANGE TO MAKE THIS WORK
OAUTH_TOKEN = "" # CHANGE TO MAKE THIS WORK
CHANNEL_NAME = args.target_channel
CHANNEL_ID = args.twitch_user_id

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
    follower_api_url = f"https://api.twitch.tv/helix/users/follows?to_id={CHANNEL_ID}"
    headers = {
        'Client-ID': '',  # CHANGE TO MAKE THIS WORK
        'Authorization': 'Bearer {args.bot_token}'
    }
    follower_response = requests.get(follower_api_url, headers=headers)
    cursor.execute("INSERT INTO followers (follower_name, timestamp) VALUES (?, ?)", (follower_name, current_time))

    # Your API request to get subscriber information
    subscriber_api_url = f"https://api.twitch.tv/helix/subscriptions?broadcaster_id={CHANNEL_ID}"
    headers = {
        'Client-ID': '',  # CHANGE TO MAKE THIS WORK
        'Authorization': 'Bearer {args.bot_token}'
    }
    subscriber_response = requests.get(subscriber_api_url, headers=headers)
    cursor.execute("INSERT INTO subscribers (subscriber_name, subscriber_tier, subscription_months, timestamp) VALUES (?, ?, ?, ?)", (subscriber_name, subscriber_tier, subscription_months, current_time))

    # Your API request to get cheer information
    cheer_api_url = f"https://api.twitch.tv/helix/bits/leaderboard?user_id={CHANNEL_ID}"
    headers = {
        'Client-ID': '',  # CHANGE TO MAKE THIS WORK
        'Authorization': 'Bearer {args.bot_token}'
    }
    cheer_response = requests.get(cheer_api_url, headers=headers)
    cursor.execute("INSERT INTO cheers (username, cheer_amount, timestamp) VALUES (?, ?, ?)", (username, cheer_amount, current_time))

    # Your API request to get raid information
    raid_api_url = f"https://api.twitch.tv/helix/channels/raids?broadcaster_id={CHANNEL_ID}"
    headers = {
        'Client-ID': '',  # CHANGE TO MAKE THIS WORK
        'Authorization': 'Bearer {args.bot_token}'
    }
    raid_response = requests.get(raid_api_url, headers=headers)
    cursor.execute("INSERT INTO raids (raider_name, viewers, timestamp) VALUES (?, ?, ?)", (raider_name, viewers, current_time))

time.sleep(1)