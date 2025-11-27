<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Form_Plugin {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        
        add_shortcode('lead_form', array($this, 'render_form'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }
    
    public function init() {
        $this->register_post_type();
    }
    
    private function register_post_type() {
        register_post_type('applications',
            array(
                'labels' => array(
                    'name' => __('Applications'),
                    'singular_name' => __('Application'),
                    'add_new' => __('Add New Application'),
                    'add_new_item' => __('Add New Application'),
                    'edit_item' => __('Edit Application'),
                    'new_item' => __('New Application'),
                    'view_item' => __('View Application'),
                    'search_items' => __('Search Applications'),
                    'not_found' => __('No applications found'),
                    'not_found_in_trash' => __('No applications found in Trash')
                ),
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => false,
                'has_archive' => false,
                'supports' => array('title', 'editor', 'custom-fields'),
                'capability_type' => 'post',
                'capabilities' => array(
                    'create_posts' => false,
                ),
                'map_meta_cap' => true,
            )
        );
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_script('jquery');
        
        wp_enqueue_script(
            'lead-form-frontend-js',
            LEAD_FORM_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            LEAD_FORM_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'lead-form-frontend-css',
            LEAD_FORM_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            LEAD_FORM_PLUGIN_VERSION
        );
        
        wp_localize_script('lead-form-frontend-js', 'lead_form_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lead_form_nonce')
        ));
    }
    
    public function render_form($atts) {
        $atts = shortcode_atts(array(
            'title' => '',
            'class' => ''
        ), $atts);
        
        ob_start();
        lead_form_load_template('form-template.php', $atts);
        return ob_get_clean();
    }
}
?>
