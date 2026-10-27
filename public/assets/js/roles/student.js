// Laravel synchronisatie: opgeslagen gebruikersgegevens worden bij het openen ingeladen.
const persistentKeys = ["ontwikkeling-reflecties", "ontwikkeling-feedback", "archived-learning-outcomes", "hours-processing-2026-09-12-read"];
if (window.LARAVEL_USER) {
  for (const [key, value] of Object.entries(window.LARAVEL_STATE || {})) {
    if (typeof key === "string" && (persistentKeys.includes(key) || key.includes("processing"))) {
      localStorage.setItem("laravel-" + window.LARAVEL_USER.id + "-" + key, JSON.stringify(value));
    }
  }
}
function loadLocalState(key) {
  if (!window.LARAVEL_USER) return localStorage.getItem(key);
  return localStorage.getItem("laravel-" + window.LARAVEL_USER.id + "-" + key);
}
let syncTimer;
function saveLocalState(key, value) {
  if (!window.LARAVEL_USER) return;
  localStorage.setItem("laravel-" + window.LARAVEL_USER.id + "-" + key, value);
  clearTimeout(syncTimer);
  syncTimer = setTimeout(() => {
    const data = {};
    for (const key of persistentKeys) {
      const raw = loadLocalState(key);
      if (raw !== null) {
        try {
          data[key] = JSON.parse(raw);
        } catch {
          data[key] = raw;
        }
      }
    }
    fetch(window.LARAVEL_SAVE_URL, {
      method: "PUT",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": window.LARAVEL_CSRF,
      },
      body: JSON.stringify({ data }),
    })
      .then((response) => {
        if (!response.ok) throw Error("Opslaan mislukt");
      })
      .catch(() => {
        const x = document.getElementById("hint");
        if (x) {
          x.textContent = "Opslaan op de server is mislukt. Probeer het opnieuw.";
          x.classList.remove("hidden");
        }
      });
  }, 250);
}
// -----------------------------------------------------------------------------
// Vaste demo- en leeruitkomstgegevens
// -----------------------------------------------------------------------------
const outcomes = [
  {
    code: "IT_OF_LU_Hardskills_001",
    phase: "OF",
    kind: "Hardskills",
    number: 1,
    description:
      "Na de oriëntatiefase kan ik eenvoudige taken uitvoeren binnen mijn vakgebied, zoals het installeren, plannen of configureren van systemen, software of processen. Dit doe ik onder begeleiding van mijn docent of begeleider.",
    pct: 78,
    hours: 117,
    target: 150,
    short: "Eenvoudige taken uitvoeren",
  },
  {
    code: "IT_OF_LU_Hardskills_002",
    phase: "OF",
    kind: "Hardskills",
    number: 2,
    description:
      "Na de oriëntatiefase kan ik afwijkingen en problemen signaleren en deze zelfstandig of in overleg met mijn begeleider of team bespreken. Ik heb, met ondersteuning, geleerd hoe ik hierover kan rapporteren.",
    pct: 62,
    hours: 93,
    target: 150,
    short: "Problemen signaleren",
  },
  {
    code: "IT_OF_LU_Hardskills_003",
    phase: "OF",
    kind: "Hardskills",
    number: 3,
    description:
      "Na de oriëntatiefase heb ik kennis gekregen van basisprincipes voor technische ontwerpen of configuraties en kan ik onder begeleiding meewerken aan eenvoudige oplossingen.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 003",
  },
  {
    code: "IT_OF_LU_Hardskills_004",
    phase: "OF",
    kind: "Hardskills",
    number: 4,
    description:
      "Na de oriëntatiefase heb ik geleerd samen te werken in een team en duidelijk en effectief te communiceren, met ondersteuning van mijn begeleider waar nodig.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 004",
  },
  {
    code: "IT_OF_LU_Hardskills_005",
    phase: "OF",
    kind: "Hardskills",
    number: 5,
    description:
      "Na de oriëntatiefase kan ik de voortgang van mijn werk of taken bijhouden en hierover gestructureerd rapporteren in overleg met mijn begeleider.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 005",
  },
  {
    code: "IT_OF_LU_Hardskills_006",
    phase: "OF",
    kind: "Hardskills",
    number: 6,
    description:
      "Na de oriëntatiefase kan ik tools en technieken gebruiken om de kwaliteit van mijn werk te verbeteren. Ik kan mijn keuzes en resultaten bespreken met mijn begeleider.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 006",
  },
  {
    code: "IT_OF_LU_Hardskills_007",
    phase: "OF",
    kind: "Hardskills",
    number: 7,
    description:
      "Na de oriëntatiefase heb ik geleerd veilige werkprocedures volgen en er onder begeleiding voor zorgen dat mijn werk voldoet aan de geldende normen en regels.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 007",
  },
  {
    code: "IT_OF_LU_Hardskills_008",
    phase: "OF",
    kind: "Hardskills",
    number: 8,
    description:
      "Na de oriëntatiefase kan ik mijn keuzes en acties in mijn werk toelichten en deze onder begeleiding onderbouwen aan mijn begeleider, team of opdrachtgever.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 008",
  },
  {
    code: "IT_OF_LU_Softskills_001",
    phase: "OF",
    kind: "Softskills",
    number: 1,
    description:
      "Na de oriëntatiefase kan ik een kritische en analytische houding tonen bij het gebruik van technologie. Ik kan onder begeleiding problemen oplossen en mijn leerproces evalueren.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 001",
  },
  {
    code: "IT_OF_LU_Softskills_002",
    phase: "OF",
    kind: "Softskills",
    number: 2,
    description:
      "Na de oriëntatiefase heb ik geleerd om creatieve denkvaardigheden in te zetten om innovatieve oplossingen te bedenken in verschillende technische en niet-technische situaties, met begeleiding en ondersteuning waar nodig.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 002",
  },
  {
    code: "IT_OF_LU_Softskills_003",
    phase: "OF",
    kind: "Softskills",
    number: 3,
    description:
      "Na de oriëntatiefase kan ik samenwerken in verschillende teams, actief bijdragen aan groepsprocessen en onder begeleiding reflecteren op de samenwerking binnen het team.",
    pct: 45,
    hours: 68,
    target: 150,
    short: "Samenwerken en reflecteren",
  },
  {
    code: "IT_OF_LU_Softskills_004",
    phase: "OF",
    kind: "Softskills",
    number: 4,
    description:
      "Na de oriëntatiefase heb ik geleerd duidelijk en doelgericht te communiceren over technische en niet-technische onderwerpen en onder begeleiding feedback te geven en te ontvangen in teams.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 004",
  },
  {
    code: "IT_OF_LU_Softskills_005",
    phase: "OF",
    kind: "Softskills",
    number: 5,
    description:
      "Na de oriëntatiefase heb ik geleerd hoe ik problemen kan oplossen en keuzes kan maken door analytisch te denken en prioriteiten te stellen. Onder begeleiding heb ik geleerd om verantwoordelijkheid te nemen voor mijn beslissingen.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 005",
  },
  {
    code: "IT_OF_LU_Softskills_006",
    phase: "OF",
    kind: "Softskills",
    number: 6,
    description:
      "Na de oriëntatiefase heb ik leren omgaan met uitdagingen en flexibel te blijven wanneer iets niet meteen lukt. Met ondersteuning heb ik geleerd geduldig en doelgericht te blijven werken aan lastige problemen.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 006",
  },
  {
    code: "IT_OF_LU_Softskills_007",
    phase: "OF",
    kind: "Softskills",
    number: 7,
    description:
      "Na de oriëntatiefase heb ik geleerd hoe belangrijk het is om ethisch en verantwoord om te gaan met digitale gegevens en onder begeleiding bij kan dragen aan veilige en privacy bewuste oplossingen.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 007",
  },
  {
    code: "IT_OF_LU_Softskills_008",
    phase: "OF",
    kind: "Softskills",
    number: 8,
    description:
      "Na de oriëntatiefase kan ik een nieuwsgierige en lerende houding tonen. Onder begeleiding kan ik reflecteren op mijn ontwikkeling en bouw ik aan mijn doorzettingsvermogen bij uitdagingen.",
    pct: 53,
    hours: 64,
    target: 120,
    short: "Lerende houding",
  },
  {
    code: "IT_VF_LU_Hardskills_001",
    phase: "VF",
    kind: "Hardskills",
    number: 1,
    description:
      "Na de verdiepende fase kan ik complexe taken uitvoeren binnen mijn vakgebied, zoals het installeren, plannen of configureren van systemen, software of processen. Dit doe ik onder beperkte begeleiding van mijn docent of begeleider.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 001",
  },
  {
    code: "IT_VF_LU_Hardskills_002",
    phase: "VF",
    kind: "Hardskills",
    number: 2,
    description:
      "Na de verdiepende fase kan ik afwijkingen en problemen signaleren en deze gestructureerd en logisch analyseren, met beperkte ondersteuning waar nodig.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 002",
  },
  {
    code: "IT_VF_LU_Hardskills_003",
    phase: "VF",
    kind: "Hardskills",
    number: 3,
    description:
      "Na de verdiepende fase kan ik complexe principes van technische ontwerpen of configuraties toepassen en onder beperkte begeleiding meewerken aan de oplevering van ingewikkelde oplossingen.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 003",
  },
  {
    code: "IT_VF_LU_Hardskills_004",
    phase: "VF",
    kind: "Hardskills",
    number: 4,
    description:
      "Na de verdiepende fase kan ik samenwerken in een team en duidelijk en effectief communiceren, met beperkte ondersteuning van mijn begeleider waar nodig.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 004",
  },
  {
    code: "IT_VF_LU_Hardskills_005",
    phase: "VF",
    kind: "Hardskills",
    number: 5,
    description:
      "Na de verdiepende fase kan ik systemen, netwerken en applicaties beheren en beveiligen. Ik pas cybersecurityprincipes toe en neem preventieve maatregelen tegen digitale dreigingen, onder beperkte begeleiding en in overleg.",
    pct: 66,
    hours: 118,
    target: 180,
    short: "Beheren en beveiligen",
  },
  {
    code: "IT_VF_LU_Hardskills_006",
    phase: "VF",
    kind: "Hardskills",
    number: 6,
    description:
      "Na de verdiepende fase kan ik data analyseren en interpreteren. Ik gebruik deze inzichten om verbeteringen voor te stellen binnen processen, applicaties of systemen en maak keuzes en resultaten bespreekbaar, onder beperkte begelei [De aangeleverde PDF breekt deze zin hier af.]",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 006",
  },
  {
    code: "IT_VF_LU_Hardskills_007",
    phase: "VF",
    kind: "Hardskills",
    number: 7,
    description:
      "Na de verdiepende fase kan ik efficiënt werken met moderne tools en technieken om mijn werkwijze en samenwerking te optimaliseren. Ik pas deze toe en verbeter mijn werkwijze onder beperkte begeleiding.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 007",
  },
  {
    code: "IT_VF_LU_Hardskills_008",
    phase: "VF",
    kind: "Hardskills",
    number: 8,
    description:
      "Na de verdiepende fase kan ik mijn eigen werk toetsen en verbeteren. Ik test mijn resultaten en verwerk feedback in herzieningen, met beperkte begeleiding van mijn begeleider, team of opdrachtgever.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 008",
  },
  {
    code: "IT_VF_LU_Softskills_001",
    phase: "VF",
    kind: "Softskills",
    number: 1,
    description:
      "Na de verdiepende fase kan ik zelfstandig mijn keuzes en acties onderbouwen op basis van argumentatie en feedback. Ik pas mijn werkwijze aan op basis van reflectie en input van collega’s, opdrachtgevers en stakeholders.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 001",
  },
  {
    code: "IT_VF_LU_Softskills_002",
    phase: "VF",
    kind: "Softskills",
    number: 2,
    description:
      "Na de verdiepende fase kan ik kritisch en analytisch denken toepassen bij het beoordelen van technologische vraagstukken en zelfstandig problemen oplossen. Ik evalueer mijn leerproces en pas inzichten toe om mijn werkwijze te verbeteren.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 002",
  },
  {
    code: "IT_VF_LU_Softskills_003",
    phase: "VF",
    kind: "Softskills",
    number: 3,
    description:
      "Na de verdiepende fase kan ik creatieve denkvaardigheden zelfstandig toepassen en innovatieve oplossingen ontwikkelen voor technische en niet-technische uitdagingen. Ik experimenteer met nieuwe methodes en technologieën om process [De aangeleverde PDF breekt deze zin hier af.]",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 003",
  },
  {
    code: "IT_VF_LU_Softskills_004",
    phase: "VF",
    kind: "Softskills",
    number: 4,
    description:
      "Na de verdiepende fase kan ik helder en doelgericht communiceren over technische en niet-technische onderwerpen en mijn boodschap afstemmen op mijn publiek. Ik geef en ontvang constructieve feedback en verwerk deze in mijn werk.",
    pct: 57,
    hours: 103,
    target: 180,
    short: "Doelgericht communiceren",
  },
  {
    code: "IT_VF_LU_Softskills_005",
    phase: "VF",
    kind: "Softskills",
    number: 5,
    description:
      "Na de verdiepende fase kan ik effectief samenwerken in multidisciplinaire teams en actief bijdragen aan een productieve werksfeer. Ik reflecteer zelfstandig op groepsprocessen en stuur mijn rol in het team bij waar nodig.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 005",
  },
  {
    code: "IT_VF_LU_Softskills_006",
    phase: "VF",
    kind: "Softskills",
    number: 6,
    description:
      "Na de verdiepende fase kan ik veerkracht en flexibiliteit tonen bij uitdagingen en doelgericht blijven werken, ook bij tegenslagen. Ik zoek zelfstandig naar oplossingen en pas mijn aanpak aan wanneer nodig.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 006",
  },
  {
    code: "IT_VF_LU_Softskills_007",
    phase: "VF",
    kind: "Softskills",
    number: 7,
    description:
      "Na de verdiepende fase kan ik een proactieve en lerende houding tonen door nieuwe kennis en vaardigheden actief te ontwikkelen. Ik reflecteer zelfstandig op mijn leerproces en pas inzichten toe om mij verder te ontwikkelen.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 007",
  },
  {
    code: "IT_VF_LU_Softskills_008",
    phase: "VF",
    kind: "Softskills",
    number: 8,
    description:
      "Na de verdiepende fase kan ik een nieuwsgierige en lerende houding tonen. Ik reflecteer op mijn ontwikkeling en bouw doorzettingsvermogen op bij uitdagingen, met beperkte begeleiding waar nodig.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 008",
  },
  {
    code: "IT_AF_LU_Hardskills_001",
    phase: "AF",
    kind: "Hardskills",
    number: 1,
    description: "Ik kan tijdens de afrondende fase complexe taken en werkzaamheden zelfstandig plannen en organiseren.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 001",
  },
  {
    code: "IT_AF_LU_Hardskills_002",
    phase: "AF",
    kind: "Hardskills",
    number: 2,
    description: "Ik kan tijdens de afrondende fase de voortgang van mijn werk zelfstandig bewaken en bijsturen waar nodig.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 002",
  },
  {
    code: "IT_AF_LU_Hardskills_003",
    phase: "AF",
    kind: "Hardskills",
    number: 3,
    description: "Ik kan tijdens de afrondende fase verschillende ontwerpprincipes en modellen toepassen om een technische oplossing uit te werken.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 003",
  },
  {
    code: "IT_AF_LU_Hardskills_004",
    phase: "AF",
    kind: "Hardskills",
    number: 4,
    description: "Ik kan tijdens de afrondende fase zelfstandig complexe onderdelen van software of systemen bouwen.",
    pct: 27,
    hours: 115,
    target: 420,
    short: "Software of systemen bouwen",
  },
  {
    code: "IT_AF_LU_Hardskills_005",
    phase: "AF",
    kind: "Hardskills",
    number: 5,
    description: "Ik kan tijdens de afrondende fase een uitgebreid testplan maken met daarin verschillende testen en kwaliteitscriteria.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 005",
  },
  {
    code: "IT_AF_LU_Hardskills_006",
    phase: "AF",
    kind: "Hardskills",
    number: 6,
    description: "Ik kan tijdens de afrondende fase complexe tests zelfstandig uitvoeren, resultaten analyseren en verbeteradvies geven.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 006",
  },
  {
    code: "IT_AF_LU_Hardskills_007",
    phase: "AF",
    kind: "Hardskills",
    number: 7,
    description: "Ik kan tijdens de afrondende fase een testrapport maken met duidelijke conclusies en verbeteradviezen.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 007",
  },
  {
    code: "IT_AF_LU_Hardskills_008",
    phase: "AF",
    kind: "Hardskills",
    number: 8,
    description: "Ik kan tijdens de afrondende fase zelfstandig verbeteringen bedenken, onderbouwen met analyse en doorvoeren in de praktijk.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 008",
  },
  {
    code: "IT_AF_LU_Softskills_001",
    phase: "AF",
    kind: "Softskills",
    number: 1,
    description: "Ik kan tijdens de afrondende fase problemen herkennen en passende vervolgstappen bespreken met de juiste personen.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 001",
  },
  {
    code: "IT_AF_LU_Softskills_002",
    phase: "AF",
    kind: "Softskills",
    number: 2,
    description: "Ik neem tijdens de afrondende fase actief deel aan overleg met iedereen die betrokken is bij het project.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 002",
  },
  {
    code: "IT_AF_LU_Softskills_003",
    phase: "AF",
    kind: "Softskills",
    number: 3,
    description: "Ik geef en ontvang tijdens de afrondende fase feedback zonder begeleiding, op een professionele manier.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 003",
  },
  {
    code: "IT_AF_LU_Softskills_004",
    phase: "AF",
    kind: "Softskills",
    number: 4,
    description: "Ik reflecteer tijdens de afrondende fase op mijn eigen prestaties en het teamproces na het afronden van een opdracht.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 004",
  },
  {
    code: "IT_AF_LU_Softskills_005",
    phase: "AF",
    kind: "Softskills",
    number: 5,
    description: "Ik maak tijdens de afrondende fase duidelijke instructies voor gebruikers en leg risico’s op een begrijpelijke manier uit.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 005",
  },
  {
    code: "IT_AF_LU_Softskills_006",
    phase: "AF",
    kind: "Softskills",
    number: 6,
    description: "Ik documenteer tijdens de afrondende fase een netwerk of systeem op zo’n manier dat anderen het goed kunnen gebruiken.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 006",
  },
  {
    code: "IT_AF_LU_Softskills_007",
    phase: "AF",
    kind: "Softskills",
    number: 7,
    description: "Ik geef tijdens de afrondende fase uitleg aan gebruikers over systemen en pas bestaande instructies aan als dat nodig is.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 007",
  },
  {
    code: "IT_AF_LU_Softskills_008",
    phase: "AF",
    kind: "Softskills",
    number: 8,
    description: "Ik breng tijdens de afrondende fase de onderdelen van een netwerk of systeem in kaart en leg die overzichtelijk vast.",
    pct: null,
    hours: null,
    target: null,
    short: "Leeruitkomst 008",
  },
];

