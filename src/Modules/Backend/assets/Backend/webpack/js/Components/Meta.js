import { EventUtil } from '../../../../../../../Core/assets/js/Components/EventUtil'

export class Meta {
  static doMeta (options, element) {
    // define defaults
    const defaults = {
      pageTitleSelector: '#pageTitle',
      pageTitleOverwriteSelector: '#pageTitleOverwrite',
      navigationTitleSelector: '#navigationTitle',
      navigationTitleOverwriteSelector: '#navigationTitleOverwrite',
      metaDescriptionSelector: '#metaDescription',
      metaDescriptionOverwriteSelector: '#metaDescriptionOverwrite',
      metaKeywordsSelector: '#metaKeywords',
      metaKeywordsOverwriteSelector: '#metaKeywordsOverwrite',
      baseFieldSelector: '#baseFieldName'
    }

    // extend options
    options = $.extend(defaults, options)

    // loop all elements
    return $(element).each((index, el) => {
      // variables
      const $element = $(el)
      const $pageTitle = $(options.pageTitleSelector)
      const $pageTitleOverwrite = $(options.pageTitleOverwriteSelector)
      const $navigationTitle = $(options.navigationTitleSelector)
      const $navigationTitleOverwrite = $(options.navigationTitleOverwriteSelector)
      const $metaDescription = $(options.metaDescriptionSelector)
      const $metaDescriptionOverwrite = $(options.metaDescriptionOverwriteSelector)
      const $metaKeywords = $(options.metaKeywordsSelector)
      const $metaKeywordsOverwrite = $(options.metaKeywordsOverwriteSelector)

      // bind keypress
      $element.bind('keyup input', EventUtil.debounce(calculateMeta, 400))

      // bind change on the checkboxes
      if ($pageTitle.length > 0 && $pageTitleOverwrite.length > 0) {
        $pageTitleOverwrite.change((e) => {
          if (!$pageTitleOverwrite.is(':checked')) $pageTitle.val($element.val())
        }).trigger('change')
      }

      if ($navigationTitle.length > 0 && $navigationTitleOverwrite.length > 0) {
        $navigationTitleOverwrite.change((e) => {
          if (!$navigationTitleOverwrite.is(':checked')) $navigationTitle.val($element.val())
        }).trigger('change')
      }

      $metaDescriptionOverwrite.change((e) => {
        if (!$metaDescriptionOverwrite.is(':checked')) $metaDescription.val($element.val())
      }).trigger('change')

      $metaKeywordsOverwrite.change((e) => {
        if (!$metaKeywordsOverwrite.is(':checked')) $metaKeywords.val($element.val())
      }).trigger('change')

      // calculate meta
      function calculateMeta (e, element) {
        const title = (typeof element !== 'undefined') ? element.val() : $(this).val()

        if ($pageTitle.length > 0 && $pageTitleOverwrite.length > 0) {
          if (!$pageTitleOverwrite.is(':checked')) $pageTitle.val(title)
        }

        if ($navigationTitle.length > 0 && $navigationTitleOverwrite.length > 0) {
          if (!$navigationTitleOverwrite.is(':checked')) $navigationTitle.val(title)
        }

        if (!$metaDescriptionOverwrite.is(':checked')) $metaDescription.val(title)

        if (!$metaKeywordsOverwrite.is(':checked')) $metaKeywords.val(title)
      }
    })
  }
}
