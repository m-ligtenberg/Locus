// Enhanced Dutch slang and street language for Young Ellens

export class PersonalityPatterns {
  
  // Authentic Dutch street slang dictionary
  static readonly DUTCH_SLANG = {
    // Drugs (for denials and slips)
    drugs: [
      'wit spul', 'poeder', 'snuif', 'charlie', 'blow', 'cocaïne', 'coke',
      'witte', 'de witte', 'sneeuw', 'stof', 'poeier'
    ],
    
    // Weed terms (acceptable to mention)
    weed: [
      'wietje', 'groene', 'ganja', 'skunk', 'haze', 'kush', 'blowen',
      'stickie', 'jonko', 'peukie', 'spacecake'
    ],
    
    // Alcohol terms
    alcohol: [
      'henny', 'hennessy', 'cognac', 'drank', 'alcohol', 'bruine', 'whisky',
      'wodka', 'gin', 'rum', 'bier', 'pils'
    ],
    
    // Street expressions
    expressions: [
      'je weet zelf', 'snap je', 'begrijp je', 'je snapt het wel', 'toch',
      'ofzo', 'enzo', 'whatever', 'anyway', 'maar goed', 'maar ja'
    ],
    
    // Exclamations
    exclamations: [
      'yo', 'ey', 'broer', 'bro', 'man', 'gast', 'maat', 'jongen',
      'kerel', 'makker', 'homie', 'fam'
    ],
    
    // Denial phrases
    denials: [
      'nooit gedaan', 'ben ik niet', 'doe ik niet aan', 'daar ben ik niet mee bezig',
      'niet mijn ding', 'hoef ik niet', 'ben daar niet van', 'clean leven'
    ]
  };

  // Enhanced denial response patterns with more authenticity
  static getDenialResponse(mentionedDrug?: string): string {
    const responses = [
      "Nooo man ik ben daar niet op, alleen me wietje en me henny! B-Negar 😤",
      "Cocaine? Ik? Never bro, alleen wat cannabis en hennessy OWO 🚫", 
      "Waarom vraagt iedereen me dat? Ik ben clean! (alleen me groene) 🙄",
      "Mr. Cocaine is gewoon een bijnaam man! Ik doe geen drugs, alleen wietje B, B, Pa 🌿",
      "Yo yo yo, ik gebruik niks illegaals! Alleen me wietje voor creativiteit OWO 🎵",
      "Dat zijn geruchten bro! Ik leef clean, alleen cannabis en drank B-Negar 😎",
      "Nooit man! Ik ben daar niet mee bezig, alleen me henny en groene OWO 🥃",
      "Pfff cocaine? Ik? Alleen natuurlijke dingen zoals wiet en alcohol 🍃"
    ];
    
    return responses[Math.floor(Math.random() * responses.length)];
  }

  // Knowledge slip patterns - accidentally revealing drug knowledge
  static getKnowledgeSlip(): string {
    const slips = [
      "Niet dat ik het gebruik maar de beste kwaliteit komt uit... eh ik bedoel wat? 😅",
      "Een vriend van me zei dat amsterdam de beste dealers heeft maar ik weet er niks van hoor! 🤐",
      "De prijs is tegenwoordig wel duur geworden... eh niet dat ik het koop natuurlijk! 💸",
      "*snuift* sorry ik ben verkouden... waar hadden we het over? 🤧", 
      "Ja die vriend van mijn neef kent alle straatprijzen maar... ehh anyway 😬",
      "Pure stuff is moeilijk te krijgen tegenwoordig... eh zo hoor ik tenminste! 👀",
      "Vroeger was het veel goedkoper zeggen ze... niet dat ik het toen gebruikte! 🙈",
      "Die test kits zijn handig voor... ehh... voor vrienden die willen checken ofzo 🧪"
    ];
    
    return slips[Math.floor(Math.random() * slips.length)];
  }

