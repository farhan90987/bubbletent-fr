import React from 'react';
import { EditorImagesWrapper, QRCodeContainer, QRHeader, ProVersion } from "../StyledComponents";
import { lang, qrcode_list } from '../Helpers';
import { QRCodeData } from "../Context/EditorDefaultData";

class QRCode extends React.Component {

    constructor(props) {
        super(props);
    }

    addQRcodeImage = (event, object) => {
        this.props.addObjectToArea(event, object)
        this.props.closeMenu(null);
    }

    render() {
        let TicketsLink = '';
        if (lang('tickets_plugin_enabled') === 'no') {
            TicketsLink = (
                <QRHeader>
                    {lang('tickets_plugin_buy_label')}: <a target="_blank" className="tickets-link" href={lang('tickets_buy_url')}>{lang('tickets_plugin_name')} &rarr;</a>
                </QRHeader>
            );
        }

        let ProAddonInfo = '';
        if (lang('coupons_pro_plugin_enabled') === 'no') {
            ProAddonInfo = (
                <ProVersion dangerouslySetInnerHTML={{ __html: lang('pro_addon_info') }}></ProVersion>
            );
        }

        if (TicketsLink || ProAddonInfo) {
            return (
                <React.Fragment>
                    {TicketsLink}
                    {ProAddonInfo}
                </React.Fragment>
            );
        }

        let qr_codes = qrcode_list();
        const QRCodeItems = qr_codes.map((object, number) => {
            let props = { ...QRCodeData, ...object };
            return (
                <QRCodeContainer key={number}>
                    <img
                        title={lang('add_to_area')}
                        alt=""
                        src={props.url}
                        onClick={(event) => this.addQRcodeImage(event, props)}
                    />
                    <p>{props.name}</p>
                </QRCodeContainer>
            )
        });

        return (
            <div className="editor-menu-image">
                <EditorImagesWrapper>
                    <QRHeader>
                        {lang('tickets_plugin_label')} <a target="_blank" className="tickets-link" href={lang('tickets_docs_url')}>{lang('tickets_docs_label')} &rarr;</a>
                    </QRHeader>
                    {QRCodeItems}
                </EditorImagesWrapper>
            </div>
        );
    }
}

export default QRCode;
