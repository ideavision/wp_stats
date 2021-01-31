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
?>

<!-- Place HTML -->
	<div style="line-height:210%;font-size:18px;">
		<?php echo "Total View"; ?>

		<?php echo "<br>"; ?>

		<?php echo "Today";?> :
		<?php echo $query_today+1; ?>

		<?php echo "<br>"; ?>

		<?php echo "Yesterday";?> :
		<?php echo $query_yesterday; ?>

		<?php echo "<br>"; ?>

		<?php echo "7days";?> :
		<?php echo $query_7days+1; ?>

		<?php echo "<br>"; ?>

		<?php echo "30days";?> :
		<?php echo $query_30days+1; ?>

		<?php echo "<br>"; ?>
	</div>
<!-- Place HTML -->
