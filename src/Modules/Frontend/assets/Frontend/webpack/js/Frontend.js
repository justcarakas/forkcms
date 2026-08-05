// import plugins
import 'bootstrap'

// registers Stimulus controllers from Core and every module's Frontend controllers/ directory
import '../../../../../../../assets/stimulus_bootstrap_frontend.js'

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
