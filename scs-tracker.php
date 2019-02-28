<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://abidr.me
 * @since             1.0.0
 * @package           Scs_Tracker
 *
 * @wordpress-plugin
 * Plugin Name:       Sundarban Courier Tracker for WooCommerce
 * Plugin URI:        http://abidr.me/scs-tracker
 * Description:       Sundarban Courier Tracker allows you to add a tracking service for your customer to track their parcel sent with Sundarban Courier Service.
 * Version:           1.0.0
 * Author:            Abidur Rahman Abid
 * Author URI:        http://abidr.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       scs-tracker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SCS_TRACKER_VERSION', '1.0.0' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-scs-tracker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

/*
Add a button to track from My Orders Page
 */

add_action( 'woocommerce_view_order', 'scs_tracker_view_order', 10 );
 
function scs_tracker_view_order( $order_id ){  

$scs_tracker_order = wc_get_order( $order_id );
$scs_tracker_order_id = $scs_tracker_order->get_id();
$scs_tracker_cn_number = get_post_meta($scs_tracker_order_id, 'scs_tracker_service_cn_number', true); ?>
<br>
    <h2><?php echo __('Tracking', 'scs-tracker'); ?></h2>

    <div class="scs-tracker-get-info" style="display:none;">
        <?php

        if($scs_tracker_cn_number) {
            $scs_tracker_args = "&b=4&f=norefer";
            $scs_tracker_url = "https://proxurf.com/browse.php?u=http%3A%2F%2F103.3.227.172%3A4040%2FDefault.aspx%3FPage%3DSearchByCNNumber%26CN_Number%3D";
            $scs_tracker_cn_plus_url = $scs_tracker_url . $scs_tracker_cn_number;
            $scs_tracker_main_content = $scs_tracker_cn_plus_url . $scs_tracker_args;
            $scs_tracker_response = wp_remote_get( $scs_tracker_main_content );
            $scs_tracker_body = wp_remote_retrieve_body( $scs_tracker_response );
            echo $scs_tracker_body;
        }
        ?>
    </div>
    
    <table class="woocommerce-table shop_table gift_info">
        <tbody>
            <?php if($scs_tracker_cn_number): ?>
            <tr>
                <th>
                    <?php echo __('CN Number:', 'scs-tracker'); ?>
                </th>
                <td>
                    <?php if($scs_tracker_cn_number): ?>
                    <p><?php echo $scs_tracker_cn_number; ?></p>
                    <?php else: ?>
                    <p>
                        <?php echo __('Your order is not booked yet.', 'scs-tracker'); ?>
                    </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo __('Booking Date:', 'scs-tracker'); ?>
                </th>
                <td>
                    <p class="bookingDate"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo __('Booking From:', 'scs-tracker'); ?>
                </th>
                <td>
                    <p class="bookingBranch"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo __('Destination:', 'scs-tracker'); ?>
                </th>
                <td>
                    <p class="bookingDestination"></p>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo __('Status:', 'scs-tracker'); ?>
                </th>
                <td>
                    <p class="cnstatus"></p>
                    <span class="statusDate"></span>
                </td>
            </tr>
            <?php else: ?>
            <tr>
                <th>
                    <?php echo __('Your order is not booked yet', 'scs-tracker'); ?>
                </th>
            </tr>
            <?php endif; ?>
        </tbody>
        <script>
                var challenges = jQuery('.scs-tracker-get-info').find("#midContent table:last-child").html()
                var bookingDate = jQuery(challenges).find("#ctl00_lblBookingDate").html()
                var bookingBranch = jQuery(challenges).find("#ctl00_lblBookingBranch").html()
                var bookingDestination = jQuery(challenges).find("#ctl00_lblDestination").html()
                var statusDate = jQuery(challenges).find("#ctl00_gvOrders tbody tr:nth-child(2) td:nth-child(3)").html()
                var cnstatus = jQuery(challenges).find("#ctl00_gvOrders tbody tr:nth-child(2) td:nth-child(5)").html()
                
                // document.querySelector(".cnnumber span").innerHTML = cn
                document.querySelector(".bookingDate").innerHTML = bookingDate
                document.querySelector(".bookingBranch").innerHTML = bookingBranch
                document.querySelector(".bookingDestination").innerHTML = bookingDestination
                document.querySelector(".statusDate").innerHTML = statusDate
                document.querySelector(".cnstatus").innerHTML = cnstatus    
        </script>
</table>
    
<?php }

/* Add a metabox to add CN Number */

function scs_tracker_service_get_meta( $value ) {
    global $post;

    $field = get_post_meta( $post->ID, $value, true );
    if ( ! empty( $field ) ) {
        return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
    } else {
        return false;
    }
}

function scs_tracker_service_add_meta_box() {
    add_meta_box(
        'scs_tracker_service-scs-tracker-service',
        __( 'SCS Tracker Service', 'scs_tracker_service' ),
        'scs_tracker_service_html',
        'shop_order',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'scs_tracker_service_add_meta_box' );

function scs_tracker_service_html( $post) {
    wp_nonce_field( '_scs_tracker_service_nonce', 'scs_tracker_service_nonce' ); ?>

    <p>
        <label for="scs_tracker_service_cn_number"><?php _e( 'CN Number', 'scs_tracker_service' ); ?></label><br>
        <input type="text" name="scs_tracker_service_cn_number" id="scs_tracker_service_cn_number" value="<?php echo scs_tracker_service_get_meta( 'scs_tracker_service_cn_number' ); ?>">
    </p><?php
}

function scs_tracker_service_save( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['scs_tracker_service_nonce'] ) || ! wp_verify_nonce( $_POST['scs_tracker_service_nonce'], '_scs_tracker_service_nonce' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $scs_tracker_cn_number_sanitize = sanitize_text_field($_POST['scs_tracker_service_cn_number']);

    if ( isset( $scs_tracker_cn_number_sanitize ) )
        update_post_meta( $post_id, 'scs_tracker_service_cn_number', $scs_tracker_cn_number_sanitize );
}
add_action( 'save_post', 'scs_tracker_service_save' );


