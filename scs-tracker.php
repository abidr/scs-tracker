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
Generate the Tracker php file to show the tracking to the user.
 */

$scs_tracker_FileName = 'tracker.php';
$scs_tracker_FileContent = '<!DOCTYPE HTML>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Package Tracker</title>
        <style type="text/css">
            @import url("https://fonts.googleapis.com/css?family=Lato:300,400,700,900");
            body {
                background:#2c3e50;
                font-family: "Lato", sans-serif;
            }

            .wrapper {
                width:85%;
                margin:60px auto;
                padding:30px;
                background:#fff;
            }

            .logo {
                max-width: 100%;
                width: 200px;
                padding: 30px 0;
                margin:0 auto;
                text-align:center;
            }

            .scs-tracker {
                padding: 20px 0;
            }

            .cnnumber {
                border: 1px solid #333;
                padding:10px;
            }

            .scs-tracker p {
                border-bottom: 1px solid #ddd;
                padding-bottom:10px;
            }
            .scs-tracker p i {
                color: #666;
            }
            .scs-tracker p:last-child {
                border-bottom:0px;
            }

            .copyright {
                color:#fff;
                text-align:center;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <center>
                <h1>Package Tracking Service</h1>
                <hr />
            </center>
            <div class="scs-tracker">
                <h3 class="cnnumber">CN Number: <span></span>, <i>Sundarban Courier Service</i></h3>
                <p class="bookingDate">Date of Booking: <span></span></p>
                <p class="bookingBranch">Booking From: <span></span></p>
                <p class="bookingDestination">Destination: <span></span></p>
                <p class="cnstatus">Status: <span></span>, <i class="statusDate"></i></p>
            </div>
        </div>
        <script src="wp-includes/js/jquery/jquery.js"></script>
        <script>
            var url_string = window.location.href; //window.location.href
            var url = new URL(url_string);
            var cn_get = url.searchParams.get("cn");
            var cn = cn_get;
            jQuery.get("http://103.3.227.172:4040/Default.aspx?Page=SearchByCNNumber&CN_Number=" + cn, function(response) {
                var challenges = jQuery(response).find("#midContent table:last-child").html()
                var bookingDate = jQuery(challenges).find("#ctl00_lblBookingDate").html()
                var bookingBranch = jQuery(challenges).find("#ctl00_lblBookingBranch").html()
                var bookingDestination = jQuery(challenges).find("#ctl00_lblDestination").html()
                var statusDate = jQuery(challenges).find("#ctl00_gvOrders tbody tr:nth-child(2) td:nth-child(3)").html()
                var cnstatus = jQuery(challenges).find("#ctl00_gvOrders tbody tr:nth-child(2) td:nth-child(5)").html()
                
                document.querySelector(".cnnumber span").innerHTML = cn
                document.querySelector(".bookingDate span").innerHTML = bookingDate
                document.querySelector(".bookingBranch span").innerHTML = bookingBranch
                document.querySelector(".bookingDestination span").innerHTML = bookingDestination
                document.querySelector(".statusDate").innerHTML = statusDate
                document.querySelector(".cnstatus span").innerHTML = cnstatus
            });      
        </script>
    </body>
</html>';

file_put_contents($scs_tracker_FileName, $scs_tracker_FileContent);


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
    <table class="woocommerce-table shop_table gift_info">
        <tbody>
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
                    <?php echo __('Track:', 'scs-tracker'); ?>
                </th>
                <td>
                    <?php if($scs_tracker_cn_number): 
                    $scs_tracker_site_url_main = site_url( '' );
                    $scs_tracker_find = array( 'http://', 'https://' );
                    $scs_tracker_replace = '';
                    $scs_tracker_site_url = str_replace( $scs_tracker_find, $scs_tracker_replace, $scs_tracker_site_url_main );
                    ?>
                    <a target="_scs_tracker" href="http://<?php echo $scs_tracker_site_url; ?>/tracker.php?cn=<?php echo $scs_tracker_cn_number; ?>" class="woocommerce-button button view">
                        <?php echo __('Track Now', 'scs-tracker'); ?>
                    </a>
                    <?php else: ?>
                    <p>
                        <?php echo __('----', 'scs-tracker'); ?>
                    </p>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
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

    if ( isset( $_POST['scs_tracker_service_cn_number'] ) )
        update_post_meta( $post_id, 'scs_tracker_service_cn_number', $_POST['scs_tracker_service_cn_number'] );
}
add_action( 'save_post', 'scs_tracker_service_save' );


