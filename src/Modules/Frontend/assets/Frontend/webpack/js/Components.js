import { Ajax } from '../../../../../../Core/assets/js/Components/Ajax'
import { Modal } from '../../../../../../Core/assets/js/Components/Modal'
import { Cookiebar } from './Components/Cookiebar'
import { Locale } from '../../../../../../Core/assets/js/Components/Locale'
import { Controls } from '../../../../../../Core/assets/js/Components/Controls'
import { Facebook } from './Components/Facebook'
import { Forms } from './Components/Forms'
import { Statistics } from './Components/Statistics'
import { Twitter } from './Components/Twitter'
import { ConsentDialog } from './Components/ConsentDialog'
import Vue from 'vue/'
import VEmbed from './Vue-components/VEmbed'
import VShareButtons from './Vue-components/VShareButtons'

export class Components {
  initComponents () {
    this.locale = new Locale()
    this.ajax = new Ajax(this.locale)
    this.modal = new Modal()
    this.cookiebar = new Cookiebar()
    this.controls = new Controls(this.locale)
    this.facebook = new Facebook()
    this.forms = new Forms()
    this.statistics = new Statistics()
    this.twitter = new Twitter()
    this.consentDialog = new ConsentDialog()

    if ($('[data-v-embed]').length) {
      window.vembed = new Vue({
        el: '[data-v-embed]',
        components: {VEmbed}
      })
    }

    if ($('[data-v-share-buttons]').length) {
      window.vsharebutton = new Vue({
        el: '[data-v-share-buttons]',
        components: {VShareButtons}
      })
    }
  }
}
