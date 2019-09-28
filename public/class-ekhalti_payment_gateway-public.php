<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Ekhalti_payment_gateway
 * @subpackage Ekhalti_payment_gateway/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ekhalti_payment_gateway
 * @subpackage Ekhalti_payment_gateway/public
 * @author     e-khalti.com
 */
class Ekhalti_payment_gateway_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The contract of this ekhalti.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $contract    The current version of this plugin.
     */
    private $contract;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->contract = Ekhalti_contract::get_instance();
        $this->order = Ekhalti_order::get_instance();
//        $this->contract->get_info();
//        die;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ekhalti_payment_gateway_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ekhalti_payment_gateway_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ekhalti_payment_gateway-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ekhalti_payment_gateway_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ekhalti_payment_gateway_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script('ekhalti_script');
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ekhalti_payment_gateway-public.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'ekhaltiAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
//        wp_enqueue_script('jquery');
    }

    public function register_shortcodes() {
        add_shortcode('ekhalti_buy_button', array($this, 'buy_button_shrotcode'));
        add_shortcode('ekhalti_buy_button_2', array($this, 'buy_button_shrotcode'));
    }

    public function register_ajax() {
        add_action('wp_ajax_ek_buy_button', array($this, 'ekhalti_buy_button_handle'));
        add_action('wp_ajax_nopriv_ek_buy_button', array($this, 'ekhalti_buy_button_handle'));
    }

    /**
     * buy_button_shrotcode
     * @param type $atts
     * @return json string
     */
    function buy_button_shrotcode($atts) {
        $from_id = uniqid();
        $id = uniqid();

        $a = shortcode_atts(array(
            'title' => 'test',
            'description' => 'test',
            'mode' => 'buy',
            'type' => 'buy',
            'price' => 0,
            'currency' => 'INR',
                ), $atts);

//        return "foo = {$a['foo']}";
//        print_r($a);
        $type = $a['type'];
        $price = $a['price'];
        $currency = $a['currency'];
        $title = $a['title'];
        $description = $a['description'];



        $output = <<<EOT
        <script>
        /* <![CDATA[ */
            jQuery(document).ready(function($){
                $(function(){
                    jQuery("#buy_$id").click( function(e) {                        
                        e.preventDefault(); 
                var that =this;
                        jQuery("#loading-indicator-$from_id").show();
                        jQuery(this).hide();
                      var  type = "$type";
                      var  wp_nonce = jQuery('#form$from_id #wp_nonce').val();
                      var  price = "$price";
                      var  id = "$id";
                      var  hash="";
                      var  title="$title";
                      var  description="$description";
                
                        jQuery.ajax({
                           type : "post",
                           dataType : "json",
                           url : ekhaltiAjax.ajaxurl,
                           data : {action: "ek_buy_button", type : type, wp_nonce: wp_nonce,hash:hash,title:title,description:description,price:price},
                           success: function(response) {
                              if(response.status == "200") {
                                 window.location.href=response.link;
                              }
                              else {
                                 alert("Your like could not be added");
                                jQuery("#loading-indicator-$from_id").hide();
                                jQuery(that).show();
                            
                              }
                           }
                        });
                     }); 
                });
            });
            /* ]]> */    
        </script>    
EOT;
        $imglink = plugin_dir_url(__FILE__) . '/images/Rolling-1s-200px.svg';
        $loadin = '<img  src="' . $imglink . '" id="loading-indicator-' . $from_id . '" style="display:none;width:50px" />';
        $output .= "<form id='form$from_id' method='post'>" . wp_nonce_field('ek_buy_button', 'wp_nonce') . $loadin . "<button class='btn' id='buy_{$id}'>Buy</button></form>";
        return $output;
    }

    function ekhalti_buy_button_handle() {

        if (empty($_POST) || !wp_verify_nonce($_POST['wp_nonce'], 'ek_buy_button')) {
            echo 'You targeted the right function, but sorry, your nonce did not verify.';
            die();
        }
        //sleep(6);

//        $title = sanitize_text_field($_POST['title']) . rand(22, 345345345);
        $title = sanitize_text_field($_POST['title']);

        $description = sanitize_text_field($_POST['description']);
        $price = ($_POST['price']);
        $type = sanitize_text_field($_POST['type']);
        $wp_nonce = sanitize_text_field($_POST['wp_nonce']);
        $current_user = "";
//        $order_id = 43534;

        $parameter = array(
            'order' => $order_id,
            'order_id' => $order_id,
            'title' => $title,
            'description' => $description,
            'all_items_name' => $title,
            'all_items_number' => 1,
            'price' => $price,
            'wp_nonce' => $wp_nonce,
            'order_total' => $price,
            'debit_base' => "",
            'customer_note' => "coool",
            'billing_first_name' => "",
            'billing_last_name' => "",
            'billing_email' => "",
            'billing_phone' => "",
            'billing_address_1' => "",
            'billing_city' => "",
            'billing_state' => "",
            'billing_country' => "",
            'billing_postcode' => "",
            'redirect_url' => "",
            'success_url' => "",
            'fail_link' => "",
            'version' => 2
        );

        $order_id = $this->order->create($parameter);



        if ($order_id) {
            $parameter['order'] = $order_id;
            $parameter['order_id'] = $order_id;

            $res = $this->contract->initialize_payment($parameter);
            $res['hash'] = $this->order->get_hash();
            $this->order->update_meta('ref', $res['ref']);

            echo json_encode($res);

            exit();
        }


        echo json_encode([
            "link" => "#",
            "flag" => "failed",
            "status" => 403,
            "ref" => "####",
            "msg" => "failed",
            "hash" => "####"
        ]);
        exit();
    }

    /**
     * End buy_button_shrotcode
     */
    function wp_paypal_get_add_to_cart_button($atts) {
        $button_code = '';
        $action_url = 'https://www.paypal.com/cgi-bin/webscr';
        if (isset($atts['env']) && $atts['env'] == "sandbox") {
            $action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        $target = 'paypal'; //let PayPal do its thing for shopping cart functionality
        /*
          if(isset($atts['target']) && !empty($atts['target'])) {
          $target = $atts['target'];
          }
         */
        $button_code .= '<form target="' . $target . '" action="' . $action_url . '" method="post" >';
        $button_code .= '<input type="hidden" name="cmd" value="_cart">';
        $button_code .= '<input type="hidden" name="add" value="1">';
        $paypal_email = get_option('wp_paypal_email');
        if (isset($paypal_email) && !empty($paypal_email)) {
            $button_code .= '<input type="hidden" name="business" value="' . $paypal_email . '">';
        }
        if (isset($atts['lc']) && !empty($atts['lc'])) {
            $lc = $atts['lc'];
            $button_code .= '<input type="hidden" name="lc" value="' . $lc . '">';
        }
        if (isset($atts['name']) && !empty($atts['name'])) {
            $name = $atts['name'];
            $button_code .= '<input type="hidden" name="item_name" value="' . $name . '">';
        }
        if (isset($atts['item_number']) && !empty($atts['item_number'])) {
            $item_number = $atts['item_number'];
            $button_code .= '<input type="hidden" name="item_number" value="' . $item_number . '">';
        }
        if (isset($atts['amount']) && is_numeric($atts['amount'])) {
            $amount = $atts['amount'];
            $button_code .= '<input type="hidden" name="amount" value="' . $amount . '">';
        }
        if (isset($atts['currency']) && !empty($atts['currency'])) {
            $currency = $atts['currency'];
            $button_code .= '<input type="hidden" name="currency_code" value="' . $currency . '">';
        }
        $button_code .= '<input type="hidden" name="button_subtype" value="products">';
        $no_note = 0; //default
        if (isset($atts['no_note']) && is_numeric($atts['no_note'])) {
            $no_note = $atts['no_note'];
            $button_code .= '<input type="hidden" name="no_note" value="' . $no_note . '">';
        }
        if (isset($atts['cn']) && !empty($atts['cn'])) {
            $cn = $atts['cn'];
            $button_code .= '<input type="hidden" name="cn" value="' . $cn . '">';
        }
        $no_shipping = 0; //default
        if (isset($atts['no_shipping']) && is_numeric($atts['no_shipping'])) {
            $no_shipping = $atts['no_shipping'];
            $button_code .= '<input type="hidden" name="no_shipping" value="' . $no_shipping . '">';
        }
        if (isset($atts['shipping']) && is_numeric($atts['shipping'])) {
            $shipping = $atts['shipping'];
            $button_code .= '<input type="hidden" name="shipping" value="' . $shipping . '">';
        }
        if (isset($atts['shipping2']) && is_numeric($atts['shipping2'])) {
            $shipping2 = $atts['shipping2'];
            $button_code .= '<input type="hidden" name="shipping2" value="' . $shipping2 . '">';
        }
        if (isset($atts['tax']) && is_numeric($atts['tax'])) {
            $tax = $atts['tax'];
            $button_code .= '<input type="hidden" name="tax" value="' . $tax . '">';
        }
        if (isset($atts['tax_rate']) && is_numeric($atts['tax_rate'])) {
            $tax_rate = $atts['tax_rate'];
            $button_code .= '<input type="hidden" name="tax_rate" value="' . $tax_rate . '">';
        }
        if (isset($atts['handling']) && is_numeric($atts['handling'])) {
            $handling = $atts['handling'];
            $button_code .= '<input type="hidden" name="handling" value="' . $handling . '">';
        }
        if (isset($atts['weight']) && is_numeric($atts['weight'])) {
            $weight = $atts['weight'];
            $button_code .= '<input type="hidden" name="weight" value="' . $weight . '">';
        }
        if (isset($atts['weight_unit']) && !empty($atts['weight_unit'])) {
            $weight_unit = $atts['weight_unit'];
            $button_code .= '<input type="hidden" name="weight_unit" value="' . $weight_unit . '">';
        }
        if (isset($atts['return']) && filter_var($atts['return'], FILTER_VALIDATE_URL)) {
            $return = esc_url($atts['return']);
            $button_code .= '<input type="hidden" name="return" value="' . $return . '">';
        }
        if (isset($atts['cancel_return']) && filter_var($atts['cancel_return'], FILTER_VALIDATE_URL)) {
            $cancel_return = esc_url($atts['cancel_return']);
            $button_code .= '<input type="hidden" name="cancel_return" value="' . $cancel_return . '">';
        }
        if (isset($atts['callback']) && !empty($atts['callback'])) {
            $notify_url = $atts['callback'];
            $button_code .= '<input type="hidden" name="notify_url" value="' . $notify_url . '">';
        }
        $button_code .= '<input type="hidden" name="bn" value="WPPayPal_AddToCart_WPS_US">';
        $button_image_url = WP_PAYPAL_URL . '/images/add-to-cart.png';
        if (isset($atts['button_image']) && filter_var($atts['button_image'], FILTER_VALIDATE_URL)) {
            $button_image_url = esc_url($atts['button_image']);
        }
        $button_code .= '<input type="image" src="' . $button_image_url . '" border="0" name="submit">';
        $button_code .= '</form>';
        return $button_code;
    }

    public function wp_ekhalti_gateway_response() {
        global $woocommerce;
        /* Change IPN URL */


        if (isset($_REQUEST['ekref'])) {

            $tmp = $this->contract->decript_response($_REQUEST['ekref']);

            $order_id = $tmp['order'];

            if ($order_id != '') {

                try {
//                    echo'<pre>';
                    $order = $this->order->get_order($order_id);




//                    print_r($tmp);
                    $this->order->update_meta('ekref', $_REQUEST['ekref']);

//                    die;
                    $status = $tmp['status_code'];
                    if ($status == '200') {
                        $this->order->payment_complete();

                        wp_redirect($this->contract->success_link());
                        exit;
                    }

                    $trans_authorised = false;

//                    if (!$order->has_status('completed')) {
//                        $status = strtolower($status);
//                        if ('confirmed' == $status) {
//                            $trans_authorised = true;
//                            $this->msg['message'] = "Thank you for the order. Your account has been charged and your transaction is successful.";
//                            $this->msg['class'] = 'success';
//                            $order->add_order_note('e-Khalti payment successful main.<br/>e-Khalti Transaction ID: ' . $tmp['transaction']);
//                            $order->payment_complete();
//                            $woocommerce->cart->empty_cart();
//                            $order->update_status('completed');
//                        } else {
//                            $this->msg['class'] = 'error';
//                            $this->msg['message'] = "Thank you for the order. However, the transaction has been declined now.";
//                            $order->add_order_note('Transaction Fail');
//                        }
//                    }
                } catch (Exception $e) {
                    $this->msg['class'] = 'error';
                    $this->msg['message'] = "Thank you for the order. However, the transaction has been declined now.";
                }
            } else {
                // if order not found
                // update status incomplete
            }




            wp_redirect($this->contract->fail_link());
            exit;
        }
    }

}
