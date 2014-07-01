<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'woothemes_get_our_team' ) ) {
/**
 * Wrapper function to get the team members from the Woothemes_Our_Team class.
 * @param  string/array $args  Arguments.
 * @since  1.0.0
 * @return array/boolean       Array if true, boolean if false.
 */
function woothemes_get_our_team ( $args = '' ) {
	global $woothemes_our_team;
	return $woothemes_our_team->get_our_team( $args );
} // End woothemes_get_our_team()
}

/**
 * Enable the usage of do_action( 'woothemes_our_team' ) to display team members within a theme/plugin.
 *
 * @since  1.0.0
 */
add_action( 'woothemes_our_team', 'woothemes_our_team' );

if ( ! function_exists( 'woothemes_our_team' ) ) {
/**
 * Display or return HTML-formatted team members.
 * @param  string/array $args  Arguments.
 * @since  1.0.0
 * @return string
 */
function woothemes_our_team ( $args = '' ) {
	global $post, $more;

	$defaults = apply_filters( 'woothemes_our_team_default_args', array(
		'limit' 					=> 12,
		'per_row' 					=> null,
		'orderby' 					=> 'menu_order',
		'order' 					=> 'DESC',
		'id' 						=> 0,
		'slug'						=> null,
		'display_author' 			=> true,
		'display_additional' 		=> true,
		'display_avatar' 			=> true,
		'display_url' 				=> true,
		'display_twitter' 			=> true,
		'display_author_archive'	=> true,
		'display_role'	 			=> true,
		'contact_email'				=> true,
		'tel'						=> true,
		'effect' 					=> 'fade', // Options: 'fade', 'none'
		'pagination' 				=> false,
		'echo' 						=> true,
		'size' 						=> 250,
		'title' 					=> '',
		'before' 					=> '<div class="widget widget_woothemes_our_team">',
		'after' 					=> '</div>',
		'before_title' 				=> '<h2>',
		'after_title' 				=> '</h2>',
		'category' 					=> 0
	) );

	$args = wp_parse_args( $args, $defaults );

	// Allow child themes/plugins to filter here.
	$args = apply_filters( 'woothemes_our_team_args', $args );
	$html = '';

	do_action( 'woothemes_our_team_before', $args );

		// The Query.
		$query = woothemes_get_our_team( $args );

		// The Display.
		if ( ! is_wp_error( $query ) && is_array( $query ) && count( $query ) > 0 ) {

			$class = '';

			if ( is_numeric( $args['per_row'] ) ) {
				$class .= ' columns-' . intval( $args['per_row'] );
			}

			if ( 'none' != $args['effect'] ) {
				$class .= ' effect-' . $args['effect'];
			}

			$html .= $args['before'] . "\n";
			if ( '' != $args['title'] ) {
				$html .= html_entity_decode( $args['before_title'] ) . esc_html( $args['title'] ) . html_entity_decode( $args['after_title'] ) . "\n";			}
			$html .= '<div class="team-members component' . esc_attr( $class ) . '">' . "\n";

			// Begin templating logic.
			$tpl = '<div itemscope itemtype="http://schema.org/Person" class="%%CLASS%%">%%AVATAR%% %%TITLE%% <div id="team-member-%%ID%%"  class="team-member-text" itemprop="description">%%TEXT%% %%AUTHOR%%</div></div>';
			$tpl = apply_filters( 'woothemes_our_team_item_template', $tpl, $args );

			$count = 0;
			foreach ( $query as $post ) {
				$count++;
				$template = $tpl;

				$css_class = apply_filters( 'woothemes_our_team_member_class', $css_class = 'team-member' );
				if ( ( is_numeric( $args['per_row'] ) && ( 0 == ( $count - 1 ) % $args['per_row'] ) ) || 1 == $count ) { $css_class .= ' first'; }
				if ( ( is_numeric( $args['per_row'] ) && ( 0 == $count % $args['per_row'] ) ) ) { $css_class .= ' last'; }

				// Add a CSS class if no image is available.
				if ( isset( $post->image ) && ( '' == $post->image ) ) {
					$css_class .= ' no-image';
				}

				setup_postdata( $post );

				$title 		= '';
				$title_name = '';

				// If we need to display the title, get the data
				if ( ( get_the_title( $post ) != '' ) && true == $args['display_author'] ) {
					$title .= '<h3 itemprop="name" class="member">';

					if ( true == $args['display_url'] && '' != $post->url && apply_filters( 'woothemes_our_team_member_url', true ) ) {
						$title .= '<a href="' . esc_url( $post->url ) . '">' . "\n";
					}

					$title_name = get_the_title( $post );

					$title .= $title_name;

					if ( true == $args['display_url'] && '' != $post->url && apply_filters( 'woothemes_our_team_member_url', true ) ) {
						$title .= '</a>' . "\n";
					}

					$title .= '</h3><!--/.member-->' . "\n";

					$member_role = '';

					if ( true == $args['display_role'] && isset( $post->byline ) && '' != $post->byline && apply_filters( 'woothemes_our_team_member_role', true ) ) {
						$member_role .= ' <p class="role" itemprop="jobTitle">' . $post->byline . '</p><!--/.excerpt-->' . "\n";
					}

					$title .= apply_filters( 'woothemes_our_team_member_fields_display', $member_role );

				}

				// Templating engine replacement.
				$template 		= str_replace( '%%TITLE%%', $title, $template );

				$author 		= '';
				$author_text 	= '';

				$user = $post->user_id;

				// If we need to display the author, get the data.
				if ( true == $args['display_additional'] ) {

					$author .= '<ul class="author-details">';

					$member_fields = '';

					if ( true == $args['display_author_archive'] && apply_filters( 'woothemes_our_team_member_user_id', true ) ) {

						// User didn't select an item from the autocomplete list
						// Let's try to get the user from the search query
						if ( 0 == $post->user_id && '' != $post->user_search ) {
							$user = get_user_by( 'slug', $post->user_search );
							if ( $user ) {
								$user = $user->ID;
							}
						}

						if ( 0 != $user ) {
							$member_fields .= '<li class="our-team-author-archive" itemprop="url"><a href="' . get_author_posts_url( $post->user_id ) . '">' . sprintf( __( 'Read posts by %1$s', 'our-team-by-woothemes' ), get_the_title() ) . '</a></li>' . "\n";
						}

					}

					if ( true == $args['contact_email'] && '' != $post->contact_email && apply_filters( 'woothemes_our_team_member_contact_email', true ) ) {
						$member_fields .= '<li class="our-team-contact-email" itemprop="email"><a href="mailto:' . esc_html( $post->contact_email ) . '">' . __( 'Email ', 'our-team-by-woothemes' ) . get_the_title() . '</a></li>';
					}

					if ( true == $args['tel'] && '' != $post->tel && apply_filters( 'woothemes_our_team_member_tel', true ) ) {
						$call_protocol = apply_filters( 'woothemes_our_team_call_protocol', $protocol = 'tel' );
						$member_fields .= '<li class="our-team-tel" itemprop="telephone"><span>' . __( 'Tel: ', 'our-team-by-woothemes' ) . '</span><a href="' . $call_protocol . ':' . esc_html( $post->tel ) . '">' . esc_html( $post->tel ) . '</a></li>';
					}

					if ( true == $args['display_twitter'] && '' != $post->twitter && apply_filters( 'woothemes_our_team_member_twitter', true ) ) {
						$member_fields .= '<li class="our-team-twitter" itemprop="contactPoint"><a href="//twitter.com/' . esc_html( $post->twitter ) . '" class="twitter-follow-button" data-show-count="false">Follow @' . esc_html( $post->twitter ) . '</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script></li>'  . "\n";
					}

					$author .= apply_filters( 'woothemes_our_member_fields_display', $member_fields );

					$author .= '</ul>';

					// Templating engine replacement.
					$template = str_replace( '%%AUTHOR%%', $author, $template );
				} else {
					$template = str_replace( '%%AUTHOR%%', '', $template );
				}

				// Templating logic replacement.
				$template = str_replace( '%%ID%%', get_the_ID(), $template );
				$template = str_replace( '%%CLASS%%', esc_attr( $css_class ), $template );

				if ( isset( $post->image ) && ( '' != $post->image ) && true == $args['display_avatar'] ) {
					$template = str_replace( '%%AVATAR%%', '<figure itemprop="image">' . $post->image . '</figure>', $template );
				} else {
					$template = str_replace( '%%AVATAR%%', '', $template );
				}

				// Remove any remaining %%AVATAR%% template tags.
				$template 	= str_replace( '%%AVATAR%%', '', $template );
			    $real_more 	= $more;
			    $more      	= 0;
				$content 	= apply_filters( 'woothemes_our_team_content', wpautop( get_the_content( __( 'Read full biography...', 'our-team-by-woothemes' ) ) ), $post );
				$more      	= $real_more;

				// Display bio if Team Member is mapped to a user on this site.
				if ( apply_filters( 'woothemes_our_team_display_bio', true ) && 0 != $user ) {
					if ( '' != get_the_author_meta( 'description', $user ) ) {
						$content = wpautop( get_the_author_meta( 'description', $user ) );
					}
				}

				$template = str_replace( '%%TEXT%%', $content, $template );

				// filter the individual team member html
				$template = apply_filters( 'woothemes_our_team_member_html', $template, $post );

				// Assign for output.
				$html .= $template;
			}

			wp_reset_postdata();

			if ( $args['pagination'] == true && count( $query ) > 1 && $args['effect'] != 'none' ) {
				$html .= '<div class="pagination">' . "\n";
				$html .= '<a href="#" class="btn-prev">' . apply_filters( 'woothemes_our_team_prev_btn', '&larr; ' . __( 'Previous', 'our-team-by-woothemes' ) ) . '</a>' . "\n";
		        $html .= '<a href="#" class="btn-next">' . apply_filters( 'woothemes_our_team_next_btn', __( 'Next', 'our-team-by-woothemes' ) . ' &rarr;' ) . '</a>' . "\n";
		        $html .= '</div><!--/.pagination-->' . "\n";
			}
			$html .= '</div><!--/.team-members-->' . "\n";
			$html .= $args['after'] . "\n";
		}

		// Allow child themes/plugins to filter here.
		$html = apply_filters( 'woothemes_our_team_html', $html, $query, $args );

		if ( $args['echo'] != true ) {
			return $html;
		}

		// Should only run is "echo" is set to true.
		echo $html;

		do_action( 'woothemes_our_team_after', $args ); // Only if "echo" is set to true.
} // End woothemes_our_team()
}

