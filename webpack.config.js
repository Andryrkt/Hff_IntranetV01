const Encore = require("@symfony/webpack-encore");
const FilterWarningsPlugin = require("webpack-filter-warnings-plugin");

Encore.setOutputPath("public/build/")
  .setPublicPath("/build")
  .addEntry("app", "./assets/app.js")
  .addStyleEntry("global", "./assets/css/global.scss")

  .copyFiles({
    from: "./assets/images",
    to: "images/[path][name].[ext]", // Les images seront copiées avec un hash dans leur nom
  })
  .configureImageRule({
    type: "asset",
    maxSize: 4 * 1024, // 4 KB
  })
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabel(() => {}, {
    useBuiltIns: "usage",
    corejs: 3,
  })
  .enableSassLoader((options) => {
    options.sassOptions = {
      outputStyle: "expanded",
    };
  })
  .enablePostCssLoader()
  .addPlugin(
    new FilterWarningsPlugin({
      exclude: /export .* was not found in/,
    })
  );

module.exports = Encore.getWebpackConfig();
