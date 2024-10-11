<?php

/**
 * Plugin Name: App Store Details Fetcher
 * Description: Fetch and display app details from the Apple App Store using the iTunes Search API.
 * Version: 1.2
 * Author: Arian Darvishi
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Shortcode to display app details
function fetch_app_store_details($atts)
{
    $atts = shortcode_atts(
        array(
            'id' => '', // App ID
        ),
        $atts,
        'app_store_details'
    );

    if (empty($atts['id'])) {
        return 'Please provide an App Store app ID.';
    }

    $app_id = sanitize_text_field($atts['id']);
    $response = wp_remote_get("https://itunes.apple.com/lookup?id={$app_id}");

    if (is_wp_error($response)) {
        return 'Unable to fetch app details.';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (empty($data->results)) {
        return 'App not found.';
    }

    $app = $data->results[0];

    // Prepare app details
    $app_name = esc_html($app->trackName);
    $app_icon = esc_url($app->artworkUrl100);
    $app_price = !empty($app->formattedPrice) ? esc_html($app->formattedPrice) : 'Free';
    $app_url = esc_url($app->trackViewUrl);
    $developer = esc_html($app->artistName);
    $app_size = isset($app->fileSizeBytes) ? size_format($app->fileSizeBytes) : 'Unknown';
    $app_version = esc_html($app->version);
    $app_category = esc_html($app->primaryGenreName);

    // Get screenshots
    $screenshots = isset($app->screenshotUrls) ? $app->screenshotUrls : [];

    // Generate HTML output using your existing structure
    $output = '<div class="app-details-holder">';
    $output .= '<div class="single-app-image-holder">';
    $output .= '<img decoding="async" width="80" height="80" src="' . $app_icon . '" alt="' . $app_name . '">';
    $output .= '</div>'; // .single-app-image-holder

    $output .= '<div class="app-details-left-holder">';
    $output .= '<div class="app-name-holder">';
    $output .= '<h3 class="app-name">' . $app_name . '</h3>';
    $output .= '<span class="app-author">سازنده : ' . $developer . '</span>';
    $output .= '</div>'; // .app-name-holder
    $output .= '<a href="' . $app_url . '" class="itunes-link" target="_blank">نمایش در App Store</a>';
    $output .= '</div>'; // .app-details-left-holder

    $output .= '<div class="app-meta-holder clearfix">';
    $output .= '<div class="app-meta-right">';
    $output .= '<span class="app-meta">حجم : ' . $app_size . '</span>';
    $output .= '<span class="app-meta">نسخه : ' . $app_version . '</span>';
    $output .= '<span class="app-meta">دسته‌بندی : ' . $app_category . '</span>';
    $output .= '<span class="app-meta">قیمت : ' . $app_price . '</span>';
    $output .= '</div>'; // .app-meta-right
    $output .= '</div>'; // .app-meta-holder

    // Add screenshots section if screenshots are available
    if (! empty($screenshots)) {
        $output .= '<section class="shots-holder">';
        $output .= '<h4 class="shots-title"><i class="fa fa-picture-o"></i>تصاویری از محیط برنامه </h4>';
        $output .= '<div class="shots" style="overflow: hidden; outline: none;" tabindex="0">';
        $output .= '<div class="shots-inner clearfix">';
        foreach ($screenshots as $screenshot) {
            $output .= '<img decoding="async" src="' . esc_url($screenshot) . '" alt="App Screenshot">';
        }
        $output .= '</div>'; // .shots-inner
        $output .= '</div>'; // .shots
        $output .= '</section>'; // .shots-holder
    }

    $output .= '</div>'; // .app-details-holder

    return $output;
}

// Register shortcode
add_shortcode('app_store_details', 'fetch_app_store_details');

// Enqueue styles for the plugin
function app_store_details_enqueue_styles()
{
    wp_enqueue_style('app-store-details', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('wp_enqueue_scripts', 'app_store_details_enqueue_styles');
