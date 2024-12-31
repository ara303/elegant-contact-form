<?php
/*
Plugin Name: Elegant Contact Form
Description: A customizable contact form with database storage and email notifications
Version: 1.0
Author: Ed Adams
*/
if (!defined('ABSPATH')) {
    exit;
}

require_once 'class-submissions-list-table.php';

class Elegant_Contact_Form {
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_shortcode('elegant_contact_form', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_route'));
        add_action('admin_menu', array($this, 'add_submissions_page'));
    }

    public function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'elegant_contact_form';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            name tinytext NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20),
            message text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function add_admin_menu() {
        add_options_page(
            'Elegant Contact Form Settings',
            'Elegant Contact Form',
            'manage_options',
            'elegant-contact-form',
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        register_setting('elegant_contact_form_settings', 'ecf_admin_email');
        register_setting('elegant_contact_form_settings', 'ecf_recaptcha_site_key');
        register_setting('elegant_contact_form_settings', 'ecf_recaptcha_secret_key');
    }

    public function settings_page() {
        include plugin_dir_path(__FILE__) . 'admin-settings.php';
    }

    public function shortcode() {
        ob_start();
        include plugin_dir_path(__FILE__) . 'form-template.php';
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
    }

    public function register_rest_route() {
        register_rest_route('elegant-contact-form/v1', '/submit', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_form_submission'),
            'permission_callback' => '__return_true'
        ));
    }

    public function handle_form_submission($request) {
        $params = $request->get_params();

        // Verify reCAPTCHA
        $recaptcha_secret = get_option('ecf_recaptcha_secret_key');
        $recaptcha_response = $params['g-recaptcha-response'];

        $verify_response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_response
            )
        ));

        $verify_response = json_decode(wp_remote_retrieve_body($verify_response), true);

        if (!$verify_response['success']) {
            return new WP_Error('recaptcha_failed', 'reCAPTCHA verification failed', array('status' => 400));
        }

        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'elegant_contact_form';

        $wpdb->insert(
            $table_name,
            array(
                'time' => current_time('mysql'),
                'name' => sanitize_text_field($params['name']),
                'email' => sanitize_email($params['email']),
                'phone' => sanitize_text_field($params['phone']),
                'message' => sanitize_textarea_field($params['message'])
            )
        );

        // Send email
        $admin_email = get_option('ecf_admin_email');
        $subject = 'New Contact Form Submission';
        $message = "Name: {$params['name']}\n";
        $message .= "Email: {$params['email']}\n";
        $message .= "Phone: {$params['phone']}\n";
        $message .= "Message: {$params['message']}\n";

        wp_mail($admin_email, $subject, $message);

        return new WP_REST_Response(array('message' => 'Form submitted successfully'), 200);
    }

    public function add_submissions_page() {
        add_menu_page(
            'Form Submissions',
            'Form Submissions',
            'manage_options',
            'ecf-submissions',
            array($this, 'submissions_page'),
            'dashicons-list-view',
            30
        );
    }

    public function submissions_page() {
        $list_table = new Submissions_List_Table();
        $list_table->prepare_items();

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['submission'])) {
            $message = 'Submission deleted successfully.';
        }
        ?>
        <div class="wrap">
            <h1>Form Submissions</h1>
            <?php
            if (isset($message)) {
                echo '<div class="updated"><p>' . $message . '</p></div>';
            }
            ?>
            <form method="post">
                <?php
                $list_table->search_box('Search', 'search');
                $list_table->display();
                ?>
                <input type="hidden" name="s" value="<?php echo esc_attr($list_table->get_search_query()); ?>" />
            </form>
        </div>
        <?php
    }
}

new Elegant_Contact_Form();
