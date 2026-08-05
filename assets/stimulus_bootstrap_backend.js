import { startStimulusApp } from '@symfony/stimulus-bridge'
import { namespaceContext, combineContexts, kebabCase, coreControllersContext, registerDropInControllers } from './stimulus_namespacing.js'

// Every module's own Backend Stimulus controllers, e.g.
// src/Modules/ContentBlocks/assets/Backend/webpack/js/controllers/foo_controller.js -> "content-blocks--foo"
const moduleControllersContext = namespaceContext(
  import.meta.webpackContext('@symfony/stimulus-bridge/lazy-controller-loader!../src/Modules', {
    recursive: true,
    regExp: /assets\/Backend\/webpack\/js\/controllers\/.+_controller\.[jt]sx?$/
  }),
  (key) => {
    const match = key.match(/^\.\/([^/]+)\/assets\/Backend\/webpack\/js\/controllers\/(.+)_controller\.[jt]sx?$/)
    if (!match) {
      return null
    }

    const [, moduleName, controllerName] = match
    return `./${kebabCase(moduleName)}--${controllerName}_controller.js`
  }
)

// Registers Stimulus controllers from controllers_backend.json, ./controllers, Core, and every
// module's own Backend controllers/ directory - so Core and each module can ship Stimulus controllers
// next to their other Backend assets instead of everything having to live centrally in this directory.
export const app = startStimulusApp(combineContexts(
  import.meta.webpackContext('@symfony/stimulus-bridge/lazy-controller-loader!./controllers', {
    recursive: true,
    regExp: /\.[jt]sx?$/
  }),
  coreControllersContext,
  moduleControllersContext
))
registerDropInControllers(app)
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
