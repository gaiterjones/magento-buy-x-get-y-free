## Magento Buy X Get Y Free - blog.gaterjones.com

### Beta Extension

Do not use this extension if you already have extended the cart controller class with another extension. e.g. if you are using a third party cart extension, or an ajax cart add/remove product enhancer.

### Version
***
	@version		0.70.0
	@since			03 2013
	@author			gaiterjones
	@documentation	blog.gaiterjones.com
	@twitter		twitter.com/gaiterjones
	
### Installation

Copy the files to the root folder of your Magento installation.

### Configuration

To configure a simple Buy X Get Y Free

1. Create a new simple product or duplicate an existing simple product in your store, this will become product Y - the free of bonus product.
Make sure the new product is enabled, price is set to 0 and product is saleable i.e. if you manage stock for the product stock quantity is greater than 0.

e.g. I created a new product FREE MOUSE MAT, and it has ID number 100

2. Take a note of the ID number of Product X - the product customers must buy to qualify for the free gift etc. (You will find product ID numbers on the left hand
side of the product management window in Magento Admin.)

e.g. my product X is a Computer Mouse and it has ID number 50. 

3. Goto Admin, Configuration, My Extensions

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


Please provide feedback on testing and use to extensions@gaiterjones.com

## License

The MIT License (MIT)
Copyright (c) 2013 Peter Jones

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.