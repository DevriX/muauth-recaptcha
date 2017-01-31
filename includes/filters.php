<?php

// load i18n
add_action('plugins_loaded', 'muauth_recaptcha_textdomain');
// parse custom settings
add_action('init', 'muauth_recaptcha_parse_settings');
// set availability
add_action('muauth_recaptcha_ready', 'muauth_recaptcha_set_availability');
// load lib
add_action('muauth_recaptcha_ready', 'muauth_recaptcha_load_recaptcha', 12);
// enqueue scripts
add_action('muauth_recaptcha_ready', 'muauth_recaptcha_enqueue_scripts', 13);
// init
add_action('muauth_recaptcha_ready', 'muauth_recaptcha_ready', 14);
// hide recaptcha errors from template header and add as inline
add_filter('muauth_parse_template_errors_exclude_codes', 'muauth_recaptcha_append_recaptcha_error_id');