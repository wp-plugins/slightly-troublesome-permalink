<?php
/*
Plugin Name: Slightly troublesome permalink
Plugin URI: http://elearn.jp/wpman/column/slightly-troublesome-permalink.html
Description: This plug-in controls the category in permalink. When the post belongs to two or more categories.
Author: tmatsuur
Version: 1.0.0
Author URI: http://12net.jp/
*/

/*
    Copyright (C) 2012 tmatsuur (Email: takenori dot matsuura at 12net dot jp)
           This program is licensed under the GNU GPL Version 2.
*/

define( 'SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN', 'slightly-troublesome-permalink' );
define( 'SLIGHTLY_TROUBLESOME_PERMALINK_DB_VERSION_NAME', 'slightly-troublesome-permalink-db-version' );
define( 'SLIGHTLY_TROUBLESOME_PERMALINK_BUTTON_DB_VERSION', '1.0.0' );
define( 'SLIGHTLY_TROUBLESOME_PERMALINK_OPTIONS', 'slightly-troublesome-permalink-options' );

$plugin_slightly_troublesome_permalink = new slightly_troublesome_permalink();
class slightly_troublesome_permalink {
	var $categories;
	var $options;
	function __construct() {
		load_plugin_textdomain( SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN, false, plugin_basename( dirname( __FILE__ ) ).'/languages' );
		register_activation_hook( __FILE__ , array( &$this , 'init' ) );
		$this->load_categories();
		$this->options = get_option( SLIGHTLY_TROUBLESOME_PERMALINK_OPTIONS, array( 'always'=>true, 'order'=>'' ) );
		add_filter( 'post_link', array( &$this, 'post_link' ), 10, 3 );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_meta' ), 9, 2 );
		add_action( 'admin_menu' , array( &$this , 'admin_menu' ) );
		if ( isset( $_GET['page'] ) && $_GET['page'] == plugin_basename( dirname( __FILE__ ) ).'/'.basename( __FILE__ ) )
			add_action( 'admin_head' , array( &$this , 'admin_head' ) );
	}
	function init() {
		if ( get_option( SLIGHTLY_TROUBLESOME_PERMALINK_DB_VERSION_NAME ) != SLIGHTLY_TROUBLESOME_PERMALINK_DB_VERSION ) {
			update_option( SLIGHTLY_TROUBLESOME_PERMALINK_DB_VERSION_NAME, SLIGHTLY_TROUBLESOME_PERMALINK_DB_VERSION );
		}
	}
	function admin_menu() {
		add_options_page( __( 'Priority of category for permalink', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ), __( 'Priority of category', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ), 9, __FILE__, array( &$this, 'settings' ) );
	}
	function plugin_meta( $links, $file ) {
		if ( $file == plugin_basename( dirname( __FILE__ ) ).'/'.basename( __FILE__ ) ) {
			$links[] = '<a href="options-general.php?page='.$file.'">'.__( 'Settings' ).'</a>';
		}
		return $links;
	}
	function load_categories() {
		$categories = get_categories( 'get=all' );
		$this->categories = array();
		foreach ( $categories as $cat ) {
			$this->categories[$cat->term_id] = $cat;
		}
	}
	function admin_head() {
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		$this->add_css();
		$this->add_js();
	}
	function add_css() {
?>
<style type="text/css">
<!--
.form-table {
	width: 75em;
}
.form-table td {
	vertical-align: top;
	width: 42%;
}
.form-table td.center {
	width: 16%;
	vertical-align: middle;
	text-align: center;
}
.form-table td.center div {
	line-height: 150%;
}
.form-table td label {
	padding-left: 0.5em;
}
#priority-category, #categories-tree {
	border: 1px solid #CCCCCC;
	padding: 0.25em 0.25em 0 0.25em;
	min-height: 15.5em;
	max-height: 31em;
	overflow: auto;
}
#priority-category li,
#categories-tree li span,
body > li.ui-draggable-dragging > span {
	display: block;
	border: 1px solid #999999;
	margin-bottom: 0.25em;
	cursor: pointer;
	padding: 0.3em 0.5em 0.2em 0.5em;
	border-radius: 0.5em;
	-webkit-border-radius: 0.5em;
	-moz-border-radius: 0.5em;
	background-color: #F0F0F0;
	background-image: -ms-linear-gradient(bottom, #DDDDDD, #F8F8F8); /* IE10 */
	background-image: -moz-linear-gradient(bottom, #DDDDDD, #F8F8F8); /* Firefox */
	background-image: -o-linear-gradient(bottom, #DDDDDD, #F8F8F8); /* Opera */
	background-image: -webkit-gradient(linear, left bottom, left top, from(#DDDDDD), to(#F8F8F8)); /* old Webkit */
	background-image: -webkit-linear-gradient(bottom, #DDDDDD, #F8F8F8); /* new Webkit */
	background-image: linear-gradient(bottom, #DDDDDD, #F8F8F8); /* proposed W3C Markup */
}
#priority-category li.ui-state-placeholder {
	height: 20px;
	border: 1px dotted #FF8800;
	background-color: #FFFFFF;
	background-image: none;
}
#categories-tree li,
body > li.ui-draggable-dragging {
	margin-bottom: 0.25em;
}
#categories-tree li ul,
body > li.ui-draggable-dragging > ul {
	margin-top: 0.25em;
}
#categories-tree li ul li,
body > li.ui-draggable-dragging > ul > li {
	padding-left: 1.25em;
}
body > li.ui-draggable-dragging {
/*	z-index: 1000;*/
	min-width: 25em;
	list-style: none;
	line-height: 20px;
	font-size: 12px;
}
#priority-category li.ui-sortable-helper,
body > li.ui-draggable-dragging > span {
	box-shadow: 0.5em 0.5em 1em #AAAAAA;
	-moz-box-shadow: 0.5em 0.5em 1em #AAAAAA; /* Firefox用 */
	-webkit-box-shadow: 0.5em 0.5em 1em #AAAAAA; /* Safari,Chrome用 */
}
#categories-tree li.ui-draggable-disabled > span {
	border: 1px dotted #FF8800;
	background-color: #FFFFFF;
	background-image: none;
}
body > li.ui-draggable-dragging > ul {
	color: #C0C0C0;
}
body > li.ui-draggable-dragging > li > span {
	border: 1px solid #CCCCCC;
}
#priority-category li.ui-draggable-disabled  span {
	border: 1px dotted #FFC080;
}
#categories-tree li > ul,
body > li.ui-draggable-dragging > ul {
	display: none;
}
#categories-tree li.open > ul {
	display: block;
}
#priority_cation {
	display: none;
	border: 1px solid #FF0000;
	background-color: #FFEEEE;
	margin-bottom: 1em;
	padding: 0.25em;
	text-align: left;
}

#categories-tree li span.hasChildren em {
	float: left;
	display: block;
	height: 15px;
	width: 15px;
	margin: 1px 3px 0 0;
	background: transparent url(../../wp-admin/images/arrows.png) no-repeat 0 0px;
	border: 1px solid #AAAAAA;
	border-radius: 8px;
	-webkit-border-radius: 8px;
	-moz-border-radius: 8px;
}
#categories-tree li.open > span.hasChildren > em {
	background: transparent url(../../wp-admin/images/arrows.png) no-repeat 0 -36px;
}
-->
</style>
<?php
	}
	function add_js() {
?>
<script type="text/javascript">
//<![CDATA[
( function($) {
	jQuery.event.add( window, 'load', function () {
		$( '#priority-category' ).height( $( '#categories-tree' ).height() );
		$( '#priority-category' ).sortable( {
			connectWith: 'ul',
			placeholder: 'ui-state-placeholder',
			stop: function( event, ui ) {
				$( '#priority_cation' ).hide();
				if ( $('#always_higher:checked').length > 0 ) {
					var catID = new Array();
					$(this).children( 'li' ).each( function () {
						catID.push( 'li.'+$(this).attr( 'class' ) );
					} );
					while ( catID.length > 1 ) {
						var curID = catID.pop();
						var prevID = catID.join( ',' );
						if ( $( '#categories-tree '+curID ).parents( prevID ).length > 0 ) {
							$(this).sortable( 'cancel' );
							$( '#priority_cation' ).html( '<?php echo esc_html( __( 'A parent category is unmovable above a child category.', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?>' ).fadeIn();
							break;
						}
					}
				}
				if ( $( '#priority_cation:visible' ).length == 0 ) {
					var categories = '';
					$(this).children( 'li' ).each( function () {
						categories += $(this).attr( 'class' )+' ';
					} );
					$( '#priority_order' ).val( jQuery.trim( categories ) );
				}
			}
		} ).droppable( {
			drop: function( event, ui ) {
				if ( $(this).attr('id') != ui.draggable.parent().attr('id') ) {
					$( '#priority_cation' ).hide();
					ui.draggable.draggable( { disabled: true } );
					ui.draggable.attr( 'class' ).match(/cat\-item\-[0-9]+/);
					var current = RegExp.lastMatch;
					var newItem = '<li class="'+current+'"><span>'+ui.draggable.children( 'span' ).html()+'</span></li>';
					if ( $('#always_higher:checked').length > 0 && $(this).children( 'li' ).length > 0 ) {
						var order = '';
						$(this).children( 'li' ).each( function () {
							prevID = $(this).attr( 'class' );
							if ( newItem != '' && $( '#categories-tree li.'+current ).parents( 'li.'+prevID ).length > 0 ) {
								$(this).before( newItem );
								newItem = '';
								order += current+' ';
							}
							order += prevID+' ';
						} );
						if ( newItem == '' )
							$( '#priority_order' ).val( jQuery.trim( order ) );
					}
					if ( newItem != '' ) {
						$(this).append( newItem );
						$( '#priority_order' ).val( jQuery.trim( $( '#priority_order' ).val()+' '+current ) );
					}
				}
			}
		} ).disableSelection();
		$( '#categories-tree' ).droppable( {
			drop: function( event, ui ) {
				if ( ui.draggable.parents( '#'+$(this).attr('id') ).length == 0 ) {
					ui.draggable.attr( 'class' ).match(/cat\-item\-[0-9]+/);
					current = RegExp.lastMatch;
					$(this).find( '.'+current ).draggable( { disabled: false } );
					$( '#priority_order' ).val( jQuery.trim( $( '#priority_order' ).val().replace( current, '' ).replace( '  ', ' ' ) ) );
					ui.draggable.remove();
				}
			}
		} ).disableSelection();
		$( '#categories-tree li' ).draggable( {
				appendTo: 'body',
				containment: 'window',
				scroll: false,
				helper: 'clone',
				stop: function ( event, ui ) { $(this).attr( 'style', 'position: relative; ' ); }
		} ).disableSelection();
		$( '#reset_priority' ).click( function () {
			$( '#priority_cation' ).hide();
			$( '#priority-category' ).html( '' );
			$( '#priority_order' ).val( '' );
			$( '#categories-tree' ).find( '.ui-draggable-disabled' ).draggable( { disabled: false } );
		} );
		$( '#always_higher' ).click( function () {
			$( '#priority_cation' ).html( '' ).hide();
			if ( $(this).attr( 'checked' ) == 'checked' ) {
				var catID = $( '#priority_order' ).val().split( ' ' );
				while ( catID.length > 1 ) {
					var curID = catID.pop();
					var moveParent = false;
					$( '#categories-tree li.'+curID ).parents( 'li.'+catID.join( ',li.' ) ).each( function () {
						if ( !moveParent ) {
							moveParent = true;
							$(this).attr( 'class' ).match(/cat\-item\-[0-9]+/);
							parentID = RegExp.lastMatch;
							$( '#priority-category li.'+curID ).after( $( '#priority-category li.'+parentID ) );
							$( '#priority_cation' ).html( '<?php echo esc_html( __( 'The priority of the category was adjusted.', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?>' );
						}
					} );
				}
				if ( $( '#priority_cation' ).html() != '' ) {
					var newOrder = '';
					$( '#priority-category li' ).each( function () {
						newOrder += $(this).attr( 'class' )+' ';
					} );
					$( '#priority_order' ).val( jQuery.trim( newOrder ) );
					$( '#priority_cation' ).fadeIn();
				}
			}
		} );
		$( '#categories-tree ul.children' ).each( function () {
			$(this).prev( 'span.item' ).addClass( 'hasChildren' ).each( function () {
				$(this).html( '<em></em>'+$(this).html()  );
			} ).click( function () {
				$(this).parent().toggleClass( 'open' );
			} );
		} );
	} );
} )( jQuery );
//]]>
</script>
<?php
	}
	function settings() {
		$message = '';
		if ( isset( $_POST['high_priority'] ) ) {
			$this->options['always'] = ( $_POST['high_priority']['always'] )? true: false;
			$this->options['order'] = $_POST['high_priority']['order'];
			update_option( SLIGHTLY_TROUBLESOME_PERMALINK_OPTIONS, $this->options );
			$message = __( 'Settings saved.' );
		}
		if ( $this->options['order'] != '' ) {
			$order_ids = explode( ' ', $this->options['order'] );
			foreach ( $order_ids as $key=>$id ) {
				$order_ids[$key] = str_replace( 'cat-item-', '', $id );
			}
		} else
			$order_ids = array();
?>
<div id="priority-of-category-for-permalink-settings" class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2><?php echo esc_html( __( 'Priority of category for permalink', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?></h2>
<?php if ( $message != '' ) {?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php } ?>
<form method="post" action="<?php print( str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ) ); ?>">
<p><?php echo esc_html( __( 'Please drag a category to give priority to from a right side list, and drop to left side.', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?><br />
<?php echo esc_html( __( 'When you raise the priority of a category, drag a category in a left side list, and drop to upwards.', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?></p>
<table summary="Priority of category setting" class="form-table">
<tbody>
<tr>
<td>
<h3><?php echo esc_html( __( 'Priority of category', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?></h3>
<ul id="priority-category">
<?php foreach ( $order_ids as $id ) { if ( isset( $this->categories[$id] ) ) { ?>
<li class="cat-item-<?php echo $id; ?>"><span><?php echo esc_html( $this->categories[$id]->name ); ?></span></li>
<?php } } ?>
</ul>
</td>
<td class="center">
<div id="priority_cation">&nbsp;</div>
<div id="reset_priority" class="button-secondary"><?php echo esc_html( __( 'Reset &raquo;', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?></div>
</td>
<td>
<h3><?php echo esc_html( __( 'Categories', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?></h3>
<ul id="categories-tree">
<?php
$out = preg_replace( "/<a.+>(.+)<\/a>/", "<span class=\"item\">\\1</span>", wp_list_categories( 'echo=0&hide_empty=0&hierarchical=1&title_li=' ) );
foreach ( $order_ids as $id ) {
	$out = str_replace( ' cat-item-'.$id, ' cat-item-'.$id.' ui-draggable-disabled ui-state-disabled', $out );
}
echo $out;
?>
</ul>
</td>
</tr>
<tr>
<td colspan="3" class="options">
<input type="hidden" id="priority_order" name="high_priority[order]" value="<?php echo $this->options['categories']; ?>" />
<input type="checkbox" id="always_higher" name="high_priority[always]" value="1" <?php checked( $this->options['always'] ); ?>/><label for="always_higher"><?php echo esc_html( __( 'The priority of a child category is always made higher than a parent category.', SLIGHTLY_TROUBLESOME_PERMALINK_DOMAIN ) ); ?></label>
</td>
</tr>
</tbody>
</table>
<?php submit_button(); ?>
</form>
</div>
<?php
	}
	function post_link( $permalink, $post, $leavename ) {
		$permalink_structure = get_option( 'permalink_structure' );
		if ( !in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft') ) &&
			strpos( $permalink_structure, '%category%' ) !== false ) {
			$hight_priority_categories = $this->hight_priority_categories();
			foreach ( $hight_priority_categories as $cat ) {
				if ( in_category( $cat->term_id, $post ) ) {
					$rewritecode = array(
						'%year%',
						'%monthnum%',
						'%day%',
						'%hour%',
						'%minute%',
						'%second%',
						$leavename? '' : '%postname%',
						'%post_id%',
						'%category%',
						'%author%',
						$leavename? '' : '%pagename%',
					);
					$unixtime = strtotime( $post->post_date );
					$date = explode( ' ', date( 'Y m d H i s', $unixtime ) );
					$category = $cat->slug;
					if ( $cat->parent != 0 )
						$category = get_category_parents( $cat->parent, false, '/', true ).$category;
					$author = '';
					if ( strpos( $permalink, '%author%' ) !== false ) {
						$authordata = get_userdata( $post->post_author );
						$author = $authordata->user_nicename;
					}
					$rewritereplace = array(
						$date[0],
						$date[1],
						$date[2],
						$date[3],
						$date[4],
						$date[5],
						$post->post_name,
						$post->ID,
						$category,
						$author,
						$post->post_name,
					);
					$lang_prefix = ( function_exists( 'qtrans_getLanguage' ) && get_option( 'qtranslate_url_mode' ) == 2 )? '/'.qtrans_getLanguage(): '';
					$permalink = home_url( $lang_prefix.str_replace( $rewritecode, $rewritereplace, $permalink_structure ) );
					$permalink = user_trailingslashit( $permalink, 'single' );
					break;
				}
			}
		}
		return $permalink;
	}
	private function _get_category_child_of( $term_id ) {
		$children = array();
		foreach ( $this->categories as $cat ) {
			if ( $cat->parent == $term_id && $cat->term_id != $term_id ) {
				if ( $this->options['always'] ) {
					foreach ( $this->_get_category_child_of( $cat->term_id ) as $child ) {
						if ( !in_array( $child, $children ) )
							$children[] = $child;
					}
				}
				$children[] = $cat;
			}
		}
		return $children;
	}
	private function hight_priority_categories() {
		$cats = array();
		if ( $this->options['order'] != '' ) {
			foreach ( (array)explode( ' ', $this->options['order'] ) as $item ) {
				$id = str_replace( 'cat-item-', '', $item );
				if ( isset( $this->categories[$id] ) ) {
					$cat = $this->categories[$id];
					if ( $this->options['always'] )
						$cats = array_merge( $cats, $this->_get_category_child_of( $cat->term_id ) );
					$cats[] = $cat;
				}
			}
		}
		return $cats;
	}
}
?>