# 🚀 Upload Instructies voor AltStore Source

## ✅ Problemen opgelost:
1. **sourceURL** → `https://michligtenberg.nl/source/apps.json`
2. **bundleIdentifier** → `nl.michligtenberg.travelbot` (moet overeenkomen met .ipa)
3. **downloadURL** → `https://michligtenberg.nl/travelbot/releases/TravelBot-4.0.0.ipa`
4. **size** → `1136431` (echte bestandsgrootte)
5. **Unicode karakters** verwijderd (emoji's veroorzaken problemen)
6. **appPermissions** sectie verwijderd (veroorzaakt parsing errors)

## 📁 Upload structuur:

### 1. Upload altstore-source/ naar: `michligtenberg.nl/source/`
```
michligtenberg.nl/source/
├── apps.json                           ← Fixed AltStore source
├── index.html                          ← Landing page
├── icons/
│   └── travelbot-icon.png             ← App icon
└── news/
    └── travelbot-4.0-release.html     ← News page
```

### 2. Upload .ipa naar: `michligtenberg.nl/travelbot/releases/`
```
michligtenberg.nl/travelbot/releases/
└── TravelBot-4.0.0.ipa                ← De echte .ipa (1.1MB)
```

## 🔧 AltStore URL:
```
https://michligtenberg.nl/source/apps.json
```

## ✅ Test checklist:
- [ ] `https://michligtenberg.nl/source/apps.json` → Geldig JSON
- [ ] `https://michligtenberg.nl/source/icons/travelbot-icon.png` → Icon zichtbaar
- [ ] `https://michligtenberg.nl/travelbot/releases/TravelBot-4.0.0.ipa` → .ipa downloadbaar
- [ ] AltStore kan source toevoegen zonder errors

## 📱 Installatie in AltStore:
1. Open AltStore op iPhone
2. Settings → Sources → + 
3. Voeg toe: `https://michligtenberg.nl/source/apps.json`
4. Browse → TravelBot → INSTALL

**Upload beide directories en test de URL!** 🎯
