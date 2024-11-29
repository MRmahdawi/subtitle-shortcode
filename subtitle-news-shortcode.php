<?php
/**
 * Plugin Name: افزونه زیرنویس
 * Description: این افزونه قابلیت زیرنویس را به سایت تان اضافه می کند. شما می توانید با نصب این افزونه از یک شورت کد برای نمایش زیرنویس در هر کجایی از سایت تان استفاده کنید. این شورت کد برای مایش زیرنویس کاربرد دارد.
 * Version: 1.0.0
 * Author: عبدالرحمان مهدوی
 */

//فعالسازی افزونه
function my_subtitle_plugin_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "subtitles";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        subtitle text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'my_subtitle_plugin_install');

// اضافه کردن منوی ادمبن
add_action('admin_menu', 'my_subtitle_plugin_menu');

function my_subtitle_plugin_menu() {
    add_menu_page(
        'زیرنویس',
        'زیرنویس ها',
        'manage_options',
        'my_subtitle_plugin',
        'my_subtitle_plugin_page'
    );
}

// صفحه مدیریت
function my_subtitle_plugin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "subtitles";

    // ذخیره زیرنویس ها در دیتابیس
    if (isset($_POST['add_subtitle'])) {
        $subtitle = sanitize_text_field($_POST['subtitle']);
        $wpdb->insert($table_name, array('subtitle' => $subtitle));
    }

    $subtitles = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <h1>مدیریت زیرنویس ها</h1>
    <form method="post">
        <input type="text" name="subtitle" placeholder="زیرنویس مد نظرتان را وارد کنید" required>
        <input type="submit" name="add_subtitle" value="اضافه کردن">
    </form>
    <ul>
        <?php foreach ($subtitles as $subtitle) : ?>
            <li ><?php echo esc_html($subtitle->subtitle); ?> <a href="?delete_id=<?php echo $subtitle->id; ?>" >حذف</a></li>
        <?php endforeach; ?>
    </ul>
    <?php
    if (isset($_GET['delete_id'])) {
        $id = intval($_GET['delete_id']);
        $wpdb->delete($table_name, array('id' => $id));
        wp_redirect(remove_query_arg('delete_id'));
        exit;
    }
}

// ایجاد شورت کد نمایش زیرنویس
add_shortcode('my_subtitles', 'my_subtitle_shortcode');

function my_subtitle_shortcode() {

    global $wpdb;
    $table_name = $wpdb->prefix . "subtitles";
    $subtitles = $wpdb->get_results("SELECT * FROM $table_name");
    $output = '<div class="subtitle-container-news"> <marquee behavior="scroll" direction="right" class="csubtitle"> ';

    $output .= '<div id="subtitle-area">';
    foreach ($subtitles as $subtitle) {
        
        $output .= '<span>' . esc_html($subtitle->subtitle) . '</span>';
        $output .= '<img src="' . get_site_icon_url() . '" alt="Logo" style="height:20px; margin:0 5px;">';
       
    }
    $output .= '</div>';
    $output .= '</marquee>';
    $output .= '</div>';

    return $output;
}

// افزودن استایل ها و اسکریپت های مورد نیاز
add_action('wp_enqueue_scripts', 'my_subtitle_style');

function my_subtitle_style() {
    wp_enqueue_style('my-subtitle-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('my-subtitle-script', plugin_dir_url(__FILE__) . 'script.js', array(), null, true);
}
