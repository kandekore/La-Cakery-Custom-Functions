<?php
/**
 * Plugin Name: La Cakery Custom Functions
 * Description: Custom functions for La Cakery website.
 * Version: 1.1
 * Author: Darren Kandekore
 */

// Add custom menu item to the admin dashboard
function custom_message_settings_menu() {
    add_menu_page(
        'Custom Message Settings',
        'Custom Message Settings',
        'manage_options',
        'custom_message_settings',
        'custom_message_settings_page',
        'dashicons-email' // Replace with the icon of your choice
    );
}
add_action('admin_menu', 'custom_message_settings_menu');

// Render the custom message settings page
function custom_message_settings_page() {
    // Save settings if form is submitted
    if (isset($_POST['custom_message_settings_submit'])) {
        $product_ids = isset($_POST['custom_message_product_ids']) ? $_POST['custom_message_product_ids'] : array();
        update_option('custom_message_product_ids', $product_ids);
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }

    // Retrieve the saved product IDs
    $saved_product_ids = get_option('custom_message_product_ids', array());

    // Get all products
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
    );
    $products = get_posts($args);
    ?>
    <div class="wrap">
        <h1>Custom Message Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Select Products</th>
                    <td>
                        <?php foreach ($products as $product) : ?>
                            <?php $product_id = $product->ID; ?>
                            <label>
                                <input type="checkbox" name="custom_message_product_ids[]" value="<?php echo $product_id; ?>" <?php checked(in_array($product_id, $saved_product_ids)); ?>>
                                <?php echo get_the_title($product_id); ?> (ID: <?php echo $product_id; ?>)
                            </label>
                            <br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="custom_message_settings_submit" class="button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}
 
 // Enqueue the custom scripts
function enqueue_custom_scripts() {
    wp_enqueue_script('custom-script', plugins_url('/js/custom.js', __FILE__), array('jquery'), '1.0', true);

}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

// Display custom text field in product variation options
function display_custom_message_field() {
    // Get the saved product IDs from the settings
    $allowed_product_ids = get_option('custom_message_product_ids', array());

    // Get the current product ID
    global $product;
    $product_id = $product->get_id();

    // Check if the current product is in the allowed product IDs array
    if (in_array($product_id, $allowed_product_ids)) {
        ?>
        <div class="woocommerce_variation">
            <label for="custom_message"><?php _e('Custom Message', 'woocommerce'); ?></label>
            <input type="text" id="custom_message" name="custom_message" value="" maxlength="">
            <p><?php _e('Enter your custom message here', 'woocommerce'); ?></p>
            <p class="notice" style="display: none; color: red;"></p>
        </div>
        <?php
    }
}
add_action('woocommerce_before_add_to_cart_button', 'display_custom_message_field', 10);

// Save custom text field value when adding product to cart
function add_custom_message_field_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['custom_message'])) {
        $cart_item_data['custom_message'] = sanitize_text_field($_POST['custom_message']);
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_custom_message_field_to_cart_item_data', 10, 3);

// Display custom message field value in cart and checkout
/*function display_custom_message_on_cart_and_checkout($item_data, $cart_item) {
    if (isset($cart_item['custom_message'])) {
        $item_data[] = array(
            'key'     => __('Custom Message', 'woocommerce'),
            'value'   => wc_clean($cart_item['custom_message']),
            'display' => '',
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_custom_message_on_cart_and_checkout', 10, 2);*/

// Save custom message to order item data
function save_custom_message_to_order_item_data($item_id, $item, $order_id) {
    if (isset($item['custom_message'])) {
        wc_add_order_item_meta($item_id, '_custom_message', sanitize_text_field($item['custom_message']));
    }
}
add_action('woocommerce_add_order_item_meta', 'save_custom_message_to_order_item_data', 10, 3);

// Display custom message in emails
function display_custom_message_in_email($item_id, $item, $order, $plain_text) {
    $custom_message = $item->get_meta('_custom_message');

    if ($custom_message) {
        if (!$plain_text) {
            echo '<p><strong>Custom Message:</strong> ' . esc_html($custom_message) . '</p>';
        } else {
            echo "\nCustom Message: " . $custom_message;
        }
    }
}
add_action('woocommerce_order_item_meta_end', 'display_custom_message_in_email', 10, 4);
// Display custom message in cart
function display_custom_message_in_cart($product_name, $cart_item, $cart_item_key) {
    if (isset($cart_item['custom_message'])) {
        $custom_message = $cart_item['custom_message'];
        $product_name .= '<br><strong>Custom Message:</strong>';
        $product_name .= '<p>' . esc_html($custom_message) . '</p>';
    }
    return $product_name;
}
add_filter('woocommerce_cart_item_name', 'display_custom_message_in_cart', 10, 3);



// Add SKU after product name on shop and archive pages
function add_sku_after_product_name_on_shop_pages() {
    global $product;

    // Make sure we have a valid product object
    if (is_a($product, 'WC_Product')) {

        // Get the SKU code
        $sku = $product->get_sku();

        // Display product name with SKU (if SKU exists)
        if ($sku) {
            $product_title = $product->get_name() . '<span style="display: block;">' . esc_html($sku) . '</span>';
        } else {
            $product_title = $product->get_name();
        }

        // Output the modified title with SKU
        echo '<h2 class="woocommerce-loop-product__title with_sku">' . $product_title . '</h2>';
    }
}
add_action('woocommerce_shop_loop_item_title', 'add_sku_after_product_name_on_shop_pages');



// Modify product title on single product page
function add_sku_after_product_name_on_single_product_page($title, $post_id) {
    // Make sure we are in a single product page
    if (is_product()) {
        // Get the product object
        $product = wc_get_product($post_id);

        // Make sure we have a valid product object
        if (is_a($product, 'WC_Product')) {

            // Get the SKU code
            $sku = $product->get_sku();

            // Append SKU code to the product title (if SKU exists)
            if ($sku) {
                $title .= '<br> <span class="product-sku">' . esc_html($sku) . '</span>';
            }
        }
    }

    return $title;
}
add_filter('the_title', 'add_sku_after_product_name_on_single_product_page', 10, 2);
// Limit the quantity of a specific product in the cart to 5
function limit_product_quantity_in_cart($passed, $product_id, $quantity) {
    if ($quantity > 5) {
        $product = wc_get_product($product_id);
        $product_name = $product->get_name();
        
        // Display an error message
        wc_add_notice("If you'd like to order more than 5 $product_name, please contact us.", 'error');
        
        return false; // Prevent the product from being added to the cart
    }
    
    return $passed; // Allow the product to be added to the cart
}
add_filter('woocommerce_add_to_cart_validation', 'limit_product_quantity_in_cart', 10, 3);

// Enforce the quantity limit on the cart page
function enforce_cart_quantity_limit() {
    global $woocommerce;
    
    foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];
        
        if ($quantity > 5) {
            $woocommerce->cart->set_quantity($cart_item_key, 5);
            wc_add_notice("If you'd like to order more than 5 of this product, please contact us.", 'error');
        }
    }
}
add_action('woocommerce_before_cart', 'enforce_cart_quantity_limit');

function custom_variation_price_format($price, $product) {
    // If product is a variable product
    if ($product->is_type('variable')) {
        // Get the minimum variation price
        $min_price = $product->get_variation_price('min', true);

        // Return the formatted price
        return 'From ' . wc_price($min_price);
    }

    // If not a variable product, return the default price
    return $price;
}
add_filter('woocommerce_get_price_html', 'custom_variation_price_format', 10, 2);
