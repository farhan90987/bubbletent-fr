import React from 'react'
import ResizableRect from '../ResizableRect/ResizableRect'
import ImageObject from '../ObjectCollections/ImageObject'
import ObjectContent from '../ObjectCollections/ObjectContent'
import PropTypes from 'prop-types';
import { lang, updateAuxlineBottom, updateAuxlineLeft, updateAuxlineRight, updateAuxlineTop } from '../../Helpers';

class EditorObject extends React.Component {

    static propTypes = {
        zIndex: PropTypes.string,
        objectProps: PropTypes.object,
        height: PropTypes.number,
        width: PropTypes.number,
        active: PropTypes.number,
    }

    constructor( props ) {
        super( props )
        this.objectID = this.props.objectId;
        this.state = { stop: false, ...props.objectProps }
		this.dragWait = 0;
    }

    componentWillReceiveProps( newProps) {
        const areaObject = newProps.objectProps;
        this.setState({ ...areaObject } );
    }

    handleResize = (style, isShiftKey, type) => {

        if( ! this.state.active ) {
            return;
        }

        let area = document.getElementById('editor-area');
        let area_width = area.offsetWidth;

        let {top, left, width, height} = style;
        top = Math.round(top);
        left = Math.round(left);
        width = Math.round(width);
        height = Math.round(height);

        // Stop scaling if the real width will be exceeded
        if( this.state.realWidth !== null && width >= this.state.realWidth ) {
        	let realWidth = this.state.realWidth;
			let realHeight = this.state.realHeight;
			if( realWidth > 0 && realHeight > 0 ) {
				this.setState({
					realWidth,
					realHeight
				});
				return false;
			}
		}

        this.edgeDetector( left, top );

        this.setState({
            top,
            left,
            width,
            height
        })
    }

    handleResizeEnd = () => {
        this.props.updateObjectState( this.objectID, this.state );
    }

    handleRotate = (rotateAngle) => {
        this.setState({
            rotateAngle
        })
    }

    handleRotateEnd = () => {
        this.props.updateObjectState( this.objectID, this.state );
    }


    objectsPosition = () => {
        let area_width = this.props.editorProp.width;
        let area_height = this.props.editorProp.height;

        this.props.objectPositions.lefts['left'] = 0;
        this.props.objectPositions.lefts['center'] = parseInt(area_width / 2);
        this.props.objectPositions.rights['right'] = parseInt(area_width);
        this.props.objectPositions.rights['center'] = parseInt(area_width / 2);
        this.props.objectPositions.tops['top'] = 0;
        this.props.objectPositions.tops['center'] = parseInt(area_height / 2);
        this.props.objectPositions.bottoms['top'] = parseInt(area_height - this.state.height);
        this.props.objectPositions.bottoms['bottom'] = parseInt(area_height);

        delete this.props.objectPositions.lefts[ this.objectID ];
        delete this.props.objectPositions.rights[ this.objectID ];
        delete this.props.objectPositions.tops[ this.objectID ];
        delete this.props.objectPositions.bottoms[ this.objectID ];

        return {
            'lefts': Object.values(this.props.objectPositions.lefts),
            'rights': Object.values(this.props.objectPositions.rights),
            'tops': Object.values(this.props.objectPositions.tops),
            'bottoms': Object.values(this.props.objectPositions.bottoms),
        };
    }

    /**
     * Detects edges of all objects inside area when selected object is resized or moved.
     *
     * @param left
     * @param top
     */
    edgeDetector = ( left, top ) => {
        let objectsPositions = this.objectsPosition();
        let pinned = 0;

        // Pin object on top corner
        if( this.state.top > top || this.state.top < top ) {

            if( objectsPositions.tops.includes( top ) ) {
                if( this.dragWait === 0 ) {
                    this.setState({ left: left, top: top } )
                }
                updateAuxlineTop( { top: top, display: 'block' } );
                this.dragWait = 1;
                setTimeout( ()  => {
                    this.dragWait = 0;
                    updateAuxlineTop( { left: 0, display: 'none' } );
                }, 200 );
            }
        }

        /** Left corner object */
        if( this.state.left < left || this.state.left > left ) {
            // Pin left from right
            if( objectsPositions.lefts.includes( left ) ) {
                pinned = 1;
                if( this.dragWait === 0 ) {
                    this.setState({ left: left, top: top } )
                }
                updateAuxlineLeft( { left: left, display: 'block' } );
                this.dragWait = 1;
                setTimeout( ()  => {
                    this.dragWait = 0;
                    updateAuxlineLeft( { left: 0, display: 'none' } );
                }, 200 );
            }
        }

        let object_center = parseInt( this.state.width / 2 );

        /** Center object with center area */
        if( ( this.state.left + object_center < left + object_center || this.state.left + object_center > left + object_center ) && !pinned ) {
            if( objectsPositions.lefts.includes( left + object_center ) ) {
                pinned = 1;
                if( this.dragWait === 0 ) {
                    this.setState({ left: left, top: top } )
                }
                updateAuxlineLeft( { left: left + object_center, display: 'block' } );
                this.dragWait = 1;
                setTimeout( ()  => {
                    this.dragWait = 0;
                    updateAuxlineLeft( { left: 0, display: 'none' } );
                }, 200 );
            }
        }

        let right = left + this.state.width;

        /** Right corner object */
        if( this.state.left < right || this.state.left > right && ! pinned ) {
            if( objectsPositions.rights.includes( right ) ) {
                if( this.dragWait === 0 ) {
                    this.setState({ left: left, top: top } )
                }
                updateAuxlineRight( { left: right, display: 'block' } );
                this.dragWait = 1;
                setTimeout( ()  => {
                    this.dragWait = 0;
                    updateAuxlineRight( { left: 0, display: 'none' } );
                }, 200 );
            }
        }
    }

