const path = require("path");
const webpack = require("webpack");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");

module.exports = {
  // Le point d'entrée de votre application front-end
  entry: {
    main: "./assets/js/main.js",
    accueil: "./assets/js/accueil.js",
  },

  // La sortie du bundle généré par Webpack
  output: {
    filename: "js/[name].[contenthash].bundle.js",
    path: path.resolve(__dirname, "public/build"),
  },
  // Configuration des loaders pour traiter CSS ou d'autres fichiers
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, "css-loader"],
      },
      {
        test: /\.(scss|sass)$/,
        use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
      },
      // Règle pour les images
      {
        test: /\.(png|jpe?g|gif|svg)$/i,
        type: "asset/resource", // Copie le fichier dans le dossier de sortie et renvoie l'URL
        generator: {
          filename: "images/[name].[contenthash][ext]", // Place les images dans le dossier "images" avec un nom unique
        },
      },
    ],
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
      "window.jQuery": "jquery",
    }),
    new WebpackManifestPlugin({
      fileName: "manifest.json",
      publicPath: "/build/",
    }),
    new MiniCssExtractPlugin({ filename: "css/[name].[contenthash].css" }), // Crée un fichier CSS séparé
  ],

  // Mode de build : 'development' ou 'production'
  mode: "development",
  watch: true,
};
