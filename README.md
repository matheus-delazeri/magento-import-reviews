# Magento 1.9 import products' reviews module
A Magento 1.9's module to import products' reviews from a .CSV file.

## Module informations
`Package/Namespace`: "Matheus"  

`Modulename`: "ImportReviews"

`codepool`: "community"  

## How to install
Add the folder `Matheus` inside `/app/code/community/` and add the file `Matheus_ImportReviews.xml` inside `/app/etc/modules/`

## How to use
After installation a new submenu named `Import Reviews` will be created at the menu `Catalog` in your admin panel. Click in it to enter the module's page. 



Now, you just need to upload your file and click in `Start` to begin the importation process.




## Input file pattern
The input file must be in CSV format and in the following order:
|sku|title|review|status|customer_name|customer_id|
| --- | --- | --- | --- | --- | --- | 
|product-sku|title of review|the review|1|Matheus| |
|product-2-sku|random title|review|1|John|1|

Where:
* `sku`: product's sku
* `title`: title of the review
* `review`: text of the review
* `status`:
  * 1 for **Approved**
  * 2 for **Pending**
  * 3 for **Not Approved**
* `customer_name`: self explanatory
* `customer_id`: you can use the ID of the customer or leave it blank for administrator

