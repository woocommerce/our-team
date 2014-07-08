<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * WooThemes Our Team Class
 *
 * All functionality pertaining to the Our Team feature.
 *
 * @package WordPress
 * @subpackage WooThemes_Our_Team
 * @category Plugin
 * @author Matty
 * @since 1.0.0
 */
class Woothemes_Our_Team {
	private $dir;
	private $assets_dir;
	private $assets_url;
	private $token;
	public $version;
	private $file;

	/**
	 * Constructor function.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct( $file ) {
		$this->dir = dirname( $file );
		$this->file = $file;
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $file ) ) );
		$this->token = 'team-member';

		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Run this on activation.
		register_activation_hook( $this->file, array( $this, 'activation' ) );

		add_filter( 'plugin_action_links_our-team-by-woothemes/woothemes-our-team.php', array( $this, 'our_team_action_links' ) );

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
    	add_action( 'load-post-new.php', array( $this, 'our_team_help_tab' ) );
    	add_action( 'load-post.php', array( $this, 'our_team_help_tab' ) );

		if ( is_admin() ) {
			global $pagenow;

			add_action( 'admin_menu', array( $this, 'meta_box_setup' ), 20 );
			add_action( 'save_post', array( $this, 'meta_box_save' ) );
			add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
			add_action( 'admin_print_styles', array( $this, 'enqueue_admin_styles' ), 10 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10 );
			add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

			if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && esc_attr( $_GET['post_type'] ) == $this->token ) {
				add_filter( 'manage_edit-' . $this->token . '_columns', array( $this, 'register_custom_column_headings' ), 10, 1 );
				add_action( 'manage_posts_custom_column', array( $this, 'register_custom_columns' ), 10, 2 );
			}

			// Get users ajax callback
			add_action( 'wp_ajax_get_users', array( $this, 'get_users_callback' ) );
			add_action( 'admin_footer',  array( $this, 'get_users_javascript' ) );

		}

		add_action( 'after_setup_theme', array( $this, 'ensure_post_thumbnails_support' ) );
	} // End __construct()

	/**
	 * Register the post type.
	 *
	 * @access public
	 * @param string $token
	 * @param string 'Team Member'
	 * @param string 'Our Team'
	 * @param array $supports
	 * @return void
	 */
	public function register_post_type () {
		$labels = array(
			'name' 					=> _x( 'Team Members', 'post type general name', 'our-team-by-woothemes' ),
			'singular_name' 		=> _x( 'Team Member', 'post type singular name', 'our-team-by-woothemes' ),
			'add_new' 				=> _x( 'Add New', 'team member', 'our-team-by-woothemes' ),
			'add_new_item' 			=> sprintf( __( 'Add New %s', 'our-team-by-woothemes' ), __( 'Team Member', 'our-team-by-woothemes' ) ),
			'edit_item' 			=> sprintf( __( 'Edit %s', 'our-team-by-woothemes' ), __( 'Team Member', 'our-team-by-woothemes' ) ),
			'new_item' 				=> sprintf( __( 'New %s', 'our-team-by-woothemes' ), __( 'Team Member', 'our-team-by-woothemes' ) ),
			'all_items' 			=> sprintf( __( 'All %s', 'our-team-by-woothemes' ), __( 'Team Members', 'our-team-by-woothemes' ) ),
			'view_item' 			=> sprintf( __( 'View %s', 'our-team-by-woothemes' ), __( 'Team Member', 'our-team-by-woothemes' ) ),
			'search_items' 			=> sprintf( __( 'Search %a', 'our-team-by-woothemes' ), __( 'Team Members', 'our-team-by-woothemes' ) ),
			'not_found' 			=> sprintf( __( 'No %s Found', 'our-team-by-woothemes' ), __( 'Team Members', 'our-team-by-woothemes' ) ),
			'not_found_in_trash' 	=> sprintf( __( 'No %s Found In Trash', 'our-team-by-woothemes' ), __( 'Team Members', 'our-team-by-woothemes' ) ),
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __( 'Team Members', 'our-team-by-woothemes' )

		);

		$single_slug = apply_filters( 'woothemes_our_team_single_slug', _x( 'team-member', 'single post url slug', 'our-team-by-woothemes' ) );
		$archive_slug = apply_filters( 'woothemes_our_team_archive_slug', _x( 'team-members', 'post archive url slug', 'our-team-by-woothemes' ) );

		$args = array(
			'labels' 				=> $labels,
			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'show_ui'			 	=> true,
			'show_in_menu' 			=> true,
			'query_var' 			=> true,
			'rewrite' 				=> array(
										'slug' 			=> $single_slug,
										'with_front' 	=> false
										),
			'capability_type' 		=> 'post',
			'has_archive' 			=> $archive_slug,
			'hierarchical' 			=> false,
			'supports' 				=> array(
										'title',
										'author',
										'editor',
										'thumbnail',
										'page-attributes'
										),
			'menu_position' 		=> 5,
			'menu_icon' 			=> ''
		);
		$args = apply_filters( 'woothemes_our_team_post_type_args', $args );
		register_post_type( $this->token, (array) $args );
	} // End register_post_type()