    /**
     * Handle drag.
     *
     * In this function we added several functions that stop the object at the edges of the area or other objects.
     *
     * @param deltaX
     * @param deltaY
     */
    handleDrag = (deltaX, deltaY) => {
        let left = this.state.left + deltaX;
        let top = this.state.top + deltaY;

        this.edgeDetector( left, top );

		if( this.dragWait === 0 ) {
			// Update left and top for objects.
			this.setState({ left: left, top: top } )
		}
    }


    handleDragEnd = () => {
        this.props.updateObjectState( this.objectID, this.state );
    }

    handleClick = () => {
        this.props.handleEditorArea( true );
        this.props.toggleSelectObject( this.objectID );
        this.props.areaHandleSelectObejct();
        this.setState({active: 1});
    }

    handleDragStart = () => {
        this.props.handleEditorArea( true );
    }

    handleDoubleClickItem = () => {
        if( 'text' === this.state.type ) {
            this.setState(prevstate => ({
                ...prevstate,
                zIndex: 10
            }));
        }
    }

    updateParent = ( e ) => {
        this.setState( {
            width: e.scrollWidth,
            height: e.scrollHeight,
        });

		this.props.updateObjectProperties( 'text', e.textContent );
		this.props.updateObjectProperties( 'width', e.scrollWidth );
		this.props.updateObjectProperties( 'height', e.scrollHeight );
    }

	handleKeyPress = ( e ) => {
		e.preventDefault();

		// Move top
		if ( e.key === 'ArrowUp' ) {
			let move_top = this.state.top - 1;
			this.props.updateObjectProperties('top', move_top);
		}

		// Move bottom
		if ( e.key === 'ArrowDown' ) {
			let move_bottom = this.state.top + 1;
			this.props.updateObjectProperties('top', move_bottom);
		}

		// Move left
		if ( e.key === 'ArrowLeft' ) {
			let move_left = this.state.left - 1;
			this.props.updateObjectProperties('left', move_left);
		}

		// Move right
		if ( e.key === 'ArrowRight' ) {
			let move_right = this.state.left + 1;
			this.props.updateObjectProperties('left', move_right);
		}

		// Delete object
		if (e.key === 'Delete' || e.key === 'Backspace' ) {
			e.preventDefault();
			this.props.fireAction( e, 'object_delete' );
		}

	}

    getColor = (color) => {
        if (!color.hasOwnProperty('b')) {
            return 'rgba(0,0,0,1)';
        }
        return `rgba(${color.r}, ${color.g}, ${color.b}, ${color.a})`;
    }

    render() {
        const {textAlign, text, zIndex, fontFamily, fontSize, fontWeight, backgroundColor, color, width, top, left, height, rotateAngle, fontStyle } = this.state;

        let fontColor = this.getColor( color );

        let font_family = "'" + fontFamily + "', serif";
        const objectStyle = { zIndex: zIndex, margin: 0, padding: 0, width, textAlign: textAlign, fontWeight: fontWeight, fontStyle: fontStyle, fontFamily: font_family, color: fontColor, fontSize: fontSize + 'px', height, top, left, transform: `rotate(${rotateAngle}deg)`, position: 'absolute' }
        const { type, url, tag } = this.props.objectProps;
        let AreaObject;

        if ( type === 'image' || type === 'qr_code' ) {
            AreaObject = <ImageObject contentTag={tag} type={type} url={url} style={objectStyle} />;
        } else {
            AreaObject = <ObjectContent updateParent={this.updateParent} tag={tag} text={text} type={type} style={objectStyle} />;
        }

        return (
            <div>
                {AreaObject}
                <ResizableRect
                    title={lang('double_click_to_edit')}
                    onDoubleClick={this.handleDoubleClickItem}
                    objectType={this.props.objectProps.type}
                    targetArea="editor-area"
                    active={this.state.active}
                    className={this.state.active ? 'active' : ''}
                    onClick={() => this.handleClick(this, this.props)}
                    left={left}
                    top={top}
                    width={width}
                    height={height}
                    rotateAngle={rotateAngle}
                    aspectRatio={!type === 'text'}
                    minWidth={20}
                    minHeight={20}
                    zoomable='n, w, s, e, nw, ne, se, sw'
                    rotatable={ type === 'image' }
                    // onRotateStart={this.handleRotateStart}
                    onRotate={this.handleRotate}
                    onRotateEnd={this.handleRotateEnd}
                    // onResizeStart={this.handleResizeStart}
                    onResize={this.handleResize}
                    onResizeEnd={this.handleResizeEnd}
                    onDragStart={this.handleDragStart}
                    onDrag={this.handleDrag}
                    onDragEnd={this.handleDragEnd}
                    onKeyDown={this.handleKeyPress}
                />
            </div>
        )
    }
}

export default EditorObject
