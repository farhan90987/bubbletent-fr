import {lang} from "../Helpers";

export const ObjectData = {
    id: null,
    title: '',
    sizes: [],
    width: 200,
    realWidth: null,
    height: 200,
    realHeight: null,
    top: 350,
    left: 30,
    rotate: 0,
    url: '',
    active: 0,
    tag: '',
    text: '',
    scalable: 1,
    clone: 1,
    rotatable: 1,
    rotateAngle: 0,
    fontSize: 16,
    fontFamily: 'Open Sans',
    color: {
        r: 0,
        g: 0,
        b: 0,
        a: 1000,
    },
    fontWeight: 400,
    fontStyle: 'normal',
    backgroundColor: 'transparent',
    textAlign: 'left',
    zIndex: 'auto',
    lineHeight: 'auto',
    opacity: '1'
}

export const ShortcodeData = {
    top: 20,
    left: 20,
    text: '',
    width: 200,
    height: 20,
    type: 'text',
    tag: 'p',
}
export const TextData = {
    type: 'text',
    name: 'Text',
    text: 'Text',
    tag: 'p',
    height: 50,
    width: 200,
    top: 150,
    fontWeight: 400,
    fontStyle: 'normal',
    fontSize: 16,
    left: 20,
}

export const QRCodeData = {
    type: 'qr_code',
    width: 150,
    height: 150,
    top: 40,
    left: 40
}

export const FontsData = [
    'Lato',
    'Montserrat',
    'Open Sans',
    'Open Sans Condensed',
    'Nunito',
    'Raleway',
    'Roboto',
    'Rubik',
    'Quicksand',
    'Titillium Web'
];
