import sqlite3
import time
import argparse
import requests
from datetime import datetime
import twitchio
from twitchio.ext import commands, eventsub
import os
import logging
import signal

# Parse command-line arguments
parser = argparse.ArgumentParser(description="YourStreamingTools Chat Bot")
parser.add_argument("-channel", dest="target_channel", required=True, help="Target Twitch channel name")
parser.add_argument("-channelid", dest="channel_id", required=True, help="Twitch user ID")
parser.add_argument("-token", dest="auth_token", required=True, help="Auth Token for authentication")
args = parser.parse_args()

# Define a flag to control the main loop
running = True

# Define a signal handler function to handle Ctrl+C (SIGINT)
def signal_handler(sig, frame):
    global running
    print("Exiting gracefully...")
    running = False

# Set up the signal handler to listen for Ctrl+C
signal.signal(signal.SIGINT, signal_handler)

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
        super().__init__(token=OAUTH_TOKEN, prefix='!', initial_channels=[CHANNEL_NAME])
        self.cursor = cursor

    async def event_ready(self):
        logging.info(f'Logged in as | {self.nick}')
        logging.info(f'User id is | {self.user_id}')

    @commands.command()
    async def start_bot(self, ctx: commands.Context):
        requests_made = 0  # Initialize requests_made
        start_time = time.time()  # Initialize start_time
        while True:
            current_time = int(time.time())  # Get current UNIX timestamp
            twitch_log_file = os.path.join(webroot, twitch_logs, f"{CHANNEL_NAME}.txt")
            logging.basicConfig(filename=twitch_log_file, level=logging.INFO, format="%(asctime)s - %(levelname)s - %(message)s")

# Create a connection to the SQLite database
database_folder = "database"
database_file = os.path.join(database_folder, f"{CHANNEL_NAME.lower()}.db")
conn = sqlite3.connect(database_file)
cursor = conn.cursor()

# Create an instance of your Bot class and pass the cursor
bot_instance = Bot(cursor)

# Run the bot instance
bot_instance.run()