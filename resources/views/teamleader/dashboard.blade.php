@extends('layouts.application')

@section('role-content')
@php($teamleaderPage = $teamleaderPage ?? 'overview')
<section id="managerGroups" class="view hidden" data-role-section="groups">
  <div class="head">
    <div>
      @if ($teamleaderPage === 'student')
        <h1>{{ $teamleaderStudent['name'] }}</h1>
        <p class="dim">Voortgang en leeruitkomsten van deze student.</p>
      @elseif ($teamleaderPage === 'group')
        <h1>{{ $teamleaderGroup['name'] }}</h1>
        <p class="dim">Voortgang en studenten in deze groep.</p>
      @else
        <h1>Groepsoverzicht</h1>
        <p class="dim">Voortgang en studenten per groep.</p>
      @endif
    </div>
    @if ($teamleaderPage === 'student')
      <a class="button" href="{{ route('teamleader.group', ['group' => $teamleaderGroup['id']]) }}">Terug naar {{ $teamleaderGroup['name'] }}</a>
    @elseif ($teamleaderPage === 'group')
      <a class="button" href="{{ route('teamleader.dashboard') }}">Terug naar Groepsoverzicht</a>
    @endif
  </div>

  <div class="teamleader-groups-list">
    @forelse ($roleData['groups'] as $group)
    @php($groupSummaryProgressClass = $group['goal'] === null ? 'teamleader-progress-NVT' : ($group['displayAveragePercentage'] >= $group['goal'] ? 'teamleader-progress-high' : ($group['displayAveragePercentage'] >= $group['goal'] - 15 ? 'teamleader-progress-medium' : 'teamleader-progress-low')))
    @php($groupSummaryStudentPercentage = $group['studentCount'] > 0 ? ($group['redStudentCount'] / $group['studentCount']) * 100 : 0)
    @php($groupSummaryStudentClass = $group['redStudentCount'] === 0 ? 'teamleader-progress-high' : ($groupSummaryStudentPercentage <= 20 ? 'teamleader-progress-medium' : 'teamleader-progress-low'))
    @php($groupSummaryOutcomeClass = $group['redOutcomeCount'] === 0 ? 'teamleader-progress-high' : ($group['redOutcomeCount'] <= 3 * $group['studentCount'] ? 'teamleader-progress-medium' : 'teamleader-progress-low'))
    @if ($teamleaderPage === 'student')
    <div id="teamleader-group-card-{{ $group['id'] }}" class="teamleader-group-card teamleader-group-card-attention-{{ $group['attentionColor'] }}">
    @else
    <details id="teamleader-group-card-{{ $group['id'] }}" class="teamleader-group-card teamleader-group-card-attention-{{ $group['attentionColor'] }}" @if ($teamleaderPage === 'group') open @endif>
      <summary class="teamleader-group-summary" aria-labelledby="teamleader-group-{{ $group['id'] }}" data-teamleader-group-summary="{{ $group['id'] }}">
        <span class="teamleader-group-name" id="teamleader-group-{{ $group['id'] }}">{{ $group['name'] }}</span>
        <span class="teamleader-group-summary-item teamleader-group-summary-item-open"><span>Schooljaar</span><strong>{{ $group['schoolYear'] ?: 'Niet ingesteld' }}</strong></span>
        <span class="teamleader-group-summary-item">
          <span>Docent/SLB'er</span>
          @if ($group['coachId'])
            <strong>{{ $group['coach'] }}</strong>
          @else
            <strong>{{ $group['coach'] ?: 'Niet toegewezen' }}</strong>
          @endif
        </span>
        <span class="teamleader-group-summary-item"><span>Fase</span><strong>{{ $group['studyPhase'] ?: 'Niet ingesteld' }}</strong></span>
        <span class="teamleader-group-summary-item teamleader-group-summary-item-open"><span>Doel</span><strong>{{ $group['goal'] !== null ? $group['goal'].'%' : 'Niet ingesteld' }}</strong></span>
        <span class="teamleader-group-summary-item teamleader-group-summary-item-extra"><span>Studenten in het rood</span><strong class="teamleader-progress-indicator {{ $groupSummaryStudentClass }}">{{ $group['redStudentCount'] }}</strong></span>
        <span class="teamleader-group-summary-item teamleader-group-summary-item-extra"><span>Leeruitkomsten rood</span><strong class="teamleader-progress-indicator {{ $groupSummaryOutcomeClass }}">{{ $group['redOutcomeCount'] }}</strong></span>
        <span class="teamleader-group-summary-item teamleader-group-summary-item-extra"><span>Aantal studenten</span><strong class="teamleader-progress-indicator teamleader-progress-NVT">{{ $group['studentCount'] }}</strong></span>
        <span class="teamleader-group-summary-item teamleader-group-summary-item-extra"><span>{{ $group['studyPhase'] ? 'Gemiddelde voortgang' : 'Gemiddelde voortgang' }}</span><strong class="teamleader-progress-indicator {{ $groupSummaryProgressClass }}">{{ $group['displayAveragePercentage'] }}%</strong></span>
      </summary>
    @endif

      <div class="teamleader-group-details">
        @if ($teamleaderPage !== 'student')
        <p>{{ $group['description'] ?: 'Geen omschrijving.' }}</p>
        <div class="grid2">
          <div class="card"><div class="big">{{ $group['studentCount'] }}</div><span class="dim">studenten</span></div>
          @php($groupProgressStatus = $group['goal'] === null ? 'neutral' : ($group['displayAveragePercentage'] >= $group['goal'] ? 'success' : ($group['displayAveragePercentage'] >= $group['goal'] - 15 ? 'warning' : 'danger')))
          <div class="card teamleader-status-card teamleader-status-card-{{ $groupProgressStatus }}"><div class="big">{{ $group['displayAveragePercentage'] }}%</div><span class="dim">{{ $group['studyPhase'] ? 'gemiddelde voortgang in '.$group['studyPhase'] : 'gemiddelde voortgang' }}</span></div>
          @php($redStudentPercentage = $group['studentCount'] > 0 ? ($group['redStudentCount'] / $group['studentCount']) * 100 : 0)
          @php($redStudentStatus = $group['redStudentCount'] === 0 ? 'success' : ($redStudentPercentage <= 20 ? 'warning' : 'danger'))
          <div class="card teamleader-status-card teamleader-status-card-{{ $redStudentStatus }}"><div class="big">{{ $group['redStudentCount'] }}</div><span class="dim">studenten in het rood</span></div>
          @php($redOutcomeStatus = $group['redOutcomeCount'] === 0 ? 'success' : ($group['redOutcomeCount'] <= 3 * $group['studentCount'] ? 'warning' : 'danger'))
          <div class="card teamleader-status-card teamleader-status-card-{{ $redOutcomeStatus }}"><div class="big">{{ $group['redOutcomeCount'] }}</div><span class="dim">leeruitkomsten in het rood</span></div>
        </div>

        <div class="card">
          <h3>Gemiddelde per fase</h3>
          @forelse ($group['phases'] as $fase => $percentage)
            @php($phaseRanks = ['Oriëntatiefase' => 1, 'Verdiepingsfase' => 2, 'Afrondingsfase' => 3])
            @php($phaseRank = $phaseRanks[$fase] ?? 0)
            @php($phasePrevious = $group['studyPhaseOrder'] && $phaseRank && $phaseRank < $group['studyPhaseOrder'])
            @php($phaseNext = $group['studyPhaseOrder'] && $phaseRank && $phaseRank > $group['studyPhaseOrder'])
            @php($phaseOutsideGroup = $group['studyPhase'] && $fase !== $group['studyPhase'])
            @php($phaseProgressClass = $phasePrevious && $percentage < 100 ? 'teamleader-progress-purple' : ($phaseNext && $percentage > 10 ? 'teamleader-progress-blue' : ($phaseOutsideGroup ? 'teamleader-progress-NVT' : ($group['goal'] === null ? 'teamleader-progress-NVT' : ($percentage >= $group['goal'] ? 'teamleader-progress-high' : ($percentage >= $group['goal'] - 15 ? 'teamleader-progress-medium' : 'teamleader-progress-low'))))))
            <div class="kv">
              <b>{{ $fase }}</b>
              <div class="bar"><span style="width: {{ $percentage }}%"></span></div>
              <span class="teamleader-progress-indicator {{ $phaseProgressClass }}">{{ $percentage }}%</span>
            </div>
          @empty
            <p class="dim">Er zijn nog geen studiefases ingesteld.</p>
          @endforelse
        </div>
        @endif

        <div class="card">
          @if ($teamleaderPage !== 'student')
            <h3>Studenten in deze groep</h3>
          @endif
          @if ($teamleaderPage === 'group')
            <div class="teamleader-student-card-grid">
              @forelse ($group['students'] as $student)
                <a class="teamleader-student-card" href="{{ route('teamleader.student', ['group' => $group['id'], 'student' => $student['id']]) }}">
                  <strong>{{ $student['name'] }}</strong>
                  <span class="teamleader-outcome-meta">{{ $student['studyPhase'] ?: 'Geen studiefase ingesteld' }}</span>
                  <span class="teamleader-progress-indicator {{ $student['progressClass'] }}">{{ $student['displayPercentage'] }}% gemiddelde voortgang{{ $student['studyPhase'] ? ' in '.$student['studyPhase'] : '' }}</span>
                  <span class="teamleader-student-card-link">Bekijk student &rarr;</span>
                </a>
              @empty
                <p class="dim">Er zijn nog geen studenten aan deze groep gekoppeld.</p>
              @endforelse
            </div>
          @else
          @forelse ($group['students'] as $student)
            @php($studentOverallProgressClass = $group['goal'] === null ? 'teamleader-progress-NVT' : ($student['percentage'] >= $group['goal'] ? 'teamleader-progress-high' : ($student['percentage'] >= $group['goal'] - 15 ? 'teamleader-progress-medium' : 'teamleader-progress-low')))
            @if ($teamleaderPage !== 'student')
            <details>
              <summary class="name" data-teamleader-student-summary="{{ $student['id'] }}">
                <strong>{{ $student['name'] }}</strong>
                <span class="dim">{{ $student['studyPhase'] ?: 'Geen studiefase ingesteld' }}</span>
                <span><span class="teamleader-progress-indicator {{ $student['progressClass'] }}">{{ $student['displayPercentage'] }}%</span> gemiddelde voortgang{{ $student['studyPhase'] ? ' in '.$student['studyPhase'] : '' }}</span>
              </summary>
            @endif
              @if ($student['outcomes']->isNotEmpty())
                <div class="actions card">
                  <label class="label">
                    Filter voortgang op fase
                    <select data-teamleader-phase-filter aria-label="Filter voortgang van {{ $student['name'] }} op fase">
                      <option value="__all">Alle fases</option>
                      @foreach ($student['outcomes']->sortBy('phaseOrder')->pluck('phase')->filter()->unique()->values() as $fase)
                        <option value="{{ $fase }}" @selected($teamleaderPage === 'student' && $fase === $student['studyPhase'])>{{ $fase }}</option>
                      @endforeach
                      @if ($teamleaderPage === 'student' && $student['studyPhase'] && !$student['outcomes']->contains(fn ($outcome) => $outcome['phase'] === $student['studyPhase']))
                        <option value="{{ $student['studyPhase'] }}" selected>{{ $student['studyPhase'] }}</option>
                      @endif
                      @if ($student['outcomes']->contains(fn ($outcome) => $outcome['phase'] === null))
                        <option value="">Geen fase</option>
                      @endif
                    </select>
                  </label>
                  <label class="label">
                    Sorteer leeruitkomsten op percentage
                    <select data-teamleader-outcome-sort aria-label="Sorteer leeruitkomsten van {{ $student['name'] }} op percentage">
                      <option value="default">Oorspronkelijke volgorde</option>
                      <option value="ascending">Laag naar hoog</option>
                      <option value="descending">Hoog naar laag</option>
                    </select>
                  </label>
                  <span
                    class="teamleader-progress-indicator {{ $studentOverallProgressClass }}"
                    data-teamleader-phase-average
                    data-teamleader-group-phase="{{ $group['studyPhase'] ?? '' }}"
                    data-teamleader-group-goal="{{ $group['goal'] ?? '' }}"
                    aria-live="polite"
                  >Gemiddelde voortgang over alle fases: {{ $student['percentage'] }}%</span>
                </div>
              @endif
              @if ($student['outcomes']->isEmpty())
                <p class="dim">Er is nog geen voortgang geregistreerd.</p>
              @elseif ($teamleaderPage === 'student')
                <div class="teamleader-outcome-columns">
                  @foreach (['Hardskills' => 'Hardskills', 'Softskills' => 'Softskills'] as $kind => $heading)
                    <section class="teamleader-outcome-column">
                      <h4>{{ $heading }}</h4>
                      <div class="teamleader-outcome-grid" data-teamleader-outcome-grid data-teamleader-outcome-kind="{{ $kind }}">
                        @foreach ($student['outcomes']->where('kind', $kind) as $outcome)
                          <article
                            class="teamleader-outcome-card"
                            data-teamleader-outcome
                            data-teamleader-progress-phase="{{ $outcome['phase'] ?? '' }}"
                            data-teamleader-progress-phase-order="{{ $outcome['phaseOrder'] }}"
                            data-teamleader-progress-percentage="{{ $outcome['percentage'] ?? '' }}"
                          >
                            <div class="teamleader-outcome-heading">
                              <strong>{{ $outcome['code'] }} — {{ $outcome['title'] }}</strong>
                            </div>
                            <span class="teamleader-outcome-meta">{{ $outcome['phase'] ?: 'Geen fase' }}</span>
                            <div class="teamleader-progress-indicator {{ $outcome['progressClass'] }}">
                              <span>{{ $outcome['hours'] }}{{ $outcome['target'] ? ' / '.$outcome['target'] : '' }} punten - </span>
                              <span>{{ $outcome['percentage'] === null ? 'Geen norm' : $outcome['percentage'].'%' }}</span>
                            </div>
                            <span class="teamleader-outcome-status">{{ str_replace('_', ' ', ucfirst($outcome['status'])) }}</span>
                          </article>
                        @endforeach
                        @if ($student['outcomes']->where('kind', $kind)->isEmpty())
                          <p class="dim">Geen leeruitkomsten.</p>
                        @endif
                      </div>
                    </section>
                  @endforeach
                </div>
              @else
                <div class="teamleader-outcome-grid" data-teamleader-outcome-grid>
                  @foreach ($student['outcomes'] as $outcome)
                    <article
                      class="teamleader-outcome-card"
                      data-teamleader-outcome
                      data-teamleader-progress-phase="{{ $outcome['phase'] ?? '' }}"
                      data-teamleader-progress-phase-order="{{ $outcome['phaseOrder'] }}"
                      data-teamleader-progress-percentage="{{ $outcome['percentage'] ?? '' }}"
                    >
                      <div class="teamleader-outcome-heading">
                        <strong>{{ $outcome['code'] }} — {{ $outcome['title'] }}</strong>
                      </div>
                      <span class="teamleader-outcome-meta">{{ $outcome['phase'] ?: 'Geen fase' }}</span>
                      <div class="teamleader-progress-indicator {{ $outcome['progressClass'] }}">
                        <span>{{ $outcome['hours'] }}{{ $outcome['target'] ? ' / '.$outcome['target'] : '' }} punten - </span>
                        <span>{{ $outcome['percentage'] === null ? 'Geen norm' : $outcome['percentage'].'%' }}</span>
                      </div>
                      <span class="teamleader-outcome-status">{{ str_replace('_', ' ', ucfirst($outcome['status'])) }}</span>
                    </article>
                  @endforeach
                </div>
              @endif
            @if ($teamleaderPage !== 'student')
            </details>
            @endif
          @empty
            <p class="dim">Er zijn nog geen studenten aan deze groep gekoppeld.</p>
          @endforelse
          @endif
        </div>
      </div>
    @if ($teamleaderPage === 'student')
    </div>
    @else
    </details>
    @endif
    @empty
      <div class="card">
        <p>Er zijn nog geen groepen aangemaakt.</p>
      </div>
    @endforelse
  </div>