const outcomeTitles = {
  IT_OF_LU_Hardskills_001: "Eenvoudige vakgerichte taken uitvoeren",
  IT_OF_LU_Hardskills_002: "Afwijkingen en problemen signaleren",
  IT_OF_LU_Hardskills_003: "Meewerken aan technische oplossingen",
  IT_OF_LU_Hardskills_004: "Samenwerken en effectief communiceren",
  IT_OF_LU_Hardskills_005: "Voortgang bijhouden en rapporteren",
  IT_OF_LU_Hardskills_006: "Kwaliteit verbeteren met tools en technieken",
  IT_OF_LU_Hardskills_007: "Veilig en volgens normen werken",
  IT_OF_LU_Hardskills_008: "Keuzes en acties onderbouwen",
  IT_OF_LU_Softskills_001: "Kritisch denken en het leerproces evalueren",
  IT_OF_LU_Softskills_002: "Creatieve oplossingen bedenken",
  IT_OF_LU_Softskills_003: "Samenwerken en reflecteren",
  IT_OF_LU_Softskills_004: "Doelgericht communiceren en feedback geven",
  IT_OF_LU_Softskills_005: "Analytisch keuzes maken",
  IT_OF_LU_Softskills_006: "Veerkrachtig en flexibel werken",
  IT_OF_LU_Softskills_007: "Ethisch en privacybewust handelen",
  IT_OF_LU_Softskills_008: "Een nieuwsgierige en lerende houding tonen",
  IT_VF_LU_Hardskills_001: "Complexe vakgerichte taken uitvoeren",
  IT_VF_LU_Hardskills_002: "Problemen gestructureerd analyseren",
  IT_VF_LU_Hardskills_003: "Complexe technische oplossingen realiseren",
  IT_VF_LU_Hardskills_004: "Zelfstandiger samenwerken en communiceren",
  IT_VF_LU_Hardskills_005: "Systemen beheren en beveiligen",
  IT_VF_LU_Hardskills_006: "Data analyseren en verbeteringen voorstellen",
  IT_VF_LU_Hardskills_007: "Werkwijzen optimaliseren met moderne tools",
  IT_VF_LU_Hardskills_008: "Eigen werk toetsen en verbeteren",
  IT_VF_LU_Softskills_001: "Keuzes onderbouwen en feedback verwerken",
  IT_VF_LU_Softskills_002: "Technologische vraagstukken kritisch beoordelen",
  IT_VF_LU_Softskills_003: "Innovatieve oplossingen ontwikkelen",
  IT_VF_LU_Softskills_004: "Doelgericht communiceren met het publiek",
  IT_VF_LU_Softskills_005: "Effectief samenwerken in multidisciplinaire teams",
  IT_VF_LU_Softskills_006: "Veerkracht en flexibiliteit tonen",
  IT_VF_LU_Softskills_007: "Proactief kennis en vaardigheden ontwikkelen",
  IT_VF_LU_Softskills_008: "Reflecteren en doorzettingsvermogen opbouwen",
  IT_AF_LU_Hardskills_001: "Complexe werkzaamheden plannen en organiseren",
  IT_AF_LU_Hardskills_002: "Voortgang zelfstandig bewaken en bijsturen",
  IT_AF_LU_Hardskills_003: "Een technische oplossing ontwerpen",
  IT_AF_LU_Hardskills_004: "Software of systemen bouwen",
  IT_AF_LU_Hardskills_005: "Een uitgebreid testplan maken",
  IT_AF_LU_Hardskills_006: "Complexe tests uitvoeren en analyseren",
  IT_AF_LU_Hardskills_007: "Een testrapport met verbeteradvies maken",
  IT_AF_LU_Hardskills_008: "Verbeteringen analyseren en doorvoeren",
  IT_AF_LU_Softskills_001: "Problemen herkennen en vervolgstappen bespreken",
  IT_AF_LU_Softskills_002: "Actief deelnemen aan projectoverleg",
  IT_AF_LU_Softskills_003: "Professioneel feedback geven en ontvangen",
  IT_AF_LU_Softskills_004: "Reflecteren op prestaties en teamproces",
  IT_AF_LU_Softskills_005: "Duidelijke gebruikersinstructies maken",
  IT_AF_LU_Softskills_006: "Een netwerk of systeem documenteren",
  IT_AF_LU_Softskills_007: "Gebruikers uitleg en instructies geven",
  IT_AF_LU_Softskills_008: "Netwerk- en systeemonderdelen in kaart brengen",
};

