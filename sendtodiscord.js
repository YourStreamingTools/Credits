function sendToDiscord(section) {
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
}  