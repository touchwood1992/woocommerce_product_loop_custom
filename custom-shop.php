<?php
/**
* Template Name:Shop Custom Page
*
*/
get_header();
the_title();
the_content();
?>
<div class='woocommerce aj'>

<?php
        if(!function_exists('wc_get_products')) {
            return;
        }
        
        $paged                   = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;        
        $ordering                = WC()->query->get_catalog_ordering_args();
        $ordering['orderby']     = array_shift(explode(' ', $ordering['orderby']));
        $ordering['orderby']     = stristr($ordering['orderby'], 'price') ? 'meta_value_num' : $ordering['orderby'];
        $products_per_page       = 1; //apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
        
        $featured_products       = wc_get_products(array(    
            'limit'                => $products_per_page,
            'page'                 => $paged,  
            'paginate'             => true,
            'return'               => 'ids' 
        ));
        
        wc_set_loop_prop('current_page', $paged);
        wc_set_loop_prop('is_paginated', wc_string_to_bool(true));
        wc_set_loop_prop('page_template', get_page_template_slug());
        wc_set_loop_prop('per_page', $products_per_page);
        wc_set_loop_prop('total', $featured_products->total);
        wc_set_loop_prop('total_pages', $featured_products->max_num_pages);
        
        if($featured_products) {
            do_action('woocommerce_before_shop_loop');
            woocommerce_product_loop_start();
            foreach($featured_products->products as $featured_product) {
                $post_object = get_post($featured_product);
                setup_postdata($GLOBALS['post'] =& $post_object);
                wc_get_template_part('content', 'product');
            }
            wp_reset_postdata();
            woocommerce_product_loop_end();
            do_action('woocommerce_after_shop_loop');		
        } else {
            do_action('woocommerce_no_products_found');
        }
?>

</div>
<?php
get_footer();
?>

<script>
(function($){
// $(document).ready(function(){
//     $.ajax({
//         url: '<?php echo admin_url( 'admin-ajax.php' );?>',
//         data: {action:"get_pros"},    
//         type: 'POST',
//         success:function(data){
//                 $('.woocommerce.aj').html(data)
//         }    
//     })    
// });

$(document).on('click' , 'a.page-numbers' , function(e){
    e.preventDefault();    
    const urlVal = e.target.href;    
    const newUrl = urlVal.split('?');
    const searchParams  = new URLSearchParams(newUrl[1]);
    const pageVal = searchParams .get('paged');
    console.log('pageVal' , pageVal)

    $.ajax({
        url: urlVal,
        //data: {action:"get_pros" , paged: pageVal},    
        //type: 'POST',
        success:function(data){
                //$('.woocommerce.aj').html(data)
                var result = $(data).filter('.woocommerce.aj');
                $('.woocommerce.aj').html(result);
                
        }
    });
})
})(jQuery);
</script>