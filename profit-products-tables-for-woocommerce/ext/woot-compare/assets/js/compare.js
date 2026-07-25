'use strict';

class WOOT_Compare {
    constructor() {
        this.popup = null;
        this.table = null;

        document.addEventListener('woot-draw-compare', e => {
            e.preventDefault();
            this.popup = e.detail.popup;
            let ids = '';

            if (typeof e.detail.ids !== 'undefined') {
                ids = e.detail.ids;
            }

            this.fill(e.detail.post_type, ids, e.detail.skin);
        });

        document.addEventListener('table23-html-drawn', e => {
            DataTable23.do_not_print.push('compare');//compare is cell data-key
        }, false);

        //***

        document.addEventListener('woot-do-after-draw', function (e) {

            e.detail.otable.table.querySelectorAll("[data-key='compare']").forEach(function (item) {
                if (!item.hasAttribute('data-inited') && !['TD', 'DIV'].includes(item.tagName)) {
                    item.setAttribute('data-inited', 1);

                    item.addEventListener('click', function (e) {

                        e.preventDefault();
                        e.stopPropagation();

                        let answer = new Object();
                        document.dispatchEvent(new CustomEvent('table23-get', {detail: {
                                table_html_id: item.closest('div.woot-data-table').id,
                                answer: answer
                            }}));


                        let skin = item.closest('div.data-table-23').getAttribute('data-skin');
                        new Popup23({title: '<span>' + woot_helper_vars.lang.compare_products + '</span> ' + woot_helper_vars.lang.products, action: 'woot-draw-compare', skin: skin, post_type: answer.table.request_data.post_type});

                    }, false);
                }
            });

            //+++
            /*
             * in generated-tables.js we have scroll bar init
             if (e.detail.otable.container.classList.contains('woot_compare')) {
             if (!e.detail.otable.wrapper.classList.contains('horizontal-scrollbar23-attached')) {
             e.detail.otable.wrapper.classList.add('horizontal-scrollbar23-attached');
             new HorizontalScrollbar23(e.detail.otable.wrapper);
             }
             }
             */

            return true;
        });

        //***

        document.addEventListener('woot-compare-product', e => {

            if (Number(e.detail.value)) {
                this.add(Number(e.detail.post_id));
            } else {
                this.delete(Number(e.detail.post_id));
            }

            document.dispatchEvent(new CustomEvent('table23-reset-cache', {detail: {
                    table_html_id: e.detail.self.closest('div.woot-data-table').id
                }}));
        });

    }

    fill(post_type, ids = '', skin) {

        let table_id = woot_helper.create_id('cc_');

        fetch(woot_helper_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: woot_helper.prepare_ajax_form_data({
                action: 'woot_get_table_compare',
                post_type: post_type,
                table_id: table_id,
                ids: ids,
                skin: skin,
                lang: woot_helper_vars.selected_lang
            })
        }).then((response) => response.json()).then((data) => {

            let div = document.createElement('div');
            div.className = 'data-table-23 woot-data-table woot-data-table-compare data-table-23-separated';
            div.setAttribute('id', table_id);
            div.setAttribute('data-skin', skin);
            div.appendChild(document.createElement('table'));
            this.popup.set_content('');
            this.popup.append_content(div);

            this.table = new WOOT_GeneratedTables(data, table_id);
        }).catch((err) => {
            woot_helper.message(err, 'error', 5000);
        });

    }

    add(post_id) {
        woot_helper.message(woot_helper_vars.lang.adding, 'warning');
        fetch(woot_helper_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: woot_helper.prepare_ajax_form_data({
                action: 'woot_add_to_compare',
                post_id: post_id
            })
        }).then((response) => response.text()).then((data) => {
            woot_helper.message(woot_helper_vars.lang.added);
        }).catch((err) => {
            woot_helper.message(err, 'error', 5000);
        });
    }

    remove(post_id) {
        if (confirm(woot_helper_vars.lang.sure)) {
            this.delete(post_id);
        }
    }

    delete(post_id) {

        if (this.table !== null) {
            this.table.delete_column(`product_compare_${post_id}`);
        } else {
            if (document.querySelector('.woot_compare')) {
                DataTable23.tables[document.querySelector('.woot_compare').id].delete_column(`product_compare_${post_id}`);
            }
        }

        woot_helper.message(woot_helper_vars.lang.deleting, 'warning');

        fetch(woot_helper_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: woot_helper.prepare_ajax_form_data({
                action: 'woot_delete_from_compare',
                post_id: post_id
            })
        }).then((response) => response.text()).then((data) => {
            woot_helper.message(woot_helper_vars.lang.deleted);
        }).catch((err) => {
            woot_helper.message(err, 'error', 5000);
        });

    }

    call_popup(ids = '', title = '', help_title = '', help_link = '', skin = '') {
        if (title.length === 0) {
            title = '<span>' + woot_helper_vars.lang.compare_products + '</span> ' + woot_helper_vars.lang.products;
        }
        new Popup23({title: title, action: 'woot-draw-compare', post_type: 'product', ids: ids, help_title: help_title, help_link: help_link, skin: skin});
    }
}

var woot_compare = new WOOT_Compare();

