<?php
/**
 * Plugin Name: Lead Form Plugin
 * Description: Плагин для сбора и управления лидами с интеграцией с n8n и EspoCRM
 * Version: 1.0
 * Author: Roman Agafonov
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LEAD_FORM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LEAD_FORM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LEAD_FORM_PLUGIN_VERSION', '1.0');

require_once LEAD_FORM_PLUGIN_PATH . 'includes/functions.php';
require_once LEAD_FORM_PLUGIN_PATH . 'includes/class-lead-form.php';
require_once LEAD_FORM_PLUGIN_PATH . 'includes/class-lead-form-admin.php';
require_once LEAD_FORM_PLUGIN_PATH . 'includes/class-lead-form-ajax.php';

function lead_form_plugin_init() {
    $lead_form_plugin = new Lead_Form_Plugin();
    $lead_form_admin = new Lead_Form_Admin();
    $lead_form_ajax = new Lead_Form_AJAX();
}
add_action('plugins_loaded', 'lead_form_plugin_init');

register_activation_hook(__FILE__, 'lead_form_plugin_activate');
function lead_form_plugin_activate() {
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'lead_form_plugin_deactivate');
function lead_form_plugin_deactivate() {
    flush_rewrite_rules();
}
?>