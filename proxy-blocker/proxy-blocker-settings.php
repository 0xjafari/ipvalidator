<?php
// تنظیمات پلاگین
add_action('admin_menu', 'proxy_blocker_settings_menu');
// ثبت اکشن AJAX برای حذف همه IP ها
add_action('wp_ajax_proxy_blocker_delete_all_ips', 'proxy_blocker_delete_all_ips');
function proxy_blocker_settings_menu() {
    add_options_page(
        'تنظیمات مسدودکننده پروکسی',
        'مسدودکننده پروکسی',
        'manage_options',
        'proxy-blocker-settings',
        'proxy_blocker_settings_page'
    );
}


// افزودن قابلیت حذف همه IP های مسدود شده
function proxy_blocker_delete_all_ips() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blocked_ips';
    $wpdb->query("TRUNCATE TABLE $table_name");
}



function proxy_blocker_settings_page() {
    ?>
    <div class="wrap">
        <h1>تنظیمات مسدودکننده پروکسی</h1>
        <form method="post" action="options.php">
            <?php settings_fields('proxy-blocker-settings-group'); ?>
            <?php do_settings_sections('proxy-blocker-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">لایسنس API (ipvalidator.ir)</th>
                    <td><input type="text" name="proxy_blocker_license" value="<?php echo esc_attr(get_option('proxy_blocker_license')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">لایسنس API (ipinfo.io)</th>
                    <td><input type="text" name="proxy_blocker_ipinfo_license" value="<?php echo esc_attr(get_option('proxy_blocker_ipinfo_license')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">مسدود کردن ترافیک تور</th>
                    <td>
                        <select name="proxy_blocker_block_tor">
                            <option value="yes" <?php selected( get_option('proxy_blocker_block_tor'), 'yes' ); ?>>بله</option>
                            <option value="no" <?php selected( get_option('proxy_blocker_block_tor'), 'no' ); ?>>خیر</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">مسدود کردن پروکسی‌های عمومی</th>
                    <td>
                        <select name="proxy_blocker_block_general_proxy">
                            <option value="yes" <?php selected( get_option('proxy_blocker_block_general_proxy'), 'yes' ); ?>>بله</option>
                            <option value="no" <?php selected( get_option('proxy_blocker_block_general_proxy'), 'no' ); ?>>خیر</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">مسدود کردن پروکسی‌ها</th>
                    <td>
                        <select name="proxy_blocker_block_proxy">
                            <option value="yes" <?php selected( get_option('proxy_blocker_block_proxy'), 'yes' ); ?>>بله</option>
                            <option value="no" <?php selected( get_option('proxy_blocker_block_proxy'), 'no' ); ?>>خیر</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">محدوده مسدودسازی</th>
                    <td>
                        <select name="proxy_blocker_scope">
                            <option value="all" <?php selected( get_option('proxy_blocker_scope'), 'all' ); ?>>کل سایت</option>
                            <option value="admin" <?php selected( get_option('proxy_blocker_scope'), 'admin' ); ?>>پنل ادمین</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">پیام مسدودسازی عمومی</th>
                    <td><textarea name="proxy_blocker_block_message"><?php echo esc_textarea(get_option('proxy_blocker_block_message')); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">پیام مسدودسازی برای تور</th>
                    <td><textarea name="proxy_blocker_tor_message"><?php echo esc_textarea(get_option('proxy_blocker_tor_message')); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">کشورهای مسدود شده (با کاما جدا کنید)</th>
                    <td><textarea name="proxy_blocker_blocked_countries"><?php echo esc_textarea(get_option('proxy_blocker_blocked_countries')); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">لیست سفید آی پی ها (با کاما جدا کنید)</th>
                    <td><textarea name="proxy_blocker_whitelist"><?php echo esc_textarea(get_option('proxy_blocker_whitelist')); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">ثبت IPهای مسدود شده</th>
                    <td>
                        <select name="proxy_blocker_log_ips">
<option value="yes" <?php selected( get_option('proxy_blocker_log_ips'), 'yes' ); ?>>بله</option>
                            <option value="no" <?php selected( get_option('proxy_blocker_log_ips'), 'no' ); ?>>خیر</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">ارسال ایمیل به ادمین</th>
                    <td>
                        <select name="proxy_blocker_email_admin">
                            <option value="yes" <?php selected( get_option('proxy_blocker_email_admin'), 'yes' ); ?>>بله</option>
                            <option value="no" <?php selected( get_option('proxy_blocker_email_admin'), 'no' ); ?>>خیر</option>
                        </select>
                    </td>
                </tr>
            </table>
			
			

            <?php submit_button(); ?>
        </form>
		
    <p>
        <button class="button" onclick="if(confirm('آیا از حذف همه IP های مسدود شده اطمینان دارید؟')) { jQuery.post(ajaxurl, {
            action: 'proxy_blocker_delete_all_ips'
        }); }">حذف همه IP های مسدود شده</button>
    </p>
    </div>
    <?php
}

// ثبت تنظیمات
add_action('admin_init', 'proxy_blocker_settings_init');
function proxy_blocker_settings_init() {
    // ... (سایر تنظیمات)
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_ipinfo_license'
    );
    register_setting(
        'proxy_blocker-settings-group',
        'proxy_blocker_blocked_countries',
        'sanitize_text_field'
    );
    register_setting(
        'proxy_blocker-settings-group',
        'proxy_blocker_whitelist',
        'sanitize_text_field'
    );
    // ... (سایر تنظیمات)
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_license'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_block_tor'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_block_proxy'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_block_general_proxy',
        'sanitize_text_field'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_block_proxy'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_scope'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_block_message'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_tor_message'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_log_ips'
    );
    register_setting(
        'proxy-blocker-settings-group',
        'proxy_blocker_email_admin'
    );
	
	
	
}