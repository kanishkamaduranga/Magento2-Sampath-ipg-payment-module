# Sampath Bank ( Sri Lanka ) IPG payment for Magento 2
This is a Payment Module for Magento 2 Community Edition,
That gives facilitate to do payments  between Sampath ( Sri Lanka ) IPG and Magento 2 .

## Requirements
 * Magento 2 Community Edition 2.x (Tested up to 2.4.0 / 2.4.3)

## Installation (manual)

* Download the Payment Module archive, unpack it and upload its contents to a new folder <root>/app/code/Elaboom/Sampath/ of your Magento 2 installation
* Enable Payment Module
```sh
    $ php bin/magento module:enable Elaboom_Sampath
```
```sh
    $ php bin/magento setup:upgrade
```
```sh
    $ php bin/magento setup:di:compile
```
* Deploy Magento Static Content (Execute If needed)
```sh
    $ php bin/magento setup:static-content:deploy 
```
## Configuration

* Login inside the __Admin Panel__ and go to ```Stores``` -> ```Configuration``` -> ```Sales``` -> ```Payment Methods```
* If the Payment Module Panel ```Sampath IPG``` is not visible in the list of available Payment Methods,
  go to  ```System``` -> ```Cache Management``` and clear Magento Cache by clicking on ```Flush Magento Cache```
* Go back to ```Payment Methods``` and click the button ```Configure``` under the payment method ```Sampath IPG Checkout``` to expand the available settings
* Set ```Enabled``` to ```Yes```, set the correct credentials, select your prefered transaction types and additional settings and click ```Save config```
