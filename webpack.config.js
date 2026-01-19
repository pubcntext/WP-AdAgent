const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
    entry: {
        // Frontend scripts
        'pubcontext-init': './assets/js/pubcontext-init.js',
        'context-extractor': './assets/js/context-extractor.js',

        // Admin scripts
        'admin/admin': './assets/js/admin/admin.js',
        'admin/settings': './assets/js/admin/settings.js',
        'admin/bidders': './assets/js/admin/bidders.js',
        'admin/placements': './assets/js/admin/placements.js',

        // Block scripts
        'blocks/pubcontext-placement': './assets/js/blocks/pubcontext-placement.js',

        // CSS
        'css/admin': './assets/css/admin.css',
        'css/blocks': './assets/css/blocks.css',
        'css/placement': './assets/css/placement.css',
    },
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: '[name].js',
        clean: true,
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@wordpress/babel-preset-default'],
                    },
                },
            },
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: ['postcss-preset-env'],
                            },
                        },
                    },
                ],
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css',
        }),
    ],
    optimization: {
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    format: {
                        comments: false,
                    },
                },
                extractComments: false,
            }),
            new CssMinimizerPlugin(),
        ],
        minimize: isProduction,
    },
    devtool: isProduction ? false : 'source-map',
    externals: {
        jquery: 'jQuery',
        '@wordpress/blocks': ['wp', 'blocks'],
        '@wordpress/element': ['wp', 'element'],
        '@wordpress/components': ['wp', 'components'],
        '@wordpress/i18n': ['wp', 'i18n'],
        '@wordpress/block-editor': ['wp', 'blockEditor'],
        '@wordpress/data': ['wp', 'data'],
        '@wordpress/api-fetch': ['wp', 'apiFetch'],
    },
    resolve: {
        extensions: ['.js', '.json'],
    },
};
