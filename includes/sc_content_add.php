<?php
//Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (current_user_can('manage_options')) {
    if (isset($_POST['sc_add_submit']) && wp_verify_nonce($_POST['add_name_nonce'], 'add_action_nonce')) {
        //Get user input and sanitize
        $shortcode = sanitize_text_field($_POST['shortcode']);//Shortocode user input
        $content = sanitize_text_field($_POST['content']);//Content user input
        $errors = [];
        $msgs = [];

        //'_e' used for translatable lines
        if (strlen($shortcode) < 3) {
            $errors[] = _e("Shortocde field must be atleast 3 chars") ;
        }
    

        if (empty($errors)) {
            //Db Insert if no errors
            global $wpdb;
            $wpdb->insert("{$wpdb->prefix}sc_content", array(
            'shortcode' => 'sc_' . $shortcode,
            'content' => $content,
        ));
            $sc_lastInsert_id = $wpdb->insert_id;//Fetching the last insert id

            //message the user
            if (!empty($sc_lastInsert_id)) {
                $msgs[] = _e("Shortcode inserted succesfully");
            } else {
                $errors[] = _e("DB insert failed");
            }
        }
    }

?>

<div class="col-md-6">
    <br>
    <h3>Add Shortcode</h3>
    <br>    
    <div class="wrap">
        <?php if(!empty($errors)){?>
        <div class="alert alert-danger">
            <?php foreach($errors as $error) { ?>
            <p><?php echo $error;?></p>
            <?php }?>
        </div>
        <?php } ?>
        <?php if(!empty($msgs)){?>
        <div class="alert alert-success">
            <?php foreach($msgs as $msg) { ?>
            <p><?php echo $msg;?></p>
            <?php }?>
        </div>
        <?php } ?>
        <div class="wrap">
            
            <form class="edit_sc_form" id="edit_sc_form" action="" method="post">
                <label for="shortcode">Shortcode</label>
                <div class="form-group input-group">
                    <div class="input-group-prepend">
                       <label for="shortcode" class="input-group-text">sc_</label> 
                    </div>

                    <input class="form-control edit_sc_form_field" type="text" name="shortcode" id="shortcode" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label><br>
                    <?php 
                        // $content = ''; 
                        // $settings = array(
                        //     'media_buttons' => false,
                        //     'textarea_rows' => 8,
                        //     'tinymce' => array(
                        //         'toolbar1' => 'bold, italic, underline,|,fontsizeselect',
                        //         'toolbar2' => false
                        //     ),
                        // );
                        // wp_editor($content, "content",$settings	); 
                    ?>
                    <textarea class="form-control edit_sc_form_field" name="content"  id="content" required></textarea>
                </div>
                <br>
                <?php wp_nonce_field("add_action_nonce","add_name_nonce");?>
                <button name="sc_add_submit" class="btn btn-info">Submit</button>
            </form> 
        </div>
    </div>
</div>
<?php }?>                     