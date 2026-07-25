<?php

/*
  Plugin Name: WOOT - Attachments
  Plugin URI: https://products-tables.com/extension/attachments/
  Description: WOOT extension. Gives opportunity to attach files to the single product and display it in table.
  Requires at least: WP 4.9
  Tested up to: WP 6.0
  Author: realmag777
  Author URI: https://pluginus.net/
  Version: 1.0.3
  Requires PHP: 7.0
  Tags: attachments, products tables, woocommerce, products
  Text Domain: woot-attachments
  Domain Path: /languages
  WC requires at least: 3.6
  WC tested up to: 6.6
  Forum URI: https://pluginus.net/support/forum/woot-woocommerce-active-products-tables/
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WOOT_ATTACHMENTS_PATH', plugin_dir_path(__FILE__));
define('WOOT_ATTACHMENTS_LINK', plugin_dir_url(__FILE__));
define('WOOT_ATTACHMENTS_VERSION', '1.0.2');

final class WOOT_Attachments {

    public $storage_key = 'woot_attachments';
    public $action = 'woot_attachments';
    public $action_assoc = 'woot_attachments_icons_associations';
    public $setting_icon_key = 'woot_attachments_icon_width';
    private $db = null;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;

        add_action('woot_helper_lang', function ($words) {

            $words['attachments_icons_default_associations'] = WOOT_Vocabulary::get(esc_html__('Global default icons associations', 'woot-attachments'));
            $words['attachments_icons_default_assoc_info'] = WOOT_Vocabulary::get(esc_html__('Selected icons will be attached automatically while files appending.', 'woot-attachments'));
            $words['select_att_icon'] = WOOT_Vocabulary::get(esc_html__('Select attachment icon', 'woot-attachments'));
            $words['select_atts'] = WOOT_Vocabulary::get(esc_html__('Select attachment(s)', 'woot-attachments'));
            $words['set_association'] = WOOT_Vocabulary::get(esc_html__('Set association', 'woot-attachments'));

            return $words;
        });

        //add options for field type attachments
        add_action('woot_ext_column_fields_options', function ($option_key, $args) {
            $table_id = intval($args['table_id']);
            $field_id = intval($args['field_id']);
            $res = '';
            $col = woot()->columns->get($field_id, ['field_key', 'options']);

            if ($col) {

                switch ($option_key) {
                    case 'attachment_group':

                        $selected = woot()->columns->options->field_options->extract_from($col['options'], $option_key);
                        $res = [
                            'pid' => $table_id,
                            'title' => esc_html__('Group', 'woot-attachments'),
                            'value' => WOOT_HELPER::draw_select([
                                'class' => 'woot-columns-field-option',
                                'data-table-id' => $table_id,
                                'data-field-id' => $field_id,
                                'data-key' => $option_key
                                    ], $this->get_groups_set(), $selected),
                            'notes' => esc_html__('What group of attachments show. Not selected means all attachments from product metabox WOOT Attachments will be shown.', 'woot-attachments'),
                        ];

                        break;

                    case 'attachment_in_popup':

                        $is_checked = woot()->columns->options->field_options->extract_from($col['options'], $option_key);
                        $res = [
                            'pid' => $table_id,
                            'title' => esc_html__('In popup', 'woot-attachments'),
                            'value' => WOOT_HELPER::draw_switcher($option_key, $is_checked, $table_id . '_' . $field_id, 'woot_save_table_column_field_option'),
                            'notes' => esc_html__('Should the attachments be shown in the popup or directly in the table cell.', 'woot-attachments'),
                        ];

                        break;

                    case 'attachment_icon_width':

                        $res = [
                            'pid' => $table_id,
                            'title' => esc_html__('Icon width', 'woot-attachments'),
                            'value' => WOOT_HELPER::draw_html_item('input', [
                                'class' => 'woot-columns-field-option',
                                'type' => 'text',
                                'value' => woot()->columns->options->field_options->extract_from($col['options'], $option_key),
                                'data-table-id' => $table_id,
                                'data-field-id' => $field_id,
                                'data-key' => $option_key
                            ]),
                            'notes' => esc_html__('Attachment button icon width (px)', 'woot-attachments')
                        ];

                        break;
                }
            }


            return $res;
        }, 10, 2);

        add_action('woot_include_assets', function () {
            wp_enqueue_style('woot-attachments', WOOT_ATTACHMENTS_LINK . 'assets/css/attachments.css', [], WOOT_ATTACHMENTS_VERSION);
            wp_enqueue_script('woot-attachments', WOOT_ATTACHMENTS_LINK . 'assets/js/attachments.js', [], WOOT_ATTACHMENTS_VERSION, true);
        }, 10);

        //***
        //add cell attachments into woot woocommerce action woot_woocommerce_tables
        add_filter('ext_woot_woocommerce_tables', function ($profile, $table_id, $shortcode_args) {

            $profile['attachments'] = [
                'title' => esc_html__('Attachments', 'woot-attachments'),
                'options' => ['attachment_group', 'attachment_in_popup', 'attachment_icon_width', 'css-background', 'css-media-hide'],
                'action' => function ($product_id) use ($table_id, $shortcode_args) {
                    return $this->draw_cell($product_id, $table_id, $shortcode_args);
                }
            ];

            return $profile;
        }, 10, 3);

        //***
        //for media in popup
        add_filter('woot_show_front_attachments_in_popup', function ($what, $post_id) {
            $table_html_id = 'woot_attachments_in_popup';
            $res = WOOT_HELPER::render_html('views/table.php', array(
                        'table_html_id' => $table_html_id,
                        'hide_text_search' => true,
                        'classes' => 'woot-data-table-self-call'
            ));

            $skin = '';
            if (isset($what['skin'])) {
                $skin = $what['skin'];
            }

            $group = -1;
            if (isset($what['group'])) {
                $group = $what['group'];
            }

            return $res . $this->get_front($post_id, $what['table_id'], $table_html_id, $group, [], ['skin' => $skin]);
        }, 10, 2);

        //***

        $this->add_shortcode();

        //***

        add_action('woot_extend_settings', function ($rows) {

            $rows[] = [
                'id' => 0,
                'title' => esc_html__('Attachment icon width', 'woot-attachments'),
                'value' => [
                    'value' => WOOT_Settings::get($this->setting_icon_key),
                    'custom_field_key' => $this->setting_icon_key
                ],
                'notes' => esc_html__('Default attachment button icon width (px)', 'woot-attachments')
            ];

            return $rows;
        }, 10, 1);

        add_action('woot_extend_settings_default', function ($defaults) {
            $defaults[$this->setting_icon_key] = 30;
            return $defaults;
        }, 10, 1);

        //***

        add_action('init', array($this, 'init'), 999);
    }

    /**
     * Hook init
     *
     * @since 1.0.0
     * 
     * @return void
     */
    public function init() {

        if (!class_exists('WOOT_HELPER')) {
            return;
        }


        if (WOOT_HELPER::can_manage_data()) {
            $this->add_table_profile();
        }

        $this->add_table_in_popup_profile();
        add_action('wp_ajax_woot_get_table_attachments', array($this, 'get_table'));
        add_action('wp_ajax_woot_add_table_attachments', array($this, 'add'));
        add_action('wp_ajax_woot_delete_table_attachments', array($this, 'delete'));
        add_action('wp_ajax_woot_update_attachment', array($this, 'update'));
        add_action('wp_ajax_woot_get_attachments_icons_associations', array($this, 'get_attachments_icons_associations'));
        add_action('wp_ajax_woot_update_association', array($this, 'update_association'));

        global $pagenow;
        if (( $pagenow == 'post.php' ) || (get_post_type() == 'product')) {

            add_action('add_meta_boxes', array($this, 'add_meta_box'), 999);

            wp_enqueue_style('woot-growls', WOOT_ASSETS_LINK . 'css/growls.css', [], WOOT_ATTACHMENTS_VERSION);
            wp_enqueue_style('data-table-23', WOOT_ASSETS_LINK . 'js/data-table-23/data-table-23.css', [], WOOT_ATTACHMENTS_VERSION);

            wp_enqueue_script('data-table-23', WOOT_ASSETS_LINK . 'js/data-table-23/data-table-23.js', [], WOOT_ATTACHMENTS_VERSION, true);
            wp_enqueue_script('woot-helper', WOOT_ASSETS_LINK . 'js/helper.js', [], WOOT_ATTACHMENTS_VERSION, true);
            wp_enqueue_script('woot-generated-tables', WOOT_ASSETS_LINK . 'js/admin/generated-tables.js', ['data-table-23'], WOOT_ATTACHMENTS_VERSION, true);

            wp_enqueue_script('woot-alasql', WOOT_ASSETS_LINK . 'js/alasql.min.js', [], '0.5.5', true);

            wp_enqueue_style('woot-popup-23', WOOT_ASSETS_LINK . 'css/popup-23.css', [], WOOT_ATTACHMENTS_VERSION);
            wp_enqueue_script('popup-23', WOOT_ASSETS_LINK . 'js/popup-23.js', [], WOOT_ATTACHMENTS_VERSION);

            add_action('admin_print_footer_scripts', function () {
                echo WOOT_HELPER::render_html('views/popup.php');
            });

            wp_enqueue_style('woot-attachments', WOOT_ATTACHMENTS_LINK . 'assets/css/attachments-admin.css', [], WOOT_ATTACHMENTS_VERSION);
            wp_enqueue_script('woot-attachments', WOOT_ATTACHMENTS_LINK . 'assets/js/attachments-admin.js', [], WOOT_ATTACHMENTS_VERSION, true);

            woot()->wp_localize_script('woot-attachments');
        }
    }

    /**
     * Hook add_meta_box
     *
     * @since 1.0.0
     * 
     * @return void
     */
    public function add_meta_box() {
        global $post;
        if ($post->post_type === 'product' AND WOOT_HELPER::can_manage_data()) {
            add_meta_box('woot-attachments', esc_html__('WOOT Attachments', 'woot-attachments'), function () {
                echo WOOT_HELPER::render_html(WOOT_ATTACHMENTS_PATH . 'views/meta-box.php', [
                    'post_id' => intval($_GET['post'])
                        ], false);
            }, 'product');
        }
    }

    /**
     * Admin part table profile
     *
     * @since 1.0.0
     * 
     * @return void
     */
    public function add_table_profile() {
        add_action($this->action, function () {
            return apply_filters("{$this->action}_profile_extend", [
        0 => [
            'post_type_fixed' => FALSE
        ],
        'move' => [
            'title' => esc_html__('Move', 'woot-attachments'),
            'action' => function ($att, $post_id) {
                return WOOT_HELPER::draw_html_item('img', [
                    'src' => WOOT_ASSETS_LINK . 'img/move.png',
                    'width' => 20,
                    'alt' => esc_html__('drag and drope', 'woot-attachments'),
                    'class' => 'woot-tr-drag-and-drope'
                ]);
            }
        ],
        'title' => [
            'title' => esc_html__('Title', 'woot-attachments'),
            'editable' => 'textinput',
            //'order' => 'asc',
            'action' => function ($att, $post_id) {
                return WOOT_Vocabulary::get(get_post_field('post_title', $att['id']));
            }
        ],
        'type' => [
            'title' => esc_html__('Type', 'woot-attachments'),
            'action' => function ($att, $post_id) {
                return get_post_field('post_mime_type', $att['id']);
            }
        ],
        'group' => [
            'title' => esc_html__('Group', 'woot-attachments'),
            //'order' => 'asc',
            'editable' => 'select',
            'action' => function ($att, $post_id) {
                return WOOT_HELPER::draw_select([
                    'data-action' => 'woot_update_attachment',
                    'data-additional' => json_encode(['post_id' => $post_id])
                        ], $this->get_groups_set(), isset($att['group']) ? $att['group'] : 0);
            }
        ],
        'icon' => [
            'title' => esc_html__('Icon', 'woot-attachments'),
            'action' => function ($att, $post_id) {
                return WOOT_HELPER::draw_html_item('a', [
                    'href' => 'javascript: void(0);',
                    'onclick' => "javascript: woot_attachments.change_icon({$att['id']}, this)",
                    'title' => esc_html__('click to change icon', 'woot-attachments')
                        ], WOOT_HELPER::draw_html_item('img', [
                            'src' => apply_filters('woot_attachment_download_btn_img', $this->__get_att_icon_url($post_id, $att['id']), $att['id']),
                            'width' => WOOT_Settings::get($this->setting_icon_key),
                            'alt' => ''
                ]));
            }
        ],
        'actions' => [
            'title' => esc_html__('Actions', 'woot-attachments'),
            'action' => function ($att, $post_id) {
                return WOOT_HELPER::draw_html_item('a', [
                    'href' => admin_url("post.php?post={$att['id']}&action=edit"),
                    'target' => '_blank',
                    'class' => 'button',
                        ], '<span class="dashicons-before dashicons-welcome-view-site"></span>') . '&nbsp;' . WOOT_HELPER::draw_html_item('a', [
                    'href' => "javascript: woot_attachments.delete({$att['id']});void(0);",
                    'class' => 'button woot-dash-btn-single',
                    'title' => esc_html__('delete', 'woot-attachments')
                        ], '<span class="dashicons-before dashicons-no"></span>');
            }
        ]
            ]
            );
        });
    }

    /**
     * Front part table profile
     *
     * @since 1.0.0
     * 
     * @return void
     */
    public function add_table_in_popup_profile() {
        add_action("{$this->action}_in_popup", function () {

            return apply_filters("{$this->action}_in_popup_profile_extend", [
        0 => [
            'post_type_fixed' => FALSE
        ],
        'title' => [
            'title' => esc_html__('Title', 'woot-attachments'),
            'order' => 'asc',
            'action' => function ($att, $post_id) {
                return WOOT_Vocabulary::get(get_post_field('post_title', $att['id']));
            }
        ],
        'type' => [
            'title' => esc_html__('Type', 'woot-attachments'),
            'action' => function ($att, $post_id) {
                return get_post_field('post_mime_type', $att['id']);
            }
        ],
        'file' => [
            'title' => esc_html__('File', 'woot-attachments'),
            'action' => function ($att, $post_id) {
                $a = get_post($att['id']);
                $type = explode('/', $a->post_mime_type);
                $res = '';
                switch ($type[0]) {
                    case 'audio':
                        $res = $this->get_audio_representation($post_id, $a);
                        break;

                    default:
                        $res = $this->get_file_representation($post_id, $a);
                        break;
                }

                return $res;
            }
        ],
        'description' => [
            'title' => esc_html__('Description', 'woot-attachments'),
            'action' => function ($att, $post_id) {
                return WOOT_Vocabulary::get(get_post_field('post_content', $att['id']));
            }
        ]
            ]
            );
        });
    }

    /**
     * Get groups 
     *
     * @since 1.0.0
     * 
     * @return array
     */
    public function get_groups_set() {
        $arr = [
            -1 => esc_html__('not selected', 'woot-attachments')
        ];

        for ($i = 1; $i <= 10; $i++) {
            $arr[$i] = $i;
        }

        return $arr;
    }

    /**
     * Preparing data for tables
     *
     * @since 1.0.0
     * @param int $post_id
     * @param string $profile_hook
     * @param int $table_id table ID
     * @param int $allow_group
     * @param array $columns
     * @return array
     */
    private function prepare_table_data($post_id, $profile_hook, $table_id = 0, $allow_group = -1, $columns = []) {
        $table_data = [];
        $attachments = $this->get_data($post_id);
        $allow_group= intval($allow_group);

        //ordering by position
        if (!empty($attachments)) {
            usort($attachments, function ($a, $b) {
                return intval($a['pos'] > $b['pos']);
            });
        }

        //***

        $profile = apply_filters($profile_hook, NULL);

        if ($table_id > 0) {
            $options = woot()->columns->get_by_field_key($table_id, 'attachments')['options'];
            if (!empty($options)) {
                $options = json_decode($options, true);
            }
        }

        if (!empty($attachments)) {
            if ($allow_group === -1) {
                if (isset($options['attachment_group'])) {
                    $allow_group = intval($options['attachment_group']);
                }
            }

            //***

            foreach ($attachments as $att) {

                if (!isset($att['group'])) {
                    $att['group'] = -1;
                }

                if ($allow_group !== -1) {
                    if (intval($att['group']) !== $allow_group) {
                        continue;
                    }
                }

                //***

                if ($att['id'] AND is_array($profile)) {
                    $tmp = [];
                    $tmp['pid'] = $att['id'];
                    $tmp['pos'] = $att['pos'];
                    foreach (array_keys($profile) as $key) {
                        if ($key === 0) {
                            continue;
                        }

                        if ($columns) {
                            if (!in_array($key, $columns)) {
                                continue;
                            }
                        }

                        $tmp[$key] = $profile[$key]['action']($att, $post_id);
                    }

                    $table_data[] = $tmp;
                }
            }
        }

        return $table_data;
    }

    /**
     * Generate HTML table on admin side in meta box
     *
     * @since 1.0.0
     * 
     * @return output string
     */
    public function get_table() {
        $post_id = intval($_REQUEST['post_id']);
        $table_id = intval($_REQUEST['table_id']);

        $table_data = $this->prepare_table_data($post_id, $this->action, $table_id);

        die(woot()->draw_table_data([
                    'mode' => 'json',
                    'action' => $this->action,
                    'per_page_position' => 't',
                    'per_page_sel_position' => 't',
                    'per_page' => 10,
                    'table_data' => $table_data,
                    'use_flow_header' => 0
                        ], $table_id, FALSE));
    }

    /**
     * Generate HTML table on front side by shortcode
     *
     * @since 1.0.0
     * 
     * @return string
     */
    public function get_front($post_id, $table_id = 0, $table_html_id = 0, $group = -1, $columns = [], $args = []) {

        $skin = '';
        if (isset($args['skin'])) {
            $skin = $args['skin'];
        }

        return woot()->draw_table_data([
                    'mode' => 'json',
                    'action' => "{$this->action}_in_popup",
                    'flow_header' => 0,
                    'per_page_position' => isset($args['per_page_position']) ? $args['per_page_position'] : 'tb',
                    'per_page_sel_position' => isset($args['per_page_sel_position']) ? $args['per_page_sel_position'] : 'none',
                    'per_page' => isset($args['per_page']) ? intval($args['per_page']) : 10,
                    'table_data' => $this->prepare_table_data($post_id, "{$this->action}_in_popup", $table_id, $group, $columns),
                    'use_flow_header' => 0,
                    'skin' => $skin,
                    'style' => boolval($skin) ? woot()->skins->get_theme_css($skin, $table_html_id) : '',
                        ], $table_html_id);
    }

    /**
     * Get one product attachments data
     *
     * @since 1.0.0
     * @param int $post_id product ID
     * @return string
     */
    public function get_data($post_id) {
        $res = get_post_meta($post_id, $this->storage_key, true);

        if (!$res) {
            $res = [];
        }

        return $res;
    }

    /**
     * Update attachment data
     *
     * @since 1.0.0
     * @param int $post_id product ID
     * @param array $new_data
     * @return void
     */
    private function update_data($post_id, $new_data) {
        update_post_meta($post_id, $this->storage_key, $new_data);
    }

    /**
     * Create new row in table for attachments on admin side
     *
     * @since 1.0.0
     * 
     * @return output string
     */
    public function add() {
        $post_id = intval($_REQUEST['post_id']);
        $new_raw_data = json_decode(stripslashes($_REQUEST['value']), true);

        if (!empty($new_raw_data)) {
            $data = $this->get_data($post_id);

            foreach ($new_raw_data as $v) {
                if (!isset($data[$v['id']])) {
                    $data[$v['id']] = [
                        'id' => $v['id'],
                        'pos' => 1 - count($data),
                        'group' => -1,
                        'icon' => $this->get_association($v['mime'])
                    ];
                }
            }

            $this->update_data($post_id, $data);
        }

        die(json_encode($this->prepare_table_data($post_id, $this->action)));
    }

    /**
     * Delete row in table for attachments on admin side
     *
     * @since 1.0.0
     * 
     * @return output string
     */
    public function delete() {
        $post_id = intval($_REQUEST['post_id']);
        $attachment_id = intval($_REQUEST['attachment_id']);
        if ($post_id > 0 AND $attachment_id > 0) {
            $data = $this->get_data($post_id);
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    if (intval($value['id']) === $attachment_id) {
                        unset($data[$key]);
                        $this->update_data($post_id, $data);
                        break;
                    }
                }
            }
        }

        die(json_encode($this->prepare_table_data($post_id, $this->action)));
    }

    /**
     * Update row in table for attachments on admin side
     *
     * @since 1.0.0
     * 
     * @return void
     */
    public function update() {

        switch ($_REQUEST['field']) {
            case 'title':
                wp_update_post([
                    'ID' => intval($_REQUEST['post_id']),
                    'post_title' => strip_tags($_REQUEST['value'])
                ]);
                break;
            case 'group':
                $additional = json_decode(stripslashes($_REQUEST['additional']), true);
                $post_id = intval($additional['post_id']);
                $attachmen_id = intval($_REQUEST['post_id']); //here we have such trick because of standard general tables functionality
                $data = $this->get_data($post_id);
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if (intval($value['id']) === $attachmen_id) {
                            $data[$key]['group'] = intval($_REQUEST['value']);
                            break;
                        }
                    }
                }

                $this->update_data($post_id, $data);
                break;

            case 'pos':
                $post_id = intval($_REQUEST['post_id']);
                $data = $this->get_data($post_id);
                $ids = explode(',', $_REQUEST['value']);
                if (!empty($ids)) {
                    foreach ($ids as $pos => $att_id) {
                        $data[$att_id]['pos'] = $pos;
                    }
                }

                $this->update_data($post_id, $data);
                break;

            case 'icon':
                $post_id = intval($_REQUEST['post_id']);
                $attachment_id = intval($_REQUEST['attachment_id']);

                if ($post_id AND $attachment_id) {
                    $data = $this->get_data($post_id);
                    if (!empty($data)) {
                        foreach ($data as $key => $value) {
                            if (intval($value['id']) === $attachment_id) {
                                $data[$key]['icon'] = intval($_REQUEST['value']);
                                break;
                            }
                        }
                    }

                    $this->update_data($post_id, $data);
                }
                break;
        }

        die(json_encode([
            'value' => intval($_REQUEST['value'])
        ]));
    }

    /**
     * Draw attachment cell of the table
     *
     * @since 1.0.0
     * @param int $product_id product ID
     * @param int $table_id table ID
     * @return array
     */
    public function draw_cell($product_id, $table_id, $shortcode_args = []) {
        $attachments = $this->get_data($product_id);
        $res = '';
        $options = [];

        if ($table_id > 0) {
            $options = woot()->get_field_options($table_id, 'attachments');
        }

        if (!empty($attachments)) {

            $show_in_cell = true;
            if (isset($options['attachment_in_popup'])) {
                $show_in_cell = !boolval($options['attachment_in_popup']);
            }

            if (isset($shortcode_args['view']) AND $shortcode_args['view'] === 'button') {
                $show_in_cell = false;
            }

            //+++

            if ($show_in_cell) {
                $res = $this->draw_cell_content($attachments, $options, $product_id);
            } else {
                $product_title = addslashes(get_post_field('post_title', $product_id));
                $popup_title = sprintf(esc_html__('Attachments of product: %s', 'woot-attachments'), $product_title);
                $button_title = '<i class="woot-icon">&#xe809;</i>';

                if (isset($shortcode_args['button_title'])) {
                    $button_title = $shortcode_args['button_title'];
                    $button_title = str_replace('%s', $product_title, $button_title);
                }

                if (isset($shortcode_args['popup_title'])) {
                    $popup_title = $shortcode_args['popup_title'];
                    $popup_title = str_replace('%s', $product_title, $popup_title);
                }

                $help_title = '';
                if (isset($shortcode_args['help_title'])) {
                    $help_title = WOOT_Vocabulary::get($shortcode_args['help_title']);
                    $help_title = trim($help_title, '"');
                    $help_title = trim($help_title, "'");
                }

                $help_link = '';
                if (isset($shortcode_args['help_link'])) {
                    $help_link = $shortcode_args['help_link'];
                }

                $group = -1;
                if (isset($shortcode_args['group'])) {
                    $group = intval($shortcode_args['group']);
                }


                $args_json = json_encode([
                    'call_action' => 'woot_show_front_attachments_in_popup',
                    'table_id' => $table_id
                ]);

                if (!empty($this->draw_cell_content($attachments, $options, $product_id))) {

                    $skin = '';
                    if (isset($shortcode_args['skin'])) {
                        $skin = $shortcode_args['skin'];
                    } else {
                        if ($table_id > 0) {
                            $skin = woot()->skins->get($table_id);
                        }
                    }

                    //$help = esc_html__('Help', 'woot-attachments');
                    $res = WOOT_HELPER::draw_html_item('a', [
                                'href' => "javascript: woot_helper.call_popup(\"woot_show_front_attachments_in_popup\",{table_id:{$table_id}, skin: \"{$skin}\", group:{$group}, not_paste:1},\"{$product_id}\",\"{$popup_title}\", {help_title: \"{$help_title}\",help_link: \"{$help_link}\"}); void(0);",
                                'class' => 'woot-btn',
                                'title' => WOOT_Vocabulary::get($popup_title)
                                    ], $button_title);
                } else {
                    $res = '-';
                }
            }
        }

        return $res;
    }

    /**
     * Draw attachment cell content
     *
     * @since 1.0.0
     * @param array $attachments
     * @param array $options
     * @param int $product_id product ID
     * @return array
     */
    public function draw_cell_content($attachments, $options, $product_id) {
        $res = '';

        foreach ($attachments as $att) {

            if (isset($options['attachment_group'])) {
                $allow_group = intval($options['attachment_group']);

                if (!isset($att['group'])) {
                    $att['group'] = -1;
                }

                if ($allow_group !== -1) {
                    if (intval($att['group']) !== $allow_group) {
                        continue;
                    }
                }
            }


            //***

            $a = get_post($att['id']);
            $type = explode('/', $a->post_mime_type);

            switch ($type[0]) {
                case 'audio':
                    $res .= $this->get_audio_representation($product_id, $a);
                    break;

                default:
                    $res .= $this->get_file_representation($product_id, $a);
                    break;
            }
        }

        return $res;
    }

    /**
     * How to represent document file on the front
     *
     * @since 1.0.0
     * @param int $post_id
     * @param array $attachment
     * @return string
     */
    private function get_file_representation($post_id, $attachment) {

        $attachment_url = wp_get_attachment_url($attachment->ID);

        if (!$attachment_url) {
            $attachment_url = WOOT_ASSETS_LINK . 'img/download.png';
        }

        //+++

        if (isset($_REQUEST['table_id']) AND intval($_REQUEST['table_id']) > 0) {
            $width = woot()->columns->options->field_options->get_option(intval($_REQUEST['table_id']), 'attachments', 'attachment_icon_width');
        } else {
            $width = WOOT_Settings::get($this->setting_icon_key);
        }

        if (!$width) {
            $width = WOOT_Settings::get($this->setting_icon_key);
        }

        //+++

        return WOOT_HELPER::draw_html_item('a', [
                    'download' => '',
                    'href' => $attachment_url,
                    'title' => WOOT_Vocabulary::get($attachment->post_title) . ' ' . WOOT_Vocabulary::get(strip_tags($attachment->post_content)),
                    //'target' => '_blank',
                    'class' => 'woot-download-file'
                        ], WOOT_HELPER::draw_html_item('img', [
                            'src' => apply_filters('woot_attachment_download_btn_img', $this->__get_att_icon_url($post_id, $attachment->ID), $attachment->ID),
                            'width' => $width,
                            'alt' => strip_tags($attachment->post_excerpt)
                        ])) . ' ';
    }

    /**
     * How to represent audio file on the front
     *
     * @since 1.0.0
     * @param int $post_id
     * @param array $attachment
     * @return string
     */
    private function get_audio_representation($post_id, $attachment) {
        $attachment_url = wp_get_attachment_url($attachment->ID);

        if ($attachment_url) {
            return '<div>' . WOOT_HELPER::draw_html_item('audio', [
                        'controls' => '',
                        'preload' => 'none',
                        'onended' => 'woot_audio_end(this)',
                        'onplay' => 'woot_audio_play(this)'
                            ], WOOT_HELPER::draw_html_item('source', [
                                'src' => $attachment_url,
                                'type' => $attachment->post_mime_type
                            ])) . $this->get_file_representation($post_id, $attachment) . '</div>';
        }

        return '';
    }

    /**
     * Add to system shortcode
     * 
     * @since 1.0.0  
     * 
     * @return void
     */
    private function add_shortcode() {
        add_shortcode('woot_attachments', function ($args) {
            if (!function_exists('woot')) {
                return;
            }

            woot()->include_assets();

            $args = (array) $args;
            $product_id = 0;
            $group = -1;

            if (isset($args['group'])) {
                $group = intval($args['group']);
            }


            if (isset($args['id'])) {
                $product_id = intval($args['id']);
                unset($args['id']);
            } else {
                global $product;
                if (is_object($product)) {
                    $product_id = $product->get_id();
                }
            }

            //***

            $display_as = 'table';
            if (isset($args['display_as']) AND!empty($args['display_as'])) {
                $display_as = $args['display_as'];
            }

            if (!in_array($display_as, ['table', 'icons'])) {
                $display_as = 'table';
            }

            //***

            if ($product_id > 0) {

                $res = '';

                if ($display_as === 'icons') {
                    $files = $this->prepare_table_data($product_id, "{$this->action}_in_popup", 0, $group);
                    if (!empty($files)) {
                        foreach ($files as $f) {
                            $res .= $this->get_file_representation($product_id, get_post($f['pid']));
                        }
                    }
                }

                //+++

                if ($display_as === 'table') {

                    $table_html_id = uniqid('t');
                    $columns = [];

                    if (isset($args['columns']) AND!empty($args['columns'])) {
                        $_REQUEST['attachments_shortcode_columns'] = $columns = explode(',', $args['columns']);
                        add_filter("{$this->action}_in_popup", [$this, '__front_columns_managing']);
                    }

                    $res = WOOT_HELPER::render_html('views/table.php', array(
                                'table_html_id' => $table_html_id,
                                'table_id' => 0,
                                'hide_text_search' => true,
                                'action' => "{$this->action}_in_popup",
                                'style' => isset($args['skin']) ? woot()->skins->get_theme_css($args['skin'], $table_html_id) : '',
                            )) . $this->get_front($product_id, 0, $table_html_id, $group, $columns, $args);

                    if (isset($args['columns'])) {
                        unset($_REQUEST['attachments_shortcode_columns']);
                        remove_filter("{$this->action}_in_popup", [$this, '__front_columns_managing']);
                    }
                }

                return $res;
            }
        });

        //+++

        add_shortcode('woot_attachments_btn', function ($args) {
            if (!function_exists('woot')) {
                return;
            }

            $args = (array) $args;
            woot()->include_assets();

            $product_id = 0;
            $group = -1;

            if (isset($args['group'])) {
                $group = intval($args['group']);
            }


            if (isset($args['id'])) {
                $product_id = intval($args['id']);
                unset($args['id']);
            } else {
                global $product;
                if (is_object($product)) {
                    $product_id = $product->get_id();
                }
            }


            if ($product_id > 0) {
                $args['view'] = 'button';
                return $this->draw_cell($product_id, 0, $args);
            }
        });
    }

    /**
     * Columns on fron for filter {$this->action}_in_popup
     * 
     * @since 1.0.0  
     * 
     * @return array
     */
    public function __front_columns_managing($profile) {
        $sorted_profile = [];

        if (is_array($profile)) {
            foreach (array_keys($profile) as $key) {
                if (!in_array($key, $_REQUEST['attachments_shortcode_columns'])) {
                    unset($profile[$key]);
                }
            }


            foreach ($_REQUEST['attachments_shortcode_columns'] as $c) {
                $sorted_profile[$c] = $profile[$c];
            }
        }

        return $sorted_profile;
    }

    /**
     * Get icon associated with doc file type
     * 
     * @since 1.0.0  
     * @param int $post_id post ID
     * @param int $attachment_id attachment ID
     * @return string
     */
    private function __get_att_icon_url($post_id, $attachment_id) {
        $data = $this->get_data($post_id);
        $icon_url = WOOT_ASSETS_LINK . 'img/download.png';

        if (!empty($data)) {
            foreach ($data as $att) {
                if (intval($att['id']) === $attachment_id) {
                    if (isset($att['icon']) AND intval($att['icon']) > 0) {
                        $icon_url = wp_get_attachment_url($att['icon']);
                        if (!$icon_url) {
                            $icon_url = WOOT_ASSETS_LINK . 'img/download.png';
                        }
                    }
                    break;
                }
            }
        }

        return $icon_url;
    }

    /**
     * Get table of icons from meta box (column Icons) by ajax
     * 
     * @since 1.0.0  
     * 
     * @return output string
     */
    public function get_attachments_icons_associations() {

        add_action($this->action_assoc, function () {
            return [
        0 => [
            'post_type_fixed' => FALSE
        ],
        'title' => [
            'title' => esc_html__('Mime type', 'woot-attachments'),
            'order' => 'asc'
        ],
        'icon' => [
            'title' => esc_html__('Default icon', 'woot-attachments')
            ]];
        });

        //***

        $table_data = [];
        $association = $this->get_association();

        foreach (get_allowed_mime_types() as $mime_type) {
            $icon_url = WOOT_ASSETS_LINK . 'img/download.png';

            if (isset($association[$mime_type]) AND $association[$mime_type] > 0) {
                $icon_url = wp_get_attachment_url($association[$mime_type]);
                if (!$icon_url) {
                    $icon_url = WOOT_ASSETS_LINK . 'img/download.png';
                }
            }

            $table_data[] = [
                'title' => $mime_type,
                'icon' => WOOT_HELPER::draw_html_item('a', [
                    'href' => 'javascript: void(0);',
                    'onclick' => "javascript: woot_attachments.change_association(\"{$mime_type}\",this)",
                    'title' => esc_html__('click to change', 'woot-attachments')
                        ], WOOT_HELPER::draw_html_item('img', [
                            'src' => $icon_url,
                            'width' => 25
                        ]))
            ];
        }

        //***

        die(WOOT_HELPER::render_html('views/table.php', array(
                    'table_html_id' => $_REQUEST['table_html_id'],
                    'hide_text_search' => false,
                    'text_search_min_symbols' => 1,
                    'placeholder' => '',
                    'search_data_key' => 'title',
                    'action' => $this->action_assoc
                )) . woot()->draw_table_data([
                    'mode' => 'json',
                    'action' => $this->action_assoc,
                    'orderby' => 'title',
                    'order' => 'asc',
                    'per_page_position' => 'b',
                    'per_page_sel_position' => 'none',
                    'per_page' => 5,
                    'table_data' => $table_data,
                    'cells_width' => '80%,20%'
                        ], $_REQUEST['table_html_id']));
    }

    /**
     * Get file type association
     * 
     * @since 1.0.0  
     * @param string $mime_type_attachment
     * @return output string
     */
    public function get_association($mime_type_attachment = '') {
        static $res = [];

        if (empty($res)) {
            $res = get_option('woot_mime_types_association', []);

            if (!is_array($res)) {
                $res = json_decode($res, true);
            }

            if (!empty($mime_type_attachment)) {
                if (isset($res[$mime_type_attachment]) AND $res[$mime_type_attachment] > 0) {
                    return $res[$mime_type_attachment];
                } else {
                    return 0;
                }
            }
        }

        return $res;
    }

    /**
     * Update file type association
     * 
     * @since 1.0.0  
     * 
     * @return void
     */
    public function update_association() {
        $association = $this->get_association();
        $association[$_REQUEST['mime_type']] = intval($_REQUEST['value']);
        update_option('woot_mime_types_association', $association);
    }

}

$GLOBALS['WOOT_Attachments'] = new WOOT_Attachments();

function woot_get_product_attachments($product_id) {
    $res = get_post_meta($product_id, 'woot_attachments', true);

    if (!$res) {
        $res = [];
    }

    return $res;
}