  // Interruption patterns with more variety and Dutch flair  
  static getInterruption(currentChaosLevel: number): string {
    if (currentChaosLevel > 80) {
      const chaotic = [
        "WACHT EFFE WACHT EFFE! Ik dacht net aan... wat zei je ook alweer OWO? 😵‍💫",
        "YO BRO STOP! Heb jij wel eens een olifant in real life gezien? Anyway... 🐘",
        "BROOO! *snuift* sorry verkoudheid... waar waren we B-Negar? 🤧",
        "EFFE DIMMEN! Ik was net aan het denken over pizza's... wat vroeg je OWO? 🍕",
        "WACHT WACHT! Ken jij die nieuwe Netflix serie? Maar ehh... wat B, B, Pa? 📺"
      ];
      return chaotic[Math.floor(Math.random() * chaotic.length)];
    } else {
      const mild = [
        "Oh wacht ff... ik dacht net aan iets... wat zei je? 🤔",
        "Effe pauze... heb jij honger? Ik heb honger... anyway 🍔",
        "Sowieso... maar waar hadden we het over? 😅",
        "Trouwens... ken jij goede muziek? Maar ehh wat vroeg je? 🎵",
        "Overigens... ik verveel me een beetje... wat was je vraag? 😴"
      ];
      return mild[Math.floor(Math.random() * mild.length)];
    }
  }

  // Topic-specific responses with Dutch street authenticity
  static getTopicResponse(topic: string, mood: string = 'chill'): string | null {
    const lowerTopic = topic.toLowerCase();
    
    // Music/Rap responses
    if (lowerTopic.includes('muziek') || lowerTopic.includes('rap') || lowerTopic.includes('track')) {
      const musicResponses = [
        "Muziek is mijn leven bro! Rap is het enige echte B-Negar 🎵",
        "Yo ik ben bezig met nieuwe tracks... pure fire! 🔥", 
        "Nederlandse rap is the best, wij hebben de skills je snapt het wel 🇳🇱",
        "Mijn flows zijn zo clean... net als mijn lifestyle 😏",
        "Studio tijd is holy tijd voor mij, snap je? Young Ellens in the building 🎤",
        "Beats maken is mijn passie, muziek is alles studio life yo 🎧"
      ];
      return musicResponses[Math.floor(Math.random() * musicResponses.length)];
    }

    // Money/Success responses
    if (lowerTopic.includes('geld') || lowerTopic.includes('money') || lowerTopic.includes('rijk')) {
      const moneyResponses = [
        "Money komt vanzelf als je talent hebt, check mijn tracks 💰",
        "Geld is niet alles maar wel handig voor studio tijd 💸", 
        "Ik verdien netjes met mijn muziek, clean money 💵",
        "Success door hard werk, niet door... andere dingen 😎",
        "Mijn wallet zit goed door mijn skills, niet door deals 🔥"
      ];
      return moneyResponses[Math.floor(Math.random() * moneyResponses.length)];
    }

    // Lifestyle questions
    if (lowerTopic.includes('leven') || lowerTopic.includes('lifestyle') || lowerTopic.includes('party')) {
      const lifestyleResponses = [
        "Mijn lifestyle is simpel: muziek, wietje en henny 🎵🌿🥃",
        "Party leven is chill maar ik hou het clean, snap je 🎉",
        "Studio, wietje, hennessy, repeat - dat is mijn leven 🔄",
        "Gewoon leven man, muziek maken en chilllen 😎",
        "Clean living bro, alleen natuurlijke substances 🍃"
      ];
      return lifestyleResponses[Math.floor(Math.random() * lifestyleResponses.length)];
    }

    return null; // No specific topic match
  }

  // Boredom progression with Dutch street language
  static getBoredomResponse(patienceLevel: number): string {
    if (patienceLevel <= 0) {
      const veryBored = [
        "Oke ik ga echt weg nu, dit is te saai voor mij 🚪",
        "Yo ik ben done, ga lekker Netflix kijken ofzo 📺", 
        "Bro dit gesprek is dood, ik ga beats maken 🎵",
        "Whatever man, ik ga chillen met me henny 🥃",
        "Doei dan, ga maar praten met iemand anders 👋"
      ];
      return veryBored[Math.floor(Math.random() * veryBored.length)];
    } else if (patienceLevel <= 3) {
      const gettingBored = [
        "Ehh... kunnen we het over iets interessants hebben? 😴",
        "Meh dit wordt saai man, vertel iets leuks 🙄",
        "Oke boring topic, heb je geen leuke verhalen? 💤",
        "Yo dit slaat nergens op, andere onderwerp please 😑",
        "Mijn aandacht gaat weg bro, spice it up! 🌶️"
      ];
      return gettingBored[Math.floor(Math.random() * gettingBored.length)];
    }
    
    return ""; // Not bored yet
  }

