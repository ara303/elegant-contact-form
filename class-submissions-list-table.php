<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Submissions_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct(array(
            'singular' => 'submission',
            'plural'   => 'submissions',
            'ajax'     => false
        ));
    }

    public function get_columns() {
        return array(
            'cb'      => '<input type="checkbox" />',
            'id'      => 'ID',
            'time'    => 'Time',
            'name'    => 'Name',
            'email'   => 'Email',
            'phone'   => 'Phone',
            'message' => 'Message',
            'actions' => 'Actions'
        );
    }

    public function get_sortable_columns() {
        return array(
            'id'    => array('id', true),
            'time'  => array('time', false),
            'name'  => array('name', false),
            'email' => array('email', false)
        );
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'time':
            case 'name':
            case 'email':
            case 'phone':
            case 'message':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="submissions[]" value="%s" />',
            $item['id']
        );
    }

    public function column_actions($item) {
        $actions = array(
            'delete' => sprintf('<a href="?page=%s&action=%s&submission=%s&_wpnonce=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id'], wp_create_nonce('delete_submission'))
        );

        return $this->row_actions($actions);
    }

    public function get_bulk_actions() {
        return array(
            'delete' => 'Delete'
        );
    }

    public function process_bulk_action() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'elegant_contact_form';

        if ('delete' === $this->current_action()) {
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'delete_submission')) {
                die('Security check failed');
            }

            if (isset($_GET['submission'])) {
                $id = absint($_GET['submission']);
                $wpdb->delete($table_name, array('id' => $id), array('%d'));
            } elseif (isset($_POST['submissions'])) {
                $submissions = array_map('absint', $_POST['submissions']);
                $ids = implode(',', $submissions);
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'elegant_contact_form';

        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $search = (isset($_REQUEST['s'])) ? sanitize_text_field($_REQUEST['s']) : '';
        $orderby = (isset($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'time';
        $order = (isset($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'DESC';

        $search_query = $search ? $wpdb->prepare(
            " AND (name LIKE '%%%s%%' OR email LIKE '%%%s%%' OR message LIKE '%%%s%%')",
            $search,
            $search,
            $search
        ) : '';

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE 1=1 $search_query");

        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $offset = $paged * $per_page;

        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE 1=1 $search_query ORDER BY $orderby $order LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );

        if( !empty( $search_query ) ){
            echo '<p>Showing search results for: <b> '.$search.'</b>.</p>';
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    public function get_search_query() {
        return isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
    }
}
