<?php
/*!
Plugin Name: Sorry AdBlocker
Plugin URI: http://openwill.jp/
Description: Restrict AdBlocker's views.
Version: 0.1
Author: Ukyo.will
Text Domain: sorry-adblocker
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

add_action( 'wp_head', 'sab_head', 999 );
add_action( 'wp_enqueue_scripts', 'sab_enqueue_script' );
add_action( 'admin_menu', 'sab_admin_menu' );
add_action( 'plugins_loaded', 'sab_load_textdomain' );
add_action( 'wp_ajax_getcountryuser', 'adblocker_ajax_callback' );
add_action( 'wp_print_footer_scripts', 'sab_footer' );

register_activation_hook( __FILE__, 'sab_activation' );

function sab_activation() {
    add_option( 'mssg', sab_get_defalt_message() );
    add_option( 'ttle', sab_get_defalt_title() );
}

function sab_head() {
    //Load style
    sab_head_style();

    // Set lasted post ID
    $lasted_post = wp_get_recent_posts( array('numberposts' => '1', 'post_status' => 'publish') );
    if ( isset( $lasted_post[0] ) && $lasted_post[0] ) {
        echo "<script>var lasted_post_id = {$lasted_post[0]['ID']}</script>";
    }
    return ;
}

function sab_head_style() {
    echo "<style>
.sab-alert-danger {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1; }
.sab-alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px; }
.sab-alert h2 {
    margin: 1em; }
</style>";
}

function sab_enqueue_script() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'sab', plugins_url( '/js/sab.js' , __FILE__ ), false, false, true );
}

function sab_admin_menu() {
    add_menu_page( 'Sorry AdBlocker Settings', __('Sorry AdBlocker Settings','sorry-adblocker'), 'manage_options', 'sorry-adblocker', 'sab_admin_setting');
}

function sab_admin_setting() {
    $msg = '';
    if ( isset($_POST['mssg']) || isset($_POST['ttle'])) {
        check_admin_referer('srrrrrry');
        if ( isset($_POST['mssg']) ) {
            $msg = $_POST['mssg'];
            $msg = stripslashes($msg);
            update_option('mssg', $msg);
        }
        if ( isset($_POST['ttle']) ) {
            $ttle = $_POST['ttle'];
            update_option('ttle', $ttle);
        }

        ?><div class="updated fade"><p><strong><?php _e('Options saved.'); ?></strong></p></div><?php
    }
    $msg = get_option('mssg');
    $ttle = get_option('ttle');

?>
<form name="adminsettings" action="" id="adminsettings" method="POST" enctype="multipart/form-data">
    <?php wp_nonce_field('srrrrrry'); ?>

    <h2><?php _e('Sorry AdBlocker Setting','sorry-adblocker'); ?></h2><br/>

    <table class="form-table"><tbody>
        <tr>
            <th scope="row"><label for="ttle"><?php _e('Message title','sorry-adblocker'); ?></label></th>
            <td><input type="text" id="ttle" name="ttle" size="80" value="<?php echo $ttle; ?>"></td>
        </tr>
        <tr>
            <th scope="row"><label for="mssg" style="vertical-align: top;"><?php _e('Display message','sorry-adblocker'); ?></label></th>
            <td><textarea name="mssg" id="mssg" rows="10" cols="60"><?php echo $msg; ?></textarea></td>
        </tr>
    </tbody></table>


    <h2><?php _e('Select valid detections','sorry-adblocker'); ?>(not ready)</h2>

    <table class="form-table"><tbody>
        <tr>
            <th scope="row"><label for="ttle"><?php _e('Fuck Adblock','sorry-adblocker'); ?></label></th>
            <td><label><input type="checkbox" name="valid[]" value="fab" checked="checked" disabled readonly /><?php _e('Enable','sorry-adblocker'); ?></label></td>
        </tr>
        <tr>
            <th scope="row"><label for="mssg" style="vertical-align: top;"><?php _e('AdSense load check','sorry-adblocker'); ?></label></th>
            <td><label><input type="checkbox" name="valid[]" value="adsense"  disabled readonly /><?php _e('Enable','sorry-adblocker'); ?></label></td>
        </tr>
    </tbody></table>
    <p class="submit"><input type="submit" value="<?php _e('Submit','sorry-adblocker'); ?>" class="button button-primary"></p>
</form>

    <hr>

<div>
    <h1><?php _e('Sample message code', 'sorry-adblocker'); ?></h1>
    <textarea rows="8" cols="60"><?php echo sab_get_defalt_message(); ?></textarea>
    <div><h4><?php _e('Template code', 'sorry-adblocker'); ?></h4>
        <p><b>{{previous_url}}</b> : <?php _e('previous posts URL', 'sorry-adblocker'); ?></p>
        <p><b>{{previous_title}}</b> : <?php _e('previous posts title', 'sorry-adblocker'); ?></p>
    </div>
</div>
<div>
    <h1><?php _e('Required theme code'); ?></h1>
    <p>For work this plugin, needs two HTML codes.<br>Post container elements (most themes use article tag) needs ID attribute include post ID. Like [id="post-123"].This number is the post ID. </p>
    <p>And post content container needs class attribute "entry-content".</p>
    <p>If don't work this plugin, check and edit theme.</p>
</div>
<?php
}

function sab_get_defalt_title() {
    return "Sorry, AdBlocker!";
}
function sab_get_defalt_message() {
    if ( get_option("WPLANG") == 'jax' ) {
        return '<p><strong>広告ブロック</strong>を使用しているユーザーには最新のコンテンツをお見せできません。</p>
<p>一つ前の記事はこちらからご覧に頂けます => <a href="{{previous_url}}">{{previous_title}}</a></p>
<p>This site needs Ads profit for operating this web site.</p>
';
    }
    return '<p>The user using an <strong>Ad Block</strong> software cannot see the latest contents.</p>
<p>You can see previous posts from here => <a href="{{previous_url}}">{{previous_title}}</a></p>
<p>We need ads profit to operate this site.</p>
';
}

function sab_load_textdomain() {
    load_plugin_textdomain( 'sorry-adblocker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}


function sab_footer() {
    $title = get_option('ttle');
    $msg = get_option('mssg');

    $lasted_post = wp_get_recent_posts( array('numberposts' => '2', 'post_status' => 'publish') );
    $previous_url = esc_url(get_permalink($lasted_post[1]['ID']));
    $previous_title = esc_html($lasted_post[1]['post_title']);
    $msg = str_replace('{{previous_url}}', $previous_url, $msg);
    $msg = str_replace('{{previous_title}}', $previous_title, $msg);
    echo "<div id='sab-mssg' style='display: none;'>"
        . "<h2>{$title}</h2>"
        . "<div>{$msg}</div>"
        . "</div>";
}