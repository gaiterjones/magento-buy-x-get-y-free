<?php
/**
 *  GaiterJones/PAJ - http://blog.gaiterjones.com
 *  Add free/discounted product/s to cart based on BUY X quantity and get Y product/s free/discounted.
 *  Add free/discounted product/s to cart based on SPEND X amount get Y product/s free/discounted.
 *  Add free/discounted product/s to cart based on CATEGORY X get Y product/s free/discounted.
 *  Add free/discounted product/s to cart based on COUPON X get Y product/s free/discounted.
 *  Extends Mage/Checkout/CartController.php
 *  
 *  Copyright (C) 2015 paj@gaiterjones.com 26.08.2015 v0.74
 *  0.59 - Bug Fix string typo maxQtyProductY
 *  0.60 - Bug Fix force lower case check for coupon name.
 *  0.61 - Added product exclusion for Spend X
 *  0.62 - Admin text changes.
 *  0.63 - Added min required product X to Category funtion, improved spend X excluded products logic.
 *  0.65 - Improved translations.
 *  0.66 - Bugs in Category X Function
 *	0.70 - Changes to indexAction to improve functionality with other modules extending cartcontroller
 *	0.71 - Added logic for MAX and MIN spend option to allow different Y gift for different spend amounts 18.11.2013
 *	0.72 - float integer for spend totals, get subtotal from session
 *  0.73 - stop duplicate notification messages 
 *  0.74 - improve translation strings
 *  0.75 - spend x for loop excluded product not array bug
 *  0.76 - needed to be able to exclude customer groups, added exclude option for all offers 05.10.2016
 *  0.77 - fix group check array
 *  0.78 - fix excluded group bug when no excluded groups configured
 *	This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @category   PAJ
 *  @package    BuyXGetYFree
 *  @license    http://www.gnu.org/licenses/ GNU General Public License
 * 
 *
 */
 
require_once Mage::getModuleDir('controllers', 'Mage_Checkout').DS.'CartController.php'; 

