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
        if (!wp_verify_nonce($_POST['nonce'], 'lead_form_nonce')) {
            wp_send_json_error(array('form' => 'Security check failed'));
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
        }
        
        try {
            $post_id = wp_insert_post(array(
                'post_title' => 'Заявка от ' . $name,
                'post_content' => $message,
                'post_type' => 'applications',
                'post_status' => 'publish'
            ));
            
            if ($post_id) {
                $this->save_lead_meta($post_id, $name, $email, $phone, $message);
                $this->send_to_webhook($post_id, $name, $email, $phone, $message);
                
                wp_send_json_success('Заявка успешно отправлена!');
            } else {
                wp_send_json_error(array('form' => 'Ошибка при создании заявки'));
            }
            
        } catch (Exception $e) {
            lead_form_log_error($e->getMessage());
            wp_send_json_error(array('form' => 'Произошла ошибка при отправке формы'));
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
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($data),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            lead_form_log_error('Webhook error: ' . $response->get_error_message());
        }
        
        return $response;
    }
    
    public function test_webhook() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $test_data = array(
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+79990001122',
            'message' => 'Test message from admin panel',
            'wordpress_id' => 0,
            'source' => 'admin_test',
            'timestamp' => current_time('mysql')
        );
        
        $webhook_url = get_option('lead_form_webhook_url');
        if (empty($webhook_url)) {
            wp_send_json_error('Webhook URL not configured');
        }
        
        $response = wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($test_data),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Webhook error: ' . $response->get_error_message());
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            wp_send_json_success('Webhook test successful! Response code: ' . $response_code);
        }
    }
    
    public function test_crm() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $crm_url = get_option('lead_form_crm_url');
        $api_key = get_option('lead_form_api_key');
        
        if (empty($crm_url) || empty($api_key)) {
            wp_send_json_error('CRM URL or API Key not configured');
        }
        
        // Если что меняем на API EspoCRM
        wp_send_json_success('CRM connection test - this would test actual EspoCRM API if configured');
    }
}
?>
