<?php

//Social Media Settings
function espresso_add_social_to_admin_menu($espresso_manager) {
	add_submenu_page('events', __('Event Espresso - Social Media Settings', 'event_espresso'), __('Social Media', 'event_espresso'), apply_filters('filter_hook_espresso_management_capability', 'administrator', $espresso_manager['espresso_manager_social']), 'espresso_social', 'espresso_social_config_mnu');
}

add_action('action_hook_espresso_add_new_submenu_to_group_settings', 'espresso_add_social_to_admin_menu', 30);