<!DOCTYPE html>
<html lang="nl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Woninginformatie - {{ $property->title }}</title>
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
            background-color: #1e40af;
            color: white;
            padding: 20px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 10pt;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section h2 {
            font-size: 12pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #1e40af;
        }
        
        .property-overview {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
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
            padding: 5px 20px 5px 0;
            vertical-align: top;
            width: 35%;
            color: #1e40af;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            vertical-align: top;
        }
        
        .highlight-box {
            background-color: #dbeafe;
            border: 2px solid #1e40af;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }
        
        .energy-label {
            display: inline-block;
            padding: 8px 15px;
            color: white;
            font-weight: bold;
            font-size: 12pt;
            border-radius: 4px;
            margin: 5px 0;
        }
        
        .energy-a { background-color: #059669; }
        .energy-b { background-color: #65a30d; }
        .energy-c { background-color: #ca8a04; }
        .energy-d { background-color: #dc2626; }
        .energy-e { background-color: #b91c1c; }
        .energy-f { background-color: #7c2d12; }
        .energy-g { background-color: #1c1917; }
        
        .features-grid {
            display: table;
            width: 100%;
        }
        
        .features-column {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }
        
        .feature-item {
            margin-bottom: 8px;
            position: relative;
            padding-left: 20px;
        }
        
        .feature-item:before {
            content: "✓";
            color: #059669;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .taxes-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .taxes-table th,
        .taxes-table td {
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
        }
        
        .taxes-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #1e40af;
        }
        
        .taxes-table .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .location-info {
            background-color: #ecfdf5;
            border-left: 4px solid #059669;
            padding: 15px;
            margin: 15px 0;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
        
        @page {
            margin: 2cm 1.5cm;
            @top-left {
                content: "Woninginformatie - {{ $property->title }}";
                font-size: 8pt;
                color: #666;
            }
            @top-right {
                content: "Pagina " counter(page);
                font-size: 8pt;
                color: #666;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>WONINGINFORMATIE</h1>
        <p>{{ $property->title }}</p>
        <p>Gegenereerd op {{ $generated_at->format('d F Y') }}</p>
    </div>

    <div class="property-overview">
        <h2>Algemene Gegevens</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Adres:</div>
                <div class="info-value">{{ $property->address }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Postcode/Plaats:</div>
                <div class="info-value">{{ $property->postal_code }} {{ $property->city }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Type woning:</div>
                <div class="info-value">{{ ucfirst($property->type ?? 'Niet gespecificeerd') }}</div>
            </div>
            @if(isset($document['construction_year']))
            <div class="info-row">
                <div class="info-label">Bouwjaar:</div>
                <div class="info-value">{{ $document['construction_year'] }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Vraagprijs:</div>
                <div class="info-value"><strong>€ {{ number_format($property->price ?? 0, 0, ',', '.') }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($property->status ?? 'Beschikbaar') }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Oppervlaktes en Inhoud</h2>
        <div class="info-grid">
            @if(isset($document['living_area']))
            <div class="info-row">
                <div class="info-label">Woonoppervlakte:</div>
                <div class="info-value">{{ number_format($document['living_area'], 0, ',', '.') }} m²</div>
            </div>
            @endif
            @if(isset($property->surface_area))
            <div class="info-row">
                <div class="info-label">Gebruiksoppervlakte:</div>
                <div class="info-value">{{ number_format($property->surface_area, 0, ',', '.') }} m²</div>
            </div>
            @endif
            @if(isset($document['plot_size']))
            <div class="info-row">
                <div class="info-label">Perceeloppervlakte:</div>
                <div class="info-value">{{ number_format($document['plot_size'], 0, ',', '.') }} m²</div>
            </div>
            @endif
            @if(isset($property->rooms))
            <div class="info-row">
                <div class="info-label">Aantal kamers:</div>
                <div class="info-value">{{ $property->rooms }}</div>
            </div>
            @endif
            @if(isset($property->bedrooms))
            <div class="info-row">
                <div class="info-label">Slaapkamers:</div>
                <div class="info-value">{{ $property->bedrooms }}</div>
            </div>
            @endif
            @if(isset($property->bathrooms))
            <div class="info-row">
                <div class="info-label">Badkamers:</div>
                <div class="info-value">{{ $property->bathrooms }}</div>
            </div>
            @endif
        </div>
    </div>

    @if(isset($document['energy_label']) || isset($document['heating_type']) || isset($document['insulation_details']))
    <div class="section">
        <h2>Energie en Milieu</h2>
        
        @if(isset($document['energy_label']))
        <div class="highlight-box">
            <p><strong>Energielabel:</strong></p>
            <span class="energy-label energy-{{ strtolower($document['energy_label']) }}">
                {{ strtoupper($document['energy_label']) }}
            </span>
        </div>
        @endif

        <div class="info-grid">
            @if(isset($document['heating_type']))
            <div class="info-row">
                <div class="info-label">Verwarming:</div>
                <div class="info-value">{{ $document['heating_type'] }}</div>
            </div>
            @endif
            @if(isset($document['insulation_details']))
            <div class="info-row">
                <div class="info-label">Isolatie:</div>
                <div class="info-value">{{ $document['insulation_details'] }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if(isset($property->features) && is_array($property->features) && count($property->features) > 0)
    <div class="section">
        <h2>Bijzonderheden</h2>
        <div class="features-grid">
            <div class="features-column">
                @foreach(array_slice($property->features, 0, ceil(count($property->features) / 2)) as $feature)
                <div class="feature-item">{{ $feature }}</div>
                @endforeach
            </div>
            <div class="features-column">
                @foreach(array_slice($property->features, ceil(count($property->features) / 2)) as $feature)
                <div class="feature-item">{{ $feature }}</div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(isset($document['municipal_taxes']) || isset($document['water_board_taxes']) || isset($document['service_costs']))
    <div class="section">
        <h2>Financiële Lasten (per jaar)</h2>
        <table class="taxes-table">
            <thead>
                <tr>
                    <th>Kostenpost</th>
                    <th>Bedrag</th>
                    <th>Toelichting</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($document['municipal_taxes']) && $document['municipal_taxes'] > 0)
                <tr>
                    <td>Gemeentelijke belastingen</td>
                    <td class="amount">€ {{ number_format($document['municipal_taxes'], 2, ',', '.') }}</td>
                    <td>OZB, rioolheffing, afvalstoffenheffing</td>
                </tr>
                @endif
                @if(isset($document['water_board_taxes']) && $document['water_board_taxes'] > 0)
                <tr>
                    <td>Waterschapsbelasting</td>
                    <td class="amount">€ {{ number_format($document['water_board_taxes'], 2, ',', '.') }}</td>
                    <td>Zuiveringsheffing, waterkeringsheffing</td>
                </tr>
                @endif
                @if(isset($document['service_costs']) && $document['service_costs'] > 0)
                <tr>
                    <td>Servicekosten</td>
                    <td class="amount">€ {{ number_format($document['service_costs'], 2, ',', '.') }}</td>
                    <td>VvE-bijdrage, onderhoud gemeenschappelijke delen</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    <div class="section">
        <h2>Locatie en Buurt</h2>
        <div class="location-info">
            <p><strong>Ligging:</strong> {{ $property->city }} is gelegen in {{ $property->province ?? 'Nederland' }} en biedt uitstekende voorzieningen voor bewoners.</p>
        </div>
        
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Provincie:</div>
                <div class="info-value">{{ $property->province ?? 'Niet gespecificeerd' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Gemeente:</div>
                <div class="info-value">{{ $property->municipality ?? $property->city }}</div>
            </div>
            @if(isset($property->neighborhood))
            <div class="info-row">
                <div class="info-label">Wijk/Buurt:</div>
                <div class="info-value">{{ $property->neighborhood }}</div>
            </div>
            @endif
        </div>
    </div>

    @if(isset($property->description) && !empty($property->description))
    <div class="section">
        <h2>Omschrijving</h2>
        <p>{{ $property->description }}</p>
    </div>
    @endif

    <div class="section">
        <h2>Juridische Gegevens</h2>
        <div class="info-grid">
            @if(isset($property->cadastral_reference))
            <div class="info-row">
                <div class="info-label">Kadastrale aanduiding:</div>
                <div class="info-value">{{ $property->cadastral_reference }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Eigendom:</div>
                <div class="info-value">Volle eigendom</div>
            </div>
            <div class="info-row">
                <div class="info-label">Lasten:</div>
                <div class="info-value">Vrij van lasten en beperkingen</div>
            </div>
            <div class="info-row">
                <div class="info-label">Aanvaarding:</div>
                <div class="info-value">In overleg</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Contact en Bezichtiging</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Verkoper:</div>
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
            <div class="info-row">
                <div class="info-label">Bezichtiging:</div>
                <div class="info-value">Op afspraak via ThuisVerkoper platform</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Belangrijke informatie:</strong></p>
        <p>Deze woninginformatie is met zorg samengesteld, maar voor de juistheid en volledigheid van de gegevens wordt geen garantie gegeven.</p>
        <p>Alle genoemde maten, oppervlaktes en inhouden zijn indicatief. Koper wordt geadviseerd eigen onderzoek te verrichten.</p>
        <p>Dit document is automatisch gegenereerd op {{ $generated_at->format('d F Y H:i') }} via het ThuisVerkoper platform.</p>
    </div>
</body>
</html>