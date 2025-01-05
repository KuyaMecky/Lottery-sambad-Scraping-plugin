<?php
/**
 * Plugin Name: Lottery Sambad Old Result Scraper
 * Description: A plugin to scrape specific content from Lottery Sambad's old results page and display selected tags.
 * Version: 1.1
 * Author: Michael Tallada
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function lottery_sambad_old_result_shortcode() {
    $url = 'https://lotterysambadresult.in/oldresult.html';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return 'Failed to fetch data.';
    }

    $body = wp_remote_retrieve_body($response);

    // Load HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($body);

    // Initialize XPath for querying
    $xpath = new DOMXPath($dom);

    // Remove the iframe
    $iframeNodes = $xpath->query('//iframe');
    foreach ($iframeNodes as $iframe) {
        $iframe->parentNode->removeChild($iframe);
    }

    // Remove the unwanted div with images
    $divNodes = $xpath->query('//div[contains(@class, "I8Y5R")]');
    foreach ($divNodes as $div) {
        $div->parentNode->removeChild($div);
    }

    // Query the target div
    $div = $xpath->query('//div[@class="inside-article"]')->item(0);
    
    if ($div) {
        // Create a new DOMDocument to store the filtered content
        $filteredDom = new DOMDocument();

        // Import the target div into the new DOMDocument
        $importedDiv = $filteredDom->importNode($div, true);
        $filteredDom->appendChild($importedDiv);

        // Initialize XPath for filtered DOM
        $xpathFiltered = new DOMXPath($filteredDom);

        // Remove all nodes except <span> and <figure> with specified classes
        $allowedNodes = [
            '//span[@class="gb-button gb-button-3705bf5d gb-button-text"]/strong',
            '//figure[@class="wp-block-table"]/table[@class="has-fixed-layout"]'
        ];

        // Select all nodes that are not in allowedNodes
        $allNodes = $xpathFiltered->query('//*');
        foreach ($allNodes as $node) {
            $match = false;
            foreach ($allowedNodes as $allowedNode) {
                if ($xpathFiltered->query($allowedNode, $node)->length > 0) {
                    $match = true;
                    break;
                }
            }
            // Remove nodes that are not in the allowed list
            if (!$match && $node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }

        // Add styles for the table
        $tableStyles = '<style>
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 8px;
                text-align: center;
            }
            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
        </style>';

        // Return the table with styles
        return $tableStyles . $filteredDom->saveHTML();
    } else {
        return 'Content not found.';
    }
    // Update specific anchor tags with different URLs
            $anchors = $div->getElementsByTagName('a');
            foreach ($anchors as $anchor) {
                $anchor_text = $anchor->textContent;
            
                // Check the text content and set the URL accordingly
                if (strpos($anchor_text, '1:00 PM') !== false) {
                    $anchor->setAttribute('href', 'https://www.lotterysambadtodays.in/nagaland-state-lottery-sambad-today-result-200-pm/');
                } elseif (strpos($anchor_text, '06:00 PM') !== false) {
                    $anchor->setAttribute('href', 'https://www.lotterysambadtodays.in/nagaland-state-lottery-sambad-today-7-pm-result/');
                } elseif (strpos($anchor_text, '08:00 PM') !== false) {
                    $anchor->setAttribute('href', 'https://www.lotterysambadtodays.in/lottery-sambad-today-result-08-00-pm/');
                }
            }
}


// Register the shortcode
add_shortcode('lottery_sambad_old_result', 'lottery_sambad_old_result_shortcode');
