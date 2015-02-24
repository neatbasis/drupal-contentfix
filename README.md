Bootstrapify Drupal Article content
===================================

A tool for modifying article content in Drupal

* Removes static width and heights from <img /> elements
* Removes classes and styles from <img /> elements
* Adds img-responsive class to all <img /> elements
* Detects iframe ratios and bootstrapify's <iframe></iframe> elements by adding a wrapper

See http://getbootstrap.com/components/#responsive-embed and http://getbootstrap.com/css/#images

## Usage

First clone this repository to the root directory of your Drupal site


Then
```shell
cd drupal-contentfix
composer update
php bin/contentfix.php
```
