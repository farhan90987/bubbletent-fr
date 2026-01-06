import React from 'react';
import styled from "styled-components";

export const EditorWrapper = styled.div`
	width: 100%;
	overflow: hidden;
	position: relative;
	flex: 1;
	display: flex;
	flex-direction: row;
	flex-wrap: nowrap;
	z-index: 0;
	box-sizing: border-box;
`

export const DivWrapper = styled.div`
  position: absolute;
  border: 1px dashed #7ddbf0;
  padding: 0;
  margin: 0;
  box-sizing: border-box;
  cursor: move;

  &.active {
    border: 1px solid #7ddbf0;
  }

  &.active:hover {
    border: 1px solid #7ddbf0;
  }

  :hover {
    border: 1px solid #7ddbf0;
  }

  .square {
    position: absolute;
    width: 9px;
    height: 9px;
    background: white;
    border: 1px solid #7ddbf0;
    border-radius: 1px;
    border-radius: 50%;
    display: none;
  }

   :hover .square {
        display: block;
    }

  &.active .square {
    display: block;
    }

  .resizable-handler {
    position: absolute;
    width: 14px;
    height: 14px;
    cursor: pointer;
    z-index: 1;

    &.tl,
    &.t,
    &.tr {
      top: -7px;
    }

    &.tl,
    &.l,
    &.bl {
      left: -7px;
    }

    &.bl,
    &.b,
    &.br {
      bottom: -7px;
    }

    &.br,
    &.r,
    &.tr {
      right: -7px;
    }

    &.l,
    &.r {
      margin-top: -7px;
    }

    &.t,
    &.b {
      margin-left: -7px;
    }
  }

  .rotate {
    position: absolute;
    left: 50%;
    top: -36px;
    width: 18px;
    height: 18px;
    margin-left: -9px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;


    :before {
      content: "";
      width: 1px;
      height: 30px;
      background-color: #7ddbf0;
      position: absolute;
      left: 50%;
      margin-top: 23px;
      margin-left: 1px;
      display: none;
    }

    .square {
      width: 11px;
      height: 11px;
    }
  }

  :hover .rotate,
  &.active .rotate {

    :before {
        display: block;
        }
  }



  .t,
  .tl,
  .tr {
    top: -5px;
  }

  .b,
  .bl,
  .br {
    bottom: -5px;
  }

  .r,
  .tr,
  .br {
    right: -3px;
  }

  .tl,
  .l,
  .bl {
    left: -3px;
  }

  .l,
  .r {
    top: 50%;
    margin-top: -3px;
  }

  .t,
  .b {
    left: 50%;
    margin-left: -5px;
  }

  .ro {
    margin-right: -3px;
    cursor: grab;
  }
`

export const MenuTextWrapper = styled.ul`
    padding: 0;
    margin: 0;
    list-style: none;
    border: 0;
`

export const MenuTextWrapperItem = styled.li`
    border: 0;
    padding: 10px 10px;
    margin: 0 0;
    font-size: ${props => props.fontSize + 'px' || "16px"};
    font-weight: ${props => props.fontWeight || "400"};
    cursor: pointer;
`


export const ListParent = styled.ul`
    padding: 0;
    margin: 0;
    list-style: none;
    border: 0;
`

export const ListItem = styled.li`
  border: 0;
  padding: 8px 6px;
  background-color: transparent;
  color: #eee;
  line-height: 1;
  position: relative;

  :hover {
    color: #fff;
    cursor: pointer;
    cursor: hand;
  }
`

export const ImageContainer = styled.div`
	width: 100px;
	height: 100px;
	padding: 0;
	display: inline-block;
	margin: 0;
	margin-right: 5px;
	overflow: hidden;
	position: relative;
	background-color: #FFF;
	border-radius: 3px;

	img {
		width: 100%;
		max-width: 100%;
		height: auto;
		cursor: pointer;
	}

	.delteEditorImage {
		position: absolute;
		top: 4px;
		right: 4px;
		width: 20px;
		height: 20px;
		border-radius: 3px;
		background-color: #FFF;
		font-size: 20px;
		cursor: pointer;
		color: #000;

		:hover {
			background-color: #DDD;
			color: #333;
		}
	}
`

export const QRCodeContainer = styled.div`
	width: 100px;
	padding: 0;
	display: inline-block;
	margin: 0;
	margin-right: 5px;
    margin-left: 10px;
	overflow: hidden;
	position: relative;
	border-radius: 3px;

	img {
		width: 100%;
		max-width: 100%;
		height: auto;
		cursor: pointer;
	}

	p {
        background-color: #32373c;
        color: white;
        margin: 0;
        text-align: center;
        font-size: 14px;
    }
`

export const EditorImagesWrapper = styled.div`
	padding: 10px 0;
`

// Menu

export const EditorMenu = styled.div`
    min-width: 100px;
    border-right: 1px solid #EEE;
    background-color: #23282d;
    height: 100vh;
    flex-shrink: 0;
    position: relative;
`

export const EditorMenuLeft = styled.div`
	position: relative;
	width: 100px;
	background-color: #23282d;
    height: 100vh;
    overflow-y: auto;
    padding-top: 1em;
    padding-bottom: 1em;
`

