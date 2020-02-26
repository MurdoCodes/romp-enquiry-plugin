<?php
/**
 * Plugin Name: ROMP Enquiry Form Plugin
 * Plugin URI: http://lidelkimdaddie.co.uk/
 * Description: Search address using Post Codes and save data to database, send data to clickfunnels and CRM
 * Author: LKD Productions
 * Author URI: http://lidelkimdaddie.co.uk/
 * Version: 1.0
 *
 * License: GPLv2
 *
 */


class ROMP_Enquiry{
	private $wpdb;

	public function __construct(){
		global $wpdb;
		$this->wpdb = $wpdb;

		// add actions enqueue assets
		add_action('init',array( $this, 'add_cors_http_header'));
		add_action( 'wp_enqueue_scripts' , array( $this, 'ROMP_enquiry_form_assets') );
		add_action( 'admin_enqueue_scripts', array( $this, 'ROMP_enquiry_form_admin_assets'));
		add_action( 'admin_menu', array( $this , 'ROMP_plugin_setup_menu' ) );
		add_action( 'wp_footer', array( $this , 'ROMP_enquiry_form_modal' ) );
		add_action('admin_post_ROMP_custom_action_hook', array( $this , 'ROMP_admin_form_response' ));
		// activate shortcodes		
		add_shortcode( 'ROMP_Enquiry_Form', array( $this , 'ROMP_enquiry_form_page_shortcode' ) );
		add_shortcode( 'ROMP_Enquiry_Modal', array( $this , 'ROMP_enquiry_modal_shortcode' ) );

		if ( is_admin() ) {
			//activation
			register_activation_hook( __FILE__, array( $this, 'ROMP_Activate' ) );
			//deactivation
			register_deactivation_hook( __FILE__, array( $this, 'ROMP_Deactivate' ) );
        }
	}

	function add_cors_http_header(){
	    header("Access-Control-Allow-Origin: *");
	}


