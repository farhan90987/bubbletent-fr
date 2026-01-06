import React from 'react';
import {ProVersion, ListParent, ListItem} from "../StyledComponents";
import {lang, shortcode_list} from '../Helpers';
import {ShortcodeData} from "../Context/EditorDefaultData";

class Shortcodes extends React.Component {

    constructor(props) {
        super(props);
    }

    addShortcode = (event, object) => {
        this.props.addObjectToArea(event, object)
        this.props.closeMenu(null);
    }

    render() {
        let shortcodes = shortcode_list();
        const ShortcodeItems = shortcodes.map((object, key) => {
            let shortcode_props = {...ShortcodeData, ...object};
            return (
                <ListItem
                    key={key}
                    title={lang('add_to_area')}
                    onClick={(event) => this.addShortcode(event, {...shortcode_props})}>
                    {shortcode_props.text}
                </ListItem>
            )
        });

        let ProVersionLink = '';
        if (lang('coupons_pro_plugin_enabled') === 'no') {
            ProVersionLink = <ProVersion dangerouslySetInnerHTML={{ __html: lang('upgrade_to_pro') }}></ProVersion>
        }

        let ShortcodeLink = '';
        if (lang('shortcodes_plugin_enabled') === 'no') {
            ShortcodeLink = <ProVersion dangerouslySetInnerHTML={{ __html: lang('shortcodes_plugin_link') }}></ProVersion>
        }

        return (
            <React.Fragment>
                <ListParent>
                    {ShortcodeItems}
                    {ProVersionLink}
                    {ShortcodeLink}
                </ListParent>
            </React.Fragment>
        );
    }

}

export default Shortcodes;
