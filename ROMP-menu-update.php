<?php 
$location = preg_replace('/wp-content.*$/','',__DIR__);
include ($location . '/wp-load.php');
global $wpdb;

$result = $wpdb->get_results("SELECT * FROM wp_romp_settings");

foreach ( $result as $value ){
	$romp_crm_id = $value->romp_crm_id;
	$romp_cf_link = $value->romp_cf_link;
}
echo json_encode(array("romp_crm_id"=>$romp_crm_id,"romp_cf_link"=>$romp_cf_link));
