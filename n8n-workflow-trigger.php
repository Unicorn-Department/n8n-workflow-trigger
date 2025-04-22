<?php
/**
 * Plugin Name: N8N Workflow Trigger
 * Plugin URI: https://unicorndepartment.com/n8n-workflow-trigger
 * Description: A simple WordPress plugin that allows users to trigger n8n workflows using a shortcode.
 * Version: 0.0.2
 * Author: Igor van Oostveen
 * Author URI: https://unicorndepartment.com
 * License: GPL-2.0+
 * Text Domain: n8n-workflow-trigger
 */

// If this file is called directly, abort.
if (!defined("WPINC")) {
    die();
}

// Define plugin constants
define("N8N_TRIGGER_VERSION", "0.0.2");
define("N8N_TRIGGER_PLUGIN_DIR", plugin_dir_path(__FILE__));
define("N8N_TRIGGER_PLUGIN_URL", plugin_dir_url(__FILE__));

/**
 * Admin menu setup
 */
function n8n_trigger_add_admin_menu()
{
    add_options_page(
        "N8N Workflow Trigger Settings",
        "N8N Workflow Trigger",
        "manage_options",
        "n8n_workflow_trigger",
        "n8n_trigger_options_page"
    );
}
add_action("admin_menu", "n8n_trigger_add_admin_menu");

/**
 * Register settings
 */
function n8n_trigger_settings_init()
{
    register_setting("n8nTriggerPlugin", "n8n_trigger_settings");

    add_settings_section(
        "n8n_trigger_settings_section",
        __("N8N Workflow Settings", "n8n-workflow-trigger"),
        "n8n_trigger_settings_section_callback",
        "n8nTriggerPlugin"
    );

    add_settings_field(
        "n8n_base_url",
        __("N8N Base URL", "n8n-workflow-trigger"),
        "n8n_base_url_render",
        "n8nTriggerPlugin",
        "n8n_trigger_settings_section"
    );
}
add_action("admin_init", "n8n_trigger_settings_init");

/**
 * Settings section callback
 */
function n8n_trigger_settings_section_callback()
{
    echo __("Configure your n8n workflow triggers", "n8n-workflow-trigger");
}

/**
 * Settings field render functions
 */
function n8n_base_url_render()
{
    $options = get_option("n8n_trigger_settings"); ?>
    <input type='text' name='n8n_trigger_settings[n8n_base_url]' value='<?php echo isset(
        $options["n8n_base_url"]
    )
        ? esc_attr($options["n8n_base_url"])
        : ""; ?>' class="regular-text">
    <p class="description"><?php _e(
        "Example: https://your-n8n-instance.com",
        "n8n-workflow-trigger"
    ); ?></p>
    <?php
}

/**
 * Options page content
 */
