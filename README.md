## eCommerce for Statamic

An eCommerce addon for Statamic v3.

### Installation

```
$ composer require damcclean/commerce
```

* Then publish config from service provider
* Then publish blueprints to `resources/blueprints`

### To Do

* [x] Control Panel (CRUD interface)
* [ ] Example front-end shop
* [ ] Order processing (Stripe stuff)
* [ ] Notifications
* [ ] Store Dashboard
* [ ] Widgets
* [ ] Fix search on listings
* [x] Install command
* [ ] Get front-end assets in a way they can be published
* [ ] Fix issue after saving assets from publish form (not being formatted properly in yaml)

### Addon Dev Questions

* How can I add my own SVG icons for the Control Panel nav or is there an icon pack I can choose from?
* How can I fix the `A facade root has not been set.` issue with the Yaml facade?
