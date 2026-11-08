<?php

const TAX_RATE = 0.07;

$customerName = "Arjay Kun";
$productName = "Wireless Mouse";

$quantity = 2;
$unitPrice = 50.00;

$discountInput = "10.50";
$discount = (float) $discountInput;

$isShippingPaid = false;

$subtotal = $quantity * $unitPrice;
$tax = $subtotal * TAX_RATE;
$total = $subtotal + $tax - $discount;

if (!$isShippingPaid) {
    $total += 5.00;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Final Reciept</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 40px;
        }

        .invoice {
            width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .total {
            border-top: 2px solid #333;
            padding-top: 15px;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="invoice">
    <h1>Final Reciept</h1>

    <p><strong>Customer:</strong>
        <?= strtoupper($customerName) ?>
    </p>

    <p><strong>Product:</strong>
        <?= $productName ?>
    </p>

    <div class="row">
        <span>Quantity</span>
        <span><?= $quantity ?></span>
    </div>

    <div class="row">
        <span>Unit Price</span>
        <span>$<?= number_format($unitPrice, 2) ?></span>
    </div>

    <hr>

    <div class="row">
        <span>Subtotal</span>
        <span>$<?= number_format($subtotal, 2) ?></span>
    </div>

    <div class="row">
        <span>Tax (7%)</span>
        <span>$<?= number_format($tax, 2) ?></span>
    </div>

    <div class="row">
        <span>Discount</span>
        <span>-$<?= number_format($discount, 2) ?></span>
    </div>

    <?php if (!$isShippingPaid): ?>
        <div class="row">
            <span>Shipping</span>
            <span>$5.00</span>
        </div>
    <?php endif; ?>

    <div class="row total">
        <span>FINAL TOTAL</span>
        <span>$<?= number_format($total, 2) ?></span>
    </div>
</div>

</body>
</html>