function n8n_trigger_options_page()
{
    // Check if we're editing a workflow
    $edit_mode = false;
    $editing_workflow = null;
    $editing_workflow_id = "";

    if (
        isset($_GET["action"]) &&
        $_GET["action"] == "edit" &&
        isset($_GET["workflow_id"])
    ) {
        $edit_mode = true;
        $editing_workflow_id = sanitize_text_field($_GET["workflow_id"]);
        $workflows = get_option("n8n_workflow_triggers", []);

        if (isset($workflows[$editing_workflow_id])) {
            $editing_workflow = $workflows[$editing_workflow_id];
        } else {
            $edit_mode = false;
            add_settings_error(
                "n8n_workflow_trigger",
                "workflow-not-found",
                __("Workflow not found.", "n8n-workflow-trigger"),
                "error"
            );
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <?php settings_errors(); ?>

        <form action='options.php' method='post'>
            <?php
            settings_fields("n8nTriggerPlugin");
            do_settings_sections("n8nTriggerPlugin");
            submit_button();
            ?>
        </form>

        <hr>

        <h2><?php echo $edit_mode
            ? __("Edit Workflow Trigger", "n8n-workflow-trigger")
            : __("Add New Workflow Trigger", "n8n-workflow-trigger"); ?></h2>

        <form method="post" action="">
            <?php if ($edit_mode) {
                wp_nonce_field(
                    "n8n_edit_workflow_action",
                    "n8n_edit_workflow_nonce"
                );
                echo '<input type="hidden" name="workflow_id" value="' .
                    esc_attr($editing_workflow_id) .
                    '">';
            } else {
                wp_nonce_field(
                    "n8n_add_workflow_action",
                    "n8n_add_workflow_nonce"
                );
            } ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e(
                        "Workflow Name",
                        "n8n-workflow-trigger"
                    ); ?></th>
                    <td>
                        <input type="text" name="workflow_name" class="regular-text" required
                               value="<?php echo $edit_mode
                                   ? esc_attr($editing_workflow["name"])
                                   : ""; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e(
                        "Webhook ID/Path",
                        "n8n-workflow-trigger"
                    ); ?></th>
                    <td>
                        <input type="text" name="webhook_id" class="regular-text" required
                               value="<?php echo $edit_mode
                                   ? esc_attr($editing_workflow["webhook_id"])
                                   : ""; ?>">
                        <p class="description"><?php _e(
                            "The part of your webhook URL that follows your n8n base URL",
                            "n8n-workflow-trigger"
                        ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e(
                        "Button Text",
                        "n8n-workflow-trigger"
                    ); ?></th>
                    <td>
                        <input type="text" name="button_text" class="regular-text"
                               value="<?php echo $edit_mode
                                   ? esc_attr($editing_workflow["button_text"])
                                   : "Trigger Workflow"; ?>">
                    </td>
                </tr>
            </table>
            <?php if ($edit_mode) {
                submit_button(
                    __("Update Workflow Trigger", "n8n-workflow-trigger")
                );
                echo ' <a href="' .
                    esc_url(
                        admin_url(
                            "options-general.php?page=n8n_workflow_trigger"
                        )
                    ) .
                    '" class="button">' .
                    __("Cancel", "n8n-workflow-trigger") .
                    "</a>";
            } else {
                submit_button(
                    __("Add Workflow Trigger", "n8n-workflow-trigger")
                );
            } ?>
        </form>

        <?php if (!$edit_mode): ?>
        <hr>

        <h2><?php _e("Your Workflow Triggers", "n8n-workflow-trigger"); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e(
                        "Workflow Name",
                        "n8n-workflow-trigger"
                    ); ?></th>
                    <th><?php _e(
                        "Webhook Path",
                        "n8n-workflow-trigger"
                    ); ?></th>
                    <th><?php _e("Button Text", "n8n-workflow-trigger"); ?></th>
                    <th><?php _e("Shortcode", "n8n-workflow-trigger"); ?></th>
                    <th><?php _e("Actions", "n8n-workflow-trigger"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $workflows = get_option("n8n_workflow_triggers", []);
                if (!empty($workflows)) {
                    foreach ($workflows as $id => $workflow) { ?>
                        <tr>
                            <td><?php echo esc_html($workflow["name"]); ?></td>
                            <td><?php echo esc_html(
                                $workflow["webhook_id"]
                            ); ?></td>
                            <td><?php echo esc_html(
                                $workflow["button_text"]
                            ); ?></td>
                            <td>
                                <input type="text" readonly value="[n8n_trigger id=&quot;<?php echo esc_attr(
                                    $id
                                ); ?>&quot;]"
                                       class="code" style="width:100%;" onclick="this.select();">
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="button button-small test-workflow"
                                            data-workflow-id="<?php echo esc_attr(
                                                $id
                                            ); ?>">
                                        <?php _e(
                                            "Test",
                                            "n8n-workflow-trigger"
                                        ); ?>
                                    </button>
                                    <a href="<?php echo esc_url(
                                        add_query_arg(
                                            [
                                                "action" => "edit",
                                                "workflow_id" => $id,
                                            ],
                                            admin_url(
                                                "options-general.php?page=n8n_workflow_trigger"
                                            )
                                        )
                                    ); ?>"
                                       class="button button-small">
                                        <?php _e(
                                            "Edit",
                                            "n8n-workflow-trigger"
                                        ); ?>
                                    </a>
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field(
                                            "n8n_delete_workflow_action",
                                            "n8n_delete_workflow_nonce"
                                        ); ?>
                                        <input type="hidden" name="delete_workflow" value="<?php echo esc_attr(
                                            $id
                                        ); ?>">
                                        <button type="submit" class="button button-small">
                                            <?php _e(
                                                "Delete",
                                                "n8n-workflow-trigger"
                                            ); ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php }
                } else {
                     ?>
                    <tr>
                        <td colspan="5"><?php _e(
                            "No workflow triggers defined yet.",
                            "n8n-workflow-trigger"
                        ); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Test workflow modal -->
    <div id="test-workflow-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); overflow:auto;">
        <div style="background-color:#fff; margin:10% auto; padding:20px; border:1px solid #888; width:50%; max-width:500px; border-radius:5px;">
            <h3><?php _e("Testing Workflow", "n8n-workflow-trigger"); ?></h3>
            <p id="test-workflow-status"><?php _e(
                "Sending request...",
                "n8n-workflow-trigger"
            ); ?></p>
            <div id="test-workflow-response" style="background:#f5f5f5; padding:10px; max-height:200px; overflow:auto; display:none; margin-top:10px; font-family:monospace; font-size:12px;"></div>
            <div style="text-align:right; margin-top:15px;">
                <button type="button" class="button" id="close-test-modal"><?php _e(
                    "Close",
                    "n8n-workflow-trigger"
                ); ?></button>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Test workflow button
        $('.test-workflow').on('click', function() {
            const workflowId = $(this).data('workflow-id');
            $('#test-workflow-status').text('<?php _e(
                "Sending request...",
                "n8n-workflow-trigger"
            ); ?>');
            $('#test-workflow-response').hide();
            $('#test-workflow-modal').show();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'test_n8n_workflow',
                    workflow_id: workflowId,
                    nonce: '<?php echo wp_create_nonce(
                        "n8n_test_workflow_nonce"
                    ); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#test-workflow-status').text('<?php _e(
                            "Success! The workflow was triggered.",
                            "n8n-workflow-trigger"
                        ); ?>');
                        $('#test-workflow-response').text(JSON.stringify(response.data, null, 2)).show();
                    } else {
                        $('#test-workflow-status').text('<?php _e(
                            "Error triggering workflow",
                            "n8n-workflow-trigger"
                        ); ?>');
                        $('#test-workflow-response').text(JSON.stringify(response.data, null, 2)).show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#test-workflow-status').text('<?php _e(
                        "Error: AJAX request failed",
                        "n8n-workflow-trigger"
                    ); ?>');
                    $('#test-workflow-response').text(error).show();
                }
            });
        });

        // Close modal
        $('#close-test-modal').on('click', function() {
            $('#test-workflow-modal').hide();
        });

        // Close modal on outside click
        $(window).on('click', function(event) {
            if ($(event.target).is('#test-workflow-modal')) {
                $('#test-workflow-modal').hide();
            }
        });
    });
    </script>
    <?php
}

