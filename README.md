```page_admin:
    resource: "@TwinElementsPageBundle/Controller/Admin/"
    prefix: /admin
    type: annotation
    requirements:
        _locale: '%app_locales%'
    defaults:
        _locale: '%locale%'
        _admin_locale: '%admin_locale%'
    options: { i18n: false }
```
    
TwinElements\PageBundle\TwinElementsPageBundle::class => ['all' => true],

add templates config 
```
twin_elements_page:
  template_translator_prefix: 'page.templates'
  templates:
    - {name: 'main_template', path: 'front/page/page.html.twig', isDefault: true}
    - {name: 'second_template', path: 'front/page/second_page.html.twig'}
```

in `messages.LOCALE.yaml` add template name translations
```
page:
  templates:
    main_template: Main template
    second_template: Second template
```