<?php

function muauth_recaptcha_textdomain() {
    load_plugin_textdomain(MUAUTH_RECAPTCHA_DOMAIN, FALSE, dirname(MUAUTH_RECAPTCHA_BASE).'/languages');
}

function muauth_recaptcha_custom_settings() {
    global $muauth_recaptcha_custom_settings;

    if ( isset($muauth_recaptcha_custom_settings) )
        return $muauth_recaptcha_custom_settings;

    $muauth_recaptcha_custom_settings = get_site_option('muauth_recaptcha_settings', array());

    return $muauth_recaptcha_custom_settings;
}

function muauth_recaptcha_parse_settings() {
    // default settings
    global $muauth_recaptcha;
    // custom settings
    $custom = muauth_recaptcha_custom_settings();
    // parse
    $settings = wp_parse_args( $custom, (array) $muauth_recaptcha );
    // pluggable
    $muauth_recaptcha = apply_filters( 'muauth_recaptcha_settings', (object) $settings, $custom );

    if ( !empty($muauth_recaptcha->public) && !empty($muauth_recaptcha->secret) ) {
        do_action('muauth_recaptcha_ready', $muauth_recaptcha);
    }
}

function muauth_recaptcha_set_availability($recaptcha) {
    // back-compat
    $recaptcha->avail = true;

    if ( $recaptcha->logged_in_disable ) {
        $recaptcha->avail = !is_user_logged_in();
    } else if ( $recaptcha->disable_roles && is_user_logged_in() ) {
        global $current_user;

        if ( $current_user->roles ) {
            foreach ( $current_user->roles as $role ) {
                if ( in_array($role, $recaptcha->disable_roles) ) {
                    $recaptcha->avail = false;
                    break;
                }
            }
        }
    }

    $GLOBALS['muauth_recaptcha'] = $recaptcha;
}

function muauth_recaptcha_load_recaptcha($recaptcha) {
    require_once MUAUTH_RECAPTCHA_DIR . (
        'includes/lib/recaptcha/src/autoload.php'
    );

    if ( !class_exists('\ReCaptcha\ReCaptcha') ) {
        // set to false, no class loaded
        $recaptcha->avail = false;
    }
}

function muauth_recaptcha() {
    global $muauth_recaptcha;

    if ( !isset($muauth_recaptcha->secret) )
        return;

    return new \ReCaptcha\ReCaptcha($muauth_recaptcha->secret);
}

function muauth_recaptcha_success() {
    $success = false;

    if ( isset($_POST['g-recaptcha-response']) ) {
        $recaptcha = muauth_recaptcha();
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
            $success = true;
        }
    }

    return apply_filters('muauth_recaptcha_success', $success);
}

function muauth_recaptcha_enqueue_scripts($recaptcha) {
    if ( isset($recaptcha->avail) && !$recaptcha->avail )
        return;

    return wp_enqueue_script('recaptcha', "https://www.google.com/recaptcha/api.js?hl={$recaptcha->locale}");
}

function muauth_recaptcha_field($recaptcha='') {
    printf(
        '<span class="g-recaptcha" data-sitekey="%s" data-tabindex="%d"></span>',
        isset($recaptcha->public) ? $recaptcha->public : $GLOBALS['muauth_recaptcha']->public,
        muauth_tabindex(true)
    );
}

function muauth_recaptcha_append_recaptcha_error_id($ids) {
    return array_merge($ids, array('recaptcha'));
}

function muauth_recaptcha_ready($recaptcha) {
    if ( isset($recaptcha->avail) && !$recaptcha->avail )
        return;

    // check for components enabled
    if ( empty($recaptcha->components) )
        return;

    foreach ( $recaptcha->components as $component ) {
        $component = str_replace('-','', $component);
        if ( function_exists("muauth_recaptcha_ready_{$component}") ) {
            call_user_func("muauth_recaptcha_ready_{$component}", $recaptcha);
        }
    }
}

function muauth_recaptcha_validate() {
    if ( !muauth_recaptcha_success() )
        return muauth_add_error(
            'recaptcha',
            __('Please complete this test!', MUAUTH_RECAPTCHA_DOMAIN),
            'error'
        );
}

function muauth_recaptcha_parse_field() {
    ?>
    <p class="form-section<?php echo muauth_has_errors('recaptcha') ? ' has-errors' : ''; ?>">
        <?php muauth_recaptcha_field(); ?>

        <?php if ( muauth_has_errors('recaptcha') ) : ?>
            <?php muauth_print_error('recaptcha'); ?>
        <?php endif; ?>
    </p>
    <?php
}

function muauth_recaptcha_ready_login($recaptcha) {
    // parse field
    add_action('muauth_login_before_submit', 'muauth_recaptcha_parse_field');
    // parse login form (func) field    
    add_action('muauth_login_form_before_submit', 'muauth_recaptcha_parse_login_form_field');
    // validate
    add_action('muauth_pre_validate_login_auth', 'muauth_recaptcha_validate');
}

function muauth_recaptcha_parse_login_form_field($args) {
    ?>
    <p class="form-section<?php echo muauth_has_errors('recaptcha', $args['unique_id']) ? ' has-errors' : ''; ?>">
        <?php muauth_recaptcha_field(); ?>

        <?php if ( muauth_has_errors('recaptcha', $args['unique_id']) ) : ?>
            <?php muauth_print_error('recaptcha', $args['unique_id']); ?>
        <?php endif; ?>
    </p>
    <?php
}

function muauth_recaptcha_ready_lostpassword($recaptcha) {
    // parse field
    add_action('muauth_lostpassword_before_submit', 'muauth_recaptcha_parse_field');
    // validate
    add_action('muauth_pre_validate_lostpassword_auth', 'muauth_recaptcha_validate');
}

function muauth_recaptcha_ready_activation($recaptcha) {
    // parse field
    add_action('muauth_activation_before_submit', 'muauth_recaptcha_parse_field');
    // validate
    add_action('muauth_pre_validate_activation_auth', 'muauth_recaptcha_validate');
}

function muauth_recaptcha_ready_register($recaptcha) {
    // parse field
    add_action('muauth_register_before_submit', 'muauth_recaptcha_parse_field');

    $tags = array(
        'muauth_validate_register_1_error_returned',
        'muauth_validate_register_2_error_returned',
        'muauth_validate_register_pre_signup_username',
        'muauth_validate_register_pre_create_another_blog',
        'muauth_validate_register_pre_signup_user_blog'
    );

    // validate
    foreach ( $tags as $tag ) {
        add_action($tag, 'muauth_recaptcha_validate');
    }
}