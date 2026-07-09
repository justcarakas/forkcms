import { Controller } from '@hotwired/stimulus'
import { Messages } from '../Components/Messages'

// Makes its own element inline-editable: hover/focus reveals an edit button, clicking it focuses
// the element for editing, and blurring it saves the new text via a plain POST if it changed.
export default class extends Controller {
  static values = {
    url: String,
    label: String
  }

  connect () {
    this.editButton = document.createElement('button')
    this.editButton.type = 'button'
    this.editButton.className = 'btn btn-primary btn-sm float-end pe-0 invisible'
    this.editButton.innerHTML = `<i class="fas fa-edit" aria-label=" ${this.labelValue || window.backend.locale.msg('ClickToEdit')}"></i>`
    this.element.after(this.editButton)

    this.onEditButtonClick = () => this.startEditing()
    this.onBlur = () => this.stopEditing()
    this.onParentShow = () => {
      if (this.element.getAttribute('contenteditable') !== 'true') {
        this.editButton.classList.remove('invisible')
      }
    }
    this.onParentHide = () => this.editButton.classList.add('invisible')

    this.editButton.addEventListener('click', this.onEditButtonClick)
    this.element.addEventListener('blur', this.onBlur)

    this.parentElement = this.element.parentElement
    this.parentElement.addEventListener('focus', this.onParentShow)
    this.parentElement.addEventListener('mouseover', this.onParentShow)
    this.parentElement.addEventListener('touchend', this.onParentShow)
    this.parentElement.addEventListener('mouseout', this.onParentHide)
    this.parentElement.addEventListener('blur', this.onParentHide)
  }

  disconnect () {
    this.editButton.removeEventListener('click', this.onEditButtonClick)
    this.element.removeEventListener('blur', this.onBlur)
    this.parentElement.removeEventListener('focus', this.onParentShow)
    this.parentElement.removeEventListener('mouseover', this.onParentShow)
    this.parentElement.removeEventListener('touchend', this.onParentShow)
    this.parentElement.removeEventListener('mouseout', this.onParentHide)
    this.parentElement.removeEventListener('blur', this.onParentHide)
    this.editButton.remove()
  }

  startEditing () {
    this.editButton.classList.add('invisible')
    this.element.setAttribute('contenteditable', 'true')
    this.originalContent = this.element.textContent
    this.element.focus()
  }

  async stopEditing () {
    this.editButton.classList.add('invisible')
    this.element.setAttribute('contenteditable', 'false')

    const content = this.element.textContent
    if (content === this.originalContent) {
      return
    }

    try {
      const response = await fetch(this.urlValue, {
        method: 'POST',
        body: new URLSearchParams({ content })
      })
      const data = await response.json()
      if (!response.ok) {
        throw new Error(data.message)
      }
      Messages.add('success', data.message || window.backend.locale.msg('Edited'))
    } catch (error) {
      Messages.add('danger', error.message)
    }
  }
}