outcomes.forEach((outcome) => {
  outcome.short = outcomeTitles[outcome.code] || outcome.short;
});

const phases = [
  ["OF", "Oriëntatiefase", 60, 342],
  ["VF", "Verdiepingsfase", 61, 221],
  ["AF", "Afrondingsfase", 27, 115],
];

const currentWorkByPhase = {
  OF: {
    title: "Werkplek installeren",
    type: "Installatieopdracht",
    code: "IT_OF_LU_Hardskills_001",
    pct: 65,
  },
  VF: {
    title: "Systemen beheren en beveiligen",
    type: "Beveiligingsopdracht",
    code: "IT_VF_LU_Hardskills_005",
    pct: 52,
  },
  AF: {
    title: "Softwaremodule bouwen",
    type: "Bouwopdracht",
    code: "IT_AF_LU_Hardskills_004",
    pct: 27,
  },
};

const records = [
  ["Werkplek installeren", "Installatieopdracht", "IT_OF_LU_Hardskills_001", 42, 1],
  ["Software configureren", "Configuratieopdracht", "IT_OF_LU_Hardskills_001", 35, 1],
  ["Werkproces plannen", "Planning en uitvoering", "IT_OF_LU_Hardskills_005", 40, 1],
  ["Storing signaleren", "Analyseopdracht", "IT_OF_LU_Hardskills_002", 38, 1],
  ["Teamreflectie", "Samenwerkingsopdracht", "IT_OF_LU_Softskills_003", 44, 1],
  ["Beveiligingscontrole", "Beheeropdracht", "IT_VF_LU_Hardskills_005", 56, 2],
  ["Netwerkdocumentatie", "Documentatieopdracht", "IT_VF_LU_Softskills_006", 48, 2],
  ["Projectoverleg", "Samenwerkingsopdracht", "IT_VF_LU_Softskills_005", 42, 2],
  ["Data-analyse", "Analyseopdracht", "IT_VF_LU_Hardskills_006", 58, 3],
  ["Technisch ontwerp", "Ontwerpopdracht", "IT_VF_LU_Hardskills_003", 52, 3],
  ["Feedback verwerken", "Verbeteropdracht", "IT_VF_LU_Hardskills_008", 46, 3],
  ["Softwaremodule bouwen", "Bouwopdracht", "IT_AF_LU_Hardskills_004", 45, 4],
  ["Testplan opstellen", "Testopdracht", "IT_AF_LU_Hardskills_005", 54, 4],
  ["Gebruikersinstructie", "Documentatieopdracht", "IT_AF_LU_Softskills_005", 38, 4],
];

const hoursByYear = {
  1: { total: 510, orientation: 410, deepening: 80, completion: 20 },
  2: { total: 620, orientation: 160, deepening: 390, completion: 70 },
  3: { total: 735, orientation: 70, deepening: 345, completion: 320 },
  4: { total: 680, orientation: 20, deepening: 130, completion: 530 },
};

