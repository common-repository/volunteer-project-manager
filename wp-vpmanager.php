<?php
/**
 * Plugin Name: WordPress Volunteer Project Manager
 * Plugin URI: http://www.volunteerwild.org/
 * Description: Create and manage volunteer projects for your organization.
 * Author: <a href="http://Cyberbusking.org/">Meitar "maymay" Moscovitz</a> and Lionel Di Giacomo
 * Version: 0.3.1
 * Text Domain: wp-vpmanager
 * Domain Path: /languages
 */

class WP_VPManager {
    private $post_type = 'wp-vpm-project';

    public function __construct () {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('plugins_loaded', array($this, 'registerL10n'));
        add_action('init', array($this, 'registerDependencies'));
        add_action('init', array($this, 'createDataTypes'));
        add_action('init', array($this, 'registerStylesScripts'), 30);
        add_action('add_meta_boxes_' . $this->post_type, array($this, 'addMetaBoxes'));
        add_action('save_post', array($this, 'savePost'));

        add_filter('the_content', array($this, 'showProjectStatus'));
    }

    public function registerL10n () {
        load_plugin_textdomain('wp-vpmanager', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Checks to ensure we've got the required WordPress setup.
     * If we don't, this will cause the plugin to deactivate itself
     * and issue an error notice to the next administrator who logs in.
     */
    public function registerDependencies () {
        global $WP_Waitlist;
        if (!$WP_Waitlist && is_admin() && current_user_can('activate_plugins')) {
            add_action('admin_init', array($this, 'deactivate'));
        } else {
            $this->WP_Waitlist = $WP_Waitlist;
        }
    }

    public function deactivate () {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', array($this, 'showDeactivationNotice'));
    }

    public function showDeactivationNotice () {
?>
<div class="error">
    <p><?php print sprintf(esc_html__('The WordPress Volunteer Project Manager requires the %s plugin to be installed and activated.', 'wp-vpmanager'), '<a href="https://wordpress.org/plugins/wp-waitlist/">' . esc_html__('WP-Waitlist', 'wp-vpmanager') . '</a>');?></p>
</div>
<?php
        if (isset($_GET['activate'])) { unset($_GET['activate']); }
    }

    public function createDataTypes () {
        $this->registerCustomPostType();
        $this->registerTaxonomies();
    }

    private function registerCustomPostType () {
        $labels = array(
            'name'               => __('Projects', 'wp-vpmanager'),
            'singular_name'      => __('Project', 'wp-vpmanager'),
            'add_new'            => __('Add New Project', 'wp-vpmanager'),
            'add_new_item'       => __('Add New Project', 'wp-vpmanager'),
            'edit'               => __('Edit Project', 'wp-vpmanager'),
            'edit_item'          => __('Edit Project', 'wp-vpmanager'),
            'new_item'           => __('NewProject', 'wp-vpmanager'),
            'view'               => __('View Project', 'wp-vpmanager'),
            'view_item'          => __('View Project', 'wp-vpmanager'),
            'search'             => __('Search Projects', 'wp-vpmanager'),
            'not_found'          => __('No Projects found', 'wp-vpmanager'),
            'not_found_in_trash' => __('No Projects found in trash', 'wp-vpmanager')
        );
        $url_rewrites = array(
            'slug' => 'projects'
        );
        $args = array(
            'labels' => $labels,
            'description' => __('Volunteer Projects', 'wp-vpmanager'),
            'public' => true,
//            'menu_icon' => plugins_url(basename(__DIR__) . '/images/seedexchange_icon.png'),
            'has_archive' => true,
            'supports' => array(
                'title',
                'editor',
                'author',
                'comments'
            ),
            'rewrite' => $url_rewrites
        );
        register_post_type($this->post_type, $args);
    }

    private function registerTaxonomies () {
        $taxonomylabels = array(
            'name'              => _x( 'Scope', 'taxonomy general name' ),
            'singular_name'     => _x( 'Scope', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Scopes' ),
            'all_items'         => __( 'All Scopes' ),
            'edit_item'         => __( 'Edit Scope' ),
            'update_item'       => __( 'Update Scope' ),
            'add_new_item'      => __( 'Add New Scope' ),
            'new_item_name'     => __( 'New Scope' ),
            'menu_name'         => __( 'Scope' )
        );
        $url_rewrites = array(
            'slug' => 'scope'
        );
        $args = array(
            'labels' => $taxonomylabels,
            'rewrite' => $url_rewrites,
            'show_admin_column' => true
        );
        register_taxonomy($this->post_type . '_scope', $this->post_type, $args);       
        register_taxonomy_for_object_type($this->post_type . '_scope', $this->post_type);

        $taxonomylabels = array(
            'name'              => _x( 'Difficulty', 'taxonomy general name' ),
            'singular_name'     => _x( 'Difficulty', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Difficulties' ),
            'all_items'         => __( 'All Difficulties' ),
            'edit_item'         => __( 'Edit Difficulty' ),
            'update_item'       => __( 'Update Difficulty' ),
            'add_new_item'      => __( 'Add New Difficulty' ),
            'new_item_name'     => __( 'New Difficulty' ),
            'menu_name'         => __( 'Difficulty' )
        );
        $url_rewrites = array(
            'slug' => 'difficulty'
        );
        $args = array(
            'labels' => $taxonomylabels,
            'rewrite' => $url_rewrites,
            'show_admin_column' => true
        );
        register_taxonomy($this->post_type . '_difficulty', $this->post_type, $args);       
        register_taxonomy_for_object_type($this->post_type . '_difficulty', $this->post_type);
    }

    public function registerStylesScripts () {
        wp_register_style('jquery-ui-css', plugins_url('/css/smoothness-jquery-ui.css', __FILE__ ) );
        wp_enqueue_style('jquery-ui-css');	      

        wp_register_script('vpm-scripts', plugins_url( '/js/wp-vpm-scripts.js', __FILE__ ) );
        wp_enqueue_script('jquery-ui-datepicker'); 
        wp_enqueue_script('vpm-scripts');
    }

    public function addMetaBoxes () {
        add_meta_box(
            $this->post_type . '-projectdetailsbox',
            __('Project Details', 'wp-vpmmanager'),
            array($this, 'renderProjectDetailsBox'),
            $this->post_type
        );

        // Since we create the above "Project Details" meta box
        // ourselves, we can remove the default meta boxes WordPress
        // gives us with our custom-registered taxonomies.
        remove_meta_box('tagsdiv-' . $this->post_type . '_scope', $this->post_type, 'side');
        remove_meta_box('tagsdiv-' . $this->post_type . '_difficulty', $this->post_type, 'side');
    }

    public function renderProjectDetailsBox ($post) {
        wp_nonce_field('editing_' . $this->post_type, $this->post_type . 'nonce');
        $lists = $this->WP_Waitlist->getListsForPost($post->ID);
        $terms = get_terms($this->post_type . '_scope', array('hide_empty' => false));
        $scopes = array();
        foreach ($terms as $t) {
            $scopes[$t->name] = $t->name;
        }
        $terms = get_terms($this->post_type . '_difficulty', array('hide_empty' => false));
        $difficulties = array();
        foreach ($terms as $t) {
            $difficulties[$t->name] = $t->name;
        }
?>
        <table width="100%" align="center">
            <tr>
                <td>
                    <label><?php esc_html_e('Project list:', 'wp-vpmanager');?><br />
                        <?php if (false === $lists) : ?>
                        <p>
                            <a href="#wp-waitlist_lists-meta-box"><?php esc_html_e('First, create a project list.', 'wp-vpmanager');?></a>
                        </p>
                        <?php else:?>
                        <?php print $this->select_field(
                            array('name' => 'wp-vpm-project_list'),
                            array($lists[0] => $lists[0]),
                            array($lists[0])
                        );?>
                        <?php endif;?>
                    </label>
                </td>
                <td>
                    <label><?php esc_html_e('Date of Project:', 'wp-vpmanager');?><br />
                        <input class="custom_date"
                            name="<?php print esc_attr($this->post_type);?>_startdate"
                            value="<?php print esc_attr(get_post_meta($post->ID, $this->post_type . '_startdate', true));?>"
                            placeholder="enter a date"
                            />
                    </label>
                </td>
                <td>
                    <label><?php esc_html_e('Scope:', 'wp-vpmanager');?><br />
                        <?php print $this->select_field(
                            array('name' => $this->post_type . '_scope'),
                            array_merge(
                                array(
                                    '' => esc_html__('[Choose an option]', 'wp-vpmanager'),
                                ),
                                $scopes
                            ),
                            array(get_post_meta($post->ID, $this->post_type . '_scope', true))
                        );?>
                    </label>
                </td>
                <td>
                    <label><?php esc_html_e('Difficulty:', 'wp-vpmanager');?><br />
                        <?php print $this->select_field(
                            array('name' => $this->post_type . '_difficulty'),
                            array_merge(
                                array(
                                    '' => esc_html__('[Choose an option]', 'wp-vpmanager'),
                                ),
                                $difficulties
                            ),
                            array(get_post_meta($post->ID, $this->post_type . '_difficulty', true))
                        );?>
                    </label>
                </td>
            </tr>
        </table>
<?php
    }

    public function savePost ($post_id) {
        // Do nothing if the nonce is invalid.
        if (!wp_verify_nonce($_POST[$this->post_type . 'nonce'], 'editing_' . $this->post_type)) { return; }
        // Ensure we have the fields we're about to use.
        if (
            isset($_POST[$this->post_type . '_startdate'])
            &&
            isset($_POST[$this->post_type . '_difficulty'])
            &&
            isset($_POST[$this->post_type . '_scope'])
            &&
            isset($_POST[$this->post_type . '_list'])
        ) {
            update_post_meta($post_id, $this->post_type . '_list', sanitize_text_field($_POST[$this->post_type . '_list']));
            update_post_meta($post_id, $this->post_type . '_startdate', sanitize_text_field($_POST[$this->post_type . '_startdate']));
            // Set meta field AND taxonomy, for now, since we're not sure which we'll ultimately use.
            update_post_meta($post_id, $this->post_type . '_difficulty', sanitize_text_field($_POST[$this->post_type . '_difficulty']));
            wp_set_object_terms($post_id, sanitize_text_field($_POST[$this->post_type . '_difficulty']), $this->post_type . '_difficulty');
            update_post_meta($post_id, $this->post_type . '_scope', sanitize_text_field($_POST[$this->post_type . '_scope']));
            wp_set_object_terms($post_id, sanitize_text_field($_POST[$this->post_type . '_scope']), $this->post_type . '_scope');
        }
    }

    public function showProjectStatus ($content) {
        global $post;
        if ($this->post_type !== get_post_type($post->ID)) { return $content; }

        $this_list = get_post_meta($post->ID, $this->post_type . '_list', true);
        $registered_users = array_map(array($this, 'getUserDisplayNameById'), $this->WP_Waitlist->getListedUsers($post->ID, $this_list));
        $waitlisted_users = array_map(array($this, 'getUserDisplayNameById'), $this->WP_Waitlist->getWaitlistedUsers($post->ID, $this_list));

        $html = '<ul class="' . $this->post_type . '-status-box">';
        $html .= '<li>' . esc_html__('Start date:', 'wp-vpmanager') . ' ' . esc_html(get_post_meta($post->ID, $this->post_type . '_startdate', true)) . '</li>';
        $html .= '<li>' . esc_html__('Sope:', 'wp-vpmanager') . ' ' . esc_html(get_post_meta($post->ID, $this->post_type . '_scope', true)) . '</li>';
        $html .= '<li>' . esc_html__('Difficulty:', 'wp-vpmanager') . ' ' . esc_html(get_post_meta($post->ID, $this->post_type . '_difficulty', true)) . '</li>';
        $html .= '<li>' . esc_html__('Registered users:', 'wp-vpmanager') . '<ul>';
        foreach ($registered_users as $x) {
            $html .= '<li>' . esc_html($x) . '</li>';
        }
        $html .= '</ul></li>';
        $html .= '<li>' . esc_html__('Waitlisted users:', 'wp-vpmanager') . '<ul>';
        foreach ($waitlisted_users as $x) {
            $html .= '<li>' . esc_html($x) . '</li>';
        }
        $html .= '</ul></li>';
        $html .= '</ul>';

        return $content . $html;
    }

    private function getUserDisplayNameById ($user_id) {
        $user = get_user_by('id', $user_id);
        return $user->display_name;
    }

    private function getVolunteerSpots ($post_id) {
        $this_list = get_post_meta($post_id, $this->post_type . '_list', true);
        $list_properties = $this->WP_Waitlist->getListProperties($post_id, $this_list);
        return $list_properties['max'];
    }

    private function select_field ($attributes = array(), $options = array(), $selected = array()) {
        $html = '<select';
        foreach ($attributes as $k => $v) {
            $html .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        }
        $html .= '>';
        foreach ($options as $k => $v) {
            $html .= '<option value="' . esc_attr($k) . '"';
            $html .= (isset($selected) && in_array($k, $selected)) ? ' selected="selected"' : '';
            $html .= '>' . esc_html($v) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function activate () {
        $this->registerL10n();
        $this->createDataTypes();

        // Default taxonomy terms.
        wp_insert_term(_x('Morning', 'noun', 'wp-vpmanager'), $this->post_type . '_scope', array('description' => __('Projects happening only during the morning hours.', 'wp-vpmanager')));
        wp_insert_term(_x('Afternoon', 'noun', 'wp-vpmanager'), $this->post_type . '_scope', array('description' => __('Projects happening only during the afternoon hours.', 'wp-vpmanager')));
        wp_insert_term(_x('All Day', 'noun', 'wp-vpmanager'), $this->post_type . '_scope', array('description' => __('Projects happening for all or most of the daylight hours.', 'wp-vpmanager')));
        wp_insert_term(_x('Multi Day', 'noun', 'wp-vpmanager'), $this->post_type . '_scope', array('description' => __('Projects happening across multiple days, but not overnight.', 'wp-vpmanager')));
        wp_insert_term(_x('Multi Day - Camping', 'noun', 'wp-vpmanager'), $this->post_type . '_scope', array('description' => __('Projects happening continuously across multiple days and nights.', 'wp-vpmanager')));

        wp_insert_term(_x('Easy', 'noun', 'wp-vpmanager'), $this->post_type . '_difficulty', array('description' => __('Easy projects.', 'wp-vpmanager')));
        wp_insert_term(_x('Intermediate', 'noun', 'wp-vpmanager'), $this->post_type . '_difficulty', array('description' => __('Projects of intermediate difficulty.', 'wp-vpmanager')));
        wp_insert_term(_x('Difficult', 'noun', 'wp-vpmanager'), $this->post_type . '_difficulty', array('description' => __('Difficult projects.', 'wp-vpmanager')));
    }
}

$WP_VPManager = new WP_VPManager();
