<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Transactions Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            /* line-height: 1.4; */
        }

        h1 {
            text-align: center;
            color: #333;
        }

        h2 {
            color: #444;
            /* margin-top: 30px; */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            margin-top: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        .pagenum:before {
            content: 'Página: ' counter(page);
        }
    </style>
</head>

<body>
    @php
    $header = '<div align="center">
        <h1 style="color:#326d46;">Movimientos diarios - ' . $date . '</h1>
    </div>';
    @endphp

    <div class="header">
        {!! $header !!}
    </div>

    <!-- Cash Transactions -->
    <div>
        <div style="text-align: center;">
            <h2>CAJA</h2>
        </div>

        <div align="right" style="background:transparent;width:95%;margin-left:12px;border-bottom-style:solid;">
            <b style="font-size:16px;">SALDO ANTERIOR: </b><b style="font-size:18px;">${{ number_format($previousCash, 2, ',', '.') }}</b>
        </div>

        <div style="text-align: center;">
            <h2>Ingresos</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Cuit</th>
                    <th>Proveedor</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyCashInTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">Total Ingresos: {{ number_format($totalCashIn, 2, ',', '.') }}</div>

        <h3>Egresos</h3>
        <table>
            <thead>
                <tr>
                    <th>Cuit</th>
                    <th>Proveedor</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyCashOutTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">Total Egresos: {{ number_format($totalCashOut, 2, ',', '.') }}</div>

        <div class="text-center" style="font-weight: bold; font-size: 16px; margin: 20px 0;">
            <span>
                Saldo Actual: {{ number_format($totalCash, 2, ',', '.') }}
            </span>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Bank Transactions -->
    <div class="header">
        {!! $header !!}
    </div>

    <div class="section">
        <h2>Banco</h2>

        <h3>Ingresos</h3>
        <table>
            <thead>
                <tr>
                    <th>Cuit</th>
                    <th>Proveedor</th>
                    <th>Banco</th>
                    <th>N° Operación</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyBankInTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['bankAccount']['bank']['name'] }} - {{ $transaction['treasuryVoucher']['bankAccount']['accountNumber'] }}</td>
                    <td>{{ $transaction['number'] }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">Total Ingresos: {{ number_format($totalBankIn, 2, ',', '.') }}</div>

        <h3>Egresos</h3>
        <table>
            <thead>
                <tr>
                    <th>Cuit</th>
                    <th>Proveedor</th>
                    <th>Banco</th>
                    <th>N° Operación</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyBankOutTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['bankAccount']['bank']['name'] }} - {{ $transaction['treasuryVoucher']['bankAccount']['accountNumber'] }}</td>
                    <td>{{ $transaction['number'] }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">Total Egresos: {{ number_format($totalBankOut, 2, ',', '.') }}</div>
    </div>

    <div class="page-break"></div>

    <!-- Cheque Transactions -->
    <div class="header">
        {!! $header !!}
    </div>

    <div class="section">
        <h2>Cheque</h2>

        <h3>Ingresos</h3>
        <table>
            <thead>
                <tr>
                    <th>Cuit</th>
                    <th>Proveedor</th>
                    <th>Banco</th>
                    <th>N° Operación</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyCheckInTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['bankAccount']['bank']['name'] }} - {{ $transaction['treasuryVoucher']['bankAccount']['accountNumber'] }}</td>
                    <td>{{ $transaction['number'] }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">Total Ingresos: {{ number_format($totalCheckIn, 2, ',', '.') }}</div>

        <h3>Egresos</h3>
        <table>
            <thead>
                <tr>
                    <th>Cuit</th>
                    <th>Proveedor</th>
                    <th>Banco</th>
                    <th>N° Operación</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyCheckOutTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['bankAccount']['bank']['name'] }} - {{ $transaction['treasuryVoucher']['bankAccount']['accountNumber'] }}</td>
                    <td>{{ $transaction['number'] }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">Total Egresos: {{ number_format($totalCheckOut, 2, ',', '.') }}</div>
    </div>
</body>

</html>