const registrationMeta = [
  ["12 september 2026", "Mevrouw Jansen · docent"],
  ["8 september 2026", "Peter de Boer · praktijkbegeleider"],
  ["2 september 2026", "Fatima El Amrani · coach"],
  ["26 augustus 2026", "Mevrouw Jansen · docent"],
  ["20 augustus 2026", "Fatima El Amrani · coach"],
  ["18 juni 2026", "Tom Smit · vakdocent"],
  ["10 juni 2026", "Peter de Boer · praktijkbegeleider"],
  ["3 juni 2026", "Fatima El Amrani · coach"],
  ["14 maart 2026", "Tom Smit · vakdocent"],
  ["5 maart 2026", "Mevrouw Jansen · docent"],
  ["24 februari 2026", "Peter de Boer · praktijkbegeleider"],
  ["15 december 2025", "Tom Smit · vakdocent"],
  ["3 december 2025", "Mevrouw Jansen · docent"],
  ["21 november 2025", "Fatima El Amrani · coach"],
];

// -----------------------------------------------------------------------------
// Navigatie en algemene interface
// -----------------------------------------------------------------------------
const routes = {
  student: [
    ["Dashboard", "studentDashboard"],
    ["Leeruitkomsten", "learningOutcomes"],
    ["Uren", "hours"],
    ["Leerdoelen", "goals"],
    ["Reflecties", "reflections"],
    ["Feedback", "feedback"],
  ],
  coach: [
    ["Studenten", "coachStudents"],
    ["Studentdetail", "coachStudent"],
  ],
  manager: [
    ["Groepsoverzicht", "managerGroups"],
    ["Rapportages", "managerReports"],
  ],
  admin: [
    ["Verwerking", "adminProcessing"],
    ["Logboek", "adminLog"],
  ],
};

const names = {
  student: "Student",
  coach: "SLB'er / coach",
  manager: "Teamleider",
  admin: "Beheerder",
};

let role = window.LARAVEL_USER?.role || "student",
  phase = "OF",
  emptyHours = false,
  reflectionPhaseFilter = "all",
  feedbackPhaseFilter = "all",
  currentOutcomeCode = null,
  reflectionReturnToOutcome = false,
  feedbackReturnToOutcome = false,
  catalogAttentionOnly = false,
  selectedRegistrationCode = null;
const archivedOutcomesKey = "archived-learning-outcomes";
const attentionOutcomesByPhase = {
  OF: ["IT_OF_LU_Softskills_003"],
  VF: ["IT_VF_LU_Softskills_004"],
  AF: ["IT_AF_LU_Hardskills_004"],
};
const attentionOutcomeCodes = Object.values(attentionOutcomesByPhase).flat();

const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);

function login() {
  role = window.LARAVEL_USER?.role || "student";

  document.getElementById("roleLabel").textContent = names[role];

  document.getElementById("actor").textContent = window.LARAVEL_USER?.name || names[role];

  document.getElementById("processingNotification").classList.toggle("hidden", role !== "student");

  document.getElementById("nav").innerHTML = routes[role]
    .map(([s, p]) => '<button class="nav" data-route="' + p + '" onclick="show(\'' + p + "')\">" + s + "</button>")
    .join("");
  show(routes[role][0][1]);
}

function show(page) {
  document.getElementById("login").classList.toggle("hidden", page !== "login");

  document.getElementById("app").classList.toggle("hidden", page === "login");

  document.querySelectorAll(".view").forEach((x) => x.classList.toggle("hidden", x.id !== page));

  document
    .querySelectorAll("[data-route]")
    .forEach((x) => x.classList.toggle("active", x.dataset.route === (page === "outcomeDetail" ? "learningOutcomes" : page)));
  window.scrollTo(0, 0);
}

let hintTimer = null;
function hint(s) {
  const x = document.getElementById("hint");
  clearTimeout(hintTimer);
  x.textContent = s;
  x.classList.remove("hidden");
  hintTimer = setTimeout(() => {
    x.classList.add("hint-leaving");
    setTimeout(() => {
      x.classList.add("hidden");
      x.classList.remove("hint-leaving");
      x.textContent = "";
    }, 180);
  }, 3500);
}

function choose(p) {
  phase = p;
  renderDashboard();
}

function openCurrentWorkOutcome() {
  openOutcome(currentWorkByPhase[phase].code);
}

function renderDashboard() {
  let current = phases.find((x) => x[0] === phase);

  document.getElementById("phaseTabs").innerHTML = phases
    .map((x) => '<button class="tab ' + (phase === x[0] ? "active" : "") + '" onclick="choose(\'' + x[0] + "')\">" + x[1] + "</button>")
    .join("");

  document.getElementById("phasePercent").textContent = current[2] + "%";

  document.getElementById("phaseBar").innerHTML = '<span style="width:' + current[2] + '%"></span>';
  const work = currentWorkByPhase[phase];
  document.getElementById("currentWorkTitle").textContent = work.title;
  document.getElementById("currentWorkMeta").textContent = work.type + " · " + current[1];
  document.getElementById("currentWorkBar").style.width = work.pct + "%";
  document.getElementById("currentWorkPercent").textContent = work.pct + "%";
  const attentionCount = attentionOutcomesByPhase[phase].length;
  document.getElementById("attentionCount").textContent = attentionCount;
  document.getElementById("attentionText").textContent =
    attentionCount === 1 ? "leeruitkomst om verder aan te werken" : "leeruitkomsten om verder aan te werken";
}

function practicalExplanation(outcome) {
  const independence = outcome.phase === "OF" ? "onder begeleiding" : outcome.phase === "VF" ? "met beperkte begeleiding" : "zelfstandig";
  return outcome.kind === "Hardskills"
    ? "Je past deze vaardigheid " +
        independence +
        " toe in een concrete opdracht. Laat niet alleen het eindresultaat zien, maar leg ook uit welke aanpak, hulpmiddelen en keuzes je hebt gebruikt."
    : "Je laat deze vaardigheid zien tijdens samenwerking, communicatie en je eigen leerproces. Beschrijf een concrete situatie, jouw handelen, ontvangen feedback en wat je een volgende keer anders doet.";
}

function openOutcome(code) {
  const x = outcomes.find((o) => o.code === code),
    hasProgress = x.pct !== null;
  currentOutcomeCode = code;
  document.getElementById("outcomeTitle").textContent = x.short;
  document.getElementById("outcomeMeta").textContent = phases.find((p) => p[0] === x.phase)[1];
  document.getElementById("outcomeCode").textContent = x.code;
  document.getElementById("outcomeText").textContent = x.description;
  document.getElementById("outcomePractical").textContent = practicalExplanation(x);
  document.getElementById("detailPercent").textContent = hasProgress ? x.pct + "%" : "—";
  document.getElementById("detailBar").style.width = (x.pct || 0) + "%";
  document.getElementById("detailHours").textContent = x.hours === null ? "Nog geen uren" : x.hours + " uur";
  document.getElementById("detailTarget").textContent = "80%";
  document.getElementById("detailStatus").textContent = !hasProgress
    ? "Nog niet gestart"
    : x.pct >= 80
      ? "Richtwaarde behaald"
      : x.pct >= 40
        ? "In ontwikkeling"
        : "Gestart";
  renderOutcomeReflections();
  renderOutcomeFeedback();
  show("outcomeDetail");
}

function renderGoalOptions() {
  const select = document.getElementById("goalCodes"),
    phaseSelect = document.getElementById("goalPhase"),
    choices = document.getElementById("goalOutcomeChoices");
  if (!select || !phaseSelect || !choices) return;
  const previous = select.value,
    phaseCode = phaseSelect.value,
    list = outcomes.filter((o) => o.phase === phaseCode);
  select.innerHTML = list.map((o) => '<option value="' + o.code + '">' + esc(o.short) + " — " + o.code + "</option>").join("");
  if (list.some((o) => o.code === previous)) select.value = previous;
  const selected = select.value;
  document.getElementById("goalChoiceCount").textContent = list.length + " " + (list.length === 1 ? "leeruitkomst" : "leeruitkomsten");
  choices.innerHTML = list
    .map(
      (o) =>
        '<button type="button" role="option" aria-selected="' +
        (o.code === selected) +
        '" class="goal-outcome-choice ' +
        (o.code === selected ? "selected" : "") +
        '" onclick="selectGoalOutcome(\'' +
        o.code +
        '\')"><span class="goal-choice-number">' +
        String(o.number).padStart(2, "0") +
        "</span><span><strong>" +
        esc(o.short) +
        "</strong><small>" +
        (o.kind === "Hardskills" ? "Vakvaardigheid" : "Persoonlijke vaardigheid") +
        '</small><span class="code">' +
        esc(o.code) +
        '</span></span><span class="goal-choice-check">✓</span></button>',
    )
    .join("");
  showGoalOutcomePreview();
}

function selectGoalOutcome(code) {
  document.getElementById("goalCodes").value = code;
  renderGoalOptions();
}