if ( ! function_exists( 'woothemes_our_team_shortcode' ) ) {
/**
 * The shortcode function.
 * @since  1.0.0
 * @param  array  $atts    Shortcode attributes.
 * @param  string $content If the shortcode is a wrapper, this is the content being wrapped.
 * @return string          Output using the template tag.
 */
function woothemes_our_team_shortcode ( $atts, $content = null ) {
	$args = (array)$atts;

	$defaults = array(
		'limit' 					=> 12,
		'per_row' 					=> null,
		'orderby' 					=> 'menu_order',
		'order' 					=> 'DESC',
		'id' 						=> 0,
		'slug'						=> null,
		'display_author' 			=> true,
		'display_additional' 		=> true,
		'display_avatar' 			=> true,
		'display_url' 				=> true,
		'display_author_archive'	=> true,
		'display_twitter' 			=> true,
		'display_role'	 			=> true,
		'effect' 					=> 'fade', // Options: 'fade', 'none'
		'pagination' 				=> false,
		'echo' 						=> true,
		'size' 						=> 250,
		'category' 					=> 0,
		'title'						=> '',
		'before_title' 				=> '<h2>',
		'after_title' 				=> '</h2>'
	);

	$args = shortcode_atts( $defaults, $atts );

	// Make sure we return and don't echo.
	$args['echo'] = false;

	// Fix integers.
	if ( isset( $args['limit'] ) ) {
		$args['limit'] = intval( $args['limit'] );
	}

	if ( isset( $args['size'] ) &&  ( 0 < intval( $args['size'] ) ) ) {
		$args['size'] = intval( $args['size'] );
	}

	if ( isset( $args['category'] ) && is_numeric( $args['category'] ) ) {
		$args['category'] = intval( $args['category'] );
	}

	// Fix booleans.
	foreach ( array( 'display_author', 'display_additional', 'display_url', 'display_author_archive', 'display_twitter', 'display_role', 'pagination', 'display_avatar' ) as $k => $v ) {
		if ( isset( $args[$v] ) && ( 'true' == $args[$v] ) ) {
			$args[$v] = true;
		} else {
			$args[$v] = false;
		}
	}

	return woothemes_our_team( $args );

} // End woothemes_our_team_shortcode()
}

