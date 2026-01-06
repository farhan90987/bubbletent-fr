import React from 'react';
import ReactDOM from 'react-dom';
import Editor from "./Editor";
import "./style.css";

document.addEventListener('DOMContentLoaded', function () {
	ReactDOM.render( <React.StrictMode><Editor/></React.StrictMode>, document.getElementById('wpdesk-canva-root') );
});