function showGoalOutcomePreview() {
  const outcome = outcomes.find((o) => o.code === document.getElementById("goalCodes").value),
    preview = document.getElementById("goalOutcomePreview");
  if (!outcome) {
    preview.innerHTML = "";
    return;
  }
  preview.innerHTML =
    '<span class="small dim">Geselecteerde leeruitkomst</span><strong>' +
    esc(outcome.short) +
    '</strong><span class="code">' +
    esc(outcome.code) +
    "</span><p>" +
    esc(outcome.description) +
    "</p>";
}
function renderCatalog() {
  const q = document.getElementById("outcomeSearch").value.toLowerCase(),
    p = document.getElementById("catalogPhase").value;
  const list = outcomes.filter(
    (x) =>
      (p === "all" || x.phase === p) &&
      (!catalogAttentionOnly || attentionOutcomeCodes.includes(x.code)) &&
      (x.code + " " + x.short + " " + x.description).toLowerCase().includes(q),
  );
  document.getElementById("catalogAttentionNotice").classList.toggle("hidden", !catalogAttentionOnly);
  document.getElementById("catalogCount").textContent = list.length + (catalogAttentionOnly ? " aandachtspunten" : " leeruitkomsten gevonden");
  document.getElementById("catalogList").innerHTML = list.length
    ? list
        .map((x) => {
          const hasProgress = x.pct !== null;
          return (
            '<button class="catalog-card" onclick="openOutcome(\'' +
            x.code +
            '\')"><span class="catalog-phase-label">' +
            esc((phases.find((p) => p[0] === x.phase) || [])[1] || "") +
            '</span><span class="catalog-arrow">↗</span><h2>' +
            esc(x.short) +
            '</h2><span class="code catalog-code">' +
            esc(x.code) +
            "</span><p>" +
            esc(x.description) +
            '</p><div class="outcome-progress"><div class="outcome-progress-label"><span>Voortgang</span><b>' +
            (hasProgress ? x.pct + "%" : "Nog geen voortgang") +
            '</b></div><div class="bar"><span style="width:' +
            (x.pct || 0) +
            '%"></span></div><span class="outcome-hours">' +
            (x.hours === null ? "Nog geen uren" : x.hours + " uur") +
            " · Richtwaarde 80%</span></div></button>"
          );
        })
        .join("")
    : '<div class="card">Geen aandachtspunten gevonden binnen deze filters.</div>';
}

function showAttentionOutcomes() {
  catalogAttentionOnly = true;
  document.getElementById("catalogPhase").value = phase;
  document.getElementById("outcomeSearch").value = "";
  renderCatalog();
  show("learningOutcomes");
}

function clearAttentionOutcomes() {
  catalogAttentionOnly = false;
  renderCatalog();
}
function renderHours() {
  const selected = document.getElementById("hoursYear").value,
    totalData = Object.values(hoursByYear).reduce(
      (sum, item) => ({
        total: sum.total + item.total,
        orientation: sum.orientation + item.orientation,
        deepening: sum.deepening + item.deepening,
        completion: sum.completion + item.completion,
      }),
      { total: 0, orientation: 0, deepening: 0, completion: 0 },
    ),
    isTotal = selected === "total",
    data = isTotal ? totalData : hoursByYear[Number(selected)],
    target = isTotal ? 3200 : 800,
    remaining = Math.max(0, target - data.total),
    percentage = Math.round((data.total / target) * 100),
    periodLabel = isTotal ? "Totaal opleiding" : "Leerjaar " + selected,
    periodRecords = isTotal ? records : records.filter((record) => record[4] === Number(selected));
  document.getElementById("hoursSelectedYear").textContent = periodLabel;
  document.getElementById("registrationsYear").textContent = periodLabel;
  document.querySelector(".hours-total>div>span").textContent = " / " + target.toLocaleString("nl-NL") + " uur";
  document.querySelector(".hours-scale span:last-child").textContent = "Doel: " + target.toLocaleString("nl-NL") + " uur";
  document.getElementById("hoursYearTotal").textContent = data.total.toLocaleString("nl-NL");
  document.getElementById("hoursYearPercentage").textContent = percentage + "% van de urennorm behaald";
  document.getElementById("hoursScalePercentage").textContent = percentage + "% van je doel behaald";
  document.getElementById("hoursOrientation").textContent = data.orientation + " uur";
  document.getElementById("hoursDeepening").textContent = data.deepening + " uur";
  document.getElementById("hoursCompletion").textContent = data.completion + " uur";
  const segments = [
    ["orientation", data.orientation, "Oriëntatiefase"],
    ["deepening", data.deepening, "Verdiepingsfase"],
    ["completion", data.completion, "Afrondingsfase"],
    ["remaining", remaining, "Nog te behalen"],
  ];
  document.getElementById("hoursStackedBar").innerHTML = segments
    .map(
      ([kind, hours, label]) =>
        '<span class="hours-segment ' +
        kind +
        '" style="width:' +
        (hours / target) * 100 +
        '%" title="' +
        label +
        ": " +
        hours +
        ' uur">' +
        (kind === "remaining" ? "" : "<span>" + hours + " uur</span>") +
        "</span>",
    )
    .join("");
  document.getElementById("hoursLegend").innerHTML = segments
    .map(
      ([kind, hours, label]) =>
        '<div><span class="legend-color ' + kind + '"></span><span><b>' + hours.toLocaleString("nl-NL") + " uur</b> " + label + "</span></div>",
    )
    .join("");
  const periodItems = [...Object.entries(hoursByYear).sort((a, b) => a[0] - b[0]), ["total", totalData]];
  document.getElementById("yearlyHoursOverview").innerHTML = periodItems
    .map(([key, item]) => {
      const itemIsTotal = key === "total",
        itemTarget = itemIsTotal ? 3200 : 800,
        label = itemIsTotal ? "Totaal" : "Leerjaar " + key;
      return (
        '<button type="button" class="year-total ' +
        (key === selected ? "active" : "") +
        " " +
        (itemIsTotal ? "grand-total" : "") +
        '" onclick="selectHoursYear(\'' +
        key +
        "')\"><span>" +
        label +
        "</span><strong>" +
        item.total.toLocaleString("nl-NL") +
        ' uur</strong><div class="bar"><span style="width:' +
        (item.total / itemTarget) * 100 +
        '%"></span></div><small>' +
        Math.round((item.total / itemTarget) * 100) +
        "% van " +
        (itemIsTotal ? "de opleidingsnorm" : "de leerjaarnorm") +
        "</small></button>"
      );
    })
    .join("");
  document.getElementById("hoursList").innerHTML = emptyHours
    ? '<div class="empty-state"><h3>Nog geen gevalideerde uren</h3><p>Er zijn voor deze periode nog geen goedgekeurde registraties zichtbaar.</p><button class="button" onclick="emptyHours=false;renderHours()">Toon voorbeeldgegevens</button></div>'
    : periodRecords
        .map((r) => {
          const index = records.indexOf(r),
            meta = registrationMeta[index];
          return (
            '<button type="button" class="simple click registration-row" onclick="openHourRegistration(' +
            index +
            ')"><span><b>' +
            esc(r[0]) +
            '</b><br><span class="small dim">' +
            esc(r[1]) +
            " · Leerjaar " +
            r[4] +
            '</span></span><span class="code">' +
            esc(r[2]) +
            '</span><span class="registration-row-meta"><b>' +
            r[3] +
            " uur</b><small>" +
            esc(meta[0]) +
            "</small></span></button>"
          );
        })
        .join("");
}

function selectHoursYear(period) {
  document.getElementById("hoursYear").value = period;
  emptyHours = false;
  renderHours();
}

function openHourRegistration(index) {
  const record = records[index],
    meta = registrationMeta[index],
    outcome = outcomes.find((item) => item.code === record[2]),
    phaseName = outcome ? (phases.find((item) => item[0] === outcome.phase) || [])[1] : "Onbekende fase";
  selectedRegistrationCode = record[2];
  document.getElementById("registrationDetailTitle").textContent = record[0];
  document.getElementById("registrationDetailType").textContent = record[1];
  document.getElementById("registrationDetailHours").textContent = record[3] + " uur";
  document.getElementById("registrationDetailYear").textContent = "Leerjaar " + record[4];
  document.getElementById("registrationDetailPhase").textContent = phaseName;
  document.getElementById("registrationDetailDate").textContent = meta[0];
  document.getElementById("registrationDetailApprover").textContent = meta[1];
  document.getElementById("registrationDetailOutcomeName").textContent = outcome ? outcome.short : "Leeruitkomst";
  document.getElementById("registrationDetailCode").textContent = record[2];
  document.getElementById("registrationDetailModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#registrationDetailModal .modal-close").focus());
}

