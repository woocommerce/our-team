=== Our Team by WooThemes ===
Contributors: woothemes, mattyza, jameskoster
Donate link: http://woothemes.com/
Tags: teams, team members, profiles, widget, shortcode, template-tag
Requires at least: 3.8
Tested up to: 3.8.1
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display team member profiles with descriptions and links to social media using our shortcode, widget or template tag.

== Description ==

= Team Member Management =

"Our Team by WooThemes" is a clean and easy-to-use team profile management system for WordPress. Load in your team members and display their profiles via a shortcode, widget or template tag on your website. Assign team members to a user, allowing team members to manage their own description via the bio on the profile page as well as display a link to their author archive.

= Support =

Looking for a helping hand? [View plugin documentation](http://wordpress.org/plugins/our-team-by-woothemes/other_notes/). Also be sure to check out the [FAQ](https://wordpress.org/plugins/our-team-by-woothemes/faq/).

= Get Involved =

Looking to contribute code to this plugin? Go ahead and [fork the repository over at GitHub](http://github.com/woothemes/our-team/).
(submit pull requests to the latest "release-" branch)

== Usage ==

To display your team member profiles via a theme or a custom plugin, please use the following code:

`<?php do_action( 'woothemes_our_team' ); ?>`

To add arguments to this, please use any of the following arguments, using the syntax provided below:

* 'limit' 			=> 5 (the maximum number of items to display)
* 'per_row' 		=> 3 (when creating rows, how many items display in a single row?)
* 'orderby' 		=> 'menu_order' (how to order the items - accepts all default WordPress ordering options)
* 'order' 			=> 'DESC' (the order direction)
* 'id' 				=> 0 (display a specific item)
* 'slug' 			=> null (Display a specific team member)
* 'display_author' 	=> true (whether or not to display the author information)
* 'display_avatar' 	=> true (whether or not to display the author avatar)
* 'display_url' 	=> true (whether or not to display the URL information)
* 'echo' 			=> true (whether to display or return the data - useful with the template tag)
* 'size' 			=> 50 (the pixel dimensions of the image)
* 'title' 			=> '' (an optional title)
* 'before' 			=> '&lt;div class="widget widget_woothemes_our_team"&gt;' (the starting HTML, wrapping the team member profiles)
* 'after' 			=> '&lt;/div&gt;' (the ending HTML, wrapping the team member profiles)
* 'before_title' 	=> '&lt;h2&gt;' (the starting HTML, wrapping the title)
* 'after_title' 	=> '&lt;/h2&gt;' (the ending HTML, wrapping the title)
* 'category' 		=> 0 (the ID/slug of the category to filter by)

The various options for the "orderby" parameter are:

* 'none'
* 'ID'
* 'title'
* 'date'
* 'menu_order'

`<?php do_action( 'woothemes_our_team', array(
	'limit' 			=> 10,
	'display_author' 	=> false )
	);
?>`

The same arguments apply to the shortcode which is `[woothemes_our_team]` and the template tag, which is `<?php woothemes_our_team(); ?>`.

== Usage Examples ==

Adjusting the limit and image dimension, using the arguments in the three possible methods:

do_action() call:

`<?php do_action( 'woothemes_our_team', array( 'limit' => 10, 'size' => 100 ) ); ?>`

woothemes_our_team() template tag:

`<?php woothemes_our_team( array(
	'limit' 	=> 10,
	'size' 		=> 100 ),
	);
?>`

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

"Our Team by WooThemes" is a lean plugin that aims to keep it's purpose as clean and clear as possible. Thus, we don't load any preset CSS styling, to allow full control over the styling within your theme or child theme. If you simply want to apply layout (as displayed in the screenshots) you can do so with [this](https://gist.github.com/jameskoster/9954311) snippet.

= I don't need the 'Role' field, can I disable that? =

You sure can. In fact you can disable all the default fields individually. To disable the role field add:

`add_filter( 'woothemes_our_team_member_role', '__return_false' );`

To your themes functions.php file. Replace '_role' with '_url', or '_twitter' for example to disable other fields.

= I need to add another field, can I do it without touching core files? =

Yesiree! To add a new field to the backend add the following to your themes `functions.php` file:

`add_filter( 'woothemes_our_team_member_fields', 'my_new_fields' );
function my_new_fields( $fields ) {
	$fields['misc'] = array(
	    'name' 			=> __( 'Misc Detail', 'our-team-by-woothemes' ),
	    'description' 	=> __( 'Some miscellaneous detail', 'our-team-by-woothemes' ),
	    'type' 			=> 'text',
	    'default' 		=> '',
	    'section' 		=> 'info'
	);
	return $fields;
}`

Then to display the contents of that field on the frontend add the following:

`add_filter( 'woothemes_our_member_fields_display', 'my_new_fields_display' );
function my_new_fields_display( $member_fields ) {
	global $post;
	$misc = esc_attr( get_post_meta( $post->ID, '_misc', true ) );
	if ( '' != $misc ) {
		$member_fields .= '<li class="misc">' . $misc . '</li><!--/.misc-->' . "\n";
	}
	return $member_fields;
}`

Done!

= Can I change the template used to display team members in the shortcode and widget? =

You sure can! Take the following example as a guide:

`add_filter( 'woothemes_our_team_item_template', 'new_team_member_template' );

function new_team_member_template( $tpl ) {
	$tpl = '<div itemscope itemtype="http://schema.org/Person" class="%%CLASS%%">%%TITLE%% %%AVATAR%% <div id="team-member-%%ID%%"  class="team-member-text" itemprop="description">%%TEXT%% %%AUTHOR%%</div></div>';
    return $tpl;
}`

That will move the title (name/title) above the avatar/featured image.

= How can I add custom CSS classes to each team member? =

Using the `woothemes_our_team_member_class` filter. Use the following snippet to add 'new-class' to each team member. Obviously you can add logic here to add unique classes per user.

`add_filter( 'woothemes_our_team_member_class', 'new_team_member_class' );
function new_team_member_class( $css_class ) {
	$css_class .= ' new-class';
	return $css_class;
}`

= What does assigning a team member to a user do? =

If you assign a team member to a user in your WordPress install a couple of things happen:

1. A link to the team members post archive is output beneath their description. This can be disabled using the `woothemes_our_team_args` filter if you want.
2. That user now has control of what is displayed as their description. If they add some information to their bio from their profile page, that will display instead of any content you added to the team member content. If their bio is empty the standard description will appear as normal.

= Team member archives and single pages don't look good =
To cover archives and single pages would require the inclusion of a template engine which is beyond the scope of this lightweight plugin. That isn't to say it's not possible. We have a [tutorial](http://docs.woothemes.com/document/integrate-our-team-with-your-theme-for-full-archive-single-page-support/) for creating a tighter integration with your theme.

= How do I contribute? =

We encourage everyone to contribute their ideas, thoughts and code snippets. This can be done by forking the [repository over at GitHub](http://github.com/woothemes/our-team/).

== Screenshots ==

1. The team member profile management screen within the WordPress admin.

2. The team members displayed on the front-end with a little CSS to arrange in to columns.

== Upgrade Notice =

= 1.2.0 =
* Filter 'woothemes_our_member_fields' has been renamed to 'woothemes_our_team_member_fields'.
* Textdomain changed from 'woothemes-our-team' to 'our-team-by-woothemes'.

= 1.0.2 =
* Some filters have been prepended with woothemes_our_. If you're customising Our Team with filters please check these customisations before updating.

= 1.0.0 =
* Initial release. Woo!

== Changelog ==

= 1.3.0 =
* New - Filters for taxonomy args, post type args and individual team member template. Kudos @helgatheviking.
* New - You can now use the 'slug' parameter in the shortcode to display an individual team member by slug.
* Fix - WordPress users can now be unassigned from a team member.

= 1.2.0 =
* New - Team members can be sorted by meta. Kudos @helgatheviking.
* Tweak - Renamed woothemes_our_member_fields filter to woothemes_our_team_member_fields. Kudos @tlehtimaki.
* Tweak - Default args now filterable via woothemes_our_team_default_args.
* Localization - Changed textdomain to match plugin slug. Kudos @cfoellmann.

= 1.1.0 =
* New - Team members can be assigned to a WordPress user pulling a link to their posts into the team page and replacing their description with the bio according to their profile.
* 3.8 UI compatibility.

= 1.0.2 =
* Fixed typo in the team members archive slug. Kudos digitales.
* title, before_title and after_title params are now passed to shortcode.
* Team member css class is now filterable allowing custom unique clases.
* Prepended all filters woothemes_our_team_

= 1.0.1 =
* Added role & twitter args to shortcode.
* Added role option to widget.
* Display 12 team members by default (previously 5).
* Typos and other minor fixes.

= 1.0.0 =
* Initial release. Woo!
