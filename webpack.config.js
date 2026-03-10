const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';

  return {
    entry: {
      app: [
        './Views/js/scripts.js',
        './Views/css/styles.css',
        './Views/css/style.css',
        './Views/css/styleBreadCrumb.css',
        './Views/css/documentation.css'
      ],
      daListCdeFrn: [
        './Views/css/list.css',
        './Views/css/da/list.css',
        './Views/css/da/list-cde-frn.css',
        './Views/css/da/style.css',
        './Views/js/utils/positionSticky.js',
        './Views/js/da/listeCdeFrn/listCdefrn.js',
        './Views/js/da/listeCdeFrn/daNonDispo.js'
      ],
      daList: [
        './Views/css/new.css',
        './Views/css/list.css',
        './Views/css/da/list.css',
        './Views/css/da/style.css',
        './Views/js/utils/positionSticky.js',
        './Views/js/da/listeDa/list.js'
      ],
      listConge: [
        './Views/js/ddc/listConge.js',
        './Views/css/list.css',
        './Views/css/ddc/listeConge.css'
      ]
    },
    output: {
      path: path.resolve(__dirname, 'Public/build'),
      filename: isProduction ? '[name].[contenthash].js' : '[name].js',
      clean: true, // Nettoie le dossier build avant chaque compilation
    },
    optimization: {
      minimize: isProduction,
      minimizer: [
        new TerserPlugin(),
        new CssMinimizerPlugin(),
      ],
    },
    module: {
      rules: [
        {
          test: /\.css$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
          ],
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: isProduction ? '[name].[contenthash].css' : '[name].css',
      }),
      new WebpackManifestPlugin({
        fileName: 'manifest.json',
        publicPath: 'Public/build/',
      }),
    ],
  };
};
