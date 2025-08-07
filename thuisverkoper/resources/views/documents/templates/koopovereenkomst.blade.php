<!DOCTYPE html>
<html lang="nl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Koopovereenkomst - {{ $property->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 9pt;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section h2 {
            font-size: 12pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .property-info {
            background-color: #f8fafc;
            padding: 15px;
            border-left: 4px solid #2563eb;
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 3px 15px 3px 0;
            vertical-align: top;
            width: 30%;
        }
        
        .info-value {
            display: table-cell;
            padding: 3px 0;
            vertical-align: top;
        }
        
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .party {
            display: table-cell;
            width: 50%;
            padding: 15px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .party:first-child {
            border-right: none;
        }
        
        .party h3 {
            font-size: 11pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        
        .conditions-list {
            margin: 15px 0;
        }
        
        .conditions-list ul {
            list-style-type: none;
            padding-left: 0;
        }
        
        .conditions-list li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 20px;
        }
        
        .conditions-list li:before {
            content: "•";
            color: #2563eb;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .price-summary {
            background-color: #f0f9ff;
            padding: 20px;
            border: 2px solid #2563eb;
            text-align: center;
            margin: 20px 0;
        }
        
        .price-summary .amount {
            font-size: 16pt;
            font-weight: bold;
            color: #2563eb;
        }
        
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-boxes {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 20px;
            border: 1px solid #666;
            vertical-align: top;
            min-height: 120px;
            text-align: center;
        }
        
        .signature-box:first-child {
            border-right: none;
        }
        
        .signature-box h4 {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .signature-image {
            max-width: 200px;
            max-height: 80px;
            margin: 10px auto;
        }
        
        .signature-line {
            border-top: 1px solid #666;
            width: 150px;
            margin: 60px auto 10px auto;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
        
        .legal-notice {
            background-color: #fef3c7;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
            font-size: 9pt;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @page {
            margin: 2cm 1.5cm;
            @top-left {
                content: "Koopovereenkomst - {{ $property->title }}";
                font-size: 8pt;
                color: #666;
            }
            @top-right {
                content: "Pagina " counter(page) " van " counter(pages);
                font-size: 8pt;
                color: #666;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KOOPOVEREENKOMST</h1>
        <p>Conform het Model Koopcontract voor bestaande woningen van de NVM</p>
        <p>Documentnummer: {{ str_pad($property->id, 6, '0', STR_PAD_LEFT) }}-{{ date('Ymd') }}</p>
    </div>

    <div class="property-info">
        <h2>Eigendomsgegevens</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Eigendom:</div>
                <div class="info-value">{{ $property->title }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Adres:</div>
                <div class="info-value">{{ $property->address }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Postcode/Plaats:</div>
                <div class="info-value">{{ $property->postal_code }} {{ $property->city }}</div>
            </div>
            @if(isset($property->cadastral_reference))
            <div class="info-row">
                <div class="info-label">Kadastrale aanduiding:</div>
                <div class="info-value">{{ $property->cadastral_reference }}</div>
            </div>
            @endif
            @if(isset($property->surface_area))
            <div class="info-row">
                <div class="info-label">Woonoppervlakte:</div>
                <div class="info-value">{{ number_format($property->surface_area, 0, ',', '.') }} m²</div>
            </div>
            @endif
            @if(isset($document['energy_label']))
            <div class="info-row">
                <div class="info-label">Energielabel:</div>
                <div class="info-value">{{ $document['energy_label'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Contractpartijen</h2>
        <div class="parties">
            <div class="party">
                <h3>VERKOPER</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Naam:</div>
                        <div class="info-value">{{ $user->name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">E-mail:</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                    @if(isset($user->phone))
                    <div class="info-row">
                        <div class="info-label">Telefoon:</div>
                        <div class="info-value">{{ $user->phone }}</div>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="party">
                <h3>KOPER</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Naam:</div>
                        <div class="info-value">{{ $document['buyer_name'] ?? 'Niet ingevuld' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">E-mail:</div>
                        <div class="info-value">{{ $document['buyer_email'] ?? 'Niet ingevuld' }}</div>
                    </div>
                    @if(isset($document['buyer_phone']))
                    <div class="info-row">
                        <div class="info-label">Telefoon:</div>
                        <div class="info-value">{{ $document['buyer_phone'] }}</div>
                    </div>
                    @endif
                    @if(isset($document['buyer_address']))
                    <div class="info-row">
                        <div class="info-label">Adres:</div>
                        <div class="info-value">{{ $document['buyer_address'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Financiële Bepalingen</h2>
        <div class="price-summary">
            <p><strong>KOOPPRIJS</strong></p>
            <div class="amount">€ {{ number_format($document['purchase_price'] ?? 0, 2, ',', '.') }}</div>
            <p style="margin-top: 10px;">
                <em>Zegge: {{ $this->numberToWords($document['purchase_price'] ?? 0) }} euro</em>
            </p>
        </div>

        <div class="info-grid">
            @if(isset($document['deposit_amount']) && $document['deposit_amount'] > 0)
            <div class="info-row">
                <div class="info-label">Aanbetaling:</div>
                <div class="info-value">€ {{ number_format($document['deposit_amount'], 2, ',', '.') }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Overdachtsdatum:</div>
                <div class="info-value">{{ isset($document['transfer_date']) ? \Carbon\Carbon::parse($document['transfer_date'])->format('d F Y') : 'Niet ingevuld' }}</div>
            </div>
            @if(isset($document['notary_choice']))
            <div class="info-row">
                <div class="info-label">Notariskeuze:</div>
                <div class="info-value">{{ $document['notary_choice'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Ontbindende Voorwaarden</h2>
        <div class="conditions-list">
            <ul>
                @if(isset($document['financing_condition']) && $document['financing_condition'])
                <li><strong>Financieringsvoorbehoud:</strong> Deze overeenkomst wordt aangegaan onder het voorbehoud van het verkrijgen van een hypothecaire geldlening.</li>
                @endif
                
                @if(isset($document['inspection_condition']) && $document['inspection_condition'])
                <li><strong>Bouwkundig onderzoek:</strong> Deze overeenkomst wordt aangegaan onder het voorbehoud van goedkeuring van een bouwkundig rapport.</li>
                @endif
                
                <li><strong>Eigendomsoverdracht vrij van lasten:</strong> Het eigendom wordt overgedragen vrij van alle lasten en beperkingen, met uitzondering van die welke voor koper redelijkerwijs aanvaardbaar zijn.</li>
                
                <li><strong>Juridische en administratieve zaken:</strong> Alle juridische en notariële kosten zijn voor rekening van de koper, tenzij anders overeengekomen.</li>
            </ul>
        </div>
    </div>

    @if(isset($document['special_agreements']) && !empty($document['special_agreements']))
    <div class="section">
        <h2>Bijzondere Bepalingen</h2>
        <p>{{ $document['special_agreements'] }}</p>
    </div>
    @endif

    <div class="legal-notice">
        <strong>Belangrijke mededeling:</strong> Deze koopovereenkomst is opgesteld volgens het standaard NVM-model. Partijen zijn vrij om af te wijken van de standaardbepalingen. Het is raadzaam om juridisch advies in te winnen voordat u dit contract ondertekent.
    </div>

    <div class="signature-section">
        <h2>Ondertekening</h2>
        <p>Door ondertekening van deze overeenkomst verklaren partijen akkoord te gaan met alle hierboven genoemde bepalingen en voorwaarden.</p>
        
        <div class="signature-boxes">
            <div class="signature-box">
                <h4>VERKOPER</h4>
                @if(isset($signature) && $signature)
                    <img src="{{ $signature['signature'] }}" alt="Handtekening verkoper" class="signature-image">
                    <p style="font-size: 8pt; margin-top: 10px;">
                        <strong>{{ $user->name }}</strong><br>
                        Ondertekend op: {{ isset($signed_at) ? $signed_at->format('d F Y H:i') : date('d F Y H:i') }}
                    </p>
                @else
                    <div class="signature-line"></div>
                    <p style="font-size: 8pt;">{{ $user->name }}</p>
                    <p style="font-size: 8pt;">Datum: _____________</p>
                @endif
            </div>
            
            <div class="signature-box">
                <h4>KOPER</h4>
                <div class="signature-line"></div>
                <p style="font-size: 8pt;">{{ $document['buyer_name'] ?? 'Koper' }}</p>
                <p style="font-size: 8pt;">Datum: _____________</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Dit document is automatisch gegenereerd op {{ $generated_at->format('d F Y H:i') }} via het ThuisVerkoper platform.</p>
        <p>Voor vragen over deze koopovereenkomst kunt u contact opnemen via info@thuisverkoper.nl</p>
    </div>

    @php
    function numberToWords($number) {
        $ones = array(
            '', 'een', 'twee', 'drie', 'vier', 'vijf', 'zes', 'zeven', 'acht', 'negen',
            'tien', 'elf', 'twaalf', 'dertien', 'veertien', 'vijftien', 'zestien',
            'zeventien', 'achttien', 'negentien'
        );
        $tens = array('', '', 'twintig', 'dertig', 'veertig', 'vijftig', 'zestig', 'zeventig', 'tachtig', 'negentig');
        
        if ($number < 20) {
            return $ones[$number];
        } elseif ($number < 100) {
            return $tens[intval($number / 10)] . ($number % 10 ? $ones[$number % 10] : '');
        } elseif ($number < 1000) {
            return $ones[intval($number / 100)] . 'honderd' . ($number % 100 ? ' ' . numberToWords($number % 100) : '');
        } elseif ($number < 1000000) {
            return numberToWords(intval($number / 1000)) . 'duizend' . ($number % 1000 ? ' ' . numberToWords($number % 1000) : '');
        } else {
            return number_format($number, 0, ',', '.');
        }
    }
    @endphp
</body>
</html>