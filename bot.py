import socket
import sqlite3
import re
import time
import argparse
import requests
from datetime import datetime

# Parse command-line arguments
parser = argparse.ArgumentParser(description="Twitch Chat Bot")
parser.add_argument("-channel", dest="target_channel", required=True, help="Target Twitch channel name")
parser.add_argument("-channelid", dest="channel_id", required=True, help="Twitch user ID")
parser.add_argument("-token", dest="auth_token", required=True, help="Auth Token for authentication")
args = parser.parse_args()

# Twitch bot settings
BOT_USERNAME = ""  # CHANGE TO MAKE THIS WORK
OAUTH_TOKEN = "" # CHANGE TO MAKE THIS WORK
CLIENT_ID = "" # CHANGE TO MAKE THIS WORK
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

# Initialize request count and timestamp
requests_made = 0
start_time = time.time()

while True:
    data = irc.recv(2048).decode("utf-8")

    if data.startswith("PING"):
        irc.send("PONG\n".encode("utf-8"))

    current_time = int(time.time())  # Get current UNIX timestamp

    # Your API request to get follower information
    follower_api_url = f"https://api.twitch.tv/helix/users/follows?to_id={CHANNEL_ID}&first=10"
    follower_headers = {
        'Client-ID': CLIENT_ID,
        'Authorization': f'Bearer {args.auth_token}'
    }
    follower_response = requests.get(follower_api_url, headers=follower_headers)
    follower_data = follower_response.json()

    # Get the current date
    current_date = datetime.now().date()

    # Extract and insert recent follower information into the database
    for follower in follower_data.get('data', []):
        follower_name = follower['from_name']
        followed_at = datetime.strptime(follower['followed_at'], '%Y-%m-%dT%H:%M:%SZ').date()

        if followed_at == current_date:
            cursor.execute("INSERT INTO followers (follower_name, timestamp) VALUES (?, ?)", (follower_name, current_time))
            conn.commit()

    # Your API request to get subscriber information
    subscriber_api_url = f"https://api.twitch.tv/helix/subscriptions?broadcaster_id={CHANNEL_ID}"
    subscriber_headers = {
        'Client-ID': CLIENT_ID,
        'Authorization': f'Bearer {args.auth_token}'
    }
    subscriber_response = requests.get(subscriber_api_url, headers=subscriber_headers)
    subscriber_data = subscriber_response.json()

    # Get the current date
    current_date = datetime.now().date()

    # Extract and insert recent subscriber information into the database
    for subscriber in subscriber_data.get('data', []):
        subscriber_name = subscriber['user_name']
        subscriber_tier = subscriber['tier']
        subscription_months = subscriber['cumulative_months']
        subscriber_timestamp = datetime.strptime(subscriber['created_at'], '%Y-%m-%dT%H:%M:%SZ')
        subscriber_date = subscriber_timestamp.date()

        if subscriber_date == current_date:
            cursor.execute("INSERT INTO subscribers (subscriber_name, subscriber_tier, subscription_months, timestamp) VALUES (?, ?, ?, ?)", (subscriber_name, subscriber_tier, subscription_months, current_time))
            conn.commit()

    # Your API request to get cheer information
    cheer_api_url = f"https://api.twitch.tv/helix/bits/leaderboard?user_id={CHANNEL_ID}"
    cheer_headers = {
        'Client-ID': CLIENT_ID,
        'Authorization': f'Bearer {args.auth_token}'
    }
    cheer_response = requests.get(cheer_api_url, headers=cheer_headers)
    cheer_data = cheer_response.json()

    # Get the current date
    current_date = datetime.now().date()

    # Extract and insert recent cheer information into the database
    for cheer in cheer_data.get('data', []):
        username = cheer['user_name']
        cheer_amount = cheer['score']
        cheer_timestamp = datetime.strptime(cheer['created_at'], '%Y-%m-%dT%H:%M:%SZ')
        cheer_date = cheer_timestamp.date()

        if cheer_date == current_date:
            cursor.execute("INSERT INTO cheers (username, cheer_amount, timestamp) VALUES (?, ?, ?)", (username, cheer_amount, current_time))
            conn.commit()

    # Your API request to get raid information
    raid_api_url = f"https://api.twitch.tv/helix/channels/raids?broadcaster_id={CHANNEL_ID}"
    raid_headers = {
        'Client-ID': CLIENT_ID,
        'Authorization': f'Bearer {args.auth_token}'
    }
    raid_response = requests.get(raid_api_url, headers=raid_headers)
    raid_data = raid_response.json()

    # Get the current date
    current_date = datetime.now().date()

    # Extract and insert recent raid information into the database
    for raid in raid_data.get('data', []):
        raider_name = raid['from_broadcaster_login']
        viewers = raid['viewers']
        raid_timestamp = datetime.strptime(raid['created_at'], '%Y-%m-%dT%H:%M:%SZ')
        raid_date = raid_timestamp.date()

        if raid_date == current_date:
            cursor.execute("INSERT INTO raids (raider_name, viewers, timestamp) VALUES (?, ?, ?)", (raider_name, viewers, current_time))
            conn.commit()

    # Update request count
        requests_made += 4

        # Check if the minute has passed since the start
        elapsed_time = time.time() - start_time
        if elapsed_time < 60:
            if requests_made >= 30:
                # Pause until the next minute begins
                time.sleep(60 - elapsed_time)
                # Reset the request count and timestamp
                requests_made = 0
                start_time = time.time()

        else:
            # Reset the request count and timestamp at the start of a new minute
            requests_made = 0
            start_time = time.time()

        # Pause for 60 seconds before the next iteration
        time.sleep(60)