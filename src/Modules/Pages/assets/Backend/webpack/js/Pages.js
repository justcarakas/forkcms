import 'jstree/dist/jstree'

import { Tree } from './Components/Tree'
import { Move } from './Components/Move'

class Pages {
  constructor () {
    // tree components
    this.tree = new Tree()

    if ($('[data-role="move-page-toggle"]').length > 0) {
      this.move = new Move()
    }
  }
}

$(function () {
  new Pages()
})
