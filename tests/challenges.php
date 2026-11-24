<?php

// Geïsoleerde regressiecontrole: gebruikt uitsluitend een SQLite-database in geheugen.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>':memory:', 'cache.default'=>'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\Artisan::call('migrate', ['--database'=>'sqlite','--force'=>true]);

use App\Models\Assignment;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\User;
use App\Services\ChallengeService;
use Illuminate\Support\Facades\DB;

function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
$phaseId = DB::table('studiefases')->insertGetId(['naam'=>'Oriëntatiefase']);
$user = User::create(['naam'=>'Challenge test','email'=>'challenge@example.test','password'=>'Test12345','rol'=>'student','studiefase_id'=>$phaseId]);
$outcome = LearningOutcome::create(['studiefase_id'=>$phaseId,'code'=>'TEST_HARD','titel'=>'Test','type'=>'Hardskills','omschrijving'=>'Test','normwaarden'=>'50']);
$service = new ChallengeService();
$first = $service->sync($user);
check($first['catalog']->count() === 14, 'De vier bestaande en tien nieuwe challenges moeten bestaan.');
check($first['active']->title === 'Punten verzamelen', 'Er moet één actieve challenge zijn.');

// Controleer nieuwe goal-challenge zonder de bestaande challengehistorie te wissen.
$goalChallenge = DB::table('challenges')->where('type','goals_created')->first();
DB::table('student_challenges')->where('user_id',$user->id)->delete();
DB::table('student_challenges')->insert(['user_id'=>$user->id,'challenge_id'=>$goalChallenge->id,'phase'=>'Oriëntatiefase','status'=>'active','baseline'=>0,'progress'=>0,'started_at'=>now()]);
LearningGoal::create(['gebruiker_id'=>$user->id,'leeruitkomst_id'=>$outcome->id,'onderwerp'=>'Mijn doel','omschrijving'=>'Ik wil beter leren analyseren.','status'=>'actief']);
$result = $service->sync($user);
check(count($result['completed']) === 1, 'Een nieuw leerdoel moet de challenge afronden.');
check((int)DB::table('xp_transactions')->sum('amount') === 25, 'Eerste stap geeft 25 XP.');
$service->sync($user);
check((int)DB::table('xp_transactions')->sum('amount') === 25, 'Hersynchroniseren mag geen dubbele XP geven.');
check(DB::table('student_challenges')->where('user_id',$user->id)->where('status','active')->count() === 1, 'Er mag maar één actieve challenge zijn.');

$project = Assignment::create(['gebruiker_id'=>$user->id,'onderwerp'=>'Project','type'=>'Test','status'=>'active','gevalideerde_uren'=>0]);
check($project->completed_at === null, 'Actief project mag geen afrondingsdatum hebben.');
$project->update(['status'=>'completed']);
check($project->fresh()->completed_at !== null, 'Afronden legt een echte datum vast.');
$soft = LearningOutcome::create(['studiefase_id'=>$phaseId,'code'=>'TEST_SOFT','titel'=>'Test soft','type'=>'Softskills','omschrijving'=>'Test','normwaarden'=>'50']);
$project->learningOutcomes()->attach([$outcome->id=>['max_points'=>20,'urenpercentage'=>50],$soft->id=>['max_points'=>20,'urenpercentage'=>50]]);
$project->update(['type'=>'Samenwerkingsopdracht','inleverdatum'=>now()->addDay(),'gevalideerde_uren'=>40]);
App\Models\AssignmentFile::create(['opdracht_id'=>$project->id,'originele_naam'=>'bewijs.pdf','opslagpad'=>'test.pdf','mime_type'=>'application/pdf','bestandsgrootte'=>100]);
App\Models\Reflection::create(['gebruiker_id'=>$user->id,'opdracht_id'=>$project->id,'leeruitkomst_id'=>$outcome->id,'onderwerp'=>'Terugblik','situatie'=>'Onze samenwerking','wat_ging_goed'=>'Taken verdelen','wat_kan_beter'=>'Meer afstemmen','volgende_stap'=>'Overleg plannen']);
$metric = new ReflectionMethod(ChallengeService::class,'metric');
foreach (['evidence'=>1,'on_time_projects'=>1,'project_reflections'=>1,'team_reflections'=>1,'skill_balance'=>2,'phase_percentage'=>50] as $type=>$expected) {
    check($metric->invoke($service,$user,$type,'Oriëntatiefase') === $expected, 'Onjuiste voortgang voor '.$type);
    check($metric->invoke($service,$user,$type,'Verdiepingsfase') === 0, 'Voortgang mag niet meetellen in een andere fase: '.$type);
}
$project->update(['status'=>'active']);
check($project->fresh()->completed_at === null, 'Heropenen verwijdert de afrondingsdatum.');
check($metric->invoke($service,$user,'evidence','Oriëntatiefase') === 0, 'Bewijs in een actief project telt niet mee.');
echo "OK: catalogus, automatische opvolging, fasevoortgang, eenmalige XP en afrondingsdatum.\n";
