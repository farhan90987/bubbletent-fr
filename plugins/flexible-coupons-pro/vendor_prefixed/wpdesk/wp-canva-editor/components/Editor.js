import React from 'react';
import EditorArea from "./Area/Area";
import EditorMenu from "./Menu/Menu";
import { ObjectData } from "./Context/EditorDefaultData";
import { EditorWrapper } from "./StyledComponents";



class Editor extends React.Component {

	constructor(props) {
		super(props);
		window.WPDeskCanvaEditor = this;

		this.state = {
			deselectComponents: true,
			editor: {
				'format': 'A4',
				'width': 595,
				'height': 842,
				'orientation': 'P',
				'backgroundColor': {
				    r: 255,
				    g: 255,
				    b: 255,
				    a: 100,
                },
				'fontColor': {
                    r: 0,
                    g: 0,
                    b: 0,
                    a: 100,
                },
				'fontSize': '16'
			},
			editorImages: [],
			areaObjects: [],
			selectedObject: {
				type: null,
				e: null,
				id: null,
			},
			cloneObjectCtrl: false,
			textEdit: 0,
		}

	}

	componentDidMount() {
		this.setState({
			...this.state,
			...window.WPDeskCanvaEditorData
		})
	}

	addImagesToEditor = (object) => {
		this.setState(prevState => ({
			editorImages: [...prevState.editorImages, object]
		}))
	}

	deleteEditorImage = ( e, id, object ) => {
		e.preventDefault();
		this.setState(prevState => ({
			editorImages: prevState.editorImages.filter(function (el, item_id) {
				return item_id !== id
			})
		}));

		this.setState(prevState => ({
			areaObjects: prevState.areaObjects.filter(function (el, item_id) {
				return el.url !== object.url
			})
		}));

		this.toggleSelectObject(false);
		this.areaHandleSelectObejct(null, null, null);
	}

	/**
	 * Update object state.
	 *
	 * @param objectId
	 * @param objectState
	 */
	updateObjectState = (objectId, objectState) => {
		let objects = this.state.areaObjects;
		objects[objectId] = objectState;
	}

	/**
	 * Change font size
	 * @param e
	 */
	actionChangeFontSize = (e) => {
		let objects = this.state.areaObjects;
		objects[this.state.selectedObject.id]['fontSize'] = e.target.value;
		this.setState({
			areaObjects: objects
		});
	}

	/**
	 * Delete object
	 *
	 * @param e
	 */
	actionDeleteObject = (e) => {
		e.preventDefault();
		let selectedId = this.state.selectedObject.id;
		this.setState(prevState => ({
			areaObjects: prevState.areaObjects.filter(function (el, item_id) {
				return item_id != selectedId
			})
		}));
		this.toggleSelectObject(false);
		this.areaHandleSelectObejct(null, null, null);
	}

	/**
	 * Clone active object
	 *
	 * @param e
	 */
	actionCloneObject = (e) => {
		e.preventDefault();
		let selectedId = this.state.selectedObject.id;
		let element = this.state.areaObjects[selectedId];
		let element_new = {
			...element,
			'active': 0,
			'top': element.top + element.height,
			'left': element.left,
		}
		this.setState(prevState => ({
			areaObjects: [...prevState.areaObjects, element_new],
		}))
		this.toggleSelectObject(false);
		this.areaHandleSelectObejct(null, null, null);
	}

	// Update object property.
	updateObjectProperties = (property, value) => {
		let selectedId = this.state.selectedObject.id;
		let objects = this.state.areaObjects;
		objects[selectedId][property] = value;
		this.setState(prevState => ({
			areaObjects: [...objects],
		}))
	}

	actionRotateObject = (e) => {
		let current_rotate, selectedId;
		selectedId = this.state.selectedObject.id;
		if ( selectedId !== null ) {
			current_rotate = this.state.areaObjects[selectedId]['rotateAngle'];
			if( current_rotate === 0 || current_rotate === 90 || current_rotate === 180 || current_rotate === 270 ) {
				current_rotate += 90;
				if( current_rotate === 360 ) {
					current_rotate = 0;
				}
			} else {
				current_rotate = 90;
			}

			this.updateObjectProperties('rotateAngle', current_rotate );
		}
	}

	actionFitToScreen = (e) => {
		let current_object, editor_width, editor_height, selectedId, aspectRatio, ratio_height, ratio_width;
		selectedId = this.state.selectedObject.id;

		editor_width = this.state.editor.width;
		editor_height = this.state.editor.height;
		if( this.state.editor.orientation === 'L' ) {
			editor_width = this.state.editor.height;
			editor_height = this.state.editor.width;
		}

		if ( selectedId !== null ) {
			current_object = this.state.areaObjects[selectedId];
			if (current_object.realHeight > current_object.realWidth && editor_height < current_object.realHeight) {
				aspectRatio = ( current_object.realWidth / current_object.realHeight );
				ratio_height = editor_height;
				ratio_width = ( editor_height * aspectRatio );
			} else if ( current_object.realHeight < current_object.realWidth && editor_width < current_object.realWidth ) {
				aspectRatio = ( current_object.realWidth / current_object.realHeight );
				ratio_width = editor_width;
				ratio_height = ( editor_width / aspectRatio );
			} else {
				ratio_width = current_object.width;
				ratio_height = current_object.height;
			}

			this.updateObjectProperties('height', Math.round( ratio_height ) );
			this.updateObjectProperties('width', Math.round( ratio_width ) );

			if( current_object.width !== ratio_width || current_object.height !== ratio_height  ) {
				this.updateObjectProperties('top', 0);
				this.updateObjectProperties('left', 0);
			}

		}
	}

