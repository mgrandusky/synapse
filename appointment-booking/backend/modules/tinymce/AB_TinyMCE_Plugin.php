<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_TinyMCE_Plugin {

    private $vars = array();

    public function __construct() {
        global $PHP_SELF;
        if ( // check if we are in admin area and current page is adding/editing the post
            is_admin() && ( strpos( $PHP_SELF, 'post-new.php' ) !== false || strpos( $PHP_SELF, 'post.php' ) !== false )
        ) {
            wp_enqueue_script( 'backbone' );
            wp_enqueue_script( 'underscore' );
            add_action( 'admin_footer', array( &$this, 'renderPopup' ) );
            add_filter( 'media_buttons', array( &$this, 'addButton' ), 50 );

            $configuration = new AB_BookingConfiguration();

            $this->vars['categoriesJson'] = $configuration->getCategoriesJson();
            $this->vars['staffJson'] = $configuration->getStaffJson();
            $this->vars['servicesJson'] = $configuration->getServicesJson();
        }
    }

    public function addButton( $buttons ) {
        // don't show on dashboard (QuickPress)
        $current_screen = get_current_screen();
        if ( 'dashboard' == $current_screen->base )
            return;

        // don't display button for users who don't have access
        if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
            return;

        // do a version check for the new 3.5 UI
        $version = get_bloginfo('version');

        if ($version < 3.5) {
            // show button for v 3.4 and below
            echo '<a href="#TB_inline?width=640&inlineId=ab-tinymce-popup&height=650" id="add-Book-form" title="' . esc_attr__( 'Add Book booking form', 'ab' ) . '">' . __('Add Book booking form', 'ab' ) . '</a>';
            echo '<a href="#TB_inline?width=400&amp;inlineId=ab-tinymce-appointment-popup&amp;height=250" id="add-ap-appointment" title="' . esc_attr__( 'Add Book appointments list', 'ab' ) . '">' . __('Add Book appointments list', 'ab' ) . '</a>';
        } else {
            // display button matching new UI
            $img = '<span class="ab-media-icon"></span> ';
            echo '<a href="#TB_inline?width=640&inlineId=ab-tinymce-popup&height=650" id="add-Book-form" class="thickbox button ab-media-button" title="' . esc_attr__( 'Add Book booking form', 'ab' ) . '">' . $img . __( 'Add Book booking form', 'ab' ) . '</a>';
            echo '<a href="#TB_inline?width=400&amp;inlineId=ab-tinymce-appointment-popup&amp;height=250" id="add-ap-appointment" class="thickbox button ab-media-button" title="' . esc_attr__( 'Add Book appointments list', 'ab' ) . '">' . $img . __( 'Add Book appointments list', 'ab' ) . '</a>';
        }
    }

    public function renderPopup() {
        extract($this->vars);

        // render
        ob_start();
        ob_implicit_flush(0);

        try {
            include  'templates/popup.php';
            include 'templates/appointment_list.php';
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        echo ob_get_clean();
    }
}
