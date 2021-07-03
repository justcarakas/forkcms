import {Config} from './Config'
import Translator from 'bazinga-translator'

export class Locale {
  constructor() {
    this.init()
  }

  init() {
    if (Locale.translationsLoaded) {
      return
    }

    $.ajax({
      url: '/_translations/' + Config.getCurrentLanguage() + '.json',
      type: 'GET',
      dataType: 'json',
      async: false,
      success: (translations) => {
        Translator.fromJSON(translations)
        Locale.translationsLoaded = true
      },
      error: (jqXHR, textStatus, errorThrown) => {
        throw new Error('Regenerate your locale-files.')
      }
    })
  }

  // get an item from the locale
  get(type, key, domain, parameters) {
    return this.trans(type + '.' + key, parameters, domain)
  }

  trans(id, parameters, domain, locale) {
    let translation = Translator.trans(id, parameters, domain || Config.getDefaultTranslationDomain(), locale)

    if (translation !== id) {
      return translation
    }

    translation = Translator.trans(id, parameters, Config.getFallbackTranslationDomain(), locale)

    if (translation !== id) {
      return translation
    }

    console.debug('Missing translation: ' + id)

    return id
  }

  transChoice(id, number, parameters, domain, locale) {
    let translation = Translator.transChoice(id, number, parameters, domain || Config.getDefaultTranslationDomain(), locale)

    if (translation !== id) {
      return translation
    }

    translation = Translator.transChoice(id, number, parameters, Config.getFallbackTranslationDomain(), locale)

    if (translation !== id) {
      return translation
    }

    console.debug('Missing translation: ' + id)

    return id
  }

  // get an error
  err(key, module, parameters) {
    return this.get('err', key, module || Config.getDefaultTranslationDomain(), parameters)
  }

  // get a label
  lbl(key, module, parameters) {
    return this.get('lbl', key, module || Config.getDefaultTranslationDomain(), parameters)
  }

  // get localization
  loc(key) {
    return this.get('loc', key)
  }

  // get a message
  msg(key, module, parameters) {
    return this.get('msg', key, module || Config.getDefaultTranslationDomain(), parameters)
  }

  // get a slug
  slg(key, module, parameters) {
    return this.get('slg', key, module || Config.getDefaultTranslationDomain(), parameters)
  }
}

Locale.translationsLoaded = false
