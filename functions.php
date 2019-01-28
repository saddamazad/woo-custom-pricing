<?php



function load_custom_core_options() {



    if ( ! function_exists( 'et_load_core_options' ) ) {



        function et_load_core_options() {



            $options = require_once( get_stylesheet_directory() . esc_attr( '/custom_options_divi.php' ) );



        }



    }



}



add_action( 'after_setup_theme', 'load_custom_core_options' );







// Move product tabs 



remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );



add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 60 );



// Remove product description tabs 



add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );



function woo_remove_product_tabs( $tabs ) {



    unset( $tabs['description'] );      	// Remove the description tab



    return $tabs;



}



// Rename the additional information tab



add_filter( 'woocommerce_product_tabs', 'woo_rename_tabs', 98 );



function woo_rename_tabs( $tabs ) {



	global $product;

	

	if( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) { // Check if product has attributes, dimensions or weight

		$tabs['additional_information']['title'] = __( 'Product Details' );	// Rename the additional information tab

	}

 

	return $tabs;

 

}


function get_products_by_category($atts, $content = null) {
	extract(shortcode_atts(array(
		'cat_slug' => '',
		'product_per_page' => 12,
		'columns' => 3
	), $atts));

	if(is_front_page()) {
		$paged = (get_query_var('page')) ? get_query_var('page') : 1;
	} else {
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	}
	$args = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'paged' => $paged,
				'posts_per_page' => $product_per_page,
				'order' => 'DESC',
				'orderby' => 'date',
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $cat_slug,
					),
				),
			);
	$productArgs = new WP_Query( $args );

	ob_start();
	echo '<div class="et_pb_shop woocommerce columns-'.$columns.'">';
	//echo '<ul class="products">';

	global $post, $woocommerce;
	$counter = 1;
	while ( $productArgs->have_posts() ) : $productArgs->the_post();
		if($counter == $columns) $last = " last";
		else $last = "";
		if($counter == 1) echo '<ul class="products">';
?>
    <li class="product post-<?php echo $post->ID.' '.$last; ?>">
        <a href="<?php echo get_permalink(); ?>" class="woocommerce-LoopProduct-link">
			<?php
				if(has_post_thumbnail( $post->ID )) {
					$prod_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) , 'medium' );
			?>
        	<span class="et_shop_image">
            	<img src="<?php echo $prod_image[0]; ?>" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="<?php the_title(); ?>" title="<?php the_title(); ?>" width="<?php echo $prod_image[1]; ?>" height="<?php echo $prod_image[2]; ?>">
                <span class="et_overlay"></span>
            </span>
            <?php } ?>
            <h3><?php the_title(); ?></h3>
        	<span class="price">
            	<?php $woo_product = wc_get_product( $post->ID ); ?>
            	<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo get_woocommerce_currency_symbol(); ?></span><?php echo $woo_product->get_price(); ?></span>
            </span>
    	</a>
    </li>
<?php
	if($counter == $columns) {
		//echo '<div class="clearfix"></div>';
		echo '</ul>';
		$counter = 0;
	}
	$counter++;

	endwhile;
	wp_reset_postdata();
	
	//echo '</ul>';
	echo '</div>';
	//echo get_caluco_pagination($productArgs->max_num_pages, $range = 2);
	if (function_exists('wp_pagenavi')){
		wp_pagenavi(array( 'query' => $productArgs ));
	}
	$content = ob_get_clean();
	return $content;
}

add_shortcode("products_by_category", "get_products_by_category");

function get_caluco_pagination($pages = '', $range = 2) {
	$output = '';	
	 $showitems = ($range * 2)+1;	
	 global $paged;
	 if(empty($paged)) $paged = 1;	
	 if($pages == '')
	 {
		 global $wp_query;
		 $pages = $wp_query->max_num_pages;
		 if(!$pages)
		 {
			 $pages = 1;
		 }
	 }	
	 if(1 != $pages)
	 {
		 $output .= "<div class='pagination loop-pagination clearfix'>";
		 if($paged > 1) $output .= "<a class='prev page-numbers' href='".get_pagenum_link($paged - 1)."'><span class='page-prev'></span>".__('Previous', 'RMTheme')."</a>";

		 for ($i=1; $i <= $pages; $i++)
		 {
			 if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
			 {
				 $output .= ($paged == $i)? "<span class='page-numbers current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
			 }
		 }	
		 if ($paged < $pages) $output .= "<a class='next page-numbers' href='".get_pagenum_link($paged + 1)."'>".__('Next', 'RMTheme')."<span class='page-next'></span></a>";
		 $output .= "</div>\n";
	 }
	 
	 return $output;
}

function get_prod_sub_categories($atts, $content = null) {
	extract(shortcode_atts(array(
		'parent_term_id' => '',
		'term_desc_title' => '',
		'desc_term_id' => ''
	), $atts));
	
	$taxonomy_name = 'product_cat';
	$parentTerm = get_term( $parent_term_id, $taxonomy_name );
	ob_start();
	if($parentTerm) {
		echo '<h4 class="term-title widgettitle">'.$parentTerm->name.'</h4>';
	}

	$termchildren = get_term_children( $parent_term_id, $taxonomy_name );
	
	echo '<div class="term-sub-categories">';
	foreach ( $termchildren as $child ) {
		$term = get_term_by( 'id', $child, $taxonomy_name );
		echo '<a href="' . get_term_link( $child, $taxonomy_name ) . '">' . $term->name . '</a><br>';
	}
	echo '</div>';
	
	//$description = term_description($term_id, $taxonomy_name);
	$descTerm = get_term( $desc_term_id, $taxonomy_name );
	if($descTerm->description) {
		echo '<div class="term-desc-wrap cat-left-text" style="background-color: #f5f5f5; margin-top: 30px;">';
		if(!empty($term_desc_title)) {
			echo '<h4 class="term-title">'.$term_desc_title.'</h4>';
		} else {
			if($descTerm) {
				echo '<h4 class="term-title">'.$descTerm->name.'</h4>';
			}
		}
		echo '<div class="cat-left-border"></div>';
		echo '<div class="term-desc">'.wpautop(wptexturize($descTerm->description)).'</div>';
		echo '</div>';
	}
	
	$content = ob_get_clean();
	return $content;
}
add_shortcode("get_subcats", "get_prod_sub_categories");

