import { Controller } from '@hotwired/stimulus'
import { getComponent } from '@symfony/ux-live-component'

// Pairs with the SlugGenerator LiveComponent, which renders no visible markup of its own: this
// projects the slug it generates into the real "slug" input and the on-page URL preview, mirroring
// what the old Meta.js/GenerateSlug AJAX call used to do by hand.
export default class extends Controller {
  static values = {
    baseFieldSelector: String,
    overwriteSelector: String,
    inputSelector: String,
    previewSelector: String,
    slug: String
  }

  connect () {
    this.baseField = document.querySelector(this.baseFieldSelectorValue)
    this.overwriteCheckbox = document.querySelector(this.overwriteSelectorValue)
    this.slugInput = document.querySelector(this.inputSelectorValue)
    this.preview = document.querySelector(this.previewSelectorValue)

    this.onBaseFieldInput = () => {
      if (!this.overwriteCheckbox?.checked) {
        this.generate(this.baseField.value)
      }
    }
    this.onSlugInput = () => {
      if (this.overwriteCheckbox?.checked) {
        this.generate(this.slugInput.value)
      }
    }

    this.baseField?.addEventListener('input', this.onBaseFieldInput)
    this.slugInput?.addEventListener('input', this.onSlugInput)
  }

  disconnect () {
    this.baseField?.removeEventListener('input', this.onBaseFieldInput)
    this.slugInput?.removeEventListener('input', this.onSlugInput)
  }

  async generate (value) {
    const component = await getComponent(this.element)
    component.action('generate', { value }, 400)
  }

  slugValueChanged (value) {
    if (this.slugInput) {
      this.slugInput.value = value
    }
    if (this.preview) {
      this.preview.textContent = value
    }
  }
}
