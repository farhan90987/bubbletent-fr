import React from 'react';
import {CloseMenuRight, ControlSelect, IconButton, PropModal} from '../StyledComponents';
import {
    BsArrowsFullscreen,
    FiHelpCircle,
    FaSortAmountUpAlt,
    FaSortAmountDownAlt,
    FaFont,
    FaAlignCenter,
    FaAlignJustify,
    FaAlignLeft,
    FaAlignRight,
    FaRegTrashAlt,
    FaRegClone,
    MdClose,
    FiBold,
    FiItalic
} from '../Context/Icons';
import {SketchPicker} from 'react-color';
import {fonts_list, lang} from '../Helpers';

class ObjectsProperties extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            colorModal: false,
            backgroundColorModal: false,
            fontSizes: [
                4,
                6,
                8,
                10,
                12,
                14,
                16,
                18,
                20,
                24,
                30,
                36,
                42,
                48,
                54,
                60,
                66,
                72,
            ],
            fontColor: {
                r: 255,
                g: 255,
                b: 255,
                a: 100,
            },
            fontFamily: 'Open Sans',
            fontStyle: 'normal',
            fontWeight: 'normal',
        }

        this.isWhiteColor = false;
    }

    valuetext = (value) => {
        return `${value}°C`;
    }

    changeFontSize = (e) => {
        e.preventDefault();
        this.props.updateObjectProperties('fontSize', e.target.value);
    }

    changeFontFamily = (e) => {
        e.preventDefault();
        this.props.updateObjectProperties('fontFamily', e.target.value);
    }

    textAlign = (e, align) => {
        e.preventDefault();
        this.props.updateObjectProperties('textAlign', align);
    }

    bold = (e, current) => {
        e.preventDefault();
        this.props.updateObjectProperties('fontWeight', current === 'bold' ? 'normal' : 'bold');
    }

    italic = (e, current) => {
        e.preventDefault();
        this.props.updateObjectProperties('fontStyle', current === 'italic' ? 'normal' : 'italic');
    }

    handleCloseModal = (index) => {
        this.setState({colorModal: false});
    }

    handleColorComplete = (color) => {
        this.setState({fontColor: color.rgb});
        this.props.updateObjectProperties('color', color.rgb);
    }

    toggleColorModal = (e) => {
        e.preventDefault();
        const currentState = this.state.colorModal;
        this.setState({colorModal: !currentState});
    }

    getColor = (color) => {
        if (!color.hasOwnProperty('b')) {
            return 'rgba(0,0,0,1)';
        }
        return `rgba(${color.r},${color.g},${color.b},${color.a})`;
    }

    render() {
        let fontSizes = this.state.fontSizes;
        let fontFamilies = fonts_list();
        const fontSizeItems = fontSizes.map((number, key) => <option value={number} key={key}>{number}</option>);
        const fontFamilyItems = fontFamilies.map((value, key) => <option value={value} key={key}>{value.replace(/\+/g, ' ')}</option>);

        if (this.props.selectedObject.type === 'image') {
            return (
                <div id="editor-object-properties" className="objectProperties">
                    <div className="left">

                    </div>
                    <div className="right">
                        <IconButton title={lang('fit_to_screen')} className="fit-to-screen" onClick={(event) => this.props.fireAction(event, 'object_fit_to_screen')}><BsArrowsFullscreen/></IconButton>
                        <IconButton title={lang('layer_up')} className="layer-up" onClick={(event) => this.props.fireAction(event, 'object_layer_up')}><FaSortAmountUpAlt/></IconButton>
                        <IconButton title={lang('layer_down')} className="layer-up" onClick={(event) => this.props.fireAction(event, 'object_layer_down')}><FaSortAmountDownAlt/></IconButton>
                        <IconButton title={lang('clone_element')} onClick={(event) => this.props.fireAction(event, 'object_clone')}><FaRegClone/></IconButton>
                        <IconButton title={lang('delete_element')} onClick={(event) => this.props.fireAction(event, 'object_delete')}><FaRegTrashAlt/></IconButton>
                    </div>
                </div>

            );
        }

        if (this.props.selectedObject.type === 'qr_code') {
            return (
                <div id="editor-object-properties" className="objectProperties">
                    <div className="left">

                    </div>
                    <div className="right">
                        <IconButton title={lang('layer_up')} className="layer-up" onClick={(event) => this.props.fireAction(event, 'object_layer_up')}><FaSortAmountUpAlt/></IconButton>
                        <IconButton title={lang('layer_down')} className="layer-up" onClick={(event) => this.props.fireAction(event, 'object_layer_down')}><FaSortAmountDownAlt/></IconButton>
                        <IconButton title={lang('clone_element')} onClick={(event) => this.props.fireAction(event, 'object_clone')}><FaRegClone/></IconButton>
                        <IconButton title={lang('delete_element')} onClick={(event) => this.props.fireAction(event, 'object_delete')}><FaRegTrashAlt/></IconButton>
                    </div>
                </div>

            );
        }

        if (this.props.selectedObject.e !== null && this.props.selectedObject.type === 'text') {
            const {fontFamily, textAlign, fontSize, color, fontStyle, fontWeight} = this.props.selectedObject.e;
            let buttonBgColor = '#FFF';
            let fontColor = this.getColor(color);
            if (fontColor === 'rgba(255,255,255,1)') {
                buttonBgColor = '#EEE';
            }
            return (
                <div id="editor-object-properties" className="objectProperties">
                    <div className="left">
                        <ControlSelect>
                            <select value={fontFamily} onChange={this.changeFontFamily} name="font-family" title={lang('change_font_family')}>
                                {fontFamilyItems}
                            </select>
                        </ControlSelect>
                        <ControlSelect>
                            <select value={fontSize} onChange={this.changeFontSize} name="font-size" title={lang('change_font_size')}>
                                {fontSizeItems}
                            </select>
                        </ControlSelect>
                        <IconButton title={lang('align_left')} className={textAlign === 'left' ? 'active' : ''} onClick={(event) => this.textAlign(event, 'left')}><FaAlignLeft/></IconButton>
                        <IconButton title={lang('align_center')} className={textAlign === 'center' ? 'active' : ''} onClick={(event) => this.textAlign(event, 'center')}><FaAlignCenter/></IconButton>
                        <IconButton title={lang('justify')} className={textAlign === 'justify' ? 'active' : ''} onClick={(event) => this.textAlign(event, 'justify')}><FaAlignJustify/></IconButton>
                        <IconButton title={lang('align_right')} className={textAlign === 'right' ? 'active' : ''} onClick={(event) => this.textAlign(event, 'right')}><FaAlignRight/></IconButton>
                        <span style={{position: 'relative', display: 'inline-block'}}>
                            <IconButton style={{backgroundColor: buttonBgColor}} title={lang('font_color')} onClick={this.toggleColorModal} color={fontColor}><FaFont/></IconButton>
                            <PropModal display={this.state.colorModal ? 'block' : 'none'}>
                                <CloseMenuRight bottom="25px" top="auto" right="27px" color="#888" onClick={this.handleCloseModal.bind(this, null)}><MdClose/></CloseMenuRight>
                                <SketchPicker key={1} width="200" presetColors={this.state.fontColors} color={color} onChangeComplete={this.handleColorComplete}/>
                            </PropModal>
                        </span>
                        <IconButton title={lang('font_bold')} className={fontWeight === 'bold' ? 'active' : ''} onClick={(event) => this.bold(event, fontWeight)}><FiBold/></IconButton>
                        <IconButton title={lang('font_italic')} className={fontStyle === 'italic' ? 'active' : ''} onClick={(event) => this.italic(event, fontStyle)}><FiItalic/></IconButton>
                    </div>
                    <div className="right">
                        <IconButton title={lang('layer_up')} className="layer-up" onClick={(event) => this.props.fireAction(event, 'object_layer_up')}><FaSortAmountUpAlt/></IconButton>
                        <IconButton title={lang('layer_down')} className="layer-up" onClick={(event) => this.props.fireAction(event, 'object_layer_down')}><FaSortAmountDownAlt/></IconButton>
                        <IconButton title={lang('clone_element')} onClick={(event) => this.props.fireAction(event, 'object_clone')}><FaRegClone/></IconButton>
                        <IconButton title={lang('delete_element')} onClick={(event) => this.props.fireAction(event, 'object_delete')}><FaRegTrashAlt/></IconButton>
                    </div>
                </div>
            );
        }
        let doc_url = 'https://wpdesk.net/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-add-new-right#New_coupon_template';
        if (wp_canva_admin.lang !== 'undefined' && wp_canva_admin.lang === 'pl_PL') {
            doc_url = 'https://www.wpdesk.pl/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-add-new-right#tworzenie-szablonu-kuponu';
        }
        return (
            <div id="editor-object-properties" className="objectProperties">
                <div className="left">

                </div>
                <div className="right">
                    <a href={doc_url} target="_blank"><IconButton type="button"><FiHelpCircle/></IconButton></a>
                </div>
            </div>
        )
    }
}

export default ObjectsProperties;
