import socket
import sqlite3
import re
import time
import argparse
import requests

# Parse command-line arguments
parser = argparse.ArgumentParser(description="Twitch Chat Bot")
parser.add_argument("-channel", dest="target_channel", required=True, help="Target Twitch channel name")
parser.add_argument("-channelid", dest="channel_id", required=True, help="Twitch user ID")
parser.add_argument("-token", dest="bot_token", required=True, help="Bot Token for authentication")
args = parser.parse_args()

# Twitch bot settings
BOT_USERNAME = ""  # CHANGE TO MAKE THIS WORK
OAUTH_TOKEN = "" # CHANGE TO MAKE THIS WORK
CHANNEL_NAME = args.target_channel
CHANNEL_ID = args.channel_id

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

while True:
    data = irc.recv(2048).decode("utf-8")

    if data.startswith("PING"):
        irc.send("PONG\n".encode("utf-8"))

    current_time = int(time.time())  # Get current UNIX timestamp

    # Your API request to get follower information
    follower_api_url = f"https://api.twitch.tv/helix/users/follows?to_id={CHANNEL_ID}"
    follower_headers = {
        'Client-ID': '', # CHANGE TO MAKE THIS WORK
        'Authorization': f'Bearer {args.bot_token}'
    }
    follower_response = requests.get(follower_api_url, headers=follower_headers)
    follower_data = follower_response.json()

    # Extract and insert follower information into the database
    for follower in follower_data.get('data', []):
        follower_name = follower['from_name']
        cursor.execute("INSERT INTO followers (follower_name, timestamp) VALUES (?, ?)", (follower_name, current_time))

    # Your API request to get subscriber information
    subscriber_api_url = f"https://api.twitch.tv/helix/subscriptions?broadcaster_id={CHANNEL_ID}"
    subscriber_headers = {
        'Client-ID': '', # CHANGE TO MAKE THIS WORK
        'Authorization': f'Bearer {args.bot_token}'
    }
    subscriber_response = requests.get(subscriber_api_url, headers=subscriber_headers)
    subscriber_data = subscriber_response.json()

    # Extract and insert subscriber information into the database
    for subscriber in subscriber_data.get('data', []):
        subscriber_name = subscriber['user_name']
        subscriber_tier = subscriber['tier']
        subscription_months = subscriber['cumulative_months']
        cursor.execute("INSERT INTO subscribers (subscriber_name, subscriber_tier, subscription_months, timestamp) VALUES (?, ?, ?, ?)", (subscriber_name, subscriber_tier, subscription_months, current_time))

    # Your API request to get cheer information
    cheer_api_url = f"https://api.twitch.tv/helix/bits/leaderboard?user_id={CHANNEL_ID}"
    cheer_headers = {
        'Client-ID': '', # CHANGE TO MAKE THIS WORK
        'Authorization': f'Bearer {args.bot_token}'
    }
    cheer_response = requests.get(cheer_api_url, headers=cheer_headers)
    cheer_data = cheer_response.json()

    # Extract and insert cheer information into the database
    for cheer in cheer_data.get('data', []):
        username = cheer['user_name']
        cheer_amount = cheer['score']
        cursor.execute("INSERT INTO cheers (username, cheer_amount, timestamp) VALUES (?, ?, ?)", (username, cheer_amount, current_time))

    # Your API request to get raid information
    raid_api_url = f"https://api.twitch.tv/helix/channels/raids?broadcaster_id={CHANNEL_ID}"
    raid_headers = {
        'Client-ID': '', # CHANGE TO MAKE THIS WORK
        'Authorization': f'Bearer {args.bot_token}'
    }
    raid_response = requests.get(raid_api_url, headers=raid_headers)
    raid_data = raid_response.json()

    # Extract and insert raid information into the database
    for raid in raid_data.get('data', []):
        raider_name = raid['from_broadcaster_login']
        viewers = raid['viewers']
        cursor.execute("INSERT INTO raids (raider_name, viewers, timestamp) VALUES (?, ?, ?)", (raider_name, viewers, current_time))

time.sleep(1)