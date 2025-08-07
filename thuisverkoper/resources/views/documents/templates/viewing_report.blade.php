<!DOCTYPE html>
<html lang="nl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bezichtigingsrapport - {{ $property->title }}</title>
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
            background-color: #059669;
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
            color: #059669;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #059669;
        }
        
        .report-summary {
            background-color: #ecfdf5;
            border: 2px solid #059669;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .interest-level {
            display: inline-block;
            padding: 8px 20px;
            color: white;
            font-weight: bold;
            font-size: 12pt;
            border-radius: 20px;
            margin: 10px 0;
        }
        
        .interest-low { background-color: #dc2626; }
        .interest-medium { background-color: #ca8a04; }
        .interest-high { background-color: #2563eb; }
        .interest-very_high { background-color: #059669; }
        
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
            width: 30%;
            color: #059669;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            vertical-align: top;
        }
        
        .viewing-details {
            background-color: #f8fafc;
            padding: 15px;
            border-left: 4px solid #059669;
            margin: 15px 0;
        }
        
        .feedback-section {
            background-color: #fefce8;
            border: 1px solid #ca8a04;
            padding: 15px;
            margin: 15px 0;
        }
        
        .feedback-section h3 {
            color: #92400e;
            margin-bottom: 10px;
            font-size: 11pt;
        }
        
        .notes-section {
            background-color: #eff6ff;
            border: 1px solid #2563eb;
            padding: 15px;
            margin: 15px 0;
        }
        
        .notes-section h3 {
            color: #1d4ed8;
            margin-bottom: 10px;
            font-size: 11pt;
        }
        
        .action-items {
            background-color: #fef2f2;
            border: 1px solid #dc2626;
            padding: 15px;
            margin: 15px 0;
        }
        
        .action-items h3 {
            color: #b91c1c;
            margin-bottom: 10px;
            font-size: 11pt;
        }
        
        .action-items ul {
            list-style-type: none;
            padding-left: 0;
        }
        
        .action-items li {
            margin-bottom: 5px;
            position: relative;
            padding-left: 20px;
        }
        
        .action-items li:before {
            content: "→";
            color: #dc2626;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .property-overview {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .duration-badge {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 9pt;
            font-weight: bold;
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
                content: "Bezichtigingsrapport - {{ $property->title }}";
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
        <h1>BEZICHTIGINGSRAPPORT</h1>
        <p>{{ $property->title }}</p>
        <p>{{ $property->address }}, {{ $property->postal_code }} {{ $property->city }}</p>
    </div>

    <div class="report-summary">
        <h2>Samenvatting Bezichtiging</h2>
        <p><strong>Bezichtigingsdatum:</strong> {{ isset($document['viewing_date']) ? \Carbon\Carbon::parse($document['viewing_date'])->format('d F Y') : 'Niet ingevuld' }}</p>
        
        @if(isset($document['interest_level']))
        <p style="margin-top: 15px;"><strong>Interesse niveau:</strong></p>
        <div class="interest-level interest-{{ $document['interest_level'] }}">
            {{ ucfirst(str_replace('_', ' ', $document['interest_level'])) }}
        </div>
        @endif
        
        @if(isset($document['follow_up_required']) && $document['follow_up_required'])
        <p style="margin-top: 15px; color: #dc2626; font-weight: bold;">
            ⚠ Follow-up actie vereist
        </p>
        @endif
    </div>

    <div class="section">
        <h2>Bezichtigingsgegevens</h2>
        <div class="viewing-details">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Bezichtiger:</div>
                    <div class="info-value">{{ $document['viewer_name'] ?? 'Niet ingevuld' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">E-mail:</div>
                    <div class="info-value">{{ $document['viewer_email'] ?? 'Niet ingevuld' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Datum:</div>
                    <div class="info-value">{{ isset($document['viewing_date']) ? \Carbon\Carbon::parse($document['viewing_date'])->format('d F Y H:i') : 'Niet ingevuld' }}</div>
                </div>
                @if(isset($document['viewing_duration']))
                <div class="info-row">
                    <div class="info-label">Duur:</div>
                    <div class="info-value">
                        <span class="duration-badge">{{ $document['viewing_duration'] }} minuten</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Woninggegevens</h2>
        <div class="property-overview">
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
                    <div class="info-label">Type:</div>
                    <div class="info-value">{{ ucfirst($property->type ?? 'Niet gespecificeerd') }}</div>
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
                @if(isset($property->rooms))
                <div class="info-row">
                    <div class="info-label">Kamers:</div>
                    <div class="info-value">{{ $property->rooms }}</div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label">Verkoper:</div>
                    <div class="info-value">{{ $user->name }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($document['viewer_feedback']) && !empty($document['viewer_feedback']))
    <div class="section">
        <h2>Feedback Bezichtiger</h2>
        <div class="feedback-section">
            <h3>Opmerkingen van de bezichtiger:</h3>
            <p>{{ $document['viewer_feedback'] }}</p>
        </div>
    </div>
    @endif

    @if(isset($document['notes']) && !empty($document['notes']))
    <div class="section">
        <h2>Notities Verkoper</h2>
        <div class="notes-section">
            <h3>Interne notities:</h3>
            <p>{{ $document['notes'] }}</p>
        </div>
    </div>
    @endif

    <div class="section">
        <h2>Bezichtigingsstatistieken</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Interesse niveau:</div>
                <div class="info-value">
                    @if(isset($document['interest_level']))
                        @switch($document['interest_level'])
                            @case('low')
                                <span style="color: #dc2626;">Laag - Bezichtiger toont weinig interesse</span>
                                @break
                            @case('medium')
                                <span style="color: #ca8a04;">Gemiddeld - Bezichtiger is geïnteresseerd</span>
                                @break
                            @case('high')
                                <span style="color: #2563eb;">Hoog - Bezichtiger toont grote interesse</span>
                                @break
                            @case('very_high')
                                <span style="color: #059669;">Zeer hoog - Bezichtiger is zeer geïnteresseerd</span>
                                @break
                            @default
                                Niet beoordeeld
                        @endswitch
                    @else
                        Niet beoordeeld
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Bezichtigingsduur:</div>
                <div class="info-value">
                    @if(isset($document['viewing_duration']))
                        {{ $document['viewing_duration'] }} minuten
                        @if($document['viewing_duration'] < 15)
                            <em>(Kort bezoek)</em>
                        @elseif($document['viewing_duration'] > 45)
                            <em>(Uitgebreid bezoek)</em>
                        @else
                            <em>(Standaard bezoek)</em>
                        @endif
                    @else
                        Niet vastgelegd
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Rapport gegenereerd:</div>
                <div class="info-value">{{ $generated_at->format('d F Y H:i') }}</div>
            </div>
        </div>
    </div>

    @if(isset($document['follow_up_required']) && $document['follow_up_required'])
    <div class="section">
        <h2>Aanbevolen Acties</h2>
        <div class="action-items">
            <h3>Follow-up acties:</h3>
            <ul>
                @if(isset($document['interest_level']) && in_array($document['interest_level'], ['high', 'very_high']))
                <li>Contact opnemen binnen 24 uur voor vervolgafspraak</li>
                <li>Aanvullende documentatie verstrekken (energielabel, plattegrond, etc.)</li>
                <li>Mogelijke tweede bezichtiging plannen</li>
                @elseif(isset($document['interest_level']) && $document['interest_level'] === 'medium')
                <li>Contact opnemen binnen 48 uur</li>
                <li>Vragen van bezichtiger beantwoorden</li>
                <li>Aanvullende informatie verstrekken indien gewenst</li>
                @else
                <li>Feedback evalueren voor toekomstige bezichtigingen</li>
                <li>Mogelijk woningpresentatie aanpassen</li>
                @endif
                
                @if(isset($document['viewer_feedback']) && !empty($document['viewer_feedback']))
                <li>Specifieke feedback punt voor punt doornemen</li>
                @endif
            </ul>
        </div>
    </div>
    @endif

    <div class="section">
        <h2>Marketinganalyse</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Bezichtigingstijd:</div>
                <div class="info-value">
                    @if(isset($document['viewing_duration']))
                        @if($document['viewing_duration'] < 15)
                            <span style="color: #dc2626;">Te kort - mogelijk niet alle ruimtes bekeken</span>
                        @elseif($document['viewing_duration'] > 60)
                            <span style="color: #059669;">Uitgebreid - bezichtiger nam uitgebreid de tijd</span>
                        @else
                            <span style="color: #2563eb;">Normaal - bezichtiger heeft alles bekeken</span>
                        @endif
                    @else
                        Niet gemeten
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Interesse indicator:</div>
                <div class="info-value">
                    @if(isset($document['interest_level']))
                        @if(in_array($document['interest_level'], ['high', 'very_high']))
                            <span style="color: #059669;">✓ Positief - kans op bod</span>
                        @elseif($document['interest_level'] === 'medium')
                            <span style="color: #ca8a04;">○ Neutraal - vervolgcontact aanbevolen</span>
                        @else
                            <span style="color: #dc2626;">⚠ Laag - weinig kans op bod</span>
                        @endif
                    @else
                        Niet beoordeeld
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Aanbeveling:</div>
                <div class="info-value">
                    @if(isset($document['follow_up_required']) && $document['follow_up_required'])
                        <strong>Actief contact zoeken</strong> - bezichtiger toont voldoende interesse
                    @else
                        Standaard follow-up - bezichtiger in database houden
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Vertrouwelijk document</strong> - Dit rapport is bedoeld voor interne gebruik door de verkoper.</p>
        <p>Voor vragen over dit bezichtigingsrapport kunt u contact opnemen via {{ $user->email }}</p>
        <p>Gegenereerd via ThuisVerkoper platform op {{ $generated_at->format('d F Y H:i') }}</p>
    </div>
</body>
</html>