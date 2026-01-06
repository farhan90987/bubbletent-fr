import React from 'react';
import { QRHeader, ProVersion } from "../StyledComponents";
import { lang } from '../Helpers';

class Sending extends React.Component {

    constructor(props) {
        super(props);
    }

    addQRcodeImage = (event, object) => {
        this.props.addObjectToArea(event, object)
        this.props.closeMenu(null);
    }

    render() {
        let SendingLink = '';
        if (lang('sending_plugin_enabled') === 'no') {
            SendingLink = (
                <QRHeader>
                    {lang('sending_plugin_buy_label')}: <a target="_blank" className="sending-link" href={lang('sending_buy_url')}>{lang('sending_plugin_name')} &rarr;</a>
                </QRHeader>
            );
        }

        let ProAddonInfo = '';
        if (lang('coupons_pro_plugin_enabled') === 'no') {
            ProAddonInfo = (
                <ProVersion dangerouslySetInnerHTML={{ __html: lang('pro_addon_info') }}></ProVersion>
            );
        }

        if (SendingLink || ProAddonInfo) {
            return (
                <React.Fragment>
                    {SendingLink}
                    {ProAddonInfo}
                </React.Fragment>
            );
        }

        return (
            <React.Fragment>
                <QRHeader>
                    <a target="_blank" className="sending-link" href={lang('sending_settings_url')}>{lang('sending_plugin_label')} &rarr;</a>
                </QRHeader>
            </React.Fragment>
        );
    }
}

export default Sending;
