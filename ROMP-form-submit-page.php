<?php

	$data = $_POST['data'];	 
	$decoded = json_decode($data, true);

	// print_r($decoded);

	$title = $decoded[0]['value'];
	$fname = $decoded[1]['value'];
	$sname = $decoded[2]['value'];
	$landline = $decoded[3]['value'];
	$mobile = $decoded[4]['value'];
	$email = $decoded[5]['value'];
	$postcode = $decoded[6]['value'];
	$county = $decoded[7]['value'];
	$postal_town = $decoded[8]['value'];
	$street_address1 = $decoded[9]['value'];
	$street_address2 = $decoded[10]['value'];
	$street_address3 = $decoded[11]['value'];
	$property_type = $decoded[12]['value'];
	$estimated_val = $decoded[13]['value'];
	$estimated_secured_debts = $decoded[14]['value'];
	$rfs = $decoded[15]['value'];
	$date = date("Y-m-d H:i:s"); 

	$location = preg_replace('/wp-content.*$/','',__DIR__);
	include ($location . '/wp-load.php');
	global $wpdb;

  	$sql = "INSERT INTO `wp_romp_enquiry`(`romp_id`, `romp_title`, `romp_fname`, `romp_sname`, `romp_landline`, `romp_mobile`, `romp_email`, `romp_postcode`, `romp_county`, `romp_postal_town`, `romp_street_address1`, `romp_street_address2`, `romp_street_address3`, `romp_property_type`, `romp_estimated_value`, `romp_estimated_secured_debts`, `romp_rfs`, `romp_date_registered`) VALUES (DEFAULT,'".$title."','".$fname."','".$sname."','".$landline."','".$mobile."','".$email."','".$postcode."','".$county."','".$postal_town."','".$street_address1."','".$street_address2."','".$street_address3."','".$property_type."','".$estimated_val."','".$estimated_secured_debts."','".$rfs."','".$date."')";
	$wpdb->query($sql);



	$value = 'admin_email';
	$result = $wpdb->get_results( 
                    $wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name=%s", $value) 
                 );
	$myemail = $result[0]->option_value;
	
	$errors = '';
	if(empty($_POST['email'])){
	   	   $errors .= "\n Error: All Fields Are Required</br>";
	   }else{
	   	   

   		   if($street_address3 == ''){
		   	$property_address = $street_address1 . ', ' . $street_address2 . ', ' . $street_address3;
		   }else{
		   	$property_address = $street_address1 . ', ' . $street_address2;
		   }

		   $property_type = $_POST['property_type'];
		   $estimated_val = $_POST['estimated_val'];
		   $estimated_secured_debts = $_POST['estimated_secured_debts'];
		   $rfs = $_POST['rfs'];	   
		}

	if( empty($errors)){ 
		$to = $myemail;		 
		$email_subject = "Must Sell My House Fast Enquiry Form Submission: $fname $sname"; 
		$email_body = "
			<h3>*** Here are the details ***</h3>
			<table border='1' width='50%'>
				<tr>
					<td colspan='2' style='text-align:center;font-weight:900;'>--- PERSONAL DETAILS ---</td>
				</tr>
				<tr>
					<td>Title :</td>
			        <td style='text-align:center;font-weight:bold;'>$title</td>
				</tr>
				<tr>
					<td>First Name :</td>
			        <td style='text-align:center;font-weight:bold;'>$fname</td>
				</tr>
				<tr>
					<td>Surname :</td>
			        <td style='text-align:center;font-weight:bold;'>$sname</td>
				</tr>
				<tr>
					<td>Email :</td>
			        <td style='text-align:center;font-weight:bold;'>$email</td>
				</tr>
				<tr>
					<td>Landline :</td>
			        <td style='text-align:center;font-weight:bold;'>$landline</td>
				</tr>
				<tr>
					<td>Mobile :</td>
			        <td style='text-align:center;font-weight:bold;'>$mobile</td>
				</tr>
			</table>
			</br></br>
			<table border='1' width='50%' style='margin-top:2em;'>
				<tr>
					<td colspan='2' style='text-align:center;font-weight:900;'>--- POST CODE DETAILS ---</td>
				</tr>
				<tr>
					<td>POST CODE :</td>
			        <td style='text-align:center;font-weight:bold;'>$postcode</td>
				</tr>
				<tr>
					<td>County :</td>
			        <td style='text-align:center;font-weight:bold;'>$county</td>
				</tr>
				<tr>
					<td>POSTAL TOWN :</td>
			        <td style='text-align:center;font-weight:bold;'>$postal_town</td>
				</tr>
			</table>
			</br></br>
			<table border='1' width='50%' style='margin-top:2em;'>
				<tr>
					<td colspan='2' style='text-align:center;font-weight:900;'>--- PROPERTY DETAILS ---</td>
				</tr>
				<tr>
					<td>Property Address :</td>
			        <td style='text-align:center;font-weight:bold;'>$property_address</td>
				</tr>
				<tr>
					<td>Property Type :</td>
			        <td style='text-align:center;font-weight:bold;'>$property_type</td>
				</tr>
				<tr>
					<td>Estimated Value :</td>
			        <td style='text-align:center;font-weight:bold;'>$estimated_val</td>
				</tr>
				<tr>
					<td>Estimated Secure Debts :</td>
			        <td style='text-align:center;font-weight:bold;'>$estimated_secured_debts</td>
				</tr>
				<tr>
					<td>Reason for Selling :</td>
			        <td style='text-align:center;font-weight:bold;'>$rfs</td>
				</tr>
			</table>
		"; 

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: ' . strip_tags($myemail) . "\r\n";
		$headers .= "Reply-To: ". strip_tags($myemail) . "\r\n";


		if(mail($to,$email_subject,$email_body,$headers)){
			echo "Your Enquiry Has Been sent Successfully!!";
		} else{
			echo "Failed To Send Enquiry";
		}
	}else{
		echo $errors;
	}