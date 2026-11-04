@extends('layouts.application')

@section('role-content')
<section id="studentDashboard" class="view student-dashboard">
          {{-- Welkomstregel en snelle toegang tot persoonlijke dashboardinstellingen. --}}
          <div class="dashboard-welcome">
            <div>
              <h1>Goed bezig, {{ $frontendUser['name'] }} <span class="wave">👋</span></h1>
              <p>Hier zie je waar je staat en wat je volgende stap is.</p>
            </div>
            <button class="dashboard-settings-shortcut" type="button" onclick="show('dashboardSettings')">
              <span class="ui-icon" data-icon="settings" aria-hidden="true"></span>
              Dashboard instellen
            </button>
          </div>

          <div class="tabs dashboard-phase-tabs" id="phaseTabs" aria-label="Studiefase kiezen"></div>

          {{-- De drie belangrijkste gegevens blijven altijd zichtbaar. --}}
          <div class="dashboard-primary-grid">
            <article class="dashboard-metric-card dashboard-progress-card">
              <div class="dashboard-card-heading">
                <span class="metric-label" id="dashboardPhaseTitle">Voortgang in deze fase</span>
                <span class="dashboard-card-icon" data-icon="progress" aria-hidden="true"></span>
              </div>
              <div class="big" id="phasePercent"></div>
              <div class="dim" id="phaseProgressLabel">van deze fase behaald</div>
              <div class="bar" id="phaseBar"></div><svg class="dashboard-phase-mountain" viewBox="0 0 280 130" aria-hidden="true"><path d="m20 130 80-85 80 85" fill="#9ee2c9"/><path d="m85 130 86-100 94 100" fill="#54c9a5"/><path d="m145 130 64-115 71 115" fill="#20ad88"/><path d="m199 32 10-17 13 24-13-8z" fill="#c8f3e4"/><path d="M209 15V0q17 9 32 0v14q-16 10-32 0" fill="#098961"/></svg>
            </article>

            <article class="dashboard-metric-card dashboard-assignment-card">
              <div class="dashboard-card-heading">
                <span class="metric-label">Huidig project</span>
                <span class="dashboard-card-icon" data-icon="assignment" aria-hidden="true"></span>
              </div>
              <h2 class="dashboard-card-title" id="currentWorkTitle">Project laden…</h2>
              <div class="dim" id="currentWorkMeta">De gegevens worden uit de database geladen.</div>
              <button id="dashboardCurrentProjectButton" class="card-link" type="button" onclick="show('assignments')">Ga verder <span aria-hidden="true">→</span></button>
            </article>


          </div>


          <div id="weekPlanModal" class="goal-detail-modal hidden" role="dialog" aria-modal="true" aria-labelledby="weekPlanTitle"><button class="goal-detail-backdrop" type="button" tabindex="-1" aria-label="Weekplanning sluiten" onclick="closeWeekPlan()"></button><section class="goal-detail-dialog week-plan-dialog"><button class="modal-close" type="button" aria-label="Weekplanning sluiten" onclick="closeWeekPlan()">×</button><h2 id="weekPlanTitle">To do van de week</h2><label class="label" for="weekPlanDate">Week van</label><input id="weekPlanDate" class="input" type="date" onchange="renderWeekPlan()"><form onsubmit="addWeekTask(event)"><label class="label" for="weekTaskText">Wat wil je deze week doen?</label><input id="weekTaskText" class="input" maxlength="200" required placeholder="Bijvoorbeeld: mijn projectdocumentatie afronden"><button class="button dark" type="submit">＋ Taak toevoegen</button></form><div id="weekTaskList" class="week-task-list"></div><p id="weekPlanMessage" class="form-message" role="status"></p></section></div>
          {{-- Deze onderdelen kan de student via Instellingen tonen of verbergen. --}}
          <div class="dashboard-widget-grid">
            <section class="dashboard-panel dashboard-widget" data-dashboard-widget="outcomes">
              <div class="dashboard-panel-head">
                <div class="dashboard-panel-title">
                  <span class="panel-icon" data-icon="outcomes" aria-hidden="true"></span>
                  <h2>Voortgang per leeruitkomst</h2>
                </div>
                <button class="card-link" type="button" onclick="show('learningOutcomes')">Alle leeruitkomsten →</button>
              </div>
              <div id="dashboardOutcomeProgress" class="dashboard-outcome-list"></div>
            </section>

            <section class="dashboard-panel dashboard-widget dashboard-current-goals" data-dashboard-widget="goals">
              <div class="dashboard-goals-heading"><span class="dashboard-goals-icon" data-icon="target" aria-hidden="true"></span><h2>Huidige leerdoelen</h2></div>
              <p id="dashboardGoalsCaption" class="dashboard-goals-caption"></p>
              <div id="dashboardActionList" class="dashboard-action-list"></div>
              <button class="card-link" type="button" onclick="show('goals')">Alle leerdoelen <span id="dashboardActiveGoalCount">(0 actief)</span> →</button>
            </section>
            <section class="dashboard-panel dashboard-widget dashboard-week-card" data-dashboard-widget="actions">
              <div class="dashboard-panel-head">
                <div class="dashboard-panel-title">
                  <span class="panel-icon" data-icon="list" aria-hidden="true"></span>
                  <h2>To do van de week</h2>
                </div>
                <button class="card-link" type="button" onclick="openWeekPlan()">Mijn weekplanning →</button>
              </div>
              <div class="dim" id="attentionText">Maak je eigen planning voor deze week</div>
              <span id="attentionCount" class="hidden"></span>
              <ul id="weekPlanPreview" class="week-plan-preview" aria-label="Planning deze week"></ul>
            </section>

            <section class="dashboard-panel dashboard-widget" data-dashboard-widget="feedback">
              <div class="dashboard-panel-head">
                <div class="dashboard-panel-title">
                  <span class="panel-icon panel-icon-green" data-icon="feedback" aria-hidden="true"></span>
                  <h2>Recente feedback</h2>
                </div>
                <button class="card-link" type="button" onclick="show('feedback')">Alle feedback →</button>
              </div>
              <div id="dashboardRecentFeedback"></div>
            </section>

            <section class="dashboard-panel dashboard-widget" data-dashboard-widget="deadlines">
              <div class="dashboard-panel-head">
                <div class="dashboard-panel-title">
                  <span class="panel-icon panel-icon-orange" data-icon="calendar" aria-hidden="true"></span>
                  <h2>Aankomende deadlines</h2>
                </div>
                <button class="card-link" type="button" onclick="show('assignments')">Bekijk projecten →</button>
              </div>
              <div id="dashboardDeadlineList" class="dashboard-deadline-list"></div>
            </section>
          </div>

          <div id="hoursApprovalModal" class="card hours-approval-modal hidden" role="dialog" aria-modal="true" aria-labelledby="hoursApprovalTitle">
            <div class="approval-modal-head">
              <div>
                <div class="approval-title-meta">
                  <p id="hoursApprovalEyebrow" class="eyebrow">URENVERWERKING</p>
                  <span id="processingReadStatus" class="processing-read-status unread">Ongelezen</span>
                </div>
                <h2 id="hoursApprovalTitle">Nieuwe punten</h2>
                <p id="hoursApprovalDescription">De gevalideerde punten worden uit de database geladen.</p>
              </div>
              <button class="modal-close" type="button" aria-label="Detailvenster sluiten" onclick="closeHoursApprovalModal()">×</button>
            </div>
            <div class="approval-summary">
              <div><span>Totaal goedgekeurd</span><strong id="hoursApprovalTotal">0 punten</strong></div>
              <div><span>Onderdelen</span><strong id="hoursApprovalCount">0</strong></div>
              <div><span>Verwerkt op</span><strong id="hoursApprovalDate">—</strong></div>
            </div>
            <div id="hoursApprovalList" class="approval-list"></div>
            <div class="approval-modal-footer">
              <span class="small dim">Alleen gevalideerde punten worden getoond.</span>
              <div class="approval-footer-actions">
                <button id="markProcessingReadButton" class="button" type="button" onclick="markHoursProcessingRead()">✓ Markeer als gelezen</button
                ><button class="button dark" type="button" onclick="closeHoursApprovalModal();show('hours')">Volledig urenoverzicht →</button>
              </div>
            </div>
          </div>
        </section>

        <section id="dashboardSettings" class="view hidden dashboard-settings-page">
          <div class="settings-page-head">
            <button class="back-link" type="button" onclick="show('studentDashboard')">← Terug naar dashboard</button>
            <p class="eyebrow">PERSOONLIJKE VOORKEUREN</p>
            <h1>Dashboard instellen</h1>
            <p class="dim">Kies zelf hoeveel informatie je op het dashboard wilt zien. Deze voorkeuren worden alleen voor jouw account bewaard.</p>
          </div>

          <div class="settings-layout">
            <section class="card settings-card">
              <div class="settings-card-head">
                <span class="settings-card-icon" data-icon="layout" aria-hidden="true"></span>
                <div>
                  <h2>Weergave</h2>
                  <p>Maak de onderdelen compacter als je meer rust op het scherm wilt.</p>
                </div>
              </div>
              <label class="settings-option">
                <span><strong>Compacte weergave</strong><small>Minder witruimte en lagere dashboardkaarten.</small></span>
                <input id="settingCompact" type="checkbox" onchange="updateDashboardPreference('compact', this.checked)">
              </label>
              <label class="settings-option">
                <span><strong>Donkere modus</strong><small>Gebruikt donkere kleuren met een rustig contrast.</small></span>
                <input id="settingDarkMode" type="checkbox" onchange="updateDashboardPreference('darkMode', this.checked)">
              </label>
            </section>

            <section class="card settings-card">
              <div class="settings-card-head">
                <span class="settings-card-icon" data-icon="eye" aria-hidden="true"></span>
                <div>
                  <h2>Zichtbare onderdelen</h2>
                  <p>Zet onderdelen uit die je niet iedere dag nodig hebt.</p>
                </div>
              </div>
              <div class="settings-options">
                <label class="settings-option"><span class="settings-option-icon" data-icon="progress" aria-hidden="true"></span><span><strong>Voortgang per leeruitkomst</strong><small>Toont de vier belangrijkste leeruitkomsten.</small></span><input id="settingOutcomes" type="checkbox" onchange="updateDashboardPreference('outcomes', this.checked)"></label>
                <label class="settings-option"><span class="settings-option-icon" data-icon="target" aria-hidden="true"></span><span><strong>Huidige leerdoelen</strong><small>Toont maximaal twee actieve leerdoelen in de gekozen fase.</small></span><input id="settingGoals" type="checkbox" onchange="updateDashboardPreference('goals', this.checked)"></label>
                <label class="settings-option"><span class="settings-option-icon" data-icon="target" aria-hidden="true"></span><span><strong>To do van de week</strong><small>Toont je eigen weekplanning en openstaande taken.</small></span><input id="settingActions" type="checkbox" onchange="updateDashboardPreference('actions', this.checked)"></label>
                <label class="settings-option"><span class="settings-option-icon" data-icon="feedback" aria-hidden="true"></span><span><strong>Recente feedback</strong><small>Toont het laatste feedbackmoment.</small></span><input id="settingFeedback" type="checkbox" onchange="updateDashboardPreference('feedback', this.checked)"></label>
                <label class="settings-option"><span class="settings-option-icon" data-icon="calendar" aria-hidden="true"></span><span><strong>Aankomende deadlines</strong><small>Toont de eerstvolgende inleverdatums.</small></span><input id="settingDeadlines" type="checkbox" onchange="updateDashboardPreference('deadlines', this.checked)"></label>
              </div>
            </section>
          </div>

          <div class="settings-footer">
            <div class="settings-save-copy"><span class="settings-save-check" aria-hidden="true">✓</span><div><strong>Wijzigingen worden alleen voor jouw account bewaard.</strong><small id="dashboardSettingsSaved" class="settings-saved" role="status">Je voorkeuren worden automatisch opgeslagen.</small></div></div>
            <button class="button" type="button" onclick="resetDashboardPreferences()">Standaardinstellingen herstellen</button><button id="saveDashboardSettingsButton" class="button dark" type="button" onclick="saveDashboardSettings()">✓ Wijzigingen opslaan</button>
          </div>
        </section>

        <section id="learningOutcomes" class="view hidden">
          <div class="head learning-catalog-head">
            <div>
              <p class="eyebrow">ONTWIKKELING</p>
              <h1>Mijn leerontwikkeling</h1>
              <p class="dim">Inzicht in mijn voortgang, leeruitkomsten en ontwikkeling in deze opleiding.</p>
            </div>
            <span id="catalogTotalPill" class="pill">48 LEERUITKOMSTEN</span>
          </div>

          {{-- Eén samenvatting toont de totale voortgang en de drie statussen. --}}
          <div class="development-metrics">
            <article><span class="dev-metric-icon">◎</span><div><small>Totaal leeruitkomsten</small><strong id="devTotal">0</strong></div></article>
            <article><span class="dev-metric-icon">✓</span><div><small>Afgerond</small><strong id="catalogCompletedCount">0</strong></div><b id="devCompletedPct"></b></article>
            <article><span class="dev-metric-icon purple">▥</span><div><small>Lopend</small><strong id="catalogActiveCount">0</strong></div><b id="devActivePct"></b></article>
            <article><span class="dev-metric-icon amber">⌛</span><div><small>Nog te starten</small><strong id="catalogTodoCount">0</strong></div><b id="devTodoPct"></b></article>
          </div>
          <div class="development-insights">
            <article class="development-panel"><h2>Totale voortgang</h2><div class="development-total"><div id="catalogProgressRing" class="learning-progress-ring"><strong id="catalogProgressPercent">0%</strong></div><div><strong id="catalogProgressTitle"></strong><p id="catalogProgressText"></p><div class="bar"><span id="catalogProgressBar"></span></div></div></div></article>
            <article class="development-panel"><h2>Voortgang per vaardigheid</h2><div id="developmentSkills"></div></article>
            <article class="development-panel"><h2>Punten per fase</h2><p class="dim small">Behaalde punten in jouw opleiding</p><div id="developmentPhasePoints"></div></article>
          </div>

          {{-- De fasekeuze bepaalt welke voortgang en leeruitkomsten worden getoond. --}}
          <div class="learning-phase-row">
            <div id="catalogPhaseTabs" class="learning-phase-tabs" aria-label="Fase kiezen"></div>
          </div>
          <input id="catalogPhase" type="hidden" value="OF">

          <div class="catalog-filter learning-catalog-filter">
            <label class="learning-search">
              <span class="learning-search-icon" aria-hidden="true">⌕</span>
              <span class="sr-only">Zoeken</span>
              <input id="outcomeSearch" class="input" type="search" placeholder="Zoek op leeruitkomst, code of onderwerp…" oninput="resetCatalogAndRender()">
            </label>
            <label class="label">Status
              <select id="catalogArchiveFilter" class="input" onchange="resetCatalogAndRender()">
                <option value="all">Alle statussen</option>
                <option value="completed">Afgerond</option>
                <option value="active">Bezig</option>
                <option value="todo">Nog te doen</option>
                <option value="archived">Gearchiveerde leeruitkomsten</option>
              </select>
            </label>
            <label class="label">Sorteer op
              <select id="catalogSort" class="input" onchange="resetCatalogAndRender()">
                <option value="standard">Standaard</option>
                <option value="progress-desc">Meeste voortgang</option>
                <option value="progress-asc">Minste voortgang</option>
                <option value="alphabetical">Alfabetisch</option>
              </select>
            </label>
          </div>

          <div id="archiveFilterNotice" class="archive-filter-notice hidden">
            <div>
              <span>✓</span>
              <p><b>Archief geopend</b> Hier vind je afgeronde leeruitkomsten terug.</p>
            </div>
          </div>
          <div id="catalogList" class="catalog-grid learning-outcome-list"></div>
          <div class="learning-catalog-footer">
            <p id="catalogCount" class="small dim" role="status"></p>
            <nav id="catalogPagination" class="catalog-pagination" aria-label="Pagina's leeruitkomsten"></nav>
          </div>
        </section>
        <section id="outcomeDetail" class="view hidden">
          <button id="outcomeBackButton" class="mutedlink" type="button" onclick="backFromOutcome()">← Terug naar leeruitkomsten</button>
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
                  <li>Een afgeronde project of beroepsproduct</li>
                  <li>Feedback van je docent, coach of projectgever</li>
                  <li>Een korte reflectie op je aanpak en het resultaat</li>
                </ul>
                <button class="button dark" type="button" onclick="show('learningOutcomes')">← Terug naar alle leeruitkomsten</button>
              </div>
            </div>
            <aside class="card outcome-detail-stats">
              <span class="metric-label">Persoonlijke voortgang</span>
              <div class="big" id="detailPercent">—</div>
              <div class="bar">
                <span id="detailBar"> </span>
              </div>
              <div class="detail-stat">
                <span>Punten</span>
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
                <div><b>Deze leeruitkomst is gearchiveerd</b><small>Alle punten, reflecties en feedback zijn bewaard.</small></div>
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

        {{-- Projectenoverzicht met de leeruitkomsten die per project worden ontwikkeld. --}}
        <section id="assignments" class="view hidden assignments-page projects-redesign">
          <header class="assignments-header">
            <p class="eyebrow">JOUW WERK EN BEWIJSSTUKKEN</p>
            <h1>Projecten</h1>
            <p class="dim">Bekijk je projecten en werk aan de leeruitkomsten.</p>
            <span class="sr-only" id="assignmentTotal">0 PROJECTEN</span>
          </header>



          <input id="assignmentStatus" type="hidden" value="active">

          <div class="project-summary-grid"><article><span data-icon="assignment" class="project-summary-icon"></span><div><small>Actieve projecten</small><strong id="projectSummaryActive">0</strong></div></article><article><span data-icon="target" class="project-summary-icon"></span><div><small>Afgerond</small><strong id="projectSummaryCompleted">0</strong></div></article><article><span data-icon="calendar" class="project-summary-icon"></span><div><small>Dichtstbijzijnde deadline</small><strong id="projectNearestDeadline" class="project-summary-date">Geen deadline</strong></div></article></div>
          <div id="projectSpotlight" class="project-spotlight">
          <article id="currentAssignmentCard" class="current-assignment-card" aria-labelledby="currentAssignmentTitle">
            <div class="current-assignment-main">
              <span id="currentAssignmentIcon" class="current-assignment-icon" aria-hidden="true"></span>
              <div class="current-assignment-copy">
                <span class="current-assignment-label">HUIDIG PROJECT</span>
                <h2 id="currentAssignmentTitle">Project laden…</h2>
                <p id="currentAssignmentDescription"></p>
              </div>
              <button id="currentAssignmentButton" class="current-assignment-button" type="button">Ga verder <span aria-hidden="true">→</span></button>
            </div>
            <div class="current-assignment-meta">
              <div><span class="current-meta-icon" data-current-icon="phase" aria-hidden="true"></span><span><small>Fase</small><strong id="currentAssignmentPhases">—</strong></span></div>
              <div><span class="current-meta-icon" data-current-icon="calendar" aria-hidden="true"></span><span><small>Inleverdatum</small><strong id="currentAssignmentDueDate">—</strong></span></div>
              <div><span class="current-meta-icon" data-current-icon="outcomes" aria-hidden="true"></span><span><small>Punten toegekend</small><strong id="currentAssignmentOutcomes">—</strong></span></div>
            </div>
          </article>
          <aside class="project-next-step"><span class="project-summary-icon" data-icon="target"></span><h2>Volgende stap</h2><p id="projectNextStepText">Werk je huidige project verder uit en lever het op tijd in.</p><svg class="project-mountain" viewBox="0 0 300 115" aria-hidden="true"><path d="M0 100Q60 40 115 85T300 60V115H0" fill="#dbf5ed"/><path d="m100 115 70-80 50 80" fill="#8cddc8"/><path d="m157 115 65-100 78 100" fill="#22b49a"/><path d="m210 33 12-18 15 20-15-7z" fill="#e1fff5"/><path d="M222 15V0m0 0q22 12 35 1v17q-14 9-35-1" fill="#09967b" stroke="#09967b" stroke-width="3"/></svg></aside>
          </div>
          <div class="assignment-status-tabs" role="tablist" aria-label="Projecten per status">
            <button class="assignment-status-tab active" type="button" role="tab" aria-selected="true" data-assignment-status="active" onclick="selectAssignmentStatus('active')">
              <span class="assignment-tab-icon" data-assignment-tab-icon="active" aria-hidden="true"></span>
              <span><strong>Projecten</strong><small><b id="assignmentActiveCount">0</b> actieve projecten</small></span>
            </button>
            <button class="assignment-status-tab" type="button" role="tab" aria-selected="false" data-assignment-status="completed" onclick="selectAssignmentStatus('completed')">
              <span class="assignment-tab-icon" data-assignment-tab-icon="completed" aria-hidden="true"></span>
              <span><strong>Afgerond</strong><small><b id="assignmentCompletedCount">0</b> voltooide projecten</small></span>
            </button>
          </div>
          <div class="assignment-section-head">
            <label class="project-search"><span class="sr-only">Zoek een project</span><input id="projectSearch" type="search" class="input" placeholder="Zoek op project of leeruitkomst…" oninput="renderAssignments()"></label>
            <div>
              <h2 id="assignmentSectionTitle">Actieve projecten</h2>
              <p id="assignmentSectionDescription">Dit zijn je projecten die nog openstaan. Lever ze op tijd in.</p>
            </div>
            <label class="assignment-sort">Sorteer op
              <select id="assignmentSort" class="input" onchange="renderAssignments()">
                <option value="deadline">Deadline (dichtstbij)</option>
                <option value="title">Naam (A-Z)</option>
              </select>
            </label>
          </div>

          <p id="assignmentCount" class="sr-only" role="status"></p>
          <div id="assignmentList" class="assignment-list"></div>
        </section>

        {{-- Detailpagina van één project; de inhoud wordt vanuit student.js ingevuld. --}}
        <section id="assignmentDetail" class="view hidden">
          <button class="mutedlink" type="button" onclick="show('assignments')">← Terug naar projecten</button>
          <div class="head assignment-detail-head">
            <div>
              <p class="eyebrow" id="assignmentDetailType"></p>
              <h1 id="assignmentDetailTitle"></h1>
              <p class="dim" id="assignmentDetailIntro"></p>
            </div>
            <span class="assignment-status" id="assignmentDetailStatus"></span>
          </div>
          <div class="assignment-detail-grid">
            <article class="card assignment-detail-main">
              <h2>Over deze project</h2>
              <p id="assignmentDetailDescription"></p>
              <section class="assignment-files" aria-labelledby="assignmentFilesTitle">
                <div class="assignment-files-head">
                  <div>
                    <h3 id="assignmentFilesTitle">Bestanden</h3>
                    <p>Voeg bewijsstukken, documenten of afbeeldingen toe aan je project.</p>
                  </div>
                  <label id="assignmentFileUploadLabel" class="assignment-file-upload">
                    <input id="assignmentFileInput" type="file" accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg,.gif,.webp,.xls,.xlsx,.csv,.ppt,.pptx,.zip" onchange="uploadAssignmentFile(this)">
                    <span>+ Bestand toevoegen</span>
                  </label>
                </div>
                <p id="assignmentFileHelp" class="assignment-file-help">Maximaal 10 MB per bestand.</p>
                <div id="assignmentFileList" class="assignment-file-list"></div>
              </section>
              <div class="assignment-detail-information">
                <div><span>Fase</span><b id="assignmentDetailPhase"></b></div>
                <div><span>Inleverdatum</span><b id="assignmentDetailDueDate"></b></div>
                <div><span>Gevalideerde punten</span><b id="assignmentDetailHours"></b></div>
              </div>
              <div class="assignment-submit-panel hidden">
                <div>
                  <h3>Project gereed?</h3>
                  <p id="assignmentSubmissionNote">Lever je project in wanneer alle onderdelen klaar zijn.</p>
                </div>
                <div class="assignment-submit-actions">
                  <button id="assignmentReopenButton" class="button assignment-reopen-button hidden" type="button" onclick="reopenAssignment()">Terugzetten naar projecten</button>
                  <button id="assignmentSubmitButton" class="button dark" type="button" onclick="submitAssignment()">Project inleveren</button>
                </div>
              </div>
            </article>
            <aside class="card assignment-detail-outcomes">
              <div class="assignment-outcomes-head">
                <div>
                  <p class="eyebrow">ONTWIKKELING</p>
                  <h2>Gekoppelde leeruitkomsten</h2>
                </div>
                <span id="assignmentDetailOutcomeCount">0</span>
              </div>
              <p class="small dim">Klik op een leeruitkomst om de volledige beschrijving en voortgang te bekijken.</p>
              <div id="assignmentDetailOutcomeList"></div>
            </aside>
          </div>
        </section>

        <section id="hours" class="view hidden hours-page">
          <header class="hours-page-head">
            <div>
              <h1>Punten</h1>
              <p>Bekijk per leerjaar je geregistreerde punten. De punten worden geregistreerd door je docent.</p>
            </div>
            <label class="year-selector">Periode
              <select id="hoursYear" class="input" onchange="resetHoursAndRender()">
                <option value="1">Leerjaar 1</option>
                <option value="2">Leerjaar 2</option>
                <option value="3">Leerjaar 3</option>
                <option value="4">Leerjaar 4</option>
                <option value="total">Totaal opleiding</option>
              </select>
            </label>
          </header>

          <section class="hours-summary-card" aria-label="Samenvatting punten">
            <div id="hoursProgressRing" class="hours-progress-ring"><strong id="hoursProgressPercent">0%</strong></div>
            <div class="hours-total">
              <div><strong id="hoursYearTotal">0</strong><span> / <b id="hoursTarget">800</b> punten</span></div>
              <p id="hoursYearPercentage">van de puntendoelstelling behaald</p>
            </div>
            <div class="xp-level-summary"><span class="xp-star">☆</span><div><h2 id="xpLevelTitle">Level laden…</h2><p id="xpLevelName">Jouw ontwikkeling</p><div class="xp-track"><span id="xpLevelBar"></span></div><strong id="xpLevelProgress"></strong></div><button type="button" class="xp-help" onclick="openXpHelp()">ⓘ Hoe verdien ik XP?</button></div>
          </section>

          <div class="xp-challenge"><span class="xp-star">◎</span><div><h2>Actieve challenge</h2><p>Behaal 100 punten in de verdiepingsfase.</p></div><div><strong id="xpChallengeProgress"></strong><div class="xp-track"><span id="xpChallengeBar"></span></div></div><button class="button dark" type="button" onclick="selectHoursPhase('VF'); document.getElementById('hoursList').scrollIntoView({behavior:'smooth'})">Bekijk challenge →</button></div>
          <div id="challengeModal" class="goal-detail-modal hidden" role="dialog" aria-modal="true" aria-labelledby="challengeDetailTitle">
            <button class="goal-detail-backdrop" type="button" tabindex="-1" aria-label="Challenge sluiten" onclick="closeChallengeDetail()"></button>
            <section class="goal-detail-dialog"><button class="modal-close" type="button" aria-label="Challenge sluiten" onclick="closeChallengeDetail()">×</button><h2 id="challengeDetailTitle"></h2><p id="challengeDetailCopy"></p><h3>Voltooide challenges</h3><ul id="challengeHistory"></ul></section>
          </div>
          <aside id="xpHelpPanel" class="xp-help-panel hidden" role="dialog" aria-modal="true" aria-labelledby="xpHelpTitle">
            <button class="modal-close" type="button" aria-label="XP uitleg sluiten" onclick="closeXpHelp()">×</button>
            <header><span class="xp-trophy">🏆</span><div><h2 id="xpHelpTitle">Hoe werkt XP?</h2><p>Verdien XP door leeractiviteiten te voltooien. Hoe actiever je leert, hoe verder je groeit!</p></div></header>
            <section class="xp-progress-card"><h3>Jouw voortgang</h3><strong id="xpHelpProgress"></strong><div class="xp-track"><span id="xpHelpBar"></span></div><p id="xpRemaining"></p></section>
            <h3>Zo verdien je XP</h3>
            <ul class="xp-rewards"><li><span>✅ Project succesvol afronden<small>Een project afronden dat door de docent is goedgekeurd.</small></span><b>+150 XP</b></li><li><span>🎯 Leeruitkomst aantonen<small>Laat zien dat je een vaardigheid beheerst.</small></span><b>+75 XP</b></li><li><span>📖 Reflectie voltooien<small>Reflecteer op wat je hebt geleerd.</small></span><b>+50 XP</b></li><li><span>💬 Feedback verwerken<small>Pas ontvangen feedback toe in je werk.</small></span><b>+40 XP</b></li><li><span>🏆 Challenge behalen<small>Behaal 100 punten in de verdiepingsfase.</small></span><b>+100 XP</b></li></ul>
          </aside>

          <div class="hours-filter-bar">
            <div class="hours-phase-tabs" role="tablist" aria-label="Registraties per fase">
              <button type="button" data-hours-phase="OF" onclick="selectHoursPhase('OF')">Oriëntatiefase</button>
              <button type="button" data-hours-phase="VF" onclick="selectHoursPhase('VF')">Verdiepingsfase</button>
              <button type="button" data-hours-phase="AF" onclick="selectHoursPhase('AF')">Afrondingsfase</button>
            </div>
            <label class="hours-search"><span aria-hidden="true">⌕</span><span class="sr-only">Zoeken</span>
              <input id="hoursSearch" type="search" placeholder="Zoeken in registraties…" oninput="resetHoursAndRender()">
            </label>
          </div>

          <section class="hours-registration-card">
            <div class="hours-registration-head">
              <div>
                <h2>Registraties in <span id="registrationsYear">Leerjaar 1</span></h2>
                <p>Bekijk je geregistreerde punten. Klik op een registratie om de project en leeruitkomsten te bekijken.</p>
              </div>
              <label>Sorteer op
                <select id="hoursSort" class="input" onchange="resetHoursAndRender()">
                  <option value="date-desc">Datum (nieuwste eerst)</option>
                  <option value="date-asc">Datum (oudste eerst)</option>
                  <option value="hours-desc">Meeste punten</option>
                  <option value="title">Project (A-Z)</option>
                </select>
              </label>
            </div>
            <div class="hours-table-head" aria-hidden="true">
              <span>Project</span><span>Fase(s)</span><span>Leeruitkomsten</span><span>Punten</span><span>Datum</span><span>Details</span>
            </div>
            <div id="hoursList" class="hours-registration-list"></div>
            <footer class="hours-table-footer">
              <span id="hoursResultCount">0 registraties</span>
              <nav id="hoursPagination" class="hours-pagination" aria-label="Pagina's puntenregistraties"></nav>
              <label><select id="hoursPerPage" class="input" onchange="resetHoursAndRender()">
                <option value="4">4 per pagina</option>
                <option value="8">8 per pagina</option>
                <option value="12">12 per pagina</option>
              </select></label>
            </footer>
          </section>

          <div id="registrationDetailModal" class="card registration-modal hidden" role="dialog" aria-modal="true" aria-labelledby="registrationDetailTitle">
            <div class="registration-modal-head">
              <div>
                <span id="registrationDetailStatus" class="registration-status">GEVALIDEERD</span>
                <h2 id="registrationDetailTitle"></h2>
                <p id="registrationDetailType" class="dim"></p>
                <p id="registrationDetailDescription" class="registration-detail-description"></p>
              </div>
              <button class="modal-close" type="button" aria-label="Registratiedetails sluiten" onclick="closeRegistrationDetail()">×</button>
            </div>
            <div class="registration-hours-banner"><span>Goedgekeurde punten</span><strong id="registrationDetailHours"></strong></div>
            <div class="registration-detail-grid">
              <div><span>Leerjaar</span><b id="registrationDetailYear"></b></div>
              <div><span>Fase</span><b id="registrationDetailPhase"></b></div>
              <div><span>Gevalideerd op</span><b id="registrationDetailDate"></b></div>
              <div><span>Goedgekeurd door</span><b id="registrationDetailApprover"></b></div>
            </div>
            <div class="registration-outcome">
              <span>Gekoppelde leeruitkomsten</span>
              <div id="registrationDetailOutcomeList" class="registration-detail-outcome-list"></div>
            </div>
            <div class="registration-modal-footer">
              <button class="button" type="button" onclick="closeRegistrationDetail()">Sluiten</button>
              <button class="button dark" type="button" onclick="openRegistrationAssignment()">Bekijk project →</button>
            </div>
          </div>
        </section>

        <section id="goals" class="view hidden goals-page goals-redesign">
          <header class="goals-page-head">
            <div>
              <p class="eyebrow">IT INNOVATION GATEWAY</p>
              <h1>Mijn leerdoelen</h1>
              <p class="dim">Werk aan je persoonlijke ontwikkeling en houd je voortgang bij.</p>
            </div>
            <button id="goalNewTab" class="button dark goal-create-button" type="button" onclick="focusGoalWizard()">＋ Nieuw leerdoel</button>
          </header>



          <div class="goal-overview-stats"><article><span aria-hidden="true">◎</span><div><strong id="goalStatsActive">0</strong><p>Actieve leerdoelen</p></div></article><article><span aria-hidden="true">✓</span><div><strong id="goalStatsCompleted">0</strong><p>Afgeronde leerdoelen</p></div></article>            <section class="goal-insight-card goal-streak-card"><header><span class="goal-insight-icon" aria-hidden="true">♧</span><div><h2>Studiestreak</h2><p id="goalStreakCopy">Jouw actieve studieweken.</p></div></header><div id="goalStreak" class="goal-streak"></div></section></div>
          <div id="goalInsightModal" class="goal-detail-modal hidden" role="dialog" aria-modal="true" aria-labelledby="goalInsightTitle"><button class="goal-detail-backdrop" type="button" tabindex="-1" aria-label="Sluiten" onclick="closeGoalInsight()"></button><section class="goal-detail-dialog"><button class="modal-close" type="button" aria-label="Sluiten" onclick="closeGoalInsight()">×</button><h2 id="goalInsightTitle"></h2><div id="goalInsightContent"></div></section></div>
          <div class="goals-workspace">
            <section class="goals-panel goals-overview-panel" aria-labelledby="goalsOverviewTitle">
              <div class="goals-panel-heading">
                <span class="goals-heading-icon" aria-hidden="true">◎</span>
                <div><h2 id="goalsOverviewTitle">Mijn leerdoelen</h2><p>Overzicht van je leerdoelen, gekoppeld aan een leeruitkomst.</p></div>
              </div>
              <div class="goals-toolbar">          <div class="goals-tabs" role="tablist" aria-label="Leerdoelen">
            <button id="goalOverviewTab" class="goals-tab active" type="button" role="tab" aria-selected="true" onclick="showGoalOverview()">
              Actief <span id="goalActiveTotal"></span>
            </button>
            <button id="goalCompletedTab" class="goals-tab" type="button" role="tab" aria-selected="false" onclick="selectGoalStatus('afgerond')">
              Afgerond <span id="goalCompletedTotal"></span>
            </button>
          </div>
                <div class="goal-phase-tabs" role="tablist" aria-label="Fase van leerdoelen">
                  <button type="button" role="tab" data-goal-phase="OF" onclick="selectGoalListPhase('OF')">Oriëntatiefase</button>
                  <button type="button" role="tab" data-goal-phase="VF" onclick="selectGoalListPhase('VF')">Verdiepingsfase</button>
                  <button type="button" role="tab" data-goal-phase="AF" onclick="selectGoalListPhase('AF')">Afrondingsfase</button>
                </div>
                <label class="sr-only" for="goalListPhase">Filter op fase</label>
                <select id="goalListPhase" class="hidden" tabindex="-1" aria-hidden="true">
                  <option value="OF">Oriëntatiefase</option>
                  <option value="VF">Verdiepingsfase</option>
                  <option value="AF">Afrondingsfase</option>
                </select>
                <label class="goals-search">
                  <span aria-hidden="true">⌕</span><span class="sr-only">Zoeken</span>
                  <input id="goalListSearch" class="input" type="search" placeholder="Zoek op titel of leeruitkomst…" oninput="resetGoalPage()">
                </label>
              </div>
              <div id="goalList" class="goal-list" aria-live="polite"></div>
              <nav id="goalPagination" class="catalog-pagination" aria-label="Pagina's leerdoelen"></nav>
            </section>

          <div class="goal-insights">
            <section class="goal-insight-card"><header><span class="goal-insight-icon" aria-hidden="true">♙</span><div><h2>Behaalde badges</h2><p>Erkenning voor je voortgang en inzet.</p></div><button type="button" onclick="openGoalInsight('badges')">Bekijk alle badges →</button></header><div id="goalBadges" class="goal-badges"></div></section>
            <section class="goal-insight-card goal-next-step"><header><span class="goal-insight-icon" aria-hidden="true">◎</span><div><h2>Volgende stap</h2><p id="goalNextStepText"></p></div></header><button class="card-link" type="button" id="goalNextStepButton">Ga naar leerdoel →</button></section>
            <section class="goal-insight-card"><header><span class="goal-insight-icon" aria-hidden="true">◷</span><div><h2>Recente activiteit</h2></div><button type="button" onclick="openGoalInsight('activity')">Bekijk alles →</button></header><div id="goalRecentActivity" class="goal-recent-activity"></div></section>
          </div>
            <section id="goalWizard" class="goals-panel goal-wizard-panel hidden" role="dialog" aria-modal="true" aria-labelledby="goalWizardTitle" tabindex="-1">
              <button class="modal-close goal-wizard-close" type="button" aria-label="Nieuw leerdoel sluiten" onclick="closeGoalWizard()">×</button>
              <div class="goals-panel-heading">
                <span class="goals-heading-icon plus" aria-hidden="true">＋</span>
                <div><h2 id="goalWizardTitle">Nieuw leerdoel</h2><p>Maak in 3 eenvoudige stappen een nieuw leerdoel aan.</p></div>
              </div>

              <div class="goal-stepper" aria-label="Voortgang nieuw leerdoel">
                <div class="goal-step active" data-goal-step-indicator="1"><span>1</span><b>Beschrijf je leerdoel</b></div>
                <div class="goal-step" data-goal-step-indicator="2"><span>2</span><b>Kies een fase</b></div>
                <div class="goal-step" data-goal-step-indicator="3"><span>3</span><b>Kies een leeruitkomst</b></div>
              </div>

              <div class="goal-wizard-content">
                <div class="goal-wizard-step" data-goal-step="1">
                  <h3>Wat wil je ontwikkelen?</h3>
                  <p>Beschrijf concreet wat je wilt leren of verbeteren.</p>
                  <textarea id="goalText" class="input" minlength="10" maxlength="500" placeholder="Bijvoorbeeld: ik wil mijn technische keuzes beter kunnen uitleggen en onderbouwen." oninput="updateGoalTextCount()"></textarea>
                  <div class="goal-character-count"><span id="goalTextCount">0</span> / 500</div>
                </div>

                <div class="goal-wizard-step hidden" data-goal-step="2">
                  <h3>In welke fase hoort dit leerdoel?</h3>
                  <p>Kies de fase waarin je aan dit doel wilt werken.</p>
                  <select class="hidden" id="goalPhase" aria-hidden="true" tabindex="-1">
                    <option value="OF">Oriëntatiefase</option>
                    <option value="VF">Verdiepingsfase</option>
                    <option value="AF">Afrondingsfase</option>
                  </select>
                  <div id="goalPhaseChoices" class="goal-phase-choices"></div>
                </div>

                <div class="goal-wizard-step hidden" data-goal-step="3">
                  <div class="goal-outcome-heading">
                    <div><h3>Kies een leeruitkomst</h3><p>Selecteer de leeruitkomst die het beste bij je doel past.</p></div>
                    <span id="goalChoiceCount" class="goal-choice-count"></span>
                  </div>
                  <select class="hidden" id="goalCodes" aria-hidden="true" tabindex="-1"></select>
                  <div id="goalOutcomeChoices" class="goal-outcome-choices" role="listbox" aria-label="Beschikbare leeruitkomsten"></div>
                  <div id="goalOutcomePreview" class="goal-preview hidden"></div>
                </div>
              </div>

              <p id="goalMessage" class="form-message" aria-live="polite"></p>
              <div class="goal-wizard-actions">
                <button id="goalPreviousButton" class="button goal-previous-button" type="button" onclick="previousGoalStep()" disabled>← Vorige</button>
                <button id="goalNextButton" class="button dark" type="button" onclick="nextGoalStep()">Volgende stap →</button>
                <button id="goalSaveButton" class="button dark hidden" type="button" onclick="saveGoal()">Leerdoel opslaan ✓</button>
              </div>
            </section>
          </div>

          <div id="goalDetailModal" class="goal-detail-modal hidden" role="dialog" aria-modal="true" aria-labelledby="goalDetailTitle">
            <button class="goal-detail-backdrop" type="button" tabindex="-1" aria-label="Leerdoel sluiten" onclick="closeGoalDetail()"></button>
            <div class="goal-detail-dialog">
              <button class="modal-close" type="button" aria-label="Leerdoel sluiten" onclick="closeGoalDetail()">×</button>
              <span id="goalDetailStatus" class="goal-status"></span>
              <h2 id="goalDetailTitle"></h2>
              <p id="goalDetailDescription"></p>
              <div class="linked-outcome"><span>Gekoppelde leeruitkomst</span><strong id="goalDetailOutcome"></strong><span id="goalDetailCode" class="code"></span></div>
              <p id="goalCompleteMessage" class="form-message" role="status"></p>
              <button id="goalCompleteButton" class="button dark" type="button" onclick="completeSelectedGoal()">✓ Leerdoel afronden</button>
              <button class="button" type="button" onclick="closeGoalDetail()">Sluiten</button>
            </div>
          </div>
        </section>

        <section id="reflections" class="view hidden">
          <div class="head">
            <div>
              <p class="eyebrow">IT INNOVATION GATEWAY</p>
              <h1>Mijn reflecties</h1>
              <p class="dim">Bekijk, schrijf en beheer je reflecties per fase, project of leeruitkomst.</p>
            </div>
            <div class="reflection-head-actions">
              <span class="pill" id="reflectionCount">0 REFLECTIES</span>
              <button class="button dark" type="button" onclick="openReflectionForm()">+ Nieuwe reflectie</button>
            </div>
          </div>
          <div class="reflection-filter" id="reflectionPhaseFilter" aria-label="Filter reflecties per fase"></div>
          <div class="reflection-highlights"><article><span class="reflection-highlight-icon">▤</span><div><h2>Laatste reflectie</h2><strong id="reflectionLatestTitle"></strong><p id="reflectionLatestDate"></p><button type="button" class="card-link" onclick="openLatestReflection()">Bekijk reflectie →</button></div></article><article><span class="reflection-highlight-icon">◎</span><div><h2>Volgende stap</h2><p id="reflectionNextCopy"></p><button type="button" class="card-link" onclick="newReflectionInPhase()">Nieuwe reflectie →</button></div></article></div>
          <div class="reflection-type-tabs" role="group" aria-label="Type reflectie"><button type="button" data-reflection-type="all" onclick="selectReflectionType('all')">Alle reflecties</button><button type="button" data-reflection-type="project" onclick="selectReflectionType('project')">Projecten</button><button type="button" data-reflection-type="outcome" onclick="selectReflectionType('outcome')">Leeruitkomsten</button></div>
          <div class="reflection-table-filters"><label><span class="sr-only">Zoeken</span><input id="reflectionSearch" class="input" type="search" placeholder="Zoek in reflecties…" oninput="renderReflections()"></label><label class="label">Soft skills<select id="reflectionSoftOutcome" class="input" onchange="selectReflectionOutcomeFilter('Softskills')"><option value="all">Alle soft skills</option></select></label><label class="label">Hard skills<select id="reflectionHardOutcome" class="input" onchange="selectReflectionOutcomeFilter('Hardskills')"><option value="all">Alle hard skills</option></select></label><label class="label">Filter<select id="reflectionListStatus" class="input" onchange="renderReflections()"><option value="active">Huidige reflecties</option><option value="deleted">Verwijderde reflecties</option><option value="all">Alle reflecties</option></select></label></div>
          <div class="reflection-layout">
            <div>
              <div class="reflection-table-heading"><span>Reflectie</span><span>Type</span><span>Leeruitkomst (koppeling)</span><span>Status</span><span>Datum</span></div>
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
              <label class="label" for="reflectionTitle">Project of onderwerp</label>
              <span class="field-help">Waarover schrijf je deze reflectie?</span>
              <input id="reflectionTitle" class="input" maxlength="80" placeholder="Bijv. installatieproject voor een werkplek" required />
              <label class="label" for="reflectionLinkType">Waarover wil je reflecteren?</label>
              <select id="reflectionLinkType" class="input" onchange="updateReflectionLinkType()"><option value="outcome">Leeruitkomst</option><option value="project">Project</option></select>
              <label id="reflectionProjectLabel" class="label hidden">Project<select id="reflectionProject" class="input" onchange="selectReflectionProject()"></select></label>
              <label class="label" for="reflectionPhase">Bij welke fase hoort je reflectie?</label>
              <span class="field-help">De gekozen fase bepaalt welke leeruitkomsten je kunt selecteren.</span>
              <select id="reflectionPhase" class="input" onchange="updateReflectionLinkType()" required>
                <option value="OF">Oriëntatiefase</option>
                <option value="VF">Verdiepingsfase</option>
                <option value="AF">Afrondingsfase</option>
              </select>
              <h3>Kies een leeruitkomst</h3>
              <p class="field-help">Selecteer een leeruitkomst voor je reflectie.</p>
              <select id="reflectionOutcome" class="hidden" aria-hidden="true" tabindex="-1"></select>
              <div class="reflection-skill-tabs" role="group" aria-label="Type vaardigheid"><button type="button" data-reflection-skill="Hardskills" onclick="selectReflectionSkill('Hardskills')">Hard skills</button><button type="button" data-reflection-skill="Softskills" onclick="selectReflectionSkill('Softskills')">Soft skills</button></div>
              <label><span class="sr-only">Zoek een leeruitkomst</span><input id="reflectionOutcomeSearch" class="input" type="search" placeholder="Zoek een leeruitkomst…" oninput="renderReflectionOutcomePicker()"></label>
              <div id="reflectionOutcomePicker" class="reflection-outcome-picker" role="group" aria-label="Beschikbare leeruitkomsten"></div>
              <div id="reflectionOutcomePreview" class="goal-preview reflection-preview"></div>
              <div class="reflection-questions">
                <label class="label" for="reflectionSituation">1. Wat was de situatie?</label>
                <span class="field-help">Beschrijf kort de project, jouw rol en wat je wilde bereiken.</span>
                <textarea
                  id="reflectionSituation"
                  class="input"
                  minlength="10"
                  maxlength="500"
                  placeholder="Tijdens deze project werkte ik aan…"
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
                <textarea id="reflectionNext" class="input" minlength="10" maxlength="500" placeholder="Bij mijn volgende project ga ik…" required></textarea>
              </div>
              <div id="reflectionMessage" class="form-message hidden" role="status"></div>
              <button class="button dark" type="submit">Reflectie bewaren</button>
              <p class="privacy-note">Je reflectie wordt veilig bij jouw account in de database bewaard.</p>
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
            <label class="label" for="feedbackTitle">Onderwerp of project</label>
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
              ><textarea id="feedbackAction" class="input" minlength="10" maxlength="600" placeholder="Bij de volgende project ga ik…" required></textarea>
            </div>
            <div id="feedbackMessage" class="form-message hidden" role="status"></div>
            <button class="button dark" type="submit">Feedback bewaren</button>
            <p class="privacy-note">Feedback wordt bij jouw account in de database bewaard.</p>
          </form>
        </section>

@endsection