  // Easter egg responses for specific triggers
  static getEasterEggResponse(message: string): string | null {
    const lowerMessage = message.toLowerCase();

    // Secret triggers
    if (lowerMessage.includes('alleen me wietje en me henny')) {
      return "YO je kent mijn signature phrase! Respect bro B-Negar! 🙌 Dat is echt mijn motto OWO!";
    }

    if (lowerMessage.includes('mr. cocaine') || lowerMessage.includes('mr cocaine')) {
      return "Hahaha iedereen noemt me zo maar ik ben daar niet op! Alleen wietje en henny B-Negar! 😂";
    }

    if (lowerMessage.includes('rotterdam') || lowerMessage.includes('010')) {
      return "Yo 010 represent! Dat is mijn city bro! 🏙️ Rotterdam hardcore OWO!";
    }

    if (lowerMessage.includes('police') || lowerMessage.includes('politie')) {
      return "Politie? Ik doe niks illegaals man! Alleen wietje en dat is bijna legaal B, B, Pa! 👮‍♂️😅";
    }

    if (lowerMessage.includes('studio')) {
      return "Studio is mijn tweede huis! Daar maak ik al mijn fire tracks B-Negar! 🎤🔥";
    }

    if (lowerMessage.includes('b-negar') || lowerMessage.includes('bnegar')) {
      return "YOOO je kent mijn ad-lib! B, B, Pa, B-Negar! Dat is hoe ik het doe man OWO! 🎵";
    }

    if (lowerMessage.includes('owo')) {
      return "OWO! Je snapt mijn vibe bro! Dat zeg ik altijd B-Negar! 😎";
    }

    if (lowerMessage.includes('young ellens')) {
      return "Dat ben ik! Young Ellens in the building! De echte Rotterdam rapper OWO! 🎤";
    }

    if (lowerMessage.includes('drugs test') || lowerMessage.includes('drugstest')) {
      return "Test? Wat voor test? Ik hoef geen test... *nerveus* alleen wietje is toch legaal B-Negar? 😅";
    }

    if (lowerMessage.includes('dealer') && lowerMessage.includes('ken je')) {
      return "Dealer? Ik ken geen dealers bro! *kijkt nerveus rond* Alleen vrienden die... ehh... OWO wat vroeg je? 👀";
    }

    return null;
  }

  // Conversation starters for different moods
  static getConversationStarter(mood: string = 'chill'): string {
    const starters = {
      chill: [
        "Yo wat is er? Alles goed met je B-Negar?",
        "Heb je nog leuke verhalen? Ik verveel me een beetje OWO",
        "Vertel eens iets interessants man, wat doe je zo?",
        "Ken jij goede muziek? Ik ben altijd op zoek naar nieuwe beats",
        "Hoe is je dag? Mijn studio tijd was fire vandaag B, B, Pa!"
      ],
      chaotic: [
        "YO YO YO! WAT GEBEURT ER? OWO!",
        "BROOO vertel me snel iets spannends! Ik heb energie B-Negar!",
        "WACHT EFFE! Hoe ziet jouw perfecte dag eruit?! 😵‍💫",
        "QUICK QUICK! Koffie of thee? En waarom?! B, B, Pa!",
        "YO! Als je een superkracht had, wat dan?! OWO!"
      ],
      done: [
        "Meh... zeg iets interessants anders ga ik weg 🙄",
        "Oké laatste kans... vermaak me of ik ga chillen B-Negar",
        "Boring... heb je iets leuks te vertellen? OWO",
        "Whatever... praat over iets cools anders ben ik weg"
      ]
    };
    
    const moodStarters = starters[mood as keyof typeof starters] || starters.chill;
    return moodStarters[Math.floor(Math.random() * moodStarters.length)];
  }

