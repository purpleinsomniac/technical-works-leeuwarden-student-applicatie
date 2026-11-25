<?php

namespace App\Http\Controllers\Student;

use App\Models\AssignmentFile;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\Reflection;

/**
 * Houd dashboardgegevens en API-antwoorden na opslaan in hetzelfde formaat.
 */
final class StudentPayload
{
    // Alleen het dashboard heeft de archiefstatus nodig om herstel aan te bieden.
    public static function reflection(Reflection $reflection, bool $includeArchiveStatus = false): array
    {
        $data = [
            'id' => (string) $reflection->id,
            'title' => $reflection->onderwerp,
            'assignmentId' => $reflection->opdracht_id,
            'code' => $reflection->learningOutcome->code,
            'situation' => $reflection->situatie,
            'good' => $reflection->wat_ging_goed,
            'improve' => $reflection->wat_kan_beter,
            'next' => $reflection->volgende_stap,
        ];

        if ($includeArchiveStatus) {
            $data['deleted'] = $reflection->trashed();
        }
        $data['date'] = $reflection->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i');

        return $data;
    }

    // Zet het feedbackbericht om naar de velden die het studentenscherm gebruikt.
    public static function feedback(Feedback $feedback): array
    {
        return [
            'id' => (string) $feedback->id,
            'type' => $feedback->type,
            'person' => $feedback->persoon ?: $feedback->author->naam,
            'title' => $feedback->onderwerp,
            'code' => $feedback->learningOutcome->code,
            'positive' => $feedback->wat_ging_goed,
            'improve' => $feedback->wat_kan_beter,
            'action' => $feedback->volgende_stap,
            'awardedHours' => $feedback->awarded_hours,
            'confirmedAt' => $feedback->processed_confirmed_at?->toIso8601String(),
            'date' => $feedback->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i'),
        ];
    }

    // Zet het leerdoel om naar de velden die het studentenscherm gebruikt.
    public static function goal(LearningGoal $goal): array
    {
        $outcome = $goal->learningOutcome;
        $phase = $outcome?->studyPhase?->naam;

        return [
            'id' => $goal->id,
            'title' => $goal->onderwerp,
            'description' => $goal->omschrijving,
            'status' => $goal->status,
            'code' => $outcome?->code,
            'outcome' => $outcome?->titel,
            'phaseCode' => self::phaseCode($phase),
            'phase' => $phase,
        ];
    }

    // Geef een downloadlink via de applicatie, zonder het interne opslagpad.
    public static function assignmentFile(AssignmentFile $file): array
    {
        return [
            'id' => $file->id,
            'name' => $file->originele_naam,
            'size' => $file->bestandsgrootte,
            'mime' => $file->mime_type,
            'downloadUrl' => route('assignments.files.download', $file),
            'uploadedAt' => $file->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i'),
        ];
    }

    // Gebruik de oriëntatiefase als er geen herkenbare fase is gekoppeld.
    public static function phaseCode(?string $name): string
    {
        return match ($name) {
            'Verdiepingsfase' => 'VF',
            'Afrondingsfase' => 'AF',
            default => 'OF',
        };
    }
}
