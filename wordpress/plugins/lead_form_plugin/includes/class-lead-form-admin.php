<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Form_Admin {
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Lead Form',
            'Lead Form',
            'manage_options',
            'lead-form',
            array($this, 'admin_dashboard_page'),
            'dashicons-email-alt',
            30
        );
        
        add_submenu_page(
            'lead-form',
            'Leads',
            'Leads',
            'manage_options',
            'edit.php?post_type=applications'
        );
        
        add_submenu_page(
            'lead-form',
            'Settings',
            'Settings',
            'manage_options',
            'lead-form-settings',
            array($this, 'admin_settings_page')
        );
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'lead-form') === false) {
            return;
        }
        
        wp_enqueue_style(
            'lead-form-admin-css',
            LEAD_FORM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LEAD_FORM_PLUGIN_VERSION
        );
        
        wp_enqueue_script('chart-js', 
            'https://cdn.jsdelivr.net/npm/chart.js', 
            array(), 
            '3.9.1', 
            true
        );
        
        wp_enqueue_script(
            'lead-form-admin-js',
            LEAD_FORM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'chart-js'),
            LEAD_FORM_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('lead-form-admin-js', 'lead_form_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lead_form_admin_nonce')
        ));
    }
    
    public function admin_init() {
        register_setting('lead_form_settings', 'lead_form_webhook_url');
        register_setting('lead_form_settings', 'lead_form_crm_url');
        register_setting('lead_form_settings', 'lead_form_api_key');
        register_setting('lead_form_settings', 'lead_form_success_message');
    }
    
    public function admin_dashboard_page() {
        $stats = $this->get_dashboard_stats();
        $recent_leads = $this->get_recent_leads(5);
        
        lead_form_load_template('admin-dashboard.php', array(
            'stats' => $stats,
            'recent_leads' => $recent_leads
        ));
    }
    
    public function admin_settings_page() {
        lead_form_load_template('admin-settings.php');
    }
    
    private function get_dashboard_stats() {
        return array(
            'total_leads' => $this->get_total_leads(),
            'new_leads' => $this->get_leads_by_status('new'),
            'processed_leads' => $this->get_leads_by_status('processed'),
            'successful_submissions' => $this->get_successful_submissions()
        );
    }
    
    private function get_total_leads() {
        $args = array(
            'post_type' => 'applications',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function get_leads_by_status($status) {
        $args = array(
            'post_type' => 'applications',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_lead_status',
                    'value' => $status
                )
            ),
            'fields' => 'ids'
        );
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    private function get_recent_leads($limit = 5) {
        $args = array(
            'post_type' => 'applications',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        return get_posts($args);
    }
    
    private function get_successful_submissions() {
        return $this->get_total_leads();
    }
    
    public function check_webhook_status() {
        $webhook_url = get_option('lead_form_webhook_url');
        if (empty($webhook_url)) {
            return false;
        }
        
        $response = wp_remote_get($webhook_url, array(
            'timeout' => 5,
            'headers' => array(
                'User-Agent' => 'WordPress Lead Form Plugin Status Check'
            )
        ));
        
        return !is_wp_error($response);
    }
    
    // Статический метод для использования в шаблонах
    public static function check_webhook_status_static() {
        $webhook_url = get_option('lead_form_webhook_url');
        if (empty($webhook_url)) {
            return false;
        }
        
        $response = wp_remote_get($webhook_url, array(
            'timeout' => 5,
            'headers' => array(
                'User-Agent' => 'WordPress Lead Form Plugin Status Check'
            )
        ));
        
        return !is_wp_error($response);
    }
}
?>
