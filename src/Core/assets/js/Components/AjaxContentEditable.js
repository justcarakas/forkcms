import {Messages} from "./Messages";

export class AjaxContentEditable {
  constructor () {
    $('[data-role=ajax-content-editable]').each((index, element) => {
      const $element = $(element)
      const url = $element.data('ajaxEditableUrl')
      const label = $element.data('ajaxEditableLabel') || window.backend.locale.msg('ClickToEdit')

      $element.html('<span data-role="ajax-content-editable-content">' + $element.html() + '</span>' +
        '<button class="btn btn-primary btn-sm float-end pe-0 invisible"  data-role="ajax-content-editable-tooltip">' +
        '<i class="fas fa-edit" aria-label=" ' + label + '"></i>' +
        '</button>')
      const $content = $element.find('[data-role=ajax-content-editable-content]')
      const $tooltip = $element.find('[data-role=ajax-content-editable-tooltip]')

      $element.parent().on('mouseover', () => {
        if ($content.attr('contenteditable') !== 'true') {
          $tooltip.removeClass('invisible')
        }
      })
      $element.parent().on('touchend', () => {
        if ($content.attr('contenteditable') !== 'true') {
          $tooltip.toggleClass('invisible')
        }
      })
      $element.parent().on('mouseout', () => {
        $tooltip.addClass('invisible')
      })
      let originalContent
      $tooltip.on('click', () => {
        $tooltip.addClass('invisible')
        $content.attr('contenteditable', true)
        originalContent = $content.text()
        $content.focus()
      })

      $content.on('blur', () => {
        $tooltip.addClass('invisible')
        $content.attr('contenteditable', false)
        if (originalContent !== $content.text()) {
          $.ajax(
            {
              url: url,
              data: {
                content: $content.text()
              },
              success: function (XMLHttpRequest) {
                Messages.add('success', XMLHttpRequest.message || window.backend.locale.msg('Edited'))
              },
              error: function (XMLHttpRequest) {
                Messages.add('danger', $.parseJSON(XMLHttpRequest.responseText).message)
              }
            }
          )
        }
      })
    })
  }
}
