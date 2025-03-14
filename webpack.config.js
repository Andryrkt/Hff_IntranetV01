const path = require("path");
const webpack = require("webpack");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");
const ImageMinimizerPlugin = require("image-minimizer-webpack-plugin");
const { PurgeCSSPlugin } = require("purgecss-webpack-plugin");
const glob = require("glob-all");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");

module.exports = {
  // Le point d'entrée de votre application front-end
  entry: {
    main: "./assets/js/main.js",
    accueil: "./assets/js/accueil.js",
    signin: "./assets/js/signin/signin.js",
    404: "./assets/js/404.js",
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
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        type: "asset/resource",
        generator: {
          filename: "fonts/[name][ext]",
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
      publicPath: "/Hffintranet/public/build/",
    }),
    new MiniCssExtractPlugin({ filename: "css/[name].[contenthash].css" }), // Crée un fichier CSS séparé
    new ImageMinimizerPlugin({
      minimizer: {
        implementation: ImageMinimizerPlugin.imageminMinify,
        options: {
          plugins: [
            ["mozjpeg", { quality: 75 }], // Compression JPEG
            ["pngquant", { quality: [0.65, 0.8] }], // Compression PNG
          ],
        },
      },
    }),
    new PurgeCSSPlugin({
      paths: glob.sync(["./Views/templates/**/*.twig", "./assets/js/**/*.js"], {
        nodir: true,
      }),
    }),
    new CleanWebpackPlugin(),
  ],

  // Mode de build : 'development' ou 'production'
  mode: "development",
  watch: true,
};
