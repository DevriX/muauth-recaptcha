<?php

// add settings tab
add_filter('muauth_network_settings_tabs', 'muauth_recaptcha_settings_tab');
// admin notices
add_action('muauth_super_admin_notices', 'muauth_recaptcha_admin_alerts');
// meta
add_filter( 'network_admin_plugin_action_links_' . MUAUTH_RECAPTCHA_BASE, 'muauth_recaptcha_admin_plugin_links');