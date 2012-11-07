<?php
/*
Plugin Name: Message:Creative Ad Squares
Plugin URI: http://messagecreative.com/plugins
Description: A simple adverts plugin.
Version: 1.0
Author: Matthew Laver at Message:Creative
*/

define( 'MCADS_URL', plugin_dir_url( __FILE__ ) );
define( 'MCADS_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'admin_menu', 'mcads_admin_add_page' );
function mcads_admin_add_page() {
    global $mcads_settings;
    $mcads_settings = add_options_page( 'mcAds Plugin Page', 'mcAds Plugin Menu', 'manage_options', 'plugin', 'mcads_plugin_options_page' );
}

add_action( 'admin_enqueue_scripts', 'mcads_load_scripts' );
function mcads_load_scripts( $hook ) {
    global $mcads_settings;

    if ( $hook != $mcads_settings )
        return;

    wp_enqueue_script( 'mcads-script', MCADS_URL . 'mcads-script.js', array( 'jquery' ) );
}

function mcads_plugin_options_page() { ?>

<?php
    
    if ( $_GET['settings-updated'] == true )
        mcads_check_if_too_many_images(); 
?>

    <div class="wrap">
        <h2 id="mcadsheader" data-pluginurl="<?php echo MCADS_URL; ?>">Message:Creative Ad Squares</h2>

        <div class="flash settings-error updated"></div>

        <form action='options.php' method='post'>
<?php
    settings_fields( 'mcads_plugin_options' );
    do_settings_sections( 'plugin' );
    $options = get_option( 'mcads_plugin_options' );
    $count = $options['rows'] * $options['columns'];
    $counter = 0;
?>

            <div id="mcads_squarescontainer" >

<?php  
                for ( $i=0; $i <= $count-1; $i++ ) :

                    // find the beginnings of columns
                    $colstart = '';
                    if ( $counter == $options['columns'] ) {
                        $colstart = 'columnstart';
                        $counter = 0;
                    }
                    $counter++;

                    $thumburl = '#';
                    if ( file_exists( MCADS_PATH . 'thumbs/' . $i . '-thumb.jpg' ) ) {
                        $thumburl = MCADS_URL . 'thumbs/' . $i . '-thumb.jpg';
                    }

                    $linkurl = 'http://';
                    if ( $options['images'][$i]['link'] ) {
                        $linkurl = $options['images'][$i]['link'];
                    }
?>
                    <div class="adsquare <?php echo $colstart; ?>">
                        <img width="220" height="220" src="<?php echo $thumburl; ?>">
                        
                        <p>
                            <input type="text" name="mcads_plugin_options[images][<?php echo $i ?>][link]" value="<?php echo $linkurl; ?>">
                        </p>
                    </div>
                <?php endfor; ?>
                <div style="clear:both;"></div>
            </div>

            <input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>">
        </form>


    </div>

    <?php
        $fixedWidth = 220;
        $fixedMargin = 20;
        $maxWidth = ( $fixedWidth + $fixedMargin ) * $options['columns'] ;
        $marginRight = ( $fixedMargin / $maxWidth ) * 100 ;
        $width = ( $fixedWidth / $maxWidth ) * 100 ;
    ?>

    <style type="text/css">
        #mcads_squarescontainer {
            margin-top: 20px;
            margin-bottom: 20px;

            max-width: <?= $maxWidth; ?>px;
        }
        .adsquare {

            margin-right: <?= $marginRight; ?>%;
            width: <?= $width; ?>%;

            margin-bottom: 10px;
            float: left;
            background-color: #e4e4e4;
            
        }
        .adsquare.columnstart {
            clear: left;
        }
        .adsquare img {
            width: 100%;
            max-width: 100%;
            height: auto;
        }
        .adsquare p {
            margin: 5px 0;
            background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAICAIAAACUI1PDAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAU1JREFUeNpi/P//PwMDw9WrV5mZmV++fs4ABva2jgwwcP7c+b///t5/ePff379//vyJjoqDSgB1Xrl05T8qmDN7NoSxZesmZPFfP37V19VB2IzXr1/X0NDYt3/XwQNHGRkZ//37Z+dgLSMh//DJ/X///79/+x5oz9mz54BKgVKWVmZAO9PT02fOnMny69dPoM17dh94+/ZtYmLity/fnJycgCIaWhoQRz178kxDQ3Pu3Lnm5uZy0opwX7C8fPWCgUHfwdFWRkr+xfMXN25d37l7J9B4oCV///6FWCUqKmrvaAtk3X981/afLVDb9+/fQf7s7+tD9szTp0+7OztfvXr16dOnlubmL1++wKVu3bxVW1MDlAL5ExK2vb29QBIYdqAw+/cvKSVFQECAlZUVyG1saICogWjOzcuDSEF1QgDIDWDAycnJgAowpQACDACZlPuqkyzC5QAAAABJRU5ErkJggg==);
            background-repeat: no-repeat;
            background-position: 3px center;
        }
        .adsquare input {
            margin-left:25px;
            width: 190px;
        }
        .wrap div.flash {
            border-width: 0;
        }
    </style>

<?php }

