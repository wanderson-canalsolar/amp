<?php
/*
Plugin Name: Suiteshare
Description: Plataforma de vendas e pagamento pelo WhatsApp
Version: 0.0.1
License: GPL2
*/

/*
Suiteshare

Options:
- Suiteshare (https://suiteshare.com/)
*/

add_action('wp_footer', 'suiteshare_get_script');

//get options
function suiteshare_get_options(){
    $options = array(
        'suiteshare_token' => get_option('suiteshare_token')
    );
    return $options;
}

function suiteshare_get_script(){
    if(!get_option('suiteshare_enable')){
         return false;
    }
    //get plugin options
    $options = suiteshare_get_options();

    //populate script;
    $script = "<script>\n";
    $script .= "(function (s,u,i,t,e) {\n";
    $script .= "var share = s.createElement('script');\n";
    $script .= "share.async = true;\n";
    $script .= "share.id = 'suiteshare';\n";
    $script .= "share.src = 'https://static.suiteshare.com/widgets.js';\n";
    $script .= "share.setAttribute('init',i);\n";
    $script .= "s.head.appendChild(share);\n";
    $script .= "})( document, 'script', '".$options['suiteshare_token']."');\n";
    $script .= "</script>\n";

    echo $script;
}

//Let's create the options menu
// create custom plugin settings menu
add_action('admin_menu', 'suiteshare_create_menu');

function suiteshare_create_menu() {

    //create new top-level menu
    add_options_page('Suiteshare - Settings', 'Suiteshare', 'administrator', __FILE__, 'suiteshare_settings_page', plugins_url('/images/icon.png', __FILE__));

    //call register settings function
    add_action( 'admin_init', 'suiteshare_register_mysettings' );
}


function suiteshare_register_mysettings() {
    //register our settings
    register_setting( 'suiteshare-settings-group', 'suiteshare_enable' );
    register_setting( 'suiteshare-settings-group', 'suiteshare_token' );
}

function suiteshare_settings_page() {  ?>
<div class="wrap">
    <img class="" src="/blog/wp-content/plugins/suiteshare/images/logo-dark.svg" width="200px" style="margin-top: 20px;">

    <form method="post" action="options.php">
        <?php settings_fields( 'suiteshare-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Ativar Plugin</th>
                <td>
                    <input type="checkbox" <?php if( get_option('suiteshare_enable' ) == 1){ echo 'checked'; }; ?> value="1" name="suiteshare_enable"/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Token</th>
                <td>
                    <input type="text" name="suiteshare_token" value="<?php echo get_option('suiteshare_token'); ?>" /> <br/> 
                    <small>
                        <a href="https://suiteshare.com/member/integrations/wordpress" target="_blank">Onde encontrar o meu token?</a>
                    </small>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>

        <p>
            D&#xFA;vidas? Envie um email para: <a href="mailto:help@suiteshare.com">help@suiteshare.com</a>
        </p>

    </form>
</div>
<?php } ?>
