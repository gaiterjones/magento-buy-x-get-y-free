BuyXGetYFree - extensions@gaiterjones.com

BETA TEST Extension

Do not use this extension if you already have extended the cart controller class with another extension. e.g. if you are using
a third party cart extension, or an ajax cart add/remove product enhancer.


To install the extension, copy the extension app folder to your magento store app folder.
Refresh your magento cache.
Logout from admin, and login again.

To configure

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


For further information or support goto blog.gaiterjones.com





Please provide feedback on testing and use to extensions@gaiterjones.com






