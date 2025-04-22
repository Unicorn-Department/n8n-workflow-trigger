This plugin provides a simple solution for your WordPress website visitors to trigger n8n workflows.

## Key Features:

* Configure your n8n instance base URL
* Create, manage, and delete webhook workflow triggers
* Embed trigger buttons anywhere using [n8n_trigger id="workflow_id"] shortcode
* Handles workflow triggering without page refreshes
* Button states show processing, success, and error conditions

## How to Use:

### Install and activate the plugin
Go to Settings > N8N Workflow Trigger
Enter your n8n instance URL (e.g., https://your-n8n-instance.com)
Add workflow triggers by providing:

- Workflow name (for your reference)
- Webhook ID/path (the part after your n8n base URL)
- Custom button text (optional)

⚠️ **Make sure you set your n8n Webhook HTTP method to POST.**


### Use the generated shortcode on any post or page
When users click the button, it sends a POST request to your n8n webhook with site information and current user data (not available yet).


## To do:

* Add server authentication
