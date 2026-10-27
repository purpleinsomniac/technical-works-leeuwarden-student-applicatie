<section id="studentDashboard" class="view">
          <div class="head">
            <div>
              <p class="eyebrow">JOUW PERSOONLIJKE LEEROMGEVING</p>
              <h1>Goed bezig, Sam <span class="wave">✦</span></h1>
              <p class="dim">Elke stap telt. Hier zie je waar je staat en hoe je verder groeit.</p>
            </div>
            <span class="pill">STUDENT</span>
          </div>
          <div class="notice">
            <b>Demogegevens:</b> de 48 codes en leeruitkomstteksten komen uit het aangeleverde overzicht. Percentages, uren, opdrachten, datums en resultaten
            zijn voorbeelden. De opdrachtgever moet de berekening en urennormen nog vaststellen.
          </div>
          <div class="tabs" id="phaseTabs"></div>
          <div class="grid3">
            <div class="card">
              <span class="metric-label">Jouw algemene voortgang</span>
              <div class="big" id="phasePercent"></div>
              <div class="dim">voorbeeldvoortgang</div>
              <div class="bar" id="phaseBar"></div>
            </div>
            <div class="card">
              <span class="metric-label">Waar ben je nu mee bezig?</span>
              <h2 class="dashboard-card-title" id="currentWorkTitle">Werkplek installeren</h2>
              <div class="dim" id="currentWorkMeta">Installatieopdracht · Oriëntatiefase</div>
              <div class="dashboard-inline-progress">
                <div class="bar">
                  <span id="currentWorkBar" style="width: 65%"></span>
                </div>
                <b id="currentWorkPercent">65%</b>
              </div>
              <button class="card-link" onclick="openCurrentWorkOutcome()">Bekijk leeruitkomst →</button>
            </div>
            <div class="card">
              <span class="metric-label">Vraagt jouw aandacht</span>
              <div class="big" id="attentionCount">1</div>
              <div class="dim" id="attentionText">leeruitkomst om verder aan te werken</div>
              <button class="card-link" onclick="showAttentionOutcomes()">Bekijk aandachtspunten →</button>
            </div>
          </div>
          <div class="card dashboard-summary">
            <div>
              <h2>Bekijk je voortgang per leeruitkomst</h2>
              <p>Op de pagina Leeruitkomsten zie je per vaardigheid je percentage en uren.</p>
              <p class="small dim">Laatst verwerkt: 12 september 2026, 09:30 (demo).</p>
            </div>
            <button class="button" onclick="show('learningOutcomes')">Naar leeruitkomsten ↗</button>
          </div>
          <div id="hoursApprovalModal" class="card hours-approval-modal hidden" role="dialog" aria-modal="true" aria-labelledby="hoursApprovalTitle">
            <div class="approval-modal-head">
              <div>
                <div class="approval-title-meta">
                  <p class="eyebrow">VERWERKING · 12 SEPTEMBER 2026</p>
                  <span id="processingReadStatus" class="processing-read-status unread">Ongelezen</span>
                </div>
                <h2 id="hoursApprovalTitle">42 nieuwe uren</h2>
                <p>Deze uren zijn gecontroleerd en gekoppeld aan drie onderdelen.</p>
              </div>
              <button class="modal-close" type="button" aria-label="Detailvenster sluiten" onclick="closeHoursApprovalModal()">×</button>
            </div>
            <div class="approval-summary">
              <div><span>Totaal goedgekeurd</span><strong>42 uur</strong></div>
              <div><span>Onderdelen</span><strong>3</strong></div>
              <div><span>Verwerkt op</span><strong>12 sep 2026</strong></div>
            </div>
            <div class="approval-list">
              <article class="approval-item">
                <div class="approval-hours">18<span>uur</span></div>
                <div>
                  <h3>Werkplek installeren</h3>
                  <strong>Eenvoudige vakgerichte taken uitvoeren</strong><span class="code">IT_OF_LU_Hardskills_001</span>
                </div>
                <div class="approval-person"><span>Goedgekeurd door</span><b>Mevrouw Jansen</b><small>Docent · 12 sep 2026</small></div>
              </article>
              <article class="approval-item">
                <div class="approval-hours">14<span>uur</span></div>
                <div>
                  <h3>Werkproces plannen</h3>
                  <strong>Voortgang bijhouden en rapporteren</strong><span class="code">IT_OF_LU_Hardskills_005</span>
                </div>
                <div class="approval-person"><span>Goedgekeurd door</span><b>Peter de Boer</b><small>Praktijkbegeleider · 11 sep 2026</small></div>
              </article>
              <article class="approval-item">
                <div class="approval-hours">10<span>uur</span></div>
                <div>
                  <h3>Teamreflectie</h3>
                  <strong>Samenwerken en reflecteren</strong><span class="code">IT_OF_LU_Softskills_003</span>
                </div>
                <div class="approval-person"><span>Goedgekeurd door</span><b>Fatima El Amrani</b><small>Coach · 10 sep 2026</small></div>
              </article>
            </div>
            <div class="approval-modal-footer">
              <span class="small dim">Deze gegevens zijn onderdeel van de mock-up.</span>
              <div class="approval-footer-actions">
                <button id="markProcessingReadButton" class="button" type="button" onclick="markHoursProcessingRead()">✓ Markeer als gelezen</button
                ><button class="button dark" type="button" onclick="closeHoursApprovalModal();show('hours')">Volledig urenoverzicht →</button>
              </div>
            </div>
          </div>
        </section>

        <section id="learningOutcomes" class="view hidden">
          <div class="head">
            <div>
              <p class="eyebrow">ONTDEK JE LEERROUTE</p>
              <h1>Leeruitkomsten</h1>
              <p class="dim">Wat ga je leren? Ontdek de kennis en vaardigheden van jouw opleiding.</p>
            </div>
            <span class="pill">48 LEERUITKOMSTEN</span>
          </div>
          <div class="catalog-intro">
            <span class="catalog-symbol">✧</span>
            <div>
              <h2>Een helder beeld van wat je leert</h2>
              <p>Bekijk per leeruitkomst de beschrijving, je voortgang en je uren.</p>
            </div>
          </div>
          <div class="filter catalog-filter">
            <label class="label search-label"
              >Zoeken<input id="outcomeSearch" class="input" type="search" placeholder="Zoek op onderwerp of code…" oninput="renderCatalog()" />
            </label>
            <label class="label"
              >Fase<select id="catalogPhase" class="input" onchange="renderCatalog()">
                <option value="OF">Oriëntatiefase</option>
                <option value="VF">Verdiepingsfase</option>
                <option value="AF">Afrondingsfase</option>
                <option value="all">Alle fasen</option>
              </select>
            </label>
            <label class="label"
              >Weergave<select id="catalogArchiveFilter" class="input" onchange="renderCatalog()">
                <option value="active">Actieve leeruitkomsten</option>
                <option value="archived">Gearchiveerde leeruitkomsten</option>
                <option value="all">Alles tonen</option>
              </select>
            </label>
          </div>
          <div id="archiveFilterNotice" class="archive-filter-notice hidden">
            <div>
              <span>✓</span>
              <p><b>Archief geopend</b> Hier vind je afgeronde leeruitkomsten terug.</p>
            </div>
          </div>
          <div id="catalogAttentionNotice" class="attention-filter-notice hidden">
            <div>
              <span>!</span>
              <p><b>Aandachtspunten actief</b> Je ziet alleen leeruitkomsten waar extra ontwikkeling nodig is.</p>
            </div>
            <button type="button" onclick="clearAttentionOutcomes()">Toon alle leeruitkomsten</button>
          </div>
          <p id="catalogCount" class="small dim" role="status"></p>
          <div id="catalogList" class="catalog-grid"></div>
        </section>
        <section id="outcomeDetail" class="view hidden">
          <button class="mutedlink" onclick="show('learningOutcomes')">← Terug naar leeruitkomsten</button>
          <div class="head">
            <div>
              <p class="eyebrow" id="outcomeMeta"></p>
              <h1 id="outcomeTitle"></h1>
              <span class="code" id="outcomeCode"> </span>
            </div>
            <span class="pill">LEERUITKOMST</span>
          </div>
          <div class="outcome-detail-grid">
            <div class="card detail-card">
              <h2>Wat je gaat leren</h2>
              <p id="outcomeText"></p>
              <div class="detail-section">
                <h2>Wat betekent dit in de praktijk?</h2>
                <p id="outcomePractical"></p>
              </div>
              <div class="detail-section">
                <h2>Hoe kun je dit aantonen?</h2>
                <ul class="evidence-list">
                  <li>Een afgeronde opdracht of beroepsproduct</li>
                  <li>Feedback van je docent, coach of opdrachtgever</li>
                  <li>Een korte reflectie op je aanpak en het resultaat</li>
                </ul>
              </div>
            </div>
            <aside class="card outcome-detail-stats">
              <span class="metric-label">Persoonlijke voortgang</span>
              <div class="big" id="detailPercent">—</div>
              <div class="bar">
                <span id="detailBar"> </span>
              </div>
              <div class="detail-stat">
                <span>Uren</span>
                <b id="detailHours">—</b>
              </div>
              <div class="detail-stat">
                <span>Richtwaarde</span>
                <b id="detailTarget">—</b>
              </div>
              <div class="detail-stat">
                <span>Status</span>
                <b id="detailStatus">Nog niet gestart</b>
              </div>
              <div id="outcomeArchiveNotice" class="outcome-archive-notice hidden">
                <span>✓</span>
                <div><b>Deze leeruitkomst is gearchiveerd</b><small>Alle uren, reflecties en feedback zijn bewaard.</small></div>
              </div>
              <button id="archiveOutcomeButton" class="button archive-outcome-button" type="button" onclick="toggleOutcomeArchive()">
                Leeruitkomst archiveren
              </button>
              <button class="button" onclick="openReflectionForCurrentOutcome()">Reflectie toevoegen</button>
              <div class="outcome-reflections-compact">
                <div class="compact-reflection-heading"><span>Reflecties bij deze leeruitkomst</span><small>Klik voor meer informatie</small></div>
                <div id="outcomeReflectionList" class="outcome-reflection-list"></div>
              </div>
              <div class="outcome-feedback-compact">
                <div class="compact-reflection-heading"><span>Feedback bij deze leeruitkomst</span><small>Ontvangen en gegeven feedback</small></div>
                <div id="outcomeFeedbackList" class="outcome-feedback-list"></div>
                <button class="compact-add-button" type="button" onclick="openFeedbackForCurrentOutcome()">+ Feedback toevoegen</button>
              </div>
            </aside>
          </div>
          <div
            id="outcomeReflectionDetailModal"
            class="card outcome-reflection-modal hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reflectionDetailTitle"
          >
            <div class="reflection-detail-modal-head">
              <div>
                <span class="reflection-date" id="reflectionDetailDate"></span>
                <h2 id="reflectionDetailTitle"></h2>
                <span class="code" id="reflectionDetailCode"></span>
              </div>
              <button class="modal-close" type="button" aria-label="Reflectiedetails sluiten" onclick="closeOutcomeReflectionDetail()">×</button>
            </div>
            <div id="reflectionDetailContent" class="reflection-detail-content"></div>
            <div class="reflection-detail-footer">
              <button class="button" type="button" onclick="closeOutcomeReflectionDetail()">Sluiten</button
              ><button class="button dark" type="button" onclick="closeOutcomeReflectionDetail();openReflectionForCurrentOutcome()">
                Nieuwe reflectie toevoegen
              </button>
            </div>
          </div>
          <div
            id="outcomeFeedbackDetailModal"
            class="card outcome-reflection-modal outcome-feedback-modal hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="feedbackDetailTitle"
          >
            <div class="reflection-detail-modal-head">
              <div>
                <div class="feedback-detail-meta">
                  <span id="feedbackDetailType" class="feedback-type"></span><span class="reflection-date" id="feedbackDetailDate"></span>
                </div>
                <h2 id="feedbackDetailTitle"></h2>
                <p id="feedbackDetailPerson" class="dim"></p>
                <span class="code" id="feedbackDetailCode"></span>
              </div>
              <button class="modal-close" type="button" aria-label="Feedbackdetails sluiten" onclick="closeOutcomeFeedbackDetail()">×</button>
            </div>
            <div id="feedbackDetailContent" class="reflection-detail-content feedback-detail-content"></div>
            <div class="reflection-detail-footer">
              <button class="button" type="button" onclick="closeOutcomeFeedbackDetail()">Sluiten</button
              ><button class="button dark" type="button" onclick="closeOutcomeFeedbackDetail();openFeedbackForCurrentOutcome()">
                Nieuwe feedback toevoegen
              </button>
            </div>
          </div>
        </section>
        <section id="hours" class="view hidden">
          <div class="head">
            <div>
              <h1>Uren</h1>
              <p class="dim">Bekijk per leerjaar je goedgekeurde registraties en totalen.</p>
            </div>
            <div class="hours-head-actions">
              <label class="year-selector"
                >Periode<select id="hoursYear" class="input" onchange="renderHours()">
                  <option value="1">Leerjaar 1</option>
                  <option value="2">Leerjaar 2</option>
                  <option value="3">Leerjaar 3</option>
                  <option value="4">Leerjaar 4</option>
                  <option value="total">Totaal opleiding</option>
                </select></label
              ><button class="button" onclick="emptyHours=!emptyHours;renderHours()">Toon lege toestand</button>
            </div>
          </div>
          <div class="notice">De norm van <b>800 uren per leerjaar</b> is een realistische demowaarde. De definitieve norm moet nog worden vastgesteld.</div>
          <div class="hours-overview card">
            <div class="hours-total">
              <span class="metric-label">Uren in <span id="hoursSelectedYear">Leerjaar 1</span></span>
              <div>
                <strong id="hoursYearTotal">678</strong>
                <span> / 800 uur</span>
              </div>
              <p class="dim" id="hoursYearPercentage">42% van de urennorm behaald</p>
            </div>
            <div class="hours-progress">
              <div id="hoursStackedBar" class="hours-stacked-bar"></div>
              <div class="hours-scale">
                <span>0 uur</span>
                <b id="hoursScalePercentage">42% van je doel behaald</b>
                <span>Doel: 800 uur</span>
              </div>
              <div id="hoursLegend" class="hours-legend"></div>
            </div>
          </div>
          <div class="grid3 hours-phases hidden" aria-hidden="true">
            <div class="card">
              <div class="big" id="hoursOrientation">342 uur</div>
              <span class="dim">oriëntatiefase</span>
            </div>
            <div class="card">
              <div class="big" id="hoursDeepening">221 uur</div>
              <span class="dim">verdiepingsfase</span>
            </div>
            <div class="card">
              <div class="big" id="hoursCompletion">115 uur</div>
              <span class="dim">afrondingsfase</span>
            </div>
          </div>
          <div class="card yearly-hours-card">
            <div class="yearly-hours-head">
              <div>
                <h2>Uren per leerjaar</h2>
                <p class="dim">Vergelijk de vier leerjaren of bekijk het totaal van de hele opleiding.</p>
              </div>
              <span class="pill">4 LEERJAREN + TOTAAL</span>
            </div>
            <div id="yearlyHoursOverview" class="yearly-hours-overview"></div>
          </div>
          <div class="card">
            <h2>Registraties in <span id="registrationsYear">Leerjaar 1</span></h2>
            <div id="hoursList"></div>
          </div>
          <p class="small dim">Laatste verwerking: 12-09-2026 09:30 (demo). Registraties zijn alleen na validatie zichtbaar.</p>
          <div id="registrationDetailModal" class="card registration-modal hidden" role="dialog" aria-modal="true" aria-labelledby="registrationDetailTitle">
            <div class="registration-modal-head">
              <div>
                <span id="registrationDetailStatus" class="registration-status">GEVALIDEERD</span>
                <h2 id="registrationDetailTitle"></h2>
                <p id="registrationDetailType" class="dim"></p>
              </div>
              <button class="modal-close" type="button" aria-label="Registratiedetails sluiten" onclick="closeRegistrationDetail()">×</button>
            </div>
            <div class="registration-hours-banner"><span>Goedgekeurde uren</span><strong id="registrationDetailHours"></strong></div>
            <div class="registration-detail-grid">
              <div><span>Leerjaar</span><b id="registrationDetailYear"></b></div>
              <div><span>Fase</span><b id="registrationDetailPhase"></b></div>
              <div><span>Gevalideerd op</span><b id="registrationDetailDate"></b></div>
              <div><span>Goedgekeurd door</span><b id="registrationDetailApprover"></b></div>
            </div>
            <div class="registration-outcome">
              <span>Gekoppelde leeruitkomst</span><strong id="registrationDetailOutcomeName"></strong><span class="code" id="registrationDetailCode"></span>
            </div>
            <div class="registration-modal-footer">
              <button class="button" type="button" onclick="closeRegistrationDetail()">Sluiten</button
              ><button class="button dark" type="button" onclick="openRegistrationOutcome()">Bekijk leeruitkomst →</button>
            </div>
          </div>
        </section>

        <section id="goals" class="view hidden">
          <div class="head">
            <div>
              <p class="eyebrow">KIES JE VOLGENDE STAP</p>
              <h1>Mijn leerdoelen</h1>
              <p class="dim">Koppel een persoonlijk doel aan een herkenbare leeruitkomst.</p>
            </div>
            <span class="pill">PERSOONLIJKE ROUTE</span>
          </div>
          <div class="notice">De naam van de leeruitkomst staat steeds voorop; de officiële code staat eronder ter herkenning.</div>
          <div class="grid2 goal-layout">
            <div class="card active-goal">
              <span class="goal-status">ACTIEF LEERDOEL · DEMO</span>
              <h2>Technische keuzes zelfstandig uitleggen</h2>
              <p>Ik wil na een installatie duidelijk kunnen toelichten welke keuzes ik heb gemaakt en waarom.</p>
              <div class="linked-outcome">
                <span class="small dim">Gekoppelde leeruitkomst</span>
                <strong>Eenvoudige vakgerichte taken uitvoeren</strong>
                <span class="code">IT_OF_LU_Hardskills_001</span>
              </div>
              <div class="goal-meta">
                <span>
                  <b>Bespreken</b>
                  <br />Volgend coachgesprek</span
                >
                <span>
                  <b>Fase</b>
                  <br />Oriëntatiefase</span
                >
              </div>
            </div>
            <div class="card goal-form">
              <h2>Nieuw leerdoel</h2>
              <label class="label" for="goalText">Wat wil je ontwikkelen?</label>
              <textarea id="goalText" class="input" placeholder="Beschrijf concreet wat je wilt leren of verbeteren"></textarea>
              <div class="goal-outcome-heading">
                <div>
                  <label class="label" for="goalPhase">Bij welke leeruitkomst past dit?</label
                  ><span class="field-help">Kies een fase. Daarna zie je alle leeruitkomsten uit die fase.</span>
                </div>
                <span id="goalChoiceCount" class="goal-choice-count"></span>
              </div>
              <div class="goal-outcome-filters">
                <label class="label" for="goalPhase"
                  >Fase<select class="input" id="goalPhase" onchange="renderGoalOptions()">
                    <option value="OF">Oriëntatiefase</option>
                    <option value="VF">Verdiepingsfase</option>
                    <option value="AF">Afrondingsfase</option>
                  </select></label
                >
              </div>
              <select class="hidden" id="goalCodes" aria-hidden="true" tabindex="-1"></select>
              <div id="goalOutcomeChoices" class="goal-outcome-choices" role="listbox" aria-label="Beschikbare leeruitkomsten"></div>
              <div id="goalOutcomePreview" class="goal-preview"></div>
              <button class="button dark" onclick="hint('Leerdoel opslaan is in deze mock-up nog niet gekoppeld.')">Leerdoel bewaren</button>
            </div>
          </div>
        </section>

        <section id="reflections" class="view hidden">
          <div class="head">
            <div>
              <p class="eyebrow">KIJK TERUG EN GROEI VERDER</p>
              <h1>Mijn reflecties</h1>
              <p class="dim">Sta stap voor stap stil bij een ervaring en koppel die aan je ontwikkeling.</p>
            </div>
            <div class="reflection-head-actions">
              <span class="pill" id="reflectionCount">0 REFLECTIES</span>
              <button class="button dark" type="button" onclick="openReflectionForm()">+ Nieuwe reflectie</button>
            </div>
          </div>
          <div class="notice">Kies eerst een leeruitkomst. Beschrijf daarna de situatie, wat goed ging, wat beter kan en je concrete volgende stap.</div>
          <div class="reflection-layout">
            <div>
              <div class="section-head">
                <div>
                  <h2>Eerdere reflecties</h2>
                  <p class="dim">Jouw persoonlijke leerjournaal, gekoppeld aan duidelijke leeruitkomsten.</p>
                </div>
              </div>
              <div class="reflection-filter" id="reflectionPhaseFilter" aria-label="Filter reflecties per fase"></div>
              <div id="reflectionList" class="reflection-list" aria-live="polite"></div>
            </div>
            <form id="reflectionForm" class="card reflection-form reflection-modal hidden" onsubmit="saveReflection(event)">
              <div class="form-heading">
                <span class="step-number">1</span>
                <div>
                  <h2>Nieuwe reflectie</h2>
                  <p class="small dim">Vul alle stappen in voor een complete terugblik.</p>
                </div>
                <button class="modal-close" type="button" aria-label="Reflectieformulier sluiten" onclick="closeReflectionForm()">×</button>
              </div>
              <label class="label" for="reflectionTitle">Opdracht of onderwerp</label>
              <span class="field-help">Waarover schrijf je deze reflectie?</span>
              <input id="reflectionTitle" class="input" maxlength="80" placeholder="Bijv. installatieopdracht voor een werkplek" required />
              <label class="label" for="reflectionPhase">Bij welke fase hoort je reflectie?</label>
              <span class="field-help">De gekozen fase bepaalt welke leeruitkomsten je kunt selecteren.</span>
              <select id="reflectionPhase" class="input" onchange="updateReflectionOutcomeOptions()" required>
                <option value="OF">Oriëntatiefase</option>
                <option value="VF">Verdiepingsfase</option>
                <option value="AF">Afrondingsfase</option>
              </select>
              <label class="label" for="reflectionOutcome">Bij welke leeruitkomst past dit?</label>
              <span class="field-help">Kies op naam; de officiële code staat achter de naam.</span>
              <select id="reflectionOutcome" class="input" onchange="showReflectionOutcomePreview()" required></select>
              <div id="reflectionOutcomePreview" class="goal-preview reflection-preview"></div>
              <div class="reflection-questions">
                <label class="label" for="reflectionSituation">1. Wat was de situatie?</label>
                <span class="field-help">Beschrijf kort de opdracht, jouw rol en wat je wilde bereiken.</span>
                <textarea
                  id="reflectionSituation"
                  class="input"
                  minlength="10"
                  maxlength="500"
                  placeholder="Tijdens deze opdracht werkte ik aan…"
                  required
                ></textarea>
                <label class="label" for="reflectionGood">2. Wat ging goed?</label>
                <span class="field-help">Noem concreet gedrag, een keuze of een resultaat waar je tevreden over bent.</span>
                <textarea id="reflectionGood" class="input" minlength="10" maxlength="500" placeholder="Wat goed ging, was…" required></textarea>
                <label class="label" for="reflectionImprove">3. Wat kan beter?</label>
                <span class="field-help">Beschrijf wat lastig was en wat je daarvan hebt geleerd.</span>
                <textarea
                  id="reflectionImprove"
                  class="input"
                  minlength="10"
                  maxlength="500"
                  placeholder="Een volgende keer wil ik verbeteren dat…"
                  required
                ></textarea>
                <label class="label" for="reflectionNext">4. Wat is je volgende stap?</label>
                <span class="field-help">Maak je voornemen klein, concreet en uitvoerbaar.</span>
                <textarea id="reflectionNext" class="input" minlength="10" maxlength="500" placeholder="Bij mijn volgende opdracht ga ik…" required></textarea>
              </div>
              <div id="reflectionMessage" class="form-message hidden" role="status"></div>
              <button class="button dark" type="submit">Reflectie bewaren</button>
              <p class="privacy-note">Deze mock-up bewaart je reflectie alleen in deze browser.</p>
            </form>
          </div>
        </section>

        <section id="feedback" class="view hidden">
          <div class="head">
            <div>
              <p class="eyebrow">SAMEN LEREN EN VERBETEREN</p>
              <h1>Mijn feedback</h1>
              <p class="dim">Bewaar ontvangen en gegeven feedback en koppel deze aan je leeruitkomsten.</p>
            </div>
            <div class="feedback-head-actions">
              <span class="pill" id="feedbackCount">0 FEEDBACKMOMENTEN</span>
              <button class="button dark" type="button" onclick="openFeedbackForm()">+ Feedback toevoegen</button>
            </div>
          </div>
          <div class="feedback-intro">
            <div class="feedback-intro-icon">↗</div>
            <div>
              <h2>Van feedback naar een concrete vervolgstap</h2>
              <p>Leg vast wie de feedback gaf, wat al goed gaat en waar je gericht aan wilt werken.</p>
            </div>
          </div>
          <div class="feedback-toolbar">
            <div class="reflection-filter" id="feedbackPhaseFilter" aria-label="Filter feedback per fase"></div>
            <label class="feedback-type-filter"
              >Soort feedback<select id="feedbackTypeFilter" class="input" onchange="renderFeedback()">
                <option value="all">Alles</option>
                <option value="received">Ontvangen</option>
                <option value="given">Gegeven</option>
              </select></label
            >
          </div>
          <div id="feedbackList" class="feedback-list" aria-live="polite"></div>

          <form id="feedbackForm" class="card feedback-form feedback-modal hidden" onsubmit="saveFeedback(event)">
            <div class="form-heading">
              <span class="step-number">1</span>
              <div>
                <h2>Feedback toevoegen</h2>
                <p class="small dim">Maak de feedback concreet en bruikbaar.</p>
              </div>
              <button class="modal-close" type="button" aria-label="Feedbackformulier sluiten" onclick="closeFeedbackForm()">×</button>
            </div>
            <div class="feedback-form-grid">
              <label class="label" for="feedbackType"
                >Soort feedback<span class="field-help">Heb je de feedback ontvangen of zelf gegeven?</span
                ><select id="feedbackType" class="input" required>
                  <option value="received">Feedback ontvangen</option>
                  <option value="given">Feedback gegeven</option>
                </select></label
              >
              <label class="label" for="feedbackPerson"
                >Van of aan wie?<span class="field-help">Bijvoorbeeld je docent, coach of teamgenoot.</span
                ><input id="feedbackPerson" class="input" maxlength="80" placeholder="Naam en eventueel rol" required
              /></label>
            </div>
            <label class="label" for="feedbackTitle">Onderwerp of opdracht</label>
            <span class="field-help">Waar ging de feedback over?</span>
            <input id="feedbackTitle" class="input" maxlength="80" placeholder="Bijv. presentatie van het projectplan" required />
            <label class="label" for="feedbackPhase">Bij welke fase hoort de feedback?</label>
            <select id="feedbackPhase" class="input" onchange="updateFeedbackOutcomeOptions()" required>
              <option value="OF">Oriëntatiefase</option>
              <option value="VF">Verdiepingsfase</option>
              <option value="AF">Afrondingsfase</option>
            </select>
            <label class="label" for="feedbackOutcome">Gekoppelde leeruitkomst</label>
            <span class="field-help">Kies op naam; de officiële code staat erachter.</span>
            <select id="feedbackOutcome" class="input" onchange="showFeedbackOutcomePreview()" required></select>
            <div id="feedbackOutcomePreview" class="goal-preview feedback-preview"></div>
            <div class="feedback-questions">
              <label class="label" for="feedbackPositive">Wat gaat al goed?</label><span class="field-help">Noteer het sterke punt zo concreet mogelijk.</span
              ><textarea id="feedbackPositive" class="input" minlength="10" maxlength="600" placeholder="Een sterk punt is…" required></textarea>
              <label class="label" for="feedbackImprove">Wat kan beter?</label
              ><span class="field-help">Beschrijf gedrag of werk dat verder ontwikkeld kan worden.</span
              ><textarea id="feedbackImprove" class="input" minlength="10" maxlength="600" placeholder="Een verbeterpunt is…" required></textarea>
              <label class="label" for="feedbackAction">Welke afspraak of vervolgstap volgt hieruit?</label
              ><span class="field-help">Maak de stap concreet en uitvoerbaar.</span
              ><textarea id="feedbackAction" class="input" minlength="10" maxlength="600" placeholder="Bij de volgende opdracht ga ik…" required></textarea>
            </div>
            <div id="feedbackMessage" class="form-message hidden" role="status"></div>
            <button class="button dark" type="submit">Feedback bewaren</button>
            <p class="privacy-note">Deze mock-up bewaart feedback alleen in deze browser.</p>
          </form>
        </section>

        <section id="history" class="view hidden">
          <h1>Ontwikkeling over tijd</h1>
          <p class="dim">Vergelijk perioden en bekijk hoe uren en leeruitkomsten zijn opgebouwd.</p>
          <div class="notice">De getallen en datums zijn voorbeelden. Welke perioden en peildata beschikbaar zijn, moet worden vastgesteld.</div>
          <div class="grid2">
            <div class="card">
              <h2>Periode 1 · demo</h2>
              <div class="big">42%</div>
              <div class="bar">
                <span style="width: 42%"> </span>
              </div>
              <p>254 uren</p>
            </div>
            <div class="card">
              <h2>Periode 2 · demo</h2>
              <div class="big">60%</div>
              <div class="bar">
                <span style="width: 60%"> </span>
              </div>
              <p>342 uren</p>
            </div>
          </div>
          <div class="card">
            <h2>Gebeurtenissen</h2>
            <div class="timeline">
              <div class="event">
                <b>12 september 2026 · verwerking</b>
                <p>Nieuwe uren opgenomen; voorbeeldvoortgang bijgewerkt.</p>
              </div>
              <div class="event">
                <b>4 september 2026 · opdracht</b>
                <p>Installatieopdracht gevalideerd en gekoppeld aan IT_OF_LU_Hardskills_001.</p>
              </div>
            </div>
          </div>
        </section>

        {{-- Coachweergaven. --}}

