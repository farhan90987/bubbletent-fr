import React from 'react';
import {MenuTextWrapper, MenuTextWrapperItem} from "../StyledComponents";
import {lang, texts_list} from '../Helpers';
import {TextData} from "../Context/EditorDefaultData";

class Text extends React.Component {

    constructor(props) {
        super(props);
    }

    addText = (event, object) => {
        this.props.addObjectToArea(event, object)
        this.props.closeMenu(null);
    }

    render() {
        let texts = texts_list();
        const TextItems = texts.map(( object, key) => {
            let text_props = {...TextData, ...object};
            return (
                <MenuTextWrapperItem
                    key={key}
                    fontSize={text_props.fontSize}
                    fontWeight={text_props.fontWeight}
                    fontStyle={text_props.fontStyle}
                    title={lang('add_to_area')}
                    onClick={ (event) => this.addText( event, { ...text_props })}>
                        {text_props.name}
                    </MenuTextWrapperItem>
            )
        });

        return (
            <MenuTextWrapper>
                {TextItems}
            </MenuTextWrapper>
        );
    }

}

export default Text;
