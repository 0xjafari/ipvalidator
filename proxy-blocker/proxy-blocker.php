<?php
/*
Plugin Name: Proxy Blocker
Description: Blocks access to your WordPress site from proxies and Tor networks.
Version: 1.0 (Beta)
Author: IPValidator.ir
Author URI: https://IPValidator.ir
*/

// فعال کردن پلاگین
add_action('plugins_loaded', 'proxy_blocker_init');
function proxy_blocker_init() {
    // بارگذاری تنظیمات
    require_once(plugin_dir_path(__FILE__) . 'proxy-blocker-settings.php');
}

// قلاب برای بررسی وضعیت IP
add_action('template_redirect', 'proxy_blocker_check_ip');
function proxy_blocker_check_ip() {
    // دریافت تنظیمات از پایگاه داده
    $license = get_option('proxy_blocker_license');
    $block_tor = get_option('proxy_blocker_block_tor', 'yes');
    $block_proxy = get_option('proxy_blocker_block_proxy', 'yes');
    $block_general_proxy = get_option('proxy_blocker_block_general_proxy', 'yes');
    $scope = get_option('proxy_blocker_scope');
    $block_message = get_option('proxy_blocker_block_message');
    $tor_message = get_option('proxy_blocker_tor_message');
    $log_ips = get_option('proxy_blocker_log_ips', 'no');
    $email_admin = get_option('proxy_blocker_email_admin', 'no');
    $admin_email = get_option('admin_email');

    // بررسی محدوده مسدودسازی
    if ($scope === 'admin' && (is_admin() || is_network_admin())) {
        wp_die('دسترسی شما به پنل مدیریت مسدود شده است.');
    }

    // بررسی IP و ارسال درخواست به API
    $ip = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'];
    $apiUrl = "https://ipvalidator.ir/api?apikey=$license&ip=$ip";

    $response = wp_remote_get($apiUrl);

    if (is_wp_error($response)) {
        // خطا در درخواست به API
        return;
    }

    $data = json_decode($response['body']);

// تابع برای ثبت IP در پایگاه داده
function proxy_blocker_log_ip($ip, $reason) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'blocked_ips';

    // بررسی وجود جدول
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if (!$table_exists) {
        // ایجاد جدول اگر وجود نداشته باشد
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ip varchar(100) NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            reason varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    // ثبت داده در جدول
    $data = array(
        'ip' => $ip,
        'timestamp' => current_time('mysql'),
        'reason' => $reason
    );

    $format = array('%s', '%s', '%s');

    $wpdb->insert($table_name, $data, $format);
}


    // بررسی پاسخ API و اعمال اقدامات
    if ($data && isset($data->status) && isset($data->status_tor)) {
        if (($block_proxy == 'yes' && $data->status == 'yes' && $block_general_proxy == 'yes') ||
            ($block_proxy == 'yes' && $block_tor == 'yes' && $data->status_tor == 'yes')) {
            //wp_die($block_tor == 'yes' ? $tor_message : $block_message);


        if ($block_proxy == 'yes' && $data->status == 'yes' && $block_general_proxy == 'yes') {
            wp_die($block_message);
        }
		
        if ($block_proxy == 'yes' && $block_tor == 'yes' && $data->status_tor == 'yes') {
            wp_die($tor_message);
        }
		
		
		
		
		
			// ثبت IP مسدود شده (در صورت فعال بودن)
			if ($log_ips == 'yes') {
				$reason = $block_tor == 'yes' ? 'Tor' : 'Proxy';
				proxy_blocker_log_ip($ip, $reason);
			}

            // ارسال ایمیل به ادمین (در صورت فعال بودن)
            if ($email_admin == 'yes') {
                wp_mail($admin_email, 'IP مسدود شده', "IP $ip مسدود شد.");
            }
			
			
			// بررسی سیاهه سفید
			//$whitelist = array('192.168.1.1', '10.0.0.1'); // لیست سفید IPها
			$whitelist = explode(',', get_option('proxy_blocker_whitelist'));
			if (in_array($ip, $whitelist)) {
				return;
			}

			// بررسی مسدودسازی بر اساس کشور
			$ipinfo_license = get_option('proxy_blocker_ipinfo_license');
			$blocked_countries = explode(',', get_option('proxy_blocker_blocked_countries'));

			if ($ipinfo_license) {
				$ipinfo = file_get_contents("http://ipinfo.io/$ip/country?token=$ipinfo_license");
				if (in_array($ipinfo, $blocked_countries)) {
					wp_die('دسترسی شما به دلیل محدودیت‌های جغرافیایی مسدود شده است.');
				}
			}
			
			
			
        }
    }
}