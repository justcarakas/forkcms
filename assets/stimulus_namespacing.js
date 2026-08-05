// Shared by every Stimulus bootstrap (Backend, Frontend, ...): re-keys a webpack context so
// stimulus-bridge's own identifier derivation (strip "_controller.ext", then "_" -> "-" and
// "/" -> "--") produces a clean, namespaced identifier instead of the full path.
export function namespaceContext (context, deriveKey) {
  const keyMap = new Map()
  context.keys().forEach((key) => {
    const namespacedKey = deriveKey(key)
    if (namespacedKey) {
      keyMap.set(namespacedKey, key)
    }
  })

  const namespacedContext = (key) => context(keyMap.get(key))
  namespacedContext.keys = () => Array.from(keyMap.keys())
  return namespacedContext
}

export function combineContexts (...contexts) {
  const combined = (key) => {
    const context = contexts.find((context) => context.keys().includes(key))
    if (!context) {
      throw new Error(`Cannot find Stimulus controller for "${key}".`)
    }

    return context(key)
  }
  combined.keys = () => contexts.flatMap((context) => context.keys())
  return combined
}

export const kebabCase = (value) => value.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase()

// Registers a module's plain, unbundled Stimulus controllers - dropped straight into its own
// assets/{application}/public/js/controllers/ directory, no webpack rebuild needed since Asset
// already serves files from a module's public/ folder as-is. PHP (DropInStimulusControllerFinder,
// wired into Header) discovers them per request and exposes {identifier: url} via the jsData global;
// this waits for that script to have run (it's rendered after the one that loads this file) before
// reading it.
export function registerDropInControllers (app) {
  const register = () => {
    const controllers = window.jsData?.Core?.dropInStimulusControllers ?? {}
    Object.entries(controllers).forEach(([identifier, url]) => {
      import(/* webpackIgnore: true */ url).then((module) => {
        app.register(identifier, module.default)
      })
    })
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', register)
  } else {
    register()
  }
}

// Core's own Stimulus controllers: src/Core/assets/js/controllers/foo_controller.js -> "core--foo".
// Rooted at src/Core/assets/js (which always exists), not .../controllers itself, so the controllers/
// directory doesn't need to exist - if nothing lives there yet, this context is just empty. Shared by
// every application (Backend, Frontend, ...): Core controllers aren't tied to one side of the site.
export const coreControllersContext = namespaceContext(
  import.meta.webpackContext('@symfony/stimulus-bridge/lazy-controller-loader!../src/Core/assets/js', {
    recursive: true,
    regExp: /controllers\/.+_controller\.[jt]sx?$/
  }),
  (key) => {
    const match = key.match(/^\.\/controllers\/(.+)_controller\.[jt]sx?$/)
    return match ? `./core--${match[1]}_controller.js` : null
  }
)
