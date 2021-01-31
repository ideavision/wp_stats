<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//Get Year,month,day,yesterday,week
	$current_now = current_time("Y-m-d");
	$current_yesterday = date( 'Y-m-d', strtotime( '-1 days', current_time('timestamp') ) );
	$current_week = date( 'Y-m-d', strtotime( '-7 days', current_time('timestamp') ) );
	$current_month = date( 'Y-m-d', strtotime( '-30 days', current_time('timestamp') ) );

//Take the value from the database "Today,Yesterday,7days,30days"
function pvs_counter_static_days($current , $current_length){
	global $wpdb;
	$table_name_sample_counter = $wpdb->prefix . 'sample_counter';

		$query = $wpdb->get_var($wpdb->prepare("
				 SELECT SUM(access)
				 FROM $table_name_sample_counter
				 WHERE time >= '%s' AND  time <= '%s'" , $current_length , $current));
		return $query;
}
$query_today = pvs_counter_static_days($current_now , $current_now);
$query_yesterday = pvs_counter_static_days($current_yesterday , $current_yesterday);
$query_7days = pvs_counter_static_days($current_now , $current_week);
$query_30days = pvs_counter_static_days($current_now , $current_month);

//Take Title,URL from the database
global $wpdb;
$table_name_sample_counter = $wpdb->prefix . 'sample_counter';
$query_title_url = $wpdb->prepare("SELECT * FROM $table_name_sample_counter");
$rows_title_url = $wpdb->get_results($query_title_url);

// Save
$Stats_Counter_save = $_POST['Stats_Counter_save'];
$Stats_Counter_save = wp_kses($Stats_Counter_save, array());

if ( isset( $Stats_Counter_save )){

  //nonce check
  if ( isset( $_POST['_wpnonce'] ) && $_POST['_wpnonce'] ) {
	   if ( check_admin_referer( 'Stats_Counter', '_wpnonce' ) ) {

		   //Clear the database table
		   delete_option('jal_db_version');
		   global $wpdb;
		   $table_name = $wpdb->prefix . 'sample_counter';
		   $sql = "DROP TABLE IF EXISTS $table_name";
		   $wpdb->query($sql);

		   //Put the table again
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
   }
}
?>

<!-- HTML -->
<div class="wrap"><br/>
    <h1>Stats Counter <font size="2">v1.0.0</font></h1>

    <table style="border:solid #000000 1px;padding:7px;font-size:15px;line-height:210%;text-align:left;">

        <tr valign="top" style="white-space:nowrap;">
            <td>
                <div>
                    <?php echo "Title and URL for the access Today<br>"; ?>

                    <?php $i=0; ?>
                    <?php //View the Title and URL ?>
                    <?php foreach ($rows_title_url as $row_title_url): ?>

						<?php //Please use $lists_time = array(); as your own ?>
                        <?php $lists_time = array();$lists_title = array();$lists_url = array(); ?>
                        <?php array_push($lists_title , $row_title_url->title); ?>
                        <?php array_push($lists_url , $row_title_url->url); ?>

                        <?php echo "$lists_title[$i] "; echo "&nbsp;&nbsp;&nbsp;<a href='$lists_url[$i]' target='_blank'>$lists_url[$i]</a>"; ?>
                        <?php echo "<br>"; ?>

                    <?php endforeach; ?>

                    <?php  echo "<hr>" ?>
                    <?php  //Total View ?>
                    <?php  echo "Total Access Page Today: $query_today" ?>
                </div>
            </td>
         </tr>

    </table>
	<br><hr><br>
	<form method="post" id="Stats_Counter_form" action="">
	<?php wp_nonce_field( 'Stats_Counter', '_wpnonce' ); ?>
		<table>
			<tr>
				<td>
				<div><span style="font-size:14px;font-weight:bold;">
					<input type="submit" name="Stats_Counter_save" value="Reset data" />
					<span style="font-weight:nomal;color:red;">← This will erase all the data from the database.</span></div>
				</td>
			</tr>
	</table>
	</form>
</div>
<!-- HTML -->
