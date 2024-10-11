<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos diarios - {{ $date }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
            color: #0e7490;
            margin: 0.5rem;
        }

        h2 {
            color: #71717a;
            margin: 0.5rem;
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
            border: 0;
            border-bottom: 1px solid #cbd5e1;
        }

        th {
            background-color: #f8fafc;
            color: #334155;
            border: 0;
        }

        .section-title {
            text-align: center;
            border-top: 1.25px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .text-left {
            text-align: left;
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
    </style>
</head>

<body>
    @php
    $header = '
    <table style="width: 100%;">
        <tr>
            <td style="text-align: left;border: 0; padding: 0px;width: 20%;"><b>Página: {PAGENO}/{nbpg}</b></td>
            <td style="text-align: center;border: 0; padding: 0px;width: 60%;">
                <b style="font-size: 22px;color: #326d46;">MOVIMIENTOS DIARIOS - ' . $date . '</b>
            </td>
            <td style="text-align: right;border: 0; padding: 0px;width: 20%;"><b>' . date('d/m/Y H:i:s') . '</b></td>
        </tr>
    </table>';
    @endphp

    <div class="header">
        {!! $header !!}
    </div>

    <!-- Cash Transactions -->
    <div class="section">
        <div class="section-title">
            <h2>CAJA</h2>
        </div>

        <div align="center" style="font-size:16px; color: #334155; border-bottom: 1px solid #000000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
            <b>SALDO ANTERIOR: </b><b style="font-size:18px;">${{ number_format($previousCash, 2, ',', '.') }}</b>
        </div>

        <div style="text-align: center; margin-bottom: 0.75rem;">
            <span style="font-size:15px;font-weight: bold; color: #334155;">INGRESOS</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th class="text-left" style="width: 20%;">CUIT</th>
                    <th class="text-center" style="width: 60%;">PROVEEDOR</th>
                    <th class="text-right" style="width: 20%;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyCashInTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td class="text-center">{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">TOTAL INGRESOS: ${{ number_format($totalCashIn, 2, ',', '.') }}</div>

        <div style="text-align: center;border-top: 1.25px solid #000000; padding-top: 2rem; margin: 0.75rem 0;">
            <span style="font-size:15px;font-weight: bold; color: #334155;">EGRESOS</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th class="text-left" style="width: 20%;">CUIT</th>
                    <th class="text-center" style="width: 60%;">PROVEEDOR</th>
                    <th class="text-right" style="width: 20%;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyCashOutTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td class="text-center">{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">TOTAL EGRESOS: ${{ number_format($totalCashOut, 2, ',', '.') }}</div>

        <div align="center" style="font-size:16px; border-top: 1.25px solid #000000; padding-top: 0.75rem; margin-top: 0.75rem;">
            <b>SALDO ACTUAL: </b><b style="font-size:18px;">${{ number_format($totalCash, 2, ',', '.') }}</b>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Bank Transactions -->
    <div class="header">
        {!! $header !!}
    </div>

    <div class="section">
        <div class="section-title" style="border-bottom: 1px solid #000000;">
            <h2>BANCO</h2>
        </div>

        <div style="text-align: center; margin-bottom: 0.75rem;">
            <span style="font-size:15px;font-weight: bold; color: #334155;">INGRESOS</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12.5%;">CUIT</th>
                    <th style="width: 30%;">PROVEEDOR</th>
                    <th style="width: 30%;">BANCO</th>
                    <th style="width: 12.5%;">N° OPERACIÓN</th>
                    <th class="text-right" style="width: 15%;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyBankInTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['bankAccount']['bank']['name'] }} - {{ $transaction['treasuryVoucher']['bankAccount']['accountNumber'] }}</td>
                    <td>{{ strtoupper($transaction['number']) }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">TOTAL INGRESOS: ${{ number_format($totalBankIn, 2, ',', '.') }}</div>

        <div style="text-align: center;border-top: 1.25px solid #000000; padding-top: 2rem; margin: 0.75rem 0;">
            <span style="font-size:15px;font-weight: bold; color: #334155;">EGRESOS</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12.5%;">CUIT</th>
                    <th style="width: 30%;">PROVEEDOR</th>
                    <th style="width: 30%;">BANCO</th>
                    <th style="width: 12.5%;">N° OPERACIÓN</th>
                    <th class="text-right" style="width: 15%;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyBankOutTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['bankAccount']['bank']['name'] }} - {{ $transaction['treasuryVoucher']['bankAccount']['accountNumber'] }}</td>
                    <td>{{ strtoupper($transaction['number']) }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right" style="border-bottom: 1.25px solid #000000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">TOTAL EGRESOS: ${{ number_format($totalBankOut, 2, ',', '.') }}</div>
    </div>

    <div class="page-break"></div>

    <!-- Cheque Transactions -->
    <div class="header">
        {!! $header !!}
    </div>

    <div class="section">
        <div class="section-title" style="border-bottom: 1px solid #000000;">
            <h2>CHEQUE</h2>
        </div>

        <div style="text-align: center; margin-bottom: 0.75rem;">
            <span style="font-size:15px;font-weight: bold; color: #334155;">INGRESOS</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12.5%;">CUIT</th>
                    <th style="width: 30%;">PROVEEDOR</th>
                    <th style="width: 30%;">BANCO</th>
                    <th style="width: 12.5%;">N° OPERACIÓN</th>
                    <th class="text-right" style="width: 15%;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyCheckInTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['bankAccount']['bank']['name'] }} - {{ $transaction['treasuryVoucher']['bankAccount']['accountNumber'] }}</td>
                    <td>{{ strtoupper($transaction['number']) }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right">TOTAL INGRESOS: ${{ number_format($totalCheckIn, 2, ',', '.') }}</div>

        <div style="text-align: center;border-top: 1.25px solid #000000; padding-top: 2rem; margin: 0.75rem 0;">
            <span style="font-size:15px;font-weight: bold; color: #334155;">EGRESOS</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12.5%;">CUIT</th>
                    <th style="width: 30%;">PROVEEDOR</th>
                    <th style="width: 30%;">BANCO</th>
                    <th style="width: 12.5%;">N° OPERACIÓN</th>
                    <th class="text-right" style="width: 15%;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyCheckOutTransactions as $transaction)
                <tr>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['cuit'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['supplier']['businessName'] }}</td>
                    <td>{{ $transaction['treasuryVoucher']['bankAccount']['bank']['name'] }} - {{ $transaction['treasuryVoucher']['bankAccount']['accountNumber'] }}</td>
                    <td>{{ strtoupper($transaction['number']) }}</td>
                    <td class="text-right">${{ number_format($transaction['treasuryVoucher']['totalAmount'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #ef4444;">Sin movimientos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="total text-right" style="border-bottom: 1.25px solid #000000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">TOTAL EGRESOS: ${{ number_format($totalCheckOut, 2, ',', '.') }}</div>
    </div>
</body>

</html>