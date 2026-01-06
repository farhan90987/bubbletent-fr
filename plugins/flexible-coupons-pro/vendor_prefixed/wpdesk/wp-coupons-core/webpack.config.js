const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');

module.exports = {
	mode: 'production',
	entry: {
		index: './assets-src/js/index.tsx',
	},
	output: {
		path: path.resolve(__dirname, 'assets/js'),
		filename: '[name].js',
	},
	module: {
		rules: [
			{
				test: /\.tsx?$/,
				use: 'ts-loader',
				exclude: /node_modules/,
			},
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader', 'postcss-loader'],
			},
			{
				test: /\.(png|svg|jpg|jpeg|gif)$/i,
				type: 'asset/resource',
			},
		],
	},
	resolve: {
		extensions: ['.tsx', '.ts', '.js'],
		modules: [path.resolve(__dirname, 'node_modules')],
		alias: {
			'@': path.resolve(__dirname, 'assets-src/js'),
		},
	},
	plugins: [
		new HtmlWebpackPlugin({
			template: './assets-src/js/index.html',
		}),
	],
	devServer: {
		static: path.join(__dirname, 'assets/js'),
		compress: true,
		port: 9000,
		open: true,
	},
	devtool: 'inline-source-map',
};
