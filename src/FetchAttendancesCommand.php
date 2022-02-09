<?php

namespace Spatie\AttendancesTile;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Google\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\GoogleCalendar\Event;

class FetchAttendancesCommand extends Command
{
    protected $signature = 'dashboard:fetch-attendances {--week=}';

    protected $description = 'Fetch the attendances for each team member';

    public function handle(AttendancesStore $store)
    {
        $this->info('Fetching attendances');

        [$startOfWeek, $endOfWeek] = $this->resolveInterval();

        $this->info("Fetching attendances from {$startOfWeek->format('d-m-Y')} until {$endOfWeek->format('d-m-Y')}");

        $days = collect(CarbonPeriod::create($startOfWeek, $endOfWeek))->mapWithKeys(fn (Carbon $date) => [
            $date->format('d-m-Y') => [],
        ]);

        foreach (config('dashboard.tiles.attendances.emails', []) as $email) {
            try {
                $events = Event::get($startOfWeek, $endOfWeek, [], $email);
            } catch (Exception $exception) {
                if ($exception->getCode() === 404) {
                    $message = "Could not load calendar `{$email}`, maybe we do not have access to it?";

                    $this->error($message);
                    Log::info($message);

                    continue;
                }

                throw $exception;
            }

            $daysAtOffice = collect($events)
                ->filter(fn (Event $event) => Str::of($event->summary)->lower()->contains(config('dashboard.tiles.attendances.keywords.office', [])))
                ->flatMap(fn (Event $event) => array_map(
                    fn (Carbon $carbon) => $carbon->format('d-m-Y'),
                    $this->resolveDatesForEvent($event, $startOfWeek, $endOfWeek)
                ))
                ->unique();

            $daysAtHome = collect($events)
                ->filter(fn (Event $event) => Str::of($event->summary)->lower()->contains(config('dashboard.tiles.attendances.keywords.home', [])))
                ->flatMap(fn (Event $event) => array_map(
                    fn (Carbon $carbon) => $carbon->format('d-m-Y'),
                    $this->resolveDatesForEvent($event, $startOfWeek, $endOfWeek)
                ))
                ->unique();

            $days->transform(function (array $attendances, string $date) use ($email, $daysAtHome, $daysAtOffice) {
                if ($daysAtOffice->contains($date)) {
                    $attendances[] = $email;

                    return $attendances;
                }

                if ($daysAtHome->contains($date)) {
                    return $attendances;
                }

                if (config('dashboard.tiles.attendances.missingKeywordMeansAtOffice', true)) {
                    $attendances[] = $email;

                    return $attendances;
                }

                return $attendances;
            });
        }

        $days->each(function (array $attendances, string $date) use ($store) {
            $weekDay = CarbonImmutable::createFromFormat('d-m-Y', $date)->format('l');

            $store->setMembersInOffice($weekDay, ...$attendances);
        });
    }

    private function resolveInterval(): array
    {
        $start = $this->option('week')
            ? CarbonImmutable::now()->setISODate(CarbonImmutable::now()->year, (int) $this->option('week'))
            : CarbonImmutable::now();

        return [
            $start->startOf('week'),
            $start->endOf('week')->subDays(2),
        ];
    }

    /** @return Carbon[] */
    private function resolveDatesForEvent(Event $event, CarbonImmutable $startOfWeek, CarbonImmutable $endOfWeek): array
    {
        $start = CarbonImmutable::createFromFormat('Y-m-d', $event->start->date)->startOfDay();
        $end = CarbonImmutable::createFromFormat('Y-m-d', $event->end->date)->subDay()->endOfDay();

        if ($start->isBefore($startOfWeek)) {
            $start = $startOfWeek;
        }

        if ($end->isAfter($endOfWeek)) {
            $end = $endOfWeek;
        }

        return CarbonPeriod::create($start, $end)->toArray();
    }
}
