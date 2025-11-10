@php
    $company_name = \App\Models\Setting::get('company_name', '');
    $company_address = \App\Models\Setting::get('company_address', []);
    $bank_details = \App\Models\Setting::get('bank_details', []);
    $tax_number = \App\Models\Setting::get('tax_number', '');
    $eu_vat_id = \App\Models\Setting::get('eu_vat_id', '');
    $company_logo_path = \App\Models\Setting::get('company_logo_path');
    $pdf_font_family = \App\Models\Setting::get('pdf_font_family', 'Fira Sans');
    $pdf_font_path = \App\Models\Setting::get('pdf_font_path');
    $date_format = \App\Models\Setting::get('date_format', 'd.m.Y');
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rechnung {{ $invoice->invoice_number }}</title>
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

        body {
            font-family: '{{ $pdf_font_family }}', sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            display: block;
            margin-bottom: 40px;
        }
        .logo {
            width: 200px;
            height: auto;
            float: left;
        }
        .logo img, .logo svg {
            width: 100%;
            height: auto;
        }
        .company-info {
            float: right;
            text-align: right;
        }
        .customer-info {
            margin-top: 20px;
            margin-bottom: 40px;
        }
        .invoice-details {
            margin-bottom: 40px;
            float: right;
            text-align: right;
        }
        .invoice-details table {
            border-collapse: collapse;
            width: auto;
        }
        .invoice-details td {
            padding: 2px 0;
        }
        .line-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .line-items th, .line-items td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .line-items th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .line-items .text-right {
            text-align: right;
        }
        .totals {
            float: right;
            width: 40%;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px;
        }
        .totals .total {
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 120px;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer table {
            width: 100%;
        }
        .footer td {
            text-align: center;
            vertical-align: top;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 0;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
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
                    <h2 style="font-size: 20px; font-weight: bold;">{{ $company_name }}</h2>
                @endif
            </div>
            <div class="company-info">
                <strong>{{ $company_name }}</strong><br>
                {{ $company_address['street'] ?? '' }}<br>
                {{ $company_address['zip'] ?? '' }} {{ $company_address['city'] ?? '' }}
            </div>
        </div>

        <div class="customer-info">
            <strong>An</strong><br>
            {{ $invoice->customer->name }}<br>
            @if($invoice->customer->formatted_address)
                {{ $invoice->customer->formatted_address }}<br>
            @endif
            @if($invoice->customer->zip && $invoice->customer->city)
                {{ $invoice->customer->zip }} {{ $invoice->customer->city }}
            @endif
        </div>

        <div class="invoice-details">
            <h1>Rechnung</h1>
            <table>
                <tr>
                    <td>Rechnungsnummer:</td>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>Rechnungsdatum:</td>
                    <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format($date_format) : '' }}</td>
                </tr>
                <tr>
                    <td>Liefer-/Leistungsdatum:</td>
                    <td>{{ $invoice->due_date ? $invoice->due_date->format($date_format) : '' }}</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        @if($invoice->subject)
            <p><strong>Betreff: {{ $invoice->subject }}</strong></p>
        @endif

        <table class="line-items">
            <thead>
                <tr>
                    <th>Beschreibung</th>
                    <th class="text-right">Menge</th>
                    <th class="text-right">Einheit</th>
                    <th class="text-right">Einzelpreis</th>
                    <th class="text-right">Gesamt</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->items)
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item['description'] }}</strong>
                        </td>
                        <td class="text-right">{{ number_format($item['quantity'], 2, ',', '.') }}</td>
                        <td class="text-right">{{ $item['unit'] }}</td>
                        <td class="text-right">{{ number_format($item['unit_price'], 2, ',', '.') }} €</td>
                        <td class="text-right">{{ number_format($item['total'], 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
                @endif
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Zwischensumme</td>
                    <td class="text-right">{{ number_format($invoice->subtotal, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>zzgl. {{ $invoice->vat_rate }}% MwSt.</td>
                    <td class="text-right">{{ number_format($invoice->vat_amount, 2, ',', '.') }} €</td>
                </tr>
                <tr class="total">
                    <td>Gesamtbetrag</td>
                    <td class="text-right">{{ number_format($invoice->total, 2, ',', '.') }} €</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        <div style="margin-top: 40px;">
            <p>
                Bitte überweisen Sie den Gesamtbetrag bis zum {{ $invoice->due_date ? $invoice->due_date->format($date_format) : '' }} auf das unten angegebene Konto.
            </p>
            <p>
                Vielen Dank für Ihren Auftrag.
            </p>
        </div>

        <div class="footer">
            <table>
                <tr>
                    <td>
                        <strong>{{ $company_name }}</strong><br>
                        {{ $company_address['street'] ?? '' }}<br>
                        {{ $company_address['zip'] ?? '' }} {{ $company_address['city'] ?? '' }}
                    </td>
                    <td>
                        <strong>Bankverbindung</strong><br>
                        IBAN: {{ $bank_details['iban'] ?? '' }}<br>
                        BIC: {{ $bank_details['bic'] ?? '' }}
                    </td>
                    <td>
                        <strong>Steuerinformationen</strong><br>
                        Steuernummer: {{ $tax_number }}<br>
                        USt-IdNr.: {{ $eu_vat_id }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

