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

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );

		if ( is_admin() ) {
			global $pagenow;

			add_action( 'admin_menu', array( $this, 'meta_box_setup' ), 20 );
			add_action( 'save_post', array( $this, 'meta_box_save' ) );
			add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
			add_action( 'admin_print_styles', array( $this, 'enqueue_admin_styles' ), 10 );
			add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

			if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && esc_attr( $_GET['post_type'] ) == $this->token ) {
				add_filter( 'manage_edit-' . $this->token . '_columns', array( $this, 'register_custom_column_headings' ), 10, 1 );
				add_action( 'manage_posts_custom_column', array( $this, 'register_custom_columns' ), 10, 2 );
			}
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
			'name' 					=> _x( 'Team Members', 'post type general name', 'woothemes-our-team' ),
			'singular_name' 		=> _x( 'Team Member', 'post type singular name', 'woothemes-our-team' ),
			'add_new' 				=> _x( 'Add New', 'team member', 'woothemes-our-team' ),
			'add_new_item' 			=> sprintf( __( 'Add New %s', 'woothemes-our-team' ), __( 'Team Member', 'woothemes-our-team' ) ),
			'edit_item' 			=> sprintf( __( 'Edit %s', 'woothemes-our-team' ), __( 'Team Member', 'woothemes-our-team' ) ),
			'new_item' 				=> sprintf( __( 'New %s', 'woothemes-our-team' ), __( 'Team Member', 'woothemes-our-team' ) ),
			'all_items' 			=> sprintf( __( 'All %s', 'woothemes-our-team' ), __( 'Team Members', 'woothemes-our-team' ) ),
			'view_item' 			=> sprintf( __( 'View %s', 'woothemes-our-team' ), __( 'Team Member', 'woothemes-our-team' ) ),
			'search_items' 			=> sprintf( __( 'Search %a', 'woothemes-our-team' ), __( 'Team Members', 'woothemes-our-team' ) ),
			'not_found' 			=>  sprintf( __( 'No %s Found', 'woothemes-our-team' ), __( 'Team Members', 'woothemes-our-team' ) ),
			'not_found_in_trash' 	=> sprintf( __( 'No %s Found In Trash', 'woothemes-our-team' ), __( 'Team Members', 'woothemes-our-team' ) ),
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __( 'Team Members', 'woothemes-our-team' )

		);

		$single_slug = apply_filters( 'woothemes_our_team_single_slug', _x( 'team-member', 'single post url slug', 'woothemes-our-team' ) );
		$archive_slug = apply_filters( 'woothemes_our_team_archive_slug', _x( 'team-members', 'post archive url slug', 'woothemes-our-team' ) );

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
		register_post_type( $this->token, $args );
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
		$new_columns 	= array( 'image' => __( 'Image', 'woothemes-our-team' ) );
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
	    1 => sprintf( __( 'Team Member updated. %sView team member%s', 'woothemes-our-team' ), '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">', '</a>' ),
	    2 => __( 'Custom field updated.', 'woothemes-our-team' ),
	    3 => __( 'Custom field deleted.', 'woothemes-our-team' ),
	    4 => __( 'Team Member updated.', 'woothemes-our-team' ),
	    /* translators: %s: date and time of the revision */
	    5 => isset($_GET['revision']) ? sprintf( __( 'Team Member restored to revision from %s', 'woothemes-our-team' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	    6 => sprintf( __( 'Team Member published. %sView team member%s', 'woothemes-our-team' ), '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">', '</a>' ),
	    7 => __('Team Member saved.'),
	    8 => sprintf( __( 'Team Member submitted. %sPreview team member%s', 'woothemes-our-team' ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">', '</a>' ),
	    9 => sprintf( __( 'Team Member scheduled for: %1$s. %2$sPreview team member%3$s', 'woothemes-our-team' ),
	      // translators: Publish box date format, see http://php.net/date
	      '<strong>' . date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) . '</strong>', '<a target="_blank" href="' . esc_url( get_permalink($post_ID) ) . '">', '</a>' ),
	    10 => sprintf( __( 'Team Member draft updated. %sPreview team member%s', 'woothemes-our-team' ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">', '</a>' ),
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
		add_meta_box( 'team-member-data', __( 'Team Member Details', 'woothemes-our-team' ), array( $this, 'meta_box_content' ), $this->token, 'normal', 'high' );
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

				$html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label></th><td><input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" />' . "\n";
				$html .= '<p class="description">' . $v['description'] . '</p>' . "\n";
				$html .= '</td><tr/>' . "\n";
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
			$title = __( 'Enter the team member\'s name here', 'woothemes-our-team' );
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
		wp_register_style( 'woothemes-our-team-admin', $this->assets_url . '/css/admin.css', array(), '1.0.1' );
		wp_enqueue_style( 'woothemes-our-team-admin' );
	} // End enqueue_admin_styles()

	/**
	 * Get the settings for the custom fields.
	 * @since  1.0.0
	 * @return array
	 */
	public function get_custom_fields_settings () {
		$fields = array();

		$fields['gravatar_email'] = array(
		    'name' 				=> __( 'Gravatar E-mail Address', 'woothemes-our-team' ),
		    'description' 		=> sprintf( __( 'Enter in an e-mail address, to use a %sGravatar%s, instead of using the "Featured Image".', 'woothemes-our-team' ), '<a href="' . esc_url( 'http://gravatar.com/' ) . '" target="_blank">', '</a>' ),
		    'type' 				=> 'text',
		    'default' 			=> '',
		    'section' 			=> 'info'
		);

		if ( apply_filters( 'team_member_role', true ) ) {
			$fields['byline'] = array(
			    'name' 			=> __( 'Role', 'woothemes-our-team' ),
			    'description' 	=> __( 'Enter a byline for the team member (for example: "Director of Production").', 'woothemes-our-team' ),
			    'type' 			=> 'text',
			    'default' 		=> '',
			    'section' 		=> 'info'
			);
		}

		if ( apply_filters( 'team_member_url', true ) ) {
			$fields['url'] = array(
			    'name' 			=> __( 'URL', 'woothemes-our-team' ),
			    'description' 	=> __( 'Enter this team member\'s URL (for example: http://woothemes.com/).', 'woothemes-our-team' ),
			    'type' 			=> 'url',
			    'default' 		=> '',
			    'section' 		=> 'info'
			);
		}

		if ( apply_filters( 'team_member_twitter', true ) ) {
			$fields['twitter'] = array(
			    'name' 			=> __( 'Twitter Username', 'woothemes-our-team' ),
			    'description' 	=> __( 'Enter this team member\'s Twitter username without the @ (for example: woothemes).', 'woothemes-our-team' ),
			    'type' 			=> 'text',
			    'default' 		=> '',
			    'section' 		=> 'info'
			);
		}

		return apply_filters( 'member_fields', $fields );
	} // End get_custom_fields_settings()

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
			'limit' 		=> 12,
			'orderby' 		=> 'menu_order',
			'order' 		=> 'DESC',
			'id' 			=> 0,
			'category' 		=> 0
		);

		$args = wp_parse_args( $args, $defaults );

		// Allow child themes/plugins to filter here.
		$args = apply_filters( 'woothemes_get_our_team_args', $args );

		// The Query Arguments.
		$query_args 						= array();
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
		load_plugin_textdomain( 'woothemes-our-team', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'woothemes-our-team';
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
} // End Class