function closeRegistrationDetail() {
  document.getElementById("registrationDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

function openRegistrationOutcome() {
  const code = selectedRegistrationCode;
  closeRegistrationDetail();
  if (code) openOutcome(code);
}

// -----------------------------------------------------------------------------
// Reflecties
// -----------------------------------------------------------------------------
const reflectionKey = "ontwikkeling-reflecties";
const defaultReflections = [
  {
    id: "demo-1",
    title: "Installatieopdracht",
    code: "IT_OF_LU_Hardskills_001",
    text: "Ik heb een werkplek geïnstalleerd en mijn keuzes met mijn begeleider besproken.",
    date: "4 september 2026 om 10:15",
    demo: true,
  },
];

function getReflections() {
  try {
    const saved = JSON.parse(loadLocalState(reflectionKey));
    return Array.isArray(saved) ? saved : defaultReflections;
  } catch {
    return defaultReflections;
  }
}

function renderOutcomeReflections() {
  if (!currentOutcomeCode) return;
  const list = getReflections().filter((item) => item.code === currentOutcomeCode),
    container = document.getElementById("outcomeReflectionList");
  container.innerHTML = list.length
    ? list
        .map(
          (item) =>
            '<button class="outcome-reflection-row" type="button" onclick="openOutcomeReflectionDetail(\'' +
            esc(item.id) +
            "')\"><div><strong>" +
            esc(item.title) +
            '</strong><span class="reflection-date">' +
            esc(item.date) +
            '</span></div><span class="row-arrow">→</span></button>',
        )
        .join("")
    : '<div class="outcome-reflection-empty compact"><strong>Nog geen reflectie</strong><p>Voeg je eerste reflectie toe.</p></div>';
}

function openOutcomeReflectionDetail(id) {
  const item = getReflections().find((reflection) => reflection.id === id);
  if (!item) return;
  document.getElementById("reflectionDetailDate").textContent = item.date;
  document.getElementById("reflectionDetailTitle").textContent = item.title;
  document.getElementById("reflectionDetailCode").textContent = item.code;
  document.getElementById("reflectionDetailContent").innerHTML = item.situation
    ? "<section><span>1</span><div><h3>Situatie</h3><p>" +
      esc(item.situation) +
      "</p></div></section><section><span>2</span><div><h3>Wat ging goed?</h3><p>" +
      esc(item.good) +
      "</p></div></section><section><span>3</span><div><h3>Wat kan beter?</h3><p>" +
      esc(item.improve) +
      "</p></div></section><section><span>4</span><div><h3>Volgende stap</h3><p>" +
      esc(item.next) +
      "</p></div></section>"
    : "<section><span>1</span><div><h3>Reflectie</h3><p>" + esc(item.text || "") + "</p></div></section>";
  document.getElementById("outcomeReflectionDetailModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#outcomeReflectionDetailModal .modal-close").focus());
}

function closeOutcomeReflectionDetail() {
  document.getElementById("outcomeReflectionDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

function keepOnlyOneOutcomeItemOpen(item) {
  if (!item.open) return;
  document.querySelectorAll(".outcome-reflection-detail[open],.outcome-feedback-detail[open]").forEach((other) => {
    if (other !== item) other.open = false;
  });
}

function viewReflectionsForPhase(phaseCode) {
  show("reflections");
  filterReflectionsByPhase(phaseCode);
}

function openReflectionForCurrentOutcome() {
  const outcome = outcomes.find((o) => o.code === currentOutcomeCode);
  show("reflections");
  if (outcome) {
    document.getElementById("reflectionPhase").value = outcome.phase;
    updateReflectionOutcomeOptions();
    document.getElementById("reflectionOutcome").value = outcome.code;
    showReflectionOutcomePreview();
  }
  openReflectionForm(true);
}

function renderReflections() {
  const all = getReflections(),
    list =
      reflectionPhaseFilter === "all"
        ? all
        : all.filter((item) => {
            const outcome = outcomes.find((o) => o.code === item.code);
            return outcome && outcome.phase === reflectionPhaseFilter;
          });
  document.getElementById("reflectionCount").textContent = all.length + " " + (all.length === 1 ? "REFLECTIE" : "REFLECTIES");
  document.getElementById("reflectionPhaseFilter").innerHTML = [["all", "Alle fases"], ...phases.map(([code, name]) => [code, name])]
    .map(
      ([code, name]) =>
        '<button type="button" class="reflection-filter-button ' +
        (reflectionPhaseFilter === code ? "active" : "") +
        '" onclick="filterReflectionsByPhase(\'' +
        code +
        "')\">" +
        name +
        "<span>" +
        getReflectionCountForPhase(all, code) +
        "</span></button>",
    )
    .join("");
  document.getElementById("reflectionList").innerHTML = list.length
    ? list
        .map((item) => {
          const outcome = outcomes.find((o) => o.code === item.code),
            phaseName = outcome ? (phases.find((p) => p[0] === outcome.phase) || [])[1] : "Onbekende fase",
            sections = item.situation
              ? '<div class="reflection-sections"><div><b>1. Situatie</b><p>' +
                esc(item.situation) +
                "</p></div><div><b>2. Wat ging goed?</b><p>" +
                esc(item.good) +
                "</p></div><div><b>3. Wat kan beter?</b><p>" +
                esc(item.improve) +
                "</p></div><div><b>4. Volgende stap</b><p>" +
                esc(item.next) +
                "</p></div></div>"
              : '<div class="legacy-reflection"><b>Terugblik</b><p>' + esc(item.text || "") + "</p></div>";
          return (
            '<details class="reflection-accordion"><summary><div class="reflection-summary"><div class="reflection-summary-top"><span class="reflection-date">' +
            esc(item.date) +
            '</span><span class="reflection-phase">' +
            esc(phaseName) +
            '</span><span class="skill-tag ' +
            (outcome && outcome.kind === "Softskills" ? "soft" : "") +
            '">' +
            esc(outcome ? outcome.kind : "Leeruitkomst") +
            "</span></div><h2>" +
            esc(item.title) +
            "</h2><strong>" +
            esc(outcome ? outcome.short : "Leeruitkomst") +
            '</strong><span class="code">' +
            esc(item.code) +
            '</span></div><span class="accordion-toggle" aria-hidden="true">⌄</span></summary><div class="reflection-accordion-body">' +
            sections +
            '<div class="reflection-actions"><button class="icon-button" type="button" onclick="deleteReflection(\'' +
            esc(item.id) +
            "')\">Reflectie verwijderen</button></div></div></details>"
          );
        })
        .join("")
    : '<div class="empty-state"><span class="empty-icon">✦</span><h2>Geen reflecties in deze fase</h2><p>Kies een andere fase of schrijf een nieuwe reflectie.</p></div>';
}

function getReflectionCountForPhase(list, phaseCode) {
  if (phaseCode === "all") return list.length;
  return list.filter((item) => {
    const outcome = outcomes.find((o) => o.code === item.code);
    return outcome && outcome.phase === phaseCode;
  }).length;
}

function filterReflectionsByPhase(phaseCode) {
  reflectionPhaseFilter = phaseCode;
  renderReflections();
}

function saveReflection(event) {
  event.preventDefault();
  const title = document.getElementById("reflectionTitle").value.trim();
  const code = document.getElementById("reflectionOutcome").value;
  const situation = document.getElementById("reflectionSituation").value.trim(),
    good = document.getElementById("reflectionGood").value.trim(),
    improve = document.getElementById("reflectionImprove").value.trim(),
    next = document.getElementById("reflectionNext").value.trim();
  const message = document.getElementById("reflectionMessage");
  if (!title || !code || [situation, good, improve, next].some((value) => value.length < 10)) {
    message.textContent = "Vul alle stappen in en schrijf bij iedere vraag minimaal 10 tekens.";
    message.className = "form-message error";
    return;
  }
  const list = getReflections();
  list.unshift({
    id: "reflection-" + Date.now(),
    title,
    code,
    situation,
    good,
    improve,
    next,
    date: new Intl.DateTimeFormat("nl-NL", {
      day: "numeric",
      month: "long",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    })
      .format(new Date())
      .replace(" om ", " om "),
  });
  saveLocalState(reflectionKey, JSON.stringify(list));
  event.target.reset();
  message.textContent = "Je reflectie is bewaard.";
  message.className = "form-message success";
  updateReflectionOutcomeOptions();
  renderReflections();
  renderOutcomeReflections();
  const returnToOutcome = reflectionReturnToOutcome;
  closeReflectionForm();
  if (returnToOutcome) show("outcomeDetail");
}

function renderReflectionOptions() {
  updateReflectionOutcomeOptions();
}

function updateReflectionOutcomeOptions() {
  const phaseCode = document.getElementById("reflectionPhase").value,
    select = document.getElementById("reflectionOutcome"),
    phaseName = (phases.find((p) => p[0] === phaseCode) || [])[1] || "";
  select.innerHTML =
    '<option value="">Kies een leeruitkomst</option><optgroup label="' +
    phaseName +
    '">' +
    outcomes
      .filter((o) => o.phase === phaseCode)
      .map((o) => '<option value="' + o.code + '">' + esc(o.short) + " — " + o.code + "</option>")
      .join("") +
    "</optgroup>";
  showReflectionOutcomePreview();
}

function showReflectionOutcomePreview() {
  const outcome = outcomes.find((o) => o.code === document.getElementById("reflectionOutcome").value),
    preview = document.getElementById("reflectionOutcomePreview");
  if (!outcome) {
    preview.innerHTML = '<span class="small dim">Na je keuze verschijnt hier de naam, code en omschrijving.</span>';
    return;
  }
  preview.innerHTML =
    '<span class="small dim">Geselecteerde leeruitkomst</span><strong>' +
    esc(outcome.short) +
    '</strong><span class="code">' +
    esc(outcome.code) +
    "</span><p>" +
    esc(outcome.description) +
    "</p>";
}

function openReflectionForm(returnToOutcome = false) {
  reflectionReturnToOutcome = returnToOutcome;
  const form = document.getElementById("reflectionForm");
  form.classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.getElementById("reflectionTitle").focus());
}

function closeReflectionForm() {
  document.getElementById("reflectionForm").classList.add("hidden");
  document.body.classList.remove("modal-open");
  reflectionReturnToOutcome = false;
}

function openHoursApprovalModal() {
  if (role === "student") show("studentDashboard");
  document.getElementById("hoursApprovalModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#hoursApprovalModal .modal-close").focus());
}

function closeHoursApprovalModal() {
  document.getElementById("hoursApprovalModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

const hoursProcessingReadKey = "hours-processing-2026-09-12-read";

function updateHoursProcessingReadState() {
  const isRead = loadLocalState(hoursProcessingReadKey) === "true",
    notification = document.getElementById("processingNotification"),
    badge = notification.querySelector(".processing-badge"),
    status = document.getElementById("processingReadStatus"),
    button = document.getElementById("markProcessingReadButton");
  notification.classList.toggle("read", isRead);
  badge.classList.toggle("hidden", isRead);
  notification.setAttribute("aria-label", isRead ? "Laatste urenverwerking bekijken" : "42 nieuwe uren bekijken");
  notification.title = isRead ? "Urenverwerking gelezen" : "Nieuwe urenverwerking";
  status.textContent = isRead ? "Gelezen" : "Ongelezen";
  status.className = "processing-read-status " + (isRead ? "read" : "unread");
  button.classList.toggle("hidden", isRead);
}

function markHoursProcessingRead() {
  saveLocalState(hoursProcessingReadKey, "true");
  updateHoursProcessingReadState();
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("reflectionForm").classList.contains("hidden")) closeReflectionForm();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("hoursApprovalModal").classList.contains("hidden")) closeHoursApprovalModal();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("outcomeReflectionDetailModal").classList.contains("hidden")) closeOutcomeReflectionDetail();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("outcomeFeedbackDetailModal").classList.contains("hidden")) closeOutcomeFeedbackDetail();
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("registrationDetailModal").classList.contains("hidden")) closeRegistrationDetail();
});

