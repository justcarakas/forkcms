// import plugins
import 'bootstrap'
import 'bootstrap-tagsinput/examples/lib/typeahead.js/dist/typeahead.bundle'
import 'bootstrap-tagsinput/dist/bootstrap-tagsinput.min'

// component imports
import { Components } from './_Components'

export class Frontend {
  initFrontend () {
    this.components = new Components()
    this.components.initComponents()
  }
}

$(window).on('load', () => {
  window.frontend = new Frontend()
  window.frontend.initFrontend()
})
