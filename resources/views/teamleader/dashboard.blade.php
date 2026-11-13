@extends('layouts.application')

@section('role-content')
<section id="managerGroups" class="view hidden" data-role-section="groups">
  <div class="head">
    <div>
      <h1>Groepsoverzicht</h1>
      <p class="dim">Voortgang en studenten per groep.</p>
    </div>
  </div>

  <div class="teamleader-groups-list">
    @forelse ($roleData['groups'] as $group)
    <details id="teamleader-group-card-{{ $group['id'] }}" class="teamleader-group-card">
      <summary class="teamleader-group-summary" aria-labelledby="teamleader-group-{{ $group['id'] }}">
        <span class="teamleader-group-name" id="teamleader-group-{{ $group['id'] }}">{{ $group['name'] }}</span>
        <span class="teamleader-group-summary-item"><span>Schooljaar</span><strong>{{ $group['schoolYear'] ?: 'Niet ingesteld' }}</strong></span>
        <span class="teamleader-group-summary-item"><span>Coach</span><strong>{{ $group['coach'] ?: 'Niet toegewezen' }}</strong></span>
        <span class="teamleader-group-summary-item teamleader-group-summary-item-extra"><span>Studenten</span><strong>{{ $group['studentCount'] }}</strong></span>
        <span class="teamleader-group-summary-item teamleader-group-summary-item-extra"><span>Gemiddelde voortgang</span><strong>{{ $group['averagePercentage'] }}%</strong></span>
      </summary>

      <div class="teamleader-group-details">
        <p>{{ $group['description'] ?: 'Geen omschrijving beschikbaar.' }}</p>
        <div class="grid3">
          <div class="card"><div class="big">{{ $group['studentCount'] }}</div><span class="dim">studenten</span></div>
          <div class="card"><div class="big">{{ $group['averagePercentage'] }}%</div><span class="dim">gemiddelde voortgang</span></div>
          <div class="card"><div class="big">{{ $group['attentionCount'] }}</div><span class="dim">voortgangsregels vragen aandacht</span></div>
        </div>

        <div class="card">
          <h3>Gemiddelde per fase</h3>
          @forelse ($group['phases'] as $fase => $percentage)
            <div class="kv">
              <b>{{ $fase }}</b>
              <div class="bar"><span style="width: {{ $percentage }}%"></span></div>
              <span>{{ $percentage }}%</span>
            </div>
          @empty
            <p class="dim">Er zijn nog geen studiefases ingesteld.</p>
          @endforelse
        </div>

        <div class="card">
          <h3>Studenten in deze groep</h3>
          @forelse ($group['students'] as $student)
            @php($studentPhaseProgressClass = $student['displayPercentage'] >= 75 ? 'teamleader-progress-high' : ($student['displayPercentage'] >= 60 ? 'teamleader-progress-medium' : 'teamleader-progress-low'))
            @php($studentOverallProgressClass = $student['percentage'] >= 75 ? 'teamleader-progress-high' : ($student['percentage'] >= 60 ? 'teamleader-progress-medium' : 'teamleader-progress-low'))
            <details>
              <summary>
                <strong>{{ $student['name'] }}</strong>
                <span class="dim">{{ $student['studyPhase'] ?: 'Geen studiefase ingesteld' }}</span>
                <span><span class="teamleader-progress-indicator {{ $studentPhaseProgressClass }}">{{ $student['displayPercentage'] }}%</span> gemiddelde voortgang{{ $student['studyPhase'] ? ' in '.$student['studyPhase'] : '' }}</span>
              </summary>
              <div class="bar" role="progressbar" aria-label="Voortgang van {{ $student['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $student['percentage'] }}">
                <span style="width: {{ $student['percentage'] }}%"></span>
              </div>
              <h4>Voortgang per leeruitkomst</h4>
              @if ($student['outcomes']->isNotEmpty())
                <div class="actions">
                  <label class="label">
                    Filter voortgang op fase
                    <select data-teamleader-phase-filter aria-label="Filter voortgang van {{ $student['name'] }} op fase">
                      <option value="__all">Alle fases</option>
                      @foreach ($student['outcomes']->pluck('phase')->filter()->unique()->sort()->values() as $fase)
                        <option value="{{ $fase }}">{{ $fase }}</option>
                      @endforeach
                      @if ($student['outcomes']->contains(fn ($outcome) => $outcome['phase'] === null))
                        <option value="">Geen fase</option>
                      @endif
                    </select>
                  </label>
                  <span class="teamleader-progress-indicator {{ $studentOverallProgressClass }}" data-teamleader-phase-average aria-live="polite">Gemiddelde voortgang over alle fases: {{ $student['percentage'] }}%</span>
                </div>
              @endif
              @forelse ($student['outcomes'] as $outcome)
                <div
                  class="row"
                  data-teamleader-progress-phase="{{ $outcome['phase'] ?? '' }}"
                  data-teamleader-progress-percentage="{{ $outcome['percentage'] ?? '' }}"
                >
                  <b>{{ $outcome['code'] }} — {{ $outcome['title'] }}</b>
                  <span>{{ $outcome['phase'] ?: 'Geen fase' }}</span>
                  <span>{{ $outcome['hours'] }}{{ $outcome['target'] ? ' / '.$outcome['target'] : '' }} uur</span>
                  <span>{{ $outcome['percentage'] === null ? 'Geen norm' : $outcome['percentage'].'%' }}</span>
                  <span>{{ str_replace('_', ' ', ucfirst($outcome['status'])) }}</span>
                </div>
              @empty
                <p class="dim">Er is nog geen voortgang geregistreerd.</p>
              @endforelse
            </details>
          @empty
            <p class="dim">Er zijn nog geen studenten aan deze groep gekoppeld.</p>
          @endforelse
        </div>
      </div>
    </details>
    @empty
      <div class="card">
        <p>Er zijn nog geen groepen aangemaakt door de beheerder.</p>
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
      <thead><tr><th>Naam</th><th>E-mailadres</th><th>Accounttype</th><th>Groepen</th></tr></thead>
      <tbody>
        @forelse ($roleData['coaches'] as $coach)
          <tr>
            <td>
              <button
                class="teamleader-teacher-toggle"
                type="button"
                data-teamleader-teacher-toggle="{{ $coach['id'] }}"
                aria-controls="teamleader-teacher-groups-{{ $coach['id'] }}"
                aria-expanded="false"
              >{{ $coach['name'] }}</button>
            </td>
            <td><a href="mailto:{{ $coach['email'] }}">{{ $coach['email'] }}</a></td>
            <td>{{ $coach['accountType'] }}</td>
            <td>{{ $coach['groups']->count() }}</td>
          </tr>
          <tr id="teamleader-teacher-groups-{{ $coach['id'] }}" class="hidden teamleader-teacher-groups-row">
            <td colspan="4">
              @if ($coach['groups']->isNotEmpty())
                <div class="teamleader-coach-group-list">
                  @foreach ($coach['groups'] as $group)
                    <article class="teamleader-coach-group">
                      <strong>{{ $group['name'] }}</strong>
                      <span>{{ $group['studentCount'] }} studenten</span>
                      <span>{{ $group['averagePercentage'] }}% gemiddelde voortgang</span>
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
