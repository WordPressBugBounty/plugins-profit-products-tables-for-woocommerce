'use strict';

class WOOT_Favourites {
    constructor() {
        this.popup = null;
        this.table = null;
        document.addEventListener('woot-draw-favourites', e => {
            e.preventDefault();
            this.popup = e.detail.popup;
            this.fill(e.detail.post_type, e.detail.skin);
        });

        document.addEventListener('table23-html-drawn', e => {
            DataTable23.do_not_print.push('favourites');//favourites is cell data-key
        }, false);

        //***

        document.addEventListener('woot-do-after-draw', function (e) {

            e.detail.otable.table.querySelectorAll("[data-key='favourites']").forEach(function (item) {
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
                        new Popup23({title: '<span>' + woot_helper_vars.lang.my_favourites + '</span> ' + woot_helper_vars.lang.products, action: 'woot-draw-favourites', skin: skin, post_type: answer.table.request_data.post_type});

                    }, false);
                }
            });


            return true;
        });

    }

    click_action(title = '', help_title = '', help_link = '', skin = '') {
        if (title.length === 0) {
            title = '<span>' + woot_helper_vars.lang.my_favourites + '</span> ' + woot_helper_vars.lang.products;
        }

        new Popup23({title: title, action: 'woot-draw-favourites', post_type: 'product', help_title: help_title, help_link: help_link, skin: skin});
    }

    fill(post_type, skin) {

        if (!post_type) {
            post_type = 'product';
        }

        let table_html_id = woot_helper.create_id('tt_');
        fetch(woot_helper_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: woot_helper.prepare_ajax_form_data({
                action: 'woot_get_table_favourites',
                post_type: post_type,
                table_html_id: table_html_id,
                skin: skin,
                lang: woot_helper_vars.selected_lang
            })
        }).then((response) => response.json()).then((data) => {

            let div = document.createElement('div');
            div.className = 'data-table-23 woot-data-table woot-data-table-favourites';
            div.setAttribute('id', table_html_id);
            div.setAttribute('data-skin', skin);
            div.appendChild(document.createElement('table'));
            this.popup.set_content('');
            this.popup.append_content(div);

            this.table = new WOOT_GeneratedTables(data, table_html_id);

        }).catch((err) => {
            woot_helper.message(err, 'error', 5000);
        });

    }

    //https://codepen.io/OxyDesign/pen/avXVbo
    toggle(heart, post_id) {
        heart.classList.toggle('woot-favourites-active');

        if (heart.classList.contains('woot-favourites-active')) {
            heart.classList.add('woot-favourites-active2');//for animation
            this.add(post_id);
        } else {
            if (confirm(woot_helper_vars.lang.sure)) {
                heart.classList.remove('woot-favourites-active2');
                this.delete(post_id);
            } else {
                heart.classList.toggle('woot-favourites-active');
            }
        }

        if (heart.closest('div.woot-data-table')) {
            document.dispatchEvent(new CustomEvent('table23-reset-cache', {detail: {
                    table_html_id: heart.closest('div.woot-data-table').id
                }}));
        }

        return false;
    }

    add(post_id) {
        //woot_helper.message(woot_helper_vars.lang.adding, 'warning');
        fetch(woot_helper_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: woot_helper.prepare_ajax_form_data({
                action: 'woot_add_to_favourites',
                post_id: post_id
            })
        }).then((response) => response.text()).then((data) => {
            //woot_helper.message(woot_helper_vars.lang.added);
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
            this.table.delete_row(post_id);
        } else {
            if (document.querySelector('.woot_favourites')) {
                DataTable23.tables[document.querySelector('.woot_favourites').id].delete_row(post_id);
            }
        }

        if (document.querySelector('.woot-favourites-heart-wrapper-' + post_id) !== null) {
            document.querySelector('.woot-favourites-heart-wrapper-' + post_id).classList.remove('woot-favourites-active');
            document.querySelector('.woot-favourites-heart-wrapper-' + post_id).classList.remove('woot-favourites-active2');
        }

        woot_helper.message(woot_helper_vars.lang.deleting, 'warning');
        fetch(woot_helper_vars.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: woot_helper.prepare_ajax_form_data({
                action: 'woot_delete_from_favourites',
                post_id: post_id
            })
        }).then((response) => response.text()).then((data) => {
            woot_helper.message(woot_helper_vars.lang.deleted);
        }).catch((err) => {
            woot_helper.message(err, 'error', 5000);
        });



    }
}

var woot_favourites = new WOOT_Favourites();

