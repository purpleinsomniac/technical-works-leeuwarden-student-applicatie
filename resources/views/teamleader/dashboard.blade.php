@extends('layouts.application')

@section('role-content')
{{-- Compact overview of current student progress across the team. --}}
<section id="managerGroups" class="view hidden" data-role-section="groups">
  <div class="head">
    <div>
      <h1>Groepsoverzicht</h1>
      <p class="dim">Actuele totalen op basis van de studentvoortgang in MySQL.</p>
    </div>
    <button class="dashboard-settings-shortcut teamleader-account-shortcut" type="button" onclick="show('managerDashboardSettings')">Dashboard instellen</button>
  </div>
  {{-- Summary values are calculated from the latest student progress data by the controller. --}}
  <div class="grid3">
    <div class="card"><div class="big">{{ $roleData['studentCount'] }}</div><span class="dim">studenten</span></div>
    <div class="card"><div class="big">{{ $roleData['averagePercentage'] }}%</div><span class="dim">gemiddelde voortgang</span></div>
    <div class="card"><div class="big">{{ $roleData['attentionCount'] }}</div><span class="dim">voortgangsregels vragen aandacht</span></div>
  </div>
  {{-- Show one average progress bar for each study phase, defaulting to zero when empty. --}}
  <div class="card">
    <h2>Gemiddelde per fase</h2>
    @foreach (['Oriëntatiefase', 'Verdiepingsfase', 'Afrondingsfase'] as $fase)
      @php($percentage = $roleData['phases'][$fase] ?? 0)
      <div class="kv">
        <b>{{ $fase }}</b>
        <div class="bar"><span style="width: {{ $percentage }}%"></span></div>
        <span>{{ $percentage }}%</span>
      </div>
    @endforeach
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
  'settingsPageId' => 'managerDashboardSettings',
  'dashboardSections' => [
    ['key' => 'groups', 'label' => 'Groepsoverzicht', 'description' => 'Bekijk de voortgang van de studentengroep.'],
    ['key' => 'reports', 'label' => 'Rapportages', 'description' => 'Bekijk de samenvatting en rapportages.'],
  ],
])

@endsection
