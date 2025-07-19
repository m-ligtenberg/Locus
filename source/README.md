# 📱 AltStore Source Setup voor michligtenberg.nl

## 🎯 Wat je moet uploaden naar je server:

### **Upload deze files naar `michligtenberg.nl/appstore/`:**

```
/appstore/
├── apps.json                           # AltStore source configuratie
├── index.html                          # Landing page 
├── news/
│   └── travelbot-4.0-release.html     # Release notes
├── icons/
│   └── travelbot-icon.png             # App icon (1024x1024)
├── screenshots/
│   ├── travelbot-1.png                # App screenshot 1
│   └── travelbot-2.png                # App screenshot 2
├── ipa/
│   └── TravelBot-4.0.0.ipa           # De .ipa file (van GitHub Actions)
└── news/
    └── travelbot-release.png          # News banner image
```

## 🚀 Setup Steps:

### **1. Upload bestanden**
Via FTP/cPanel upload alle bestanden uit `altstore-source/` naar je server

### **2. Voeg .ipa toe** 
- Build TravelBot.ipa via GitHub Actions
- Upload naar `michligtenberg.nl/appstore/ipa/TravelBot-4.0.0.ipa`

### **3. Voeg afbeeldingen toe**
- **App icon**: 1024x1024 PNG → `icons/travelbot-icon.png`
- **Screenshots**: iPhone screenshots → `screenshots/travelbot-1.png`, etc.
- **News banner**: 800x400 PNG → `news/travelbot-release.png`

### **4. Test de source**
Open: `https://michligtenberg.nl/appstore/`

---

## 📱 Voor gebruikers:

### **AltStore Source toevoegen:**
1. Open `https://michligtenberg.nl/appstore/` in Safari op iPhone
2. Tap "Voeg toe aan AltStore" 
3. Open AltStore → Browse tab
4. Install TravelBot!

### **Source URL:**
```
https://michligtenberg.nl/appstore/apps.json
```

---

## 🔧 Customization:

### **Update apps.json voor nieuwe versies:**
```json
{
  "version": "4.1.0",
  "date": "2025-08-01", 
  "downloadURL": "https://michligtenberg.nl/appstore/ipa/TravelBot-4.1.0.ipa"
}
```

### **Voeg meer apps toe:**
Duplicate de app entry in `apps.json` met andere `bundleIdentifier`

---

## ✅ Checklist:

- [ ] Upload `altstore-source/` bestanden naar server
- [ ] Upload .ipa naar `ipa/` folder  
- [ ] Voeg app icon (1024x1024) toe
- [ ] Voeg screenshots toe
- [ ] Test `michligtenberg.nl/appstore/` in browser
- [ ] Test AltStore source toevoegen op iPhone
- [ ] Test app installatie via AltStore

**Your personal App Store is ready! 🎉**
