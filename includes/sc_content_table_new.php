<?php
//Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

//User meta insert for SC Author
$user = wp_get_current_user();//get current user
//check if the current user is with SC Author capability
if ($user->roles[0] === 'sc_author') {
    $sc_check_author = $wpdb->get_row("SELECT `id` FROM {$wpdb->prefix}sc_content_meta WHERE `id_user` = $user->ID");
    if(!isset($sc_check_author)){
        $wpdb->insert("{$wpdb->prefix}sc_content_meta", array(
            'id_user' => $user->ID
        ));
    }
}

//For pagiantion of list table
if(isset($_POST['paginate']) && wp_verify_nonce($_POST['showing_name_nonce'],'showing_action_nonce') === 1){
    $limit = sanitize_text_field($_POST['paginate']);//limits the no. of records shown
    $num_pg = sanitize_text_field($_POST['page_num']); // For pagiantion
   
    //Check to see if on the first page
    if (!empty($num_pg) && is_numeric($num_pg)){
        $pageno = (int)$num_pg;
    } else {
        $pageno = 1;
    }

    //Similar to limit .
    $no_of_records_per_page = sanitize_text_field($_POST['paginate']);
    $offset = ($pageno - 1) * $no_of_records_per_page;
  
    $total_rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sc_content", ARRAY_A);
    $total_num = $wpdb->num_rows;
    $total_pages = ceil($total_num/$no_of_records_per_page);  //Total no. of pages needed
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sc_content LIMIT $offset,$no_of_records_per_page", ARRAY_A);
}

//For search shortcodes
elseif(isset($_POST['search']) && wp_verify_nonce($_POST['search_name_nonce'], 'search_action_nonce') === 1){
 
    $search_text = sanitize_text_field($_POST['search_text']);
    if (!empty($search_text)) {
        //results of search on DB    
        $results = $wpdb->get_results
            ("SELECT * FROM {$wpdb->prefix}sc_content WHERE 
                    `shortcode`LIKE '%$search_text%'
                OR `content` LIKE '%$search_text%' 
            ",
            ARRAY_A);

        $search_num_results = $wpdb->num_rows;
    }
}

else{
    //Default view of the table with pagination
    $pageno = 1;
    $no_of_records_per_page =  5;
    $offset = ($pageno - 1) * $no_of_records_per_page;

    $total_rows = $wpdb->get_results("SELECT *FROM {$wpdb->prefix}sc_content", ARRAY_A);
    $total_num = $wpdb->num_rows;

    $total_pages = ceil($total_num / $no_of_records_per_page);
    
    $limit = '5';
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sc_content LIMIT $offset,$no_of_records_per_page", ARRAY_A);
}


//Instruction Hide controller for current user
$cur_user = wp_get_current_user();
if (isset($_POST['close_info']) && wp_verify_nonce($_POST['hide_name_nonce'], 'hide_action_nonce') === 1) {
    $update = $wpdb->update(
        "{$wpdb->prefix}sc_content_meta",
        array(
            'show_info' => 0,

        ),
        array(
            'id_user' => $cur_user->ID
        )
    );
    $meta_results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sc_content_meta WHERE `id_user` = $cur_user->ID", ARRAY_A);
}
//Instruction Show controller for current user
if(isset($_POST['show_info']) && wp_verify_nonce($_POST['show_name_nonce'], 'show_action_nonce') === 1){

    $update = $wpdb->update(
        "{$wpdb->prefix}sc_content_meta",
        array(
            'show_info' => 1,

        ),
        array(
            'id_user' => $cur_user->ID
        )
    );
    $meta_results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sc_content_meta WHERE `id_user` = $cur_user->ID", ARRAY_A);
}
$cur_user = wp_get_current_user();
$meta_results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sc_content_meta WHERE `id_user` = $cur_user->ID", ARRAY_A);

