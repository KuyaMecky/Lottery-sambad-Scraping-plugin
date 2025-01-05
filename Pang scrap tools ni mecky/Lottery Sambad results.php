<?php
/**
 * Plugin Name: Lottery Scraper (Sambad Today's Result)
 * Description: A simple plugin to scrape a specific div from Lottery Sambad's result page and dynamically update links.
 * Version: 1.3
 * Author: Michael Tallada
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Shortcode to display the scraped content
function lottery_scraper_sambad_Result_shortcode() {
    $url = 'https://lotterysambadresult.in/';
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
        // Remove unwanted elements (optional)
        $elements_to_remove = [
            '//div[@id="quads-ad3"]',
            '//div[@class="quads-location quads-ad"]',
            '//iframe',
            '//img',
            '//div[@id="quads-ad3"]',
            '//div[@class="simplesocialbuttons simplesocial-simple-icons simplesocialbuttons_inline simplesocialbuttons-align-centered post-146 page  simplesocialbuttons-inline-no-animation"]',
            '//div[@class="quads-location quads-ad"]',
            // '//a',
            '//iframe',
            '//div[contains(@class, "8518def8ed19f5efc9b0ff7bb212644c")]',
            '//header[@class="entry-header"]',
            // '//table', // Remove all tables
            '//h3[text()="Dear Morning 1:00 PM"]', // Remove specific <h3> element
            '//h3[strong[text()="sambad 1:00 PM"]]', // Remove <h3> with specific strong text inside
            '//img',
            '//h2[@class="wp-block-heading has-text-align-center has-text-color has-link-color wp-elements-fffaa7bc5d98809a51c41eaa9c6cd3a7"]',
            // '//h2[@class="wp-block-heading"]',
            '//a[@class="maxbutton-1 maxbutton maxbutton-1-pm"]',
            '//a[@class="maxbutton-18 maxbutton maxbutton-dhankesari"]'
          
        ];

        foreach ($elements_to_remove as $query) {
            $nodes = $xpath->query($query);
            foreach ($nodes as $node) {
                if ($node && $node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

              // Update all anchor tags to the new URL
        // $anchors = $div->getElementsByTagName('a');
        // foreach ($anchors as $anchor) {
        //     $anchor->setAttribute('href', 'https://lotteryresultsambad.in/nagaland-state-lottery-result-800-pm/');
            
        // }
        // Update specific anchor tags with different URLs
            $anchors = $div->getElementsByTagName('a');
            foreach ($anchors as $anchor) {
                $anchor_text = $anchor->textContent;
            
                // Check the text content and set the URL accordingly
                if (strpos($anchor_text, 'Today 1PM') !== false) {
                    $anchor->setAttribute('href', 'https://www.lotterysambadtodays.in/nagaland-state-lottery-sambad-today-result-200-pm/');
                } elseif (strpos($anchor_text, 'Today 6PM') !== false) {
                    $anchor->setAttribute('href', 'https://www.lotterysambadtodays.in/nagaland-state-lottery-sambad-today-7-pm-result/');
                } elseif (strpos($anchor_text, 'Today 8PM') !== false) {
                    $anchor->setAttribute('href', 'https://www.lotterysambadtodays.in/lottery-sambad-today-result-08-00-pm/');
                }
            }
        // Update specific anchor tags
        $anchors = $div->getElementsByTagName('a');
        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');
            $anchor_text = trim($anchor->textContent);

            // Change links based on their text or href
            if (strpos($anchor_text, 'Lottery Sambad') !== false) {
                $anchor->setAttribute('href', 'https://www.lotteryresultsambad.in/');
            } elseif (strpos($href, 'lottery7.html') !== false) {
                $anchor->setAttribute('href', 'https://lottery7game.in/');
            }
        }

        return $dom->saveHTML($div);
    } else {
        return 'Div not found.';
    }
}

add_shortcode('Lottery_Result_sambad', 'lottery_scraper_sambad_Result_shortcode');
