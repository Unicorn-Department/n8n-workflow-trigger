This plugin provides a complete solution for triggering n8n workflows from WordPress. Here's what it includes:
Key Features:

* Admin Settings Page - Configure your n8n instance base URL
* Workflow Management - Create, manage, and delete workflow triggers
* Shortcode System - Embed trigger buttons anywhere using [n8n_trigger id="workflow_id"]
* AJAX Processing - Handles workflow triggering without page refreshes
* Visual Feedback - Button states show processing, success, and error conditions
* User Data - Automatically sends WordPress user data with the trigger

## How to Use:

**Install and activate the plugin**
Go to Settings > N8N Workflow Trigger
Enter your n8n instance URL (e.g., https://your-n8n-instance.com)
Add workflow triggers by providing:

- Workflow name (for your reference)
- Webhook ID/path (the part after your n8n base URL)
- Custom button text (optional)


Use the generated shortcode on any post or page

When users click the button, it sends a POST request to your n8n webhook with site information and current user data.
