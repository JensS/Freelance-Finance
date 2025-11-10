@php
    $company_name = \App\Models\Setting::get('company_name', '');
    $company_logo_path = \App\Models\Setting::get('company_logo_path');
    $pdf_font_family = \App\Models\Setting::get('pdf_font_family', 'Fira Sans');
    $pdf_font_path = \App\Models\Setting::get('pdf_font_path');
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Monatsbericht {{ $summary['period_de'] }}</title>
    <style>
        @if($pdf_font_path && file_exists(storage_path('app/' . $pdf_font_path)))
        @font-face {
            font-family: '{{ $pdf_font_family }}';
            src: url('{{ storage_path('app/' . $pdf_font_path) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: '{{ $pdf_font_family }}';
            src: url('{{ storage_path('app/' . $pdf_font_path) }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @endif

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: '{{ $pdf_font_family }}', sans-serif;
            font-weight: 300;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
        }

        .header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }

        .logo {
            width: 120px;
            height: auto;
            margin-bottom: 10px;
        }

        h1 {
            font-weight: 600;
            font-size: 18pt;
            margin-bottom: 5px;
        }

        h2 {
            font-weight: 500;
            font-size: 14pt;
            margin-top: 20px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }

        h3 {
            font-weight: 500;
            font-size: 11pt;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-label {
            display: table-cell;
            font-weight: 400;
            padding: 4px 0;
            width: 60%;
        }

        .summary-value {
            display: table-cell;
            font-weight: 400;
            text-align: right;
            padding: 4px 0;
        }

        .summary-total {
            font-weight: 600;
            font-size: 11pt;
            border-top: 1px solid #000;
            padding-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }

        thead {
            background: #f3f4f6;
        }

        th {
            font-weight: 500;
            text-align: left;
            padding: 6px 8px;
            border-bottom: 1px solid #000;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount-positive {
            color: #059669;
        }

        .amount-negative {
            color: #dc2626;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8pt;
            color: #6b7280;
            text-align: center;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
        }

        .page-break {
            page-break-after: always;
        }

        .highlight-box {
            background: #f9fafb;
            padding: 15px;
            margin: 15px 0;
            border-left: 3px solid #6366f1;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            @if($company_logo_path && file_exists(storage_path('app/public/' . $company_logo_path)))
                @php
                    $logo_content = file_get_contents(storage_path('app/public/' . $company_logo_path));
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime_type = $finfo->buffer($logo_content);
                @endphp
                @if(str_contains($mime_type, 'svg'))
                    {!! $logo_content !!}
                @else
                    <img src="{{ storage_path('app/public/' . $company_logo_path) }}">
                @endif
            @else
                <h1 style="margin: 0;">{{ $company_name }}</h1>
            @endif
        </div>
        <h1>Monatsbericht {{ $summary['period_de'] }}</h1>
        <p style="font-weight: 400; color: #6b7280;">
            Zeitraum: {{ $summary['start_date'] ? $summary['start_date']->format($dateFormat) : '' }} - {{ $summary['end_date'] ? $summary['end_date']->format($dateFormat) : '' }}
        </p>
    </div>

    <!-- Executive Summary -->
    <div class="highlight-box">
        <h2 style="margin-top: 0; border: none;">Zusammenfassung</h2>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-label">Gesamtumsatz (brutto):</div>
                <div class="summary-value">{{ number_format($summary['total_revenue'], 2, ',', '.') }} EUR</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Umsatzsteuer:</div>
                <div class="summary-value">{{ number_format($summary['total_tax'], 2, ',', '.') }} EUR</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Nettoumsatz:</div>
                <div class="summary-value">{{ number_format($summary['net_revenue'], 2, ',', '.') }} EUR</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Betriebsausgaben:</div>
                <div class="summary-value">{{ number_format($summary['business_expenses'], 2, ',', '.') }} EUR</div>
            </div>
            <div class="summary-row summary-total">
                <div class="summary-label">Gewinn:</div>
                <div class="summary-value">{{ number_format($summary['profit'], 2, ',', '.') }} EUR</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Gewinnmarge:</div>
                <div class="summary-value">{{ number_format($summary['profit_margin'], 1, ',', '.') }}%</div>
            </div>
        </div>
    </div>

    <!-- Invoice Statistics -->
    <h2>Rechnungsstatistik</h2>
    <div class="summary-grid" style="margin-bottom: 15px;">
        <div class="summary-row">
            <div class="summary-label">Anzahl ausgestellter Rechnungen:</div>
            <div class="summary-value">{{ $invoice_stats['count'] }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Durchschnittlicher Rechnungsbetrag:</div>
            <div class="summary-value">
                {{ $invoice_stats['count'] > 0 ? number_format($invoice_stats['total'] / $invoice_stats['count'], 2, ',', '.') : '0,00' }} EUR
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    @if($invoices->count() > 0)
    <h3>Rechnungsübersicht</h3>
    <table>
        <thead>
            <tr>
                <th>Rechnungsnr.</th>
                <th>Datum</th>
                <th>Kunde</th>
                <th class="text-right">Netto</th>
                <th class="text-right">MwSt.</th>
                <th class="text-right">Brutto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->issue_date ? $invoice->issue_date->format($dateFormat) : '' }}</td>
                <td>{{ $invoice->customer->name }}</td>
                <td class="text-right">{{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($invoice->tax, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($invoice->total, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr style="border-top: 2px solid #000; font-weight: 500;">
                <td colspan="3">Summe</td>
                <td class="text-right">{{ number_format($invoice_stats['subtotal'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($invoice_stats['tax'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($invoice_stats['total'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    @else
    <p>Keine Rechnungen in diesem Zeitraum.</p>
    @endif

    <div class="page-break"></div>

    <!-- Transaction Statistics -->
    <h2>Transaktionsstatistik</h2>
    <div class="summary-grid" style="margin-bottom: 15px;">
        <div class="summary-row">
            <div class="summary-label">Einnahmen ({{ $transaction_stats['income_count'] }} Transaktionen):</div>
            <div class="summary-value amount-positive">{{ number_format($transaction_stats['total_income'], 2, ',', '.') }} EUR</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Ausgaben gesamt ({{ $transaction_stats['expense_count'] }} Transaktionen):</div>
            <div class="summary-value amount-negative">{{ number_format($transaction_stats['total_expenses'], 2, ',', '.') }} EUR</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">- davon betrieblich:</div>
            <div class="summary-value">{{ number_format($transaction_stats['business_expenses'], 2, ',', '.') }} EUR</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">- davon privat:</div>
            <div class="summary-value">{{ number_format($transaction_stats['personal_expenses'], 2, ',', '.') }} EUR</div>
        </div>
    </div>

    <!-- Expenses by Category -->
    @if($expenses_by_category->count() > 0)
    <h3>Ausgaben nach Kategorie</h3>
    <table>
        <thead>
            <tr>
                <th>Kategorie</th>
                <th class="text-center">Anzahl</th>
                <th class="text-right">Betrag</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses_by_category as $category => $data)
            <tr>
                <td>{{ $category ?: 'Unkategorisiert' }}</td>
                <td class="text-center">{{ $data['count'] }}</td>
                <td class="text-right">{{ number_format($data['total'], 2, ',', '.') }} EUR</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Business Expenses Detail -->
    @if($business_expenses->count() > 0)
    <h3>Betriebsausgaben (detailliert)</h3>
    <table>
        <thead>
            <tr>
                <th>Datum</th>
                <th>Beschreibung</th>
                <th>Kategorie</th>
                <th class="text-right">Betrag</th>
            </tr>
        </thead>
        <tbody>
            @foreach($business_expenses as $expense)
            <tr>
                <td>{{ $expense->transaction_date ? $expense->transaction_date->format($dateFormat) : '' }}</td>
                <td>{{ Str::limit($expense->description, 60) }}</td>
                <td>{{ $expense->category ?: '-' }}</td>
                <td class="text-right">{{ number_format(abs($expense->amount), 2, ',', '.') }} EUR</td>
            </tr>
            @endforeach
            <tr style="border-top: 2px solid #000; font-weight: 500;">
                <td colspan="3">Summe Betriebsausgaben</td>
                <td class="text-right">{{ number_format($transaction_stats['business_expenses'], 2, ',', '.') }} EUR</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        Erstellt am {{ now()->format($dateFormat . ' H:i') }} Uhr | {{ $company_name }}
    </div>
</body>
</html>
