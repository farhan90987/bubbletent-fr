import React from 'react';
import { Label, Header, Modal, BackgroundSquare } from '../StyledComponents'
import { SketchPicker} from 'react-color';
import {lang} from '../Helpers';

class General extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
			'backgroundColorModal': false
        }
    }

	/**
	 * Change area format.
	 *
	 * @param e
	 */
	handleFormatChange = (e) => {
        let format = e.currentTarget.value;

        let format_data;

        if (format === 'A4') {
            format_data = {
                'format': 'A4',
                'width': 595,
                'height': 842,
            }
        }

        if (format === 'A5') {
            format_data = {
                'format': 'A5',
                'width': 420,
                'height': 595,
            }
        }

        if (format === 'A6') {
            format_data = {
                'format': 'A6',
                'width': 298,
                'height': 420,
            };
        }

        this.props.updateEditorProperty( 'width', format_data.width );
        this.props.updateEditorProperty( 'height', format_data.height );
        this.props.updateEditorProperty( 'format', format_data.format );
    }

	/**
	 * Change area orientation.
	 *
	 * P = Vertical, L = Horizontal
	 * @param e
	 */
    handleOrientationChange = (e) => {
        let value = e.target.value;
        this.props.updateEditorProperty( 'orientation', value );
    }

	/**
	 * Toggle background picker.
	 *
	 * @param e
	 */
	toggleBackgroundModal = ( e ) => {
		e.preventDefault();
		const currentState = this.state.backgroundColorModal;
		this.setState({ backgroundColorModal: !currentState });
	}

	/**
	 * Change area background color.
	 *
	 * P = Vertical, L = Horizontal
	 * @param color
	 */
	handleBackgroundColorChange = ( color ) => {
        this.props.updateEditorProperty( 'backgroundColor', color.rgb );
    }

    getBackgroundColor = ( backgroundColor ) => {
        if( ! backgroundColor.hasOwnProperty('b') ) {
            return {
                r: 255,
                g: 255,
                b: 255,
                a: 100,
            }
        }

        return backgroundColor;
    }

    render() {

    	const { format, orientation, backgroundColor } = this.props.editorProps;
    	let bgColor = this.getBackgroundColor( backgroundColor );

        return (
            <React.Fragment>
				<div className="editor-menu-dimension">
					<Header>{lang('select_format')}</Header>
					<Label><input key={1} checked={format === 'A4'} onChange={this.handleFormatChange} type="radio" name="format" value="A4"/> A4</Label>
					<Label><input key={3} checked={format === 'A5'} onChange={this.handleFormatChange} type="radio" name="format" value="A5"/> A5</Label>
					<Label><input key={2} checked={format === 'A6'} onChange={this.handleFormatChange} type="radio" name="format" value="A6"/> A6</Label>
				</div>
				<div className="editor-menu-area-orientation">
					<Header>{lang('page_orientation')}</Header>
					<Label><input key={1} checked={orientation === 'P'} onChange={this.handleOrientationChange} type="radio" name="orientation" value="P"/> {lang('vertical')}</Label>
					<Label><input key={2} checked={orientation === 'L'} onChange={this.handleOrientationChange} type="radio" name="orientation" value="L"/> {lang('horizontal')} </Label>
				</div>
				<div className="editor-menu-area-background-color">
					<Header>{lang('background_color')}</Header>
					<BackgroundSquare onClick={this.toggleBackgroundModal} bgcolor={bgColor} />
					<Modal display={this.state.backgroundColorModal ? 'block': 'none'}>
						<SketchPicker width="200" color={bgColor} onChangeComplete={ this.handleBackgroundColorChange } />
					</Modal>
				</div>
			</React.Fragment>
        );
    }

}

export default General;
