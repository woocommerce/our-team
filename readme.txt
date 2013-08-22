=== Our Team by WooThemes ===
Contributors: woothemes, mattyza, jameskoster
Donate link: http://woothemes.com/
Tags: teams, team members, profiles, widget, shortcode, template-tag
Requires at least: 3.4.2
Tested up to: 3.6.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show off what your customers are saying about your business and how great they say you are, using our shortcode, widget or template tag.

== Description ==

"Our Team by WooThemes" is a clean and easy-to-use team profile management system for WordPress. Load in your team members and display their profiles via a shortcode, widget or template tag on your website.

Looking for a helping hand? [View plugin documentation](http://wordpress.org/plugins/our-team-by-woothemes/other_notes/).

Looking to contribute code to this plugin? [Fork the repository over at GitHub](http://github.com/woothemes/our-team/).
(submit pull requests to the latest "release-" branch)

== Usage ==

To display your team member profiles via a theme or a custom plugin, please use the following code:

`<?php do_action( 'woothemes_our_team' ); ?>`

To add arguments to this, please use any of the following arguments, using the syntax provided below:

* 'limit' => 5 (the maximum number of items to display)
* 'per_row' => 3 (when creating rows, how many items display in a single row?)
* 'orderby' => 'menu_order' (how to order the items - accepts all default WordPress ordering options)
* 'order' => 'DESC' (the order direction)
* 'id' => 0 (display a specific item)
* 'display_author' => true (whether or not to display the author information)
* 'display_avatar' => true (whether or not to display the author avatar)
* 'display_url' => true (whether or not to display the URL information)
* 'echo' => true (whether to display or return the data - useful with the template tag)
* 'size' => 50 (the pixel dimensions of the image)
* 'title' => '' (an optional title)
* 'before' => '&lt;div class="widget widget_woothemes_our_team"&gt;' (the starting HTML, wrapping the team member profiles)
* 'after' => '&lt;/div&gt;' (the ending HTML, wrapping the team member profiles)
* 'before_title' => '&lt;h2&gt;' (the starting HTML, wrapping the title)
* 'after_title' => '&lt;/h2&gt;' (the ending HTML, wrapping the title)
* 'category' => 0 (the ID/slug of the category to filter by)

The various options for the "orderby" parameter are:

* 'none'
* 'ID'
* 'title'
* 'date'
* 'menu_order'

`<?php do_action( 'woothemes_our_team', array( 'limit' => 10, 'display_author' => false ) ); ?>`

The same arguments apply to the shortcode which is `[woothemes_our_team]` and the template tag, which is `<?php woothemes_our_team(); ?>`.

== Usage Examples ==

Adjusting the limit and image dimension, using the arguments in the three possible methods:

do_action() call:

`<?php do_action( 'woothemes_our_team', array( 'limit' => 10, 'size' => 100 ) ); ?>`

woothemes_our_team() template tag:

`<?php woothemes_our_team( array( 'limit' => 10, 'size' => 100 ) ); ?>`

[woothemes_our_team] shortcode:

`[woothemes_our_team limit="10" size="100"]`

== Installation ==

Installing "Our Team by WooThemes" can be done either by searching for "Our Team by WooThemes" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org.
1. Upload the ZIP file through the "Plugins > Add New > Upload" screen in your WordPress dashboard.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action( 'woothemes_our_team' ); ?>` in your templates, or use the provided widget or shortcode.

== Frequently Asked Questions ==

= The plugin looks unstyled when I activate it. Why is this? =

"Our Team by WooThemes" is a lean plugin that aims to keep it's purpose as clean and clear as possible. Thus, we don't load any preset CSS styling, to allow full control over the styling within your theme or child theme.

= How do I contribute? =

We encourage everyone to contribute their ideas, thoughts and code snippets. This can be done by forking the [repository over at GitHub](http://github.com/woothemes/our-team/).

== Screenshots ==

1. The team member profile management screen within the WordPress admin.

== Upgrade Notice =

= 1.0.0 =
* Initial release. Woo!

== Changelog ==

= 1.0.0 =
* Initial release. Woo!
