<?php

function muauth_recaptcha_settings_tab($tabs) {
    return array_merge($tabs, array(
        'recaptcha' => array(
            'contentCallback' => 'muauth_recaptcha_settings',
            'updateCallbak' => 'muauth_recaptcha_update_settings',
            'title' => _x('reCaptcha', 'settings title', MUAUTH_RECAPTCHA_DOMAIN)
        )
    ));
}

function muauth_recaptcha_settings() {
    global $muauth_raw_components;
    $raw = $muauth_raw_components;
    $roles = get_editable_roles();

    global $muauth_recaptcha, $muauth_recaptcha_locales;
    ?>
    <form method="post">

        <div class="section" id="recaptcha">
            <p><strong><?php _e('reCaptcha credentials:', MUAUTH_RECAPTCHA_DOMAIN); ?></strong></p>

            <p><?php _e('Before you setup this plugin, make sure to go to <a href="https://www.google.com/recaptcha" target="_blank">Google reCaptcha</a> website and register your site. After that, insert both public and secret captcha keys in the following fields. A <a href="https://www.google.com/search?q=how+to+get+google+recaptcha" target="_blank">tutorial</a> might also help.', MUAUTH_RECAPTCHA_DOMAIN); ?></p>

            <p>
                <label><?php _e('Enter your Google reCaptcha public key:', MUAUTH_RECAPTCHA_DOMAIN); ?><br/>
                <input type="text" name="recaptcha_public" size="50" value="<?php echo esc_attr($muauth_recaptcha->public); ?>" /></label>
            </p>

            <p>
                <label><?php _e('Enter your Google reCaptcha secret key:', MUAUTH_RECAPTCHA_DOMAIN); ?><br/>
                <input type="text" name="recaptcha_secret" size="50" value="<?php echo esc_attr($muauth_recaptcha->secret); ?>" /></label>
            </p>

            <p>
                <label><strong><?php _e('Where to require reCaptcha:', MUAUTH_RECAPTCHA_DOMAIN); ?></strong></label>
            </p>
            <p>
                <?php foreach ( array('login','register','lost-password','activation') as $component ) : ?>
                    <label>
                        <input type="checkbox" name="recaptcha_components[]" value="<?php echo $component; ?>" <?php checked(in_array($component, $muauth_recaptcha->components)); ?>/>
                        <?php echo isset($raw[$component]) ? esc_attr($raw[$component]) : esc_attr($component); ?>
                    </label><br/>
                <?php endforeach; ?>
            </p>

            <p><strong><?php _e('Disable reCaptcha:', MUAUTH_RECAPTCHA_DOMAIN); ?></strong></p>

            <p>
                <label>
                    <input type="checkbox" name="recaptcha_logged_in_disable" <?php checked($muauth_recaptcha->logged_in_disable); ?>/>
                    <?php _e('Disable reCaptcha for logged-in users', MUAUTH_RECAPTCHA_DOMAIN); ?>
                </label>
            </p>

            <p>
                <label><?php _e('Disable for user roles: (when logged-in)', MUAUTH_RECAPTCHA_DOMAIN); ?></label>
            </p>
            <p>
                <?php foreach ($roles as $id=>$role) : ?>
                    <label>
                        <input type="checkbox" name="recaptcha_disable_roles[]" value="<?php echo esc_attr($id); ?>" <?php checked(in_array($id, $muauth_recaptcha->disable_roles)); ?>/>
                        <?php echo esc_attr( $role['name'] ); ?>
                    </label><br/>
                <?php endforeach; ?>
            </p>

            <p>
                <label for="recaptcha_locale"><strong><?php _e('reCaptcha Language:', MUAUTH_RECAPTCHA_DOMAIN); ?></strong></label>
            </p>
            <p>
                <select name="recaptcha_locale" id="recaptcha_locale">
                    <?php foreach ( $muauth_recaptcha_locales as $locale => $display ) : ?>
                        <option value="<?php echo esc_attr($locale); ?>" <?php selected($locale,$muauth_recaptcha->locale); ?>><?php echo esc_attr($display); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

        </div>

        <?php wp_nonce_field( 'muauth_nonce', 'muauth_nonce' ); ?>
        <?php submit_button(); ?>

    </form>
    <?php
}

function muauth_recaptcha_update_settings() {
    $custom_settings = muauth_recaptcha_custom_settings();

    if ( isset($_POST['recaptcha_public']) && trim($_POST['recaptcha_public']) ) {
        $custom_settings['public'] = sanitize_text_field( $_POST['recaptcha_public'] );
    } else {
        $custom_settings['public'] = null;
    }

    if ( isset($_POST['recaptcha_secret']) && trim($_POST['recaptcha_secret']) ) {
        $custom_settings['secret'] = sanitize_text_field( $_POST['recaptcha_secret'] );
    } else {
        $custom_settings['secret'] = null;
    }

    if ( isset($_POST['recaptcha_components']) && is_array($_POST['recaptcha_components']) && $_POST['recaptcha_components'] ) {
        $custom_settings['components'] = array_map('sanitize_text_field', $_POST['recaptcha_components']);
        $custom_settings['components'] = array_filter($custom_settings['components'], 'trim');
    } else {
        $custom_settings['components'] = array();
    }

    $custom_settings['logged_in_disable'] = isset( $_POST['recaptcha_logged_in_disable'] );

    if ( isset($_POST['recaptcha_disable_roles']) && is_array($_POST['recaptcha_disable_roles']) && $_POST['recaptcha_disable_roles'] ) {
        $custom_settings['disable_roles'] = array_map('sanitize_text_field', $_POST['recaptcha_disable_roles']);
        $custom_settings['disable_roles'] = array_filter($custom_settings['disable_roles'], 'trim');
        $custom_settings['disable_roles'] = array_filter($custom_settings['disable_roles'], 'get_role');
    } else {
        $custom_settings['disable_roles'] = array();
    }

    global $muauth_recaptcha_locales;
    if ( isset($_POST['recaptcha_locale']) && isset($muauth_recaptcha_locales[$_POST['recaptcha_locale']]) ) {
        $custom_settings['locale'] = sanitize_text_field($_POST['recaptcha_locale']);
    } else {
        $custom_settings['locale'] = null;
    }

    update_site_option('muauth_recaptcha_settings', $custom_settings);

    // flush settings
    unset( $GLOBALS['muauth_recaptcha_custom_settings'] );
    muauth_recaptcha_parse_settings();
}

function muauth_recaptcha_admin_alerts() {
    if ( !class_exists('\MUAUTH\Includes\Admin\Admin') )
        return;

    $Admin = new \MUAUTH\Includes\Admin\Admin;

    global $muauth_recaptcha;

    if ( empty( $muauth_recaptcha->public ) || empty( $muauth_recaptcha->secret ) ) {
        $Admin::feedback(array(
            'success' => false,
            'message' => sprintf(__('Google reCaptcha public and secret keys missing, please update your <a href="%s">reCaptcha settings</a>.', MUAUTH_RECAPTCHA_DOMAIN), network_admin_url('settings.php?page=mu-auth&tab=recaptcha'))
        ));
    }
}

function muauth_recaptcha_admin_plugin_links($links) {
    return array_merge(array(
        'Settings' => sprintf(
            '<a href="%s">' . __('Settings', MUAUTH_RECAPTCHA_DOMAIN) . '</a>',
            network_admin_url('settings.php?page=mu-auth&tab=recaptcha')
        )
    ), $links);
}