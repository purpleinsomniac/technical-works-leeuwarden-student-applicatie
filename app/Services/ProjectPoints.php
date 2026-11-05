<?php

namespace App\Services;

use App\Models\User;

class ProjectPoints
{
    /** Verdeel ieder project precies eenmaal over leeruitkomsten binnen zijn eigen fase. */
    public function forStudent(User $user): array
    {
        return $this->allocation($user)['outcomes'];
    }

    public function allocation(User $user): array
    {
        $points = [];
        $projects = [];
        $maxProjects = [];
        foreach ($user->assignments()->with('learningOutcomes.studyPhase')->orderBy('id')->get() as $project) {
            $linked = $project->learningOutcomes->sortBy('id')->values();
            $phaseId = $linked->first()?->studiefase_id;
            $linked = $linked->where('studiefase_id', $phaseId)->values();
            $projects[$project->id] = 0;
            if ($linked->isEmpty()) continue;
            $maximum = min(80, (int) $linked->sum(fn ($outcome) => min(20, $outcome->pivot->max_points)));
            $maxProjects[$project->id] = $maximum;
            // Punten tellen pas mee nadat het project is afgerond.
            if ($project->status !== 'completed') continue;
            $total = max(0, min((int) $project->gevalideerde_uren, $maximum));
            if ($maximum > 0) {
                $shares = [];
                foreach ($linked as $outcome) {
                    $weight = $linked->sum(fn ($item) => min(20, $item->pivot->max_points));
                    $exact = $total * min(20, $outcome->pivot->max_points) / $weight;
                    $shares[$outcome->id] = ['points'=>(int) floor($exact),'remainder'=>$exact-floor($exact)];
                }
                $remaining = $total - array_sum(array_column($shares,'points'));
                uasort($shares, fn ($a,$b) => $b['remainder'] <=> $a['remainder']);
                foreach ($shares as &$share) { if ($remaining > 0) { $share['points']++; $remaining--; } }
                unset($share);
                foreach ($linked as $outcome) {
                    $amount = min($shares[$outcome->id]['points'], max(0,$this->norm($outcome)-($points[$outcome->id]??0)));
                    $points[$outcome->id] = ($points[$outcome->id]??0)+$amount;
                    $projects[$project->id] += $amount;
                }
                continue;
            }
            // Verdeel uitsluitend binnen de resterende ruimte; een volle leeruitkomst krijgt niets extra.
            while ($total > 0) {
                $available = $linked->filter(fn ($outcome) => ($points[$outcome->id] ?? 0) < $this->norm($outcome));
                if ($available->isEmpty()) break;
                $share = max(1, intdiv($total, $available->count()));
                foreach ($available as $outcome) {
                    $amount = min($share, $total, $this->norm($outcome) - ($points[$outcome->id] ?? 0));
                    $points[$outcome->id] = ($points[$outcome->id] ?? 0) + $amount;
                    $projects[$project->id] += $amount;
                    $total -= $amount;
                }
            }
        }
        return ['outcomes' => $points, 'projects' => $projects, 'maximums'=>$maxProjects];
    }

    public function norm($outcome): int
    {
        $legacyNorm = $outcome->normuren ?: match ($outcome->studyPhase?->naam) {
            'Verdiepingsfase' => 200,
            'Afrondingsfase' => 300,
            default => 150,
        };
        return (int) ceil($legacyNorm * .8);
    }
}
