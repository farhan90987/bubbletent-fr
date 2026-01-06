import React from 'react';
import EditorObject from "../Objects/EditorObject/EditorObject";
import ObjectsProperties from "./ObjectsProperties";
import {ObjectData} from "../Context/EditorDefaultData";
import { AuxLineVertical, AuxLineHorizontal } from "../StyledComponents";

class Area extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            activeChildren: false,
        }
    }

    handleEditorArea = (is_visible) => {
        let visible = is_visible ? 'visible' : 'hidden';
        this.setState({
            'style': {
                'overflow': visible,
            }
        })
    }

    handleCanvaAreaClick = ( e ) => {

        if( e.target.id === 'wpdesk-canva-area' || e.target.id === 'editor-area' ) {
			this.handleEditorArea( false );
            this.props.areaHandleSelectObejct( null, null, null );
            this.props.toggleSelectObject( null );
        }
    }

    updateAuxline = ( data ) => {
    	this.setState(prevState => ( {
			'auxLine': { ...prevState.auxLine, ...data }
		}))
	}

    getBackgroundColor = (color) => {
        if (!color.hasOwnProperty('b')) {
            return 'rgba(255,255,255,1)';
        }
        return `rgba(${color.r}, ${color.g}, ${color.b}, ${color.a})`;
    }

    render() {
        let areaObjects = this.props.areaObjects;
		let positions = {
			lefts: [],
			tops: [],
			rights: [],
			bottoms: []
		}

        let editorProp = this.props.editorStyle;

        const listItems = areaObjects.map((object, number) => {
            let merged_object = {...ObjectData, ...object};

			positions.lefts[ number ] = object.left;
			positions.tops[ number ] = object.top;
			positions.rights[ number ] = object.width + object.left;
			positions.bottoms[ number ] = object.height + object.top;

            return (
                <EditorObject
					items={areaObjects.length}
					updateAuxline={this.updateAuxline}
                    key={number}
                    handleEditorArea={this.handleEditorArea}
                    areaHandleSelectObejct={() => this.props.areaHandleSelectObejct(this, object.type, number)}
                    toggleSelectObject={this.props.toggleSelectObject}
                    objectProps={merged_object}
                    objectId={number}
                    objectPositions={positions}
                    updateObjectState={this.props.updateObjectState}
					updateObjectProperties={this.props.updateObjectProperties}
					fireAction={this.props.fireAction}
                    editorProp={editorProp}
                />
            )
        });

		const areaStyle = {
		    width: editorProp.width,
            height: editorProp.height,
            backgroundColor: this.getBackgroundColor( editorProp.backgroundColor ),
        }

        let hcleft = parseInt(editorProp.width / 2 );

        const style = {...areaStyle, ...this.state.style}
        return (
            <div id="wpdesk-canva-area" onClick={this.handleCanvaAreaClick}>
                <ObjectsProperties
                    selectedObject={this.props.selectedObject}
					fireAction={this.props.fireAction}
                    handleImageEdit={this.props.handleImageEdit}
                    updateObjectProperties={this.props.updateObjectProperties}
					onChange={this.onChangeValue}
					inputValue={this.state.inputValue}
                />
                <div style={style} className="editor-area" id="editor-area">
					<AuxLineVertical id="auxLineLeft" right="auto" />
					<AuxLineVertical id="auxLineRight" left="auto" />
					<AuxLineHorizontal id="auxLineTop" bottom="auto" />
					<AuxLineHorizontal id="auxLineBottom" top="auto" />
					<span className="area-border-center-vertical" style={{left:hcleft}} />
                    <span className="area-border-center-horizontal" />
                    <span className="area-border-top" />
                    <span className="area-border-left" />
                    <span className="area-border-right" />
                    <span className="area-border-bottom" />
                    {listItems}
                </div>

            </div>
        );
    }

}

export default Area;
