import React from 'react';
import { ImageContainer, EditorImagesWrapper } from "../StyledComponents";
import { MdClose } from '../Context/Icons';
import {lang} from '../Helpers';

class Image extends React.Component {

    constructor(props) {
        super(props);
    }

	addImages = ( objects ) => {
		objects.map( ( object, key ) => {
			let image = {
				id: object.id,
				title: object.title,
				url: object.sizes.full.url,
				width: object.sizes.full.width,
				height: object.sizes.full.height,
				realWidth: object.sizes.full.width,
				realHeight: object.sizes.full.height,
				sizes: object.sizes,
				type: 'image',
			};
			this.props.addImagesToEditor( image );
		}, this );
	}

	selectImage = (e) => {
		e.preventDefault();

		let rthis = this;

		var mediaUploader, attachments;

		if (mediaUploader) {
			mediaUploader.open();
			return;
		}
		// Extend the wp.media object
		mediaUploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
            library: {
                type: [ 'image' ]
            },
            multiple: true
		});

		mediaUploader.on('select', function() {
			attachments = mediaUploader.state().get('selection').toJSON();
			rthis.addImages( attachments );
		});

		mediaUploader.open();
	}

	addImage = ( event, object ) => {
        this.props.addObjectToArea(event, object)
        this.props.closeMenu( null );
    }

    render() {

		const images = this.props.editorImages.map((object, number) => {
            let thumbnail;
            if (
                typeof object.id !== 'undefined'
                && object.hasOwnProperty('sizes')
                && object.sizes.hasOwnProperty('thumbnail')
            ) {
                thumbnail = object.sizes.thumbnail.url;
            } else {
                thumbnail = object.url
            }

			return (
				<ImageContainer key={number}>
					<span title={lang('remove_from_project')} className="delteEditorImage" onClick={(event) => this.props.deleteEditorImage(event, number, object)} ><MdClose/></span>
					<img title={lang('add_to_area')} alt="" src={thumbnail} onClick={(event) => this.addImage(event, object)} />
				</ImageContainer>
			)
		});

        return (
            <div className="editor-menu-image">
				<div className="button-wrapper"><button className="button button-primary" onClick={this.selectImage}>{lang('select_images')}</button></div>
				<EditorImagesWrapper>
				{images}
				</EditorImagesWrapper>
            </div>
        );
    }

}

export default Image;