function deleteReflection(id) {
  const list = getReflections().filter((item) => item.id !== id);
  saveLocalState(reflectionKey, JSON.stringify(list));
  renderReflections();
  renderOutcomeReflections();
}

// -----------------------------------------------------------------------------
// Feedback
// -----------------------------------------------------------------------------
const feedbackKey = "ontwikkeling-feedback";
const defaultFeedback = [
  {
    id: "feedback-demo-1",
    type: "received",
    person: "Mevrouw Jansen · docent",
    title: "Presentatie installatieplan",
    code: "IT_OF_LU_Hardskills_008",
    positive: "Je legde de belangrijkste technische keuzes rustig en in een duidelijke volgorde uit.",
    improve: "Onderbouw bij iedere keuze ook welk risico je ermee voorkomt en welk alternatief je hebt overwogen.",
    action: "Bij de volgende presentatie voeg ik per technische keuze een risico en een alternatief toe.",
    date: "10 september 2026 om 14:20",
    demo: true,
  },
];

function getFeedback() {
  try {
    const saved = JSON.parse(loadLocalState(feedbackKey));
    return Array.isArray(saved) ? saved : defaultFeedback;
  } catch {
    return defaultFeedback;
  }
}

function renderOutcomeFeedback() {
  if (!currentOutcomeCode) return;
  const list = getFeedback().filter((item) => item.code === currentOutcomeCode),
    container = document.getElementById("outcomeFeedbackList");
  container.innerHTML = list.length
    ? list
        .map(
          (item) =>
            '<button class="outcome-feedback-row" type="button" onclick="openOutcomeFeedbackDetail(\'' +
            esc(item.id) +
            '\')"><div><span class="feedback-type ' +
            item.type +
            '">' +
            (item.type === "given" ? "Gegeven" : "Ontvangen") +
            "</span><strong>" +
            esc(item.title) +
            "</strong><small>" +
            esc(item.date) +
            " · " +
            esc(item.person) +
            '</small></div><span class="row-arrow">→</span></button>',
        )
        .join("")
    : '<div class="outcome-reflection-empty compact"><strong>Nog geen feedback</strong><p>Voeg feedback bij deze leeruitkomst toe.</p></div>';
}

function openOutcomeFeedbackDetail(id) {
  const item = getFeedback().find((feedback) => feedback.id === id);
  if (!item) return;
  const type = document.getElementById("feedbackDetailType");
  type.textContent = item.type === "given" ? "Gegeven" : "Ontvangen";
  type.className = "feedback-type " + item.type;
  document.getElementById("feedbackDetailDate").textContent = item.date;
  document.getElementById("feedbackDetailTitle").textContent = item.title;
  document.getElementById("feedbackDetailPerson").textContent = (item.type === "given" ? "Aan: " : "Van: ") + item.person;
  document.getElementById("feedbackDetailCode").textContent = item.code;
  document.getElementById("feedbackDetailContent").innerHTML =
    "<section><span>✓</span><div><h3>Wat gaat goed?</h3><p>" +
    esc(item.positive) +
    "</p></div></section><section><span>↗</span><div><h3>Wat kan beter?</h3><p>" +
    esc(item.improve) +
    "</p></div></section><section><span>→</span><div><h3>Vervolgafspraak</h3><p>" +
    esc(item.action) +
    "</p></div></section>";
  document.getElementById("outcomeFeedbackDetailModal").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.querySelector("#outcomeFeedbackDetailModal .modal-close").focus());
}

function closeOutcomeFeedbackDetail() {
  document.getElementById("outcomeFeedbackDetailModal").classList.add("hidden");
  document.body.classList.remove("modal-open");
}

function openFeedbackForCurrentOutcome() {
  const outcome = outcomes.find((o) => o.code === currentOutcomeCode);
  show("feedback");
  if (outcome) {
    document.getElementById("feedbackPhase").value = outcome.phase;
    updateFeedbackOutcomeOptions();
    document.getElementById("feedbackOutcome").value = outcome.code;
    showFeedbackOutcomePreview();
  }
  openFeedbackForm(true);
}

function feedbackCountForPhase(list, phaseCode) {
  if (phaseCode === "all") return list.length;
  return list.filter((item) => {
    const outcome = outcomes.find((o) => o.code === item.code);
    return outcome && outcome.phase === phaseCode;
  }).length;
}

function filterFeedbackByPhase(phaseCode) {
  feedbackPhaseFilter = phaseCode;
  renderFeedback();
}

function renderFeedback() {
  const all = getFeedback(),
    typeFilter = document.getElementById("feedbackTypeFilter").value,
    list = all.filter((item) => {
      const outcome = outcomes.find((o) => o.code === item.code),
        phaseMatch = feedbackPhaseFilter === "all" || (outcome && outcome.phase === feedbackPhaseFilter),
        typeMatch = typeFilter === "all" || item.type === typeFilter;
      return phaseMatch && typeMatch;
    });
  document.getElementById("feedbackCount").textContent = all.length + " " + (all.length === 1 ? "FEEDBACKMOMENT" : "FEEDBACKMOMENTEN");
  document.getElementById("feedbackPhaseFilter").innerHTML = [["all", "Alle fases"], ...phases.map(([code, name]) => [code, name])]
    .map(
      ([code, name]) =>
        '<button type="button" class="reflection-filter-button ' +
        (feedbackPhaseFilter === code ? "active" : "") +
        '" onclick="filterFeedbackByPhase(\'' +
        code +
        "')\">" +
        name +
        "<span>" +
        feedbackCountForPhase(all, code) +
        "</span></button>",
    )
    .join("");
  document.getElementById("feedbackList").innerHTML = list.length
    ? list
        .map((item) => {
          const outcome = outcomes.find((o) => o.code === item.code),
            phaseName = outcome ? (phases.find((p) => p[0] === outcome.phase) || [])[1] : "Onbekende fase",
            typeLabel = item.type === "given" ? "Gegeven" : "Ontvangen";
          return (
            '<details class="feedback-accordion"><summary><div class="feedback-summary"><div class="feedback-summary-top"><span class="feedback-type ' +
            item.type +
            '">' +
            typeLabel +
            '</span><span class="reflection-phase">' +
            esc(phaseName) +
            '</span><span class="reflection-date">' +
            esc(item.date) +
            "</span></div><h2>" +
            esc(item.title) +
            "</h2><p>" +
            esc(item.type === "given" ? "Aan: " : "Van: ") +
            esc(item.person) +
            "</p><strong>" +
            esc(outcome ? outcome.short : "Leeruitkomst") +
            '</strong><span class="code">' +
            esc(item.code) +
            '</span></div><span class="accordion-toggle" aria-hidden="true">⌄</span></summary><div class="feedback-body"><div class="feedback-points"><div class="feedback-point positive"><span>✓</span><div><b>Wat gaat goed?</b><p>' +
            esc(item.positive) +
            '</p></div></div><div class="feedback-point improve"><span>↗</span><div><b>Wat kan beter?</b><p>' +
            esc(item.improve) +
            '</p></div></div><div class="feedback-point action"><span>→</span><div><b>Vervolgafspraak</b><p>' +
            esc(item.action) +
            '</p></div></div></div><div class="feedback-actions"><button class="icon-button" type="button" onclick="deleteFeedback(\'' +
            esc(item.id) +
            "')\">Feedback verwijderen</button></div></div></details>"
          );
        })
        .join("")
    : '<div class="empty-state"><span class="empty-icon">↗</span><h2>Geen feedback gevonden</h2><p>Pas je filters aan of voeg een nieuw feedbackmoment toe.</p></div>';
}