/**
 * Enqueue admin scripts
 */
function n8n_trigger_admin_scripts($hook)
{
    if ($hook != "settings_page_n8n_workflow_trigger") {
        return;
    }

    wp_enqueue_script("jquery");
}
add_action("admin_enqueue_scripts", "n8n_trigger_admin_scripts");

/**
 * Process form submissions for adding/editing/deleting workflows
 */
function n8n_process_admin_actions()
{
    // Handle adding a new workflow trigger
    if (
        isset($_POST["n8n_add_workflow_nonce"]) &&
        wp_verify_nonce(
            $_POST["n8n_add_workflow_nonce"],
            "n8n_add_workflow_action"
        )
    ) {
        if (isset($_POST["workflow_name"]) && isset($_POST["webhook_id"])) {
            $workflows = get_option("n8n_workflow_triggers", []);
            $id = "workflow_" . time();
            $workflows[$id] = [
                "name" => sanitize_text_field($_POST["workflow_name"]),
                "webhook_id" => sanitize_text_field($_POST["webhook_id"]),
                "button_text" => sanitize_text_field($_POST["button_text"]),
            ];
            update_option("n8n_workflow_triggers", $workflows);

            // Redirect to prevent form resubmission
            wp_redirect(
                add_query_arg(
                    "updated",
                    "true",
                    admin_url("options-general.php?page=n8n_workflow_trigger")
                )
            );
            exit();
        }
    }

    // Handle editing a workflow trigger
    if (
        isset($_POST["n8n_edit_workflow_nonce"]) &&
        wp_verify_nonce(
            $_POST["n8n_edit_workflow_nonce"],
            "n8n_edit_workflow_action"
        )
    ) {
        if (
            isset($_POST["workflow_id"]) &&
            isset($_POST["workflow_name"]) &&
            isset($_POST["webhook_id"])
        ) {
            $workflow_id = sanitize_text_field($_POST["workflow_id"]);
            $workflows = get_option("n8n_workflow_triggers", []);

            if (isset($workflows[$workflow_id])) {
                $workflows[$workflow_id] = [
                    "name" => sanitize_text_field($_POST["workflow_name"]),
                    "webhook_id" => sanitize_text_field($_POST["webhook_id"]),
                    "button_text" => sanitize_text_field($_POST["button_text"]),
                ];
                update_option("n8n_workflow_triggers", $workflows);

                // Redirect to prevent form resubmission
                wp_redirect(
                    add_query_arg(
                        "updated",
                        "true",
                        admin_url(
                            "options-general.php?page=n8n_workflow_trigger"
                        )
                    )
                );
                exit();
            }
        }
    }

    // Handle deleting a workflow trigger
    if (
        isset($_POST["n8n_delete_workflow_nonce"]) &&
        wp_verify_nonce(
            $_POST["n8n_delete_workflow_nonce"],
            "n8n_delete_workflow_action"
        )
    ) {
        if (isset($_POST["delete_workflow"])) {
            $workflow_id = sanitize_text_field($_POST["delete_workflow"]);
            $workflows = get_option("n8n_workflow_triggers", []);

            if (isset($workflows[$workflow_id])) {
                unset($workflows[$workflow_id]);
                update_option("n8n_workflow_triggers", $workflows);
            }

            // Redirect to prevent form resubmission
            wp_redirect(
                add_query_arg(
                    "updated",
                    "true",
                    admin_url("options-general.php?page=n8n_workflow_trigger")
                )
            );
            exit();
        }
    }
}
add_action("admin_init", "n8n_process_admin_actions");

