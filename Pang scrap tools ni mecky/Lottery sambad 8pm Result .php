<?php
/**
 * Plugin Name: Lottery Scraper (Nagaland8pm Todays Result)
 * Description: A simple plugin to scrape a specific div from Lottery Sambad's Nagaland8pm Result result page.
 * Version: 1.2
 * Author: Michael Tallada
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Shortcode to display the scraped content
function lottery_scraper_Nagaland8pm_Result_shortcode() {
    // Update the URL to the new page
    $url = 'https://lotterysambad.one/nagaland-state-lottery-result-8-00-pm/';
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
    // Assuming the results are in a div with class 'inside-article'
    $div = $xpath->query('//div[@class="inside-article"]')->item(0);
    
    if ($div) {
        // List of ids or classes of elements you want to remove
        $elements_to_remove = [
            '//div[@id="quads-ad3"]',
            '//div[@class="simplesocialbuttons simplesocial-simple-icons simplesocialbuttons_inline simplesocialbuttons-align-centered post-146 page  simplesocialbuttons-inline-no-animation"]',
            '//div[@class="quads-location quads-ad"]',
            '//a',
            '//div[@class="simplesocialbuttons simplesocial-simple-icons simplesocialbuttons_inline simplesocialbuttons-align-centered post-65 page  simplesocialbuttons-inline-no-animation"]',
            '//header[@class="entry-header"]'
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
                $img->setAttribute('src', 'https://lotterysambad.one/nagaland-state-lottery-result-8-00-pm/' . ltrim($src, '/'));
            }
        }

        // Helper function to style tables
        if (!function_exists('style_table')) {
            function style_table($specificTable) {
                if ($specificTable) {
                    // Apply styles to the table
                    $specificTable->setAttribute('style', 'color: #000; font-size: 24px; text-align: center;');

                    // Apply styles to rows and columns
                    $rows = $specificTable->getElementsByTagName('tr');
                    foreach ($rows as $row) {
                        $row->setAttribute('style', 'color:#000; border-bottom: 1px solid #ddd;');
                    }

                    $columns = $specificTable->getElementsByTagName('th');
                    foreach ($columns as $column) {
                        if ($column->getAttribute('class') == 'column-1') {
                            $column->setAttribute('style', 'font-weight: bold; color:#000;');
                        }
                        if ($column->getAttribute('class') == 'column-2') {
                            $column->setAttribute('style', 'font-style: italic; color:#000;');
                        }
                        if ($column->getAttribute('class') == 'column-3') {
                            $column->setAttribute('style', 'font-style: italic; color:#000;');
                        }
                        if ($column->getAttribute('class') == 'column-4') {
                            $column->setAttribute('style', 'font-style: italic; color:#000;');
                        }
                    }
                }
            }
        }

        // Style tablepress-1 if it exists
        $specificTable1 = $xpath->query('//table[@id="tablepress-1"]')->item(0);
        style_table($specificTable1);
        // Style tablepress-2 if it exists
        $specificTable2 = $xpath->query('//table[@id="tablepress-2"]')->item(0);
        style_table($specificTable2);
        // Style tablepress-3 if it exists
        $specificTable3 = $xpath->query('//table[@id="tablepress-3"]')->item(0);
        style_table($specificTable3);
        // Style tablepress-4 if it exists
        $specificTable4 = $xpath->query('//table[@id="tablepress-4"]')->item(0);
        style_table($specificTable4);

        return $dom->saveHTML($div);
    } else {
        return 'Div not found.';
    }
}

add_shortcode('Lottery_Result_Nagaland8pm', 'lottery_scraper_Nagaland8pm_Result_shortcode');
