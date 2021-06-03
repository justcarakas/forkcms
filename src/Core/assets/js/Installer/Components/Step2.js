export class Step2 {
  constructor() {
    this.toggleLanguageType()
    this.handleLanguageChanges()
    this.setInterfaceDefaultLanguage()
  }

  /**
   * Toggles between multiple and single lexanguage site
   */
  toggleLanguageType() {
    $('[data-fork-cms-role="multilingual"]').on('change', (event) => {
      if ($(event.target).is(':checked')) {
        $('[data-fork-cms-role="multilingual-wrapper"]').show()
        $('[data-fork-cms-role="default-locale"] option').prop('disabled', true)
        $('[data-fork-cms-role="locales"] input:checked').each((index, element) => {
          $('[data-fork-cms-role="default-locale"] option[value=' + $(element).val() + ']').removeAttr('disabled')
        })
        let $defaultLocaleSelector = $('[data-fork-cms-role="default-locale"]');
        if ($('[data-fork-cms-role="default-locale"] option[value=' + $defaultLocaleSelector.val() + ']').length === 0) {
          $defaultLocaleSelector.val($('[data-fork-cms-role="default-locale"] option:enabled:first').val())
        }
      }

      this.setInterfaceDefaultLanguage()
    }).trigger('change')

    // single languages
    $('[data-fork-cms-role="not-multilingual"]').on('change', (event) => {
      if ($(event.target).is(':checked')) {
        $('[data-fork-cms-role="multilingual-wrapper"]').hide()
        $('[data-fork-cms-role="default-locale"] option').removeAttr('disabled')
      }

      this.setInterfaceDefaultLanguage()
    }).trigger('change')
  }

  setInterfaceDefaultLanguage() {
    let $defaultInterfaceLocale = $('[data-fork-cms-role="default-interface-locale"]')
    let $defaultInterfaceLocaleOptions = $defaultInterfaceLocale.find('option')
    // same language as frontend
    if ($('[data-fork-cms-role="same-interface-locale"]').is(':checked')) {
      // just 1 language selected = only selected frontend language is available as interface language
      if ($('[data-fork-cms-role="not-multilingual"]').is(':checked')) {
        $defaultInterfaceLocaleOptions.prop('disabled', true)
        $('[data-fork-cms-role="default-interface-locale"] option[value=' + $('[data-fork-cms-role="default-locale"]').val() + ']').removeAttr('disabled')
        $defaultInterfaceLocale.val($('[data-fork-cms-role="default-interface-locale"] option:enabled:first').val())
      }
      else if ($('[data-fork-cms-role="multilingual"]').is(':checked')) {
        $defaultInterfaceLocaleOptions.prop('disabled', true)
        $('[data-fork-cms-role="locales"] input:checked').each((index, element) => {
          $('[data-fork-cms-role="default-interface-locale"] option[value=' + $(element).val() + ']').removeAttr('disabled')
        })

        if ($('[data-fork-cms-role="default-interface-locale"] option[value=' + $defaultInterfaceLocale.val() + ']').length === 0) {
          $defaultInterfaceLocale.val($('[data-fork-cms-role="default-interface-locale"] option:enabled:first').val())
        }
      }
    }
    else {
      // different languages than frontend
      $defaultInterfaceLocaleOptions.prop('disabled', true)
      $('[data-fork-cms-role="interface-locales"] input:checked').each((index, element) => {
        $('[data-fork-cms-role="default-interface-locale"] option[value=' + $(element).val() + ']').removeAttr('disabled')
      })
      if ($('[data-fork-cms-role="default-interface-locale"] option[value=' + $defaultInterfaceLocale.val() + ']').length === 0) {
        $defaultInterfaceLocale.val($('[data-fork-cms-role="default-interface-locale"] option:enabled:first').val())
      }
    }
  }

  handleLanguageChanges() {
    $('[data-fork-cms-role="locales"] input:checkbox').on('change', () => {
      $('[data-fork-cms-role="default-locale"] option').prop('disabled', true)
      $('[data-fork-cms-role="locales"] input:checked').each((index, element) => {
        $('[data-fork-cms-role="default-locale"] option[value=' + $(element).val() + ']').removeAttr('disabled')
      })
      let $defaultLocale = $('[data-fork-cms-role="default-locale"]')
      if ($('[data-fork-cms-role="default-locale"] option[value=' + $defaultLocale.val() + ']').length === 0) {
        $defaultLocale.val($('[data-fork-cms-role="default-locale"] option:enabled:first').val())
      }

      this.setInterfaceDefaultLanguage()
    })

    $('[data-fork-cms-role="default-locale"]').on('change', () => {
      this.setInterfaceDefaultLanguage()
    })

    // interface language
    $('[data-fork-cms-role="same-interface-locale"]').on('change', () => {
      if ($('[data-fork-cms-role="same-interface-locale"]').is(':checked')) {
        $('[data-fork-cms-role="interface-locales-explanation"]').hide()
        $('[data-fork-cms-role="interface-locales"]').hide()
      }
      else {
        $('[data-fork-cms-role="interface-locales-explanation"]').show()
        $('[data-fork-cms-role="interface-locales"]').show()
      }

      this.setInterfaceDefaultLanguage()
    }).trigger('change')

    $('[data-fork-cms-role="interface-locales"] input:checkbox').on('change', () => {
      this.setInterfaceDefaultLanguage()
    })
  }
}
