const webpack = require("webpack");
var path = require('path');

module.exports = {
    entry: "./components/index.js",
    output: {
        path: __dirname,
        filename: "./src/assets/js/wpdesk-canva-editor.js"
    },
    module: {
        loaders: [
            {
                test: /\.(js|mjs|jsx)$/,
                loader: "babel-loader",
                exclude: /node_modules/,
                options: {
                    presets: [["env", "react"]],
                    plugins: ["transform-class-properties"]
                }
            },
            {
                test: /\.css$/,
                use: [
                    'style-loader',
                    {
                        loader: 'css-loader',
                    },
                ]
            },
        ]
    },
    plugins: [
        new webpack.optimize.UglifyJsPlugin({
            include: /\.min\.js$/,
            minimize: true
        })
    ]};
