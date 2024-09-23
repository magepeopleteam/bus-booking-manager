<?php 
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

class WBTM_Plugin_Activator {

    // Function to get page slug
    public function wbtm_get_page_by_slug($slug) {
        if ($pages = get_pages()) {
            foreach ($pages as $page) {
                if ($slug === $page->post_name) return $page;
            }
        }
        return false;
    }

    public static function activate() {
        $activator = new self();

        // Array of pages to create
        $pages_to_create = [
            [
                'slug' => 'bus-search',
                'title' => 'Bus Search Page',
                'content' => '[bus-search]',
            ],
            [
                'slug' => 'view-ticket',
                'title' => 'View Ticket',
                'content' => '[view-ticket]',
            ],
            [
                'slug' => 'bus-search-list',
                'title' => 'Bus Search result',
                'content' => '[bus-search]',
            ],
            [
                'slug' => 'bus-global-search',
                'title' => 'Global search form',
                'content' => '[bus-search-form]',
            ],
        ];

        foreach ($pages_to_create as $page) {
            if (!$activator->wbtm_get_page_by_slug($page['slug'])) {
                $new_page = array(
                    'post_type' => 'page',
                    'post_name' => sanitize_title($page['slug']), // Sanitize slug
                    'post_title' => sanitize_text_field($page['title']), // Sanitize title
                    'post_content' => wp_kses_post($page['content']), // Escape content
                    'post_status' => 'publish',
                );
                wp_insert_post($new_page);
            }
        }
    }
}
