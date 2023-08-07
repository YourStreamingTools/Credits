function formatFollowersData(followers) {
  let formattedData = "Recent Followers:\n";
  followers.forEach((follower, index) => {
    formattedData += `${index + 1}. ${follower.follower_name} - ${follower.timestamp}\n`;
  });
  return formattedData;
}

function sendToDiscord(section, buttonId) {
  var button = document.getElementById(buttonId);
  console.log("Button clicked!");

  if (!webhookURL) {
    alert("Webhook URL is not available.");
    return;
  }

  switch (section) {
    case "followers":
      sendWebhook(webhookURL, "Recent Followers", formatFollowersData(followerData));
      break;
    case "subscribers":
      sendWebhook(webhookURL, "Recent Subscribers", formatSubscribersData(subscriberData));
      break;
    case "cheers":
      sendWebhook(webhookURL, "Recent Cheers", formatCheersData(cheerData));
      break;
    case "raids":
      sendWebhook(webhookURL, "Recent Raids", formatRaidsData(raidData));
      break;
    default:
      alert("Invalid section.");
      break;
  }

  button.innerHTML = "Sent to Discord &#10004;";
  button.style.backgroundColor = "green";
}

function sendWebhook(webhookURL, title, data) {
  var payload = {
    username: "YourStreamingToolsBot",
    content: `**${title}**\n\n${data}`
  };

  fetch(webhookURL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  .then(response => {
    if (response.ok) {
      alert("Message sent to Discord!");
    } else {
      console.error("Error sending message:", response.statusText);
      alert("An error occurred while sending the message to Discord.");
    }
  })
  .catch(error => {
    console.error("Error sending message:", error);
    alert("An error occurred while sending the message to Discord.");
  });
}