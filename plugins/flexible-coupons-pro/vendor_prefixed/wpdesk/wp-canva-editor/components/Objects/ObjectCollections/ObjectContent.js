import React from 'react';

export default class ObjectContent extends React.Component {

	constructor( props ) {
		super( props );
		this.state = {
			'text' : props.text,
		}

		this.myRef = React.createRef();
	}

	handleLoginKeyUp = () =>  {
		this.props.updateParent( this.myRef.current );
	}

    render() {

        const ContentTag = this.props.tag ? this.props.tag : "p";

        return (
            <ContentTag
				ref={this.myRef}
				onBlur={this.handleLoginKeyUp}
				style={this.props.style}
				contentEditable={true}
				suppressContentEditableWarning={true}
			>
                {this.props.text}
            </ContentTag>
        );
    }
}