add_shortcode( 'woothemes_our_team', 'woothemes_our_team_shortcode' );

if ( ! function_exists( 'woothemes_our_team_content_default_filters' ) ) {
/**
 * Adds default filters to the "woothemes_our_team_content" filter point.
 * @since  1.0.0
 * @return void
 */
function woothemes_our_team_content_default_filters () {
	add_filter( 'woothemes_our_team_content', 'do_shortcode' );
} // End woothemes_our_team_content_default_filters()

add_action( 'woothemes_our_team_before', 'woothemes_our_team_content_default_filters' );
}

add_filter( 'the_content', 'woothemes_our_team_content' );
/**
 * Display team member data on single / archive pages
 * @since 1.4.0
 * @return  $content the post content
 */
function woothemes_our_team_content( $content ) {
	global $post;

	$team_member_email 	= esc_attr( get_post_meta( $post->ID, '_gravatar_email', true ) );
	$user 				= esc_attr( get_post_meta( $post->ID, '_user_id', true ) );
	$user_search 		= esc_attr( get_post_meta( $post->ID, '_user_search', true ) );
	$twitter 			= esc_attr( get_post_meta( $post->ID, '_twitter', true ) );
	$role 				= esc_attr( get_post_meta( $post->ID, '_byline', true ) );
	$url 				= esc_attr( get_post_meta( $post->ID, '_url', true ) );
	$tel 				= esc_attr( get_post_meta( $post->ID, '_tel', true ) );
	$contact_email 		= esc_attr( get_post_meta( $post->ID, '_contact_email', true ) );

	if ( 'team-member' == get_post_type() ) {

		$team_member_gravatar 	= '';
		$team_member_role 		= '';
		$member_fields 			= '';
		$author 				= '';

		if ( isset( $team_member_email ) && ( '' != $team_member_email ) ) {
			$team_member_gravatar = '<figure itemprop="image">' .  get_avatar( $team_member_email, 250 ) . '</figure>';
		}

		if ( isset( $role ) && '' != $role && apply_filters( 'woothemes_our_team_member_role', true ) ) {
			$team_member_role .= ' <p class="role" itemprop="jobTitle">' . $role . '</p>' . "\n";
		}

		$author .= '<ul class="author-details">';

		if ( apply_filters( 'woothemes_our_team_member_user_id', true ) ) {
			if ( 0 == $user && '' != $user_search ) {
				$user = get_user_by( 'slug', $user_search );
				if ( $user ) {
					$user = $user;
				}
			}

			if ( 0 != $user ) {
				$member_fields .= '<li class="our-team-author-archive" itemprop="url"><a href="' . get_author_posts_url( $user ) . '">' . sprintf( __( 'Read posts by %1$s', 'woothemes' ), get_the_title() ) . '</a></li>' . "\n";
			}
		}

		if ( '' != $tel && apply_filters( 'woothemes_our_team_member_contact_email', true ) ) {
			$member_fields .= '<li class="our-team-contact-email" itemprop="email"><a href="mailto:' . $contact_email . '">' . __( 'Email ', 'our-team-by-woothemes' ) . get_the_title() . '</a></li>';
		}

		if ( '' != $tel && apply_filters( 'woothemes_our_team_member_tel', true ) ) {
			$call_protocol = apply_filters( 'woothemes_our_team_call_protocol', $protocol = 'tel' );
			$member_fields .= '<li class="our-team-tel" itemprop="telephone"><span>' . __( 'Tel: ', 'our-team-by-woothemes' ) . '</span><a href="' . $call_protocol . ':' . $tel . '">' . $tel . '</a></li>';
		}

		if ( '' != $twitter && apply_filters( 'woothemes_our_team_member_twitter', true ) ) {
			$member_fields .= '<li class="our-team-twitter" itemprop="contactPoint"><a href="//twitter.com/' . esc_html( $twitter ) . '" class="twitter-follow-button" data-show-count="false">Follow @' . esc_html( $twitter ) . '</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script></li>'  . "\n";
		}

		$author .= apply_filters( 'woothemes_our_member_fields_display', $member_fields );

		$author .= '</ul>';

		return $team_member_gravatar . $team_member_role . $content . $author;
	} else {
		return $content;
	}
}
