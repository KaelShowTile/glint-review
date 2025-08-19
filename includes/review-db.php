<?php class Glint_WC_Product_Review_DB{
    
    public static function create_table(){
        
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();
        $review_table_name = 'glint_review';
        $email_table_name = 'glint_review_feedback_email';
        $email_setting_table_name = 'glint_review_email_setting';

        $review_table_sql = "CREATE TABLE $review_table_name (
            review_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            customer_name VARCHAR(255) NOT NULL DEFAULT '',
            customer_email VARCHAR(255) NOT NULL DEFAULT '',
            review_content TEXT NOT NULL,
            review_date DATE NOT NULL,
            review_imgs TEXT NOT NULL,
            product_rating int(11) NULL,
            show_review int(11) NULL,
            PRIMARY KEY (review_id)
        ) $charset_collate;";

        $email_table_sql = "CREATE TABLE $email_table_name (
            email_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            customer_name VARCHAR(255) NOT NULL DEFAULT '',
            customer_email VARCHAR(255) NOT NULL DEFAULT '',
            review_item VARCHAR(255) NOT NULL DEFAULT '',
            review_item_link VARCHAR(255) NOT NULL DEFAULT '',
            check_reviewed BIGINT(20) UNSIGNED NOT NULL,
            first_send_date DATE NOT NULL,
            send_times BIGINT(20) UNSIGNED NOT NULL,
            review_date DATE NOT NULL,
            PRIMARY KEY (email_id)
        ) $charset_collate;";

        $email_setting_table_sql = "CREATE TABLE $email_setting_table_name (
            setting_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_name VARCHAR(255) NOT NULL DEFAULT '',
            setting_value TEXT NOT NULL,
            PRIMARY KEY (setting_id)
        ) $charset_collate;";

        dbDelta($review_table_sql);
        dbDelta($email_table_sql);
        dbDelta($email_setting_table_sql);
    }

    public static function cleanup(){
        $review_table_name = 'glint_review';
        $email_table_name = 'glint_review_feedback_email';
        $email_setting_table_name = 'glint_review_email_setting';
        
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS $review_table_name");
        $wpdb->query("DROP TABLE IF EXISTS $email_table_name");
        $wpdb->query("DROP TABLE IF EXISTS $email_setting_table_name");
    }
}