	/**
	 * Register the "our-team-category" taxonomy.
	 * @access public
	 * @since  1.3.0
	 * @return void
	 */
	public function register_taxonomy () {
		$this->taxonomy_category = new Woothemes_Our_Team_Taxonomy(); // Leave arguments empty, to use the default arguments.
		$this->taxonomy_category->register();
	} // End register_taxonomy()

	/**
	 * Add custom columns for the "manage" screen of this post type.
	 *
	 * @access public
	 * @param string $column_name
	 * @param int $id
	 * @since  1.0.0
	 * @return void
	 */
	public function register_custom_columns ( $column_name, $id ) {
		global $wpdb, $post;

		$meta = get_post_custom( $id );

		switch ( $column_name ) {

			case 'image':
				$value = '';

				$value = $this->get_image( $id, 40 );

				echo $value;
			break;

			default:
			break;

		}
	} // End register_custom_columns()

	/**
	 * Add custom column headings for the "manage" screen of this post type.
	 *
	 * @access public
	 * @param array $defaults
	 * @since  1.0.0
	 * @return void
	 */
	public function register_custom_column_headings ( $defaults ) {
		$new_columns 	= array( 'image' => __( 'Image', 'our-team-by-woothemes' ) );
		$last_item 		= '';

		if ( isset( $defaults['date'] ) ) { unset( $defaults['date'] ); }

		if ( count( $defaults ) > 2 ) {
			$last_item = array_slice( $defaults, -1 );

			array_pop( $defaults );
		}
		$defaults = array_merge( $defaults, $new_columns );

		if ( $last_item != '' ) {
			foreach ( $last_item as $k => $v ) {
				$defaults[$k] = $v;
				break;
			}
		}

		return $defaults;
	} // End register_custom_column_headings()