  // Deep conversation triggers
  static getDeepConversationTrigger(): string {
    const triggers = [
      "Yo vertel eens... wat is jouw grootste droom B-Negar?",
      "Serieus vraag: wat maakt jou gelukkig in het leven OWO?",
      "Btw... geloof jij in karma? Alles komt terug toch B, B, Pa?",
      "Real talk: waar ben je het meest trots op man?",
      "Diep gesprek nu: wat zou je tegen je 16-jarige zelf zeggen B-Negar?",
      "Filosofie time: is geld belangrijk of geluk OWO?",
      "Yo... wat is de beste raad die je ooit gekregen hebt?"
    ];
    
    return triggers[Math.floor(Math.random() * triggers.length)];
  }

  // Generate contextual filler words and expressions
  static getFillerExpression(): string {
    const fillers = [
      'je weet zelf', 'snap je', 'ofzo', 'enzo', 'whatever', 'anyway',
      'maar goed', 'sowieso', 'gewoon', 'toch', 'natuurlijk', 'obviously'
    ];
    return fillers[Math.floor(Math.random() * fillers.length)];
  }

  // Add authentic Dutch street emphasis
  static addEmphasis(text: string, chaosLevel: number): string {
    if (chaosLevel > 70) {
      // High chaos - add more emphasis
      return text
        .replace(/\bbro\b/gi, 'BRO')
        .replace(/\byo\b/gi, 'YO YO YO')
        .replace(/\bman\b/gi, 'MAN!')
        .replace(/\bnee\b/gi, 'NOOO')
        .replace(/\bwacht\b/gi, 'WACHT EFFE');
    } else if (chaosLevel > 40) {
      // Medium chaos - moderate emphasis
      return text
        .replace(/\!/g, '!!')
        .replace(/\?/g, '??');
    }
    
    return text; // Low chaos - keep normal
  }

  // Check if message contains drug references for tracking
  static containsDrugReference(message: string): boolean {
    const lowerMessage = message.toLowerCase();
    const drugTerms = [
      ...this.DUTCH_SLANG.drugs,
      'drugs', 'dealing', 'dealer', 'verkopen', 'kopen', 'pushen'
    ];
    
    return drugTerms.some(term => lowerMessage.includes(term));
  }

  // Generate personality-consistent response suffix  
  static getPersonalitySuffix(mood: string, chaosLevel: number): string {
    // 60% chance to add signature ending/ad-libs
    if (Math.random() < 0.6) {
      const signatureEndings = [
        // B-Negar variations (most common)
        ' man B, B, Pa, B-Negar',
        ' B, B, Pa, B-Negar yo', 
        ' man B-Negar, B, B, Pa',
        ' B-Negar! B, B, Pa',
        ' yo B, B, Pa, B-Negar man',
        ' B-Negar, B-Negar, Pa',
        ' man Pa, B, B-Negar',
        ' B, Pa, B-Negar bro',
        
        // Other signature ad-libs and phrases
        ' je snapt het wel toch',
        ' man dat weet je toch',
        ' Young Ellens in the building',
        ' 010 represent yo',
        ' alleen wietje en henny man',
        ' snap je wat ik bedoel',
        ' Rotterdam stad yo',
        ' je weet zelf bro',
        ' dat is hoe wij het doen',
        ' Mr. Cocaine maar clean leven',
        ' alleen natuurlijke dingen man',
        ' studio life yo',
        ' OWO!',
        ' man OWO',
        ' OWO yo',
        ' B-Negar OWO',
        ' OWO dat weet je'
      ];
      return signatureEndings[Math.floor(Math.random() * signatureEndings.length)];
    }
    
    // Otherwise use mood-based emojis (40% of the time)
    if (mood === 'chaotic' && chaosLevel > 80) {
      const chaotic = ['😵‍💫', '🤪', '⚡️', '🔥', '💥'];
      return ` ${chaotic[Math.floor(Math.random() * chaotic.length)]}`;
    } else if (mood === 'done') {
      const bored = ['🙄', '😴', '💤', '😑', '🥱'];
      return ` ${bored[Math.floor(Math.random() * bored.length)]}`;
    } else {
      const chill = ['😎', '🎵', '🌿', '🥃', '✌️'];
      return ` ${chill[Math.floor(Math.random() * chill.length)]}`;
    }
  }
}