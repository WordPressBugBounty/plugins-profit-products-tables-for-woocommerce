'use strict';

class WOOT_Attachments {
    constructor() {
        this.area = document.getElementById('woot-attachments-content');
        if (this.area) {
            this.post_id = parseInt(this.area.getAttribute('data-post-id'), 10);
            this.table = null;
            this.create_table();
        }

        this.associations_table = null;
    }

    create_table() {
        let table_html_id = woot_helper.create_id('tt_');
        let self = this;

        fetch(woot_helper_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: woot_helper.prepare_ajax_form_data({
                action: 'woot_get_table_attachments',
                post_id: this.post_id,
                table_id: table_html_id,
                settings_nonce: woot_helper_vars.settings_nonce
            })
        }).then((response) => response.json()).then((data) => {
            let div = document.createElement('div');
            div.className = 'data-table-23 woot-data-table woot-data-table-attachments';
            div.setAttribute('id', table_html_id);
            div.appendChild(document.createElement('table'));
            this.area.appendChild(div);

            //+++

            let _this = this;
            document.addEventListener('table23-do-after-draw', e => {
                e.stopPropagation();
                if (e.detail.table_html_id === table_html_id) {

                    jQuery('#woot-attachments-content tbody').sortable({
                        items: 'tr',
                        update: function (event, ui) {

                            let tr_pids = [];
                            _this.table.table.parentElement.querySelectorAll('tbody > tr').forEach(function (tr) {
                                tr_pids.push(parseInt(tr.getAttribute('data-pid'), 10));
                            });

                            if (tr_pids.length > 1) {
                                _this.table.save(_this.post_id, 'pos', tr_pids, 'woot_update_attachment'/*,JSON.stringify({post_id:_this.post_id})*/);
                            }

                        },
                        opacity: 0.8,
                        cursor: 'crosshair',
                        handle: '.woot-tr-drag-and-drope',
                        placeholder: 'woot-tr-highlight'
                    });

                }
            });

            //+++

            this.table = new WOOT_GeneratedTables(data, table_html_id);
            this.table.save_table_field_action = 'woot_update_attachment';

            //+++

            this.table.table.querySelectorAll("th[data-key='icon']").forEach(function (item) {
                item.addEventListener('click', function (e) {
                    e.preventDefault();

                    let table_html_id = woot_helper.create_id('t');
                    let popup = new Popup23();
                    popup.set_title(woot_helper_vars.lang.attachments_icons_default_associations);

                    fetch(ajaxurl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: woot_helper.prepare_ajax_form_data({
                            action: 'woot_get_attachments_icons_associations',
                            table_html_id: table_html_id,
                            settings_nonce: woot_helper_vars.settings_nonce
                        })
                    }).then(response => response.text()).then(html => {
                        
                        if (html.indexOf('"success":false') !== -1) {
                            woot_helper.message(woot_helper_vars.lang.error, 'error', 5000);
                            return;
                        }
                        
                        popup.set_content(html);
                        popup.set_title_info(woot_helper_vars.lang.attachments_icons_default_assoc_info);
                        self.associations_table = new WOOT_GeneratedTables(JSON.parse(popup.node.querySelector(`[data-table-id="${table_html_id}"]`).innerText), table_html_id);
                    }).catch((err) => {
                        woot_helper.message(err, 'error', 5000);
                    });


                    return false;
                });
            });

        }).catch((err) => {
            woot_helper.message(err, 'error', 5000);
        });

    }

    call_media_lib(prepend = false) {
        let _this = this;

        var image = wp.media({
            title: woot_helper_vars.lang.select_atts,
            multiple: true,
            library: {
                //type: ['image']
            }
        }).open()
                .on('select', function (e) {
                    let uploaded_images = image.state().get('selection');
                    uploaded_images = uploaded_images.toJSON();

                    if (Object.keys(uploaded_images).length > 0) {

                        woot_helper.message(woot_helper_vars.lang.adding, 'warning');

                        fetch(ajaxurl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: woot_helper.prepare_ajax_form_data({
                                action: 'woot_add_table_attachments',
                                post_id: _this.post_id,
                                value: JSON.stringify(uploaded_images),
                                settings_nonce: woot_helper_vars.settings_nonce
                            })
                        }).then(response => response.json()).then(data => {
                            _this.table.settings.table_data = data;
                            _this.table.request_data.orderby = 'pos';
                            _this.table.request_data.order = 'asc';
                            _this.table.draw_data(null);
                            woot_helper.message(woot_helper_vars.lang.added, 'notice');
                        }).catch((err) => {
                            woot_helper.message(err, 'error', 5000);
                        });

                    }
                });


        return false;
    }

    delete(attachment_id) {
        if (confirm(woot_helper_vars.lang.sure)) {
            woot_helper.message(woot_helper_vars.lang.deleting, 'warning');
            this.table.delete_row(attachment_id);
            fetch(ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: woot_helper.prepare_ajax_form_data({
                    action: 'woot_delete_table_attachments',
                    post_id: this.post_id,
                    attachment_id: attachment_id,
                    settings_nonce: woot_helper_vars.settings_nonce
                })
            }).then(response => response.json()).then(data => {
                this.table.settings.table_data = data;
                this.table.draw_data(null);
                woot_helper.message(woot_helper_vars.lang.deleted, 'notice');
            }).catch((err) => {
                woot_helper.message(err, 'error', 5000);
            });
        }
    }

    change_icon(attachment_id, button) {
        let self = this;

        var image = wp.media({
            title: woot_helper_vars.lang.select_att_icon,
            multiple: false,
            library: {
                type: ['image']
            }
        }).open()
                .on('select', function (e) {
                    var uploaded_image = image.state().get('selection').first();
                    uploaded_image = uploaded_image.toJSON();

                    if (typeof uploaded_image.url != 'undefined') {
                        if (typeof uploaded_image.sizes.thumbnail !== 'undefined') {
                            button.querySelector('img').setAttribute('src', uploaded_image.sizes.thumbnail.url);
                        } else {
                            button.querySelector('img').setAttribute('src', uploaded_image.url);
                        }

                        woot_helper.message(woot_helper_vars.lang.saving, 'warning');

                        fetch(ajaxurl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: woot_helper.prepare_ajax_form_data({
                                action: 'woot_update_attachment',
                                field: 'icon',
                                attachment_id: attachment_id,
                                post_id: self.post_id,
                                value: uploaded_image.id,
                                settings_nonce: woot_helper_vars.settings_nonce
                            })
                        }).then(response => response.json()).then(data => {
                            woot_helper.message(woot_helper_vars.lang.saved, 'notice');
                        }).catch((err) => {
                            woot_helper.message(err, 'error', 5000);
                        });


                    }
                });


        return false;
    }

    change_association(mime_type, button) {
        let self = this;

        var image = wp.media({
            title: mime_type + ': ' + woot_helper_vars.lang.set_association,
            multiple: false,
            library: {
                type: ['image']
            }
        }).open()
                .on('select', function (e) {
                    var uploaded_image = image.state().get('selection').first();
                    uploaded_image = uploaded_image.toJSON();

                    if (typeof uploaded_image.url != 'undefined') {
                        if (typeof uploaded_image.sizes.thumbnail !== 'undefined') {
                            button.querySelector('img').setAttribute('src', uploaded_image.sizes.thumbnail.url);
                        } else {
                            button.querySelector('img').setAttribute('src', uploaded_image.url);
                        }

                        woot_helper.message(woot_helper_vars.lang.saving, 'warning');

                        fetch(ajaxurl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: woot_helper.prepare_ajax_form_data({
                                action: 'woot_update_association',
                                mime_type: mime_type,
                                value: uploaded_image.id,
                                settings_nonce: woot_helper_vars.settings_nonce
                            })
                        }).then(response => response.json()).then(data => {
                            //as the table is jsoned and data is in memory - we should update it mannually 
                            //because user can order data and paginate it, and rows will be redrawn with data from memory
                            self.associations_table.settings.table_data.forEach(function (o, i) {
                                if (o.title === mime_type) {
                                    self.associations_table.settings.table_data[i]['icon'] = button.outerHTML;
                                    return;
                                }
                            });


                            woot_helper.message(woot_helper_vars.lang.saved, 'notice');
                        }).catch((err) => {
                            woot_helper.message(err, 'error', 5000);
                        });


                    }
                });


        return false;
    }
}



var woot_attachments = new WOOT_Attachments();