	/**
	 * Update editor property.
	 *
	 * @param property
	 * @param value
	 */
	updateEditorProperty = (property, value) => {
		let object = {[property]: value};
		this.setState(prevState => ({
			editor: {...prevState.editor, ...object},
		}))
	}

	/**
	 * Get editor data.
	 *
	 * @returns {Readonly<S>}
	 */
	getEditorData = () => {
		return this.state;
	}

	/**
	 * Set active for current object, deselect all.
	 *
	 * Maybe we dont need iterate?
	 *
	 * @param id
	 */
	toggleSelectObject = (id) => {
		this.setState(prevState => ({
			areaObjects: prevState.areaObjects.map(function (el, item_id) {
				if (id !== false) {
					if (item_id === id) {
						el['active'] = 1;
					} else {
						el['active'] = 0;
					}
				} else {
					el['active'] = 0;
				}

				return el;
			})
		}));
	}


	actionHandleLayerUp = (e) => {
		e.preventDefault();
		let selectedId = this.state.selectedObject.id;
		let new_object = this.state.areaObjects[selectedId]
		let objects = this.state.areaObjects;
		if (selectedId === objects.length - 1) {
			return;
		}
		objects.splice(selectedId, 1);
		objects.splice(selectedId + 1, 0, new_object);

		this.setState(prevState => ({
			selectedObject: {...this.state.selectedObject, ...{id: selectedId + 1}}
		}))

		this.setState(prevState => ({
			areaObjects: [...objects]
		}))
	}

	actionLayerDown = (e) => {
		e.preventDefault();
		let selectedId = this.state.selectedObject.id;
		if (selectedId === 0) {
			return;
		}
		let new_object = this.state.areaObjects[selectedId]
		let objects = this.state.areaObjects;
		objects.splice(selectedId, 1);
		objects.splice(selectedId - 1, 0, new_object);

		this.setState(prevState => ({
			selectedObject: {...this.state.selectedObject, ...{id: selectedId - 1}}
		}))

		this.setState(prevState => ({
			areaObjects: [...objects]
		}))
	}

	handleImageEdit = () => {
		alert('Wymieniam obrazek');
	}

	areaHandleSelectObejct = (e, type, id) => {
		this.setState({
			selectedObject: {
				e: this.state.areaObjects[id],
				type: type,
				id: id,
			}
		})
	}

	addObjectToArea = (e, properties) => {
		e.preventDefault();
		let object = {
			type: properties.type ? properties.type : 'image',
			width: 200,
			height: 200,
			top: 40,
			left: 40,
			rotate: 0,
			url: properties.type === 'image' ? '' : '',
			tag: properties.tag ? properties.tag : '',
			fontWeight: properties.tag === 'h1' || properties.tag === 'h2' || properties.tag === 'h3' ? '700' : '400',
            fontStyle: properties.fontStyle ? properties.fontStyle : 'normal',
			color: '#000',
			zIndex: '0',
			active: 0,
			...properties
		}

		if (object.width > this.state.editor.width) {
			let object_width_fit = object.width;
			object.width = this.state.editor.width / 2
			object.height = Math.round(object.width * object.height / object_width_fit);
		}

		this.setState(previousState => ({
			areaObjects: [
				...previousState.areaObjects,
				{...ObjectData, ...object}
			]
		}));
		return false;
	}

	fireAction = (e, action, properties = {}) => {
		e.preventDefault();
		switch (action) {
			case 'object_font_size':
				this.actionChangeFontSize(e, properties);
				break;
			case 'object_layer_up':
				this.actionHandleLayerUp(e, properties);
				break;
			case 'object_layer_down':
				this.actionLayerDown(e, properties);
				break;
			case 'object_delete':
				this.actionDeleteObject(e, properties);
				break;
			case 'object_clone':
				this.actionCloneObject(e, properties);
				break;

			case 'object_rotate_right':
				this.actionRotateObject(e, properties);
				break;
			case 'object_fit_to_screen':
				this.actionFitToScreen(e, properties);
				break;
		}
	}


	render() {
		const EditorAreaStyle = {
			width: this.state.editor.orientation === 'L' ? this.state.editor.height : this.state.editor.width,
			height: this.state.editor.orientation === 'L' ? this.state.editor.width : this.state.editor.height,
			backgroundColor: this.state.editor.backgroundColor,
		}

		return (
			<EditorWrapper>
				<EditorMenu
					addObjectToArea={this.addObjectToArea}
					updateEditorProperty={this.updateEditorProperty}
					editorProps={this.state.editor}
					editorImages={this.state.editorImages}
					deleteEditorImage={this.deleteEditorImage}
					addImagesToEditor={this.addImagesToEditor}
					fireAction={this.fireAction}
				/>

				<EditorArea
					toggleSelectObject={this.toggleSelectObject}
					areaObjects={this.state.areaObjects}
					selectedObject={this.state.selectedObject}
					areaHandleSelectObejct={this.areaHandleSelectObejct}
					editorStyle={EditorAreaStyle}
					updateObjectState={this.updateObjectState}
					fireAction={this.fireAction}
					handleImageEdit={this.handleImageEdit}
					updateObjectProperties={this.updateObjectProperties}

				/>
			</EditorWrapper>
		);
	}

}

export default Editor;
