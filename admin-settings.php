<div class="wrap">
    <h1>Elegant Contact Form Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('elegant_contact_form_settings'); ?>
        <?php do_settings_sections('elegant_contact_form_settings'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Admin Email</th>
                <td><input type="email" name="ecf_admin_email" value="<?php echo esc_attr(get_option('ecf_admin_email')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">reCAPTCHA Site Key</th>
                <td><input type="text" name="ecf_recaptcha_site_key" value="<?php echo esc_attr(get_option('ecf_recaptcha_site_key')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">reCAPTCHA Secret Key</th>
                <td><input type="text" name="ecf_recaptcha_secret_key" value="<?php echo esc_attr(get_option('ecf_recaptcha_secret_key')); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>