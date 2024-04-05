module.exports = {
    // Point d'entrée
    entry: {
      badm: './Views/js/badm.js',
      dom: './Views/js/dom.js'
    },
    // Sortie
    output: {
      path: __dirname + '/Views/dist',
      filename: '[name].bundle.js'
    },
    // Règles de module
    module: {
      rules: [
        {
          test: /\.js$/, // Appliquer le loader aux fichiers .js
          exclude: /node_modules/, // Exclure le dossier node_modules
          use: {
            loader: 'babel-loader', // Utiliser le loader Babel
            options: {
              presets: ['@babel/preset-env'] // Preset pour compiler ES6+
            }
          }
        }
      ]
    },
    // Mode
    mode: 'development'
  };
  