export const EditorMenuRight = styled.div`
	display: ${props => props.collapse ? "block" : "none"};
	width: 220px;
	height: 100vh;
	padding: 20px 10px;
	color: #EEE;
	background-color: #32373c;
	position: absolute;
	left: 100px;
	top: 0;
	bottom: 0;
	z-index: 400;
	overflow-y: auto;
    padding-top: 1em;
    padding-bottom: 1em;

	.object-text {
		color: #EEE;
	}
`

export const MenuItem = styled.h3`
	font-size: 12px;
	cursor: pointer;
	background-color: #23282d;
	margin: 0 0 1px;
	vertical-align: middle;
	position: relative;
	padding: 10px 10px;
	color: #DDD;
	text-align: center;
	font-weight: 400;
	cursor: pointer;

	&.active {
		background-color: #32373c;
	}

	:hover {
		color: #FFF;
	}

	.icon {
		font-size: 20px;
		display: block;
		color: #DDD;
	}

	:hover .icon {
		color: #FFF;
	}
`

export const MenuItemBody = styled.div`
	display: none;

	&.active-body {
		display: block;
	}
`

export const CloseMenuRight = styled.div`
	color: ${props => props.color || "#EEE"};
	font-size: 24px;
	position: absolute;
	right: ${props => props.right || "5px"};
	top: ${props => props.top || "5px"};
	bottom: ${props => props.bottom || "auto"};
	cursor: pointer;
	width: 26px;
	height: 26px;
	cursor: pointer;
	z-index: 20;
`

// General

export const Label = styled.label`
  display: block;
  padding: 10px 0;
  font-size: 12px;

  input[type="radio"] {
  	border: 0;

  	:checked::before {
  		width: 0.6rem;
		height: 0.6rem;
	}
  }
`
export const Header = styled.h4`
  display: block;
  padding: 10px 0;
  font-size: 14px;
  margin: 0 0;
  position: relative;
`

export const Modal = styled.div`
  margin-top: 20px;
  display: ${props => props.display || "block"};
`
export const BackgroundSquare = styled.div`
  display: inline-block;
  background-color: ${props => `rgba(${props.bgcolor.r},${props.bgcolor.g},${props.bgcolor.b},${props.bgcolor.a})`};
  width: 20px;
  height: 20px;
  border-radius: 50%;
  bax-shadow: 0 0 3px #888;
  cursor: pointer;
`

// Object properties

export const ControlSelect = styled.div`
	display: inline-block;
  	position: relative;
  	padding-right: 2px;

  	select {
  		height: 36px;
  		line-height: 1;
  		margin-top: -9px;
  	}
`

export const IconButton = styled.button`
  border: 0;
  padding: 4px 6px 2px;
  background-color: ${props => props.backgroundColor || "#FFFFFF"};
  color: ${props => props.color || "#0e1318"};
  font-size: 1rem;
  border-radius: 5px;
  line-height: 1;
  position: relative;
  text-align: center;
  border: 1px solid ${props => props.backgroundColor || "#EEEEEE"};
  margin: 0 4px 0 0;
  height: 36px;
  width: 36px;

  &.active, :hover {
    color: #333;
    background-color: rgba(14,19,24,.07);
    cursor: pointer;
    cursor: hand;
  }

  .layer-square {
    position: absolute;
    top: 3px;
    left: 0;
    right: 0;
    height: 17px;
    width: 18px;
    margin: 0 auto;
    border: 2px solid #333;
    border-radius: 3px;
    font-size: 2px;
  }
`

export const PropModal = styled.div`
  display: ${props => props.display || "block"};
  width: ${props => props.width || "300px"};
  height: ${props => props.height || "auto"};
  padding: ${props => props.padding || "20px 20px"};
  margin: ${props => props.margin || "30px 0 0 0"};
  position: ${props => props.position || "absolute"};
  top: ${props => props.top || "0px"};
  left: ${props => props.left || "-150px"};
  z-index: 250;
`

export const AuxLineVertical = styled.span`
	position: absolute;
	display: none;
	top: 0;
	bottom: 0;
	left: ${props => props.left || "0"};
	right: ${props => props.right || "0"};
	z-index: 9999;
	width: 1px;
	height: 100%;
	background-color: #EF93FC;
`

export const AuxLineHorizontal = styled.span`
	position: absolute;
	display: none;
	top: ${props => props.top || "0"};
	bottom: ${props => props.bottom || "0"};
	left: 0;
	right: 0;
	z-index: 9999;
	width: 100%;
	height: 1px;
	background-color: #EF93FC;
`
export const ProVersion = styled.li`
  margin-top: 10px;
  font-weight: 700;
  line-height: 1.5;
  padding: 8px 6px;
  list-style: none;

  a {
    font-weight: 700;
  }
`

export const QRHeader = styled.div`
  margin-top: 10px;
  margin-bottom: 10px;
  font-weight: 700;
  line-height: 1.5;
  padding: 8px 6px;
  font-size: 12px;

  a {
    font-weight: 700;
  }
`