function redirect_pending_users() {
	$user_id = get_current_user_id();
	// if we are in the my-account page then redirect the user
	if( strpos( $_SERVER['REQUEST_URI'], '/my-account' ) !== false ) {
		if( get_user_meta( $user_id, 'pw_user_status', true ) == 'pending' || get_user_meta( $user_id, 'pw_user_status', true ) == 'denied') {
			wp_logout();
		    wp_redirect( home_url('/registration-success/') );
			exit;
		}
	}
}
add_action('init', 'redirect_pending_users');

function get_products_by_tag_category($atts, $content = null) {
	extract(shortcode_atts(array(
		'cat_slug' => '',
		'tag_slug' => '',
		'product_per_page' => 12,
		'columns' => 3
	), $atts));

	if(is_front_page()) {
		$paged = (get_query_var('page')) ? get_query_var('page') : 1;
	} else {
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	}
	$args = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'paged' => $paged,
				'posts_per_page' => $product_per_page,
				'order' => 'DESC',
				'orderby' => 'date',
				'tax_query' => array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $cat_slug,
					),
					array(
						'taxonomy' => 'product_tag',
						'field' => 'slug',
						'terms' => array( $tag_slug )
					)
				),
			);
	$productArgs = new WP_Query( $args );

	ob_start();
	echo '<div class="et_pb_shop woocommerce columns-'.$columns.'">';
	echo '<ul class="products">';

	global $post, $woocommerce;
	$counter = 1;
	while ( $productArgs->have_posts() ) : $productArgs->the_post();
		if($counter == $columns) $last = " last";
		else $last = "";
?>
    <li class="product post-<?php echo $post->ID.' '.$last; ?>">
        <a href="<?php echo get_permalink(); ?>" class="woocommerce-LoopProduct-link">
			<?php
				if(has_post_thumbnail( $post->ID )) {
					$prod_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) , 'medium' );
			?>
        	<span class="et_shop_image">
            	<img src="<?php echo $prod_image[0]; ?>" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="<?php the_title(); ?>" title="<?php the_title(); ?>" width="<?php echo $prod_image[1]; ?>" height="<?php echo $prod_image[2]; ?>">
                <span class="et_overlay"></span>
            </span>
            <?php } ?>
            <h3><?php the_title(); ?></h3>
        	<span class="price">
            	<?php $woo_product = wc_get_product( $post->ID ); ?>
            	<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo get_woocommerce_currency_symbol(); ?></span><?php echo $woo_product->get_price(); ?></span>
            </span>
    	</a>
    </li>
<?php
	if($counter == $columns) {
		//echo '<div class="clearfix"></div>';
		$counter = 0;
	}
	$counter++;

	endwhile;
	wp_reset_postdata();
	
	echo '</ul>';
	echo '</div>';
	//echo get_caluco_pagination($productArgs->max_num_pages, $range = 2);
	if (function_exists('wp_pagenavi')){
		wp_pagenavi(array( 'query' => $productArgs ));
	}
	$content = ob_get_clean();
	return $content;
}

add_shortcode("products_by_tag_category", "get_products_by_tag_category");


/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */
if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

add_action( 'cmb2_admin_init', 'caluco_register_pdf_metabox' );
/**
 * Hook in and add metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
 */
function caluco_register_pdf_metabox() {
	$prefix = '_cmb_';
	/**
	 * Sample metabox to demonstrate each field type included
	 */
	$cmb_ins = new_cmb2_box( array(
		'id'            => $prefix . 'pdf_docs',
		'title'         => esc_html__( 'Product Specs', 'cmb2' ),
		'object_types'  => array( 'product' ), // Post type
	) );
	$cmb_ins->add_field( array(
		'name'       => esc_html__( 'PDF Files', 'cmb2' ),
		'desc'       => esc_html__( 'Upload or add multiple files.', 'cmb2' ),
		'id'         => $prefix . 'product_pdf_doc',
		'type'         => 'file_list',
		'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
	) );
	$cmb_ins->add_field( array(
		'name'             => esc_html__( 'Enable Product Customization Feature', 'cmb2' ),
		'desc'             => esc_html__( '', 'cmb2' ),
		'id'               => $prefix . 'enable_product_customization',
		'type'             => 'select',
		//'show_option_none' => true,
		'options'          => array(
			'No' => esc_html__( 'No', 'cmb2' ),
			'Yes'   => esc_html__( 'Yes', 'cmb2' ),
		),
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Grade A Price', 'cmb2' ),
		'desc' => esc_html__( 'Set Grade A Price', 'cmb2' ),
		'id'   => $prefix . 'grade_a_reg_price',
		'type' => 'text_money',
		// 'before_field' => '£', // override '$' symbol if needed
		// 'repeatable' => true,
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Grade A Wholesale Price', 'cmb2' ),
		'desc' => esc_html__( 'Set Grade A Wholesale Price', 'cmb2' ),
		'id'   => $prefix . 'grade_a_wholesale_price',
		'type' => 'text_money',
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Grade B Price', 'cmb2' ),
		'desc' => esc_html__( 'Set Grade B Price', 'cmb2' ),
		'id'   => $prefix . 'grade_b_reg_price',
		'type' => 'text_money',
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Grade B Wholesale Price', 'cmb2' ),
		'desc' => esc_html__( 'Set Grade B Wholesale Price', 'cmb2' ),
		'id'   => $prefix . 'grade_b_wholesale_price',
		'type' => 'text_money',
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Grade C Price', 'cmb2' ),
		'desc' => esc_html__( 'Set Grade C Price', 'cmb2' ),
		'id'   => $prefix . 'grade_c_reg_price',
		'type' => 'text_money',
	) );
	$cmb_ins->add_field( array(
		'name' => esc_html__( 'Grade C Wholesale Price', 'cmb2' ),
		'desc' => esc_html__( 'Set Grade C Wholesale Price', 'cmb2' ),
		'id'   => $prefix . 'grade_c_wholesale_price',
		'type' => 'text_money',
	) );
}

