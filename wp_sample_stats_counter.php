<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: WP Stats
Plugin URI: https://ideavisionx.com
Description: statistic for Total View, Today, Yesterday,7days, 30days
Author: Narvik Aghamalian
Version: 1.0.0
License: GPLv2 or later
*/
class WPSampleStatsCounter
{
    public function __construct()
    {
        // Plugin is activated
        if (function_exists('register_activation_hook')) {
            register_activation_hook(__FILE__, array($this, 'activationHook'));
        }
        //Plugin is deactivated
        if (function_exists('register_deactivation_hook')) {
            register_deactivation_hook(__FILE__, array($this, 'deactivationHook'));
        }
        //Plugin is deleted
        if (function_exists('register_uninstall_hook')) {
            register_uninstall_hook(__FILE__, 'uninstallHook');
        }
        //footer hook
        add_action('wp_footer', array($this, 'filter_footer'));

        //widget section
        add_action( 'widgets_init', function () {
        	register_widget( 'My_Widget' );  //Set Widget to WordPress
        } );
        function wp_access_counter() {
        	// Include the most post page
            include(sprintf("%s/wp_widget_counter.php", dirname(__FILE__)));
        }
        //admin menu
        add_action('admin_menu', array($this, 'stats_counter_admin_menu'));

    }
    // Plugin is activated
    public function activationHook()
    {
        //Add Table to the Database
		global $jal_db_version;
		$jal_db_version = '1.0';

		  global $wpdb;
		  global $jal_db_version;

		  $table_name = $wpdb->prefix . 'sample_counter';

		  $charset_collate = $wpdb->get_charset_collate();

		  $sql = "CREATE TABLE $table_name (
		    id mediumint(9) NOT NULL AUTO_INCREMENT,
		    time date DEFAULT '0000-00-00' NOT NULL,
		    title varchar(155) DEFAULT '' NOT NULL,
		    url varchar(155) DEFAULT '' NOT NULL,
		    access int(30) DEFAULT 0 NOT NULL,
		    pageid int(30) DEFAULT 0 NOT NULL,
		    ipaddress varchar(155) DEFAULT '' NOT NULL,
		    useragent varchar(155) DEFAULT '' NOT NULL,
		    UNIQUE KEY id (id)
		  ) $charset_collate;";

		  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		  dbDelta( $sql );

		  add_option( 'jal_db_version', $jal_db_version );
    }

    // Plugin is deactivated
    public function deactivationHook()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sample_counter';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
        delete_option("jal_db_version");
    }

    // Plugin is deleted
    public function uninstallHook()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sample_counter';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
        delete_option("jal_db_version");
    }
    // admin page
    public function stats_counter_admin_menu()
    {
        add_options_page(
            'Stats Counter',
            'Settings for Stats Counter',
            'manage_options',
            'post_views_stats_admin_menu',
            array($this, 'stats_counter_edit_setting')
        );
    }

    // Link the admin page
    public function stats_counter_edit_setting()
    {
        // Include the settings page
        include(sprintf("%s/manage/admin.php", dirname(__FILE__)));
    }

    // Footer section
    public function filter_footer()
    {

        function insert_database($title,$permalink)
        {
            //Get Today's date
    		$current_now = current_time("Y-m-d");

            global $wpdb;
            $table_name = $wpdb->prefix . 'sample_counter';
            $wpdb->insert(
              $table_name,
              array(
                'time' => $current_now,
                'title' => $title,
                'url' => $permalink,
                'access' => 1,
                'pageid' => 0,
                'ipaddress' => 0,
                'useragent' => 0
              )
            );
        }
        //get "title"
          global $wpdb;
          $table_name = $wpdb->prefix . 'sample_counter';
          $query = $wpdb->prepare("SELECT * FROM $table_name");
          $rows = $wpdb->get_results($query);

          //Omit if the title is doubled
            foreach ($rows as $row) {
                if ($row->title == get_bloginfo('name') ){
                    $insert_sitetitle_none = true;
                    break;
                }
            }
            foreach ($rows as $row) {
                if ($row->title == get_the_title() ){
                    $insert_title_none = true;
                    break;
                }
            }

        //Top page
        if(is_front_page() and $insert_sitetitle_none !== true){//Omit if the title is doubled
                $get_title = get_bloginfo('name');
                $get_permalink = site_url();
                insert_database($get_title,$get_permalink);
        }else {
            //Blog list page , Category page, Archive page
            if(!(is_home() or is_category() or is_archive())){

                if($insert_title_none !== true){//Omit if the title is doubled
                    // Other pages
                    $get_title = get_the_title();
                    $get_permalink = get_permalink();
                    insert_database($get_title,$get_permalink);
                }
            }
        }
    }

}
$WPSampleStatsCounter = new WPSampleStatsCounter();

//Widget
class My_Widget extends  WP_Widget{

	function __construct() {
		parent::__construct(
			'my_widget', // Base ID
			'Stats Counter', // Name
			array( 'description' => 'Display View Count', ) // Args
		);
	}
    public function widget( $args, $instance ) {

    echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
    echo wp_access_counter();

    }
    public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'no title';
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}
    public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}

}
