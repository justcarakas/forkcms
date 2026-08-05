import { startStimulusApp } from '@symfony/stimulus-bridge'
import { namespaceContext, combineContexts, kebabCase, coreControllersContext, registerDropInControllers } from './stimulus_namespacing.js'

// Every module's own Frontend Stimulus controllers, e.g.
// src/Modules/Blog/assets/Frontend/webpack/js/controllers/foo_controller.js -> "blog--foo"
const moduleControllersContext = namespaceContext(
  import.meta.webpackContext('@symfony/stimulus-bridge/lazy-controller-loader!../src/Modules', {
    recursive: true,
    regExp: /assets\/Frontend\/webpack\/js\/controllers\/.+_controller\.[jt]sx?$/
  }),
  (key) => {
    const match = key.match(/^\.\/([^/]+)\/assets\/Frontend\/webpack\/js\/controllers\/(.+)_controller\.[jt]sx?$/)
    if (!match) {
      return null
    }

    const [, moduleName, controllerName] = match
    return `./${kebabCase(moduleName)}--${controllerName}_controller.js`
  }
)

// Registers Stimulus controllers from Core and every module's own Frontend controllers/ directory -
// a separate Application instance from the Backend one (see stimulus_bootstrap_backend.js), since a
// page is always either a Backend admin page or a public Frontend page, never both.
export const app = startStimulusApp(combineContexts(
  coreControllersContext,
  moduleControllersContext
))
registerDropInControllers(app)
