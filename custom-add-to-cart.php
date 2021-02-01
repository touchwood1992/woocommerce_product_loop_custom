add_action( 'woocommerce_before_calculate_totals', 'adding_custom_price', 10, 1);
function adding_custom_price( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;
    foreach ( $cart->get_cart() as $cart_item ) {
		if(!empty($cart_item['total_lawn_size']))
		{
			$cart_item['data']->set_price(floatval($cart_item['fixed_price'] + ($cart_item['total_lawn_size'] * $cart_item['percentage_price'])));
		}       
    }
}
add_shortcode('process_make_calculation' , function($atts){
	if(is_admin()) return;
	global $woocommerce;
	$atts = shortcode_atts(
        array(
			'percentage_price' => 0.21,
			'fixed_price' => 7.50,
			'pid' => 0,
			'per_month_text' => 'per month per m2',
			'total_lawn_size_top_label' => 'Total Lawn Size',
			'total_lawn_calculate_label' => 'Total lawn size',
			'buynow_text' => 'Buy Now'
		), $atts, 'process_make_calculation' );

	if(!empty($_POST['total_lawn_size']) && !empty($_POST['fixed_price']) && !empty($_POST['percentage_price']))
	{
		
		$product_id = $atts['pid'];
		if(empty($product_id)){
			die('Product not set up');
		}
		$woocommerce->cart->empty_cart();
		$woocommerce->cart->add_to_cart( $product_id );
		wp_redirect(wc_get_checkout_url()); die();
	}
	ob_start();	
	?>
		<form  method='post' style='text-align:center;padding:10rem 0'>
			<h4>from <?php echo wc_price($atts['fixed_price']);?>/pcm</h4>
			<div class="card">
				<p><?php echo wc_price($atts['percentage_price'] + 0); echo " ".$atts['per_month_text']; ?></p>
				<div>
					<p><?php echo $atts['total_lawn_size_top_label'];?></p>
					<input type="number" class='total_lawn_size' required name="total_lawn_size" id="" min='1' value='1'/>
					<p><?php echo $atts['total_lawn_calculate_label'];?>: <span class='show-total-lawn-price'></span></p>					
					<input type="hidden" name="fixed_price" value='<?php echo $atts['fixed_price'];?>'>
					<input type="hidden" name="percentage_price" value='<?php echo $atts['percentage_price'];?>'>
					<button><?php echo $atts['buynow_text'];?></button>
				</div>
			</div>
		
		</form>
		<script>
			(function($){
				$(document).ready(function(){
						const defaultValue = <?php echo $atts['percentage_price'];?>;
						const defaultQty = 1;
						const priceShowSelector = document.querySelector('.show-total-lawn-price');
						const qtyInputSelector = document.querySelector('.total_lawn_size');
						class App{
							constructor(){
								this._init();
							}
							_init()
							{
								this._showPrice(1);
								qtyInputSelector.addEventListener('input' , this._changeQty.bind(this));
							}
							_changeQty(e){
								const {value} = e.target;
								if(value)
								{
									this._showPrice(value);
								}								
							}
							_showPrice(qty){
								priceShowSelector.innerHTML = (defaultValue * qty).toFixed(2);
							}
						}
						new App();
				});
			})(jQuery)
		</script>
	<?php
	return ob_get_clean();
});
add_filter( 'woocommerce_add_cart_item_data', 'add_cart_item_custom_data_vase', 10, 2 );
function add_cart_item_custom_data_vase( $cart_item_meta, $product_id ) {
	global $woocommerce;
	if(!empty($_POST['total_lawn_size']) && !empty($_POST['fixed_price']) && !empty($_POST['percentage_price'])){
		$cart_item_meta['total_lawn_size'] = $_POST['total_lawn_size'];
		$cart_item_meta['fixed_price'] = $_POST['fixed_price'];
		$cart_item_meta['percentage_price'] = $_POST['percentage_price'];
	}
	return $cart_item_meta; 
}

function cl_displaylawn_cb( $item_data, $cart_item ) {
	if ( empty( $cart_item['total_lawn_size'] ) || empty( $cart_item['fixed_price'] )  || empty( $cart_item['percentage_price'] )  ) {
		return $item_data;
	}
	$item_data[] = array(
		'key'       => 'Lawn Size',
		'value'     => $cart_item['total_lawn_size']
	);
	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'cl_displaylawn_cb', 999, 2 );
function cl_create_order_line_item( $item, $cart_item_key, $values, $order ) {
	if( isset( $values['total_lawn_size'] ) ) {
	$item->add_meta_data(
	__( 'Lawn Size', 'plugin-republic' ),
	$values['total_lawn_size'],
	true
	);
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'cl_create_order_line_item', 10, 4 );
   function cl_create_order_line_item_add_cb( $product_name, $item ) {
	if( isset( $item['total_lawn_size'] ) ) {
	$product_name .= sprintf(
	'<ul><li>%s: %s</li></ul>',
	__( 'Lawn Size', 'plugin_republic' ),
	esc_html( $item['total_lawn_size'] )
	);
	}
	return $product_name;
   }   
add_filter( 'woocommerce_order_item_name', 'cl_create_order_line_item_add_cb', 10, 2 );

add_action('init' , function(){ob_start();});
