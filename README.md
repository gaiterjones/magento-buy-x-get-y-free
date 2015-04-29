## Magento Buy X Get Y Free - blog.gaterjones.com

### Magento module

Do not use this module if you already have extended the cart controller class with another module. e.g. if you are using a third party cart module, or an ajax cart add/remove product enhancer.

### Synopsis
A common requirement for an eCommerce store is the Buy X Get Y free sales promotion, where a bonus product Y is offered if the customer buys a quantity of product X or the Spend X Get Y Free scenario where a bonus product Y is offered when the customer spends X amount on a single order.

This Magento module code provides Buy X Get Y, Spend X get Y, use Coupon X get Y and buy from Category X get Y functionality for Magento eCommerce stores.

### Version
***
	@version		0.70.0
	@since			03 2013
	@author			gaiterjones
	@documentation	blog.gaiterjones.com
	@twitter		twitter.com/gaiterjones
	
### Installation

Extract the module and copy the files to the /app folder of your magento installation. Refresh your cache, log out of admin and back in again.

### Configuration

Configure the module under 

	System>Configuration>My modules
	
see below for examples. There are four sections to configure, one for each type of BUYXGETYFREE promotion, BUY X, SPEND X, CATEGORY X and COUPON X.

### BUY X
Buy product X get, product Y free, discounted.

### SPEND X
Spend amount X, get product Y free, discounted.

### COUPON X
Use coupon X get product Y free, discounted.

### CATEGORY X
Buy products that belong to a specific category X, get product Y free, discounted.

You must create a new simple product to represent your free gift product (product Y), or duplicate an existing product. Product Y must have a unique ID across all configs, buy, spend and coupon. It must be saleable, should be hidden from your catalog search and have a zero price.

If you want to provide the same product Y free or discounted for various product X's you must duplicate product Y for each offer so that each product Y has a unique ID number.

If you want to give a bonus product for multiple product X's either use an existing category that the X products are members of, or create a dummy category, i.e. BuyXGetYFree that is not enabled/visible. Then add the X products to this category. Specify the ID number of the offer category in the module configuration.

The module works best for a free gift product that is a simple product without options. If you want to make your free gift product Y a configurable product i.e. a product with colours RED/BLACK/WHITE, then consider creating a simple product to represent the free gift i.e. "FREE RED/BLACK/WHITE GIFT - select colour at checkout" and then include a comments section at checkout to allow customer to specify the colour/size etc there.

To configure the module for use with a coupon, first configure the coupon under Admin->Promotions. Configure the name of the coupon but do not apply any discount info, conditions or actions, i.e. in effect the coupon does nothing. Under the Coupon X configuration section of the module, define product Y, the free or discounted product and the name of the configured coupon. You can also configure a minimum cart total required for this coupon. Test the module by applying the coupon to the cart, product Y should then be added.

Consider modifying your theme so that products that have a zero price have the quantity selection box disabled or removed in the cart or during the checkout process.

Test out the module at my development store here http://dev.gaiterjones.com/magento/

To configure a simple Buy X Get Y Free

1. Create a new simple product or duplicate an existing simple product in your store, this will become product Y - the free of bonus product.
Make sure the new product is enabled, price is set to 0 and product is saleable i.e. if you manage stock for the product stock quantity is greater than 0.

e.g. I created a new product FREE MOUSE MAT, and it has ID number 100

2. Take a note of the ID number of Product X - the product customers must buy to qualify for the free gift etc. (You will find product ID numbers on the left hand
side of the product management window in Magento Admin.)

e.g. my product X is a Computer Mouse and it has ID number 50. 

3. Goto Admin, Configuration, My modules

4. Enter the product ID number of Product X, this is the product customers must buy to qualify for a bonus, in my example the Computer Mouse - ID number 50

5. Enter the product ID of Product Y, the free gift simple product you created - ID number 100.

6. Enter the amount of product X the customer must buy to qualify for product Y, lets say Buy 2 Computer Mice, get a free mouse mat, so we enter 2 here.

7. Click save.

Test

Use the same configuration for Spend X Get Y Free except there is no product X and quantity becomes cart total, e.g. spend $50 and get a free mouse mat.

Use the same configurtation for Coupon X Get Y Free, configure the coupon first under promotions. Configure the coupon so that it gives a 0% discount.
Specify the coupon name and product Y ID under the Coupon configuration.

### Feedback

For further information or support goto blog.gaiterjones.com


Please provide feedback on testing and use to modules@gaiterjones.com

## License

The MIT License (MIT)
Copyright (c) 2013 Peter Jones

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.