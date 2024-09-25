<?php
/**
 * Page of the plugin options
 * 
 * @see https://products-tables.com/woot-documentation/
 * @version 1.0.0
 */
if (!defined('ABSPATH'))
    die('No direct access allowed');
?>

<div class="woot-admin-preloader">
    <div class="cssload-loader">
        <div class="cssload-inner cssload-one"></div>
        <div class="cssload-inner cssload-two"></div>
        <div class="cssload-inner cssload-three"></div>
    </div>
</div>

<?php //woot()->rate_alert->show_alert() ?>

<div class="wrap nosubsub woot-options-wrapper">



    <h3 class="woot-plugin-name"><?php printf(esc_html__('WOOT - WooCommerce Active Products Tables %s', 'profit-products-tables-for-woocommerce'), "<span>v." . WOOT_VERSION . "</span>") ?></h3>
    <i><?php printf(esc_html__('Actualized for WooCommerce v.%s', 'profit-products-tables-for-woocommerce'), WOOCOMMERCE_VERSION) ?></i><br />

    <div class="woot-tabs">

        <nav>
            <ul>

                <li class="tab-current">
                    <a href="#tabs-main-tables">
                        <span class="icon-cubes"></span>
                        <span><?php esc_html_e('Tables', 'profit-products-tables-for-woocommerce') ?></span>
                    </a>
                </li>


                <li>
                    <a href="#tabs-main-settings">
                        <span class="icon-cog-outline"></span>
                        <span><?php esc_html_e('Settings', 'profit-products-tables-for-woocommerce') ?></span>
                    </a>
                </li>

                <?php if (WOOT_Vocabulary::is_enabled()): ?>
                    <li>
                        <a href="#tabs-main-vocabulary">
                            <span class="icon-book"></span>
                            <span><?php esc_html_e('Vocabulary', 'profit-products-tables-for-woocommerce') ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="#tabs-main-help">
                        <span class="icon-info"></span>
                        <span><?php esc_html_e('Help', 'profit-products-tables-for-woocommerce') ?></span>
                    </a>
                </li>

            </ul>
        </nav>

        <div class="content-wrap">
            <section id="tabs-main-tables" class="content-current">

                <div style="float: left;">
                    <?php
                    echo WOOT_HELPER::draw_html_item('a', [
                        'href' => 'javascript: woot_main_table.create();void(0);',
                        'class' => 'woot-btn'
                            ], '<span class="icon-plus"></span>&nbsp;' . esc_html__('New table', 'profit-products-tables-for-woocommerce'));
                    ?>
                </div>
                <?php if (woot()->show_notes): ?>
                    <div style="float: right;">
                        <?php
                        echo WOOT_HELPER::draw_html_item('a', [
                            'href' => 'https://products-tables.com/downloads',
                            'target' => '_blank',
                            'class' => 'button woot-btn-upgrade',
                            //'style' => '',
                                ], '<span class="icon-upload"></span>&nbsp;' . esc_html__('Upgrade to Premium', 'profit-products-tables-for-woocommerce'));
                        ?>
                    </div>
                <?php endif; ?>

                <div class="clearfix"></div>

                <br />

                <?php
                echo wp_kses($main_table, [
                    'div' => [
                        'class' => true,
                        'id' => true,
                        'data-skin' => true,
                        'data-table-id' => true,
                        'style' => true
                    ],
                    'input' => [
                        'type' => true,
                        'data-key' => true,
                        'value' => true,
                        'minlength' => true,
                        'class' => true,
                        'placeholder' => true
                    ],
                    'table' => [
                        'class' => true,
                        'id' => true
                    ],
                    'thead' => [],
                    'tfoot' => [],
                    'tbody' => [
                        'style' => true,
                    ],
                    'th' => [
                        'data-key' => true,
                        'style' => true,
                        'class' => true
                    ],
                    'tr' => [
                        'data-pid' => true
                    ],
                    'td' => [
                        'class' => true,
                        'data-field-type' => true,
                        'data-pid' => true,
                        'data-key' => true,
                        'data-field' => true
                    ],
                ]);
                ?>

            </section>

            <section id="tabs-main-settings">
                <?php if (woot()->show_notes): ?>
                    <p class="notice notice-error">
                        <?php printf(esc_html__('In free version is possible to display only first %s columns!', 'profit-products-tables-for-woocommerce'), woot()->max_col) ?>
                    </p>
                <?php endif; ?>

                <?php
                echo wp_kses($settings_table, [
                    'div' => [
                        'class' => true,
                        'id' => true,
                        'data-skin' => true,
                        'data-table-id' => true,
                        'style' => true
                    ],
                    'input' => [
                        'type' => true,
                        'data-key' => true,
                        'value' => true,
                        'minlength' => true,
                        'class' => true,
                        'placeholder' => true
                    ],
                    'table' => [
                        'class' => true,
                        'id' => true
                    ],
                    'thead' => [],
                    'tfoot' => [],
                    'tbody' => [
                        'style' => true,
                    ],
                    'th' => [
                        'data-key' => true,
                        'style' => true,
                        'class' => true
                    ],
                    'tr' => [
                        'data-pid' => true
                    ],
                    'td' => [
                        'class' => true,
                        'data-field-type' => true,
                        'data-pid' => true,
                        'data-key' => true,
                        'data-field' => true
                    ],
                ]);
                ?>

                <a href='javascript: new Popup23({title: "<?php echo esc_html__('Info data: All possible columns keys for [woot] shortcode attributes', 'profit-products-tables-for-woocommerce') ?>", what: "possible_columns_keys"}); void(0);' class="woot-btn"><?php echo esc_html__('All possible columns keys', 'profit-products-tables-for-woocommerce') ?></a>&nbsp;
                <a href='javascript: new Popup23({title: "<?php echo esc_html__('Export WOOT Data', 'profit-products-tables-for-woocommerce') ?>", what: "export"}); void(0);' class="woot-btn"><?php echo esc_html__('Export WOOT Data', 'profit-products-tables-for-woocommerce') ?></a>&nbsp;
                <a href='javascript: new Popup23({title: "<?php echo esc_html__('Import WOOT Data', 'profit-products-tables-for-woocommerce') ?>", what: "import"}); void(0);' class="woot-btn"><?php echo esc_html__('Import WOOT Data', 'profit-products-tables-for-woocommerce') ?></a>

            </section>

            <?php if (WOOT_Vocabulary::is_enabled()): ?>
                <section id="tabs-main-vocabulary">

                    <?php if (woot()->show_notes): ?>
                        <p class="notice notice-error">
                            <?php printf(esc_html__('In free version is possible to display only first %s columns!', 'profit-products-tables-for-woocommerce'), woot()->max_col) ?>
                        </p>
                    <?php endif; ?>

                    <div class="woot-notice">
                        <?php
                        printf(esc_html__('This vocabulary is not for interface words, which you can translate for example by %s, but for the arbitrary words which you applied in the tables columns. Taxonomies terms also possible to translate here, to display them in the WOOT tables.', 'profit-products-tables-for-woocommerce'), WOOT_HELPER::draw_html_item('a', [
                                    'href' => 'https://wordpress.org/plugins/loco-translate/',
                                    'target' => '_blank'
                                        ], 'Loco Translate'))
                        ?>
                    </div>
                    <?php
                    echo WOOT_HELPER::draw_html_item('a', [
                        'href' => 'javascript: woot_vocabulary_table.create();void(0);',
                        'class' => 'woot-btn'
                            ], '<span class="icon-plus"></span>&nbsp;' . esc_html__('New key word', 'profit-products-tables-for-woocommerce'));
                    ?>

                    <br /><br />
                    <?php woot()->vocabulary->draw_table(); ?>
                    <div class="clearfix"></div>
                </section>
            <?php endif; ?>


            <section id="tabs-main-help">

                <div class="woof-p-4">

                    <div class="woof-card-holder woof__col-2">

                        <div class="woof-card-item">

                            <div class="woof-card woof-transition woof-text-center woof-rounded">
                                <div class="woof-card-body">
                                    <a href="https://pluginus.net/support/forum/woot-woocommerce-active-products-tables/" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/support.svg" class="woof-avatar woof-avatar-small woof-mb-3" alt=""></a>
                                    <h5 class="woof-h5"><a href="https://pluginus.net/support/forum/woot-woocommerce-active-products-tables/" class="woof-text-dark" target="_blank"><?php esc_html_e("Support", 'profit-products-tables-for-woocommerce') ?></a></h5>
                                    <p><?php esc_html_e("If you have questions about plugin functionality or found bug write us please", 'profit-products-tables-for-woocommerce') ?></p>
                                </div>
                            </div>

                        </div>

                        <div class="woof-card-item">

                            <div class="woof-card woof-transition woof-text-center woof-rounded">
                                <div class="woof-card-body">
                                    <h5 class="woof-h5"><a href="https://products-tables.com/faq" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/faq.svg" class="woof-avatar woof-avatar-small woof-mb-3" alt=""></a>
                                        <h5 class="woof-h5"><a href="https://products-tables.com/faq" class="woof-text-dark" target="_blank"><?php esc_html_e("FAQ", 'profit-products-tables-for-woocommerce') ?></a></h5>
                                        <p><?php esc_html_e("If you have questions check please already prepared answers", 'profit-products-tables-for-woocommerce') ?></p>
                                </div>
                            </div>

                        </div>

                        <div class="woof-card-item">

                            <div class="woof-card woof-transition woof-text-center woof-rounded">
                                <div class="woof-card-body">
                                    <a href="https://products-tables.com/codex" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/codex.svg" class="woof-avatar woof-avatar-small woof-mb-3" alt=""></a>
                                    <h5 class="woof-h5"><a href="https://products-tables.com/codex" class="woof-text-dark" target="_blank"><?php esc_html_e("Codex", 'profit-products-tables-for-woocommerce') ?></a></h5>
                                    <p><?php esc_html_e("For developers WOOT has power bunch of functionality", 'profit-products-tables-for-woocommerce') ?></p>
                                </div>
                            </div>

                        </div>

                        <div class="woof-card-item">

                            <div class="woof-card woof-transition woof-text-center woof-rounded">
                                <div class="woof-card-body">
                                    <a href="https://products-tables.com/video" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/video.svg" class="woof-avatar woof-avatar-small woof-mb-3" alt=""></a>
                                    <h5 class="woof-h5"><a href="https://products-tables.com/video" class="woof-text-dark" target="_blank"><?php esc_html_e("Video", 'profit-products-tables-for-woocommerce') ?></a></h5>
                                    <p><?php esc_html_e("If you are beginner watch the video please", 'profit-products-tables-for-woocommerce') ?></p>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="woof-row">

                        <div class="woof-col-lg-6 woof-mt-4">

                            <div class="woof-d-flex woof-p-4 woof-shadow woof-align-items-center woof-features woof-rounded">
                                <div class="woof-icons woof-text-primary woof-text-center">
                                    <span class="icon-info woof-d-block woof-rounded"></span>
                                </div>
                                <div class="woof-ms-4">
                                    <h5 class="woof-h5 woof-mb-1">
                                        <a class="woof-text-dark" href="https://products-tables.com/document/after-woot-installation-you-can-do-next/" target="_blank"><?php esc_html_e("You can do next ...", 'profit-products-tables-for-woocommerce') ?></a>
                                    </h5>
                                </div>
                            </div>

                        </div>

                        <div class="woof-col-lg-6 woof-mt-4">
                            <div class="woof-d-flex woof-p-4 woof-shadow woof-align-items-center woof-features woof-rounded">
                                <div class="woof-icons woof-text-primary woof-text-center">
                                    <span class="icon-heart woof-d-block woof-rounded"></span>
                                </div>
                                <div class="woof-ms-4">
                                    <h5 class="woof-h5 woof-mb-1">
                                        <a class="woof-text-dark" href="https://pluginus.net/support/forum/woot-woocommerce-active-products-tables/" target="_blank"><?php esc_html_e("WooCommerce Products Tables Support", 'profit-products-tables-for-woocommerce') ?></a>
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <div class="woof-col-lg-6 woof-mt-4">
                            <div class="woof-d-flex woof-align-items-center woof-p-4 woof-shadow woof-features woof-rounded">
                                <div class="woof-icons woof-text-primary woof-text-center">
                                    <span class="icon-language woof-d-block woof-rounded"></span>
                                </div>
                                <div class="woof-ms-4">
                                    <h5 class="woof-h5 woof-mb-1">
                                        <a class="woof-text-dark" href="https://products-tables.com/translations" target="_blank"><?php esc_html_e("Translations", 'profit-products-tables-for-woocommerce') ?></a>
                                    </h5>
                                </div>
                            </div>
                        </div>



                        <div class="woof-col-lg-6 woof-mt-4">
                            <div class="woof-d-flex woof-align-items-center woof-p-4 woof-shadow woof-features woof-rounded">
                                <div class="woof-icons woof-text-primary woof-text-center">
                                    <span class="icon-globe woof-d-block woof-rounded"></span>
                                </div>
                                <div class="woof-ms-4">
                                    <h5 class="woof-h5 woof-mb-1">
                                        <a class="woof-text-dark" href="https://demo.products-tables.com/" target="_blank"><?php esc_html_e("Demo site", 'profit-products-tables-for-woocommerce') ?></a>
                                    </h5>
                                </div>
                            </div>
                        </div>




                        <div class="woof-col-lg-6 woof-mt-4">
                            <div class="woof-d-flex woof-align-items-center woof-p-4 woof-shadow woof-features woof-rounded">
                                <div class="woof-icons woof-text-primary woof-text-center">
                                    <span class="icon-desktop woof-d-block woof-rounded"></span>
                                </div>
                                <div class="woof-ms-4">
                                    <h5 class="woof-h5 woof-mb-1">
                                        <a class="woof-text-dark" href="https://products-tables.com/document/skins/" target="_blank"><?php esc_html_e("Make skins", 'profit-products-tables-for-woocommerce') ?></a>
                                    </h5>
                                </div>
                            </div>
                        </div>



                        <div class="woof-col-lg-6 woof-mt-4">
                            <div class="woof-d-flex woof-align-items-center woof-p-4 woof-shadow woof-features woof-rounded">
                                <div class="woof-icons woof-text-primary woof-text-center">
                                    <span class="icon-book-1 woof-d-block woof-rounded"></span>
                                </div>
                                <div class="woof-ms-4">
                                    <h5 class="woof-h5 woof-mb-1">
                                        <a class="woof-text-dark" href="https://products-tables.com/woot-documentation/" target="_blank"><?php esc_html_e("Documentation", 'profit-products-tables-for-woocommerce') ?></a>
                                    </h5>
                                </div>
                            </div>
                        </div>



                    </div>

                    <div class="woof__alert woof__alert-info2" role="alert">
                        <h5 class="woof__alert-heading"><?php esc_html_e("Some questions", 'profit-products-tables-for-woocommerce') ?>:</h5>
                        <ul class="woof-list-unstyled woof-text-muted woof-border-top woof-mb-0 woof-pt-3">

                            <li><a href="https://products-tables.com/how-to-add-custom-column-to-the-tables" target="_blank" class="woof-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woof-fea woof-icon-sm woof-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("How to add custom column to the tables?", 'profit-products-tables-for-woocommerce') ?></a></li>
                            <li><a href="https://products-tables.com/how-to-add-meta-key-globally-to-all-shortcodes" target="_blank" class="woof-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woof-fea woof-icon-sm woof-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("How to add meta key globally to all shortcodes?", 'profit-products-tables-for-woocommerce') ?></a></li>
                            <li><a href="https://products-tables.com/how-to-add-search-input-where-a-product-table-as-drop-down-list" target="_blank" class="woof-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woof-fea woof-icon-sm woof-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("How to add search input where a product table as drop-down list?", 'profit-products-tables-for-woocommerce') ?></a></li>
                            <li><a href="https://products-tables.com/how-to-create-remote-page-with-the-products-table" target="_blank" class="woof-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woof-fea woof-icon-sm woof-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("How to create remote page with the products table?", 'profit-products-tables-for-woocommerce') ?></a></li>
                            <li><a href="https://products-tables.com/how-to-insert-shortcodes-into-the-single-product-page" target="_blank" class="woof-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woof-fea woof-icon-sm woof-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("How to insert shortcodes into the single product page?", 'profit-products-tables-for-woocommerce') ?></a></li>
                            <li><a href="https://products-tables.com/how-to-overload-archive-products-template-to-display-products-table-instead" target="_blank" class="woof-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woof-fea woof-icon-sm woof-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("How to overload archive products template to display products table instead?", 'profit-products-tables-for-woocommerce') ?></a></li>
                            <li><a href="https://products-tables.com/i-want-to-show-different-product-table-based-on-different-category-in-different-pages" target="_blank" class="woof-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woof-fea woof-icon-sm woof-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("I want to show different product table based on different category in different pages?", 'profit-products-tables-for-woocommerce') ?></a></li>


                        </ul>
                    </div>

                    <h3><?php echo esc_html__('Extensions for WOOT', 'profit-products-tables-for-woocommerce') ?>:</h3>

                    <?php
                    echo WOOT_HELPER::draw_html_item('a', [
                        'href' => 'https://products-tables.com/extension/favourites/',
                        'target' => '_blank',
                        'class' => 'woot-btn'
                            ], esc_html__('Favourites', 'profit-products-tables-for-woocommerce'))
                    ?>&nbsp;<?php
                    echo WOOT_HELPER::draw_html_item('a', [
                        'href' => 'https://products-tables.com/extension/compare/',
                        'target' => '_blank',
                        'class' => 'woot-btn'
                            ], esc_html__('Compare', 'profit-products-tables-for-woocommerce'))
                    ?>&nbsp;<?php
                    echo WOOT_HELPER::draw_html_item('a', [
                        'href' => 'https://products-tables.com/extension/attachments/',
                        'target' => '_blank',
                        'class' => 'woot-btn'
                            ], esc_html__('Attachments', 'profit-products-tables-for-woocommerce'))
                    ?>

                    <br>

                    <hr />

                    <a href="https://posts-table.com/" target="_blank" class="woot-btn"><?php echo esc_html__('Tables for WordPress posts', 'profit-products-tables-for-woocommerce') ?>:&nbsp;<?php echo esc_html__('TABLEON - WordPress Posts Table Filterable', 'profit-products-tables-for-woocommerce') ?></a><br />

                    <hr />

                    <div class="woof__section-title woof-mb-3">
                        <h5><?php esc_html_e("Recommended plugins for your site flexibility and features", 'profit-products-tables-for-woocommerce') ?>:</h5>
                    </div>

                    <ul class="woof__features-gallery woof__col-6">
                        <li><a target="_blank" href="https://pluginus.net/affiliate/woocommerce-products-filter"><img class="woof-rounded" width="300" src="<?php echo WOOT_ASSETS_LINK ?>/img/banners/woof-banner.png"></a></li>
                        <li><a target="_blank" href="https://pluginus.net/affiliate/woocommerce-bulk-editor"><img class="woof-rounded" width="300" src="<?php echo WOOT_ASSETS_LINK ?>img/banners/woobe-banner.png"></a></li>
                        <li><a target="_blank" href="https://pluginus.net/affiliate/woocommerce-currency-switcher"><img class="woof-rounded" width="300" src="<?php echo WOOT_ASSETS_LINK ?>img/banners/woocs-banner.png"></a></li>
                    </ul>

                </div>

            </section>


        </div>


    </div>


    <?php if (woot()->show_notes): ?>
        <br />
        <div>
            <a href="https://codecanyon.pluginus.net/item/woot-woocommerce-products-tables/27928580" title="PREMIUM version: WOOT - WooCommerce Active Products Tables" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/banners/woot-banner.png" width="250" alt="WOOT - WooCommerce Active Products Tables"></a>
            &nbsp;<a href="https://products-filter.com/" title="WOOF - WooCommerce Products Filter" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/banners/woof-banner.png" width="250" alt="WOOF - WooCommerce Products Filter"></a>
            &nbsp;<a href="https://currency-switcher.com/" title="WOOCS - WooCommerce Currency Switcher" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/banners/woocs-banner.png" width="250" alt="WOOCS - WooCommerce Currency Switcher"></a>
            &nbsp;<a href="https://bulk-editor.com/" title="WOOBE - WooCommerce Bulk Editor and Products Manager Professional" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/banners/woobe-banner.png" width="250" alt="WOOBE - WooCommerce Bulk Editor and Products Manager Professional"></a>
            &nbsp;<a href="https://posts-table.com/" title="TABLEON - WordPress Post Tables Filterable" target="_blank"><img src="<?php echo WOOT_ASSETS_LINK ?>img/banners/tableon-banner.png" width="250" alt="TABLEON - WordPress Post Tables Filterable"></a>
        </div>
    <?php endif; ?>



    <div id="woot-popup-columns-template" style="display: none;">

        <div class="woot-modal">
            <div class="woot-modal-inner">
                <div class="woot-modal-inner-header">
                    <h3 class="woot-modal-title">&nbsp;</h3>

                    <div class="woot-modal-title-info"><a href="https://products-tables.com/document/columns/" id="main-table-help-link" class="woot-btn" target="_blank"><?php echo esc_html__('Help', 'profit-products-tables-for-woocommerce') ?></a></div>

                    <a href="javascript: woot_columns_table.close_popup(); void(0)" class="woot-modal-close"></a>


                </div>
                <div class="woot-modal-inner-content">
                    <div class="woot-form-element-container">

                        <div class="woot-tabs woot-tabs-style-shape">

                            <nav>
                                <ul>

                                    <li class="tab-current">
                                        <a href="#tabs-columns">
                                            <span class="icon-cubes"></span>
                                            <span><?php esc_html_e('Columns', 'profit-products-tables-for-woocommerce') ?></span>
                                        </a>
                                    </li>


                                    <li>
                                        <a href="#tabs-meta">
                                            <span class="icon-flow-cross"></span>
                                            <span><?php esc_html_e('Meta', 'profit-products-tables-for-woocommerce') ?></span>
                                        </a>
                                    </li>



                                    <li>
                                        <a href="#tabs-filter">
                                            <span class="icon-filter"></span>
                                            <span><?php esc_html_e('Filter', 'profit-products-tables-for-woocommerce') ?></span>
                                        </a>
                                    </li>


                                    <li>
                                        <a href="#tabs-predefinition">
                                            <span class="icon-puzzle-outline"></span>
                                            <span><?php esc_html_e('Predefinition', 'profit-products-tables-for-woocommerce') ?></span>
                                        </a>
                                    </li>


                                    <li>
                                        <a href="#tabs-custom-css">
                                            <span class="icon-code"></span>
                                            <span><?php esc_html_e('Custom CSS', 'profit-products-tables-for-woocommerce') ?></span>
                                        </a>
                                    </li>


                                    <li>
                                        <a href="#tabs-options">
                                            <span class="icon-cog-outline"></span>
                                            <span><?php esc_html_e('Options', 'profit-products-tables-for-woocommerce') ?></span>
                                        </a>
                                    </li>


                                </ul>
                            </nav>

                            <div class="content-wrap">
                                <section id="tabs-columns" class="content-current">

                                    <div>
                                        <?php if (woot()->show_notes): ?>
                                            <p class="notice notice-error">
                                                <?php printf(esc_html__('In free version is possible to display only first %s columns!', 'profit-products-tables-for-woocommerce'), woot()->max_col) ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php
                                        echo WOOT_HELPER::draw_html_item('a', [
                                            'href' => 'javascript: woot_columns_table.create();void(0);',
                                            'class' => 'button woot-dash-btn'
                                                ], '<span class="dashicons-before dashicons-welcome-add-page"></span>' . esc_html__('Prepend column', 'profit-products-tables-for-woocommerce'));
                                        ?>
                                    </div>
                                    <br />

                                    <div class="woot-columns-table-zone"></div>

                                    <br />

                                    <div>
                                        <?php
                                        echo WOOT_HELPER::draw_html_item('a', [
                                            'href' => 'javascript: woot_columns_table.create(false);void(0);',
                                            'class' => 'button woot-dash-btn woot-dash-btn-rotate'
                                                ], '<span class="dashicons-before dashicons-welcome-add-page"></span>' . esc_html__('Append column', 'profit-products-tables-for-woocommerce'));
                                        ?>
                                    </div>

                                </section>

                                <section id="tabs-custom-css">

                                    <table style="width: 100%;">
                                        <tr>
                                            <td>
                                                <a href="javascript: woot_main_table.save_custom_css(); void(0)" class="woot-btn woot-btn-1"><i class="woot-icon">&#xe801;</i></a>
                                            </td>

                                            <td>
                                                <div class="woot-notice"><?php
                                                    printf(esc_html__('You can use custom CSS for small changes, but for quite big the table restyling its recommended to use %s. Use hotkey combination CTRL+S for CSS code saving!', 'profit-products-tables-for-woocommerce'), WOOT_HELPER::draw_html_item('a', [
                                                                'href' => 'https://products-tables.com/document/skins/',
                                                                'target' => '_blank',
                                                                    ], esc_html__('table skins', 'profit-products-tables-for-woocommerce')))
                                                    ?></div>
                                            </td>

                                        </tr>
                                    </table>

                                    <div class="woot-options-custom-css-zone"></div>                                    

                                </section>

                                <section id="tabs-options">
                                    <div class="woot-table-options-zone"></div>
                                </section>


                                <section id="tabs-meta">

                                    <div class="woot-notice">
                                        <?php
                                        printf(esc_html__('If to use sorting by meta keys - will be visible only products which has any value for the selected meta key. %s', 'profit-products-tables-for-woocommerce'), WOOT_HELPER::draw_html_item('a', [
                                                    'href' => 'https://products-tables.com/document/meta/',
                                                    'target' => '_blank'
                                                        ], esc_html__('Also read', 'profit-products-tables-for-woocommerce')))
                                        ?>
                                    </div>

                                    <?php
                                    echo WOOT_HELPER::draw_html_item('a', [
                                        'href' => 'javascript: woot_meta_table.create();void(0);',
                                        'class' => 'button'
                                            ], '<span class="icon-plus"></span>' . esc_html__('Add meta field', 'profit-products-tables-for-woocommerce'));
                                    ?>
                                    <br /><br />

                                    <div class="woot-meta-table-zone"></div>
                                </section>

                                <section id="tabs-filter">
                                    <p class="notice notice-success"><?php
                                        printf(esc_html__('Also for products filtration you can use %s filter!', 'profit-products-tables-for-woocommerce'), WOOT_HELPER::draw_html_item('a', [
                                                    'href' => 'https://demo.products-filter.com/demonstration-of-woot-and-woof-compatibility/',
                                                    'target' => '_blank'
                                                        ], 'WOOF'))
                                        ?></p>

                                    <?php if (woot()->show_notes): ?>
                                        <p class="notice notice-error">
                                            <?php printf(esc_html__('In free version is possible to display only first %s items!', 'profit-products-tables-for-woocommerce'), 2) ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="tabs-filter-container"></div>
                                </section>

                                <section id="tabs-predefinition">
                                    <div class="woot-notice"><?php
                                        printf(esc_html__('Here you can set rules about what products to display in the table. The filtration will work with the predefined products as with basic ones. %s.', 'profit-products-tables-for-woocommerce'), WOOT_HELPER::draw_html_item('a', [
                                                    'href' => 'https://products-tables.com/document/predefinition/',
                                                    'target' => '_blank'
                                                        ], esc_html__('Read more here', 'profit-products-tables-for-woocommerce')))
                                        ?></div>
                                    <div class="woot-predefinition-table-zone"></div>
                                </section>
                            </div>

                        </div>


                    </div>
                </div>
                <div class="woot-modal-inner-footer">
                    <a href="javascript: woot_columns_table.close_popup(); void(0)" class="button button-primary2 woot-modal-button-large-1"><?php esc_html_e('Close', 'profit-products-tables-for-woocommerce') ?></a>
                    <!-- <a href="javascript:void(0)" class="woot-modal-save button button-primary button-large-2"><?php esc_html_e('Apply', 'profit-products-tables-for-woocommerce') ?></a>-->
                </div>
            </div>
        </div>

        <div class="woot-modal-backdrop"></div>

    </div>

    <?php echo WOOT_HELPER::render_html('views/popup.php'); ?>


</div>

