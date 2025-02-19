const path = require("path");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");

module.exports = {
  mode: "development", // ou production pour la mise en prod
  entry: "./assets/js/app.js",
  output: {
    filename: "app.js",
    path: path.resolve(__dirname, "Public/build"),
    publicPath: "/Hffintranet/Public/build/",
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: ["style-loader", "css-loader"],
      },
      {
        test: /\.(png|jpg|gif|svg|woff2?|eot|ttf|otf)$/i,
        type: "asset/resource",
      },
    ],
  },
  plugins: [new CleanWebpackPlugin()],
};