?>
<div class="wrap">
      <h1>Shortcode Content <a href="admin.php?page=add_new" class="page-title-action">Add new</a></h1>
      
        <br>
       <?php if ($meta_results['show_info'] === '1') { ?> 
            <!-- For SC author different instructions  -->
        <?php if ($user->roles[0] === 'sc_author') { ?>
    <div class="jumbotron" id="sc_content_info">
        <div class="col-md-12">
            <form action="" method="post">
                 <?php wp_nonce_field("hide_action_nonce", "hide_name_nonce"); ?>
                <button type="submit" class="close float-right cls-but" name = "close_info" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </form>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <h4>How to Use: </h4>
                        <p>
                            <strong>Shortcode Content</strong> is targeted for the developers with clients who are non-techies, such as bloggers ,authors and so on. You give this to the client ,and he/she just needs to edit the content and save in our great UI. That’s it! . You also get a separate user role ‘SC Author’ which you assign to the customer making him see only the SC Content panel in the backend. This makes sure that the customer doesn’t mess with any other settings you have worked hard to  achieve. 
                        </p>
                    </div>
                </div> 
              
               
            </div>
        </div>                   
    </div>
    <!-- For Administrator different instructions  -->
    <?php } else {?>
    <div class="jumbotron" id="sc_content_info">
        <div class="col-md-12">
            <form action="" method="post">
                <?php wp_nonce_field("hide_action_nonce", "hide_name_nonce"); ?>
                <button type="submit" class="close float-right cls-but" name = "close_info" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </form>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <h4>How to Use: </h4>
                        <p>
                            <strong>Shortcode Content</strong> is targeted for the developers with clients who are non-techies, such as bloggers ,authors and so on. You give this to the client ,and he/she just needs to edit the content and save in our great UI. That’s it! . You also get a separate user role ‘SC Author’ which you assign to the customer making him see only the SC Content panel in the backend. This makes sure that the customer doesn’t mess with any other settings you have worked hard to  achieve. 
                        </p>
                    </div>
                </div> 
              
               
            </div>
        </div>                   
    </div>
    <?php }?>
      <?php } else {?>
      <div class="d-flex justify-content-end">
        <div class="wrap">
            <form action="" method="post">
                <?php wp_nonce_field("show_action_nonce", "show_name_nonce"); ?>
                <button type="submit" class="btn btn-info" name="show_info">Show Instructions</button>
            </form>
        </div>
        </div>
    <?php } ?>

   <div class="d-flex  justify-content-between align-items-end">
        <div>
            <form class="form-inline" method="post" action="" >
            <?php wp_nonce_field("showing_action_nonce","showing_name_nonce");?>
                <div>
                    <?php if(!isset($search_num_results)){?>
            <div class="text-primary p-2 d-flex align-items-baseline">
                <div><strong>Showing: </strong></div>
                <div class="d-flex align-items-baseline"> 
                    <label for="pag5" class="badge badge-light text-secondary <?php if($limit === '5'){echo "text-primary";}?>">5</label>
                    <button hidden type="submit" id="pag5" name="paginate" value="5">5</button>
                    /
                    <label for="pag10" class="badge badge-light text-secondary <?php if($limit === '10'){echo "text-primary";}?>">10</label>
                    <button hidden type="submit" id="pag10" name="paginate" value="10">10</button>
                    /
                    <label for="pag20" class="badge badge-light text-secondary <?php if($limit === '20'){echo "text-primary";}?>">20</label>
                    <button hidden type="submit" id="pag20" name="paginate" value="20">20</button>
                    /
                    <label for="pag50" class="badge badge-light text-secondary <?php if($limit === '50'){echo "text-primary";}?>">50</label>
                    <button hidden type="submit" id="pag50" name="paginate" value="50">50</button>
                </div>
                <small class="text-secondary">items</small>
            </div>
                    <?php }else{?>
                        <div><?php echo $search_num_results; ?> <strong class="text-primary">&nbsp;items found</strong></div>
                    <?php }?>
        </div>
            </form>
        </div>
            <form class="form-inline" method="post" action="">
                <?php wp_nonce_field("search_action_nonce", "search_name_nonce"); ?>
                <div class="form-group">
                    <input type="text" class="form-control" id="sc_search" name="search_text" required>
                </div>
                <span class="form-group">
                    <!-- needed to match padding for floating labels -->
                    <button type="submit" class="action-link btn btn-secondary" name="search">Search ShortCode(s)</button>
                </span>
            </form>
    </div>
    <table class="table table-striped table-bordered table-hover rounded">
        <thead class="thead_color">
            <tr class="text-white">
                <th class="text-white" scope="col">ID</th>
                <th class="text-white" scope="col">Shortcode</th>
                <th class="text-white" scope="col">Content</th>                
                <!-- <th scope="col">Description</th> -->
                <th class="text-white" scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(!empty($results)){
                // var_dump($results);
                // die();
                foreach ($results as $val) {
            ?>
            <tr>
                <td><?php  echo $val['id']; ?></td>
                <th class="hover"><span id="copy"><?php echo "[" . $val['shortcode'] . "]"; ?></span>&nbsp;<button id="copy_button" type="submit" class="btn btn-primary copy_button bg-light" style="visibility:hidden;" onclick="copyToClipboard(this)">copy</button></th>
                <td><?php echo$val['content']; ?></td>
                
                <td>
                <!-- <td><button class="btn btn-danger btn-sm mx-1 btn-delete">Delete</button><button class="btn btn-primary btn-sm mx-1">Edit</button> -->
                <?php echo sprintf('<a href="?page=%s&action=%s&sc_id=%s" class="btn btn-primary">Edit</a>',$_GET['page'],'sc_edit',$val['id']);?>  
                <?php echo sprintf('<a href="?page=%s&action=%s&sc_id=%s" class="btn btn-danger">Delete</a>',$_GET['page'],'sc_delete',$val['id']);?>  
                 
                </td>
            </tr>
                <?php } ?> 
            <?php }else {?>
            <tr>
                <td colspan="5">No Items Found</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
             </div>
   <div class="d-flex justify-content-end">
        <div class="wrap">
            <nav aria-label="Page navigation example float-right">
                <ul class="pagination">
                    <?php
                    if (!isset($search_num_results)) {
                        if ($pageno > 1) {
                            echo '
                            <form class="form-inline" method="post" action="" >
                            ' . wp_nonce_field("showing_action_nonce", "showing_name_nonce") . '
                                <li class="page-item">
                                    <input type="hidden" name="page_num" value="'.($pageno-1).'">
                                    <button class="page-title-action adge badge-light text-secondary" type="submit" name="paginate" value="'.$limit.'" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                        <span class="sr-only">Previous</span>
                                    </button>
                                </li>
                            </form>';
                        } else {
                            echo '
                            <li class="page-item">
                                <button class="page-title-action adge badge-light text-secondary" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </button>
                            </li>';
                        }
                        for ($i = 1;$i <= $total_pages;$i++) {
                            echo '
                            <form class="form-inline" method="post" action="" >
                            '.wp_nonce_field("showing_action_nonce","showing_name_nonce").'
                                <li class="page-item">
                                    <input type="hidden" name="page_num" value="'.$i.'">
                                    <button class="page-title-action adge badge-light text-secondary" type="submit" name="paginate" value="'.$limit.'">
                                        ' .$i . '
                                    </button>
                                </li>
                            </form>';
                        }
                        if ($pageno < $total_pages) {
                            echo '
                            <form class="form-inline" method="post" action="" >
                            ' . wp_nonce_field("showing_action_nonce", "showing_name_nonce") . '
                                <li class="page-item">
                                    <input type="hidden" name="page_num" value="'.($pageno+1).'">
                                    <button class="page-title-action adge badge-light text-secondary" type="submit" name="paginate" value="'.$limit.'" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                        <span class="sr-only">Next</span>
                                    </button>
                                </li>
                            </form>';
                        } else {
                            echo '
                            <li class="page-item">
                                <button class="page-title-action adge badge-light text-secondary" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                    <span class="sr-only">Next</span>
                                </button>
                            </li>';
                        }
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
             