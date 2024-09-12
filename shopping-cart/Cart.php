<?php

declare(strict_types=1);

namespace NeonWebId\ShoppingCart;

use function md5;

/**
 * Class Cart
 *
 * This class manages a shopping cart with basic functionalities such as adding, removing,
 * updating items, calculating totals, applying discounts and taxes, and storing the cart data in the session.
 *
 * @package NeonWebId\ShoppingCart
 * @creator [NeonWebId] <neonwebid@gmail.com>
 * @version 1.0.0
 * @since 2024-09-12
 * @license GPL-3.0
 * @see CartItem
 * @link https://neon.web.id
 */
class Cart
{
    /**
     * @var string The session key for storing cart data.
     */
    private string $cartSessionKey = 'cart';

    /**
     * @var array The cart data including items, discounts, taxes, and totals.
     */
    private array $cart = [
        'cart_id'                  => '',
        'customer_id'              => 0,
        'items'                    => [],
        'discount'                 => 0,
        'discount_in_percentage'   => 0,
        'subtotal_before_discount' => 0,
        'subtotal'                 => 0,
        'tax'                      => 0,
        'tax_in_percentage'        => 0,
        'total'                    => 0
    ];

    /**
     * Cart constructor.
     *
     * Initializes a cart ID based on the customer ID.
     *
     * @param int $customerId The ID of the customer.
     */
    public function __construct(int $customerId)
    {
        $cartId = md5('cart_' . $customerId);
        if (isset($_SESSION[$this->cartSessionKey][$cartId])) {
            $this->cart = $_SESSION[$this->cartSessionKey][$cartId];
        } else {
            $this->cart['cart_id']     = $cartId;
            $this->cart['customer_id'] = $customerId;
        }
    }

    /**
     * Add an item to the cart.
     *
     * If the item already exists in the cart, its quantity will be updated.
     *
     * @param int $itemId The ID of the item.
     * @param int $qty The quantity of the item. Default is 1.
     * @param float $pricePerItem The price of the item. Default is 0.
     */
    public function addItem(int $itemId, int $qty = 1, float $pricePerItem = 0): void
    {
        if (isset($this->cart['items'][$itemId])) {
            $this->cart['items'][$itemId]['qty'] += $qty;
        } else {
            $this->cart['items'][$itemId] = [
                'item_id' => $itemId,
                'qty'     => $qty,
                'price'   => $pricePerItem,
            ];
        }
    }

    /**
     * Remove an item from the cart by item ID.
     *
     * @param int $itemId The ID of the item to be removed.
     */
    public function removeItem(int $itemId): void
    {
        unset($this->cart['items'][$itemId]);
    }

    /**
     * Update the quantity of an existing item in the cart.
     *
     * @param int $itemId The ID of the item to be updated.
     * @param int $qty The new quantity for the item.
     */
    public function updateItem(int $itemId, int $qty): void
    {
        if (isset($this->cart['items'][$itemId])) {
            $this->cart['items'][$itemId]['qty'] = $qty;
        }
    }

    /**
     * Set a discount in a fixed amount.
     *
     * @param float $discount The discount amount to apply.
     */
    public function setDiscInAmount(float $discount): void
    {
        $this->cart['discount'] = $discount;
    }

    /**
     * Set a discount as a percentage of the subtotal.
     *
     * @param float $discountPercentage The discount percentage to apply.
     */
    public function setDiscInPercentage(float $discountPercentage): void
    {
        $this->cart['discount_in_percentage'] = $discountPercentage;
    }

    /**
     * Set the tax rate to be applied to the subtotal.
     *
     * @param float $taxRate The tax rate as a percentage.
     */
    public function setTaxRate(float $taxRate): void
    {
        $this->cart['tax_in_percentage'] = $taxRate;
    }

    /**
     * Calculate the subtotal, discounts, taxes, and total for the cart.
     *
     * Updates the session with the new cart totals.
     */
    public function calculate(): void
    {
        $subtotal = 0;

        foreach ($this->cart['items'] as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }

        if ($subtotal == 0) {
            return;
        }

        $this->cart['subtotal_before_discount'] = $subtotal;

        // Apply discount if applicable
        if ($this->cart['discount_in_percentage'] > 0 && $this->cart['discount'] == 0) {
            $this->cart['discount'] = $subtotal * ($this->cart['discount_in_percentage'] / 100);
        }

        $subtotalAfterDiscount  = $subtotal - $this->cart['discount'];
        $this->cart['subtotal'] = $subtotalAfterDiscount;

        if ($this->cart['discount_in_percentage'] == 0 && $this->cart['discount'] > 0) {
            $this->cart['discount_in_percentage'] = ($this->cart['discount'] / $subtotal) * 100;
        }

        // Calculate tax based on subtotal after discount
        $this->cart['tax'] = $subtotalAfterDiscount * ($this->cart['tax_in_percentage'] / 100);

        // Total calculation
        $this->cart['total'] = $subtotalAfterDiscount + $this->cart['tax'];

        // Store cart in session
        $_SESSION[$this->cartSessionKey][$this->cart['cart_id']] = $this->cart;
    }

    /**
     * Get the current cart data.
     *
     * @return array The cart data including items, discounts, taxes, and totals.
     */
    public function getCart(): array
    {
        $cart = $_SESSION[$this->cartSessionKey][$this->cart['cart_id']] ?? $this->cart;

        if ($cart['items'] !==[]) {
            $items = [];
            foreach ($cart['items'] as $item) {
                $_item = new CartItem();
                $_item->setItemId($item['item_id']);
                $_item->setQty($item['qty']);
                $_item->setPrice($item['price']);
                $items[] = $_item;
            }

            $cart['items'] = $items;
        }

        return $cart;
    }

    /**
     * Get the cart items as CartItem objects.
     *
     * @return CartItem[] An array of CartItem objects.
     */
    public function getCartItems(): array
    {
        $cart = $this->getCart();
        return $cart['items'];
    }

    /**
     * Get the tax rate applied to the cart.
     *
     * @return float The tax rate as a percentage.
     */
    public function getTaxRate(): float
    {
        return $this->cart['tax_in_percentage'];
    }

    /**
     * Get the tax amount applied to the cart.
     *
     * @return float The tax amount.
     */
    public function getTaxInAmount(): float
    {
        return $this->cart['tax'];
    }

    /**
     * Get the discount amount applied to the cart.
     *
     * @return float The discount amount.
     */
    public function getDiscInAmount(): float
    {
        return $this->cart['discount'];
    }

    /**
     * Get the discount percentage applied to the cart.
     *
     * @return float The discount percentage.
     */
    public function getDiscInPercentage(): float
    {
        return $this->cart['discount_in_percentage'];
    }

    /**
     * Get the subtotal after discounts.
     *
     * @return float The subtotal after discounts.
     */
    public function getSubtotal(): float
    {
        return $this->cart['subtotal'];
    }

    /**
     * Get the subtotal before discounts.
     *
     * @return float The subtotal before discounts are applied.
     */
    public function getSubtotalBeforDiscount(): float
    {
        return $this->cart['subtotal_before_discount'];
    }

    /**
     * Get the total amount for the cart.
     *
     * @return float The total amount.
     */
    public function getTotal(): float
    {
        return $this->cart['total'];
    }

    /**
     * Clear the cart and remove it from the session.
     */
    public function clear(): void
    {
        unset($_SESSION[$this->cartSessionKey][$this->cart['cart_id']]);
    }
}