	// Register Styles and Scripts
	function ROMP_enquiry_form_admin_assets(){
		wp_enqueue_style('CSS-bootstrap-min', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css');
		wp_enqueue_script('JS-bootstrap-min', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js');
		wp_enqueue_style( 'ROMP-enquiry-form-css', plugins_url( 'romp-enquiry-plugin/assets/css/style.css'), 20, 1 );
	}
	function ROMP_enquiry_form_assets() {
		// wp_enqueue_style('CSS-bootstrap-min', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css');
		// wp_enqueue_script('JS-bootstrap-min', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js');
		// wp_enqueue_script('Jquery_DataTable', 'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js');
		// wp_enqueue_style( 'CSS_DataTable', 'https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css' );
	    // wp_enqueue_script('Bootstrap_DataTabe', 'https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js');
		
	    wp_enqueue_style( 'ROMP-enquiry-form-css', plugins_url( 'romp-enquiry-plugin/assets/css/style.css'), 20, 1 );
	    wp_enqueue_script( 'ROMP-enquiry-form-postcodes-api', plugins_url( 'romp-enquiry-plugin/assets/js/postcodes.min.js'), array('jquery'), '1.0.0', true );	    
	    wp_enqueue_script( 'ROMP-enquiry-form-script', plugins_url( 'romp-enquiry-plugin/assets/js/scripts.js'), '', '1.0.0', true );
	    wp_localize_script('ROMP-enquiry-form-script', 'ROMPpluginScript', array(
		    'pluginsUrl' => plugins_url(),
		));
	}

	// Activation function
	function ROMP_Activate(){
		//create database
		$this->create_ROMP_enquiry_Db_table();
		$this->create_ROMP_settings_Db_table();
	}

	// Deactivation function
	function ROMP_Deactivate(){		
		flush_rewrite_rules();
	}


	//ERROR MESSAGE 
	function my_error_notice() {
?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'There has been an error. Bummer!', 'my_plugin_textdomain' ); ?></p>
    </div>
<?php
	}

	//SUCCESS MESSAGE 
	function my_update_notice() {
?>
	    <div class="notice notice-success is-dismissible">
	        <p><?php _e( 'The plugin has been updated, excellent!', 'my_plugin_textdomain' ); ?></p>
	    </div>
<?php
	}
	

	//Create DB Table For Saving All the Registrations
	function create_ROMP_enquiry_Db_table(){
		ob_start();
		$table_name = $this->wpdb->prefix . 'romp_enquiry';
    	$charset_collate = $this->wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name ( 
				`romp_id` INT NOT NULL AUTO_INCREMENT ,
				`romp_title` TEXT NOT NULL ,
				`romp_fname` TEXT NOT NULL ,
				`romp_sname` TEXT NOT NULL ,
				`romp_landline` VARCHAR(50) NOT NULL ,
				`romp_mobile` VARCHAR(50) NOT NULL ,
				`romp_email` VARCHAR(50) NOT NULL ,
				`romp_postcode` VARCHAR(50) NOT NULL ,
				`romp_county` TEXT NOT NULL ,
				`romp_postal_town` TEXT NOT NULL ,
				`romp_street_address1` TEXT NOT NULL ,
				`romp_street_address2` TEXT NOT NULL ,
				`romp_street_address3` TEXT NOT NULL ,
				`romp_property_type` TEXT NOT NULL ,
				`romp_estimated_value` VARCHAR(50) NOT NULL ,
				`romp_estimated_secured_debts` VARCHAR(50) NOT NULL ,
				`romp_rfs` TEXT NOT NULL ,
				`romp_date_registered` DATETIME NOT NULL ,
				PRIMARY KEY (`romp_id`)
			) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	//Create Table For Plugin Settings
	function create_ROMP_settings_Db_table(){
		ob_start();
		$table_name = $this->wpdb->prefix . 'romp_settings';
    	$charset_collate = $this->wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
				`romp_crm_id` TEXT NOT NULL ,
				`romp_cf_link` TEXT NOT NULL
			) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	// For Admin Settings
	function ROMP_plugin_setup_menu(){
        add_menu_page( 'ROMP Enquiry Settings', 'ROMP Enquiry', 'manage_options', 'romp_enquiry_settings', array( $this , 'ROMP_setup_menu_content' ), '', 99 );
	}
	
	// For Admin Settings Content
	function ROMP_setup_menu_content(){	
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$ROMP_add_meta_nonce = wp_create_nonce( 'ROMP_add_user_meta_form_nonce' ); 

		if(isset($_GET['message'])==1){
			$this->my_update_notice();
		}else if(isset($_GET['message'])==2){
			$this->my_update_notice();
		}else if(isset($_GET['message'])==3){
			$this->my_error_notice();
		}

		echo '
			<div class="container">
				<div class="row">
					<div class="col-6">
						<div class="card" id="constant_contact">
							<img src="' . esc_url( plugins_url( 'assets/img/ROMP.png', __FILE__ ) ) . '" class="img-responsive" style="width:50%;">
							<h2 class="title">ROMP Settings</h2>
							<br class="clear">
							<div class="inside">
								<p>
									First Install <em style="background: hsl(0, 0%, 90%);padding: .5% 1% .5% 1%;">BootstrapCDN – WordPress CDN Plugin</em><br>
									Use this shortcode for page type enquiry form <em style="background: hsl(0, 0%, 90%);padding: .5% 1% .5% 1%;">[ROMP_Enquiry_Form]</em><br>
									Use this shortcode for modal type enquiry form <em style="background: hsl(0, 0%, 90%);padding: .5% 1% .5% 1%;">[ROMP_Enquiry_Modal]</em>
								</p>
							</div>
						</div>
					</div>
		';

		$result = $this->wpdb->get_results("SELECT * FROM wp_romp_settings");
		foreach ( $result as $value ){
			$romp_crm_id = $value->romp_crm_id;
			$romp_cf_link = $value->romp_cf_link;
		}

?>	
		<div class="col-6">
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="romp_admin_options_form">

					<input type="hidden" name="action" value="ROMP_custom_action_hook">
					<input type="hidden" name="ROMP_add_user_meta_nonce" value="<?php echo $ROMP_add_meta_nonce ?>" />

					<div class="card" id="constant_contact">
					  <legend>Enter Settings:</legend>

							<div class="col-md-12">
								<div class="form-group">
								  <label for="crmMID">Enter CRM MID : </label>
								  <input type="text" class="form-control" id="crmMID" name="crmMID" placeholder="<?php if(isset($romp_crm_id)){echo $romp_crm_id;}else{echo 'Please Enter MID';}?>" required>
								</div>
							</div>

							<div class="col-md-12">
								<div class="form-group">
								  <label for="CFLink">Enter ClickFunnels Link : </label>
								  <input type="text" class="form-control" id="CFLink" name="CFLink" placeholder="<?php if(isset($romp_cf_link)){echo $romp_cf_link;}else{echo 'Please Enter Clickfunnels Link';} ?>" required>
								</div>
							</div>

							<div class="col-md-12">
								<div class="form-group">
								  <button type="submit" class="btn btn-success" id="CRMCFSubmit">SAVE</button>
								</div>
							</div>
					</div>

				</form>
			</div>
		</div>
	
	</br></br>

<?php
		$this->romp_show_all_data();
	}
	
	// For Admin Settings Saving, Updating data
	function ROMP_admin_form_response(){

		if( isset( $_POST['ROMP_add_user_meta_nonce'] ) && wp_verify_nonce( $_POST['ROMP_add_user_meta_nonce'], 'ROMP_add_user_meta_form_nonce') ) {
			
			$crmMID = $_POST['crmMID'];
			$CFLink = $_POST['CFLink'];
			$result = $this->wpdb->get_results("SELECT * FROM wp_romp_settings");

			if(empty($result)){

				$sql = "INSERT INTO `wp_romp_settings`(`romp_crm_id`, `romp_cf_link`) VALUES ('".$crmMID."','".$CFLink."')";
				echo $this->wpdb->query($sql);
				wp_safe_redirect(admin_url('admin.php?page=romp_enquiry_settings&message=1'));
				exit;

			}else{

				foreach ( $result as $page ){
					$sql = 'UPDATE wp_romp_settings SET romp_crm_id = "'.$crmMID.'", romp_cf_link = "'.$CFLink.'" WHERE romp_crm_id='.$page->romp_crm_id;
					echo $this->wpdb->query($sql);
				}
				wp_safe_redirect(admin_url('admin.php?page=romp_enquiry_settings&message=2'));
				exit;

			}

		}else {

			// wp_safe_redirect(admin_url('admin.php?page=romp_enquiry_settings&message=3'));
			echo "ERROR";

		}

	}

	// For Showing all the list of registered enquirues
	function romp_show_all_data(){
		ob_start();
		$retrieve_data = $this->wpdb->get_results("SELECT * FROM wp_romp_enquiry ORDER BY romp_id DESC");
?>	
		<div class="row">
			<div class="container">
				<table id="romp_data_table" class="table table-striped table-bordered" style="width:100%">
				    <thead>
				        <tr>
				            <th scope="col">ID</th>
				            <th scope="col">Name</th>
				            <th scope="col">Email</th>
				            <th scope="col">Contact Number</th>
				            <th scope="col">Postcode</th>
				            <th scope="col">Date/Time Registered</th>
				        </tr>
				    </thead>
				    <tbody>
				    	<?php foreach ($retrieve_data as $retrieved_data){ ?>
				        <tr>
				            <td scope="row"><?php echo $retrieved_data->romp_id; ?></td>
				            <td><?php echo $retrieved_data->romp_title.".". $retrieved_data->romp_fname . " " . $retrieved_data->romp_sname; ?></td>
				            <td><?php echo $retrieved_data->romp_email ?></td>
				            <td><?php echo $retrieved_data->romp_landline . " / " . $retrieved_data->romp_mobile; ?></td>
				            <td><?php echo $retrieved_data->romp_postcode; ?></td>
				            <td><?php echo $retrieved_data->romp_date_registered; ?></td>
				        </tr>
				    	<?php } ?>
				    </tbody>
				    <tfoot>
				        <tr>
				            <th scope="col">ID</th>
				            <th scope="col">Name</th>
				            <th scope="col">Email</th>
				            <th scope="col">Contact Number</th>
				            <th scope="col">Postcode</th>
				            <th scope="col">Date/Time Registered</th>
				        </tr>
				    </tfoot>
				</table>
			</div>
		</div>
	</div>
<?php

	}

	// For Admin Settings | Using of Shortcodes Page
	function ROMP_enquiry_form_page_shortcode(){

?>		<div class="container">
				<div class="row"><div class="loading">Loading&#8230;</div></div>
				<form method="POST" onsubmit="enquiryPageSubmitFunction()" id="form-enquiry-page">
					<div class="row">
						<fieldset id="personal_information">
							<legend>Personal Information:</legend>
								<div class="col-12 col-sm-12 col-md-6 col-xl-6">
									<div class="form-group">
									  	<select name="title" id="title" class="form-control">
											<option value="Select Title">Select Title</option>
											<option value="Mr">Mr</option>
											<option value="Mrs">Mrs</option>
											<option value="Miss">Miss</option>
											<option value="Ms">Ms</option>
											<option value="Dr">Dr</option>
											<option value="Other">Other</option>
										</select>
									</div>

									<div class="form-group">
									  <input type="text" class="form-control" id="fname" name="fname" placeholder="Enter First Name" required>
									</div>

									<div class="form-group">
									  <input type="text" class="form-control" id="sname" name="sname" placeholder="Surname" required>
									</div>
								</div>
								<div class="col-12 col-sm-12 col-md-6 col-xl-6">
									<div class="form-group">
									  <input type="tel" class="form-control" id="landline" name="landline" placeholder="Phone Number">
									</div>
									<div class="form-group">
									  <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Mobile Number" required>
									</div>
									<div class="form-group">
									  <input type="email" class="form-control email" id="email" name="email" placeholder="Email Address" required>
									</div>
								</div>
						</fieldset>
					</div>

					<div class="row">
						<fieldset id="postcode_section">
							<legend>About Your Property:</legend>
							<div class="col-12 col-sm-12 col-md-12 col-xl-12">								
								<div class="form-group theLabel">
								  <input type="text" id="postcode" placeholder="Postcode" name="postcode">
								  <span id="enquiry_page_lookup_field"></span>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-6 col-xl-6">
								<div class="form-group theLabel">
								  <input type="text" class="form-control" id="county" name="county" placeholder="County" readonly>
								</div>

								<div class="form-group theLabel">
								  <input type="text" class="form-control" id="postal_town" name="postal_town" placeholder="Postal Town" readonly>
								</div>

								<div class="form-group">
								  <input type="text" class="form-control" id="street_address1" name="street_address1" placeholder="Address Line 1" readonly>
								  <input type="text" class="form-control" id="street_address2" name="street_address2" placeholder="Address Line 2" readonly>
								  <input type="text" class="form-control" id="street_address3" name="street_address3" placeholder="Address Line 3" readonly>
								</div>
							</div>


							<div class="col-12 col-sm-12 col-md-6 col-xl-6">
								<div class="form-group">
								  <select class="form-control" id="property_type" name="property_type">
								  	<option value="Type of Property">Type of Property</option>
								  	<option value="Self-Contained Studio">Self-Contained Studio</option>
								  	<option value="1-Bed Flat">1-Bed Flat</option>
								  	<option value="2-Bed Flat">2-Bed Flat</option>
								  	<option value="3-Bed Flat">3-Bed Flat</option>
								  	<option value="4-Bed Flat">4-Bed Flat</option>
								  	<option value="1-Bed Terraced">1-Bed Terraced</option>
								  	<option value="2-Bed Terraced">2-Bed Terraced</option>
								  	<option value="3-Bed Terraced">3-Bed Terraced</option>
								  	<option value="4-Bed Terraced">4-Bed Terraced</option>
								  	<option value="5-Bed Terraced">5-Bed Terraced</option>
								  	<option value="6-Bed Terraced">6-Bed Terraced</option>
								  	<option value="1-Bed Semi-Detached">1-Bed Semi-Detached</option>
								  	<option value="2-Bed Semi-Detached">2-Bed Semi-Detached</option>
								  	<option value="3-Bed Semi-Detached">3-Bed Semi-Detached</option>
								  	<option value="4-Bed Semi-Detached">4-Bed Semi-Detached</option>
								  	<option value="5-Bed Semi-Detached">5-Bed Semi-Detached</option>
								  	<option value="6-Bed Semi-Detached">6-Bed Semi-Detached</option>
								  	<option value="1-Bed Detached">1-Bed Detached</option>
								  	<option value="2-Bed Detached">2-Bed Detached</option>
								  	<option value="3-Bed Detached">3-Bed Detached</option>
								  	<option value="4-Bed Detached">4-Bed Detached</option>
								  	<option value="5-Bed Detached">5-Bed Detached</option>
								  	<option value="6-Bed Detached">6-Bed Detached</option>
								  	<option value="1-Bed Apartment">1-Bed Apartment</option>
								  	<option value="2-Bed Apartment">2-Bed Apartment</option>
								  	<option value="3-Bed Apartment">3-Bed Apartment</option>
								  	<option value="4-Bed Apartment">4-Bed Apartment</option>
								  	<option value="5-Bed Apartment">5-Bed Apartment</option>
								  	<option value="Land">Land</option>
								  	<option value="Guest House">Guest House</option>
								  	<option value="Hotel">Hotel</option>
								  	<option value="Commercial Property">Commercial Property</option>
								  </select>
								</div>
								<div class="form-group">
								  <input type="text" class="form-control" id="estimated_val" name="estimated_val" placeholder="Enter Estimated Value">
								</div>
								<div class="form-group">
								  <input type="text" class="form-control" id="estimated_secured_debts" name="estimated_secured_debts" placeholder="Estimated Secured Debts">
								</div>
							</div>

							<div class="col-12 col-sm-12 col-md-12 col-xl-12">
								<div class="form-group">
								  <textarea class="form-control" cols="40" rows="10" id="rfs" name="rfs" placeholder="Reason For Selling"></textarea>
								</div>
							</div>

							<div class="col-12 col-sm-12 col-md-12 col-xl-12">
								<button type="submit" class="btn btn-block btn-primary disabled" id="submitButton" disabled>Submit</button>
								<div class="alert alert-warning" role="alert"><strong>Warning!</strong> Email Address Invalid! Please use valid email address!</div>
							</div>
						</fieldset>
					</div>
				</form>
			</div>
		</div>
<?php
	}

	// For Admin Settings | Using of Shortcodes Modal
	function ROMP_enquiry_form_modal(){
		echo '
		<div id="myEnquiryModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Enter Important Details</h4>
					</div>
						<div class="modal-body">
						<form form method="POST" onsubmit="enquiryModalSubmitFunction()" id="form-enquiry-modal">
						<div class="fieldset_1">
							<fieldset id="postcode_section">
								<legend>Post Code Details:</legend>
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
										<div class="form-group theLabel">
										  <label for="m_postcode">Post Code:</label>
										  <input type="text" id="m_postcode" name="m_postcode" value="">
										  <span id="m_enquiry_modal_lookup_field"></span>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 postcodehide">
										<div class="form-group theLabel">
										  <label for="m_county">County:</label>
										  <input type="text" class="form-control" id="m_county" name="m_county" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 postcodehide">
										<div class="form-group theLabel">
										  <label for="m_postal_town">Postal Town:</label>
										  <input type="text" class="form-control" id="m_postal_town" name="m_postal_town" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 postcodehide">
										<div class="form-group">
										  <label for="property_address">Property Address</label>
										  <input type="text" class="form-control" id="m_street_address1" name="m_street_address1" readonly>
										  <input type="text" class="form-control" id="m_street_address2" name="m_street_address2" readonly>
										  <input type="text" class="form-control" id="m_street_address3" name="m_street_address3" readonly>
										</div>
									</div>
								</div>
							</fieldset>

							<center><button type="button" class="next" id="next1">Next</button></center>
						</div>

						<div class="fieldset_2">
							<fieldset id="personal_information">
								<legend>Personal Information:</legend>
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">


										<div class="form-group">
										  <label for="m_title">Title</label>
											<select name="m_title" id="m_title" class="form-control">
												<option value="Select Title">Select Title</option>
												<option value="Mr">Mr</option>
												<option value="Mrs">Mrs</option>
												<option value="Miss">Miss</option>
												<option value="Ms">Ms</option>
												<option value="Dr">Dr</option>
												<option value="Other">Other</option>
											</select>
										</div>

										<div class="form-group">
										  <label for="m_fname">First Name</label>
										  <input type="text" class="form-control" id="m_fname" name="m_fname" placeholder="Enter First Name" required>
										</div>

										<div class="form-group">
										  <label for="m_sname">Surname</label>
										  <input type="text" class="form-control" id="m_sname" name="m_sname" placeholder="Enter Surname" required>
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
										<div class="form-group">
										  <label for="m_landline">Landline</label>
										  <input type="tel" class="form-control" id="m_landline" name="m_landline" placeholder="Enter Landline" >
										</div>
										<div class="form-group">
										  <label for="m_mobile">Mobile</label>
										  <input type="tel" class="form-control" id="m_mobile" name="m_mobile" placeholder="Enter Mobile" required>
										</div>
										<div class="form-group">
										  <label for="m_email">Email Address</label>
										  <input type="email" class="form-control" id="m_email" name="m_email" placeholder="Enter Email" required>
										</div>
									</div>
								</div>
							</fieldset>

							<center>
							<button type="button" class="prev" id="prev1">Previous</button>
							<button type="button" class="disabled "class="next" id="next2" disabled>Next</button>
							<div class="alert alert-warning" role="alert" style="width: 100% !important;"><strong>Warning!</strong> Email Address Invalid! Please use valid email address!</div>	
							</center>
						</div>

						<div class="fieldset_3">
							<fieldset id="property_details">
								<legend>Property Details:</legend>
								<div class="row">
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
										<div class="form-group">
										  <label for="m_property_type">Property Type</label>
										  <select class="form-control" id="m_property_type" name="m_property_type">
											<option value="Type of Property">Type of Property</option>
											<option value="Self-Contained Studio">Self-Contained Studio</option>
											<option value="1-Bed Flat">1-Bed Flat</option>
											<option value="2-Bed Flat">2-Bed Flat</option>
											<option value="3-Bed Flat">3-Bed Flat</option>
											<option value="4-Bed Flat">4-Bed Flat</option>
											<option value="1-Bed Terraced">1-Bed Terraced</option>
											<option value="2-Bed Terraced">2-Bed Terraced</option>
											<option value="3-Bed Terraced">3-Bed Terraced</option>
											<option value="4-Bed Terraced">4-Bed Terraced</option>
											<option value="5-Bed Terraced">5-Bed Terraced</option>
											<option value="6-Bed Terraced">6-Bed Terraced</option>
											<option value="1-Bed Semi-Detached">1-Bed Semi-Detached</option>
											<option value="2-Bed Semi-Detached">2-Bed Semi-Detached</option>
											<option value="3-Bed Semi-Detached">3-Bed Semi-Detached</option>
											<option value="4-Bed Semi-Detached">4-Bed Semi-Detached</option>
											<option value="5-Bed Semi-Detached">5-Bed Semi-Detached</option>
											<option value="6-Bed Semi-Detached">6-Bed Semi-Detached</option>
											<option value="1-Bed Detached">1-Bed Detached</option>
											<option value="2-Bed Detached">2-Bed Detached</option>
											<option value="3-Bed Detached">3-Bed Detached</option>
											<option value="4-Bed Detached">4-Bed Detached</option>
											<option value="5-Bed Detached">5-Bed Detached</option>
											<option value="6-Bed Detached">6-Bed Detached</option>
											<option value="1-Bed Apartment">1-Bed Apartment</option>
											<option value="2-Bed Apartment">2-Bed Apartment</option>
											<option value="3-Bed Apartment">3-Bed Apartment</option>
											<option value="4-Bed Apartment">4-Bed Apartment</option>
											<option value="5-Bed Apartment">5-Bed Apartment</option>
											<option value="Land">Land</option>
											<option value="Guest House">Guest House</option>
											<option value="Hotel">Hotel</option>
											<option value="Commercial Property">Commercial Property</option>
										  </select>
										</div>
										<div class="form-group">
										  <label for="m_estimated_val">Estimated Value</label>
										  <input type="text" class="form-control" id="m_estimated_val" name="m_estimated_val" placeholder="Enter Estimated Value">
										</div>
										<div class="form-group">
										  <label for="m_estimated_secured_debts">Estimated Secured Debts</label>
										  <input type="text" class="form-control" id="m_estimated_secured_debts" name="m_estimated_secured_debts" placeholder="Estimated Secured Debts">
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">

										<div class="form-group">
										  <label for="m_rfs">Reason For Selling</label>
										  <textarea class="form-control" cols="40" rows="10" id="m_rfs" name="m_rfs"></textarea>
										</div>
									</div>

								</div>
							</fieldset>
							<div class="row">
								<center>
									<button type="button" class="prev" id="prev2">Previous</button>
									<button type="submit" class="btn btn-primary disabled" id="m_submitButton" disabled>Submit</button>
								</center>
							</div>
						</div>
						</form>
					</div>
					<div class="modal-footer">
				 		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				 		<div class="spinner"></div>
				 	</div><!--modal-footer-->
				</div><!--modal-content-->
			</div><!--modal-dialog-->
		</div><!--myModal-->
		';
	}
	
	// For Admin Settings | Using of Shortcodes Page
	function ROMP_enquiry_modal_shortcode(){	
?>	
	
		<div class="row">
			<div class="postcodeContainer input-group col-12">
			  <input type="text" name="postcode" id="universalPostcode1" value="" placeholder="Postcode" class="form-control">
			  <span class="input-group-btn">
			    <button class="btn btn-default OptinSubmit" type="submit" id="OptinSubmit1" data-toggle="modal" data-target="#myEnquiryModal"></button>
			  </span>
			</div>
		</div>
<?php
	}

}
$ROMP_Enquiry = new ROMP_Enquiry();