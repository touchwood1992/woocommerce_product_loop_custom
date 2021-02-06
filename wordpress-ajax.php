function cl_send_attachments_order_cb()
{
		if(!check_ajax_referer( 'ops-send-email', 'security' , false ))
		{
			wp_send_json_error( 'Invalid security token sent.' , 403);
		}
		$order_id = $_POST['oId'];
		$attachments = get_posts( array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $order_id,
            'exclude'     => get_post_thumbnail_id($order_id)
		) );
		if($attachments)
		{			
			$order = new WC_Order($order_id);
			$customer_email = $order->billing_email;
			$mail_attachemtns = array();
			foreach ($attachments as $key => $value) {
				$mail_attachemtns[] = get_attached_file($value->ID);
			}
			$headers[] = 'From: Admin Creatufut  <contacto@creatufut.com>';
			wp_mail($customer_email, 'Your Order Images', 'Here are you completed designs. For any further changes please contact us at contacto@creatufut.com', $headers, $mail_attachemtns );
			wp_send_json_success( array('msg'=>'Email sent successfully.' , 'success' => true) , 200 );
		}
		else{
			wp_send_json_success( array('msg'=>'No attachment found.' , 'success' => false) , 200 );
		}		
}
add_action('wp_ajax_cl_send_attachments_order' , 'cl_send_attachments_order_cb');




<script>
(function ($) {
  $(document).ready(function () {
    const sendEmailButton = $("#send-email-attachemnts");

    const showLoader = () => {
      sendEmailButton.attr("disabled", "disabled");
      $(".oz_upload_file_for_order_loader").show();
    };

    const hideLoader = () => {
      sendEmailButton.removeAttr("disabled");
      $(".oz_upload_file_for_order_loader").hide();
    };
    sendEmailButton.click(function (e) {
      e.preventDefault();
      showLoader();
      const { oId } = e.target.dataset;
      if (oId) {
        $.ajax({
          url: ajaxurl,
          data: {
            oId,
            action: "cl_send_attachments_order",
            security: oz_obj.ajnon,
          },
          type: "POST",
          dataType: "JSON",
          success: function (data) {
            hideLoader();
            alert(data.data.msg);
          },
          error: function (jqXHR, exception) {
            hideLoader();
            alert(jqXHR?.responseJSON?.data);
          },
        });
      }
    });
  });
})(jQuery);
</script>
