@extends('layouts.application')

@section('role-content')
<section id="managerGroups" class="view hidden">
  <div class="head">
    <div>
      <h1>Groepsoverzicht</h1>
      <p class="dim">Actuele totalen op basis van de studentvoortgang in MySQL.</p>
    </div>
  </div>
  <div class="grid3">
    <div class="card"><div class="big">{{ $roleData['studentCount'] }}</div><span class="dim">studenten</span></div>
    <div class="card"><div class="big">{{ $roleData['averagePercentage'] }}%</div><span class="dim">gemiddelde voortgang</span></div>
    <div class="card"><div class="big">{{ $roleData['attentionCount'] }}</div><span class="dim">voortgangsregels vragen aandacht</span></div>
  </div>
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

<section id="managerReports" class="view hidden">
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

@endsection
