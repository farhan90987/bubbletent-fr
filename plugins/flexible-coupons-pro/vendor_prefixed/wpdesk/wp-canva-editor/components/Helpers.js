import {MenuTextWrapper, MenuTextWrapperItem} from "./StyledComponents";
import React from "react";
import {FontsData} from "./Context/EditorDefaultData";

export function lang(key) {
    if (typeof wpdesk_canva_editor_lang !== 'undefined' && wpdesk_canva_editor_lang.hasOwnProperty(key)) {
        return wpdesk_canva_editor_lang[key];
    }

    return key;
}

export function shortcode_list() {
    if (typeof wpdesk_canva_editor_shortcodes !== 'undefined') {
        return wpdesk_canva_editor_shortcodes;
    }

    return [
        {
            text: '[coupon_price]',
            top: 10,
            left: 10,
        },
        {
            text: '[coupon_code]',
            top: 120,
            left: 10,
        },
    ];
}

export function fonts_list() {
    if (typeof wpdesk_canva_editor_fonts !== 'undefined') {
        return wpdesk_canva_editor_fonts;
    }

    return FontsData;
}

export function texts_list() {
    let default_texts = [
        {
            type: 'text',
            name: lang('header1'),
            text: lang('header1'),
            tag: 'h1',
            fontSize: 36,
            fontWeight: 700,
            height: 50,
            width: 200,
            top: 50,
        },
        {
            type: 'text',
            name: lang('header2'),
            text: lang('header2'),
            tag: 'h2',
            fontSize: 30,
            fontWeight: 700,
            height: 50,
            width: 200,
            top: 100,
        },
        {
            type: 'text',
            name: lang('header3'),
            text: lang('header3'),
            tag: 'h3',
            fontSize: 24,
            fontWeight: 700,
            height: 50,
            width: 200,
            top: 150,
        },
        {
            type: 'text',
            name: lang('content'),
            tag: 'p',
            text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec commodo turpis sit amet sapien fringilla placerat. Curabitur condimentum euismod neque, id fermentum elit posuere ut.',
            height: 100,
            width: 400,
            top: 200,
        },
    ];

    if (typeof wpdesk_canva_editor_texts !== 'undefined' ) {
        return [ ...default_texts, ...wpdesk_canva_editor_texts ];
    }

    return default_texts;
}

export function qrcode_list() {
    return [
        {
            type: 'qr_code',
            text: '[qr_coupon_code]',
            name: 'Coupon Code',
			url: 'data:image/png;base64,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'
        }
    ];
}

export function updateAuxlineLeft(object) {
    let element = document.getElementById('auxLineLeft');
    element.style.top = '0';
    element.style.left = object.left + 'px';
    element.style.display = object.display;
}

export function updateAuxlineRight(object) {
    let element = document.getElementById('auxLineRight');
    element.style.top = '0';
    element.style.left = object.left + 'px';
    element.style.display = object.display;
}

export function updateAuxlineTop(object) {
    let element = document.getElementById('auxLineTop');
    element.style.left = '0';
    element.style.top = object.top + 'px';
    element.style.display = object.display;
}

export function updateAuxlineBottom(object) {
    let element = document.getElementById('auxLineBottom');
    element.style.left = '0';
    element.style.bottom = object.bottom + 'px';
    element.style.display = object.display;
}
