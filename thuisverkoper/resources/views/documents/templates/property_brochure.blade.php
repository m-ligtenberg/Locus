<!DOCTYPE html>
<html lang="nl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Woningbrochure - {{ $property->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #333;
            background: white;
        }
        
        .cover {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-bottom: 0;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .cover h1 {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .cover .address {
            font-size: 16pt;
            margin-bottom: 15px;
        }
        
        .cover .price {
            font-size: 20pt;
            font-weight: bold;
            margin: 20px 0;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            display: inline-block;
        }
        
        .cover .tagline {
            font-size: 12pt;
            font-style: italic;
            margin-top: 20px;
        }
        
        .content {
            padding: 30px 20px;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section h2 {
            font-size: 14pt;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #667eea;
        }
        
        .highlights {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 25px;
            margin: 20px 0;
            border-radius: 15px;
            text-align: center;
        }
        
        .highlights h3 {
            font-size: 16pt;
            margin-bottom: 15px;
        }
        
        .highlight-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        
        .highlight-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
        }
        
        .highlight-number {
            font-size: 18pt;
            font-weight: bold;
            display: block;
        }
        
        .highlight-label {
            font-size: 9pt;
            margin-top: 5px;
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
            padding: 8px 20px 8px 0;
            vertical-align: top;
            width: 35%;
            color: #667eea;
        }
        
        .info-value {
            display: table-cell;
            padding: 8px 0;
            vertical-align: top;
        }
        
        .description-box {
            background-color: #f8f9ff;
            border-left: 5px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            font-style: italic;
            line-height: 1.6;
        }
        
        .features-grid {
            display: table;
            width: 100%;
        }
        
        .features-column {
            display: table-cell;
            width: 50%;
            padding: 0 15px;
            vertical-align: top;
        }
        
        .feature-item {
            margin-bottom: 8px;
            position: relative;
            padding-left: 25px;
            font-size: 10pt;
        }
        
        .feature-item:before {
            content: "✓";
            color: #4c51bf;
            font-weight: bold;
            position: absolute;
            left: 0;
            top: 0;
        }
        
        .location-section {
            background-color: #ecfdf5;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .amenities-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        
        .amenity-column {
            display: table-cell;
            width: 33.33%;
            padding: 0 10px;
            vertical-align: top;
        }
        
        .amenity-item {
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 20px;
            text-align: center;
            font-size: 9pt;
            font-weight: 500;
        }
        
        .energy-section {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            color: #065f46;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
        }
        
        .energy-label {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            font-weight: bold;
            font-size: 14pt;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .energy-a { background-color: #059669; }
        .energy-b { background-color: #65a30d; }
        .energy-c { background-color: #ca8a04; }
        .energy-d { background-color: #dc2626; }
        .energy-e { background-color: #b91c1c; }
        .energy-f { background-color: #7c2d12; }
        .energy-g { background-color: #1c1917; }
        
        .contact-section {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin: 30px 0;
        }
        
        .contact-section h3 {
            color: #9a3412;
            font-size: 16pt;
            margin-bottom: 15px;
        }
        
        .contact-info {
            color: #7c2d12;
            font-size: 11pt;
            line-height: 1.6;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @page {
            margin: 1.5cm;
            @bottom-center {
                content: "ThuisVerkoper.nl - Jouw huis, jouw verhaal";
                font-size: 8pt;
                color: #666;
            }
        }
    </style>
</head>
<body>
    <!-- Cover Page -->
    <div class="cover">
        <h1>{{ $property->title }}</h1>
        <div class="address">{{ $property->address }}</div>
        <div class="address">{{ $property->postal_code }} {{ $property->city }}</div>
        
        <div class="price">€ {{ number_format($property->price ?? 0, 0, ',', '.') }}</div>
        
        <div class="tagline">Uw droomhuis wacht op u</div>
    </div>

    <!-- Content Pages -->
    <div class="content">
        <!-- Property Highlights -->
        <div class="highlights">
            <h3>In één oogopslag</h3>
            <div class="highlight-grid">
                @if(isset($property->surface_area))
                <div class="highlight-item">
                    <span class="highlight-number">{{ number_format($property->surface_area, 0, ',', '.') }}</span>
                    <div class="highlight-label">m² woonoppervlak</div>
                </div>
                @endif
                @if(isset($property->rooms))
                <div class="highlight-item">
                    <span class="highlight-number">{{ $property->rooms }}</span>
                    <div class="highlight-label">kamers</div>
                </div>
                @endif
                @if(isset($property->bedrooms))
                <div class="highlight-item">
                    <span class="highlight-number">{{ $property->bedrooms }}</span>
                    <div class="highlight-label">slaapkamers</div>
                </div>
                @endif
                @if(isset($property->bathrooms))
                <div class="highlight-item">
                    <span class="highlight-number">{{ $property->bathrooms }}</span>
                    <div class="highlight-label">badkamers</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Description -->
        @if(isset($document['marketing_description']) || isset($property->description))
        <div class="section">
            <h2>Welkom in uw nieuwe thuis</h2>
            <div class="description-box">
                {{ $document['marketing_description'] ?? $property->description ?? 'Een prachtige woning wacht op nieuwe bewoners.' }}
            </div>
        </div>
        @endif

        <!-- Property Details -->
        <div class="section">
            <h2>Woninggegevens</h2>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Type woning:</div>
                    <div class="info-value">{{ ucfirst($property->type ?? 'Woning') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Vraagprijs:</div>
                    <div class="info-value"><strong>€ {{ number_format($property->price ?? 0, 0, ',', '.') }}</strong></div>
                </div>
                @if(isset($property->surface_area))
                <div class="info-row">
                    <div class="info-label">Woonoppervlakte:</div>
                    <div class="info-value">{{ number_format($property->surface_area, 0, ',', '.') }} m²</div>
                </div>
                @endif
                @if(isset($document['construction_year']))
                <div class="info-row">
                    <div class="info-label">Bouwjaar:</div>
                    <div class="info-value">{{ $document['construction_year'] }}</div>
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
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">{{ ucfirst($property->status ?? 'Beschikbaar') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Aanvaarding:</div>
                    <div class="info-value">In overleg</div>
                </div>
            </div>
        </div>

        <!-- Key Features -->
        @if((isset($document['key_features']) && count($document['key_features']) > 0) || (isset($property->features) && count($property->features) > 0))
        <div class="section">
            <h2>Bijzondere kenmerken</h2>
            <div class="features-grid">
                <div class="features-column">
                    @php 
                    $features = $document['key_features'] ?? $property->features ?? [];
                    $halfCount = ceil(count($features) / 2);
                    @endphp
                    @foreach(array_slice($features, 0, $halfCount) as $feature)
                    <div class="feature-item">{{ $feature }}</div>
                    @endforeach
                </div>
                <div class="features-column">
                    @foreach(array_slice($features, $halfCount) as $feature)
                    <div class="feature-item">{{ $feature }}</div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Energy Information -->
        @if(isset($document['include_energy_info']) && $document['include_energy_info'])
        <div class="energy-section">
            <h2 style="color: #065f46;">Energie & Duurzaamheid</h2>
            @if(isset($document['energy_label']))
            <p><strong>Energielabel:</strong></p>
            <div class="energy-label energy-{{ strtolower($document['energy_label']) }}">
                {{ strtoupper($document['energy_label']) }}
            </div>
            @endif
            
            @if(isset($document['heating_type']) || isset($document['insulation_details']))
            <div class="info-grid" style="margin-top: 15px;">
                @if(isset($document['heating_type']))
                <div class="info-row">
                    <div class="info-label" style="color: #065f46;">Verwarming:</div>
                    <div class="info-value">{{ $document['heating_type'] }}</div>
                </div>
                @endif
                @if(isset($document['insulation_details']))
                <div class="info-row">
                    <div class="info-label" style="color: #065f46;">Isolatie:</div>
                    <div class="info-value">{{ $document['insulation_details'] }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endif

        <!-- Location & Neighborhood -->
        <div class="section">
            <h2>Locatie & Omgeving</h2>
            <div class="location-section">
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
                        <div class="info-label">Provincie:</div>
                        <div class="info-value">{{ $property->province ?? 'Nederland' }}</div>
                    </div>
                    @if(isset($property->neighborhood))
                    <div class="info-row">
                        <div class="info-label">Wijk/Buurt:</div>
                        <div class="info-value">{{ $property->neighborhood }}</div>
                    </div>
                    @endif
                </div>

                @if(isset($document['neighborhood_info']))
                <p style="margin-top: 15px; font-style: italic;">{{ $document['neighborhood_info'] }}</p>
                @endif

                <!-- Nearby Amenities -->
                @if(isset($document['nearby_amenities']) && count($document['nearby_amenities']) > 0)
                <h3 style="margin-top: 20px; margin-bottom: 10px; color: #059669;">Voorzieningen in de buurt:</h3>
                <div class="amenities-grid">
                    @foreach(array_chunk($document['nearby_amenities'], ceil(count($document['nearby_amenities']) / 3)) as $column)
                    <div class="amenity-column">
                        @foreach($column as $amenity)
                        <div class="amenity-item">{{ $amenity }}</div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div class="page-break"></div>

        <!-- Contact Information -->
        <div class="contact-section">
            <h3>Interesse in deze woning?</h3>
            <div class="contact-info">
                <p><strong>{{ $user->name }}</strong></p>
                <p>E-mail: {{ $user->email }}</p>
                @if(isset($user->phone))
                <p>Telefoon: {{ $user->phone }}</p>
                @endif
                <p style="margin-top: 15px;">
                    <em>"Ik help u graag bij het vinden van uw droomhuis.<br>
                    Neem gerust contact met mij op voor een bezichtiging!"</em>
                </p>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="section">
            <h2>Bezichtiging & Informatie</h2>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Bezichtiging:</div>
                    <div class="info-value">Op afspraak via ThuisVerkoper platform</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Makelaar:</div>
                    <div class="info-value">Eigen verkoop (geen makelaarskosten)</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Overdracht:</div>
                    <div class="info-value">In overleg</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Aangeboden sinds:</div>
                    <div class="info-value">{{ $property->created_at->format('F Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Legal Notice -->
        <div class="section">
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; font-size: 9pt;">
                <strong>Belangrijke informatie:</strong> Deze brochure is met zorg samengesteld. Voor de juistheid van de opgegeven gegevens wordt evenwel geen garantie aanvaard. Alle maten en oppervlaktes zijn indicatief. Koper dient zelf onderzoek te verrichten naar zaken die voor hem van belang zijn. De NEN2580 is van toepassing bij de opgegeven oppervlaktes. Koper wordt geadviseerd zich door een deskundige te laten adviseren.
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>ThuisVerkoper.nl</strong> - Uw partner in eigenverkoop</p>
        <p>Deze brochure is automatisch gegenereerd op {{ $generated_at->format('d F Y') }}</p>
        <p>Voor meer informatie: info@thuisverkoper.nl</p>
    </div>
</body>
</html>