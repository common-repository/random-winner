<?php
/**
* Plugin Name:Random Winner
* Plugin URI: anjitvishwakarma28.wordpress.com
* Description: this plugin use to select Winners from random table.
* Version:1.0
* Author: Anjit Vishwakarma
* Author URI:anjitvishwakarma28.wordpress.com
* License: GPL2
*/
// Add Shortcode
add_action( 'admin_menu', 'aj_init' );

function aj_init(){
add_menu_page('Winners', "Winners", "manage_options", "winners", "winners",plugins_url('ico.png',__FILE__));
add_action( 'admin_init', 'update_winsettings' );
}

function create_win_database_table() {
require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

$table_name = $wpdb->prefix . 'aj_winners';
$sql = "CREATE TABLE $table_name (
id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
email varchar(50) NOT NULL,
PRIMARY KEY  (id)
);";

dbDelta($sql);
}

register_activation_hook( __FILE__, 'create_win_database_table' );

function aj_scripts_method() {
wp_enqueue_script( 'winjs',plugins_url('/win.js',__FILE__));
}
add_action( 'admin_enqueue_scripts', 'aj_scripts_method' );

if( !function_exists("update_winsettings") )
{
function update_winsettings() {
register_setting( 'update_winsettings', 'tables' );
register_setting( 'update_winsettings',  'field_name');
register_setting( 'update_winsettings',  'form_name');
register_setting( 'update_winsettings',  'recent_win');
}
}

if(isset($_REQUEST["submit"])){ 
update_option('tables',sanitize_text_field($_REQUEST['tables']));    
update_option('field_name',sanitize_text_field($_REQUEST['field_name']));    
update_option('form_name',sanitize_text_field($_REQUEST['form_name']));    
update_option('recent_win',sanitize_text_field($_REQUEST['recent_win']));    
echo '<div id="message" class="updated fade"><p>Options Updates</p></div>';
}
function winners(){
?>
<style type="text/css">
.lucky {
font-size: 26px;
padding: 20px;
background-image: url(<?php echo plugins_url('congratulation-g.gif',__FILE__)?>);
background-size: 150px 60px;
text-transform: capitalize;
color: #000000;
line-height: 75px;
text-shadow: 0px 0px 5px #0C0000;
text-align: center;
}
.recent_lucky {
font-size: 14px;
background-color: rgba(179, 172, 164, 0.2); 
padding: 2px;
text-transform: capitalize;
color: #000000;
width: 50%;
background-repeat: no-repeat;
background-size: contain;
padding-left: 33px;
background-image: url(<?php echo plugins_url('Trophy.png',__FILE__)?>);
}
.col-6{
width: 50%;	
float: left;
}
.col-12{
width: 100%;
float: left;	
}
</style>
<div class="col-6">
<h1>Winners</h1>
<p>Choose table From You want to select winner</p>
<form method="post" action="options.php" >
<?php settings_fields( 'update_winsettings' ); ?>
<?php do_settings_sections( 'update_winsettings' ); ?>
<div class="form-group">
<label for="exampleInputPassword1">Select Table</label>
<select name="tables" id="win_table">
<?php 
global $wpdb; 
$results =  $wpdb->get_results($wpdb->prepare("SHOW TABLES LIKE '%s'",'%'));
foreach($results as $index => $value) {
foreach($value as $tableName) {
echo "<option value='$tableName'";
if(get_option( 'tables' )==$tableName) echo 'selected';
echo ">$tableName</option>";
}
}
?> 
</select>   
</div>
<div class="form-group">
<label for="exampleInputPassword1">Field Name</label>
<input type="text" class="form-control" id="field_name"   name="field_name" <?php if(get_option( 'field_name' )):?>value="<?php echo get_option( 'field_name' );?>"<?php endif;?>>
</div>
<p>If you want to select the Winner form Contact form 7 DB</p>
<div class="form-group">
<label for="exampleInputPassword1">Form Name</label>
<input type="text" class="form-control field_name" id="form_name"  name="form_name" <?php if(get_option( 'form_name' )):?>value="<?php echo get_option( 'form_name' );?>"<?php endif;?>>
</div>
<p>Are you want to add Recent winners in This Contest?</p>
<div class="form-group">
<label for="exampleInputPassword1">If Yes,Please Check it</label>
<input type="checkbox" class="form-control" id="recent_win"  name="recent_win" <?php if(get_option( 'recent_win' )):?> checked <?php endif;?>>
</div>
<?php submit_button(); ?>
</form>
</div>
<div class="col-6">
<h1>Previous Winners</h1>
<?php 
$recresults = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->prefix"."aj_winners order by id DESC limit %d",'5'), OBJECT );
foreach ($recresults as $result):
$recentwinlist[]=$result->email;
echo "<p class='recent_lucky'>".$result->email."</p>";
endforeach; 
?> 
</div>
<div class="col-12">	
<p><input type="button" name="Winner" id="winner" class="button button-primary" value="Luck Winner"></p>
<div id="winners"></div>
</div>
<?php
}

function aj_ajax(){
global $wpdb;
$table=$_REQUEST['win_table'];
$field=$_REQUEST['field_name'];
$form_name=$_REQUEST['form_name'];
$recent=$_REQUEST['recent'];
if($form_name):
$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE form_name = '%s' and field_name='%s'",$form_name,$field), OBJECT );
foreach ($results as $result):
$winlist[]=$result->field_value;
endforeach; 
else:
$results = $wpdb->get_results($wpdb->prepare("SELECT $field FROM $table order by  %s",'DESC'), OBJECT );
foreach ($results as $result):
$winlist[]=$result->$field;
endforeach; 
endif;
$recresults = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->prefix"."aj_winners order by  %s",'DESC'), OBJECT );
foreach ($recresults as $result):
$recentwinlist[]=$result->email;
endforeach; 
if($recent=='true'):
$winner= $winlist[array_rand($winlist)];
else:
if(isset($recentwinlist)):
$new_winlist=@array_diff($winlist,$recentwinlist);
$winner= @$new_winlist[@array_rand($new_winlist)];
else:
$winner= $winlist[array_rand($winlist)];	
endif;	
endif;	
echo '<div class="lucky">';
if($winner):
$results =$wpdb->insert($wpdb->prefix.'aj_winners',array('email'=>$winner));
echo $winner;
else:
echo 'There is no any New Member Who will be a Winner';
endif;	
echo '</div>';
die();
}
add_action( 'wp_ajax_aj_ajax', 'aj_ajax' );
?>