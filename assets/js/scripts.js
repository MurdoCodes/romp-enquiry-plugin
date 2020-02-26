(function($){
	
	"use strict";
	
	var t,d,o,n,s,l,e,m,a,i,u,r,c,_,p,v,l;
	var romp_crm_id;
	var romp_cf_link;
	getValueFromDB();

	// EMAIL VALIDATION
	function validateEmail(e){
		var t=/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
		return t.test(e);
	}
	
	// GET FILE LOCATION
	function enquiryPluginUrl(){
		var plugin_url = ROMPpluginScript.pluginsUrl;
		return plugin_url;
	}
	
	// SUBMIT TO DB AND SEND TO EMAIL
	window.enquiryPageSubmitFunction = function(){
		event.preventDefault();
		$(".loading").show();
		
		var jsonValue = JSON.stringify($('#form-enquiry-page').serializeArray());
		$.ajax({
		   type: "POST",
		   url: enquiryPluginUrl() + "/romp-enquiry-plugin/ROMP-form-submit-page.php",
		   data: {data: jsonValue},
		   success: function(data){
		      enquirySubmitToCRM();
		      return true;
		   },
		   complete: function() {},
		   error: function(xhr, textStatus, errorThrown) {
		     return false;
		   }
		});
	}
	
	window.enquiryModalSubmitFunction = function (){
		event.preventDefault();
		$(".spinner").show();
		
		var jsonValue = JSON.stringify($('#form-enquiry-modal').serializeArray());
		
		$.ajax({
		   type: "POST",
		   url: enquiryPluginUrl() + "/romp-enquiry-plugin/ROMP-form-submit-modal.php",
		   data: {data: jsonValue},
		   success: function(data){
		      modalenquirySubmitToCRM();
		      return true;
		   },
		   complete: function() {},
		   error: function(xhr, textStatus, errorThrown) {
		     return false;
		   }
		});
	}

	function getValueFromDB(){
		$.ajax({
		    type: 'get',
		    url: enquiryPluginUrl() + "/romp-enquiry-plugin/ROMP-menu-update.php",
		    dataType: 'JSON',
		    success: function(response){
		      romp_crm_id = response.romp_crm_id[0]+''+response.romp_crm_id[1];
			  romp_cf_link = response.romp_cf_link; 
		    }
		});
	}

	// SUBMIT TO CRM
	function enquirySubmitToCRM(){
		
		t=document.getElementById("fname").value;
		d=document.getElementById("sname").value;
		n=document.getElementById("landline").value;
		l=document.getElementById("mobile").value;
		s=document.getElementById("email").value;
		o=document.getElementById("postcode").value;
		m=document.getElementById("county").value;
		i=document.getElementById("postal_town").value;
		u=document.getElementById("street_address1").value;
		c=document.getElementById("property_type").value;
		_=document.getElementById("estimated_val").value;
		p=document.getElementById("rfs").value;
		v="street_address1="+u+"&postal_town="+i+"&postcode="+o+"&county="+m+"&property_type="+c+"&rfs="+p+"&estimated_val="+_+"&fname="+t+"&sname="+d+"&landline="+n+"&email="+s;

		$.ajax({
		  type: 'GET',
		  url: 'https://propertyinvestorscrm.com/crm/Campaigns/webservice/?',
		  headers: {  'Access-Control-Allow-Origin': '*' },
		  crossDomain: true,
		  dataType: 'jsonp',
		  data: v+"&mid="+romp_crm_id+"&callback=parseRequest"
		}).done(function(data) { 
			enquiryPagesubmitToCF();
		 }).fail(function(data) {
			enquiryPagesubmitToCF();
		});

	}
	
	function modalenquirySubmitToCRM(){
		t=document.getElementById("m_fname").value;
		d=document.getElementById("m_sname").value;
		n=document.getElementById("m_landline").value;
		l=document.getElementById("m_mobile").value;
		s=document.getElementById("m_email").value;
		o=document.getElementById("m_postcode").value;
		m=document.getElementById("m_county").value;
		i=document.getElementById("m_postal_town").value;
		u=document.getElementById("m_street_address1").value;
		c=document.getElementById("m_property_type").value;
		_=document.getElementById("m_estimated_val").value;
		p=document.getElementById("m_rfs").value;

		v="street_address1="+u+"&postal_town="+i+"&postcode="+o+"&county="+m+"&property_type="+c+"&rfs="+p+"&estimated_val="+_+"&fname="+t+"&sname="+d+"&landline="+l+"&email="+s;		
		
		$.ajax({
		  type: 'GET',
		  url: 'https://propertyinvestorscrm.com/crm/Campaigns/webservice/?',
		  headers: {  'Access-Control-Allow-Origin': '*' },
		  crossDomain: true,
		  dataType: 'jsonp',
		  data: v+"&mid="+romp_crm_id+"&callback=parseRequest"
		}).done(function(data) { 
			submitToCF();
		 }).fail(function(data) {
			submitToCF();
		});

	}

	
	// SUBMIT TO CLICKFUNNELS
	function enquiryPagesubmitToCF(){

		var cflink = $("#CFLink").attr('placeholder');
		e=document.getElementById("fname").value;
		t=document.getElementById("sname").value;
		d=document.getElementById("landline").value;
		n=document.getElementById("mobile").value;
		l=document.getElementById("email").value;
		s=document.getElementById("postcode").value;
		o=document.getElementById("county").value;
		m=document.getElementById("postal_town").value;
		i=document.getElementById("street_address1").value;
		u=document.getElementById("street_address2").value;
		a=document.getElementById("street_address3").value;
		r=i+" / "+u+" / "+a;
		c=d+" / "+n;	
		
		// https://richard259.clickfunnels.com/wordpress-form22992198
		$(document.body).append('<form action="'+romp_cf_link+'" method="POST" id="cfForm"><input type="hidden" name="contact[zip]" value="'+s+'"><input type="hidden" name="contact[state]" value="'+o+'"><input type="hidden" name="contact[city]" value="'+m+'"><input type="hidden" name="contact[address]" value="'+r+'"><input type="hidden" name="contact[first_name]" value="'+e+'"><input type="hidden" name="contact[last_name]" value="'+t+'"><input type="hidden" name="contact[phone]" value="'+c+'"><input type="hidden" name="contact[email]" value="'+l+'"></form>');
		$('#cfForm').submit();

	}
	
	function submitToCF(){

		e=document.getElementById("m_fname").value;
		t=document.getElementById("m_sname").value;
		d=document.getElementById("m_landline").value;
		n=document.getElementById("m_mobile").value;
		l=document.getElementById("m_email").value;
		s=document.getElementById("m_postcode").value;
		o=document.getElementById("m_county").value;
		m=document.getElementById("m_postal_town").value;
		i=document.getElementById("m_street_address1").value;
		u=document.getElementById("m_street_address2").value;
		a=document.getElementById("m_street_address3").value;
		r = i + " / " + u + " / " + a;
		c = d + " / " + n;	

		// https://richard259.clickfunnels.com/wordpress-form21142536
		$(document.body).append('<form action="'+romp_cf_link+'" method="POST" id="cfForm"><input type="hidden" name="contact[zip]" value="'+s+'"><input type="hidden" name="contact[state]" value="'+o+'"><input type="hidden" name="contact[city]" value="'+m+'"><input type="hidden" name="contact[address]" value="'+r+'"><input type="hidden" name="contact[first_name]" value="'+e+'"><input type="hidden" name="contact[last_name]" value="'+t+'"><input type="hidden" name="contact[phone]" value="'+c+'"><input type="hidden" name="contact[email]" value="'+l+'"></form>');
		$('#cfForm').submit();

	}
		
	
	$(document).ready(function(){
		$(".alert").hide();
		$(".loading").hide();
		$(".spinner").hide();		
		$("#idpc_dropdown").addClass("form-control");
		$(".fieldset_2").hide();
		$(".fieldset_3").hide();
		// $('#romp_data_table').DataTable();
		
		$("#OptinSubmit1").on("click",function(){
			var e=document.getElementById("universalPostcode1").value;
			$("#m_postcode").val(e);
		});
		
		$("#OptinSubmit2").on("click",function(){
			var e=document.getElementById("universalPostcode2").value;
			$("#m_postcode").val(e);
		});
		
		$("#OptinSubmit3").on("click",function(){
			var e=document.getElementById("universalPostcode3").value;
			$("#m_postcode").val(e);
		});
		
		$("#next1").on("click",function(){
			$(".fieldset_1").hide("slow");
			$(".fieldset_2").show("slow");
			$(".fieldset_3").hide("slow");
		});
		
		$("#next2").on("click",function(){
			$(".fieldset_1").hide("slow");
			$(".fieldset_2").hide("slow");
			$(".fieldset_3").show("slow");
		});
		
		$("#prev1").on("click",function(){
			$(".fieldset_1").show("slow");
			$(".fieldset_2").hide("slow");
			$(".fieldset_3").hide("slow");
		});
		
		$("#prev2").on("click",function(){
			$(".fieldset_1").hide("slow");
			$(".fieldset_2").show("slow");
			$(".fieldset_3").hide("slow");
		});
		
		$("#m_email").keyup(function(){
			
			if(validateEmail($(this).val()))
			{
				$("#m_email").css({"border-color":"green","border-style":"solid"});
				$("#next2").removeClass("disabled");
				$("#next2").removeAttr("disabled");
				$( "#next2" ).addClass( "next" );
				$("#m_submitButton").removeClass("disabled");
				$("#m_submitButton").removeAttr("disabled");
				$(".alert").hide();
			}
			else{
				$("#m_email").css({"border-color":"red","border-style":"solid"});
				$(".alert").show();
			}
		});
		
		$("#email").keyup(function(){
			
			if(validateEmail($(this).val())){
				$("#email").css({"border-color":"green","border-style":"solid"});
				$("#submitButton").removeClass("disabled");
				$("#submitButton").removeAttr("disabled");
				$(".alert").hide();
			}	
			else{
				$("#email").css({"border-color":"red","border-style":"solid"});
				$(".alert").show();
			}
		});
		
		$("#enquiry_page_lookup_field").setupPostcodeLookup({
				api_key:"ak_iu6o2tv1fXGpi1evLcnQ0OFirLeru",
				output_fields:{
				line_1:"#street_address1",
				line_2:"#street_address2",
				line_3:"#street_address3",
				postcode:"#postcode",		
				post_town:"#postal_town",
				county:"#county"
			},
				button_class:"btn btn-md btn-info",
				input_class:"form-control",
				input:"#postcode"
		});

		$("#m_enquiry_modal_lookup_field").setupPostcodeLookup({
			api_key:"ak_iu6o2tv1fXGpi1evLcnQ0OFirLeru",
			output_fields:{
				line_1:"#m_street_address1",
				line_2:"#m_street_address2",
				line_3:"#m_street_address3",
				postcode:"#m_postcode",
				county:"#county",
				post_town:"#m_postal_town"
			},
			button_class:"btn btn-md btn-info",
			input_class:"form-control",
			input:"#m_postcode"
		});

		// $('.romp_admin_options_form').on('submit', function(e) {
		// 	e.preventDefault();
	 
		// 	var $form = $(this);
	 
		// 	$.post($form.attr('action'), $form.serialize(), function(data) {
		// 		alert('This is data returned from the server ' + data);
		// 	}, 'json');
		// });
		
	});
		
})(jQuery);