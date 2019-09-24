<?php if ( ! defined( 'ABSPATH' ) ) exit;

	function phoen_dpad_custom_tab_options_tab_discounts() {

		?>

	    <li class="phoeniixx_dynamic_discount_custom_tab"><a class="phoen_xxx" href="#phoeniixx_discount_html_content_div_main"><?php _e('  Discounts', 'phoen-dpad'); ?></a></li>

		<?php

	}
	
	function phoen_dpad_custom_tab_options_discounts() {
   ?>
		
		<script>

			jQuery(document).ready(function(){
				
				jQuery('.product_data_tabs li a').click(function(){	
					if(jQuery(this).is(':not(.phoen_xxx)')){	
						jQuery('#phoeniixx_discount_html_content_div_main').css('display','none');
					}
				});
			
				var a = jQuery('#phoeniixx_discount_div').html();

					jQuery('.phoe_add_disc_more').click(function(){

						jQuery('.phoeniixx_discount_html_content_div').append(a);

					});

			});


			jQuery(document).on('click','.phoe_remove_disc_div',function(){

				jQuery(this).parent('div').remove();

			});


		</script>

<body>

	

	<div id="phoeniixx_discount_div" style="display:none;">
	
		<div class="phoeniixx_discount_min_max_div">

			<input type="number" placeholder="Min Quantity" name="min_val[]" min=0  class="min_val" value="">

			<input type="number"  placeholder="Max Quantity" name="max_val[]" min=0 class="max_val" value="">

			<input type="number" step='any' placeholder="Discount Value" name="discount[]" min=0 class="discount" value="">
			<select name="disc_type[]" >
				<option value="percentage" ><?php _e('Percentage','phoen-dpad'); ?></option>
				<option value="amount"><?php _e('Amount','phoen-dpad'); ?></option>
			</select>
			<button name="remove_b" class="phoe_remove_disc_div button">-</button>			
		</div>
		
	</div>

	<?php 
	global $product;
		
	global $post;

	$val= get_post_meta($post->ID,'phoen_woocommerce_discount_mode', true); 
	
	
	
	?>
	<div id="phoeniixx_discount_html_content_div_main" style="display:none;float:left;width:80%;" >
	<script>
		jQuery(document).on('ready', function(){
			
			jQuery('.phoen_never_min').on('change',function(){
							
				if (jQuery(this).is(':checked')) {
					jQuery('.phoen_expiry').css('display','none');
				}else{
					jQuery('.phoen_expiry').css('display','inline-block');
				}
				
			});
			
			jQuery('.phoen_expiry_min').datepicker({
					dateFormat : 'dd-mm-yy',
					minDate: 0
				});
			jQuery('.user_role').select2({theme: "classic"});	
			
		});	        
	</script>
		<div id="accordion1" class="phoen_main_accordian">
				
					<div class="phoen_user_roles" >
					
						<label><b><?php _e('Select role:','phoen-dpad'); ?></b></label>
								
						<select class="user_role" name="user_role[]" multiple="multiple" >
						
							<option value="administrator" <?php if(isset($val[0]['user_role']) && in_array('administrator', $val[0]['user_role'])) echo 'selected';?>><?php _e('Administrator','phoen-dpad'); ?></option>
							
							<option value="editor" <?php if(isset($val[0]['user_role']) && in_array('editor', $val[0]['user_role'])) echo 'selected';?>><?php _e('Editor','phoen-dpad'); ?></option>
							
							<option value="author" <?php if(isset($val[0]['user_role']) && in_array('author', $val[0]['user_role'])) echo 'selected';?>><?php _e('Author','phoen-dpad'); ?></option>
							
							<option value="contributor" <?php if(isset($val[0]['user_role']) && in_array('contributor', $val[0]['user_role'])) echo 'selected';?>><?php _e('Contributor','phoen-dpad'); ?></option>
							
							<option value="subscriber" <?php if(isset($val[0]['user_role']) && in_array('subscriber', $val[0]['user_role'])) echo 'selected';?>><?php _e('Subscriber','phoen-dpad'); ?></option>
							
							<option value="customer" <?php if(isset($val[0]['user_role']) && in_array('customer', $val[0]['user_role'])) echo 'selected';?>><?php _e('Customer','phoen-dpad'); ?></option>
							
							<option value="shop_manager" <?php if(isset($val[0]['user_role']) && in_array('shop_manager', $val[0]['user_role'])) echo 'selected';?>><?php _e('Shop Manager','phoen-dpad'); ?></option>
							
						</select>
					</div>
					
					<div class="phoen_never_expiry">
						<h2><b><?php _e('Discount period','phoen-dpad'); ?></b></h2>
						<label><?php _e('Never expire:','phoen-dpad'); ?></label>
						<input type="checkbox" class="phoen_never_min" name="never_expire"  <?php if(isset($val[0]['never_expire']) && $val[0]['never_expire']=='1') echo 'checked';?>  value="1"  />
					</div>
					<div class="phoen_expiry" <?php if(isset($val[0]['never_expire']) && $val[0]['never_expire']=='1') echo 'style=display:none;';?>>
						<label><b><?php _e('From:','phoen-dpad'); ?></b></label>
						<input type="text" class="phoen_expiry_min" name="from"  value="<?php if(isset($val[0]['from'])){ echo $val[0]['from']; } ?>" readonly />
						<label><b><?php _e('To:','phoen-dpad'); ?></b></label>
						<input type="text" class="phoen_expiry_min" name="to" value="<?php if(isset($val[0]['to'])){ echo $val[0]['to']; } ?>" readonly />
					</div>
				
					
					<div class="phoeniixx_discount_html_content_div">
					
						<div class="phoen_mindiv_label">
							<label><b><?php _e('Min quantity','phoen-dpad'); ?></b></label> 
							<label><b><?php _e('Max quantity','phoen-dpad'); ?></b></label> 
							<label><b><?php _e('Discount value','phoen-dpad'); ?></b></label>
							<label><b><?php _e('Discount type','phoen-dpad'); ?></b></label> 
						</div>
						<?php 
						if(isset($val[0]) && is_array($val)){
						//if(isset($val) && count($val)>0){
							for($i=0;$i<count($val);$i++)
					
							{							
								?>
								<div class="phoeniixx_discount_min_max_div" > 

									<input type="number" placeholder="Min Quantity" name="min_val[]" min=0 class="min_val" value="<?php echo isset($val[$i]['min_val'])?$val[$i]['min_val']:''; ?>" />

									<input type="number" placeholder="Max Quantity" name="max_val[]" min=0  class="max_val" value="<?php echo isset($val[$i]['max_val'])?$val[$i]['max_val']:''; ?>" />

									<input type="number" step='any' placeholder="Discount Value" name="discount[]" min=0 class="discount" value="<?php echo isset($val[$i]['discount'])?$val[$i]['discount']:''; ?>" />
									<select name="disc_type[]" >
							
										<option value="percentage" <?php if(isset($val[$i]['type']) && $val[$i]['type']=='percentage') echo 'selected';?>><?php _e('Percentage','phoen-dpad'); ?></option>
										
										<option value="amount" <?php if(isset($val[$i]['type']) && $val[$i]['type']=='amount') echo 'selected';?>><?php _e('Amount','phoen-dpad'); ?></option>
									
									</select>
									<button name="remove_b" class="phoe_remove_disc_div button">-</button>
								</div>
								<?php 
								
							}
						}else{
							?>
								<div class="phoeniixx_discount_min_max_div"> 

									<input type="number" placeholder="Min Quantity" name="min_val[]" min=0 class="min_val" value="" />

									<input type="number"  placeholder="Max Quantity" name="max_val[]" min=0 class="max_val" value="" />

									<input type="number" step='any' placeholder="Discount Value" name="discount[]" min=0 class="discount" value="" />
									<select name="disc_type[]" >
							
										<option value="percentage" ><?php _e('Percentage','phoen-dpad'); ?></option>
										
										<option value="amount"><?php _e('Amount','phoen-dpad'); ?></option>
									
									</select>
									<button name="remove_b" class="phoe_remove_disc_div button">-</button>
								</div>
								<?php 
						}
						
						 ?>
					</div>
				
					<input type="button" value="Add More" class="phoe_add_disc_more button button-primary button-large" />

			</div>
	</div>
	<?php	

	}
	function phoen_dpad_process_product_meta_custom_tab_discounts( $post_id ) {
				
		$discount_data = array();
		
		$min_val= $_POST['min_val'];

		$max_val= $_POST['max_val'];

		$discount= $_POST['discount'];
		
		$disc_type=$_POST['disc_type'];
		
		$from=$_POST['from'];
		
		$to=$_POST['to'];
		
		$user_role=$_POST['user_role'];
		
		$never_expire=$_POST['never_expire'];
	
		
		for($i=0;$i<COUNT($min_val);$i++)
		{
			
			 if( $min_val[$i] != ''){
				 
				$discount_data[] = array(
				 
					'min_val' =>	$min_val[$i],
						
					'max_val' =>	$max_val[$i],

					'discount' =>	$discount[$i],
					
					'type'=>$disc_type[$i],
					
					'from'=>$from,
					
					'to'=>$to,
					
					'user_role'=>$user_role,
					
					'never_expire'=>$never_expire
				);

			}
		}
		update_post_meta( $post_id, 'phoen_woocommerce_discount_mode', $discount_data );
		
	}
	
	add_action('woocommerce_process_product_meta', 'phoen_dpad_process_product_meta_custom_tab_discounts');
	
	add_action('woocommerce_product_data_panels', 'phoen_dpad_custom_tab_options_discounts');
	
	add_action('woocommerce_product_write_panel_tabs', 'phoen_dpad_custom_tab_options_tab_discounts'); 
	
?>