<?php
/**
 * Plugin Name: Lottery Scraper (Nagaland home page Todays Result)
 * Description: A simple plugin to scrape a specific div from Lottery Sambad's nagaland Result result page.
 * Version: 1.2
 * Author: Michael Tallada
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Shortcode to display the scraped content
function lottery_scraper_Dhankesari_Result_shortcode() {
    // Update the URL to the new page
    $url = 'https://lotterysambad.one/dhankesari/';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return 'Failed to fetch data.';
    }

    $body = wp_remote_retrieve_body($response);
    
    // Load HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($body);
    
    // Get the specific div by class or ID (adjust based on the page structure)
    $xpath = new DOMXPath($dom);
    $div = $xpath->query('//div[@class="inside-article"]')->item(0);
    
    if ($div) {
        // List of ids or classes of elements you want to remove
        $elements_to_remove = [
            '//div[@id="quads-ad3"]',
            '//div[@class="simplesocialbuttons simplesocial-simple-icons simplesocialbuttons_inline simplesocialbuttons-align-centered post-146 page  simplesocialbuttons-inline-no-animation"]',
            '//div[@class="quads-location quads-ad"]',
            '//a',
            '//iframe',
            '//div[contains(@class, "8518def8ed19f5efc9b0ff7bb212644c")]',
            // '//header[@class="entry-header"]',
            '//table', // Remove all tables
            '//h3[text()="Dear Morning 1:00 PM"]',
            '//h3[text()="Dear Day 6:00 PM"]',
            '//h3[text()="Dear Evening 8:00 PM"]',
            '//p[text()="Dhankesari Today’s Result of Nagaland State Lottery – 1:00 PM Morning, 6:00 PM Day and 8:00 PM Evening / Night Live on Dhankesari Dear Lottery Sambad."]',
            '//p[text()=" The price chart above is for Dear Morning Lottery. The number of prizes for Dear Evening Lottery is: 1, 259, 2600, 26000, 26000, 260000 and Dear Evening Lottery is 1, 699, 7000, 70000, 70000, 700000 in the same order. The price amount is the same."]',
            '//h3[text()="Nagaland State Lottery Prize"]',
            '//h3',
            // '//img[@class="aligncenter size-full wp-image-42024"]', //unang image to na nakaremove,
            '//h3[strong[text()="Dhankesari 1:00 PM"]]',
            '//header[@class="entry-header"]'
            // '//img[@class="aligncenter size-full wp-image-42036"]', // pangalawang imahe na naka tago
            // '//h3[strong[text()="Dhankesari 8:00 PM"]]',
            // '//h3[strong[text()="Dhankesari 6:00 PM"]]',
            // '//h3[strong[text()="Todays Result"]]'
            
        ];

        // Remove unwanted elements
        foreach ($elements_to_remove as $query) {
            $nodes = $xpath->query($query);
            foreach ($nodes as $node) {
                if ($node && $node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        // Update image URLs to absolute paths
        $images = $div->getElementsByTagName('img');
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            // Ensure src is absolute
            if (strpos($src, 'http') !== 0) {
                $img->setAttribute('src', 'https://lotterysambad.one/' . ltrim($src, '/'));
            }
        }

        return $dom->saveHTML($div);
    } else {
        return 'Div not found.';
    }
}

add_shortcode('Lottery_Result_Dhankesari', 'lottery_scraper_Dhankesari_Result_shortcode');
