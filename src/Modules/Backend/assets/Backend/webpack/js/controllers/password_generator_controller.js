import { Controller } from '@hotwired/stimulus'
import { StringUtil } from '../../../../../../../Core/assets/js/Components/StringUtil'

const generatePassword = require('generate-password-browser')

// Inserts a "generate password" button after its own field, filling that field and (if it looks
// like a RepeatedType's first half, i.e. its id ends in "_first") the matching "_second" field with
// the same freshly-generated password - both need the same value to pass validation.
export default class extends Controller {
  connect () {
    this.confirmField = document.getElementById(this.element.id.replace(/_first$/, '_second'))

    this.generateButton = document.createElement('button')
    this.generateButton.type = 'button'
    this.generateButton.className = 'btn btn-primary'
    this.generateButton.innerHTML = `<span>${StringUtil.ucfirst(window.backend.locale.lbl('Generate'))}</span>`
    this.onGenerateClick = () => this.generate()
    this.generateButton.addEventListener('click', this.onGenerateClick)
    this.element.after(this.generateButton)
  }

  disconnect () {
    this.generateButton.removeEventListener('click', this.onGenerateClick)
    this.generateButton.remove()
  }

  generate () {
    const password = generatePassword.generate({
      length: 10,
      numbers: true,
      symbols: true,
      lowercase: true,
      uppercase: true
    })

    ;[this.element, this.confirmField].filter(Boolean).forEach((field) => {
      field.type = 'text'
      field.value = password
      field.dispatchEvent(new Event('input', { bubbles: true }))
    })
  }
}
