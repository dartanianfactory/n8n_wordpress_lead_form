<?php
/**
 * Plugin Name: Lead Form Plugin
 * Description: 
 * Version: 1.0
 * Author: Roman Agafonov
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeadFormPlugin {
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_shortcode('lead_form', array($this, 'render_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_submit_lead_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_submit_lead_form', array($this, 'handle_form_submission'));
    }
    
    public function init() {
        register_post_type('applications',
            array(
                'labels' => array(
                    'name' => __('Applications'),
                    'singular_name' => __('Application')
                ),
                'public' => true,
                'has_archive' => true,
                'supports' => array('title', 'editor', 'custom-fields')
            )
        );
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'lead-form-js',
            plugin_dir_url(__FILE__) . 'lead-form.js',
            array('jquery'),
            '1.0',
            true
        );
        
        wp_localize_script('lead-form-js', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lead_form_nonce')
        ));
    }
    
    public function render_form($atts) {
        ob_start();
        ?>
        <div id="lead-form-container">
            <form id="lead-capture-form" method="post">
                <div class="form-group">
                    <label for="name">Имя *</label>
                    <input type="text" id="name" name="name" required>
                    <span class="error-message" id="name-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                    <span class="error-message" id="email-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" required>
                    <span class="error-message" id="phone-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="message">Комментарий</label>
                    <textarea id="message" name="message" rows="4"></textarea>
                </div>
                
                <button type="submit" id="submit-btn">Отправить заявку</button>
                
                <div id="form-messages"></div>
            </form>
        </div>
        
        <style>
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error-message {
            color: red;
            font-size: 12px;
            display: none;
        }
        #submit-btn {
            background: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        #form-messages {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    public function handle_form_submission() {
        if (!wp_verify_nonce($_POST['nonce'], 'lead_form_nonce')) {
            wp_die('Security check failed');
        }
        
        $errors = array();
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        
        if (empty($name)) {
            $errors['name'] = 'Пожалуйста, введите ваше имя';
        }
        
        if (empty($email) || !is_email($email)) {
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
                update_post_meta($post_id, '_lead_email', $email);
                update_post_meta($post_id, '_lead_phone', $phone);
                update_post_meta($post_id, '_lead_status', 'new');
                update_post_meta($post_id, '_lead_source', 'website');
                update_post_meta($post_id, '_lead_created', current_time('mysql'));
                
                $this->send_to_webhook(array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'message' => $message,
                    'wordpress_id' => $post_id,
                    'source' => 'website',
                    'timestamp' => current_time('mysql')
                ));
                
                wp_send_json_success('Заявка успешно отправлена!');
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array('form' => 'Произошла ошибка при отправке формы'));
        }
    }
    
    private function send_to_webhook($data) {
        $webhook_url = 'http://n8n:5678/webhook/wordpress-lead';
        
        $response = wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($data),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            error_log('Webhook error: ' . $response->get_error_message());
        }
        
        return $response;
    }

    public function update_application_status($post_id, $status) {
        update_post_meta($post_id, '_lead_status', $status);
    }
}

new LeadFormPlugin();

?>