<?php
session_start();

require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/CartItem.php';

$customerId = 123;
$cart = new ShoppingCart\Cart($customerId);
$cart->addItem(1, 2, 200000);
$cart->addItem(2, 1, 300000);
$cart->addItem(3, 3, 400000);

$cart->setTaxRate(11);
$cart->setDiscInAmount(50000);

$cart->calculate();

echo 'Subtotal: $' . number_format($cart->getSubtotal(), 2) . PHP_EOL;


$cart->addItem(8, 2, 15000);
$cart->calculate();

print_r($cart->getCart());

echo 'Subtotal: $' . number_format($cart->getSubtotal(), 2) . PHP_EOL;