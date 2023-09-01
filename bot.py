import sqlite3
import time
import argparse
import requests
from datetime import datetime
from twitchio.ext import commands
import os
import logging

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

# Logs
webroot = "/var/www/html"
logs_directory = "logs"
bot_logs = os.path.join(logs_directory, "bot")
chat_logs = os.path.join(logs_directory, "chat")
twitch_logs = os.path.join(logs_directory, "twitch")

for directory in [logs_directory, bot_logs]:
    directory_path = os.path.join(webroot, directory)
    if not os.path.exists(directory_path):
        os.makedirs(directory_path)

log_file = os.path.join(webroot, bot_logs, f"{CHANNEL_NAME}.txt")
logging.basicConfig(filename=log_file, level=logging.INFO,
                    format="%(asctime)s - %(levelname)s - %(message)s")

class Bot(commands.Bot):
    def __init__(self, cursor):
        super().__init__(token=OAUTH_TOKEN, prefix='$', initial_channels=[CHANNEL_NAME])
        self.cursor = cursor

    async def event_ready(self):
        logging.info(f'Logged in as | {self.nick}')
        logging.info(f'User id is | {self.user_id}')
        
        ctx = await self.get_context(self.get_channel(CHANNEL_NAME))
        await ctx.send(f'Connected!')

    @commands.command()
    async def start_bot(self, ctx: commands.Context):
        requests_made = 0  # Initialize requests_made
        start_time = time.time()  # Initialize start_time
        while True:
            current_time = int(time.time())  # Get current UNIX timestamp

            # Your API request to get follower information
            follower_api_url = f"https://api.twitch.tv/helix/users/follows?to_id={CHANNEL_ID}"
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
                subscription_months = subscriber.get('cumulative_months', 0)

                subscriber_timestamp = subscriber.get('time')
                if subscriber_timestamp:
                    subscriber_timestamp = datetime.strptime(subscriber_timestamp, '%Y-%m-%dT%H:%M:%S%z')
                    cursor.execute("INSERT INTO subscribers (subscriber_name, subscriber_tier, subscription_months, timestamp) VALUES (?, ?, ?, ?)", (subscriber_name, subscriber_tier, subscription_months, subscriber_timestamp.timestamp()))
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
            #raid_api_url = f"https://api.twitch.tv/helix/channels/raids?broadcaster_id={CHANNEL_ID}"
            #raid_headers = {
            #    'Client-ID': CLIENT_ID,
            #    'Authorization': f'Bearer {args.auth_token}'
            #}
            #raid_response = requests.get(raid_api_url, headers=raid_headers)
            #raid_data = raid_response.json()

            # Get the current date
            #current_date = datetime.now().date()

            # Extract and insert recent raid information into the database
            #for raid in raid_data.get('data', []):
            #    raider_name = raid['from_broadcaster_login']
            #    viewers = raid['viewers']
            #    raid_timestamp = datetime.strptime(raid['created_at'], '%Y-%m-%dT%H:%M:%SZ')
            #    raid_date = raid_timestamp.date()

            #    if raid_date == current_date:
            #        cursor.execute("INSERT INTO raids (raider_name, viewers, timestamp) VALUES (?, ?, ?)", (raider_name, viewers, current_time))
            #        conn.commit()

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

# Create a connection to the SQLite database
database_name = f"{CHANNEL_NAME.lower()}.db"
conn = sqlite3.connect(database_name)
cursor = conn.cursor()

# Create an instance of your Bot class and pass the cursor
bot_instance = Bot(cursor)

# Run the bot instance
bot_instance.run()