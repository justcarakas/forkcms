const Encore = require('@symfony/webpack-encore')
const LiveReloadPlugin = require('webpack-livereload-plugin')
const dotenv = require('dotenv')
const env = dotenv.config()

if (env.error) {
  throw env.error
}
//
// const themePaths = {
//   build: `src/Frontend/Themes/${env.parsed.THEME}/Core/build`,
//   core: `src/Frontend/Themes/${env.parsed.THEME}/Core`
// }

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
    from: './src/Core/assets/images/Installer',
    to: './images/[path][name].[ext]'
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
    from: './src/Core/assets/images/Backend',
    to: './images/[path][name].[ext]'
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

// ===========================
//
// // START FRONTEND CORE SETUP
// //
//
// // Manually configure the runtime environment if not already configured yet by the "encore" command.
// // It's useful when you use tools that rely on webpack.config.js file.
// if (!Encore.isRuntimeEnvironmentConfigured()) {
//   // Set the runtime environment
//   Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
// }
//
// Encore
//   .setOutputPath('src/Frontend/Core/build/')
//   .setPublicPath('/src/Frontend/Core/build')
//   .setManifestKeyPrefix('src/Frontend/Core/build/')
//
//   .addEntry('frontend', './src/Frontend/Core/Js/Frontend.js')
//   .addStyleEntry('screen', './src/Frontend/Core/Layout/Sass/screen.scss')
//
//   .cleanupOutputBeforeBuild()
//
//   // will require an extra script tag for runtime.js
//   // but, you probably want this, unless you're building a single-page app
//   .enableSingleRuntimeChunk()
//   .enableSassLoader((options) => {}, {
//     resolveUrlLoader: false
//   })
//   .enablePostCssLoader()
//
//   .enableVueLoader()
//
//   .configureUrlLoader()
//   // enables @babel/preset-env polyfills
//   .enableSourceMaps(!Encore.isProduction())
//   // enables hashed filenames (e.g. app.abc123.css)
//   .enableVersioning(Encore.isProduction())
//
//   .copyFiles({
//     from: './src/Frontend/Core/Layout/Images',
//     to: './src/Frontend/Core/Build/Images/[path][name].[ext]'
//   })
//
//   .autoProvidejQuery()
//   .autoProvideVariables({
//     moment: 'moment'
//   })
//
//   // enables @babel/preset-env polyfills
//   .configureBabel(() => {}, {
//     useBuiltIns: 'usage',
//     corejs: 3
//   })
//
//   .configureWatchOptions((watchOptions) => {
//     // polling is useful when running Encore inside a Virtual Machine
//     watchOptions.poll = 250
//   })
//
//   .enableBuildNotifications(true, (options) => {
//     options.alwaysNotify = true
//   })
//
//   .addPlugin(new LiveReloadPlugin())
//
// // build the first configuration
// const frontendConfig = Encore.getWebpackConfig()
//
// // Set a unique name for the config (needed later!)
// frontendConfig.name = 'frontendConfig'
//
// // reset Encore to build the next config
// Encore.reset()
//
// //
// // END FRONTEND CORE SETUP
//
// // START FRONTEND THEME SETUP
// //
//
// // Manually configure the runtime environment if not already configured yet by the "encore" command.
// // It's useful when you use tools that rely on webpack.config.js file.
// if (!Encore.isRuntimeEnvironmentConfigured()) {
//   // Set the runtime environment
//   Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
// }
//
// Encore
//   .setOutputPath(`${themePaths.build}`)
//   .setPublicPath(`/${themePaths.build}/`)
//   .setManifestKeyPrefix(`${themePaths.build}`)
//
//   .addEntry('frontend', `./${themePaths.core}/Js/Index.js`)
//   .addStyleEntry('screen', `./${themePaths.core}/Layout/Sass/screen.scss`)
//
//   .cleanupOutputBeforeBuild()
//
//   // will require an extra script tag for runtime.js
//   // but, you probably want this, unless you're building a single-page app
//   .enableSingleRuntimeChunk()
//   .enableSassLoader((options) => {}, {
//     resolveUrlLoader: false
//   })
//   .enablePostCssLoader()
//
//   .enableVueLoader()
//
//   .configureUrlLoader()
//   // enables @babel/preset-env polyfills
//   .enableSourceMaps(!Encore.isProduction())
//   // enables hashed filenames (e.g. app.abc123.css)
//   .enableVersioning(Encore.isProduction())
//
//   .copyFiles({
//     from: `/${themePaths.core}/Layout/Images`,
//     to: `/${themePaths.build}/images/[path][name].[ext]`
//   })
//
//   .autoProvidejQuery()
//   .autoProvideVariables({
//     moment: 'moment'
//   })
//
//   // enables @babel/preset-env polyfills
//   .configureBabel(() => {}, {
//     useBuiltIns: 'usage',
//     corejs: 3
//   })
//
//   .configureWatchOptions((watchOptions) => {
//     // polling is useful when running Encore inside a Virtual Machine
//     watchOptions.poll = 250
//   })
//
//   .enableBuildNotifications(true, (options) => {
//     options.alwaysNotify = true
//   })
//
//   .addPlugin(new LiveReloadPlugin())
//
// // build the second configuration
// const frontendThemeConfig = Encore.getWebpackConfig()
//
// // Set a unique name for the config (needed later!)
// frontendThemeConfig.name = 'frontendThemeConfig'
//
// //
// // END FRONTEND THEME SETUP
//
// module.exports = [installerConfig, backendConfig, frontendConfig, frontendThemeConfig]
module.exports = [installerConfig, backendConfig]
