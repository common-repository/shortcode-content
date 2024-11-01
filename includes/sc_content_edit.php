<?php 

//Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
//Fetching shortcode data from DB
global $wpdb;
$sc_details = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sc_content WHERE `id` = $sc_content_id", ARRAY_A);

//Update data on submit
if (isset($_POST['sc_edit_submit']) && wp_verify_nonce($_POST["edit_name_nonce"], "edit_action_nonce")) {
    if (current_user_can('manage_options')) {
        //Fetch user form input
        global $wpdb;
        $shortcode = sanitize_text_field($_POST['shortcode']); //Shortocode user input
        $content = sanitize_text_field($_POST['content']); //content user input
        $errors = [];
        $msgs = [];

        //if there's no error, UPDATE DB
        $update = $wpdb->update(
            "{$wpdb->prefix}sc_content",
            array(
                'shortcode' => $shortcode,
                'content' => $content,
            ),
            array(
                'id' => $sc_content_id
            )
        );
        //Check if update successful,default false if unsuccessful
        if (false === $update) {
            $errors[] = "Update Unsuccessful";
        } else {
            $msgs[] = "Update successfull";
            $sc_details = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sc_content WHERE `id` = $sc_content_id", ARRAY_A);
        }
    }
}
?>
<div class="col-md-6">
    <br>
    <h3>Edit Shortcode</h3>
    <br>
    <div class="wrap">
        <?php if (!empty($errors)) {?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) {?>
            <p>
                <?php echo $error; ?>
            </p>
            <?php } ?>
        </div>
        <?php

    } ?>
        <?php if (!empty($msgs)) {
            ?>
        <div class="alert alert-success">
            <?php foreach ($msgs as $msg) {
                ?>
            <p>
                <?php echo $msg; ?>
            </p>
            <?php

        } ?>
        </div>
        <?php

    } ?>
        <div class="wrap">
            <form class="edit_sc_form" id="edit_sc_form" action="" method="post">
                <input type="hidden" name="sc_content_id" value="<?php echo $sc_details['id']; ?>">
                <div class="form-group">
                    <label for="shortcode">Shortcode</label><br>
                    <input class="form-control edit_sc_form_field" type="text" name="shortcode" value="<?php echo $sc_details['shortcode']; ?>" id="shortcode" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label><br>
                    <?php
                        // $content = $sc_details['content'];
                    // $settings = array(
                    //     'media_buttons' => false,
                    //     'textarea_rows' => 8
                    // );
                    // wp_editor($content, "content",$settings);
                    ?>
                    <textarea class="form-control edit_sc_form_field" name="content" value="" id="content" required><?php echo $sc_details['content']; ?></textarea>
                </div>
                <br>
                <?php wp_nonce_field("edit_action_nonce", "edit_name_nonce"); ?>
                <button name="sc_edit_submit" class="btn btn-info btn-lg">Submit</button>
            </form>
        </div>
    </div>
</div> 