function show_pdf_download_option() {
	global $post;
	if(get_post_meta($post->ID, '_cmb_product_pdf_doc', true)) {
		$pdf_arr = get_post_meta($post->ID, '_cmb_product_pdf_doc', true);
		//print_r($pdf_arr);
		echo '<div class="product-pdf-doc" style="margin-bottom: 30px;">
				<h3>Resource Library</h3>';
		foreach($pdf_arr as $pdf) {
			$file_name_arr = explode(".", end(explode("/", $pdf)));
			$file_name = ucwords(str_replace("-", " ", $file_name_arr[0]));
			echo '<a href="'.$pdf.'" target="_blank">'.$file_name.' <img src="'.get_stylesheet_directory_uri().'/bullet-pdf.gif" alt="" /></a>';
		}
		echo '</div>';
	}
}
add_action('woocommerce_single_product_summary', 'show_pdf_download_option', 35);

function hook_product_customizations() {
	global $post;
	if(get_post_meta($post->ID, '_cmb_enable_product_customization', true) == "Yes") {
?>
	<div class="product_customization_wrap">
    	<div class="pc-contain">
            <h3>Customize Your Product</h3>
            <p class="fabrics">
                <label>Select Your Grade of Fabric</label>
                <select id="fabrics_list">
                	<option value="">Select Grade</option>
                    <?php
                        $grade_of_fabrics = explode(",", get_option('grade_of_fabric'));
                        foreach($grade_of_fabrics as $grade_of_fabric) {
							$wlsl_pos = strpos($grade_of_fabric, "||");
							$user_id = get_current_user_id();
							
							$grade_price = explode("$", $grade_of_fabric);
							$option_hint = explode(" ", trim(strtolower($grade_price[0])));
							
							if((get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_reg_price', true) != "") || (get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_wholesale_price', true) != "")) {
								if($user_id != 0 && current_user_can( 'wholesale_customer' ) && (get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_wholesale_price', true) != "")) {
									$custom_price = get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_wholesale_price', true);
									$grade_name = trim($grade_price[0]).' $'.trim($custom_price);
								} else {
									$custom_price = get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_reg_price', true);
									$grade_name = trim($grade_price[0]).' $'.trim($custom_price);
								}
							} else {
								/*if($wlsl_pos === false) {
									$grade_name = $grade_of_fabric;
								} else {
									if($user_id != 0 && current_user_can( 'wholesale_customer' )) {
										$grade_name_price = explode("$", $grade_of_fabric);
										$rw_prices = explode("||", end($grade_name_price));
										// $rw_prices[0] = regular price
										// $rw_prices[1] = wholesale price
										$grade_name = $grade_name_price[0].' $'.trim($rw_prices[1]);
									} else {
										$grade_name_price = explode("||", $grade_of_fabric);
										$grade_name = $grade_name_price[0];
									}
								}*/
								$dollar_exist = substr(trim($grade_of_fabric), -1);
								// if last character is $ no need to show it
								if($dollar_exist == '$') {
									$grade_of_fabric = trim($grade_of_fabric, '$');
								}
								$grade_name = $grade_of_fabric;
							}
							
							$prices = explode("||", end($grade_price));
							if((get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_reg_price', true) != "") || (get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_wholesale_price', true) != "")) {
								if($user_id != 0 && current_user_can( 'wholesale_customer' ) && (get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_wholesale_price', true) != "")) {
									$price = get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_wholesale_price', true);
								} else {
									$price = get_post_meta($post->ID, '_cmb_grade_'.end($option_hint).'_reg_price', true);
								}
							} else {
								/*if ($user_id == 0) {
									// regular price
									$price = $prices[0];
								} else {
									if(current_user_can( 'wholesale_customer' )) {
										if(isset($prices[1])) {
											// wholesale price
											$price = $prices[1];
										} else {
											// regular price
											$price = $prices[0];
										}
									} else {
										// regular price
										$price = $prices[0];
									}
								}*/
								$price = 0;
							}
                            echo '<option value="'.trim($grade_price[0]).'" data-price="'.trim($price).'">'.trim($grade_name).'</option>';
                        }
                    ?>
                </select>
            </p>
            <p class="pattern">
                <label>Select Your Pattern</label>
                <select id="pattern_list">
                	<option value="">Select Pattern</option>
                    <?php
                        /*$caluco_patterns = explode(",", get_option('caluco_pattern'));
                        foreach($caluco_patterns as $pattern) {
                            echo '<option value="'.$pattern.'">'.$pattern.'</option>';
                        }*/
                    ?>
                </select>
            </p>
            <p class="foam-type">
                <label>Select Foam Type</label>
                <select id="foam_type">
                	<option value="">Select Foam</option>
                    <?php
                        $caluco_foams = explode(",", get_option('caluco_foam'));
                        foreach($caluco_foams as $foam) {
							//$price = explode("$", $foam);
							$wlsl_pos = strpos($foam, "||");
							$user_id = get_current_user_id();
							if($wlsl_pos === false) {
								$foam_name = $foam;
							} else {
								if($user_id != 0 && current_user_can( 'wholesale_customer' )) {
									$foam_name_price = explode("$", $foam);
									$rw_prices = explode("||", end($foam_name_price));
									// $rw_prices[0] = regular price
									// $rw_prices[1] = wholesale price
									$foam_name = $foam_name_price[0].' $'.trim($rw_prices[1]);
								} else {
									$foam_name_price = explode("||", $foam);
									$foam_name = $foam_name_price[0];
								}
							}
							
							$foam_price = explode("$", $foam);
							$prices = explode("||", end($foam_price));
							if ($user_id == 0) {
								// regular price
								$price = $prices[0];
							} else {
								if(current_user_can( 'wholesale_customer' )) {
									if(isset($prices[1])) {
										// wholesale price
										$price = $prices[1];
									} else {
										// regular price
										$price = $prices[0];
									}
								} else {
									// regular price
									$price = $prices[0];
								}
							}
                            echo '<option value="'.trim($foam_name).'" data-price="'.trim($price).'">'.trim($foam_name).'</option>';
                        }
                    ?>
                </select>
                <span><a href="<?php echo home_url('/foam-types/'); ?>" target="_blank">View</a> Foam Types</span>
            </p>
        </div>
    </div>
<?php
	}
}
add_action('woocommerce_single_product_summary', 'hook_product_customizations', 25);

