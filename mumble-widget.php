<?php
/*
Plugin Name: Mumble Widget
Plugin URI: http://legionofsittingducks.com/
Description: Displays users on a mmo-mumble server
Author: Reznix
Author URI: http://codeangel.org/
Version: 0.1
Text Domain: mumble-widget
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
namespace com\legionofsittingducks;

class MumbleWidget extends \WP_Widget {
	function __construct() {
		parent::__construct("mumble", 'Mumble Widget');
	}
	
	function widget($args, $instance) {
		extract($args);
		$mumbleContents = file_get_contents("http://mmo-mumble.com/account/servers/".$instance['server-id']."/status.json?token=".$instance['api-key']."&secret=".$instance['api-secret']);
		$mumble = json_decode($mumbleContents);
		echo $before_widget;
		echo $before_title."Mumble".$after_title;
		?>
		<link rel="stylesheet" href="<?php echo plugins_url('mumble-widget.css', __FILE__); ?>" type="text/css" media="screen" />
		<div style="height: 500px;" id="mumble-widget-div">
		<?php $this->display_channel("-1", $mumble); ?>
		<div>
		<?php
		echo $after_widget;
	}
	
	protected function display_channel($parent, $mumble) {
		?> <ul class="mumble-widget-channel"> <?php
		foreach($mumble->channels as $channel) {
			if($channel->parent == $parent) {
				?><li><img src="<?php echo plugins_url('images/chat.png', __FILE__); ?>" /><div><?php echo $channel->name; ?></div>
				<?php $this->display_users($channel->id, $mumble); $this->display_channel($channel->id, $mumble); ?></li>
				<?php
			}
		}
		?></ul><?php
	}
	
	protected function display_users($id, $mumble) {
		?> <ul class="mumble-widget-user"> <?php
		foreach($mumble->users as $user) {
			if($user->channel == $id) {
				?><li><img src="<?php echo plugins_url('images/user.png', __FILE__); ?>" /><div><?php echo $user->name;?></div></li><?php
			}
		}
		?></ul><?php
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		foreach($new_instance as $k => $v) {
			$new_instance[$k] = trim($v);	
		}
		if(filter_var($new_instance['server-id'], FILTER_VALIDATE_INT) !== false) {
			$instance['server-id'] = $new_instance['server-id']; 
		}
		if(preg_match('#^[a-f0-9]+$#', $new_instance['api-key'])) {
			$instance['api-key'] = $new_instance['api-key']; 
		}
		if(preg_match('#^[a-f0-9]+$#', $new_instance['api-secret'])) {
			$instance['api-secret'] = $new_instance['api-secret']; 
		}
		return $instance;
	}
	
	function form($instance) {
		$defaults = array("server-id" => "", "api-key" => "", "api-secret" => "");
		$instance = wp_parse_args((array)$instance, $defaults);
		?>
		<p>
		    <label for="<?php echo $this->get_field_id('server-id'); ?>">Server ID:</label>
		    <input type="text" id="<?php echo $this->get_field_id('server-id'); ?>" name="<?php echo $this->get_field_name('server-id'); ?>" value="<?php echo $instance['server-id']; ?>" style="width: 100%" />
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id('api-key'); ?>">API Key:</label>
		    <input type="text" id="<?php echo $this->get_field_id('api-key'); ?>" name="<?php echo $this->get_field_name('api-key'); ?>" value="<?php echo $instance['api-key']; ?>" style="width: 100%" />
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id('api-secret'); ?>">API Secret:</label>
		    <input type="text" id="<?php echo $this->get_field_id('api-secret'); ?>" name="<?php echo $this->get_field_name('api-secret'); ?>" value="<?php echo $instance['api-secret']; ?>" style="width: 100%" />
		</p>
		
		<?php
	}
}

add_action('widgets_init',function(){
     return register_widget('com\legionofsittingducks\MumbleWidget');
});