const Encore = require('@symfony/webpack-encore')
const LiveReloadPlugin = require('webpack-livereload-plugin')
const { execSync } = require('child_process')
const fs = require('fs');

const extensionConfig = JSON.parse(execSync("bin/console forkcms:extensions:webpack-config -vvv", (error, jsonConfig, stderr) => {
  if (error) {
    console.log(`error: ${error.message}`)
    return
  }
  if (stderr) {
    console.log(`stderr: ${stderr}`)
    return
  }
}).toString())

const THEME_PATH = {'output':'public/assets/themes', 'public':'/assets/themes'}
const MODULE_PATH = {'output':'public/assets/modules', 'public':'/assets/modules'}
const EXPORTS = []

// START INSTALLER SETUP
//

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  // Set the runtime environment
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
  .setOutputPath('public/assets/installer')
  .setPublicPath('/assets/installer')

  .addEntry('installer', './src/Core/assets/Installer/webpack/js/Installer.js')
  .addStyleEntry('screen', './src/Core/assets/Installer/webpack/scss/screen.scss')

  .copyFiles({
    from: './src/Core/assets/Installer/public',
    to: './[path][name].[ext]'
  })

  .cleanupOutputBeforeBuild()

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
  .enableSingleRuntimeChunk()

// build the first configuration
const installerConfig = Encore.getWebpackConfig()

// Set a unique name for the config (needed later!)
installerConfig.name = 'installerConfig'

EXPORTS.push(installerConfig)

// reset Encore to build the next config
Encore.reset()

//
// END INSTALLER SETUP
//
// ===========================
//
// THEMES SETUP
//
for (const THEME_CONFIG of extensionConfig.themes) {
  // Manually configure the runtime environment if not already configured yet by the "encore" command.
  // It's useful when you use tools that rely on webpack.config.js file.
  if (!Encore.isRuntimeEnvironmentConfigured()) {
    // Set the runtime environment
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
  }

  Encore
    .setOutputPath(`${THEME_PATH.output}`)
    .setPublicPath(`${THEME_PATH.public}/`)
    .configureUrlLoader()
    // enables @babel/preset-env polyfills
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .configureWatchOptions((watchOptions) => {
      // polling is useful when running Encore inside a Virtual Machine
      watchOptions.poll = 250
    })
    .enableBuildNotifications(true, (options) => {
      options.alwaysNotify = true
    })
    .addPlugin(new LiveReloadPlugin())
    .disableSingleRuntimeChunk() // we will never load more than one team
    .enableVueLoader()
    .autoProvidejQuery()
    .autoProvideVariables({
      moment: 'moment'
    })
    // enables @babel/preset-env polyfills
    .configureBabel(() => {
    }, {
      useBuiltIns: 'usage',
      corejs: 3
    })
    .enableSassLoader((options) => {
    }, {
      resolveUrlLoader: true
    })
    .enablePostCssLoader()

  const COPY_FILES_CONFIGS = []
  for (const THEME_CONFIG of extensionConfig.themes) {
    const THEME_PUBLIC_DIR = `${THEME_CONFIG.path}/public`
    const THEME_JS_PATH = `${THEME_CONFIG.path}/webpack/js/${THEME_CONFIG.name}.js`
    const THEME_SCSS_DIR = `${THEME_CONFIG.path}/webpack/scss`
    if (fs.existsSync(THEME_PUBLIC_DIR)) {
      COPY_FILES_CONFIGS.push({
        from: THEME_PUBLIC_DIR,
        to: `./${THEME_CONFIG.name}/[path][name].[ext]`
      })
    }
    if (fs.existsSync(THEME_JS_PATH)) {
      Encore.addEntry(`${THEME_CONFIG.name}/${THEME_CONFIG.name}`, THEME_JS_PATH)
    }
    if (fs.existsSync(`${THEME_SCSS_DIR}/screen.scss`)) {
      Encore.addStyleEntry('screen', `${THEME_SCSS_DIR}/screen.scss`)
    }
    if (fs.existsSync(`${THEME_SCSS_DIR}/print.scss`)) {
      Encore.addStyleEntry('print', `${THEME_SCSS_DIR}/print.scss`)
    }
  }
  Encore.copyFiles(COPY_FILES_CONFIGS)

  // build the second configuration
  const webpackConfig = Encore.getWebpackConfig()

  // Set a unique name for the config (needed later!)
  webpackConfig.name = 'ThemeConfig'

  EXPORTS.push(webpackConfig)
  Encore.reset()
}

