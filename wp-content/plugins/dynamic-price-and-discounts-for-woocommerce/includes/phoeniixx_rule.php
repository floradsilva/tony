<?php if ( ! defined( 'ABSPATH' ) ) exit;
	
if ( ! empty( $_POST ) && check_admin_referer( 'phoe_Discount_form_action', 'phoe_Discount_form_action_form_nonce_field' ) ) {


	$discount_data = array();
		
		$min_val= isset($_POST['min_val'])?$_POST['min_val']:'';
		
		$max_val= isset($_POST['max_val'])?$_POST['max_val']:'';

		$discount= isset($_POST['discount'])?$_POST['discount']:'';
		
		$disc_type= isset($_POST['disc_type'])?$_POST['disc_type']:'';
		
		$from= isset($_POST['from'])?$_POST['from']:'';
		
		$user_role=isset($_POST['user_role']) ? $_POST['user_role']:'';
		
		$to=isset($_POST['to']) ? $_POST['to']:'';
		
		$cat_list=isset($_POST['cat_list']) ? $_POST['cat_list']:'';
				
		$never_expire=isset($_POST['never_expire']) ? $_POST['never_expire']:'';
		
	 if(sanitize_text_field( $_POST['disc_submit'] ) == 'Save'){
		
		for($i=0;$i<COUNT($min_val);$i++)
		{
			if(isset($min_val[$i])){
			 if( $min_val[$i] != ''){
				 
				$discount_data[] = array(
				 
					'min_val' =>	$min_val[$i],
						
					'max_val' =>	$max_val[$i],

					'discount' =>	$discount[$i],
					
					'type'=>$disc_type[$i],
				);

			}
		}
	}
		
		$finel_array=array(
		'cat_list'=>$cat_list,
		'user_role'=>$user_role,
		'data'=>$discount_data,
		'from'=>$from,
		'to'=>$to,
		'never_expire'=>$never_expire
		);
		
		update_option('phoen_backend_array',$finel_array);
		
	} 
	
}

	$gen_settings = get_option('phoen_backend_array');
	
	$cat_list_min=$gen_settings['cat_list'];
	
	$plugin_dir_url = plugin_dir_url( __FILE__ );	
	
	$taxonomy     = 'product_cat';
			
			$orderby      = 'name';  
			
			$show_count   = 0;      // 1 for yes, 0 for no
			
			$pad_counts   = 0;      // 1 for yes, 0 for no
			
			$hierarchical = 1;      // 1 for yes, 0 for no  
			
			$title        = '';  
			
			$empty        = 0;

			$args = array(
			'taxonomy'     => $taxonomy,
			'orderby'      => $orderby,
			'show_count'   => $show_count,
			'pad_counts'   => $pad_counts,
			'hierarchical' => $hierarchical,
			'title_li'     => $title,
			'hide_empty'   => $empty
			);
			
			$all_categories = get_categories( $args );
			
			$phoen_main_catlist=array();
			
			foreach ($all_categories as $cat) {
				
				$term_id=$cat->term_id;	
				
				$phoen_main_catlist[$term_id]=$cat->name;  
				
			}
			
 ?>

	<div id="phoeniixx_phoe_Disc_wrap_profile-page" class=" phoeniixx_phoe_Disc_wrap_profile_div">
	<!--<h3 color="red">Switch to the premium version of Dynamic pricing and discount for more features.</h3>-->
					

			<h2 class="phoen_title_head"><?php _e('Category rules','phoen-dpad'); ?> </h2>
	
		<form method="post" id="phoeniixx_phoe_Disc_wrap_profile_form" action="" >
		
			<?php wp_nonce_field( 'phoe_Discount_form_action', 'phoe_Discount_form_action_form_nonce_field' ); ?>
	
			<script>

				jQuery(document).ready(function(){
				
					var a = jQuery('#phoeniixx_discount_div').html();

						jQuery('.phoe_add_disc_more').click(function(){

							jQuery('.phoeniixx_discount_html_content_div').append(a);

						});
						
						jQuery('.phoen_never_min').on('change',function(){
							
							if (jQuery(this).is(':checked')) {
								jQuery('.phoen_expiry').css('display','none');
							}else{
								jQuery('.phoen_expiry').css('display','inline-block');
							}
							
						});
						

				});


				jQuery(document).on('click','.phoe_remove_disc_div',function(){

					jQuery(this).parent('div').remove();

				});

			</script>
			<?php 
			$cat_list_min=$gen_settings['cat_list'];
			$val_data=$gen_settings['data'];
			
			?>
			<script>
				jQuery(document).on('ready', function(){
					
					jQuery('.phoen_expiry_min').datepicker({
							dateFormat : 'dd-mm-yy',
							minDate: 0
						});
					jQuery('.discount_conditions').select2({theme: "classic"});	
					jQuery('.user_role').select2({theme: "classic"});	
					
				});	        
			</script>
		
			<div class="phoen_user_roles" >
			
				<div class="phoen_category">	
					<label><?php _e('Category list','phoen-dpad'); ?> </label>
					
					<select class="discount_conditions" name="cat_list[]" multiple="multiple"  >
						
						<?php //$phoen_product_cat_list= get_option('phoen_product_cat_list');
						
						//if(!empty($cat_list[0]) && is_array($cat_list) ){
							
							foreach($phoen_main_catlist as $keyq=>$val){
								?>
								<option value="<?php echo $keyq;?>" <?php if(is_array($cat_list_min) && in_array($keyq, $cat_list_min)){echo 'selected';} ?>><?php echo $val;?></option>
								<?php
							}
							
						//}
						?>
					</select>
					
				</div>	
				
				<div class="phoen_role">	
					<label><?php _e('Select Role','phoen-dpad'); ?></label>
							
					<select class="user_role" name="user_role[]" multiple="multiple" >
					
						<option value="administrator" <?php if(!empty($gen_settings['user_role']) && in_array('administrator', $gen_settings['user_role'])) echo 'selected';?>><?php _e('Administrator','phoen-dpad'); ?></option>
						
						<option value="editor" <?php if(!empty($gen_settings['user_role']) && in_array('editor', $gen_settings['user_role'])) echo 'selected';?>><?php _e('Editor','phoen-dpad'); ?></option>
						
						<option value="author" <?php if(!empty($gen_settings['user_role']) &&  in_array('author', $gen_settings['user_role'])) echo 'selected';?>><?php _e('Author','phoen-dpad'); ?></option>
						
						<option value="contributor" <?php if(!empty($gen_settings['user_role']) && in_array('contributor', $gen_settings['user_role'])) echo 'selected';?>><?php _e('Contributor','phoen-dpad'); ?></option>
						
						<option value="subscriber" <?php if(!empty($gen_settings['user_role']) && in_array('subscriber', $gen_settings['user_role'])) echo 'selected';?>><?php _e('Subscriber','phoen-dpad'); ?></option>
						
						<option value="customer" <?php if(!empty($gen_settings['user_role']) && in_array('customer', $gen_settings['user_role'])) echo 'selected';?>><?php _e('Customer','phoen-dpad'); ?></option>
						
						<option value="shop_manager" <?php if(!empty($gen_settings['user_role']) && in_array('shop_manager', $gen_settings['user_role'])) echo 'selected';?>><?php _e('Shop Manager','phoen-dpad'); ?></option>
						
					</select>
				</div>	
			</div>
			<div class="phoen_never_expiry">
				<h4><?php _e('Discount Period','phoen-dpad'); ?></h4>
				<label><?php _e('Never Expire','phoen-dpad'); ?></label>
				<input type="checkbox" class="phoen_never_min" name="never_expire"  <?php if($gen_settings['never_expire']=='1') echo 'checked';?>  value="1"  />
			</div>
			<div class="phoen_expiry" <?php if($gen_settings['never_expire']=='1') echo 'style=display:none;';?>>
				<label><?php _e('From:','phoen-dpad'); ?></label>
				<input type="text" class="phoen_expiry_min" name="from"  value="<?php echo isset($gen_settings['from'])?$gen_settings['from']:''; ?>" readonly />
				<label><?php _e('To:','phoen-dpad'); ?></label>
				<input type="text" class="phoen_expiry_min" name="to" value="<?php echo isset($gen_settings['to'])?$gen_settings['to']:''; ?>" readonly />
			</div>
		
		
			<div class="phoeniixx_discount_html_content_div">
				<div class="phoen_mindiv_label">
					<label><?php _e('Min Quantity','phoen-dpad'); ?> </label>
					<label><?php _e('Max Quantity','phoen-dpad'); ?> </label>
					<label><?php _e('Discount Value','phoen-dpad'); ?> </label>
					<label><?php _e('Discount Type','phoen-dpad'); ?> </label>
				</div>
					<?php 	
					if(isset($val_data[0]) && is_array($val_data)){
						for($i=0;$i<count($val_data);$i++)
						{							
							?>
							<div class="phoeniixx_discount_min_max_div"> 

								<input type="number" placeholder="Min Quantity" name="min_val[]" min=0 class="min_val" value="<?php echo isset($val_data[$i]['min_val'])?$val_data[$i]['min_val']:''; ?>" />

								<input type="number"  placeholder="Max Quantity" name="max_val[]" min=0 class="max_val" value="<?php echo isset($val_data[$i]['max_val'])?$val_data[$i]['max_val']:''; ?>" />

								<input type="number" step='any' placeholder="Discount Value" name="discount[]" min=0 class="discount" value="<?php echo isset($val_data[$i]['discount'])?$val_data[$i]['discount']:''; ?>" />
								<select name="disc_type[]" >
						
									<option value="percentage"  <?php if($val_data[$i]['type']=='percentage') echo 'selected';?>><?php _e('Percentage','phoen-dpad'); ?></option>
									
									<option value="amount"  <?php if($val_data[$i]['type']=='amount') echo 'selected';?>><?php _e('Amount','phoen-dpad'); ?></option>
								
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

								<input type="number" step='any' placeholder="Discount Value" name="discount[]" class="discount" value="" />
								<select name="disc_type[]" >
						
									<option value="percentage" ><?php _e('Percentage','phoen-dpad'); ?></option>
									
									<option value="amount" ><?php _e('Amount','phoen-dpad'); ?></option>
								
								</select>
								<button name="remove_b" class="phoe_remove_disc_div button">-</button>
							</div>
							<?php 
						
					}
					 ?>

			</div>
		
			<input type="button" value="Add More" class="phoe_add_disc_more button button-primary" />
			<input type="submit" value="Save" name="disc_submit" id="submit" class="button button-primary" />
			
		</form>
		
	</div>
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