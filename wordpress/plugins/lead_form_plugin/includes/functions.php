<?php
if (!defined('ABSPATH')) {
    exit;
}

function lead_form_get_template($template_name) {
    $template_path = LEAD_FORM_PLUGIN_PATH . 'templates/' . $template_name;
    
    if (file_exists($template_path)) {
        return $template_path;
    }
    
    return false;
}

function lead_form_load_template($template_name, $data = array()) {
    $template_path = lead_form_get_template($template_name);
    
    if ($template_path) {
        extract($data);
        include $template_path;
    }
}

function lead_form_log_error($message) {
    if (WP_DEBUG === true) {
        error_log('[Lead Form Plugin] ' . $message);
    }
}

function lead_form_is_valid_email($email) {
    return is_email($email);
}

function lead_form_sanitize_phone($phone) {
    return preg_replace('/[^0-9+\-\s\(\)]/', '', $phone);
}

function lead_form_get_statuses() {
    return array(
        'new' => 'Новый',
        'processed' => 'Обработан',
        'qualified' => 'Квалифицирован',
        'rejected' => 'Отклонен'
    );
}

function lead_form_get_status_label($status) {
    $statuses = lead_form_get_statuses();
    return $statuses[$status] ?? $status;
}
?>