</section>

<section id="managerTeachers" class="view hidden" data-role-section="teachers">
  <div class="head">
    <div>
      <h1>Docenten</h1>
      <p class="dim">Overzicht van alle Docenten.</p>
    </div>
  </div>
  <div class="card manager-teachers-table-wrap">
    <table class="manager-teachers-table">
      <thead><tr><th>Naam</th><th>E-mailadres</th><th>Groep status</th><th>Groepen</th></tr></thead>
      <tbody>
        @forelse ($roleData['coaches'] as $coach)
          <tr>
            <td>
              <button
                id="teamleader-teacher-toggle-{{ $coach['id'] }}"
                class="teamleader-teacher-toggle"
                type="button"
                data-teamleader-teacher-toggle="{{ $coach['id'] }}"
                aria-controls="teamleader-teacher-groups-{{ $coach['id'] }}"
                aria-expanded="false"
              >{{ $coach['name'] }}</button>
            </td>
            <td><a href="mailto:{{ $coach['email'] }}">{{ $coach['email'] }}</a></td>
            <td>
              <div
                class="teamleader-group-status-counts"
                role="img"
                aria-label="Groen: {{ $coach['groupStatusCounts']['green'] }} groepen, geel: {{ $coach['groupStatusCounts']['yellow'] }} groepen, rood: {{ $coach['groupStatusCounts']['red'] }} groepen, grijs: {{ $coach['groupStatusCounts']['gray'] }} groepen"
              >
                @if ($coach['groupStatusCounts']['green'] > 0)
                  <span class="teamleader-group-status-count teamleader-group-status-count-green" title="Groen">{{ $coach['groupStatusCounts']['green'] }}</span>
                @endif
                @if ($coach['groupStatusCounts']['yellow'] > 0)
                  <span class="teamleader-group-status-count teamleader-group-status-count-yellow" title="Geel">{{ $coach['groupStatusCounts']['yellow'] }}</span>
                @endif
                @if ($coach['groupStatusCounts']['red'] > 0)
                  <span class="teamleader-group-status-count teamleader-group-status-count-red" title="Rood">{{ $coach['groupStatusCounts']['red'] }}</span>
                @endif
                @if ($coach['groupStatusCounts']['gray'] > 0)
                  <span class="teamleader-group-status-count teamleader-group-status-count-gray" title="Grijs">{{ $coach['groupStatusCounts']['gray'] }}</span>
                @endif
              </div>
            </td>
            <td>{{ $coach['groups']->count() }}</td>
          </tr>
          <tr id="teamleader-teacher-groups-{{ $coach['id'] }}" class="hidden teamleader-teacher-groups-row">
            <td colspan="4">
              @if ($coach['groups']->isNotEmpty())
                <div class="teamleader-coach-group-list">
                  @foreach ($coach['groups'] as $group)
                    <article class="teamleader-coach-group teamleader-coach-group-attention-{{ $group['attentionColor'] }}">
                      <strong>{{ $group['name'] }}</strong>
                      <span>{{ $group['studentCount'] }} studenten</span>
                      @php($coachRedStudentPercentage = $group['studentCount'] > 0 ? ($group['redStudentCount'] / $group['studentCount']) * 100 : 0)
                      @php($coachRedStudentClass = $group['redStudentCount'] === 0 ? 'teamleader-progress-high' : ($coachRedStudentPercentage <= 20 ? 'teamleader-progress-medium' : 'teamleader-progress-low'))
                      @php($coachRedOutcomeClass = $group['redOutcomeCount'] === 0 ? 'teamleader-progress-high' : ($group['redOutcomeCount'] <= 3 * $group['studentCount'] ? 'teamleader-progress-medium' : 'teamleader-progress-low'))
                      <span class="teamleader-progress-indicator {{ $coachRedStudentClass }}">{{ $group['redStudentCount'] }} studenten in het rood</span>
                      <span class="teamleader-progress-indicator {{ $coachRedOutcomeClass }}">{{ $group['redOutcomeCount'] }} leeruitkomsten in het rood</span>
                      @php($coachGroupProgressClass = $group['goal'] === null ? 'teamleader-progress-NVT' : ($group['displayAveragePercentage'] >= $group['goal'] ? 'teamleader-progress-high' : ($group['displayAveragePercentage'] >= $group['goal'] - 15 ? 'teamleader-progress-medium' : 'teamleader-progress-low')))
                      <span class="teamleader-progress-indicator {{ $coachGroupProgressClass }}">{{ $group['displayAveragePercentage'] }}% {{ $group['studyPhase'] ? 'gemiddelde voortgang in '.$group['studyPhase'] : 'gemiddelde voortgang' }}</span>
                      <button class="button" type="button" data-teamleader-open-group="{{ $group['id'] }}">Bekijk groep</button>
                    </article>
                  @endforeach
                </div>
              @else
                <p class="dim">Deze docent heeft nog geen groepen.</p>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4">Er zijn nog geen docenten.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

{{-- Report view reuses the same totals and explains why export is not yet available. --}}
<section id="managerReports" class="view hidden" data-role-section="reports">
  <h1>Rapportages</h1>
  <p class="dim">Dezelfde actuele databasecijfers in een compact overzicht.</p>
  <div class="grid2">
    <div class="card">
      <h2>Huidige stand</h2>
      <p><b>{{ $roleData['studentCount'] }}</b> studenten hebben samen een gemiddelde voortgang van <b>{{ $roleData['averagePercentage'] }}%</b>.</p>
    </div>
    <div class="card">
      <h2>Export</h2>
      <p class="dim">Een bestandsexport is nog niet aanwezig; daarvoor moeten eerst het formaat en de privacyregels worden vastgesteld.</p>
    </div>
  </div>
</section>

@include('components.role-dashboard-settings', [
  'preferencesPageId' => 'managerPreferencesSettings',
])

@endsection