/**
 * AJAX handler for testing workflows
 */
function n8n_test_workflow_ajax_handler()
{
    // Verify nonce
    if (
        !isset($_POST["nonce"]) ||
        !wp_verify_nonce($_POST["nonce"], "n8n_test_workflow_nonce")
    ) {
        wp_send_json_error("Invalid nonce");
        return;
    }

    // Check for workflow ID
    if (!isset($_POST["workflow_id"])) {
        wp_send_json_error("Missing workflow ID");
        return;
    }

    $workflow_id = sanitize_text_field($_POST["workflow_id"]);
    $workflows = get_option("n8n_workflow_triggers", []);

    // Check if workflow exists
    if (!isset($workflows[$workflow_id])) {
        wp_send_json_error("Workflow not found");
        return;
    }

    // Reuse the same logic from the regular trigger handler
    $workflow = $workflows[$workflow_id];
    $settings = get_option("n8n_trigger_settings", []);

    // Check for base URL
    if (empty($settings["n8n_base_url"])) {
        wp_send_json_error("N8N base URL not configured");
        return;
    }

    $base_url = trailingslashit($settings["n8n_base_url"]);
    $webhook_url = $base_url . ltrim($workflow["webhook_id"], "/");

    // Add current user info to the request
    $user_data = [];
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_data = [
            "user_id" => $current_user->ID,
            "user_login" => $current_user->user_login,
            "user_email" => $current_user->user_email,
            "display_name" => $current_user->display_name,
            "roles" => $current_user->roles,
        ];
    }

    // Make the HTTP request to n8n
    $response = wp_remote_post($webhook_url, [
        "method" => "POST",
        "timeout" => 45,
        "redirection" => 5,
        "httpversion" => "1.0",
        "blocking" => true,
        "headers" => [
            "Content-Type" => "application/json",
        ],
        "body" => json_encode([
            "source" => "wordpress_admin_test",
            "trigger_time" => current_time("mysql"),
            "site_url" => get_site_url(),
            "user" => $user_data,
        ]),
        "cookies" => [],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
        return;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if ($response_code >= 200 && $response_code < 300) {
        wp_send_json_success([
            "message" => "Workflow triggered successfully",
            "response_code" => $response_code,
            "response" => $response_body,
        ]);
    } else {
        wp_send_json_error([
            "message" => "Error triggering workflow",
            "response_code" => $response_code,
            "response" => $response_body,
        ]);
    }
}
add_action("wp_ajax_test_n8n_workflow", "n8n_test_workflow_ajax_handler");

/**
 * Enqueue scripts and styles
 */
function n8n_trigger_enqueue_scripts()
{
    wp_enqueue_script(
        "n8n-trigger-script",
        N8N_TRIGGER_PLUGIN_URL . "assets/js/n8n-trigger.js",
        ["jquery"],
        N8N_TRIGGER_VERSION,
        true
    );

    wp_localize_script("n8n-trigger-script", "n8nTrigger", [
        "ajaxurl" => admin_url("admin-ajax.php"),
        "nonce" => wp_create_nonce("n8n_trigger_nonce"),
    ]);

    wp_enqueue_style(
        "n8n-trigger-style",
        N8N_TRIGGER_PLUGIN_URL . "assets/css/n8n-trigger.css",
        [],
        N8N_TRIGGER_VERSION
    );
}
add_action("wp_enqueue_scripts", "n8n_trigger_enqueue_scripts");

/**
 * Create plugin directories and files upon activation
 */
function n8n_trigger_activate()
{
    // Create assets directories if they don't exist
    if (!file_exists(N8N_TRIGGER_PLUGIN_DIR . "assets/js")) {
        wp_mkdir_p(N8N_TRIGGER_PLUGIN_DIR . "assets/js");
    }
    if (!file_exists(N8N_TRIGGER_PLUGIN_DIR . "assets/css")) {
        wp_mkdir_p(N8N_TRIGGER_PLUGIN_DIR . "assets/css");
    }

    // Create JS file
    $js_content = <<<'EOT'
jQuery(document).ready(function($) {
    $('.n8n-trigger-button').on('click', function(e) {
        e.preventDefault();

        const button = $(this);
        const workflowId = button.data('workflow-id');
        const originalText = button.text();

        button.prop('disabled', true)
              .text('Processing...')
              .addClass('n8n-trigger-running');

        $.ajax({
            url: n8nTrigger.ajaxurl,
            type: 'POST',
            data: {
                action: 'trigger_n8n_workflow',
                workflow_id: workflowId,
                nonce: n8nTrigger.nonce
            },
            success: function(response) {
                if (response.success) {
                    button.text('Success!')
                          .removeClass('n8n-trigger-running')
                          .addClass('n8n-trigger-success');

                    setTimeout(function() {
                        button.text(originalText)
                              .removeClass('n8n-trigger-success')
                              .prop('disabled', false);
                    }, 2000);
                } else {
                    button.text('Error')
                          .removeClass('n8n-trigger-running')
                          .addClass('n8n-trigger-error');

                    setTimeout(function() {
                        button.text(originalText)
                              .removeClass('n8n-trigger-error')
                              .prop('disabled', false);
                    }, 2000);

                    console.error('N8N workflow error:', response.data);
                }
            },
            error: function(xhr, status, error) {
                button.text('Error')
                      .removeClass('n8n-trigger-running')
                      .addClass('n8n-trigger-error');

                setTimeout(function() {
                    button.text(originalText)
                          .removeClass('n8n-trigger-error')
                          .prop('disabled', false);
                }, 2000);

                console.error('AJAX error:', error);
            }
        });
    });
});
EOT;
    file_put_contents(
        N8N_TRIGGER_PLUGIN_DIR . "assets/js/n8n-trigger.js",
        $js_content
    );

    // Create CSS file
    $css_content = <<<'EOT'
.n8n-trigger-button {
    display: inline-block;
    padding: 10px 15px;
    background-color: #0073aa;
    color: #ffffff;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.n8n-trigger-button:hover {
    background-color: #005d8c;
    color: #ffffff;
}

.n8n-trigger-button:focus {
    outline: none;
    box-shadow: 0 0 0 1px #ffffff, 0 0 0 3px #0073aa;
}

.n8n-trigger-button.n8n-trigger-running {
    background-color: #f0ad4e;
    cursor: not-allowed;
}

.n8n-trigger-button.n8n-trigger-success {
    background-color: #5cb85c;
}

.n8n-trigger-button.n8n-trigger-error {
    background-color: #d9534f;
}
EOT;
    file_put_contents(
        N8N_TRIGGER_PLUGIN_DIR . "assets/css/n8n-trigger.css",
        $css_content
    );
}
register_activation_hook(__FILE__, "n8n_trigger_activate");

/**
 * AJAX handler for triggering workflows
 */
function n8n_trigger_ajax_handler()
{
    // Verify nonce
    if (
        !isset($_POST["nonce"]) ||
        !wp_verify_nonce($_POST["nonce"], "n8n_trigger_nonce")
    ) {
        wp_send_json_error("Invalid nonce");
        return;
    }

    // Check for workflow ID
    if (!isset($_POST["workflow_id"])) {
        wp_send_json_error("Missing workflow ID");
        return;
    }

    $workflow_id = sanitize_text_field($_POST["workflow_id"]);
    $workflows = get_option("n8n_workflow_triggers", []);

    // Check if workflow exists
    if (!isset($workflows[$workflow_id])) {
        wp_send_json_error("Workflow not found");
        return;
    }

    $workflow = $workflows[$workflow_id];
    $settings = get_option("n8n_trigger_settings", []);

    // Check for base URL
    if (empty($settings["n8n_base_url"])) {
        wp_send_json_error("N8N base URL not configured");
        return;
    }

    $base_url = trailingslashit($settings["n8n_base_url"]);
    $webhook_url = $base_url . ltrim($workflow["webhook_id"], "/");

    // Add current user info to the request
    $user_data = [];
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_data = [
            "user_id" => $current_user->ID,
            "user_login" => $current_user->user_login,
            "user_email" => $current_user->user_email,
            "display_name" => $current_user->display_name,
            "roles" => $current_user->roles,
        ];
    }

    // Make the HTTP request to n8n
    $response = wp_remote_post($webhook_url, [
        "method" => "POST",
        "timeout" => 45,
        "redirection" => 5,
        "httpversion" => "1.0",
        "blocking" => true,
        "headers" => [
            "Content-Type" => "application/json",
        ],
        "body" => json_encode([
            "source" => "wordpress",
            "trigger_time" => current_time("mysql"),
            "site_url" => get_site_url(),
            "user" => $user_data,
        ]),
        "cookies" => [],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
        return;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if ($response_code >= 200 && $response_code < 300) {
        wp_send_json_success([
            "message" => "Workflow triggered successfully",
            "response" => $response_body,
        ]);
    } else {
        wp_send_json_error([
            "message" => "Error triggering workflow",
            "response_code" => $response_code,
            "response" => $response_body,
        ]);
    }
}
add_action("wp_ajax_trigger_n8n_workflow", "n8n_trigger_ajax_handler");
add_action("wp_ajax_nopriv_trigger_n8n_workflow", "n8n_trigger_ajax_handler");

/**
 * Shortcode for displaying the trigger button
 */
function n8n_trigger_shortcode($atts)
{
    $atts = shortcode_atts(
        [
            "id" => "",
        ],
        $atts,
        "n8n_trigger"
    );

    if (empty($atts["id"])) {
        return '<p class="n8n-trigger-error">Error: Workflow ID is required.</p>';
    }

    $workflows = get_option("n8n_workflow_triggers", []);

    if (!isset($workflows[$atts["id"]])) {
        return '<p class="n8n-trigger-error">Error: Workflow not found.</p>';
    }

    $workflow = $workflows[$atts["id"]];
    $button_text = !empty($workflow["button_text"])
        ? $workflow["button_text"]
        : "Trigger Workflow";

    return '<button class="n8n-trigger-button" data-workflow-id="' .
        esc_attr($atts["id"]) .
        '">' .
        esc_html($button_text) .
        "</button>";
}

add_shortcode("n8n_trigger", "n8n_trigger_shortcode");
