<?php 

/*
** Plugin Name:Dynamic Pricing And Discounts For Woocommerce

** Plugin URI: https://www.phoeniixx.com/product/dynamic-pricing-and-discounts-for-woocommerce/

** Description: It is a plugin which helps you to set up product based bulk discounts based on the quantity. 

** Version: 1.4.5

** Author: Phoeniixx

** Text Domain:phoen-dpad

** Author URI: http://www.phoeniixx.com/

** License: GPLv2 or later

** License URI: http://www.gnu.org/licenses/gpl-2.0.html

** WC requires at least: 2.6.0

** WC tested up to: 3.7.0

**/  

if ( ! defined( 'ABSPATH' ) ) exit;
	
		
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
			$gen_settings = get_option('phoe_disc_value');
			
			$enable_disc=isset($gen_settings['enable_disc'])?$gen_settings['enable_disc']:'';
			
			define('PHOEN_DPADPLUGURL',plugins_url(  "/", __FILE__));
	
			define('PHOEN_DPADPLUGDIRPATH',plugin_dir_path(  __FILE__));
				
		

		function phoe_dpad_menu_disc() {
			
			add_menu_page('Phoeniixx_Discounts',__( 'Discounts', 'phoeniixx_woocommerce_discount' ) ,'nosuchcapability','Phoeniixx_Discounts',NULL, PHOEN_DPADPLUGURL.'assets/images/aa2.png' ,'57.1');
		
			add_submenu_page( 'Phoeniixx_Discounts', 'Phoeniixx_Disc_settings', 'Settings','manage_options', 'Phoeniixx_Disc_settings',  'Phoen_dpad_settings_func' );
	
		}
		
		add_action('admin_menu', 'phoe_dpad_menu_disc');
		
	function phoen_scripts_for_discount(){	
								 													
		wp_enqueue_script('jquery-ui-accordion');
		
		wp_enqueue_script('phoen-select2-js-discount',plugin_dir_url(__FILE__).'assets/js/select2.min.js'); 
			
		wp_enqueue_style('phoen-select2-css-discount',plugin_dir_url(__FILE__).'assets/css/select2.min.css'); 
		
		wp_enqueue_style('phoen-new-css-discount',plugin_dir_url(__FILE__).'assets/css/phoen_new_add_backend.css'); 
		
		wp_enqueue_style('phoen-jquery-ui-discount',plugin_dir_url(__FILE__).'assets/css/admin_jquery_css_backend.css'); 
		
		wp_enqueue_script( 'jquery-ui-datepicker' );
		
	}
	
	add_action('admin_head','phoen_scripts_for_discount');
		
		function Phoen_dpad_settings_func()  {
			 
			$gen_settings = get_option('phoe_disc_value');
			 
			$enable_disc=isset($gen_settings['enable_disc'])?$gen_settings['enable_disc']:'';

				?>
			
			<div id="profile-page" class="wrap">
		
				<?php
					
				if(isset($_GET['tab']))
						
				{
					$tab = sanitize_text_field( $_GET['tab'] );
					
				}
				
				else
					
				{
					
					$tab="";
					
				}
				
				?>
				<h2> <?php _e('Settings','phoen-dpad'); ?></h2>
				
				<?php $tab = (isset($_GET['tab']))?$_GET['tab']:'';?>
				
				<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
				
					
					<a class="nav-tab <?php if($tab == 'phoeniixx_rule' ){ echo esc_html( "nav-tab-active" ); } ?>" href="?page=Phoeniixx_Disc_settings&amp;tab=phoeniixx_rule"><?php _e('Discounts','phoen-dpad'); ?><span class="phoen_oopw"> <?php _e('New','phoen-dpad'); ?></span></a>
					
					<a class="nav-tab <?php if($tab == 'phoeniixx_setting' ){ echo esc_html( "nav-tab-active" ); } ?>" href="?page=Phoeniixx_Disc_settings&amp;tab=phoeniixx_setting"><?php _e('Settings','phoen-dpad'); ?></a>
					
					<a class="nav-tab <?php if($tab == 'phoeniixx_premium' ){ echo esc_html( "nav-tab-active" ); } ?>" href="?page=Phoeniixx_Disc_settings&amp;tab=phoeniixx_premium"><span class="phoen_mine"><?php _e('Premium','phoen-dpad'); ?></span></a>				
					
				</h2>
				
			</div>
			
			<?php
			
			if($tab=='phoeniixx_setting' )
			{
				
				include_once(PHOEN_DPADPLUGDIRPATH.'includes/phoeniixx_discount_pagesetting.php');
										
			}elseif($tab=='phoeniixx_premium'){
				
				 include_once(PHOEN_DPADPLUGDIRPATH.'includes/phoen_premium_setting.php'); 
				 
			} elseif($tab=='phoeniixx_rule'  || $tab == ''){
				
				 include_once(PHOEN_DPADPLUGDIRPATH.'includes/phoeniixx_rule.php'); 
			} 
			
		}
		
		register_activation_hook( __FILE__, 'phoe_dpad_activation_func');

		function phoe_dpad_activation_func()
		{
			
			$phoe_disc_value = array(
				
				'enable_disc'=>1,
				'coupon_disc'=>1
			
				);
				
			update_option('phoe_disc_value',$phoe_disc_value);
			
		}
		
		if($enable_disc=="1") 	{
			
			include_once(PHOEN_DPADPLUGDIRPATH.'includes/phoeniixx_discount_productplugin.php');
		
		}
	
		function phoen_dpad_calculate_extra_fee( $cart_object ) {
		
			$gen_settings = get_option('phoe_disc_value');
			
			
			
			$coupon_disc=isset($gen_settings['coupon_disc'])?$gen_settings['coupon_disc']:'';
			
			/* echo '<pre>';
			print_r($cart_object);
			echo '</pre>';die(); */
			
			 if(($coupon_disc==1)&& !empty($cart_object->applied_coupons))
			{ 
				 
			}
			else 
			{
				
				// echo '1';die();
			
				
			
				$num_phoen='';
				
				$phoenuuy='';
				
				foreach ( $cart_object->cart_contents as $key => $value ) {  
				
					$val= get_post_meta($value['product_id'],'phoen_woocommerce_discount_mode', true); 
					
						 $old_price=$value['data']->get_price();
					
					$num_phoen='';
					if(!empty($val)){
						for($i=0;$i<count($val);$i++) 	{
						
							$quantity = intval( $value['quantity'] );
					   
							$orgPrice = intval( $value['data']->get_price() );
							
							$phoen_minval=isset($val[$i]['min_val'])?$val[$i]['min_val']:"";
							
							$phoen_maxval=isset($val[$i]['max_val'])?$val[$i]['max_val']:"";
							
							$phoen_from=isset($val[$i]['from'])?$val[$i]['from']:"";
							
							$phoen_from=strtotime($phoen_from);
							
							$phoen_to=isset($val[$i]['to'])?$val[$i]['to']:"";
							
							$phoen_to=strtotime($phoen_to);
							
							$current_date =strtotime(date("d-m-Y"));
							
							$crr_user_roles=wp_get_current_user()->roles;
							
							$user_role=isset($crr_user_roles[0])?$crr_user_roles[0]:'';
							
							$phoen_user_role=isset($val[$i]['user_role'])?$val[$i]['user_role']:"";
							
							if(!empty($phoen_user_role)){
								
								if( in_array($user_role,$phoen_user_role)){
									$phoenvar=5;
								}else{
									$phoenvar=2;
								}
								
							}else{
								$phoenvar=5;
							}
							 $product_never_expire=$val[$i]['never_expire'];
							
			
							if(((($current_date>=$phoen_from)&&($current_date<=$phoen_to)) || $product_never_expire=='1') && $phoenvar===5 ){
								
								if(($quantity>= $phoen_minval)&&($quantity<=$phoen_maxval))  {
									
									$type=isset($val[$i]['type'])?$val[$i]['type']:'';
										
									if($type=='percentage') {
										
										  $percent=(100-$val[$i]['discount'])/100 ;
										  
										 // $pp=$orgPrice;
										
										 $new_prc_value =  $orgPrice *=$percent;
										   
										   $value['data']->set_price($new_prc_value);      
										
											$num_phoen=87;
										
										break;
										
									}else{
										
										$new_prc_value =  $orgPrice-=$val[$i]['discount'];
										 
										   $value['data']->set_price($new_prc_value); 
										   
										   $num_phoen=87;
										 
										break;
										
									}
									
								}
							
							}
							
						}
					}
					
					
					$product_id_min=$value['product_id'];
					
					$gen_settings = get_option('phoen_backend_array');
					
					
					$product_data=$gen_settings['data'];
					$product_never_expire=$gen_settings['never_expire'];
					
					$product_user_role=$gen_settings['user_role']; 
			
					
					for($i=0;$i<count($product_data);$i++) 	{
						
						$quantity = intval( $value['quantity'] );
				   
						 $orgPrice = intval( $value['data']->get_price() );
						
						$phoen_minval=isset($product_data[$i]['min_val'])?$product_data[$i]['min_val']:"";
						
						$phoen_maxval=isset($product_data[$i]['max_val'])?$product_data[$i]['max_val']:"";
						
						$phoen_from=isset($gen_settings['from'])?$gen_settings['from']:"";
						
						$phoen_from=strtotime($phoen_from);
						
						$phoen_to=isset($gen_settings['to'])?$gen_settings['to']:"";
						
						$phoen_to=strtotime($phoen_to);
						
						$current_date =strtotime(date("d-m-Y"));
						
						$crr_user_roles=wp_get_current_user()->roles;
						
						$user_role=isset($crr_user_roles[0])?$crr_user_roles[0]:'';
						
						$phoen_user_role=isset($product_user_role)?$product_user_role:"";
						
						if(!empty($phoen_user_role)){
							
							if( in_array($user_role,$phoen_user_role)){
								$phoenuuy=5;
							}else{
								$phoenuuy=2;
							}
							
						}else{
							$phoenuuy=5;
						}
						
				$terms = get_the_terms( $value['product_id'], 'product_cat' );
				foreach ($terms as $term) {
					$product_cat_id[] = $term->term_id;
				}
					$cat_list=$gen_settings['cat_list'];
					
					$product_user_role=$gen_settings['user_role'];
					
					
					if(is_array($product_cat_id) && !empty($cat_list)){
						
						$capabilities=array_intersect($cat_list,$product_cat_id);
						
					}elseif(empty($cat_list)){
						
						$capabilities='50';
						
					}
					
					
					
						if(((($current_date>=$phoen_from)&&($current_date<=$phoen_to)) || $product_never_expire==='1') && $phoenuuy===5 && $num_phoen!==87  && (!empty($capabilities) || $capabilities=="50")){
					 						
							if(($quantity>= $phoen_minval)&&($quantity<=$phoen_maxval))  {
								
								$type=isset($product_data[$i]['type'])?$product_data[$i]['type']:'';
									
								if($type=='percentage') {
									
									  $percent=(100-$product_data[$i]['discount'])/100 ;
									  
									 // $pp=$value['data']->price ;
									
									   $new_prc_value =  $orgPrice *=$percent;
									   
									   $value['data']->set_price($new_prc_value);      
									
									break;
									
								}else{
									
									$new_prc_value =  $orgPrice-=$product_data[$i]['discount'];
									 
									   $value['data']->set_price($new_prc_value); 
									 
									break;
									
								}
								
							}
						
						} 
						
					}
					
					
				}
			
			}
		}
		
		
		function phoen_dpad_filter_item_price( $price, $values ) {
			  		
			global $woocommerce;

			$new_prod_val=get_post_meta( $values['product_id']);
			
			$ret_val="0";
			
			$num_phoen="0";
			$terms = get_the_terms( $values['product_id'], 'product_cat' );
			foreach ($terms as $term) {
				$product_cat_id[] = $term->term_id;
				
			}
			
			
				$val= get_post_meta($values['product_id'],'phoen_woocommerce_discount_mode', true); 
					
					$quantity = intval( $values['quantity'] );
					if(!empty($val)){
						
						for($i=0;$i<count($val);$i++) 	{
												 
							$phoen_minval=isset($val[$i]['min_val'])?$val[$i]['min_val']:"";
							
							$phoen_maxval=isset($val[$i]['max_val'])?$val[$i]['max_val']:"";
							
							$phoen_from=isset($val[$i]['from'])?$val[$i]['from']:"";
							
							$phoen_from=strtotime($phoen_from);
							
							$phoen_to=isset($val[$i]['to'])?$val[$i]['to']:"";
							
							$phoen_to=strtotime($phoen_to);
							
							$current_date =strtotime(date("d-m-Y"));
							
							$crr_user_roles=wp_get_current_user()->roles;
							
							$user_role=isset($crr_user_roles[0])?$crr_user_roles[0]:"";
							
							$phoen_user_role=isset($val[$i]['user_role'])?$val[$i]['user_role']:"";
							
							if(!empty($phoen_user_role)){
							
								if( in_array($user_role,$phoen_user_role)){
									$phoenvar=5;
								}else{
									$phoenvar=2;
								}
								
							}else{
									$phoenvar=5;
								}
							$product_never_expire=$val[$i]['never_expire'];
								
								if(((($current_date>=$phoen_from)&&($current_date<=$phoen_to)) || $product_never_expire=='1') && $phoenvar===5 ){
									
									if(($quantity>= $phoen_minval)&&($quantity<=$phoen_maxval))  {
										
									 $ret_val=1;
									 
									 $num_phoen=87;
									 
									}
									
								}
							
						}
					}
					
					
					$product_id_min=$values['product_id'];
					
					$gen_settings = get_option('phoen_backend_array');
					
					
					$product_data=$gen_settings['data'];
						
					
					for($i=0;$i<count($product_data);$i++) 	{
						
						$phoen_minval=isset($product_data[$i]['min_val'])?$product_data[$i]['min_val']:"";
						
						$phoen_maxval=isset($product_data[$i]['max_val'])?$product_data[$i]['max_val']:"";
						
						$phoen_from=isset($gen_settings['from'])?$gen_settings['from']:"";
						
						$phoen_from=strtotime($phoen_from);
						
						$phoen_to=isset($gen_settings['to'])?$gen_settings['to']:"";
						
						$phoen_to=strtotime($phoen_to);
						
						$current_date =strtotime(date("d-m-Y"));
						
						$crr_user_roles=wp_get_current_user()->roles;
						
						$user_role=isset($crr_user_roles[0])?$crr_user_roles[0]:'';
						
							$product_user_role=$gen_settings['user_role'];
						
						$phoen_user_role=isset($product_user_role)?$product_user_role:"";
						
						if(!empty($phoen_user_role)){
							
								if( in_array($user_role,$phoen_user_role)){
									$phoenvar=5;
								}else{
									$phoenvar=2;
								}
								
							}else{
								$phoenvar=5;
							}
						
								$product_never_expire=$gen_settings['never_expire'];
								
								$cat_list=$gen_settings['cat_list'];
					
								if(!empty($cat_list)){
									
									$capabilities=array_intersect($cat_list,$product_cat_id);
										
								}else{
						
									$capabilities='50';
									
								}
							
						
						if(((($current_date>=$phoen_from)&&($current_date<=$phoen_to)) || $product_never_expire==='1') && $phoenvar===5 && $num_phoen!==87 && (!empty($capabilities) || $capabilities=="50") ){
								 
							if(($quantity>= $phoen_minval)&&($quantity<=$phoen_maxval))  {
								
							
								
							 $ret_val=1;
							
							}
							
						}
						
					}
						
						
						
			$curr=get_woocommerce_currency_symbol();
			
			$old_price1="";
			
			$old_price="";
			
			global $product;
							
			$plan = wc_get_product($values['product_id']);
			
			$name=get_post($values['product_id'] );
			
			$_product = wc_get_product( $values['product_id'] );

			if ( $_product && $_product instanceof WC_Product_Variable && $values['variation_id'] )
			{
				$variations = $plan->get_available_variation($values['variation_id']);

				if($variations['display_regular_price']!='')
				{
					
				  $old_price1=$curr.$variations['display_regular_price'];
					
				}	
							
					if($variations['display_price']!='')
				{
				
					  $old_price1=$curr.$variations['display_price'];
					
				}	 
			}
			else
			{
				if($new_prod_val['_regular_price'][0]!='')
				{
					 $old_price1=$curr.$new_prod_val['_regular_price'][0];
					
				}
				if($new_prod_val['_sale_price'][0]!='')
				{
					 $old_price1=$curr.$new_prod_val['_sale_price'][0];
					
				}
	
			}
			$gen_settings = get_option('phoe_disc_value');
			
			$coupon_disc=isset($gen_settings['coupon_disc'])?$gen_settings['coupon_disc']:'';
			
			
		
		
			if((($coupon_disc==1)&&(!( empty( $woocommerce->cart->applied_coupons ))))||($ret_val==0))
			{
				return "<span class='discount-info' title=''>" .
					"<span class='old-price' >$old_price1</span></span>";
				
			}
			else{
			
					return "<span class='discount-info' title=''>" .
					"<span class='old-price' style='color:red; text-decoration:line-through;'>$old_price1</span> " .
					"<span class='new-price' > $price</span></span>";
			
		
			}
		}

		function phoen_dpad_filter_subtotal_price( $price, $values ) {
			
			global $woocommerce;

			$amt='';
			
			$type_curr='';
			
			$num_phoen='';
			
			$quantity = intval( $values['quantity'] );
			
			$curr=get_woocommerce_currency_symbol();
			
			$val= get_post_meta($values['product_id'],'phoen_woocommerce_discount_mode', true); 
			
			$gen_settings = get_option('phoe_disc_value');
			
			$coupon_disc=isset($gen_settings['coupon_disc'])?$gen_settings['coupon_disc']:'';
			if(!empty($val)){
				
				for($i=0;$i<count($val);$i++) 	{
						
					$quantity = intval( $values['quantity'] );
				   $phoen_minval=isset($val[$i]['min_val'])?$val[$i]['min_val']:"";
					$phoen_maxval=isset($val[$i]['max_val'])?$val[$i]['max_val']:"";
					
					$phoen_from=isset($val[$i]['from'])?$val[$i]['from']:"";
							
					$phoen_from=strtotime($phoen_from);
					
					$phoen_to=isset($val[$i]['to'])?$val[$i]['to']:"";
					
					$phoen_to=strtotime($phoen_to);
					
					$current_date =strtotime(date("d-m-Y"));
					
					$crr_user_roles=wp_get_current_user()->roles;
					
					$user_role=isset($crr_user_roles[0])?$crr_user_roles[0]:'';
							
					$phoen_user_role=isset($val[$i]['user_role'])?$val[$i]['user_role']:"";
					
					if(!empty($phoen_user_role)){
						
							if( in_array($user_role,$phoen_user_role)){
								$phoenvar=5;
							}else{
								$phoenvar=2;
							}
							
						}else{
							$phoenvar=5;
						}
					$product_never_expire=$val[$i]['never_expire'];
							
					if(((($current_date>=$phoen_from)&&($current_date<=$phoen_to)) || $product_never_expire==='1') && $phoenvar===5 ){
						
						if(($quantity>=$phoen_minval)&&($quantity<=$phoen_maxval))  {
							
							$amt=isset($val[$i]['discount'])?$val[$i]['discount']:'';
									
							$type=isset($val[$i]['type'])?$val[$i]['type']:'';
									
							if($type=='percentage') {
								
								$type_curr="[".$amt."% Discount]";
								
								$num_phoen=87;
								break;
							}
									
							else{
								
								$type_curr="[". $curr.$amt." Discount on each Product]";	
								
								$num_phoen=87;
								break;									
							}
							
						}
				 		
					}
						
				}
			}
			
			$product_id_min=$values['product_id'];
					
			$gen_settings = get_option('phoen_backend_array');
			
			$product_data=$gen_settings['data'];
			
			$product_user_role=$gen_settings['user_role'];
			
			
			for($i=0;$i<count($product_data);$i++) 	{
				
				$phoen_minval=isset($product_data[$i]['min_val'])?$product_data[$i]['min_val']:"";
				
				$phoen_maxval=isset($product_data[$i]['max_val'])?$product_data[$i]['max_val']:"";
				
				$phoen_from=isset($gen_settings['from'])?$gen_settings['from']:"";
				
				$phoen_from=strtotime($phoen_from);
				
				$phoen_to=isset($gen_settings['to'])?$gen_settings['to']:"";
				
				$phoen_to=strtotime($phoen_to);
				
				$current_date =strtotime(date("d-m-Y"));
				
				$crr_user_roles=wp_get_current_user()->roles;
				
				$user_role=isset($crr_user_roles[0])?$crr_user_roles[0]:'';
				
				$phoen_user_role=isset($product_user_role)?$product_user_role:"";
				
				 if(!empty($phoen_user_role)){
				 	
				 		if( in_array($user_role,$phoen_user_role)){
							$phoenvar=5;
						}else{
							$phoenvar=2;
						}
						
					}else{
						$phoenvar=5;
					}
				$product_never_expire=$gen_settings['never_expire'];
						
				$terms = get_the_terms( $values['product_id'], 'product_cat' );
				foreach ($terms as $term) {
					$product_cat_id[] = $term->term_id;
					
				}
					$cat_list=$gen_settings['cat_list'];
					
					$product_user_role=$gen_settings['user_role'];
					
					
					if(!empty($cat_list)){
							
							$capabilities=array_intersect($cat_list,$product_cat_id);
								
					}else{
						
						$capabilities='50';
						
					}
				
						
				if(((($current_date>=$phoen_from)&&($current_date<=$phoen_to)) || $product_never_expire==='1') && $phoenvar===5 && $num_phoen!==87 && (!empty($capabilities) || $capabilities=="50")){
				
					if(($quantity>=$phoen_minval)&&($quantity<=$phoen_maxval))  {
											
						$amt=isset($product_data[$i]['discount'])?$product_data[$i]['discount']:'';
								
						$type=isset($product_data[$i]['type'])?$product_data[$i]['type']:'';
								
						if($type=='percentage') {
							
							$type_curr="[".$amt."% Discount]";
							break;
						}
								
						else{
								
							$type_curr="[". $curr.$amt." Discount on each Product]";	
							break;									
						}
						
					}
					
				}
					
			}
		
			if(($coupon_disc==1)&&(!( empty( $woocommerce->cart->applied_coupons ))))
				{
					return "<span class='discount-info' title='$type_curr'>" .
					"<span>$price</span></span>";
					
				}
			else{
				
					return "<span class='discount-info' title='$type_curr'>" .
					"<span>$price</span>" .
					"<span class='new-price' style='color:red;'> $type_curr</span></span>";

				}
		}
		


		function phoen_dpad_filter_subtotal_order_price( $price, $values, $order )
		{
			
			global $woocommerce;

			$amt='';
			
			$type_curr='';
			
			$curr=get_woocommerce_currency_symbol();
		
			$val= get_post_meta($values['product_id'],'phoen_woocommerce_discount_mode', true); 
			
			$quantity = intval (isset($values['item_meta']['_qty'][0]) ? $values['item_meta']['_qty'][0]:'');
			
			$gen_settings = get_option('phoe_disc_value');
			
			$coupon_disc=isset($gen_settings['coupon_disc'])?$gen_settings['coupon_disc']:'';
			
			for($i=0;$i<count($val);$i++) 	{
				$phoen_minval=isset($val[$i]['min_val'])?$val[$i]['min_val']:"";
				$phoen_maxval=isset($val[$i]['max_val'])?$val[$i]['max_val']:"";
				
				$phoen_from=isset($val[$i]['from'])?$val[$i]['from']:"";
						
				$phoen_from=strtotime($phoen_from);
				
				$phoen_to=isset($val[$i]['to'])?$val[$i]['to']:"";
				
				$phoen_to=strtotime($phoen_to);
				
				$current_date =strtotime(date("d-m-Y"));
				
				$crr_user_roles=wp_get_current_user()->roles;
				
				$user_role=isset($crr_user_roles[0])?$crr_user_roles[0]:'';
						
				$phoen_user_role=isset($val[$i]['user_role'])?$val[$i]['user_role']:"";
				
				if(!empty($phoen_user_role)){
				
					if( in_array($user_role,$phoen_user_role)){
						$phoenvar=5;
					}else{
						$phoenvar=2;
					}
					
				}else{
					$phoenvar=5;
				}

				$product_never_expire=isset($val[$i]['never_expire'])?$val[$i]['never_expire']:'';
					if(isset($product_never_expire)){
				if(((($current_date>=$phoen_from)&&($current_date<=$phoen_to)) || $product_never_expire==='1') && $phoenvar===5 ){
					
					if(($quantity>=$phoen_minval)&&($quantity<=$phoen_maxval))  {
						
						$amt=isset($val[$i]['discount'])?$val[$i]['discount']:'';
								
						$type=isset($val[$i]['type'])?$val[$i]['type']:'';
								
						if($type=='percentage') {
								
							 $type_curr="[".$amt."% Discount]";
							 $num_phoen=87;
							 
							break;
						}
								
						else {
							
							$type_curr="[". $curr.$amt." Discount on each Product]";	
							
							 $num_phoen=87;
							 
							break;							
						}
					}
					
				}
			}
						
			}
			
			
			$product_id_min=$values['product_id'];
					
			$gen_settings = get_option('phoen_backend_array');
			
			$product_data=$gen_settings['data'];
			
			$product_user_role=$gen_settings['user_role'];
			
			
			for($i=0;$i<count($product_data);$i++) 	{
				
				$phoen_minval=isset($product_data[$i]['min_val'])?$product_data[$i]['min_val']:"";
				
				$phoen_maxval=isset($product_data[$i]['max_val'])?$product_data[$i]['max_val']:"";
				
				$phoen_from=isset($gen_settings['from'])?$gen_settings['from']:"";
				
				$phoen_from=strtotime($phoen_from);
				
				$phoen_to=isset($gen_settings['to'])?$gen_settings['to']:"";
				
				$phoen_to=strtotime($phoen_to);
				
				$current_date =strtotime(date("d-m-Y"));
				
				$crr_user_roles=wp_get_current_user()->roles;
				
				$user_role=isset($crr_user_roles[0])?$crr_user_roles[0]:'';
				
				$phoen_user_role=isset($product_user_role)?$product_user_role:"";
				
				if(!empty($phoen_user_role)){
					
						if( in_array($user_role,$phoen_user_role)){
							$phoenvar=5;
						}else{
							$phoenvar=2;
						}
						
					}else{
						$phoenvar=5;
					}
				$product_never_expire=$gen_settings['never_expire'];
				
				
				$terms = get_the_terms( $values['product_id'], 'product_cat' );
				foreach ($terms as $term) {
					$product_cat_id[] = $term->term_id;
					
				}
					$cat_list=$gen_settings['cat_list'];
					
					$product_user_role=$gen_settings['user_role'];
					
					if(!empty($cat_list)){
						
						$capabilities=array_intersect($cat_list,$product_cat_id);
						
					}else{
						
					$capabilities='50';
									
					}
									
					if(isset($num_phoen)){	
				if(((($current_date>=$phoen_from)&&($current_date<=$phoen_to)) || $product_never_expire==='1') && $phoenvar===5 && $num_phoen!==87 && (!empty($capabilities) || $capabilities=="50")){
					 
					if(($quantity>=$phoen_minval)&&($quantity<=$phoen_maxval))  {
						
						$amt=isset($product_data[$i]['discount'])?$product_data[$i]['discount']:'';
								
						$type=isset($product_data[$i]['type'])?$product_data[$i]['type']:'';
								
						if($type=='percentage') {
								
							 $type_curr="[".$amt."% Discount]";
							break;
						}
								
						else {
							
							$type_curr="[". $curr.$amt." Discount on each Product]";	
									break;							
						}
					}
					
				}
				
			}
						
			}
				
			$discount_type = get_post_meta( $order->get_id());
			
			
			if(($coupon_disc==1)&&(!( empty( $discount_type['_cart_discount'][0]))))
			{
				return "<span class='discount-info' title='$type_curr'>" .
				"<span>$price</span></span>";
				
			}
			else{
			
				return "<span class='discount-info' title='$type_curr'>" .
				"<span>$price</span>" .
				"<span class='new-price' style='color:red;'> $type_curr</span></span>";

			}
		} 
			
			
		if($enable_disc=="1") 	{
			
			add_action( 'woocommerce_before_calculate_totals', 'phoen_dpad_calculate_extra_fee', 1, 1 );
		
			add_filter( 'woocommerce_cart_item_price', 'phoen_dpad_filter_item_price', 10, 2 );
			
			add_filter( 'woocommerce_cart_item_subtotal', 'phoen_dpad_filter_subtotal_price' , 10, 2 );
			
			add_filter( 'woocommerce_checkout_item_subtotal', 'phoen_dpad_filter_subtotal_price' , 10, 2 ); 
			
			add_filter( 'woocommerce_order_formatted_line_subtotal', 'phoen_dpad_filter_subtotal_order_price' , 10, 3 );
			
			
		}

	} ?>
