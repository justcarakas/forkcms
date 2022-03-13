// import plugins
import 'bootstrap'

// component imports
import { Components } from '../../../../Core/assets/js/Frontend/Components'
import { Modules } from '../../../../Core/assets/js/Frontend/Modules'

export class Index {
  initFrontend () {
    this.components = new Components()
    this.components.initComponents()
    this.modules = new Modules()
    this.modules.initModules()
  }
}

$(window).on('load', () => {
  window.frontend = new Index()
  window.frontend.initFrontend()
})
