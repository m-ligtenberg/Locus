# ✅ AltStore JSON Fix - MarketplaceID toegevoegd

## 🔧 **Probleem opgelost:**
**AltStore fout:** "the app is missing a marketplace id"
**Oplossing:** `marketplaceID` toegevoegd aan alle apps

## 📁 **Gefixte bestanden:**

### 1. **test.json** - Ultra simpel (PROBEER EERST DEZE!)
```json
{
  "name": "Test Store",
  "identifier": "test.store", 
  "sourceURL": "https://michligtenberg.nl/source/test.json",
  "apps": [
    {
      "name": "TravelBot",
      "bundleIdentifier": "com.travelbot.app",
      "marketplaceID": "travelbot-4.0",        ← **TOEGEVOEGD**
      "developerName": "Mich",
      "subtitle": "Test app",
      "localizedDescription": "Test description",
      "iconURL": "https://michligtenberg.nl/source/icons/travelbot-icon.png",
      "versions": [...]
    }
  ]
}
```

### 2. **apps-minimal.json** - Basis functionaliteit
- ✅ marketplaceID toegevoegd
- ✅ bundleIdentifier: com.travelbot.app
- ✅ Alle vereiste velden

### 3. **apps.json** - Volledige versie  
- ✅ marketplaceID toegevoegd
- ✅ bundleIdentifier: com.travelbot.app
- ✅ News, screenshots, etc.

## 🎯 **Test strategie:**

1. **Upload alle 3 naar `michligtenberg.nl/source/`**
2. **Test eerst:** `https://michligtenberg.nl/source/test.json`
3. **Als die werkt:** `https://michligtenberg.nl/source/apps-minimal.json` 
4. **Als die werkt:** `https://michligtenberg.nl/source/apps.json`

## ✅ **Alle JSON bestanden zijn syntactisch geldig getest!**

**De marketplaceID was het missende puzzelstukje! 🧩**
