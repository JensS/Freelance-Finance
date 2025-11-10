@php
    $company_name = \App\Models\Setting::get('company_name', '');
    $company_address = \App\Models\Setting::get('company_address', []);
    $bank_details = \App\Models\Setting::get('bank_details', []);
    $tax_number = \App\Models\Setting::get('tax_number', '');
    $eu_vat_id = \App\Models\Setting::get('eu_vat_id', '');
    $company_logo_path = \App\Models\Setting::get('company_logo_path');
    $date_format = \App\Models\Setting::get('date_format', 'd.m.Y');

    // Load font styles
    $fontStyles = \App\Models\Setting::get('font_styles', [
        'heading' => [
            'font_family' => 'Fira Sans',
            'font_path' => null,
            'font_size' => '24px',
            'font_weight' => 'bold',
            'font_style' => 'normal',
            'color' => '#333333',
        ],
        'small_heading' => [
            'font_family' => 'Fira Sans',
            'font_path' => null,
            'font_size' => '14px',
            'font_weight' => 'bold',
            'font_style' => 'normal',
            'color' => '#333333',
        ],
        'body' => [
            'font_family' => 'Fira Sans',
            'font_path' => null,
            'font_size' => '12px',
            'font_weight' => 'normal',
            'font_style' => 'normal',
            'color' => '#333333',
        ],
    ]);
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Angebot {{ $quote->quote_number }}</title>
    <style>
        /* Heading Font */
        @if($fontStyles['heading']['font_path'] && file_exists(storage_path('app/public/' . $fontStyles['heading']['font_path'])))
        @font-face {
            font-family: '{{ $fontStyles['heading']['font_family'] }}';
            src: url('{{ storage_path('app/public/' . $fontStyles['heading']['font_path']) }}') format('truetype');
            font-weight: {{ $fontStyles['heading']['font_weight'] }};
            font-style: {{ $fontStyles['heading']['font_style'] }};
        }
        @endif

        /* Small Heading Font */
        @if($fontStyles['small_heading']['font_path'] && file_exists(storage_path('app/public/' . $fontStyles['small_heading']['font_path'])))
        @font-face {
            font-family: '{{ $fontStyles['small_heading']['font_family'] }}';
            src: url('{{ storage_path('app/public/' . $fontStyles['small_heading']['font_path']) }}') format('truetype');
            font-weight: {{ $fontStyles['small_heading']['font_weight'] }};
            font-style: {{ $fontStyles['small_heading']['font_style'] }};
        }
        @endif

        /* Body Font */
        @if($fontStyles['body']['font_path'] && file_exists(storage_path('app/public/' . $fontStyles['body']['font_path'])))
        @font-face {
            font-family: '{{ $fontStyles['body']['font_family'] }}';
            src: url('{{ storage_path('app/public/' . $fontStyles['body']['font_path']) }}') format('truetype');
            font-weight: {{ $fontStyles['body']['font_weight'] }};
            font-style: {{ $fontStyles['body']['font_style'] }};
        }
        @endif

        body {
            font-family: '{{ $fontStyles['body']['font_family'] }}', sans-serif;
            color: {{ $fontStyles['body']['color'] }};
            font-size: {{ $fontStyles['body']['font_size'] }};
            font-weight: {{ $fontStyles['body']['font_weight'] }};
            font-style: {{ $fontStyles['body']['font_style'] }};
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
        .quote-details {
            margin-bottom: 40px;
            float: right;
            text-align: right;
        }
        .quote-details table {
            border-collapse: collapse;
            width: auto;
        }
        .quote-details td {
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
            border-top: 2px solid #333;
        }
        .totals .total td {
            font-family: '{{ $fontStyles['small_heading']['font_family'] }}', sans-serif;
            font-size: {{ $fontStyles['small_heading']['font_size'] }};
            font-weight: {{ $fontStyles['small_heading']['font_weight'] }};
            font-style: {{ $fontStyles['small_heading']['font_style'] }};
            color: {{ $fontStyles['small_heading']['color'] }};
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
            font-family: '{{ $fontStyles['heading']['font_family'] }}', sans-serif;
            font-size: {{ $fontStyles['heading']['font_size'] }};
            font-weight: {{ $fontStyles['heading']['font_weight'] }};
            font-style: {{ $fontStyles['heading']['font_style'] }};
            color: {{ $fontStyles['heading']['color'] }};
            margin-bottom: 0;
        }
        strong, .small-heading, .line-items th {
            font-family: '{{ $fontStyles['small_heading']['font_family'] }}', sans-serif;
            font-size: {{ $fontStyles['small_heading']['font_size'] }};
            font-weight: {{ $fontStyles['small_heading']['font_weight'] }};
            font-style: {{ $fontStyles['small_heading']['font_style'] }};
            color: {{ $fontStyles['small_heading']['color'] }};
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
            {{ $quote->customer->name }}<br>
            @if($quote->customer->formatted_address)
                {{ $quote->customer->formatted_address }}<br>
            @endif
            @if($quote->customer->zip && $quote->customer->city)
                {{ $quote->customer->zip }} {{ $quote->customer->city }}
            @endif
        </div>

        <div class="quote-details">
            <h1>Angebot</h1>
            <table>
                <tr>
                    <td>Angebotsnummer:</td>
                    <td>{{ $quote->quote_number }}</td>
                </tr>
                <tr>
                    <td>Datum:</td>
                    <td>{{ $quote->quote_date ? $quote->quote_date->format($date_format) : '' }}</td>
                </tr>
                <tr>
                    <td>Gültig bis:</td>
                    <td>{{ $quote->valid_until ? $quote->valid_until->format($date_format) : '' }}</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        @if($quote->subject)
            <p><strong>Betreff: {{ $quote->subject }}</strong></p>
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
                @if($quote->items)
                @foreach($quote->items as $item)
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
                    <td class="text-right">{{ number_format($quote->subtotal, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>zzgl. {{ $quote->vat_rate }}% MwSt.</td>
                    <td class="text-right">{{ number_format($quote->vat_amount, 2, ',', '.') }} €</td>
                </tr>
                <tr class="total">
                    <td>Gesamtbetrag</td>
                    <td class="text-right">{{ number_format($quote->total, 2, ',', '.') }} €</td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        <div style="margin-top: 40px;">
            <p>
                Ich hoffe, dieses Angebot entspricht Ihren Vorstellungen. Bei Fragen stehe ich Ihnen gerne zur Verfügung.
            </p>
            <p>
                Mit freundlichen Grüßen
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