class PAJ_BuyXGetYFree_Frontend_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
		$_excludedCustomerGroupConfig=Mage::getStoreConfig('buyxgetyfree_section1/general/excluded_customer_groups');
			
		$_groupId = (string)Mage::getSingleton('customer/session')->getCustomerGroupId(); //Get Customers Group ID
		$_excludedCustomerGroups=explode (",",$_excludedCustomerGroupConfig); // get list of excluded groups
		
		$_excludeFromOffer=false;
		foreach ($_excludedCustomerGroups as $_excludedCustomerGroup)
		{
			if ($_groupId===$_excludedCustomerGroup) {$_excludeFromOffer=true;} // group is excluded from all offers
		}	
		
		if (!$_excludeFromOffer) {
			// Buy X get Y Free
			$this->buyXgetYfree();
			// Spend X get Y Free
			$this->spendXgetYfree();				
			// Coupon X get Y Free
			$this->couponXgetYfree();
			// Category X get Y Free
			$this->categoryXgetYfree();
		}

		
			
		return parent::indexAction();
    }
	
	public function buyXgetYfree()
	{
		// BUY X quantity Get Y product/s free/discounted
		
		$cart = $this->_getCart();
		
		if (!$this->_getCart()->getQuote()->getItemsCount()) {
            // cart is empty
			return;
        }		
		
		// Get admin variables for BUY x get y free
		$buyProductXID = explode (",",Mage::getStoreConfig('buyxgetyfree_section1/general/productx_product_id'));
		$buyProductYID = explode (",",Mage::getStoreConfig('buyxgetyfree_section1/general/producty_product_id'));
		$buyProductXminQty = explode (",",Mage::getStoreConfig('buyxgetyfree_section1/general/productx_required_qty'));
		$buyProductXmaxQty = explode (",",Mage::getStoreConfig('buyxgetyfree_section1/general/productx_limit_qty'));	
		$buyProductYDescription = explode (",",Mage::getStoreConfig('buyxgetyfree_section1/general/producty_description'));
		
		$error="A BuyXGetYFree Extension cart error was detected!";		
		
		try
		{
			for($i = 0; $i < count($buyProductXID); $i++){
				if (empty($buyProductYDescription[$i])) {
					$buyProductYDescription[$i]="free gift";
				}
				if (empty($buyProductXID[$i])) {
					$buyProductXID[$i]="0";
				}
				if (empty($buyProductYID[$i])) {
					$buyProductYID[$i]="0";
				}
				if (empty($buyProductXminQty[$i])) {
					$buyProductXminQty[$i]="999";
				}
				if (empty($buyProductXmaxQty[$i])) { // if no max quantity configured set to 0
					$buyProductXmaxQty[$i]="0";
				}				
				if ($buyProductXID[$i] !="0" && $buyProductYID[$i] !="0") {	
					if ($this->isProductYUnique()) // product Y must be unique
					{
						// update the cart for this offer
						$this->buyXgetYfreeCartUpdate((int)$buyProductXID[$i],(int)$buyProductXminQty[$i],(int)$buyProductYID[$i],$buyProductYDescription[$i],(int)$buyProductXmaxQty[$i]);				
					} else {	
						$error = "Error in Buy X configuration - Product Y is not unique across all extension settings."; 	
						throw new Exception($error);
					}
				}

			}

		} catch (Exception $ex) { 
			// Catch errors
			$this->addNotificationMessage($cart,'error',$this->__($error));
			$this->sendErrorEmail($error);
			}
	}

	public function spendXgetYfree()
	{	
		// SPEND X quantity Get Y product/s free/discounted
		
		$cart = $this->_getCart();
 		if (!$this->_getCart()->getQuote()->getItemsCount()) {
            // cart is empty
			return;
        }
		
		
		// Get admin variables for SPEND x get y free
		$spendProductYID = explode (",",Mage::getStoreConfig('buyxgetyfree_section2/general/spend_producty_product_id'));
		$spendCartYLimit = explode (",",Mage::getStoreConfig('buyxgetyfree_section2/general/spend_cart_y_limit'));
		$spendCartTotalRequired = explode (",",Mage::getStoreConfig('buyxgetyfree_section2/general/spend_cart_total_required'));
		$spendProductYDescription = explode (",",Mage::getStoreConfig('buyxgetyfree_section2/general/spend_producty_description'));
		$spendCustomerGroupID = explode (",",Mage::getStoreConfig('buyxgetyfree_section2/general/spend_customer_group_id'));
		$spendExcludedProductsID = Mage::getStoreConfig('buyxgetyfree_section2/general/spend_excluded_products_id');
		
		if (empty($spendExcludedProductsID)) {
			$spendExcludedProductsID=false;
		} else {
			$spendExcludedProductsID = explode (",",Mage::getStoreConfig('buyxgetyfree_section2/general/spend_excluded_products_id'));
		}
		
		$error="A SpendXGetYFree Extension cart error was detected!";
		
		// Spend X amount get Y Product/s free/discounted
		try
		{

			for($i = 0; $i < count($spendProductYID); $i++){
				if (empty($spendProductYDescription[$i])) {
					$spendProductYDescription[$i]="free gift";
				}
				if (empty($spendProductYID[$i])) {
					$spendProductYID[$i]="0";
				}
				if (empty($spendCartTotalRequired[$i])) {
					$spendCartTotalRequired[$i]="50";
				}
				if (empty($spendCartYLimit[$i])) {
					$spendCartYLimit[$i]="0";
				}				
				if ($spendProductYID[$i] !="0") {
					if ($this->isProductYUnique())
					{
						// update the cart for this offer
						$this->spendXgetYfreeCartUpdate((int)$spendProductYID[$i],(float)$spendCartTotalRequired[$i],(float)$spendCartYLimit[$i],$spendProductYDescription[$i],$spendCustomerGroupID[$i],$spendExcludedProductsID);
					} else {	
						$error = "Error in Spend X configuration - Product Y is not unique across all extension settings."; 	
						throw new Exception($error);
					}
				}
			}

		} catch (Exception $ex) { 
			// Catch errors
			$this->addNotificationMessage($cart,'error',$this->__($error));
			$this->sendErrorEmail($error);
			}
	}
	
	public function couponXgetYfree()
	{
		// Use Coupon X Get Y product/s free/discounted
		
		$cart = $this->_getCart();
		
		if (!$this->_getCart()->getQuote()->getItemsCount()) {
            // cart is empty
			return;
        }
		
		// Get admin variables for COUPON x get y free
		$couponProductYID = explode (",",Mage::getStoreConfig('buyxgetyfree_section4/general/coupon_producty_product_id'));
		$couponRequired = explode (",",Mage::getStoreConfig('buyxgetyfree_section4/general/coupon_required'));
		$couponProductYDescription = explode (",",Mage::getStoreConfig('buyxgetyfree_section4/general/coupon_producty_description'));	
		$couponCartTotalRequired = explode (",",Mage::getStoreConfig('buyxgetyfree_section4/general/coupon_cart_total_required'));	
		
		// Coupon X get Y Free
		$error="A CouponXGetYFree Extension cart error was detected!";
		
			try
			{

				for($i = 0; $i < count($couponProductYID); $i++){
					if (empty($couponProductYDescription[$i])) {
						$couponProductYDescription[$i]="free gift";
					}
					if (empty($couponProductYID[$i])) {
						$couponProductYID[$i]="0";
					}
					if (empty($couponCartTotalRequired[$i])) {
						$couponCartTotalRequired[$i]="0";
					}					
					if (empty($couponRequired[$i])) {
						// no coupon specified
						break;
					} else {
					}
					if ($couponProductYID[$i] !="0") {
						if ($this->isProductYUnique() )
						{
							// update the cart for this offer
							$this->couponXgetYfreeCartUpdate((int)$couponProductYID[$i],$couponRequired[$i],$couponProductYDescription[$i],$couponCartTotalRequired[$i]);
						} else {	
							$error = "Error in Coupon X configuration - Product Y is not unique across all extension settings."; 	
							throw new Exception($error);
						}
					}
				}

			} catch (Exception $ex) { 
				// Catch errors
				$this->addNotificationMessage($cart,'error',$this->__($error));
				$this->sendErrorEmail($error);
				}
	}
	
	public function categoryXgetYfree()
	{
		// Use a category as qualifier for bonus product Y
		
		$cart = $this->_getCart();
		
		if (!$this->_getCart()->getQuote()->getItemsCount()) {
            // cart is empty
			return;
        }
		
		// Get admin variables for CATEGORY x get y free
		$categoryProductYID = explode (",",Mage::getStoreConfig('buyxgetyfree_section5/general/category_producty_product_id'));
		$productXcategoryID = explode (",",Mage::getStoreConfig('buyxgetyfree_section5/general/category_id'));
		$categoryProductYDescription = explode (",",Mage::getStoreConfig('buyxgetyfree_section5/general/category_producty_description'));	
		$maxQtyProductY = explode (",",Mage::getStoreConfig('buyxgetyfree_section5/general/category_producty_max_qty'));	
		$categoryProductXminQty = explode (",",Mage::getStoreConfig('buyxgetyfree_section5/general/category_productx_min_qty'));
		$categoryProductYstep = explode (",",Mage::getStoreConfig('buyxgetyfree_section5/general/category_producty_step'));
		
		// Category X get Y Free
		$error="A CategoryXGetYFree Extension cart error was detected!";
		
			try
			{

				for($i = 0; $i < count($categoryProductYID); $i++){
					if (empty($categoryProductYDescription[$i])) {
						$categoryProductYDescription[$i]="free gift";
					}
					if (empty($categoryProductYID[$i])) {
						$categoryProductYID[$i]="0";
					}
					// define default value for mimimum X products required in cart to qualify for product Y
					if (empty($categoryProductXminQty[$i])) {
						$categoryProductXminQty[$i]="1";
					}
					if (empty($categoryProductYstep[$i])) {
						$categoryProductYstep[$i]=$categoryProductXminQty[$i];
					}						
					if (empty($productXcategoryID[$i])) {
						// no category specified
						break;
					} else {
					}
					if (empty($maxQtyProductY[$i])) {
						$maxQtyProductY[$i]="1";
					}					
					if ($categoryProductYID[$i] !="0") {
						if ($this->isProductYUnique() )
						{
							// update the cart for this offer
							$this->categoryXgetYfreeCartUpdate((int)$categoryProductYID[$i],$productXcategoryID[$i],$categoryProductYDescription[$i],$maxQtyProductY[$i],(int)$categoryProductXminQty[$i],$categoryProductYstep[$i]);
						} else {	
							$error = "Error in Category X configuration - Product Y is not unique across all extension settings."; 	
							throw new Exception($error);
						}
					}
				}

			} catch (Exception $ex) { 
				// Catch errors
				$this->addNotificationMessage($cart,'error',$this->__($error));
				$this->sendErrorEmail($error);
				}
	}	

	public function buyXgetYfreeCartUpdate($productXID, $productXminQtyRequired, $productYID, $productYDesc, $productXmaxQty)
    {
		// if max product X quantity is zero, set to infinity (and beyond)...
		if ($productXmaxQty <= 0) {
			$productXmaxQty = 999999;
		}
		$cart = $this->_getCart();
		$cart->init();

		$productYCartItemId = null;
		$productXCartId = null;
		$lowStockWarningAmount = 5;

		//make sure there is never more than one of product Y in cart
         foreach ($cart->getQuote()->getAllItems() as $item) {
             if ($item->getProduct()->getId() == $productYID) {
               if ($item->getQty() > 1) {
                     $item->setQty(1);
                     $cart->save();
				}
             // product y exists in cart
			 $productYCartItemId = $item->getItemId();
             }
         }
		 
		// check cart contents for product X
		foreach ($cart->getQuote()->getAllItems() as $item) {

			// check if product X exists
			if ($item->getProduct()->getId() == $productXID) {
				
				$productXCartId = $item->getItemId();
				
				// is product X configurable?
				if($item->getProduct()->getTypeId() == 'configurable') {			
				
						// for configurable products, load cart as array and check for all occurences of product X
						// and quantity to determine total quantity products in cart. i.e. when product X has multiple colours
						
						$cfg_qty = 0;
						$cfg_quantities = array();

						foreach ($cart->getQuote()->getAllItems() as $cfg_item) 
						{
							$id = $cfg_item->getProduct()->getId();
							$qty = $cfg_item->getQty();
							$cfg_quantities[$id][] = $qty;
						}
						if(array_key_exists($productXID, $cfg_quantities))
						{
						// calculate product X totals from array
						$cfg_qty = array_sum($cfg_quantities[$productXID]);
						}
						
						// check if quantity of configurable product X qualifies for free product Y
					if ($cfg_qty >= $productXminQtyRequired && $cfg_qty <= $productXmaxQty) { // check product x meets min and max set quantity
						// quantity qualifies add free product Y to cart
							if ($productYCartItemId == null) {
								$product = Mage::getModel('catalog/product')
								->setStoreId(Mage::app()->getStore()->getId())
								->load($productYID);
								$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productYID);
								$qty = $stockItem->getQty();									
								// check stock quantity of product Y.
								// to do, check if product inventory is managed otherwise this can become a minus qty
									if($product->isSaleable()) {
										if ($qty >= 0 && $qty <= $lowStockWarningAmount) {
											$this->sendErrorEmail('BuyXGetYFree product is at very low stock levels. There are only ' . ($qty - 1) . ' left.');
										}
										$cart->addProduct($product);
										$cart->save();
										$this->addNotificationMessage($cart,'success',$this->__('Your %1$s has been added to your cart.', $productYDesc));									
										session_write_close();										
										$this->_redirect('checkout/cart');

									} else {
										if ($qty == 0) {
											$this->sendErrorEmail($product->getName(). ' '. $this->__('stock quantity is 0 and could not be added to the cart!'));
											$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));
											session_write_close();										
										} else {
											$this->sendErrorEmail($product->getName(). ' ' . $this->__('was not saleable and could not be added to the cart!'));
											$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));
											session_write_close();	
										}
									}
								}

							break;
								
						} else {
							// quantity does not qualify
							// check if free product exists
							if ($productYCartItemId != null) {
								$cart->removeItem($productYCartItemId);
								$cart->save();
								$this->addNotificationMessage($cart,'success',$this->__('Your %1$s has been removed from your cart.', $productYDesc));
								session_write_close();
								$this->_redirect('checkout/cart');
							}
							if ($cfg_qty >= ($productXminQtyRequired-1) && $cfg_qty <= $productXmaxQty) {
								// one more required for free gift prompt
								$this->addNotificationMessage($cart,'notice',$this->__('Buy one more %1$s to qualify for a %2$s !',$item->getName(),$productYDesc));
								session_write_close();
							}

							break;								
						}
					
				} else {	// product is not configurable
				
					if ($item->getQty() >= $productXminQtyRequired && $item->getQty() <= $productXmaxQty ) {
						// quantity qualifies so add free product Y
						if ($productYCartItemId == null) {
						    $product = Mage::getModel('catalog/product')
								->setStoreId(Mage::app()->getStore()->getId())
								->load($productYID);
							$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productYID);
							$qty = $stockItem->getQty();
							// check stock quantity of product Y.
								if($product->isSaleable()) {
									if ($qty >= 0 && $qty <= $lowStockWarningAmount) {
										$this->sendErrorEmail('BuyXGetYFree product is at very low stock levels. There are only ' . ($qty - 1) . ' left.');
									}
										$message=$this->__('Your %1$s has been added to your cart.', $productYDesc);
										$cart->addProduct($product);
										$cart->save();
										$this->addNotificationMessage($cart,'success',$message);
										session_write_close();
										$this->_redirect('checkout/cart');
								} else {
										if ($qty == 0) {
											$this->sendErrorEmail($product->getName(). ' '. $this->__('stock quantity is 0 and could not be added to the cart!'));
											$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));
											session_write_close();										
										} else {
											$this->sendErrorEmail($product->getName(). ' ' . $this->__('was not saleable and could not be added to the cart!'));
											$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));
											session_write_close();	
										}
								}
						}
					} else {
							// quantity does not qualify
							// check if free product exists
							if ($productYCartItemId != null) {
								$cart->removeItem($productYCartItemId);
								$cart->save();
								$this->addNotificationMessage($cart,'success',$this->__('Your %s has been removed from your cart.', $productYDesc));
								session_write_close();								
								$this->_redirect('checkout/cart');
							}
							if ($item->getQty() >= ($productXminQtyRequired-1) && $item->getQty() <= $productXmaxQty) {
								// one more required for free gift prompt
								$this->addNotificationMessage($cart,'notice',$this->__('Buy one more %1$s to qualify for a %2$s !',$item->getName(),$productYDesc));
								session_write_close();
							}
					}
				}
			}
		// continue checking cart.
		}
		
		// finished checking cart.
		// if product X not in cart check for product Y and remove
		if (Mage::getStoreConfig('buyxgetyfree_section3/general/allow_duplicate_product_y'))
		{ // allow product y to be duplicated in cart without product x
		  // TO DO for development
		  // can be removed
		} else {
			if ($productXCartId == null) {
				foreach ($cart->getQuote()->getAllItems() as $item) {
				 if ($item->getProduct()->getId() == $productYID) {
						// remove product Y because product X no longer in cart
						$cart->removeItem($productYCartItemId);
						$cart->save();
						$this->addNotificationMessage($cart,'success',$this->__('Your %s has been removed from your cart.', $productYDesc));
						session_write_close();
						$this->_redirect('checkout/cart');
					}
				}
			}
		}
	


	// end function	
	}	

	public function spendXgetYfreeCartUpdate($productYID,$cartTotalRequired,$cartYLimit,$productYDesc,$customerGroupID,$excludeProductID)
    {
		$cart = $this->_getCart();
		$cart->init();

        $productYCartItemId = null;
		$excludeProductTotal=0;
		$lowStockWarningAmount = 5;

		//make sure there is never more than one of product Y in cart
        foreach ($cart->getQuote()->getAllItems() as $item) {
            if ($item->getProduct()->getId() == $productYID) {
				if ($item->getQty() > 1) {
                    $item->setQty(1);
                    $cart->save();
				}
				// product y exists in cart
				$productYCartItemId = $item->getItemId();
            }
			
			if ($excludeProductID) {
				
				foreach ($excludeProductID as $excludedProductID)
				{
					// deduct excluded items from cart total
					if ($item->getProduct()->getId() == $excludedProductID) {
						
							$excludeProductTotal=$excludeProductTotal + ($item->getPrice() * $item->getQty()) ;
						
							// notify customer when products excluded
							if (Mage::getStoreConfig('buyxgetyfree_section2/general/spend_notify_excluded_products'))
							{
								$this->addNotificationMessage($cart,'notice',$item->getName(). ' '. $this->__('excluded from our offers.'));
							}
					}
				}
			}
		}
 
        // get subtotal
        //$subtotal = $cart->getQuote()->getSubtotal();
		
		// get subtotal
		$totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
		$subtotal = $totals['subtotal']->getValue();

		// subtract excluded product total cart price
		$subtotal = $subtotal - $excludeProductTotal;
		
		// get currency codes
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		// if current currency code does not equal base currency do a conversion
		if ($baseCurrencyCode != $currentCurrencyCode)
		{	
			// convert required Spend X cart total from base currency to current currency
			$cartTotalRequired = Mage::helper('directory')->currencyConvert($cartTotalRequired, $baseCurrencyCode, $currentCurrencyCode);
		}

		if ($cartYLimit===0) {$cartYLimit=($subtotal+1); } // if no Y limit applied
		
		// check subtotal and customer group check qualify for offer
        if (($subtotal >= $cartTotalRequired && $subtotal <= $cartYLimit) && $this->checkCustomerGroupId($customerGroupID)) {
		
			if ($productYCartItemId == null) {
				$product = Mage::getModel('catalog/product')
					->setStoreId(Mage::app()->getStore()->getId())
					->load($productYID);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productYID);
                $qty = $stockItem->getQty();
					// check stock quantity of product Y.
						if($product->isSaleable()) {
							if ($qty <= $lowStockWarningAmount) {
								$this->sendErrorEmail('BuyXGetYFree product is at very low stock levels. There are only ' . ($qty - 1) . ' left.');
							}
							$cart->addProduct($product);
							$cart->save();
							$this->addNotificationMessage($cart,'success',$this->__('Your %1$s has been added to your cart.', $productYDesc));							
							session_write_close();
							$this->_redirect('checkout/cart');
						} else {
								if ($qty == 0) {
									$this->sendErrorEmail($product->getName(). ' '. $this->__('stock quantity is 0 and could not be added to the cart!'));
									$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));							
									session_write_close();									
								} else {
									$this->sendErrorEmail($product->getName(). ' ' . $this->__('was not saleable and could not be added to the cart!'));
									$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));							
									session_write_close();	
								}
						}
			}

		} else {   //remove product if it is already there because the subtotal is less than the threshold
				if ($productYCartItemId != null) {
					$cart->removeItem($productYCartItemId);
					$cart->save();
					$this->addNotificationMessage($cart,'success',$this->__('Your %1$s has been removed from your cart.', $productYDesc));							
					session_write_close();
					$this->_redirect('checkout/cart');
				}
        }
    // end function  	
	}
	
	public function categoryXgetYfreeCartUpdate($productYID,$productXcategoryID,$productYDesc,$maxQtyProductY,$minQtyProductXrequired,$productYstep)
    {		
		// init cart
		$cart = $this->_getCart();
		$cart->init();

        $productYCartItemId = null;
		$categoryXproductCount=0;
		$productYCartQty=0;
		
		$lowStockWarningAmount = 5;

		// loop through the cart to get total of qualifying product X
        foreach ($cart->getQuote()->getAllItems() as $item) {

			// ignore product Y
			if ($productYID == $item->getProduct()->getId()){continue;}

			// determine category ids for product
			$categoryIds = $item->getProduct()->getCategoryIds();			
			foreach($categoryIds as $categoryId)
			{
				if ($categoryId==$productXcategoryID)
				{
					// get true count of product x
					$categoryXproductCount = $categoryXproductCount + $item->getQty();
				}
			}
		}
		

		// loop through the cart to control product Y total
        foreach ($cart->getQuote()->getAllItems() as $item)
		{			
            if ($item->getProduct()->getId() == $productYID) // this is product y
			{
					if (floor($categoryXproductCount / $productYstep) > $maxQtyProductY) // set qty using step amount
					{
                    	$item->setQty($maxQtyProductY);
						if ($maxQtyProductY > 1)
						{
							$this->addNotificationMessage($cart,'success',$this->__('You have reached your %1$s limit of %2$s.',$productYDesc,$maxQtyProductY));	
							session_write_close();							
						}
					} else {
                    	$item->setQty(floor($categoryXproductCount / $productYstep));
					}
	                $cart->save();

				// product y exists in cart
				$productYCartItemId = $item->getItemId();
				$productYCartQty = $item->getQty();
				// continue checking....
            }		
		}
		
		// debug notice, remove.
		//$cart->getCheckoutSession()->addNotice($this->__('y qty='. $productYCartQty. ' | x qty='. $categoryXproductCount. ' | max qty Y '.  $maxQtyProductY. ' | step - '. $productYstep. ' | floor - '. (floor($categoryXproductCount / $productYstep))));
		//session_write_close();
		
        if ($categoryXproductCount >= $minQtyProductXrequired) { // minimum required products in category X exist in cart
			if ($productYCartItemId == null) { // product Y is not in the cart so we need to add it
				$product = Mage::getModel('catalog/product')
					->setStoreId(Mage::app()->getStore()->getId())
					->load($productYID);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productYID);
                $qty = $stockItem->getQty();
					// check stock quantity of product Y.
						if($product->isSaleable()) {
							if ($qty <= $lowStockWarningAmount) {
								$this->sendErrorEmail('BuyXGetYFree product is at very low stock levels. There are only ' . ($qty - 1) . ' left.');
							}
							$cart->addProduct($product);
							$cart->save();
							$this->addNotificationMessage($cart,'success',$this->__('Your %1$s has been added to your cart.', $productYDesc));								
							session_write_close();
							$this->_redirect('checkout/cart');
						} else {
								if ($qty == 0) {
									$this->sendErrorEmail($product->getName(). ' '. $this->__('stock quantity is 0 and could not be added to the cart!'));
									$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));								
									session_write_close();									
								} else {
									$this->sendErrorEmail($product->getName(). ' ' . $this->__('was not saleable and could not be added to the cart!'));
									$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));								
									session_write_close();	
								}
						}
			}

		} else {

            //there are no products belonging to category x in cart so remove product y if it is present
				if ($productYCartItemId != null) {
					$cart->removeItem($productYCartItemId);
					$cart->save();
					$this->addNotificationMessage($cart,'success',$this->__('Your %1$s has been removed from your cart.', $productYDesc));								
					session_write_close();
					$this->_redirect('checkout/cart');
				}
			
			// one more required for free gift prompt				
				if ($categoryXproductCount >= ($minQtyProductXrequired-1)) {
					$this->addNotificationMessage($cart,'notice',$this->__('Buy one more to qualify for the %1$s offer!',$productYDesc));								
					session_write_close();
				}				
        }
    // end function  
	
	}
	
	public function couponXgetYfreeCartUpdate($productYID,$couponRequired,$productYDesc,$cartTotalRequired)
    {
        //get coupon code currently applied to cart
		$cartCouponCode = $this->_getQuote()->getCouponCode();
		
		// init cart
		$cart = $this->_getCart();
		$cart->init();

		// get cart subtotal
        //$subtotal = $cart->getQuote()->getSubtotal();
		$totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
		$subtotal = $totals['subtotal']->getValue();		
		
        $productYCartItemId = null;
		$lowStockWarningAmount = 5;

		//make sure there is never more than one of product Y in cart
        foreach ($cart->getQuote()->getAllItems() as $item) {
            if ($item->getProduct()->getId() == $productYID) {
				if ($item->getQty() > 1) {
                    $item->setQty(1);
                    $cart->save();
				}
				// product y exists in cart
				$productYCartItemId = $item->getItemId();
            }
		}

		// get currency codes
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		// if current currency code does not equal base currency do a conversion
		if ($baseCurrencyCode != $currentCurrencyCode)
		{	
			// convert required Spend X cart total from base currency to current currency
			$cartTotalRequired = Mage::helper('directory')->currencyConvert($cartTotalRequired, $baseCurrencyCode, $currentCurrencyCode);
		}
		
		// check for valid coupon and cart total
        if ($subtotal >= $cartTotalRequired && strtolower($cartCouponCode) === strtolower($couponRequired)) {
		
		// use array of coupons ???
		//if ($subtotal >= $cartTotalRequired && in_array(strtolower($cartCouponCode),array_map('strtolower',$couponRequired))){

			if ($productYCartItemId == null) {
				$product = Mage::getModel('catalog/product')
					->setStoreId(Mage::app()->getStore()->getId())
					->load($productYID);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productYID);
                $qty = $stockItem->getQty();
					// check stock quantity of product Y.
						if($product->isSaleable()) {
							if ($qty <= $lowStockWarningAmount) {
								$this->sendErrorEmail('BuyXGetYFree product is at very low stock levels. There are only ' . ($qty - 1) . ' left.');
							}
							$cart->addProduct($product);
							$cart->save();
							$this->addNotificationMessage($cart,'success',$this->__('Your %1$s has been added to your cart.', $productYDesc));	
							session_write_close();
							$this->_redirect('checkout/cart');
						} else {
								if ($qty == 0) {
									$this->sendErrorEmail($product->getName(). ' '. $this->__('stock quantity is 0 and could not be added to the cart!'));
									$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));																								
									session_write_close();										
								} else {
									$this->sendErrorEmail($product->getName(). ' ' . $this->__('was not saleable and could not be added to the cart!'));
									$this->addNotificationMessage($cart,'notice',$productYDesc. ' '. $this->__('is out of stock and cannot be added to the cart!'));																								
									session_write_close();	
								}
						}
			}

		} else {
            //remove product if it is already there because the coupon code is not valid or cart total is below required value.
				if ($productYCartItemId != null) {
					$cart->removeItem($productYCartItemId);
					$cart->save();
					$this->addNotificationMessage($cart,'success',$this->__('Your %1$s has been removed from your cart.', $productYDesc));		
					session_write_close();
					$this->_redirect('checkout/cart');
				}
				// add cart notice when total below required value and coupon is present.
				if ($subtotal < $cartTotalRequired && $cartCouponCode === $couponRequired && $cartTotalRequired !=0) {
					$this->addNotificationMessage($cart,'notice',$this->__('Cart total does not qualify for this Coupon offer.'));																								
				}
        }
    // end function  	
	}	

    public function sendErrorEmail($message)
    {
		if (Mage::getStoreConfig('buyxgetyfree_section3/general/send_alert_email')) {
			$message = wordwrap($message, 70);
			$from = "buyxgetyfree@gaiterjones.com";
			$headers = "From: $from";

			mail(Mage::getStoreConfig('trans_email/ident_general/email'), 'Alert from BuyXGetYFree Extension', $message, $headers);
		}
	}
	
	public function isProductYUnique()
	{
		
		if (Mage::getStoreConfig('buyxgetyfree_section3/general/allow_duplicate_product_y'))
		{
			// do nothing, returning true here to allow duplicates will create a nasty cart loop.
		}
		
		// check product Y is unique across all arrays
		$buyProductYID = explode (",",Mage::getStoreConfig('buyxgetyfree_section1/general/producty_product_id'));
		$spendProductYID = explode (",",Mage::getStoreConfig('buyxgetyfree_section2/general/spend_producty_product_id'));	
		$couponProductYID = explode (",",Mage::getStoreConfig('buyxgetyfree_section4/general/coupon_producty_product_id'));
		$categoryProductYID = explode (",",Mage::getStoreConfig('buyxgetyfree_section5/general/category_producty_product_id'));		
		
		$result = array_merge((array)$buyProductYID, (array)$spendProductYID, (array)$couponProductYID, (array)$categoryProductYID);
		
		foreach ($result as $key=>$val )
		{ 
			if (empty($val)) unset($result[$key] ); 
		}
		if ($this->isUnique($result) == true )
		{	// product Y must be unique across all offers
			return false;
		} else {
			return true;
		}
	}
	
	public function isUnique($array)
	{
     return (array_unique($array) != $array);
	}
	 

	private function checkCustomerGroupId($_requiredGroupId)
	{
		// required group ID not configured
		if(empty($_requiredGroupId)) { return true; }

		$_requiredGroupId = explode (',',$_requiredGroupId);
		
		$_groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		
		if (in_array($_groupId, $_requiredGroupId)) {
			// group match found
			return true;
		}		

		// no group match found
		return false;
	}
	
	protected function addNotificationMessage($_cart,$_type='error',$_message)
	{
		$_messages = array_values((array)$_cart->getCheckoutSession()->getMessages());
		foreach ($_messages[0] as $_existingMessages) {
			foreach($_existingMessages as $_existingMessage) {
				$_existingMessage = array_values((array)$_existingMessage);

				if ($_existingMessage[1] == $_message) {
					// If the message is already set, stop here
					return;
				}
			}
		}		

		// clear messages
		$_cart->getCheckoutSession()->getMessages(true);
		
		if ($_type==="success") {
			$_cart->getCheckoutSession()->addSuccess($_message);
			return;
		}
		
		if ($_type==="notice") {
			$_cart->getCheckoutSession()->addNotice($_message);
			return;
		}

		$_cart->getCheckoutSession()->addError($_message);
	}
	
	
// end class	
}