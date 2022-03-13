const Encore = require('@symfony/webpack-encore')
const LiveReloadPlugin = require('webpack-livereload-plugin')
const { exec } = require("child_process");
let tmpTheme = 'Fork';
exec("bin/console forkcms:extensions:active-theme -vvv", (error, stdout, stderr) => {
  if (error) {
    console.log(`error: ${error.message}`);
    return;
  }
  if (stderr) {
    console.log(`stderr: ${stderr}`);
    return;
  }

  tmpTheme = stdout.replace(/\n/g, '');
});
const ACTIVE_THEME = tmpTheme;

const themePaths = {
  build: `public/build/theme`,
  source: `src/Themes/${ACTIVE_THEME}/assets`
}

// START INSTALLER SETUP
//

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  // Set the runtime environment
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
  .setOutputPath('public/build/installer')
  .setPublicPath('/build/installer')
  .setManifestKeyPrefix('public/build/installer')

  .addEntry('installer', './src/Core/assets/js/Installer/Installer.js')
  .addStyleEntry('screen', './src/Core/assets/sass/Installer/screen.scss')

  .copyFiles({
    from: './src/Core/assets/public/Installer',
    to: './[path][name].[ext]'
  })

  .cleanupOutputBeforeBuild()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()
  .enableSassLoader((options) => {}, {
    resolveUrlLoader: true
  })
  .enablePostCssLoader()
  // enables @babel/preset-env polyfills
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .autoProvidejQuery()
  .autoProvideVariables({
    moment: 'moment'
  })

  // enables @babel/preset-env polyfills
  .configureBabel(() => {}, {
    useBuiltIns: 'usage',
    corejs: 3
  })

  .configureWatchOptions((watchOptions) => {
    watchOptions.poll = 250
  })

  .enableBuildNotifications(true, (options) => {
    options.alwaysNotify = true
  })

  .addPlugin(new LiveReloadPlugin())

// build the first configuration
const installerConfig = Encore.getWebpackConfig()

// Set a unique name for the config (needed later!)
installerConfig.name = 'installerConfig'

// reset Encore to build the next config
Encore.reset()

//
// END INSTALLER SETUP
//
// ===========================
//
// START BACKEND
//

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  // Set the runtime environment
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
  .setOutputPath('public/build/backend')
  .setPublicPath('/build/backend')
  .setManifestKeyPrefix('public/build/backend')

  .addEntry('backend', './src/Core/assets/js/Backend/Backend.js')
  .addStyleEntry('screen', './src/Core/assets/sass/Backend/screen.scss')

  .cleanupOutputBeforeBuild()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()
  .enableSassLoader((options) => {}, {
    resolveUrlLoader: true
  })
  .enablePostCssLoader()
  // enables @babel/preset-env polyfills
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .copyFiles({
    from: './src/Core/assets/public/Backend',
    to: './[path][name].[ext]'
  })

  .autoProvidejQuery()
  .autoProvideVariables({
    moment: 'moment'
  })

  // enables @babel/preset-env polyfills
  .configureBabel(() => {}, {
    useBuiltIns: 'usage',
    corejs: 3
  })

  .configureWatchOptions((watchOptions) => {
    watchOptions.poll = 250
  })

  .enableBuildNotifications(true, (options) => {
    options.alwaysNotify = true
  })

  .addPlugin(new LiveReloadPlugin())

// build the first configuration
const backendConfig = Encore.getWebpackConfig()

// Set a unique name for the config (needed later!)
backendConfig.name = 'backendConfig'

// reset Encore to build the next config
Encore.reset()

//
// END BACKEND SETUP
//
// ===========================
//
// START FRONTEND CORE SETUP
//

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  // Set the runtime environment
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
  .setOutputPath('public/build/frontend')
  .setPublicPath('/build/frontend')
  .setManifestKeyPrefix('public/build/frontend')

  .addEntry('frontend', './src/Core/assets/js/Frontend/Frontend.js')
  .addStyleEntry('screen', './src/Core/assets/sass/Frontend/screen.scss')

  .cleanupOutputBeforeBuild()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()
  .enableSassLoader((options) => {}, {
    resolveUrlLoader: false
  })
  .enablePostCssLoader()

  .enableVueLoader()

  .configureUrlLoader()
  // enables @babel/preset-env polyfills
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .copyFiles({
    from: './src/Core/assets/public/Frontend',
    to: './[path][name].[ext]'
  })

  .autoProvidejQuery()
  .autoProvideVariables({
    moment: 'moment'
  })

  // enables @babel/preset-env polyfills
  .configureBabel(() => {}, {
    useBuiltIns: 'usage',
    corejs: 3
  })

  .configureWatchOptions((watchOptions) => {
    // polling is useful when running Encore inside a Virtual Machine
    watchOptions.poll = 250
  })

  .enableBuildNotifications(true, (options) => {
    options.alwaysNotify = true
  })

  .addPlugin(new LiveReloadPlugin())

// build the first configuration
const frontendConfig = Encore.getWebpackConfig()

// Set a unique name for the config (needed later!)
frontendConfig.name = 'frontendConfig'

// reset Encore to build the next config
Encore.reset()

//
// END FRONTEND CORE SETUP

// START FRONTEND THEME SETUP
//

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  // Set the runtime environment
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
  .setOutputPath(`${themePaths.build}`)
  .setPublicPath(`/${themePaths.build}/`)
  .setManifestKeyPrefix(`${themePaths.build}`)

  .addEntry('frontend', `./${themePaths.source}/js/Index.js`)
  .addStyleEntry('screen', `./${themePaths.source}/sass/screen.scss`)

  .cleanupOutputBeforeBuild()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()
  .enableSassLoader((options) => {}, {
    resolveUrlLoader: false
  })
  .enablePostCssLoader()

  .enableVueLoader()

  .configureUrlLoader()
  // enables @babel/preset-env polyfills
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .copyFiles({
    from: `./${themePaths.source}/public`,
    to: `./[path][name].[ext]`
  })

  .autoProvidejQuery()
  .autoProvideVariables({
    moment: 'moment'
  })

  // enables @babel/preset-env polyfills
  .configureBabel(() => {}, {
    useBuiltIns: 'usage',
    corejs: 3
  })

  .configureWatchOptions((watchOptions) => {
    // polling is useful when running Encore inside a Virtual Machine
    watchOptions.poll = 250
  })

  .enableBuildNotifications(true, (options) => {
    options.alwaysNotify = true
  })

  .addPlugin(new LiveReloadPlugin())

// build the second configuration
const frontendThemeConfig = Encore.getWebpackConfig()

// Set a unique name for the config (needed later!)
frontendThemeConfig.name = 'frontendThemeConfig'

//
// END FRONTEND THEME SETUP

module.exports = [installerConfig, backendConfig, frontendConfig, frontendThemeConfig]
