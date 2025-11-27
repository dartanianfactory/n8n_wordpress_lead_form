<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Form_AJAX {
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_ajax_submit_lead_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_submit_lead_form', array($this, 'handle_form_submission'));

        add_action('wp_ajax_test_webhook', array($this, 'test_webhook'));
        add_action('wp_ajax_test_crm', array($this, 'test_crm'));
    }
    
    public function handle_form_submission() {
        if (ob_get_length()) {
            ob_clean();
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'lead_form_nonce')) {
            wp_send_json_error(array('form' => 'Security check failed'));
            wp_die();
        }
        
        $errors = array();
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = lead_form_sanitize_phone($_POST['phone'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if (empty($name)) {
            $errors['name'] = 'Пожалуйста, введите ваше имя';
        }
        
        if (empty($email) || !lead_form_is_valid_email($email)) {
            $errors['email'] = 'Пожалуйста, введите корректный email';
        }
        
        if (empty($phone)) {
            $errors['phone'] = 'Пожалуйста, введите ваш телефон';
        }

        if (!empty($errors)) {
            wp_send_json_error($errors);
            wp_die();
        }
        
        try {
            $post_id = wp_insert_post(array(
                'post_title' => 'Заявка от ' . $name,
                'post_content' => $message,
                'post_type' => 'applications',
                'post_status' => 'publish'
            ), true);
            
            if (is_wp_error($post_id)) {
                throw new Exception($post_id->get_error_message());
            }
            
            $this->save_lead_meta($post_id, $name, $email, $phone, $message);
            
            // Пытаемся отправить в вебхук, но не блокируем пользователя
            $webhook_sent = $this->send_to_webhook($post_id, $name, $email, $phone, $message);
            
            if (is_wp_error($webhook_sent)) {
                lead_form_log_error('Webhook failed: ' . $webhook_sent->get_error_message());
            }
            
            wp_send_json_success('Заявка успешно отправлена!');
            wp_die();
            
        } catch (Exception $e) {
            lead_form_log_error('Form submission error: ' . $e->getMessage());
            wp_send_json_error(array('form' => 'Произошла ошибка при отправке формы'));
            wp_die();
        }
    }
    
    private function save_lead_meta($post_id, $name, $email, $phone, $message) {
        update_post_meta($post_id, '_lead_email', $email);
        update_post_meta($post_id, '_lead_phone', $phone);
        update_post_meta($post_id, '_lead_status', 'new');
        update_post_meta($post_id, '_lead_source', 'website');
        update_post_meta($post_id, '_lead_created', current_time('mysql'));
    }
    
    private function send_to_webhook($post_id, $name, $email, $phone, $message) {
        $webhook_url = get_option('lead_form_webhook_url', 'http://n8n:5678/webhook/wordpress-lead');
        
        if (empty($webhook_url)) {
            return new WP_Error('webhook_url_missing', 'Webhook URL not configured');
        }
        
        $data = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'wordpress_id' => $post_id,
            'source' => 'website',
            'timestamp' => current_time('mysql')
        );
        
        $response = wp_remote_post($webhook_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
            'timeout' => 5,
            'blocking' => true
        ));
        
        if (is_wp_error($response)) {
            update_post_meta($post_id, '_lead_webhook_status', 'failed');
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code >= 200 && $response_code < 300) {
            update_post_meta($post_id, '_lead_webhook_status', 'sent');
            return true;
        } else {
            update_post_meta($post_id, '_lead_webhook_status', 'failed');
            return new WP_Error('webhook_error', 'Webhook returned error code: ' . $response_code);
        }
    }
    
    public function test_webhook() {
        if (ob_get_length()) {
            ob_clean();
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            wp_die();
        }

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'lead_form_admin_nonce')) {
            wp_send_json_error('Security check failed');
            wp_die();
        }
        
        $webhook_url = get_option('lead_form_webhook_url', 'http://n8n:5678/webhook/wordpress-lead');
        if (empty($webhook_url)) {
            wp_send_json_error('Webhook URL not configured. Current value: ' . $webhook_url);
            wp_die();
        }
        
        $test_data = array(
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+79990001122',
            'message' => 'Test message from admin panel',
            'wordpress_id' => 999,
            'source' => 'admin_test',
            'timestamp' => current_time('mysql')
        );
        
        lead_form_log_error('Testing webhook to: ' . $webhook_url);
        lead_form_log_error('Test data: ' . print_r($test_data, true));
        
        $response = wp_remote_post($webhook_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($test_data),
            'timeout' => 10,
            'blocking' => true
        ));
        
        if (is_wp_error($response)) {
            $error_message = 'Webhook connection error: ' . $response->get_error_message();
            lead_form_log_error($error_message);
            wp_send_json_error($error_message);
            wp_die();
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        lead_form_log_error('Webhook response - Code: ' . $response_code . ' Body: ' . $response_body);
        
        if ($response_code >= 200 && $response_code < 300) {
            wp_send_json_success('Webhook test successful! Response code: ' . $response_code . ' Response: ' . $response_body);
        } else {
            $error_message = 'Webhook returned error. Response code: ' . $response_code;
            if (!empty($response_body)) {
                $error_message .= ' Response: ' . substr($response_body, 0, 500);
            }
            wp_send_json_error($error_message);
        }
        
        wp_die();
    }
    
    public function test_crm() {
        if (ob_get_length()) {
            ob_clean();
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            wp_die();
        }
        
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'lead_form_admin_nonce')) {
            wp_send_json_error('Security check failed');
            wp_die();
        }

        $response = wp_remote_get('http://mock-api:3000/', array(
            'timeout' => 5
        ));
        
        if (is_wp_error($response)) {
            $error_message = 'Mock API connection error: ' . $response->get_error_message();
            lead_form_log_error($error_message);
            wp_send_json_error($error_message);
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            wp_send_json_success('Mock API is accessible. Response code: ' . $response_code);
        }
        
        wp_die();
    }
}
?>