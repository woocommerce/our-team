<?php
/**
 * Plugin Name: Our Team
 * Plugin URI: http://woothemes.com/
 * Description: Hi, I'm your team profile management plugin for WordPress. Show off what your team members using our shortcode, widget or template tag.
 * Author: WooThemes
 * Version: 1.4.2
 * Author URI: http://woothemes.com/
 * Text Domain: our-team-by-woothemes
 *
 * @package WordPress
 * @subpackage Woothemes_Our_Team
 * @author Matty
 * @since 1.0.0
 */

require_once( 'classes/class-woothemes-our-team.php' );
require_once( 'classes/class-woothemes-our-team-taxonomy.php' );
require_once( 'woothemes-our-team-template.php' );
require_once( 'classes/class-woothemes-widget-our-team.php' );
global $woothemes_our_team;
$woothemes_our_team = new Woothemes_Our_Team( __FILE__ );
$woothemes_our_team->version = '1.4.2';