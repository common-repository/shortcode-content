<?php
/*
Plugin Name:  Shortcode Content
Plugin URI:   https://elobyte.com
Description:  Text creation with shortcodes anywhere
Version:      1.0
Author:       Elobyte
Author URI:   https://elobyte.com/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

 */
/**
 * The license under which the WordPress software is released is the GPLv2 (or later) from the Free Software Foundation. A copy of the license is included with every copy of WordPress, but you can also read the text of the license here.

*Part of this license outlines requirements for derivative works, such as plugins or themes. Derivatives of WordPress code inherit the GPL license. Drupal, which has the same GPL license as WordPress, has an excellent page on licensing as it applies to themes and modules (their word for plugins).

*There is some legal grey area regarding what is considered a derivative work, but we feel strongly that plugins and themes are derivative work and thus inherit the GPL license. If you disagree, you might want to consider a non-GPL platform such as Serendipity (BSD license) or Habari (Apache license) instead.
 */

//Exit if accessed directly
if(!defined('ABSPATH')){
    exit;
}
define('SC_CONTENT_VERSION', '1.0');//Global version for all local scripts

function sc_content_admin_scripts(){
    //Include main js for plugin
    wp_enqueue_script('sc_content-main-js', plugins_url('js/main.js', __FILE__),array(), SC_CONTENT_VERSION, false);
}

function sc_content_admin_styles()
{
    
    //material designs
    wp_enqueue_style('material-css', plugins_url('css/material_design.css', __FILE__)); 
    //Include the main css
    wp_enqueue_style('sc_content-main-css', plugins_url('css/main.css', __FILE__), SC_CONTENT_VERSION, false);
    wp_enqueue_style('google-fonts-css', plugins_url('css/google_fonts.css', __FILE__), SC_CONTENT_VERSION, false);

}

// load the scripts on only the plugin admin page 
if (isset($_GET['page']) || ($_GET['page'] == 'sc_content') || ($_GET['page'] == 'add_new')) { 
    // if we are on the plugin page, enable the script
    add_action('admin_print_styles', 'sc_content_admin_styles');//print styles
    add_action('admin_print_scripts', 'sc_content_admin_scripts');//print scripts
 
}


//activation hook
function sc_content_activate(){
    //flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'sc_content_activate');


//Creating a database for the plugin on activation
function sc_content_create_db()
{
    global $wpdb;//Wordpress core variable for db access
    global $sc_version;
    $sc_version = '1.0'; //plugin version

    $sc_content_charset_collate = $wpdb->get_charset_collate();//DB collation
    $sc_content_table_name = $wpdb->prefix . 'sc_content'; 
    $sc_content_table_two_name = $wpdb->prefix . 'sc_content_meta';

    /** ---- check if plugin DB exists . Create a db table if one does not exist------ */
    if ($wpdb->get_var("SHOW TABLES LIKE '$sc_content_table_name'") != $sc_content_table_name) {
        /** ------  if not create a DB for plugin ----- */
        $sc_create_sql = "CREATE TABLE $sc_content_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            shortcode varchar(128) NOT NULL,
            content varchar(128) NOT NULL,
            UNIQUE KEY id (id),
            PRIMARY KEY(id)
	    ) $sc_content_charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sc_create_sql);
    }
    if ($wpdb->get_var("SHOW TABLES LIKE '$sc_content_table_two_name'") != $sc_content_table_two_name) {
    /** ------  if not create a DB for plugin ----- */
        $sc_create_sql = "CREATE TABLE $sc_content_table_two_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            id_user int(11) NOT NULL,
            show_info tinyint(2) DEFAULT 1 NOT NULL,
            UNIQUE KEY id (id),
            PRIMARY KEY(id)
        ) $sc_content_charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sc_create_sql);
    }


    /** ----- Check to see if any new version is released ------ */
  $sc_installed_version = get_option("sc_version");
    if($sc_installed_version != $sc_version){
        $sc_create_sql = "CREATE TABLE $sc_content_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            shortcode varchar(128) NOT NULL,
            content varchar(128) NOT NULL,
            UNIQUE KEY id (id),
            PRIMARY KEY(id)
	    ) $sc_content_charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sc_create_sql);
        update_option("sc_version", $sc_version); //restoring the version to current
    }

}
register_activation_hook(__FILE__, 'sc_content_create_db');



//Registering Version check for plugin globally
function sc_content_update_db_check()
{
    global $sc_version;
    if (get_site_option('sc_version') != $sc_version) {
        sc_content_create_db();
    }
}
add_action('plugins_loaded', 'sc_content_update_db_check');


// Registering the shortcodes on init.
function sc_content_add_shortcodes()
{
    global $wpdb;
    $query = "SELECT * FROM {$wpdb->prefix}sc_content";
    $results = $wpdb->get_results($query);
    if (isset($results)) {
        foreach ($results as $row) {
            $fn = function () use ($row) {
                return $row->content;
            };
            add_shortcode($row->shortcode, $fn);
        }
    }
}
add_action('init', 'sc_content_add_shortcodes');


