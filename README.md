Soflomo Blog
===

Soflomo\Blog is an [ensemble](http://ensemble.github.com) module that provides blogging features. It provides the following features:

1. Blog articles with a title, lead and body text.
2. Articles have a publish date after which they are visible for visitors
3. A "recent" listing with a configurable number of items for the listing
4. An archive with paginator with a configurable number of items per page
5. A dedicated view page for the full article
6. A view helper to retrieve some recent items on an arbitrary page
7. An admin interface based on ZfcAdmin
8. A preview feature in the admin to read unpublished blog posts
9. Multiple blog instances (for example a blog for "news" and one for "press")

Installation
---
Soflomo\Blog is available as a composer package. Currently, Soflomo\Blog is not registered on Packagist, but you can add this repository to your `composer.json` file:

```
"repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:Soflomo/Blog.git"
        }
    ],
```

Then require `soflomo/blog`. Currenly, Soflomo\Blog is in development and alpha versions are tagged. The latest alpha release is `v0.1.0-alpha5`. To get the latest version of Soflomo\Blog, require the `@alpha` version in your composer.json:

```
"require": {
    "soflomo/blog": "@alpha"
}
```

Enable the module (named `Soflomo\Blog`) in your application.config.php.

Configuration
---
Create your own configuration file in `config/autoload/` (e.g. `soflomo_blog.config.global.php`). Check the configuration from `vendor/soflomo/blog/config/module.config.php` for all the configuration options.

I18n
---
If you need your blog to support i18n (translations), check the module Soflomo\BlogI18n out which extends this blogging module to enable support for translated articles.