	/**
	 * Update messages for the post type admin.
	 * @since  1.0.0
	 * @param  array $messages Array of messages for all post types.
	 * @return array           Modified array.
	 */
	public function updated_messages ( $messages ) {
	  global $post, $post_ID;

	  $messages[$this->token] = array(
	    0 => '', // Unused. Messages start at index 1.
	    1 => sprintf( __( 'Team Member updated. %sView team member%s', 'our-team-by-woothemes' ), '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">', '</a>' ),
	    2 => __( 'Custom field updated.', 'our-team-by-woothemes' ),
	    3 => __( 'Custom field deleted.', 'our-team-by-woothemes' ),
	    4 => __( 'Team Member updated.', 'our-team-by-woothemes' ),
	    /* translators: %s: date and time of the revision */
	    5 => isset($_GET['revision']) ? sprintf( __( 'Team Member restored to revision from %s', 'our-team-by-woothemes' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	    6 => sprintf( __( 'Team Member published. %sView team member%s', 'our-team-by-woothemes' ), '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">', '</a>' ),
	    7 => __('Team Member saved.'),
	    8 => sprintf( __( 'Team Member submitted. %sPreview team member%s', 'our-team-by-woothemes' ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">', '</a>' ),
	    9 => sprintf( __( 'Team Member scheduled for: %1$s. %2$sPreview team member%3$s', 'our-team-by-woothemes' ),
	      // translators: Publish box date format, see http://php.net/date
	      '<strong>' . date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) . '</strong>', '<a target="_blank" href="' . esc_url( get_permalink($post_ID) ) . '">', '</a>' ),
	    10 => sprintf( __( 'Team Member draft updated. %sPreview team member%s', 'our-team-by-woothemes' ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">', '</a>' ),
	  );

	  return $messages;
	} // End updated_messages()

	/**
	 * Setup the meta box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function meta_box_setup () {
		add_meta_box( 'team-member-data', __( 'Team Member Details', 'our-team-by-woothemes' ), array( $this, 'meta_box_content' ), $this->token, 'normal', 'high' );
	} // End meta_box_setup()

	/**
	 * The contents of our meta box.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function meta_box_content () {
		global $post_id;
		$fields = get_post_custom( $post_id );
		$field_data = $this->get_custom_fields_settings();

		$html = '';

		$html .= '<input type="hidden" name="woo_' . $this->token . '_noonce" id="woo_' . $this->token . '_noonce" value="' . wp_create_nonce( plugin_basename( $this->dir ) ) . '" />';

		if ( 0 < count( $field_data ) ) {
			$html .= '<table class="form-table">' . "\n";
			$html .= '<tbody>' . "\n";

			foreach ( $field_data as $k => $v ) {
				$data = $v['default'];
				if ( isset( $fields['_' . $k] ) && isset( $fields['_' . $k][0] ) ) {
					$data = $fields['_' . $k][0];
				}

				switch ( $v['type'] ) {
					case 'hidden':
						$field = '<input name="' . esc_attr( $k ) . '" type="hidden" id="' . esc_attr( $k ) . '" value="' . esc_attr( $data ) . '" />';
						$html .= '<tr valign="top">' . $field . "\n";
						$html .= '<tr/>' . "\n";
						break;
					default:
						$field = '<input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" />';
						$html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label></th><td>' . $field . "\n";
						$html .= '<p class="description">' . $v['description'] . '</p>' . "\n";
						$html .= '</td><tr/>' . "\n";
						break;
				}

			}

			$html .= '</tbody>' . "\n";
			$html .= '</table>' . "\n";
		}

		echo $html;
	} // End meta_box_content()

	/**
	 * Save meta box fields.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param int $post_id
	 * @return void
	 */
	public function meta_box_save ( $post_id ) {
		global $post, $messages;

		// Verify
		if ( ( get_post_type() != $this->token ) || ! wp_verify_nonce( $_POST['woo_' . $this->token . '_noonce'], plugin_basename( $this->dir ) ) ) {
			return $post_id;
		}

		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		$field_data = $this->get_custom_fields_settings();
		$fields = array_keys( $field_data );

		foreach ( $fields as $f ) {

			${$f} = strip_tags(trim($_POST[$f]));

			// Escape the URLs.
			if ( 'url' == $field_data[$f]['type'] ) {
				${$f} = esc_url( ${$f} );
			}

			if ( get_post_meta( $post_id, '_' . $f ) == '' ) {
				add_post_meta( $post_id, '_' . $f, ${$f}, true );
			} elseif( ${$f} != get_post_meta( $post_id, '_' . $f, true ) ) {
				update_post_meta( $post_id, '_' . $f, ${$f} );
			} elseif ( ${$f} == '' ) {
				delete_post_meta( $post_id, '_' . $f, get_post_meta( $post_id, '_' . $f, true ) );
			}
		}
	} // End meta_box_save()

	/**
	 * Customise the "Enter title here" text.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param string $title
	 * @return void
	 */
	public function enter_title_here ( $title ) {
		if ( get_post_type() == $this->token ) {
			$title = __( 'Enter the team member\'s name here', 'our-team-by-woothemes' );
		}
		return $title;
	} // End enter_title_here()

	/**
	 * Enqueue post type admin CSS.
	 *
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	public function enqueue_admin_styles () {
		wp_register_style( 'woothemes-our-team-admin', $this->assets_url . 'css/admin.css', array(), '1.0.1' );
		wp_enqueue_style( 'woothemes-our-team-admin' );
	} // End enqueue_admin_styles()

	/**
	 * Enqueue post type admin JavaScript.
	 *
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	public function enqueue_admin_scripts () {
		wp_enqueue_script('jquery-ui-autocomplete', null, array('jquery'), null, false);
	} // End enqueue_admin_styles()

	/**
	 * Get the settings for the custom fields.
	 * @since  1.0.0
	 * @return array
	 */
	public function get_custom_fields_settings () {
		$fields = array();

		$fields['gravatar_email'] = array(
		    'name' 				=> __( 'Gravatar E-mail Address', 'our-team-by-woothemes' ),
		    'description' 		=> sprintf( __( 'Enter an e-mail address, to use a %sGravatar%s, instead of using the "Featured Image".', 'our-team-by-woothemes' ), '<a href="' . esc_url( 'http://gravatar.com/' ) . '" target="_blank">', '</a>' ),
		    'type' 				=> 'text',
		    'default' 			=> '',
		    'section' 			=> 'info'
		);

		if ( apply_filters( 'woothemes_our_team_member_role', true ) ) {
			$fields['byline'] = array(
			    'name' 			=> __( 'Role', 'our-team-by-woothemes' ),
			    'description' 	=> __( 'Enter a byline for the team member (for example: "Director of Production").', 'our-team-by-woothemes' ),
			    'type' 			=> 'text',
			    'default' 		=> '',
			    'section' 		=> 'info'
			);
		}

		if ( apply_filters( 'woothemes_our_team_member_url', true ) ) {
			$fields['url'] = array(
			    'name' 			=> __( 'URL', 'our-team-by-woothemes' ),
			    'description' 	=> __( 'Enter this team member\'s URL (for example: http://woothemes.com/).', 'our-team-by-woothemes' ),
			    'type' 			=> 'url',
			    'default' 		=> '',
			    'section' 		=> 'info'
			);
		}

		if ( apply_filters( 'woothemes_our_team_member_contact_email', true ) ) {
			$fields['contact_email'] = array(
		    	'name' 				=> __( 'Contact E-mail Address', 'our-team-by-woothemes' ),
		    	'description' 		=> __( 'Enter a contact email address for this team member to be displayed as a link on the frontend.', 'our-team-by-woothemes' ),
		    	'type' 				=> 'text',
		    	'default' 			=> '',
		    	'section' 			=> 'info'
			);
		}

		if ( apply_filters( 'woothemes_our_team_member_tel', true ) ) {
			$fields['tel'] = array(
		    	'name' 				=> __( 'Telephone Number', 'our-team-by-woothemes' ),
		    	'description' 		=> __( 'Enter a telephone number for this team member to be displayed as a link on the frontend.', 'our-team-by-woothemes' ),
		    	'type' 				=> 'text',
		    	'default' 			=> '',
		    	'section' 			=> 'info'
			);
		}

		if ( apply_filters( 'woothemes_our_team_member_twitter', true ) ) {
			$fields['twitter'] = array(
			    'name' 			=> __( 'Twitter Username', 'our-team-by-woothemes' ),
			    'description' 	=> __( 'Enter this team member\'s Twitter username without the @ (for example: woothemes).', 'our-team-by-woothemes' ),
			    'type' 			=> 'text',
			    'default' 		=> '',
			    'section' 		=> 'info'
			);
		}

		if ( apply_filters( 'woothemes_our_team_member_user_search', true ) ) {
			$fields['user_search'] = array(
			    'name' 			=> get_bloginfo( 'name' ) . ' ' . __( 'Username', 'our-team-by-woothemes' ),
			    'description' 	=> sprintf( __( 'Map this team member to a user on this site. See the %sdocumentation%s for more info.', 'our-team-by-woothemes' ), '<a href="' . esc_url( 'http://docs.woothemes.com/document/our-team-plugin/' ) . '" target="_blank">', '</a>' ),
			    'type' 			=> 'text',
			    'default' 		=> '',
			    'section' 		=> 'info'
			);
		}

		if ( apply_filters( 'woothemes_our_team_member_user_id', true ) ) {
			$fields['user_id'] = array(
			    'name' 			=> get_bloginfo( 'name' ) . ' ' . __( 'Username', 'our-team-by-woothemes' ),
			    'description' 	=> __( 'Holds the id of the selected user.', 'our-team-by-woothemes' ),
			    'type' 			=> 'hidden',
			    'default' 		=> 0,
			    'section' 		=> 'info'
			);
		}

		return apply_filters( 'woothemes_our_team_member_fields', $fields );
	} // End get_custom_fields_settings()

	/**
	 * Ajax callback to search for users.
	 * @param  string $query Search Query.
	 * @since  1.1.0
	 * @return json       	Search Results.
	 */
	public function get_users_callback() {

		check_ajax_referer( 'our_team_ajax_get_users', 'security' );

		$term = urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );

		if ( !empty( $term ) ) {

			header( 'Content-Type: application/json; charset=utf-8' );

			$users_query = new WP_User_Query( array(
				'fields'			=> 'all',
				'orderby'			=> 'display_name',
				'search'			=> '*' . $term . '*',
				'search_columns'	=> array( 'ID', 'user_login', 'user_email', 'user_nicename' )
			) );

			$users = $users_query->get_results();
			$found_users = array();

			if ( $users ) {
				foreach ( $users as $user ) {
					$found_users[] = array( 'id' => $user->ID, 'display_name' => $user->display_name );
				}
			}

			echo json_encode( $found_users );

		}

		die();

	}

	/**
	 * Get the image for the given ID. If no featured image, check for Gravatar e-mail.
	 * @param  int 				$id   Post ID.
	 * @param  string/array/int $size Image dimension.
	 * @since  1.0.0
	 * @return string       	<img> tag.
	 */
	protected function get_image ( $id, $size ) {
		$response = '';

		if ( has_post_thumbnail( $id ) ) {
			// If not a string or an array, and not an integer, default to 150x9999.
			if ( ( is_int( $size ) || ( 0 < intval( $size ) ) ) && ! is_array( $size ) ) {
				$size = array( intval( $size ), intval( $size ) );
			} elseif ( ! is_string( $size ) && ! is_array( $size ) ) {
				$size = array( 50, 50 );
			}
			$response = get_the_post_thumbnail( intval( $id ), $size, array( 'class' => 'avatar' ) );
		} else {
			$gravatar_email = get_post_meta( $id, '_gravatar_email', true );
			if ( '' != $gravatar_email && is_email( $gravatar_email ) ) {
				$response = get_avatar( $gravatar_email, $size );
			}
		}

		return $response;
	} // End get_image()

	/**
	 * Get team members.
	 * @param  string/array $args Arguments to be passed to the query.
	 * @since  1.0.0
	 * @return array/boolean      Array if true, boolean if false.
	 */
	public function get_our_team ( $args = '' ) {
		$defaults = array(
			'query_id'		=> 'our_team',
			'limit' 		=> 12,
			'orderby' 		=> 'menu_order',
			'order' 		=> 'DESC',
			'id' 			=> 0,
			'slug'			=> null,
			'category' 		=> 0,
			'meta_key'		=> null,
			'meta_value'	=> null
		);

		$args = wp_parse_args( $args, $defaults );

		// Allow child themes/plugins to filter here.
		$args = apply_filters( 'woothemes_get_our_team_args', $args );

		// The Query Arguments.
		$query_args 						= array();
		$query_args['query_id']				= $args['query_id'];
		$query_args['post_type'] 			= 'team-member';
		$query_args['numberposts'] 			= $args['limit'];
		$query_args['orderby'] 				= $args['orderby'];
		$query_args['order'] 				= $args['order'];
		$query_args['suppress_filters'] 	= false;

		$ids = explode( ',', $args['id'] );
		if ( 0 < intval( $args['id'] ) && 0 < count( $ids ) ) {
			$ids = array_map( 'intval', $ids );
			if ( 1 == count( $ids ) && is_numeric( $ids[0] ) && ( 0 < intval( $ids[0] ) ) ) {
				$query_args['p'] = intval( $args['id'] );
			} else {
				$query_args['ignore_sticky_posts'] = 1;
				$query_args['post__in'] = $ids;
			}
		}

		if ( $args['slug'] ) {
			$query_args['name'] = esc_html( $args['slug'] );
		}

		// Whitelist checks.
		if ( ! in_array( $query_args['orderby'], array( 'none', 'ID', 'author', 'title', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order', 'meta_value', 'meta_value_num' ) ) ) {
			$query_args['orderby'] = 'date';
		}

		if ( ! in_array( $query_args['order'], array( 'ASC', 'DESC' ) ) ) {
			$query_args['order'] = 'DESC';
		}

		if ( ! in_array( $query_args['post_type'], get_post_types() ) ) {
			$query_args['post_type'] = 'team-member';
		}

		$tax_field_type = '';

		// If the category ID is specified.
		if ( is_numeric( $args['category'] ) && 0 < intval( $args['category'] ) ) {
			$tax_field_type = 'id';
		}

		// If the category slug is specified.
		if ( ! is_numeric( $args['category'] ) && is_string( $args['category'] ) ) {
			$tax_field_type = 'slug';
		}

		// If a meta query is specified
		if ( is_string( $args['meta_key'] ) ) {
			$query_args['meta_key'] = esc_html( $args['meta_key'] );
		}

		if ( is_string( $args['meta_value'] ) ) {
			$query_args['meta_value'] = esc_html( $args['meta_value'] );
		}

		// Setup the taxonomy query.
		if ( '' != $tax_field_type ) {
			$term = $args['category'];
			if ( is_string( $term ) ) { $term = esc_html( $term ); } else { $term = intval( $term ); }
			$query_args['tax_query'] = array( array( 'taxonomy' => 'team-member-category', 'field' => $tax_field_type, 'terms' => array( $term ) ) );
		}

		// The Query.
		$query = get_posts( $query_args );

		// The Display.
		if ( ! is_wp_error( $query ) && is_array( $query ) && count( $query ) > 0 ) {
			foreach ( $query as $k => $v ) {
				$meta = get_post_custom( $v->ID );

				// Get the image.
				$query[$k]->image = $this->get_image( $v->ID, $args['size'] );

				foreach ( (array)$this->get_custom_fields_settings() as $i => $j ) {
					if ( isset( $meta['_' . $i] ) && ( '' != $meta['_' . $i][0] ) ) {
						$query[$k]->$i = $meta['_' . $i][0];
					} else {
						$query[$k]->$i = $j['default'];
					}
				}
			}
		} else {
			$query = false;
		}

		return $query;
	} // End get_our_team()

	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'our-team-by-woothemes', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'our-team-by-woothemes';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
		$this->flush_rewrite_rules();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( 'woothemes-our-team' . '-version', $this->version );
		}
	} // End register_plugin_version()

	/**
	 * Flush the rewrite rules
	 * @access public
	 * @since 1.4.0
	 * @return void
	 */
	private function flush_rewrite_rules () {
		$this->register_post_type();
		flush_rewrite_rules();
	} // End flush_rewrite_rules()

	/**
	 * Ensure that "post-thumbnails" support is available for those themes that don't register it.
	 * @since  1.0.1
	 * @return  void
	 */
	public function ensure_post_thumbnails_support () {
		if ( ! current_theme_supports( 'post-thumbnails' ) ) { add_theme_support( 'post-thumbnails' ); }
	} // End ensure_post_thumbnails_support()

	/**
	 * Output admin javascript
	 * @since  1.1.0
	 * @return  void
	 */
	public function get_users_javascript() {

		global $pagenow, $post_type;

		if ( ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) && isset( $post_type ) && esc_attr( $post_type ) == $this->token ) {

			$ajax_nonce = wp_create_nonce( 'our_team_ajax_get_users' );

	?>
			<script type="text/javascript" >
				jQuery(function() {
					jQuery( "#user_search" ).autocomplete({
						minLength: 1,
						source: function ( request, response ) {
							jQuery.ajax({
								url: ajaxurl,
								dataType: 'json',
								data: {
									action: 'get_users',
									security: '<?php echo $ajax_nonce; ?>',
									term: request.term
								},
								success: function( data ) {
									response( jQuery.map( data, function( item ) {
										return {
											label: item.display_name,
											value: item.id
										}
									}));
								}
							});
						},
						select: function ( event, ui ) {
							event.preventDefault();
							jQuery( "#user_search" ).val( ui.item.label );
							jQuery( "#user_id" ).val( ui.item.value );
						}
					});

					// Unset #user_id if #user_search is emptied
					jQuery( '#user_search' ).blur(function() {
					    if ( jQuery(this).val().length == 0 ) {
					        jQuery( "#user_id" ).val( 0 );
					    }
					});

					// Unser #user_id if #user_search is empty on page load
					if ( jQuery( '#user_search' ).val().length == 0 ) {
				        jQuery( "#user_id" ).val( 0 );
				    }
				});
			</script>
	<?php
		}
	} //End get_users_javascript

	/**
	 * Add the Our Team action links
	 * @param  array $links current action links
	 * @return array current action links merged with new action links
	 */
	public function our_team_action_links( $links ) {
		$our_team_links = array(
			'<a href="http://docs.woothemes.com/documentation/plugins/our-team/" target="_blank">' . __( 'Documentation', 'our-team-by-woothemes' ) . '</a>',
		);

		return array_merge( $links, $our_team_links );
	}

	/**
	 * Our Team Help Tab
	 * Gives users quick access to shortcode examples via the dashboard
	 * @return array content for the help tab
	 */
	public function our_team_help_tab () {
    $screen = get_current_screen();

    $screen->add_help_tab( array(
        'id'		=> 'woo_our_team_help_tab',
        'title'		=> __( 'Our Team', 'our-team-by-woothemes' ),
        'callback'	=> 'our_team_help_tab_content',
    ) );

    /**
     * Our Team help tab content
     * @return void
     */
    function our_team_help_tab_content() {
    	$odd 	= 'width: 46%; float: left; clear: both;';
    	$even 	= 'width: 46%; float: right;';
    	?>
    		<h3><?php _e( 'Displaying team members in posts and pages', 'our-team-by-woothemes' ); ?></h3>
    		<p>
    			<?php echo sprintf( __( 'The easiest way to display team members is to use the %s[woothemes_our_team]%s shortcode.', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?>
    		</p>
    		<p>
    			<?php _e( 'The shortcode accepts various arguments as described below:', 'our-team-by-woothemes' ); ?>
    		</p>
    		<ul style="overflow: hidden;">
	    		<li style="<?php echo $odd; ?>"><?php echo sprintf( __( '%slimit%s - The maximum number of team members to display.', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $even; ?>"><?php echo sprintf( __( '%sorderby%s - How to order the team members. (Accepts all default WordPress ordering options).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $odd; ?>"><?php echo sprintf( __( '%sorder%s - The order direction. (eg. ASC or DESC).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $even; ?>"><?php echo sprintf( __( '%sid%s - Display a specific team member by ID.', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $odd; ?>"><?php echo sprintf( __( '%sdisplay_avatar%s - Display the team members gravatar. (true or false).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $even; ?>"><?php echo sprintf( __( '%ssize%s - The size to display the team members gravatar.', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $odd; ?>"><?php echo sprintf( __( '%sdisplay_additional%s - Global toggle for displaying all additional information such as twitter, email and telephone number. (true or false).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $even; ?>"><?php echo sprintf( __( '%sdisplay_url%s - Display the team members URL. (true or false).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $odd; ?>"><?php echo sprintf( __( '%sdisplay_role%s - Display the team members role. (true or false).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $even; ?>"><?php echo sprintf( __( '%sdisplay_twitter%s - Display the team members twitter follow button. (true or false).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $odd; ?>"><?php echo sprintf( __( '%sdisplay_author_archive%s - Display the team members author archive link if specified. (true or false).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $even; ?>"><?php echo sprintf( __( '%scontact_email%s - Display the team members contact email. (true or false).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $odd; ?>"><?php echo sprintf( __( '%stel%s - Display the team members telephone number. (true or false).', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $even; ?>"><?php echo sprintf( __( '%sslug%s - Display a specific team member by post slug.', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
	    		<li style="<?php echo $odd; ?>"><?php echo sprintf( __( '%scategory%s - Display team members from within a specified category. Use the category slug.', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?></li>
    		</ul>
    		<p>
    			<?php echo sprintf( __( 'For example, to display 6 team members while hiding gravatars you would use this shortcode: %s[woothemes_our_team limit="6" display_avatar="false"]%s.', 'our-team-by-woothemes' ), '<code>', '</code>' ); ?>
    		</p>
    		<p>
    			<p><?php echo sprintf( __( 'Read more about how to use Our Team in the %sdocumentation%s.', 'our-team-by-woothemes' ), '<a href="http://docs.woothemes.com/document/our-team-plugin/">', '</a>' ); ?></p>
    		</p>
    	<?php
    }
}

} // End Class