function theme_custom_scripts() {
	?>
    <script type="text/javascript">
		jQuery( document ).ready(function() {
			if(jQuery(".wholesale_price_container").length) {
				var wholesale_text_price = jQuery(".wholesale_price_container .amount").text();
				var wholesale_price = parseInt(wholesale_text_price.substring(1));
				jQuery(".wholesale_price_container").attr("data-price", wholesale_price);
			}
			
			jQuery( "#fabrics_list" ).on( "change", function() {
				var search_this = jQuery(this).val();
				var site_url = jQuery(".logo_container > a").attr("href");
				//var grade = search_this.split("$");
				var grade = search_this.replace(" ", "-");
				var fab_grade = grade.toLowerCase();
				jQuery(".fabrics span").remove();
				//jQuery(".fabrics").append('<span><a href="'+site_url+fab_grade+'-fabrics">View</a> '+search_this+' Fabrics</span>');
				jQuery(".fabrics").append('<span><a href="'+site_url+'textiles-'+fab_grade+'" target="_blank">View</a> '+search_this+' Fabrics</span>');
				jQuery(".pattern span").remove();
				//jQuery(".pattern").append('<span><a href="'+site_url+fab_grade+'-patterns">View</a> '+search_this+' Patterns</span>');
				jQuery(".pattern").append('<span><a href="'+site_url+'textiles-'+fab_grade+'" target="_blank">View</a> '+search_this+' Patterns</span>');
				jQuery.ajax({
					type : "post",
					dataType: "json",
					url : et_pb_custom.ajaxurl,
					data : {action : 'get_patterns', fabric_grade: search_this},
					beforeSend: function() {
						//jQuery(".clinics_container").html('Loading...').show();
						jQuery("#pattern_list").attr("disabled", "disabled");
					}, 
					success: function(data) {
						jQuery("#pattern_list").removeAttr("disabled");
						jQuery("#pattern_list").empty();
						jQuery("#pattern_list").append('<option value="">Select Pattern</option>'+data.options);
					}
				});

				
				// if product price is hidden, then we should not trigger this feature
				if(jQuery('.summary meta[itemprop=price]').length) {
					if(jQuery(".wholesale_price_container").length) {
						var product_price = jQuery(".wholesale_price_container").attr("data-price");
					} else {
						var product_price = jQuery('.summary meta[itemprop=price]').attr("content");
					}
					var fabric_price = jQuery( "#fabrics_list option:selected" ).attr("data-price");
					
					var foam_type = jQuery("#foam_type").val();
					if(fabric_price) {
						if(foam_type != '') {
							var foam_price = jQuery( "#foam_type option:selected" ).attr("data-price");
							var new_price = parseInt(product_price) + parseInt(fabric_price) + parseInt(foam_price);
							if(jQuery(".wholesale_price_container").length) {
								jQuery(".summary .price .wholesale_price_container .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							} else {
								jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							}
						} else {
							var new_price = parseInt(product_price) + parseInt(fabric_price);
							if(jQuery(".wholesale_price_container").length) {
								jQuery(".summary .price .wholesale_price_container .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							} else {
								jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							}
						}
					} else {
						if(foam_type != '') {
							var foam_price = jQuery( "#foam_type option:selected" ).attr("data-price");
							var new_price = parseInt(product_price) + 0 + parseInt(foam_price);
							if(jQuery(".wholesale_price_container").length) {
								jQuery(".summary .price .wholesale_price_container .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							} else {
								jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							}
						}
					}

					if(jQuery("#foam_type").val() != '' || jQuery("#fabrics_list").val() != '') {
						jQuery("#custom_price").remove();
						jQuery( '<input type="hidden" name="custom_price" id="custom_price" value="'+new_price+'" />' ).insertBefore( ".single_add_to_cart_button" );
						
						if(jQuery("#foam_type").val() != '') {
							jQuery("#pc_foam_type").remove();
							jQuery( '<input type="hidden" name="pc_foam_type" id="pc_foam_type" value="'+jQuery("#foam_type").val()+'" />' ).insertBefore( ".single_add_to_cart_button" );
						} else {
							jQuery("#pc_foam_type").remove();
						}

						if(jQuery("#fabrics_list").val() != '') {
							// pattern should be removed on Grade change
							jQuery("#pc_pattern").remove();
							/*jQuery( '<input type="hidden" name="pc_pattern" id="pc_pattern" value="'+jQuery("#pattern_list").val()+'" />' ).insertBefore( ".single_add_to_cart_button" );
						} else {
							jQuery("#pc_pattern").remove();*/
						}
					} else {
						jQuery("#custom_price").remove();
						jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+product_price);
						jQuery("#pc_foam_type").remove();
						jQuery("#pc_grade").remove();
					}
				}


				if(jQuery(this).val() != '') {
					jQuery("#pc_grade").remove();
					jQuery( '<input type="hidden" name="pc_grade" id="pc_grade" value="'+search_this+'" />' ).insertBefore( ".single_add_to_cart_button" );
				} else {
					jQuery("#pc_grade").remove();
					jQuery(".fabrics span").remove();
					jQuery(".pattern span").remove();
					jQuery("#pc_pattern").remove();
				}
			});

			jQuery( "#pattern_list" ).on( "change", function() {
				// if product price is hidden, then we should not trigger this feature
				if(jQuery('.summary meta[itemprop=price]').length) {
					/*var product_price = jQuery('.summary meta[itemprop=price]').attr("content");
					var pattern_price = jQuery( "#pattern_list option:selected" ).attr("data-price");
					
					var foam_type = jQuery("#foam_type").val();
					if(pattern_price) {
						if(foam_type != '') {
							var foam_price = jQuery( "#foam_type option:selected" ).attr("data-price");
							var new_price = parseInt(product_price) + parseInt(pattern_price) + parseInt(foam_price);
							jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
						} else {
							var new_price = parseInt(product_price) + parseInt(pattern_price);
							jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
						}
					} else {
						if(foam_type != '') {
							var foam_price = jQuery( "#foam_type option:selected" ).attr("data-price");
							var new_price = parseInt(product_price) + 0 + parseInt(foam_price);
							jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
						}
					}*/

					if(jQuery("#pattern_list").val() != '') {
						jQuery("#pc_pattern").remove();
						jQuery( '<input type="hidden" name="pc_pattern" id="pc_pattern" value="'+jQuery("#pattern_list").val()+'" />' ).insertBefore( ".single_add_to_cart_button" );
					} else {
						jQuery("#pc_pattern").remove();
					}
				}
			});
			jQuery( "#foam_type" ).on( "change", function() {
				// if product price is hidden, then we should not trigger this feature
				if(jQuery('.summary meta[itemprop=price]').length) {
					if(jQuery(".wholesale_price_container").length) {
						var product_price = jQuery(".wholesale_price_container").attr("data-price");
					} else {
						var product_price = jQuery('.summary meta[itemprop=price]').attr("content");
					}
					var foam_price = jQuery( "#foam_type option:selected" ).attr("data-price");
					
					/*if(jQuery("#custom_price").length) {
						var custom_price = jQuery("#custom_price").val();
					} else {
						var custom_price = 0;
					}*/
					
					var fabrics_list = jQuery("#fabrics_list").val();
					if(foam_price) {
						if(fabrics_list != '') {
							var fabric_price = jQuery( "#fabrics_list option:selected" ).attr("data-price");
							var new_price = parseInt(product_price) + parseInt(fabric_price) + parseInt(foam_price);
							if(jQuery(".wholesale_price_container").length) {
								jQuery(".summary .price .wholesale_price_container .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							} else {
								jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							}
						} else {
							var new_price = parseInt(product_price) + parseInt(foam_price);
							if(jQuery(".wholesale_price_container").length) {
								jQuery(".summary .price .wholesale_price_container .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							} else {
								jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							}
						}
					} else {
						if(fabrics_list != '') {
							var fabric_price = jQuery( "#fabrics_list option:selected" ).attr("data-price");
							var new_price = parseInt(product_price) + 0 + parseInt(fabric_price);
							if(jQuery(".wholesale_price_container").length) {
								jQuery(".summary .price .wholesale_price_container .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							} else {
								jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+new_price);
							}
						}
					}

					if(jQuery("#foam_type").val() != '' || jQuery("#fabrics_list").val() != '') {
						jQuery("#custom_price").remove();
						jQuery( '<input type="hidden" name="custom_price" id="custom_price" value="'+new_price+'" />' ).insertBefore( ".single_add_to_cart_button" );

						if(jQuery("#foam_type").val() != '') {
							jQuery("#pc_foam_type").remove();
							jQuery( '<input type="hidden" name="pc_foam_type" id="pc_foam_type" value="'+jQuery("#foam_type").val()+'" />' ).insertBefore( ".single_add_to_cart_button" );
						} else {
							jQuery("#pc_foam_type").remove();
						}

						if(jQuery("#fabrics_list").val() != '') {
							jQuery("#pc_grade").remove();
							jQuery( '<input type="hidden" name="pc_grade" id="pc_grade" value="'+jQuery("#fabrics_list").val()+'" />' ).insertBefore( ".single_add_to_cart_button" );
						} else {
							jQuery("#pc_grade").remove();
						}
					} else {
						jQuery("#custom_price").remove();
						jQuery(".summary .price .amount").html('<span class="woocommerce-Price-currencySymbol">$</span>'+product_price);
						jQuery("#pc_foam_type").remove();
						jQuery("#pc_grade").remove();
					}
				}
			});
		});
	</script>
    <?php
}
add_action('wp_head', 'theme_custom_scripts');

// This captures additional posted information
add_filter('woocommerce_add_cart_item_data', 'caluco_add_item_data', 1, 3);
function caluco_add_item_data($cart_item_data, $product_id, $variation_id) {

    global $woocommerce;
    /*$new_value = array();
    $new_value['_custom_options'] = $_POST['custom_options'];*/

	if(isset($_POST['custom_price'])) {
    	$cart_item_data['_custom_price'] = stripslashes($_POST['custom_price']);
	}

	if(isset($_POST['pc_foam_type'])) {
    	$cart_item_data['_foam_type'] = stripslashes($_POST['pc_foam_type']);
	}

	if(isset($_POST['pc_pattern'])) {
    	$cart_item_data['_pattern'] = stripslashes($_POST['pc_pattern']);
	}
	
	if(isset($_POST['pc_grade'])) {
    	$cart_item_data['_grade'] = stripslashes($_POST['pc_grade']);
	}

    return $cart_item_data;

    /*if(empty($cart_item_data)) {
        return $new_value;
    } else {
        return array_merge($cart_item_data, $new_value);
    }*/
}
//This captures the information from the previous function and attaches it to the item.
add_filter('woocommerce_get_cart_item_from_session', 'caluco_get_cart_items_from_session', 1, 3 );
function caluco_get_cart_items_from_session($cart_item_data, $cart_item_session_data, $key) {

    /*if (array_key_exists( '_custom_options', $values ) ) {
        $item['_custom_options'] = $values['_custom_options'];
    }

    return $item;*/

    if ( isset( $cart_item_session_data['_custom_price'] ) ) {
        $cart_item_data['_custom_price'] = $cart_item_session_data['_custom_price'];
    }
    if ( isset( $cart_item_session_data['_foam_type'] ) ) {
        $cart_item_data['_foam_type'] = $cart_item_session_data['_foam_type'];
    }
    if ( isset( $cart_item_session_data['_pattern'] ) ) {
        $cart_item_data['_pattern'] = $cart_item_session_data['_pattern'];
    }
    if ( isset( $cart_item_session_data['_grade'] ) ) {
        $cart_item_data['_grade'] = $cart_item_session_data['_grade'];
    }

    return $cart_item_data;

}
// If you want to override the price you can use information saved against the product to do so
add_action( 'woocommerce_before_calculate_totals', 'update_custom_price', 1, 1 );
function update_custom_price( $cart_object ) {
    foreach ( $cart_object->cart_contents as $cart_item_key => $value ) {
		if(isset($value['_custom_price'])) {
        	$value['data']->price = $value['_custom_price'];
		}
    }
}
//This adds the information as meta data so that it can be seen as part of the order (to hide any meta data from the customer just start it with an underscore)
add_action( 'woocommerce_add_order_item_meta', 'add_custom_order_item_data', 10, 3 );
function add_custom_order_item_data( $itemId, $values, $key ) {
    if ( isset( $values['_grade'] ) ) {
        //wc_add_order_item_meta( $itemId, 'grade', $values['_grade'] );
        wc_add_order_item_meta( $itemId, 'Grade', $values['_grade'] );
    }
    if ( isset( $values['_foam_type'] ) ) {
        //wc_add_order_item_meta( $itemId, 'foam_type', $values['_foam_type'] );
        wc_add_order_item_meta( $itemId, 'Foam-Type', $values['_foam_type'] );
    }
    if ( isset( $values['_pattern'] ) ) {
        //wc_add_order_item_meta( $itemId, 'pattern', $values['_pattern'] );
        wc_add_order_item_meta( $itemId, 'Pattern', $values['_pattern'] );
    }
}

add_action( 'wp_ajax_get_patterns', 'get_patterns' );
add_action( 'wp_ajax_nopriv_get_patterns', 'get_patterns' );
function get_patterns() {
	$fabric_grade = strip_tags($_POST['fabric_grade']);
	$fabric_grade = mysql_real_escape_string($fabric_grade); // Attack Prevention
	
	$option_name = strtolower(str_replace(" ", "_", $fabric_grade))."_patterns";
	$pattern = $_POST['pattern_list'];
	
	$options = '';
	if(get_option($option_name)) {
		$fabric_patterns_grade = explode(",", get_option($option_name));
		sort($fabric_patterns_grade);
		foreach($fabric_patterns_grade as $pattern) {
			$price = explode("$", $pattern);
			//$options .= '<option value="'.$pattern.'" data-price="'.end($price).'">'.$pattern.'</option>';
			$options .= '<option value="'.$pattern.'">'.$pattern.'</option>';
		}
	}
	
	echo json_encode(array("options" => $options));
	exit;
}

add_action('admin_menu', 'caluco_register_product_customization_options_page');
function caluco_register_product_customization_options_page() {
	add_submenu_page( 'edit.php?post_type=product', 'Product Customization', 'Product Customization', 'manage_options', 'product-customization', 'product_customization_options_callback' );
}

function product_customization_options_callback() {
	//update_option( 'grade_a_patterns', '' );
	//update_option( 'grade_b_patterns', '' );
	//update_option( 'grade_c_patterns', '' );
?>
    <div class="wrap">
    	<h2><?php echo __('Product Customization Options'); ?></h2>
		<script type='text/javascript'>
            jQuery(document).ready(function(){
				var site_url = jQuery("#wp-admin-bar-view-site > a").attr("href");
                jQuery(".pattern-item .delete").click(function( event ){
                    //event.preventDefault();
					var pattern = jQuery(this).parent().attr("data-pattern");
					window.location = site_url+"wp-admin/edit.php?post_type=product&page=product-customization&del_patt="+pattern;
                });
                jQuery(".foam-item .delete").click(function( event ){
                    //event.preventDefault();
					var foam = jQuery(this).parent().attr("data-foam");
					window.location = site_url+"wp-admin/edit.php?post_type=product&page=product-customization&del_foam="+foam;
                });
                jQuery(".grade-item .delete").click(function( event ){
                    //event.preventDefault();
					var grade = jQuery(this).parent().attr("data-grade");
					window.location = site_url+"wp-admin/edit.php?post_type=product&page=product-customization&del_grade="+grade;
                });
                jQuery(".grade-item .edit").click(function( event ){
                    //event.preventDefault();
					var grade = jQuery(this).parent().attr("data-grade");
					window.location = site_url+"wp-admin/edit.php?post_type=product&page=product-customization&edit_grade="+grade;
                });
                jQuery(".foam-item .edit").click(function( event ){
                    //event.preventDefault();
					var foam = jQuery(this).parent().attr("data-foam");
					window.location = site_url+"wp-admin/edit.php?post_type=product&page=product-customization&edit_foam="+foam;
                });
			});
		</script>
        <?php
			if(isset($_GET['del_patt'])) {
				$pattern = $_GET['del_patt'];
				//echo $pattern;
				$caluco_patterns = explode(",", get_option('caluco_pattern'));
				$index = array_search($pattern, $caluco_patterns);
				unset($caluco_patterns[$index]);
				update_option( 'caluco_pattern', implode(",", $caluco_patterns) );

				$grade_of_fabrics = explode(",", get_option('grade_of_fabric'));
				foreach($grade_of_fabrics as $grade_of_fabric) {

					$option_name = strtolower(str_replace(" ", "_", $grade_of_fabric))."_patterns";
					
					if(get_option($option_name)) {
						$fabric_patterns_grade = explode(",", get_option($option_name));
						if(in_array($pattern, $fabric_patterns_grade)) {
							$index = array_search($pattern, $fabric_patterns_grade);
							unset($fabric_patterns_grade[$index]);
							update_option( $option_name, implode(",", $fabric_patterns_grade) );
						}
					}
					
				}
				echo "<div class='updated'><p>Successfully Removed</p></div>";
			}
			if(isset($_GET['unassign_pattern'])) {
				$pattern = $_GET['unassign_pattern'];
				$option_name = $_GET['option_name'];

				if(get_option($option_name)) {
					$fabric_patterns_grade = explode(",", get_option($option_name));
					if(in_array($pattern, $fabric_patterns_grade)) {
						$index = array_search($pattern, $fabric_patterns_grade);
						unset($fabric_patterns_grade[$index]);
						update_option( $option_name, implode(",", $fabric_patterns_grade) );
					}
				}
					
				echo "<div class='updated'><p>Successfully Removed</p></div>";
			}
			
			if(isset($_GET['del_foam'])) {
				$foam = $_GET['del_foam'];
				//echo $foam;
				$caluco_foams = explode(",", get_option('caluco_foam'));
				$index = array_search($foam, $caluco_foams);
				unset($caluco_foams[$index]);
				update_option( 'caluco_foam', implode(",", $caluco_foams) );
				echo "<div class='updated'><p>Successfully Removed</p></div>";
			}

			if(isset($_GET['del_grade'])) {
				$grade = $_GET['del_grade'];
				//echo $grade;
				$grade_of_fabrics = explode(",", get_option('grade_of_fabric'));
				$index = array_search($grade, $grade_of_fabrics);
				unset($grade_of_fabrics[$index]);
				update_option( 'grade_of_fabric', implode(",", $grade_of_fabrics) );
				echo "<div class='updated'><p>Successfully Removed</p></div>";
			}
		?>
		<?php
            if(isset($_POST['pc_options_submit'])){
				if(get_option('grade_of_fabric') != "") {
                	update_option( 'grade_of_fabric', get_option('grade_of_fabric').','.$_POST['grade_of_fabric'] );
				} else {
                	update_option( 'grade_of_fabric', $_POST['grade_of_fabric'] );
				}
                
                echo "<div class='updated'><p>Successfully Saved</p></div>";
            }
        ?>
		<?php
            if(isset($_POST['pc_grade_price_submit'])) {
				$grade_name = $_POST['edited_grade_name'];
				$grade_of_fabrics = explode(",", get_option('grade_of_fabric'));
                if(in_array($grade_name, $grade_of_fabrics)) {
                    $index = array_search($grade_name, $grade_of_fabrics);
					$grade_arr = explode("$", $grade_of_fabrics[$index]);
					$grade_label = trim($grade_arr[0]);
					$new_grade_price = $grade_label.' $'.$_POST['edit_grade_price'];
					
					$grade_of_fabrics[$index] = $new_grade_price;
					update_option( 'grade_of_fabric', implode(",", $grade_of_fabrics) );
					echo "<div class='updated'><p>Successfully Updated</p></div>";
				}
			}
            if(isset($_GET['edit_grade'])) {
                $grade = $_GET['edit_grade'];
                //echo $grade;
				$grade_of_fabrics = explode(",", get_option('grade_of_fabric'));
                if(in_array($grade, $grade_of_fabrics)) {
                    $arr_index = array_search($grade, $grade_of_fabrics);
					$grade_price = explode("$", $grade_of_fabrics[$arr_index]);
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <?php echo __('Edit Price'); ?>
                        </th>
                       <td>
                        	<form action="<?php echo admin_url('/edit.php?post_type=product&page=product-customization'); ?>" method="post">
                            	<input type="text" name="edit_grade_price" value="<?php echo trim($grade_price[1]); ?>" style="border:1px solid #0C6;" /> 
                                <input type="hidden" name="edited_grade_name" value="<?php echo $grade_of_fabrics[$arr_index]; ?>" />
                                <input type="submit" name="pc_grade_price_submit" class="button-primary" value="<?php _e('Update') ?>" />
                            </form>
                        </td>
                    </tr>
                </table>
                <?php
                }
            }
            if(isset($_POST['pc_foam_price_submit'])) {
				$foam_name = $_POST['edited_foam_name'];
				$caluco_foams = explode(",", get_option('caluco_foam'));
                if(in_array($foam_name, $caluco_foams)) {
                    $index = array_search($foam_name, $caluco_foams);
					$foam_arr = explode("$", $caluco_foams[$index]);
					$foam_label = trim($foam_arr[0]);
					$new_foam_price = $foam_label.' $'.$_POST['edit_foam_price'];
					
					$caluco_foams[$index] = $new_foam_price;
					update_option( 'caluco_foam', implode(",", $caluco_foams) );
					echo "<div class='updated'><p>Successfully Updated</p></div>";
				}
			}
            if(isset($_GET['edit_foam'])) {
                $foam = $_GET['edit_foam'];
                //echo $foam;
				$caluco_foams = explode(",", get_option('caluco_foam'));
                if(in_array($foam, $caluco_foams)) {
                    $arr_index = array_search($foam, $caluco_foams);
					$foam_price = explode("$", $caluco_foams[$arr_index]);
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <?php echo __('Edit Foam Price'); ?>
                        </th>
                       <td>
                        	<form action="<?php echo admin_url('/edit.php?post_type=product&page=product-customization'); ?>" method="post">
                            	<input type="text" name="edit_foam_price" value="<?php echo trim($foam_price[1]); ?>" style="border:1px solid #0C6;" /> 
                                <input type="hidden" name="edited_foam_name" value="<?php echo $caluco_foams[$arr_index]; ?>" />
                                <input type="submit" name="pc_foam_price_submit" class="button-primary" value="<?php _e('Update') ?>" />
                            </form>
                        </td>
                    </tr>
                </table>
                <?php
                }
            }
        ?>
        <form action="<?php echo admin_url('/edit.php?post_type=product&page=product-customization'); ?>" method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <?php echo __('Grade of Fabric List'); ?>
                    </th>
                    <td>
                    	<!--<select name="fabric_grade_list" id="fabric_grade_list">-->
                    	<?php
							$grade_of_fabrics = explode(",", get_option('grade_of_fabric'));
							foreach($grade_of_fabrics as $grade_of_fabric) {
								$dollar_exist = substr(trim($grade_of_fabric), -1);
								// if last character is $ no need to show it
								if($dollar_exist == '$') {
									$grade_of_fabric = trim($grade_of_fabric, '$');
								}
								//echo '<option value="'.$grade_of_fabric.'">'.$grade_of_fabric.'</option>';
								echo '<span class="grade-item" data-grade="'.$grade_of_fabric.'">'.$grade_of_fabric.' <strong class="delete">x</strong></span>';/* <strong class="edit" title="Edit">+</strong> */
							}
						?>
                        <!--</select>-->
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <?php echo __('Grade of Fabric'); ?>
                    </th>
                    <td>
                        <input type="text" name="grade_of_fabric" size="40" />
                        <br />
                        <small style="color:#777777;">Ex: Grade A <!--$50 || 30--></small>
                        <!--<br />
                        <small style="color:#999999;">For the example above, "50" is Regular price and "30" is Wholesale price</small>-->
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">&nbsp;</th>
                    <td>
                        <input type="submit" name="pc_options_submit" class="button-primary" value="<?php _e('Add Grade') ?>" />
                    </td>
                </tr>
            </table>
        </form>
        <hr />
		<?php
            if(isset($_POST['pc_pattern_submit'])){
				if(get_option('caluco_pattern') != "") {
                	update_option( 'caluco_pattern', get_option('caluco_pattern').','.$_POST['caluco_pattern'] );
				} else {
                	update_option( 'caluco_pattern', $_POST['caluco_pattern'] );
				}
                
                echo "<div class='updated'><p>Successfully Saved</p></div>";
            }
        ?>
        <style type="text/css">
			.pattern-item, .foam-item, .grade-item { background: #e6e6e6; display: inline-block; margin-right: 3px; padding: 3px 5px; }
			.pattern-item > strong, .foam-item > strong, .grade-item > strong { color: red; cursor: pointer; }
			.grade-item > strong.edit, .foam-item > strong.edit { color: green; font-size: 16px; font-weight: 700; }
			.assigned-pattern a { color: red; font-weight: 700; text-decoration: none; }
		</style>
        <form action="<?php echo admin_url('/edit.php?post_type=product&page=product-customization'); ?>" method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <?php echo __('Patterns'); ?>
                    </th>
                    <td>
                    	<!--<select name="pattern_list" id="pattern_list">-->
                    	<?php
							$caluco_patterns = explode(",", get_option('caluco_pattern'));
							sort($caluco_patterns);
							foreach($caluco_patterns as $pattern) {
								//echo '<option value="'.$pattern.'">'.$pattern.'</option>';
								echo '<span class="pattern-item" data-pattern="'.$pattern.'">'.$pattern.' <strong class="delete">x</strong></span>';
							}
						?>
                        <!--</select>-->
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <?php echo __('Pattern'); ?>
                    </th>
                    <td>
                        <input type="text" name="caluco_pattern" size="40" />
                        <br />
                        <small style="color:#777777;">Ex: Astoria Lagoon</small>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">&nbsp;</th>
                    <td>
                        <input type="submit" name="pc_pattern_submit" class="button-primary" value="<?php _e('Add Pattern') ?>" />
                    </td>
                </tr>
            </table>
        </form>
        <hr />
        <hr />
		<?php
            if(isset($_POST['pc_pattern_grade_submit'])){
				$fabric_grade = $_POST['fabric_grade_list'];
				$option_name = strtolower(str_replace(" ", "_", $fabric_grade))."_patterns";
				$pattern = $_POST['pattern_list'];
				
				if(get_option($option_name)) {
					$fabric_patterns_grade = explode(",", get_option($option_name));
					if(in_array($pattern, $fabric_patterns_grade)) {
						// do nothing
					} else {
                		update_option( $option_name, get_option($option_name).','.$pattern );
					}
				} else {
					update_option( $option_name, $pattern );
				}
				
                echo "<div class='updated'><p>Successfully Saved</p></div>";
            }
        ?>
        <form action="<?php echo admin_url('/edit.php?post_type=product&page=product-customization'); ?>" method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <?php echo __('Assign Pattern / Grade'); ?>
                    </th>
                    <td>
                    	<select name="fabric_grade_list" id="fabric_grade_list">
                    	<?php
							$grade_of_fabrics = explode(",", get_option('grade_of_fabric'));
							foreach($grade_of_fabrics as $grade_of_fabric) {
								$grade_price = explode("$", $grade_of_fabric);
								echo '<option value="'.trim($grade_price[0]).'">'.trim($grade_price[0]).'</option>';
							}
						?>
                        </select>
                    </td>
                    <td>
                    	<select name="pattern_list" id="pattern_list">
                    	<?php
							$caluco_patterns = explode(",", get_option('caluco_pattern'));
							sort($caluco_patterns);
							foreach($caluco_patterns as $pattern) {
								echo '<option value="'.$pattern.'">'.$pattern.'</option>';
							}
						?>
                        </select>
                        <br />
                        <small style="color:#777777;">Pattern</small>
                    </td>
                    <td>
                        <input type="submit" name="pc_pattern_grade_submit" class="button-primary" value="<?php _e('Submit') ?>" />
                    </td>
                </tr>
            </table>
        </form>
        <hr />
        <hr />
        <table class="form-table">
        	<tr valign="top">
				<?php
                    $grade_of_fabrics = explode(",", get_option('grade_of_fabric'));
					sort($grade_of_fabrics);
                    foreach($grade_of_fabrics as $grade_of_fabric) {
						echo '<td>';
                        $grade_price = explode("$", $grade_of_fabric);
						echo '<h3 style="margin-top: 0; margin-bottom: 10px;">'.trim($grade_price[0]).' Patterns</h3>';

						$option_name = strtolower(str_replace(" ", "_", trim($grade_price[0])))."_patterns";
						
						if(get_option($option_name)) {
							$fabric_patterns_grade = explode(",", get_option($option_name));
							sort($fabric_patterns_grade);
							foreach($fabric_patterns_grade as $pattern) {
								echo '<span class="assigned-pattern">'.$pattern.' <a class="delete" href="'.admin_url('/edit.php?post_type=product&page=product-customization&unassign_pattern='.$pattern.'&option_name='.$option_name).'">x</a></span><br>';
							}
						}
						echo '</td>';
                    }
                ?>
            </tr>
        </table>
        <hr />
		<?php
            if(isset($_POST['pc_foam_submit'])){
				if(get_option('caluco_foam') != "") {
                	update_option( 'caluco_foam', get_option('caluco_foam').','.$_POST['caluco_foam'] );
				} else {
                	update_option( 'caluco_foam', $_POST['caluco_foam'] );
				}
                
                echo "<div class='updated'><p>Successfully Saved</p></div>";
            }
        ?>
        <form action="<?php echo admin_url('/edit.php?post_type=product&page=product-customization'); ?>" method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <?php echo __('Foams'); ?>
                    </th>
                    <td>
                    	<!--<select name="foam_list" id="foam_list">-->
                    	<?php
							$caluco_foams = explode(",", get_option('caluco_foam'));
							foreach($caluco_foams as $foam) {
								//echo '<option value="'.$foam.'">'.$foam.'</option>';
								echo '<span class="foam-item" data-foam="'.$foam.'"><strong class="edit" title="Edit">+</strong> '.$foam.' <strong class="delete">x</strong></span>';
							}
						?>
                        <!--</select>-->
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <?php echo __('Foam'); ?>
                    </th>
                    <td>
                        <input type="text" name="caluco_foam" size="40" />
                        <br />
                        <small style="color:#777777;">Ex: Dry Fast Foam $100 || 70</small>
                        <br />
                        <small style="color:#999999;">For the example above, "100" is Regular price and "70" is Wholesale price</small>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">&nbsp;</th>
                    <td>
                        <input type="submit" name="pc_foam_submit" class="button-primary" value="<?php _e('Add Foam') ?>" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
<?php
}

function get_portfolio_posts($atts, $content = null) {
	extract(shortcode_atts(array(
		'cat_slug' => 'portfolio',
		'product_per_page' => 5
	), $atts));

	$args = array(
				'posts_per_page' => $product_per_page,
				'order' => 'DESC',
				'orderby' => 'date',
				'tax_query' => array(
					array(
						'taxonomy' => 'category',
						'field'    => 'slug',
						'terms'    => $cat_slug,
					)
				),
			);
	$portfolioArgs = new WP_Query( $args );

	ob_start();
	echo '<ul class="portfolio-posts">';

	global $post;
	while ( $portfolioArgs->have_posts() ) : $portfolioArgs->the_post();
?>
    <li>
        <a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a>
    </li>
<?php
	endwhile;
	wp_reset_postdata();
	
	echo '</ul>';

	$content = ob_get_clean();
	return $content;
}
add_shortcode("portfolio_posts", "get_portfolio_posts");
?>