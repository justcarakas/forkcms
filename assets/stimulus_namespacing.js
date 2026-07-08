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

// Core's own Stimulus controllers: src/Core/assets/js/controllers/foo_controller.js -> "core--foo".
// Rooted at src/Core/assets/js (which always exists), not .../controllers itself, so the controllers/
// directory doesn't need to exist - if nothing lives there yet, this context is just empty. Shared by
// every application (Backend, Frontend, ...): Core controllers aren't tied to one side of the site.
export const coreControllersContext = namespaceContext(
  import.meta.webpackContext('@symfony/stimulus-bridge/lazy-controller-loader!../src/Core/assets/js', {
    recursive: true,
    regExp: /controllers\/.+_controller\.[jt]sx?$/,
  }),
  (key) => {
    const match = key.match(/^\.\/controllers\/(.+)_controller\.[jt]sx?$/)
    return match ? `./core--${match[1]}_controller.js` : null
  }
)