// Add the admin settings
add_action( 'admin_init', 'mcads_plugin_admin_init' );
function mcads_plugin_admin_init() {
    register_setting( 'mcads_plugin_options', 'mcads_plugin_options', 'mcads_plugin_options_validate' );
    add_settings_section( 'mcads_plugin_main', 'Main Settings', 'mcads_plugin_section_text', 'plugin' );

    //Rows
    add_settings_field( 'mcads_plugin_rows', 'Rows', 'mcads_plugin_setting_rows', 'plugin', 'mcads_plugin_main' );
    //Columns
    add_settings_field( 'mcads_plugin_columns', 'Columns', 'mcads_plugin_setting_columns', 'plugin', 'mcads_plugin_main' );
}

function mcads_plugin_section_text() {
    echo "<p>How many squares would you like?</p>";
}

function mcads_plugin_setting_rows() {
    $options = get_option( 'mcads_plugin_options' );
    echo "<input id='mcads_plugin_rows' name='mcads_plugin_options[rows]' size='2' type='text' value='{$options['rows']}' />";
}

function mcads_plugin_setting_columns() {
    $options = get_option( 'mcads_plugin_options' );
    echo "<input id='mcads_plugin_columns' name='mcads_plugin_options[columns]' size='2' type='text' value='{$options['columns']}' />";
}

add_action( 'wp_ajax_mcads_action', 'mcads_process_ajax' );

function mcads_plugin_options_validate( $input ) {
    $newinput = $input;

    foreach ( $newinput['images'] as $key => $value ) {

        //check url starts with http
        if ( $value['link'] && substr( $value['link'], 0, 7 ) != 'http://' ) {
            $newinput['images'][$key]['link'] = 'http://' . $value['link'];
        }

        //check url is not empty
        if ( $value['link'] == 'http://' ) {
            $newinput['images'][$key]['link'] = '';
        }
    }

    return $newinput;

}

function mcads_process_ajax() {

    //Check for jpegs
    if ( $_FILES['file']['type'] != 'image/jpeg' ) {
        die( 'not jpeg' );
    }

    //get the index of the square for filename
    $index = $_POST['index'];

    // echo "file being saved: ";
    // echo MCADS_PATH . 'thumbs/' . $index . '.jpg';
    $tmp = $_FILES['file']['tmp_name'];
    $dest = MCADS_PATH . 'thumbs/' . $index . '.jpg';

    if ( !move_uploaded_file( $tmp, $dest ) ) {
        die( 'Error uploading file - check destination is writeable.' );
    }

    image_resize( $dest, 220, 220, true, 'thumb' );
    unlink( $dest );

    //everyone has to die at the end of their ajax response
    die( 'All done' );
}

function mcads_check_if_too_many_images() {
    
    $options = get_option( 'mcads_plugin_options' );
    $count = $options['rows'] * $options['columns'];

    //get list of files
    $files = array();
    
    $handle = opendir( MCADS_PATH . '/thumbs' );
    while ( false !== ( $entry = readdir( $handle ) ) ) {
        if ( $entry != "." && $entry != ".." && $entry != '.DS_Store' ) {
            array_push( $files, $entry );
        }
    }
    closedir( $handle );

    //for every image above count number delete

    for ( $i = $count; $i < count( $files ); $i++ ) {
        $file_to_delete = MCADS_PATH . 'thumbs/' . $i . '-thumb.jpg';

        unlink( $file_to_delete );
    }

}


// **** TODO ****
// ==============
//
// drag drop to reorded
//
//
// add display code and shortcode?
//
