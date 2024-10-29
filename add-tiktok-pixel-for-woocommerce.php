<?php
/**
 *
 * @link              https://www.dcsdigital.co.uk
 * @since             1.0.0
 * @package           Add_TikTok_Pixel_For_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Add TikTok Pixel for WooCommerce
 * Plugin URI:        https://www.dcsdigital.co.uk/tiktok-pixel-for-woocommerce/
 * Description:       Add the TikTok tracking pixel to your WooCommerce website
 * Version:           1.0.3
 * Author:            DCS Digital
 * Author URI:        https://www.dcsdigital.co.uk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dcs-digital-tiktok-pixel
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Only initiate the plugin after Woocommerce has loaded (and by extension, if it's installed and active)
add_action( 'woocommerce_loaded', 'tiktok_pixel_action_woocommerce_loaded', 10, 1 ); 

function tiktok_pixel_action_woocommerce_loaded() {
    new TikTok_Pixel_For_WooCommerce();
}

class TikTok_Pixel_For_WooCommerce {

    public $pixel_id;
    public $currency;

	public function __construct() {

        $this->pixel_id = get_option('tiktok_pixel_id');

        // Front-end output the tracking events and pixel
        if( !is_admin() && !empty( $this->pixel_id ) ) {

            $this->currency = get_woocommerce_currency();

            // Add Tracking Pixel
            add_action( 'wp_head', [ $this, 'wp_head_add_tiktok_pixel' ] );

            // Single Product "View" Event
            add_action( 'woocommerce_after_single_product', [ $this, 'single_product_view_tiktok_event' ] );

            // "Add to Cart" Events
            add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'single_product_add_to_cart_tiktok_event' ] );
            add_action( 'wp_footer', [ $this, 'loop_add_to_cart_tiktok_event' ] );

            // "Initiate Checkout" Event
            add_action( 'woocommerce_after_checkout_form', [ $this, 'checkout_initiate_tiktok_event' ] );

            // "Purchase" Events
            add_action( 'woocommerce_thankyou', [ $this, 'purchase_tiktok_event' ], 10, 1 );

            // Hook for modifying the add to cart button
            add_filter('woocommerce_available_variation', array($this, 'add_tiktok_sku_to_variations'), 10, 3);

        }

        if( is_admin() ) {

            // Initiate the admin settings
            // Accessed via Woocommerce > TikTok Pixel
            add_action( 'admin_menu', [ $this, 'admin_menu_item' ], 999999 );
            add_action( 'admin_init', [ $this, 'tiktok_pixel_load_settings'] );

            // Add Custom Meta field for TikTok SKU, allowing you to override the SKU in the tracking pixel

            // Hooks for adding and saving product field
            add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_tiktok_sku_product_field' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'save_tiktok_sku_product_field' ) );

            // Hooks for adding and saving product variation fields
            add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_tiktok_sku_variation_field' ), 10, 3 );
            add_action( 'woocommerce_save_product_variation', array( $this, 'save_tiktok_sku_variation_field' ), 10, 2 );

        }


    }

    public function admin_menu_item() {
	    add_submenu_page( 'woocommerce', 'TikTok Pixel', 'TikTok Pixel', 'manage_options', 'tiktox-pixel-for-woocommerce', [$this, 'init_admin_page' ] );
    }

    /**
     * Add the TikTok tracking pixel to the head tag
     *
     * @since 1.0.0
     */
    public function wp_head_add_tiktok_pixel() {

        // Docs: https://ads.tiktok.com/help/article?aid=10021

        echo  '
        <script>
            !function (w, d, t) {
                w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
                ttq.load(\''.esc_js( $this->pixel_id ).'\');
                ttq.page();
            }(window, document, "ttq");
        </script>
        ';

    }

    /**
     * Trigger TikTok "view" event on the product page
     * Sends values for the parameters: content_type, content_id 
     *
     * @since 1.0.0
     */
    public function single_product_view_tiktok_event() {

        global $product;

        // Attempt to get the TikTok SKU from product meta
        $tiktok_sku = get_post_meta( $product->get_id(), 'tiktok_sku', true );
        
        // Use TikTok SKU if it exists, otherwise fall back to the product SKU or ID
        $product_id = $tiktok_sku ? $tiktok_sku : ($product->get_sku() ? $product->get_sku() : $product->get_id());

        // Docs: https://ads.tiktok.com/help/article?aid=10028

        echo "
        <script>
            ttq.track('ViewContent', {
                content_type: 'product',
                content_id: '".esc_js( $product_id )."',
            });
        </script>
        ";

    }

	/**
	 * Injects jQuery into the page which will wait for a "add to cart" button click so we can trigger
     * the TikTok tracking
     *
     * @since 1.0.0
	 */
    public function single_product_add_to_cart_tiktok_event() {

        if ( ! is_single() ) {
            return;
        }
    
        global $product;
        global $tiktok_pixel_add_to_cart_script_added; // Make sure we don't inject the script more than once
    
        if( empty( $tiktok_pixel_add_to_cart_script_added ) ) {
    
            // Docs: https://ads.tiktok.com/help/article?aid=10028

            // Handle non-variable products by setting the parent product details as the defaults  
            // Attempt to get the TikTok SKU from product meta
            $tiktok_sku = get_post_meta( $product->get_id(), 'tiktok_sku', true );

            // Use TikTok SKU if it exists, otherwise fall back to the product SKU or ID
            $product_id = $tiktok_sku ? $tiktok_sku : ($product->get_sku() ? $product->get_sku() : $product->get_id());

            // Get product name and price
            $product_name = $product->get_name();
            $product_price = $product->get_price();
    
            wc_enqueue_js( "
            var tiktokContentId = '".esc_js( $product_id )."';
            var tiktokValue = ".esc_js( $product_price ).";
            var tiktokName = '".esc_js( $product_name )."';
    
                // Listen for when a variation is selected
                $( '.variations_form' ).on( 'found_variation', function( event, variation ) {
                    // Use TikTok SKU if it exists, otherwise fall back to the variation SKU or ID
                    tiktokContentId = variation.tiktok_sku ? variation.tiktok_sku : (variation.sku ? variation.sku : variation.variation_id);
                    tiktokValue = variation.display_price;
                    tiktokName = variation.variation_description ? variation.variation_description : '".esc_js( $product->get_title() )."';
                    console.log( 'tiktokContentId: ' + tiktokContentId );
                });
    
                $( '.single_add_to_cart_button' ).on( 'click', function() {
                    ttq.track('AddToCart', {
                        content_id: tiktokContentId,
                        content_type: 'product',
                        content_name: tiktokName,
                        quantity: $( 'input.qty' ).val() ? $( 'input.qty' ).val() : '1',
                        currency: '".esc_js( $this->currency )."',
                        value: tiktokValue,
                    });
                });
            " );
    
            $tiktok_pixel_add_to_cart_script_added = true;
        }
    
    }

	/**
	 * Injects jQuery into the page which will wait for a "add to cart" button click on an archive page
     * so we can trigger the TikTok tracking.
     * 
     * NOTE: Does not handle variable or grouped products, as generally these are added to the basket from
     * a "single" product page
     *
     * @since 1.0.0
	 */
    public function loop_add_to_cart_tiktok_event() {

        // Docs: https://ads.tiktok.com/help/article?aid=10028

        wc_enqueue_js( "
            $( '.add_to_cart_button:not(.product_type_variable, .product_type_grouped)' ).on( 'click', function() {
                console.log('here');
                ttq.track('AddToCart', {
                    content_id: ($(this).data('tiktok_sku')) ? ($(this).data('tiktok_sku')) : ( ($(this).data('product_sku')) ? ($(this).data('product_sku')) : ( $(this).data('product_id')) ),
                    content_type: 'product',
                    quantity: $(this).data('quantity'),
                    currency: '".esc_js( $this->currency )."',
                });
            });
        " );
    }

    /**
     * Trigger TikTok "Initiate Checkout" event on the product page
     * Sends values for the parameters: content_type, content_id 
     *
     * @since 1.0.0
     */
    public function checkout_initiate_tiktok_event() {

        // Docs: https://ads.tiktok.com/help/article?aid=10028

        echo "
        <script>
            ttq.track('InitiateCheckout');
        </script>
        ";

    }

    /**
     * Trigger TikTok "purchase" event on the thank you page
     *
     * @since 1.0.0
     */
    public function purchase_tiktok_event( $order_id ) {

        $order = new WC_Order( $order_id );
        $contents = '';

        $items = $order->get_items();
        
        foreach ( $items as $item ) {

            $product = $item->get_product();

            // Attempt to get the TikTok SKU from product meta
            $tiktok_sku = get_post_meta( $product->get_id(), 'tiktok_sku', true );

            // Use TikTok SKU if it exists, otherwise fall back to the product SKU or ID
            $product_id = $tiktok_sku ? $tiktok_sku : ($product->get_sku() ? $product->get_sku() : $product->get_id());

            $quantity = $item->get_quantity();
            $subtotal = round( $item->get_total() + $item->get_total_tax(), 2 );

            $contents .= "
                ttq.track('CompletePayment', {
                    content_id: '". esc_js( $product_id) ."',
                    content_type: 'product',
                    value: '". esc_js( $subtotal) ."',
                    quantity: '". esc_js( $quantity) ."',
                    currency: '".esc_js( $this->currency )."',
                });  
            ";

        }

        if( !empty( $contents ) ) {
            print "<script>".$contents."</script>";
        }

    }


    /* ADMIN PAGE FUNCTIONS */

    /**
     * Prepare the admin page
     * 
     * since 1.0.0
     */
	public function init_admin_page(){
		
        ?>
        
        <div style="max-width: 600px; margin: 0 auto; margin-top: 40px;">
            <h1>TikTok Tracking Pixel Setup</h1>
            
            <form method="POST" action="<?php echo admin_url( 'options.php' ); ?>">
                <?php
                    settings_fields( 'tiktox-pixel-for-woocommerce' );
                    do_settings_sections( 'tiktox-pixel-for-woocommerce' );
                    submit_button( __( 'Save', 'tiktok-pixel-for-woocommerce' ), 'primary' )
                ?>
            </form>
        </div>

        <?php
	
	}

    /**
     * Define the settings
     * 
     * since 1.0.0
     */
    public function tiktok_pixel_load_settings() {
    
        add_settings_section(
            'tiktok-pixel-settings-section',
            'Connection',
            [ $this, 'tiktok_pixel_settings_section_callback'],
            'tiktox-pixel-for-woocommerce'
        );
        
        add_settings_field(
            'tiktok_pixel_id',
            'TikTok Pixel ID',
            [ $this, 'tiktok_pixel_field'],
            'tiktox-pixel-for-woocommerce',
            'tiktok-pixel-settings-section'
        );
        
        register_setting( 'tiktox-pixel-for-woocommerce', 'tiktok_pixel_id' );		
    
    }
    
    /**
     * Output the section HTML
     * 
     * since 1.0.0
     */
    public function tiktok_pixel_settings_section_callback() {
        echo '<p>Simply enter or update your tracking pixel below. Once a pixel has been added, event tracking will automatically be enabled on this website.</p>';
        echo '<p><strong>Need help getting setup?</strong> Send us a <a href="mailto:support@dcsdigital.co.uk?subject=TikTok Pixel Setup">support ticket</a>.</p>';
    }
    
    /**
     * Function TikTok SKU field to variable product edit screens
     * 
     * since 1.0.3
     */
    public function tiktok_pixel_field() {
        ?>
        <input type="text" id="tiktok_pixel_id" name="tiktok_pixel_id" value="<?php echo esc_attr( $this->pixel_id ); ?>" placeholder="e.g. ADP5K6JC77U9O4C8DQLG">
        <?php
    }

    public function add_tiktok_sku_to_variations($variation_data, $product, $variation) {
        $tiktok_sku = get_post_meta($variation->get_id(), 'tiktok_sku', true);
        
        $variation_data['tiktok_sku'] = $tiktok_sku ? $tiktok_sku : '';
        
        return $variation_data;
    }

    /**
     * Function to add custom tiktok_sku field for product
     * 
     * since 1.0.3
     */
    public function add_tiktok_sku_product_field() {
        woocommerce_wp_text_input(
            array(
                'id' => 'tiktok_sku',
                'label' => __( 'TikTok SKU', 'woocommerce' ),
                'desc_tip' => 'true',
                'description' => __( 'Complete this field if you want to use the TikTok Generated SKU ID in tracking pixel events, rather than your WooCommerce SKU or product ID.', 'woocommerce' ),
            )
        );
    }

    /**
     * Function to save custom tiktok_sku field for product
     * 
     * since 1.0.3
     */
    public function save_tiktok_sku_product_field( $post_id ) {
        $tiktok_sku = $_POST['tiktok_sku'];
        if ( ! empty( $tiktok_sku ) ) {
            update_post_meta( $post_id, 'tiktok_sku', esc_attr( $tiktok_sku ) );
        }
    }

    /**
     * Function to add custom tiktok_sku field for product variation
     * 
     * since 1.0.3
     */
    public function add_tiktok_sku_variation_field( $loop, $variation_data, $variation ) {
        woocommerce_wp_text_input(
            array(
                'id' => 'tiktok_sku_' . $variation->ID,
                'label' => __( 'TikTok SKU', 'woocommerce' ),
                'desc_tip' => 'true',
                'description' => __( 'Complete this field if you want to use the TikTok Generated SKU ID in tracking pixel events, rather than your WooCommerce SKU or product ID.', 'woocommerce' ),
                'value' => get_post_meta( $variation->ID, 'tiktok_sku', true ),
            )
        );
    }

    /**
     * Function to save custom tiktok_sku field for product variation
     * 
     * since 1.0.3
     */
    public function save_tiktok_sku_variation_field( $variation_id, $i ) {
        $tiktok_sku = $_POST['tiktok_sku_' . $variation_id];
        if ( isset( $tiktok_sku ) ) {
            update_post_meta( $variation_id, 'tiktok_sku', esc_attr( $tiktok_sku ) );
        }
    }

}
