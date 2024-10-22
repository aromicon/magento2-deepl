# Magento 2 Aromicon DeepL Translate

This Magento 2 module extends your shop with the possibility to automatically translate CMS pages, CMS blocks, products, 
attributes, ratings and categories with the help of DeepL API.

<p align="center">
  <img width="460" height="300" src="https://www.aromicon.de/magento-download-extensions-modules/media/catalog/product/a/r/aromicon_deepl_magento2_extension_box.jpg">
</p>

A DeepL API Key is required and can be created [here](https://www.deepl.com/pro?cta=header-prices#developer/) 

### FREE DEEPL API KEY + 500.000 Words monthly
You can register for new Deepl Free API  [here](https://www.deepl.com/pro?cta=header-prices#developer/)

Currently Supported Languages:

* BG - Bulgarian
* CS - Czech
* DA - Danish
* DE - German
* EL - Greek
* EN - English
* ES - Spanish
* ET - Estonian
* FI - Finnish
* FR - French
* HU - Hungarian
* ID - Indonesian
* IT - Italian
* JA - Japanese
* KO - Korean
* LT - Lithuanian
* LV - Latvian
* NB - Norwegian (Bokmål)
* NL - Dutch
* PL - Polish
* PT - Portuguese (all Portuguese varieties mixed)
* RO - Romanian
* RU - Russian
* SK - Slovak
* SL - Slovenian
* SV - Swedish
* TR - Turkish
* UK - Ukrainian
* ZH - Chinese

## Installation
### Requirements
Magento CE > 2.2.5

### Composer
Go to Magento 2 Root Folder and execute following commands as web user (mostly www-data).

```bash
composer require aromicon/module-deepl
bin/magento module:enable Aromicon_Deepl
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:enable # (only needed if caches were enabled before)
```

### Manual (not recommended)
Go to your Magento 2 root directory and create a folder `app/code/Aromicon/Deepl`. Extract Archive to this folder and
execute following commands as web user (usually www-data).

```bash
bin/magento module:enable Aromicon_Deepl
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:enable # (only needed, if caches were enabled before)
```

## Magento 2 Configuration

Aromicon Deepl Translation Configuration can be found under ```Stores > Configuration > Aromicon > Deepl Translate```

1. Specify your Default Store View – the Store View where you normally add your content in your default language.
2. Choose which API Version corresponds to Your Account. You can choose "Free" or "Pro".
3. Enter your API Key provided by [DeepL Pro](https://www.deepl.com/pro.html).

You can also configure, which fields should be translated by default for CMS Pages, Products and Categories.

## Usage

All translations will be added per Store View. The target language will be recieved from your Store View locale settings.

### Translate Magento 2 CMS Blocks

Go to ```Content > CMS > Blocks``` and open the block you want to translate. At the edit form on top right corner, 
there will be a new Translate Button. Select the Store View, where the Block should be translated and copied.

By default the block will be copied to selected Store View and the Title and Content will be translated.

### Translate CMS Pages

Go to ```Content > CMS > Pages``` and open the page you want to translate. At the edit form on top right corner, 
there will be a new Translate Button. Select the Store View, where the CMS Page should be translated and copied.

By Default the page will be copied to selected Store View and the Title, Url Key, Meta Information and Content will be translated.

### Translate Attributes

All Magento 2 Attributes can be translated, but only the Store Labels will be translated. If the attribute is a dropdown or multiselect
attribute, without a custom source model, the option walues will be translated as well.

Go to ````Stores > Attributes > Product```` and select the needed attribute. Use the translate button on top right corner.


### Translate Magento 2 Categories

Name, Page Title, Content, Url Key and Meta Information will be translated on Categories. If you open a Category you will see the Translate button on top right corner.
Translated Content will be copied to the selected Store View.

### Translate Products

By default, Name, Short Description, Description, Url Key and Meta Information will be translated. You can also configure 
all attributes of type text or textarea to be translated as well. Just go to your Product Edit View and hit the Translate 
Button for your desired Store View. Translated data will be copied to the selected Store View.

### Translate Products Reviews

Product Reviews can be translated too. Just go to ```Marketing > User Content > Reviews``` and open a review, 
then select a Store View from the Translate Button.

## Aromicon Deepl Pro 
Are you looking for Batch Translation from Backend or Console? Try our [Aromicon Translate Pro Magento 2 Module](https://www.aromicon.de/magento-download-extensions-modules/de/magento-2-translate-pro.html)

## Support

Pull requests, issues and features are always welcome.

<p align="center">
<small>We ❤ Magento. by [aromicon](https://www.aromicon.de)</small><br/>
<small>Made by [aromicon](https://www.aromicon.de)</small>
</p>
