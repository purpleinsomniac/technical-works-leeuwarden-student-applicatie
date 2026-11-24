<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','cache.default'=>'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\Artisan::call('migrate',['--database'=>'sqlite','--force'=>true]);

use App\Models\{Assignment, LearningOutcome, Role, StudyPhase, User};
use App\Services\ProjectPoints;
use Database\Seeders\SecondStudentDemoSeeder;

$phase = StudyPhase::create(['naam'=>'Oriëntatiefase']);
$role = Role::create(['naam'=>'student']);
foreach (['Hardskills','Softskills'] as $kind) {
    for ($i=1;$i<=8;$i++) LearningOutcome::create(['studiefase_id'=>$phase->id,'code'=>'IT_OF_LU_'.$kind.'_'.str_pad((string)$i,3,'0',STR_PAD_LEFT),'titel'=>$kind.' '.$i,'type'=>$kind,'omschrijving'=>'Voorbeeld','normwaarden'=>'50']);
}
$sam = User::create(['naam'=>'Sam de Vries','email'=>'student@example.test','password'=>'Demo12345!','rol'=>'student','studiefase_id'=>$phase->id,'hoofdrol_id'=>$role->id]);
$project = $sam->assignments()->create(['onderwerp'=>'Alleen voor Sam','type'=>'Test','status'=>'completed','gevalideerde_uren'=>20]);
$project->learningOutcomes()->attach(LearningOutcome::first()->id,['max_points'=>20,'urenpercentage'=>100]);
User::create(['naam'=>'Coach','email'=>'coach@example.test','password'=>'Demo12345!','rol'=>'coach']);
$samBefore = $sam->assignments()->get()->toJson();
(new SecondStudentDemoSeeder())->run();
$wesley = User::where('email','student2@example.test')->firstOrFail();
if ($wesley->naam !== 'Wesley Rinsma' || !Illuminate\Support\Facades\Hash::check('Demo12345!',$wesley->password)) throw new RuntimeException('Accountgegevens onjuist.');
if ($samBefore !== $sam->assignments()->get()->toJson()) throw new RuntimeException('Sams gegevens zijn gewijzigd.');
$service = app(ProjectPoints::class);
if (array_sum($service->forStudent($sam)) !== 20 || array_sum($service->forStudent($wesley)) !== 100) throw new RuntimeException('Punten zijn niet per student gescheiden.');
$method = new ReflectionMethod(App\Http\Controllers\Student\DashboardController::class,'studentData');
$data = $method->invoke(app(App\Http\Controllers\Student\DashboardController::class),$wesley);
if ($data['assignments']->count() !== 3 || $data['goals']->count() !== 3 || $data['reflections']->count() !== 1 || $data['feedback']->count() !== 1) throw new RuntimeException('Dashboardgegevens ontbreken of zijn gemengd.');
if ($data['assignments']->contains('title','Alleen voor Sam')) throw new RuntimeException('Project van andere student gelekt.');
(new SecondStudentDemoSeeder())->run();
if ($wesley->assignments()->count() !== 3 || $wesley->learningGoals()->count() !== 3 || $wesley->reflections()->count() !== 1) throw new RuntimeException('Seeder maakt dubbele gegevens.');
echo "OK: eigen punten, projecten, leerdoelen, reflecties en feedback; Sam ongewijzigd; opnieuw uitvoeren zonder duplicaten.\n";
