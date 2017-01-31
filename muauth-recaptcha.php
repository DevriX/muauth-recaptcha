<?php
/*
Plugin Name: reCaptcha for Multisite Auth
Plugin URI: https://samelh.com/
Description: Google's No Captcha reCaptcha implementation for Multisite Auth plugin
Author: Samuel Elh
Version: 0.1.2
Author URI: https://samelh.com
Text Domain: muauth-recaptcha
*/

// prevent direct access
defined('ABSPATH') || exit('Direct access not allowed.' . PHP_EOL);

class MUAUTH_RECAPTCHA
{
    /** Class instance **/
    protected static $instance = null;

    /** Constants **/
    public $constants;

    /** Get Class instance **/
    public static function instance()
    {
        return null == self::$instance ? new self : self::$instance;
    }

    public static function init()
    {
        return self::instance()
            ->setupConstants()
            ->setupGlobals();
    }

    /** define necessary constants **/
    public function setupConstants()
    {
        $this->constants = array(
            "MUAUTH_RECAPTCHA_FILE" => __FILE__,
            "MUAUTH_RECAPTCHA_DIR" => plugin_dir_path(__FILE__),
            "MUAUTH_RECAPTCHA_DOMAIN" => 'muauth-recaptcha',
            "MUAUTH_RECAPTCHA_BASE" => plugin_basename(__FILE__)
        );

        foreach ( $this->constants as $constant => $def ) {
            if ( !defined( $constant ) ) {
                define( $constant, $def );
            }
        }

        return $this;
    }

    public function setupGlobals()
    {
        global $muauth_recaptcha, $muauth_recaptcha_locales;

        $muauth_recaptcha = (object) array(
            'public' => null,
            'secret' => null,
            'components' => array(
                'login',
                'register',
                'lost-password',
                'activation'
            ),
            'logged_in_disable' => false,
            'disable_roles' => array(),
            'locale' => 'en'
        );

        $muauth_recaptcha_locales = array(
            "ar" => "Arabic",
            "af" => "Afrikaans",
            "am" => "Amharic",
            "hy" => "Armenian",
            "az" => "Azerbaijani",
            "eu" => "Basque",
            "bn" => "Bengali",
            "bg" => "Bulgarian",
            "ca" => "Catalan",
            "zh-HK" => "Chinese (Hong Kong)",
            "zh-CN" => "Chinese (Simplified)",
            "zh-TW" => "Chinese (Traditional)",
            "hr" => "Croatian",
            "cs" => "Czech",
            "da" => "Danish",
            "nl" => "Dutch",
            "en-GB" => "English (UK)",
            "en" => "English (US)",
            "et" => "Estonian",
            "fil" => "Filipino",
            "fi" => "Finnish",
            "fr" => "French",
            "fr-CA" => "French (Canadian)",
            "gl" => "Galician",
            "ka" => "Georgian",
            "de" => "German",
            "de-AT" => "German (Austria)",
            "de-CH" => "German (Switzerland)",
            "el" => "Greek",
            "gu" => "Gujarati",
            "iw" => "Hebrew",
            "hi" => "Hindi",
            "hu" => "Hungarain",
            "is" => "Icelandic",
            "id" => "Indonesian",
            "it" => "Italian",
            "ja" => "Japanese",
            "kn" => "Kannada",
            "ko" => "Korean",
            "lo" => "Laothian",
            "lv" => "Latvian",
            "lt" => "Lithuanian",
            "ms" => "Malay",
            "ml" => "Malayalam",
            "mr" => "Marathi",
            "mn" => "Mongolian",
            "no" => "Norwegian",
            "fa" => "Persian",
            "pl" => "Polish",
            "pt" => "Portuguese",
            "pt-BR" => "Portuguese (Brazil)",
            "pt-PT" => "Portuguese (Portugal)",
            "ro" => "Romanian",
            "ru" => "Russian",
            "sr" => "Serbian",
            "si" => "Sinhalese",
            "sk" => "Slovak",
            "sl" => "Slovenian",
            "es" => "Spanish",
            "es-419" => "Spanish (Latin America)",
            "sw" => "Swahili",
            "sv" => "Swedish",
            "ta" => "Tamil",
            "te" => "Telugu",
            "th" => "Thai",
            "tr" => "Turkish",
            "uk" => "Ukrainian",
            "ur" => "Urdu",
            "vi" => "Vietnamese",
            "zu" => "Zulu"
        );

        return $this;
    }
}

// init
MUAUTH_RECAPTCHA::init();

// core filters
include MUAUTH_RECAPTCHA_DIR . 'includes/filters.php';
// core functions
include MUAUTH_RECAPTCHA_DIR . 'includes/functions.php';

if ( is_admin() ) {
    // admin filters
    include MUAUTH_RECAPTCHA_DIR . 'includes/admin/filters.php';
    // admin functions
    include MUAUTH_RECAPTCHA_DIR . 'includes/admin/functions.php';
}