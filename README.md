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
