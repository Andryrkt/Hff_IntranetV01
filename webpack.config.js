module.exports = {
    // Point d'entrée
    entry: './Views/js/app.js',
    // Sortie
    output: {
      path: __dirname + '/Views/dist',
      filename: 'bundle.js'
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
  