function updateFeedbackOutcomeOptions() {
  const phaseCode = document.getElementById("feedbackPhase").value,
    select = document.getElementById("feedbackOutcome"),
    phaseName = (phases.find((p) => p[0] === phaseCode) || [])[1] || "";
  select.innerHTML =
    '<option value="">Kies een leeruitkomst</option><optgroup label="' +
    phaseName +
    '">' +
    outcomes
      .filter((o) => o.phase === phaseCode)
      .map((o) => '<option value="' + o.code + '">' + esc(o.short) + " — " + o.code + "</option>")
      .join("") +
    "</optgroup>";
  showFeedbackOutcomePreview();
}

function showFeedbackOutcomePreview() {
  const outcome = outcomes.find((o) => o.code === document.getElementById("feedbackOutcome").value),
    preview = document.getElementById("feedbackOutcomePreview");
  if (!outcome) {
    preview.innerHTML = '<span class="small dim">Na je keuze verschijnt hier de naam, code en omschrijving.</span>';
    return;
  }
  preview.innerHTML =
    '<span class="small dim">Geselecteerde leeruitkomst</span><strong>' +
    esc(outcome.short) +
    '</strong><span class="code">' +
    esc(outcome.code) +
    "</span><p>" +
    esc(outcome.description) +
    "</p>";
}

function openFeedbackForm(returnToOutcome = false) {
  feedbackReturnToOutcome = returnToOutcome;
  document.getElementById("feedbackForm").classList.remove("hidden");
  document.body.classList.add("modal-open");
  requestAnimationFrame(() => document.getElementById("feedbackPerson").focus());
}

function closeFeedbackForm() {
  document.getElementById("feedbackForm").classList.add("hidden");
  document.body.classList.remove("modal-open");
  feedbackReturnToOutcome = false;
}

function saveFeedback(event) {
  event.preventDefault();
  const type = document.getElementById("feedbackType").value,
    person = document.getElementById("feedbackPerson").value.trim(),
    title = document.getElementById("feedbackTitle").value.trim(),
    code = document.getElementById("feedbackOutcome").value,
    positive = document.getElementById("feedbackPositive").value.trim(),
    improve = document.getElementById("feedbackImprove").value.trim(),
    action = document.getElementById("feedbackAction").value.trim(),
    message = document.getElementById("feedbackMessage");
  if (!person || !title || !code || [positive, improve, action].some((value) => value.length < 10)) {
    message.textContent = "Vul alle velden in en schrijf bij ieder feedbackpunt minimaal 10 tekens.";
    message.className = "form-message error";
    return;
  }
  const list = getFeedback();
  list.unshift({
    id: "feedback-" + Date.now(),
    type,
    person,
    title,
    code,
    positive,
    improve,
    action,
    date: new Intl.DateTimeFormat("nl-NL", {
      day: "numeric",
      month: "long",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    }).format(new Date()),
  });
  saveLocalState(feedbackKey, JSON.stringify(list));
  event.target.reset();
  message.textContent = "De feedback is bewaard.";
  message.className = "form-message success";
  updateFeedbackOutcomeOptions();
  renderFeedback();
  renderOutcomeFeedback();
  const returnToOutcome = feedbackReturnToOutcome;
  closeFeedbackForm();
  if (returnToOutcome) show("outcomeDetail");
}

function deleteFeedback(id) {
  saveLocalState(feedbackKey, JSON.stringify(getFeedback().filter((item) => item.id !== id)));
  renderFeedback();
  renderOutcomeFeedback();
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && !document.getElementById("feedbackForm").classList.contains("hidden")) closeFeedbackForm();
});

// -----------------------------------------------------------------------------
// Archiveren van leeruitkomsten
// -----------------------------------------------------------------------------
function getArchivedOutcomes() {
  try {
    const saved = JSON.parse(loadLocalState(archivedOutcomesKey) || "[]");
    return Array.isArray(saved) ? saved : [];
  } catch (error) {
    return [];
  }
}

function isOutcomeArchived(code) {
  return getArchivedOutcomes().includes(code);
}

function updateOutcomeArchiveControls() {
  const archived = isOutcomeArchived(currentOutcomeCode),
    button = document.getElementById("archiveOutcomeButton"),
    notice = document.getElementById("outcomeArchiveNotice");
  if (!button || !notice) return;
  notice.classList.toggle("hidden", !archived);
  button.textContent = archived ? "Terugzetten bij actieve leeruitkomsten" : "Leeruitkomst archiveren";
  button.classList.toggle("restore", archived);
  button.setAttribute("aria-label", archived ? "Leeruitkomst uit archief terugzetten" : "Afgeronde leeruitkomst archiveren");
}

function toggleOutcomeArchive() {
  if (!currentOutcomeCode) return;
  const archived = getArchivedOutcomes(),
    wasArchived = archived.includes(currentOutcomeCode),
    updated = wasArchived ? archived.filter((code) => code !== currentOutcomeCode) : [...archived, currentOutcomeCode];
  saveLocalState(archivedOutcomesKey, JSON.stringify(updated));
  const filter = document.getElementById("catalogArchiveFilter");
  filter.value = wasArchived ? "active" : "archived";
  renderCatalog();
  show("learningOutcomes");
  hint(wasArchived ? "De leeruitkomst staat weer bij je actieve leeruitkomsten." : "De leeruitkomst is gearchiveerd en blijft hier makkelijk terug te vinden.");
}

const openOutcomeWithArchiveState = openOutcome;
openOutcome = function (code) {
  openOutcomeWithArchiveState(code);
  updateOutcomeArchiveControls();
};

const renderCatalogWithArchiveFilter = renderCatalog;
renderCatalog = function () {
  renderCatalogWithArchiveFilter();
  const mode = document.getElementById("catalogArchiveFilter")?.value || "active",
    archived = getArchivedOutcomes(),
    list = document.getElementById("catalogList"),
    cards = [...list.querySelectorAll(".catalog-card")];
  let visible = 0;
  cards.forEach((card) => {
    const code = card.querySelector(".catalog-code")?.textContent.trim(),
      isArchived = archived.includes(code),
      showCard = mode === "all" || (mode === "archived" ? isArchived : !isArchived);
    card.classList.toggle("hidden", !showCard);
    card.classList.toggle("archived", isArchived);
    if (isArchived && !card.querySelector(".catalog-archive-badge"))
      card.insertAdjacentHTML("afterbegin", '<span class="catalog-archive-badge">Gearchiveerd</span>');
    if (showCard) visible++;
  });
  document.getElementById("archiveFilterNotice").classList.toggle("hidden", mode !== "archived");
  document.getElementById("catalogCount").textContent = visible + " " + (visible === 1 ? "leeruitkomst" : "leeruitkomsten") + " gevonden";
  if (!visible)
    list.insertAdjacentHTML(
      "beforeend",
      '<div class="card catalog-empty-archive"><h2>' +
        (mode === "archived" ? "Je archief is nog leeg" : "Geen leeruitkomsten gevonden") +
        "</h2><p>" +
        (mode === "archived" ? "Zodra je een afgeronde leeruitkomst archiveert, verschijnt die hier." : "Pas je zoekopdracht of filters aan.") +
        "</p></div>",
    );
};

const renderHoursWithUpdatedLabels = renderHours;
renderHours = function () {
  renderHoursWithUpdatedLabels();
  const emptyTitle = document.querySelector("#hoursList .empty-state h3");
  if (emptyTitle) emptyTitle.textContent = "Nog geen uren";
};

if (window.LARAVEL_USER) {
  if (role === "student") {
    renderGoalOptions();
    renderReflectionOptions();
    showReflectionOutcomePreview();
    updateFeedbackOutcomeOptions();
    renderDashboard();
    renderHours();
    renderCatalog();
    renderReflections();
    renderFeedback();
    updateHoursProcessingReadState();
  }

  login();
}
