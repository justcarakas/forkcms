export class Step5 {
  constructor() {
    this.toggleDebugEmail()
  }

  toggleDebugEmail() {
    $('[data-fork-cms-role="different-debug-email"]').on('change', () => {
      if ($('[data-fork-cms-role="different-debug-email"]').is(':checked')) {
        $('[data-fork-cms-role="different-debug-email-wrapper"]').show()
        $('[data-fork-cms-role="debug-email"]').focus()
      } else {
        $('[data-fork-cms-role="different-debug-email-wrapper"]').hide()
      }
    }).trigger('change')
  }
}