//
// END THEMES SETUP
//
// ===========================
//
// START MODULES SETUP
//
for (const APPLICATION of ['Frontend', 'Backend']) {
  // Manually configure the runtime environment if not already configured yet by the "encore" command.
  // It's useful when you use tools that rely on webpack.config.js file.
  if (!Encore.isRuntimeEnvironmentConfigured()) {
    // Set the runtime environment
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
  }

  Encore
    .setOutputPath(`${MODULE_PATH.output}/${APPLICATION}`)
    .setPublicPath(`${MODULE_PATH.public}/${APPLICATION}/`)
    .configureUrlLoader()
    // enables @babel/preset-env polyfills
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .configureWatchOptions((watchOptions) => {
      // polling is useful when running Encore inside a Virtual Machine
      watchOptions.poll = 250
    })
    .enableBuildNotifications(true, (options) => {
      options.alwaysNotify = true
    })
    .addPlugin(new LiveReloadPlugin())
    .enableSingleRuntimeChunk()
    .enableVueLoader()
    .autoProvidejQuery()
    .autoProvideVariables({
      moment: 'moment'
    })
    // enables @babel/preset-env polyfills
    .configureBabel(() => {
    }, {
      useBuiltIns: 'usage',
      corejs: 3
    })
    .enableSassLoader((options) => {
    }, {
      resolveUrlLoader: true
    })
    .enablePostCssLoader()

  const COPY_FILES_CONFIGS = []
  for (const MODULE_CONFIG of extensionConfig.modules) {
    const MODULE_PUBLIC_DIR = `${MODULE_CONFIG.path}/${APPLICATION}/public`
    const MODULE_APPLICATION_JS_PATH = `${MODULE_CONFIG.path}/${APPLICATION}/webpack/js/${MODULE_CONFIG.name}.js`
    const MODULE_APPLICATION_SCSS_DIR = `${MODULE_CONFIG.path}/${APPLICATION}/webpack/scss`
    if (fs.existsSync(MODULE_PUBLIC_DIR)) {
      COPY_FILES_CONFIGS.push({
        from: MODULE_PUBLIC_DIR,
        to: `./${MODULE_CONFIG.name}/[path][name].[ext]`
      })
    }
    if (fs.existsSync(MODULE_APPLICATION_JS_PATH)) {
      Encore.addEntry(`${MODULE_CONFIG.name}/${MODULE_CONFIG.name}`, MODULE_APPLICATION_JS_PATH)
    }
    if (fs.existsSync(`${MODULE_APPLICATION_SCSS_DIR}/screen.scss`)) {
      Encore.addStyleEntry('screen', `${MODULE_APPLICATION_SCSS_DIR}/screen.scss`)
    }
    if (fs.existsSync(`${MODULE_APPLICATION_SCSS_DIR}/print.scss`)) {
      Encore.addStyleEntry('print', `${MODULE_APPLICATION_SCSS_DIR}/print.scss`)
    }
  }
  Encore.copyFiles(COPY_FILES_CONFIGS)

  // build the second configuration
  const webpackConfig = Encore.getWebpackConfig()

  // Set a unique name for the config (needed later!)
  webpackConfig.name = APPLICATION + 'Config'

  EXPORTS.push(webpackConfig)
  Encore.reset()
}

//
// END MODULES SETUP

module.exports = EXPORTS
