<?php
/**
 * @package KalWPScheduler
 */
/*
Plugin Name: Kal Wordpress Scheduler
Plugin URI: https://github.com/kalmarsh
Description: This is a simple Wordpress Post Scheduler. Create a draft post that matches the name of an existing post. Make all the changes you want. Schedule the post to publish at a later time and once it's published. The old post will be marked draft.
Version: 1.0.0
Author: Automattic
Author URI: https://github.com/kalmarsh
License: GPLv2 or later
Text Domain: kalmarsh
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/

function automation_on_post_status( $new_status, $old_status, $new_post ) {
    if ( 'publish' === $new_status && 'future' === $old_status ) {
        // Do something if the post has been transitioned from the future (scheduled) post to publish.

        global $wpdb;

        //fetch the post that's already been published (OLD)
        $old_post = $wpdb->get_row("
SELECT      *
FROM        $wpdb->posts
WHERE       $wpdb->posts.post_title like '$new_post->post_title'
AND         $wpdb->posts.post_type='post'
AND         $wpdb->posts.post_status='publish'
AND         $wpdb->posts.ID != $new_post->ID");

        if (!is_null($old_post)) {
            $wpdb->update('wp_posts', [
                'post_status' => 'draft', 'post_name' => $old_post->post_name . '-2'], ['ID' => $old_post->ID]);

            //update the link of the new post to match the old
            $wpdb->update('wp_posts', [
                'post_name' => $old_post->post_name,
                'post_date' => $old_post->post_date,
                'post_date_gmt' => $old_post->post_date_gmt
            ], ['ID' => $new_post->ID]);

        }
    }
}
add_action( 'transition_post_status', 'automation_on_post_status', 10, 3 );
