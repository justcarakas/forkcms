import { Controller } from '@hotwired/stimulus'

/**
 * Drives a data grid's filter live: as-you-type (debounced), Enter, or changing which field to
 * filter on all reload the enclosing <turbo-frame> by updating its `src`, the same native
 * mechanism the grid's own sort/pagination links already use. Frame reloads don't touch browser
 * history, so this never spams the back button the way pushing a new visit per keystroke would.
 */
export default class extends Controller {
  static targets = ['field', 'value']
  static values = {
    action: String,
    fieldName: String,
    valueName: String,
    debounce: { type: Number, default: 300 }
  }

  connect () {
    this.timeout = null
  }

  disconnect () {
    window.clearTimeout(this.timeout)
  }

  onInput () {
    window.clearTimeout(this.timeout)
    this.timeout = window.setTimeout(() => this.submit(), this.debounceValue)
  }

  onKeydown (event) {
    if (event.key !== 'Enter') {
      return
    }

    event.preventDefault()
    window.clearTimeout(this.timeout)
    this.submit()
  }

  submit () {
    const frame = this.element.closest('turbo-frame')
    if (!frame) {
      return
    }

    const params = new URLSearchParams()
    params.set(this.fieldNameValue, this.fieldTarget.value)
    params.set(this.valueNameValue, this.valueTarget.value)

    const separator = this.actionValue.includes('?') ? '&' : '?'
    frame.src = this.actionValue + separator + params.toString()
  }
}
