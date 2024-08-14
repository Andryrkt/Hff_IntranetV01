const Encore = require("@symfony/webpack-encore");

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
  .enablePostCssLoader();

module.exports = Encore.getWebpackConfig();
