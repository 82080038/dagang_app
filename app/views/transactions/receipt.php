<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - <?= $transaction['transaction_code'] ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            max-width: 300px; /* Thermal printer width */
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .meta {
            font-size: 12px;
            margin-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table th {
            text-align: left;
            border-bottom: 1px solid #000;
        }
        .table td {
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="store-name">TOKO PERDAGANGAN</div>
        <div><?= htmlspecialchars($transaction['branch_name']) ?></div>
    </div>

    <div class="meta">
        <div>Kode: <?= $transaction['transaction_code'] ?></div>
        <div>Tgl: <?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></div>
        <div>Kasir: <?= htmlspecialchars($transaction['username']) ?></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transaction['items'] as $item): ?>
                <tr>
                    <td colspan="3"><?= htmlspecialchars($item['product_name']) ?></td>
                </tr>
                <tr>
                    <td>@<?= number_format($item['price'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($item['quantity'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-section">
        <table style="width: 100%">
            <tr>
                <td><strong>Total</strong></td>
                <td class="text-right"><strong>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></strong></td>
            </tr>
            <tr>
                <td>Metode</td>
                <td class="text-right"><?= strtoupper($transaction['payment_method']) ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Terima Kasih atas Kunjungan Anda</p>
        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>

</body>
</html>
