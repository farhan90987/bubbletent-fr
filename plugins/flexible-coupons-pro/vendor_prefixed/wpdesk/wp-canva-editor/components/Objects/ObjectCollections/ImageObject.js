import React from 'react';

export default class ImageObject extends React.Component {

    onClick = ( e ) => {
        return false;
    }

    render() {
        return (
        	<img style={this.props.style} onClick={this.onClick} src={this.props.url} alt="" />
        );
    }
}
