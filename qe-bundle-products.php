<?php
/**
 * Plugin Name: Qe Bundle Products
 * Description: Create and manage bundle products in WooCommerce.
 * Version: 1.0.0
 * Author: QeWebby
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Qe_Bundle_Products {
    public function __construct() {
        add_action( 'init', [ $this, 'register_bundle_product_type' ] );
        add_filter( 'product_type_selector', [ $this, 'add_bundle_product_type' ] );
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_bundle_fields' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_bundle_fields' ] );
        add_filter( 'woocommerce_add_to_cart_handler', [ $this, 'handle_bundle_add_to_cart' ], 10, 2 );
    }

    // Register product type "bundle"
    public function register_bundle_product_type() {
        class WC_Product_QE_Bundle extends WC_Product {
            public function get_type() {
                return 'qe_bundle';
            }
        }
    }

    // Add to product type dropdown
    public function add_bundle_product_type( $types ) {
        $types['qe_bundle'] = __( 'Qe Bundle', 'qe-bundle-products' );
        return $types;
    }

    // Add admin fields to select bundled products
    public function add_bundle_fields() {
        global $post;
        echo '<div class="options_group show_if_qe_bundle">';
        woocommerce_wp_textarea_input( [
            'id' => '_qe_bundle_items',
            'label' => __( 'Bundle Products (IDs, comma separated)', 'qe-bundle-products' ),
            'desc_tip' => true,
            'description' => __( 'Enter product IDs separated by commas', 'qe-bundle-products' ),
        ] );
        echo '</div>';
    }

    // Save bundle field
    public function save_bundle_fields( $post_id ) {
        $bundle_ids = isset( $_POST['_qe_bundle_items'] ) ? sanitize_text_field( $_POST['_qe_bundle_items'] ) : '';
        update_post_meta( $post_id, '_qe_bundle_items', $bundle_ids );
    }

    // Add all bundled products to cart
    public function handle_bundle_add_to_cart( $handler, $product_id ) {
        $product = wc_get_product( $product_id );
        if ( $product && $product->get_type() === 'qe_bundle' ) {
            $bundle_ids = get_post_meta( $product_id, '_qe_bundle_items', true );
            if ( ! empty( $bundle_ids ) ) {
                $ids = array_map( 'trim', explode( ',', $bundle_ids ) );
                foreach ( $ids as $id ) {
                    WC()->cart->add_to_cart( $id );
                }
            }
            return 'success';
        }
        return $handler;
    }
}

new Qe_Bundle_Products();
