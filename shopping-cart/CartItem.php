<?php

declare(strict_types=1);

namespace NeonWebId\ShoppingCart;

/**
 * Class CartItem
 *
 * Represents an individual item in the shopping cart, including details
 * like item ID, name, quantity, price, and total amount.
 *
 * @package NeonWebId\ShoppingCart
 * @creator [NeonWebId] <neonwebid@gmail.com>
 * @version 1.0.0
 * @since 2024-09-12
 * @license GPL-3.0
 * @link https://neon.web.id
 */
class CartItem
{
    /**
     * @var int The ID of the item.
     */
    private int $itemId;

    /**
     * @var string The name of the item.
     */
    private string $itemName;

    /**
     * @var int The quantity of the item.
     */
    private int $qty;

    /**
     * @var float The price of the item.
     */
    private float $price;

    /**
     * Get item ID
     * @return int
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * Set item ID
     * @param int $itemId
     */
    public function setItemId(int $itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * Get item name
     * @return string
     */
    public function getItemName(): string
    {
        return $this->itemName;
    }

    /**
     * Set item name
     * @param string $itemName
     */
    public function setItemName(string $itemName): void
    {
        $this->itemName = $itemName;
    }

    /**
     * Get quantity
     * @return int
     */
    public function getQty(): int
    {
        return $this->qty;
    }

    /**
     * Set quantity
     * @param int $qty
     */
    public function setQty(int $qty): void
    {
        $this->qty = $qty;
    }

    /**
     * Get price
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Set price
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * Calculate and return total for this item (price * quantity)
     * @return float
     */
    public function getTotal(): float
    {
        return $this->price * $this->qty;
    }
}
