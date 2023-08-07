function sendToDiscord(section, buttonId) {
  var button = document.getElementById(buttonId);

  if (!webhookURL) {
    alert("Webhook URL is not available.");
    return;
  }

  switch (section) {
    case "followers":
      sendWebhook(webhookURL, "Recent Followers", formatFollowersData());
      break;
    case "subscribers":
      sendWebhook(webhookURL, "Recent Subscribers", formatSubscribersData());
      break;
    case "cheers":
      sendWebhook(webhookURL, "Recent Cheers", formatCheersData());
      break;
    case "raids":
      sendWebhook(webhookURL, "Recent Raids", formatRaidsData());
      break;
    default:
      alert("Invalid section.");
      break;
  }

  // Disable the button after clicking
  button.disabled = true;
  // Change the button text to indicate success
  button.innerHTML = "Sent to Discord &#10004;";
  // Change the button color to green
  button.style.backgroundColor = "green";
}


function sendWebhook(webhookURL, title, data) {
  // Create the payload for the webhook
  var payload = {
    username: "YourStreamingToolsBot",
    content: `**${title}**\n\n${data}`
  };

  // Send the payload to the webhook
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