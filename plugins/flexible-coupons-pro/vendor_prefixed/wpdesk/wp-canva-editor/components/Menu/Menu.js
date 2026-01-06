import React from 'react';
import MenuGeneral from "./General";
import MenuImage from "./Image";
import MenuText from "./Text";
import MenuShortcodes from "./Shortcodes";
import MenuQRCode from "./QRCode";
import MenuSending from "./Sending";
import {MdClose, General, Images, Text, Shortcodes, QRCode, Sending} from '../Context/Icons'
import {EditorMenu, EditorMenuLeft, EditorMenuRight, MenuItem, MenuItemBody, CloseMenuRight} from "../StyledComponents";
import {lang} from '../Helpers';

class Menu extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            deselectComponents: true,
            'activeIndex': null,
        }
    }

    handleClickMenu = (index) => {
        this.setState({activeIndex: index});
    }


    render() {
        const MenuItems = [
            {
                'icon': <General/>,
                'title': lang('general'),
                'body': <MenuGeneral updateEditorProperty={this.props.updateEditorProperty}
                                     editorProps={this.props.editorProps}/>,
            },
            {
                'icon': <Images />,
                'title': lang('images'),
                'body': <MenuImage
                    closeMenu={this.handleClickMenu}
                    addImagesToEditor={this.props.addImagesToEditor}
                    editorImages={this.props.editorImages}
                    deleteEditorImage={this.props.deleteEditorImage}
                    addObjectToArea={this.props.addObjectToArea}
                />,
            },
            {
                'icon': <Text/>,
                'title': lang('text'),
                'body': <MenuText closeMenu={this.handleClickMenu} addObjectToArea={this.props.addObjectToArea}/>,
            },
            {
                'icon': <Shortcodes/>,
                'title': lang('shortcodes'),
                'body': <MenuShortcodes closeMenu={this.handleClickMenu} addObjectToArea={this.props.addObjectToArea}/>,
            },
            {
                'icon': <QRCode/>,
                'title': lang('qrcode'),
                'body': <MenuQRCode closeMenu={this.handleClickMenu} addObjectToArea={this.props.addObjectToArea}/>,
            },
            {
                'icon': <Sending/>,
                'title': lang('sending'),
                'body': <MenuSending closeMenu={this.handleClickMenu} addObjectToArea={this.props.addObjectToArea}/>,
            }
        ]

        return (
            <EditorMenu>
                <EditorMenuLeft>
                    {
                        MenuItems.map(function (item, index) {
                            const className = this.state.activeIndex === index ? 'active' : '';
                            return (
                                <MenuItem
                                    key={index}
                                    className={className}
                                    onClick={this.handleClickMenu.bind(this, index)}
                                >
                                    <div className="icon">{item.icon}</div>
                                    <div className="menu-item-title">{item.title}</div>
                                </MenuItem>
                            );
                        }, this)
                    }
                </EditorMenuLeft>
                <EditorMenuRight className="editor-right-menu" collapse={this.state.activeIndex !== null}>
                    <CloseMenuRight onClick={this.handleClickMenu.bind(this, null)}><MdClose/></CloseMenuRight>
                    {
                        MenuItems.map(function (item, index) {
                            const className = this.state.activeIndex === index ? 'active-body menu-right-body' : 'menu-right-body';
                            return (
                                <MenuItemBody
                                    key={index}
                                    className={className}
                                >
                                    {item.body}
                                </MenuItemBody>
                            );
                        }, this)
                    }
                </EditorMenuRight>
            </EditorMenu>
        );
    }

}

export default Menu;
