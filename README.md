# DOCUMENTATION HFF INTERANET

# configuration du php.ini pour la production

- display_errors = Off
- display_startup_errors = Off
- log_errors = On
- error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

# configuration du php.ini pour la taille de ficher à uploder

- upload_max_filesize = 5M
- post_max_size =5M

# configuration du php.ini pour la durée de session par defaut

session.gc_maxlifetime = 3600


# webpack
## instalation fait 
npm init -y

npm install webpack webpack-cli --save-dev

npm install --save-dev mini-css-extract-plugin
npm install select2

npm install jquery --save

npm install sass sass-loader css-loader style-loader --save-dev
npm install @popperjs/core
npm install bootstrap