//Delete database tables on Plugin deactivation
function sc_content_remove_database()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sc_content';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);

    $table_name = $wpdb->prefix . 'sc_content_meta';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}
register_deactivation_hook(__FILE__, 'sc_content_remove_database');

//deactivation hook
function sc_content_deactivate(){
    //flush rewrite rules
    remove_role('sc_author');
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'sc_content_deactivate');


//create Top level menu for plugin
//menu page hook
function sc_content_create_admin_menu_item(){
    //Top level menu
    add_menu_page(
        __('SC Content'),
        __('SC Content'),
        'manage_options',
        'sc_content',
        'sc_content_shortcodes_html',
        'dashicons-format-status',
        20
    );
    //add submenu under toplevel menu
    add_submenu_page(
        'sc_content',
        'Add Shortcodes',
        'Add new',
        'manage_options',
        'add_new',
        'sc_content_addNew_html'

    );
}
add_action('admin_menu', 'sc_content_create_admin_menu_item');


//Edit and Delete Action handler function
function sc_content_shortcodes_html(){
    $action  = isset($_GET['action']) ? trim($_GET['action']) : '';

    if($action == "sc_edit"){
        $sc_content_id = isset($_GET['sc_id']) ? intval($_GET['sc_id']) : '';//getting id from url
        ob_start();
        require_once plugin_dir_path(__FILE__) . 'includes/sc_content_edit.php';

        $template = ob_get_contents();
        ob_end_clean();
        echo $template;

    }elseif($action == "sc_delete"){
        $sc_content_id = isset($_GET['sc_id']) ? intval($_GET['sc_id']) : '';

        global $wpdb;
        $delete = $wpdb->delete("{$wpdb->prefix}sc_content",array('id' => $sc_content_id));
        $sql = "ALTER TABLE {$wpdb->prefix}sc_content AUTO_INCREMENT = 1 ";
        $wpdb->query($sql);

        ob_start();
        require_once plugin_dir_path(__FILE__) . 'includes/sc_content_table_new.php';

        $template = ob_get_contents();

        ob_end_clean();

        echo $template;

    }else{
        include  plugin_dir_path(__FILE__) . 'includes/sc_content_table_new.php';
    }
}

/** Function getting the add new shortocde page */
 function sc_content_addNew_html(){    
     ob_start();
     require_once plugin_dir_path(__FILE__) . 'includes/sc_content_add.php';

     $template = ob_get_contents();

     ob_end_clean();

     echo $template;     
 }


 //This is the final solution, Added manage_options as the new capability
//Create a new user Role SC Author on activation
function sc_content_add_user_role()
{
    add_role(
        'sc_author',
        __('SC Author'),
        array(
            'read' => true,
            'manage_options' =>true   
        )
    );
    
}
register_activation_hook(__FILE__, 'sc_content_add_user_role');


//hide in admin menu
function sc_content_hide_menu_items()
{
   //Checking if the user has capability of 'sc_author'
    if (current_user_can('sc_author')) {
        
        add_menu_page(
            __('Logout'),
            __('Logout'),
            'manage_options',
            'logout',
            'sc_content_logout',
            'dashicons-admin-users'

        );
    }
}
add_action('admin_menu', 'sc_content_hide_menu_items');

//Removing permissions to view other plugin menus for SC author
function sc_content_admin_init(){
    if(current_user_can('sc_author')){
        $menu_items_to_stay = array(
        //SC content
            'sc_content', 
            'logout'
        );
        foreach ($GLOBALS['menu'] as $key => $value) {//remove other plugin menus except  Sc Content
            if (!in_array($value[2], $menu_items_to_stay)) {
                remove_menu_page($value[2]);
            }
        }
    }
}
add_action('admin_init','sc_content_admin_init');


//Insert user meta for admin
function sc_content_admin_meta_insert(){
    $admin_id = get_current_user_id();
    global $wpdb;
    $wpdb->insert("{$wpdb->prefix}sc_content_meta", array(
        'id_user' => $admin_id
    ));
 }
register_activation_hook(__FILE__,'sc_content_admin_meta_insert');


/**Redirect to SC Content page fro USER ROLE 'SC AUTHOR' */
function sc_content_author_login_redirect($redirect_to, $request, $user)
{
    //Check if any user has caps
    if (isset($user->roles) && is_array($user->roles)) {

        if (in_array('sc_author', (array)$user->roles)) {
            return $redirect_to = home_url("/wp-admin/admin.php?page=sc_content");
        } else {
            return $redirect_to = home_url("/wp-admin/index.php");
        }

    }
}
add_filter("login_redirect", "sc_content_author_login_redirect", 10, 3);
/**END REdirect */


//Logout button action for Sc Author
add_action('init', 'sc_content_redirect_loggingout');
function sc_content_redirect_loggingout()
{
    if (isset($_GET['page']) && $_GET['page'] == 'logout') {
        wp_redirect(wp_logout_url());
        exit();
    }
}



     