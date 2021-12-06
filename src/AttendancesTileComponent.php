<?php

namespace Spatie\AttendancesTile;

use Livewire\Component;

class AttendancesTileComponent extends Component
{
    /** @var string */
    public $calendarId;

    /** @var string */
    public $position;

    /** @var string|null */
    public $title;

    /** @var int|null */
    public $refreshInSeconds;

    public function mount(string $position, ?string $title = null, int $refreshInSeconds = null)
    {
        $this->calendarId = $calendarId ?? config('dashboard.tiles.calendar.ids')[0];

        $this->position = $position;

        $this->title = $title;

        $this->refreshInSeconds = $refreshInSeconds;
    }

    public function render()
    {
        return view('dashboard-attendances-tile::tile', [
            'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'attendances' => AttendancesStore::make(),
            'refreshIntervalInSeconds' => config('dashboard.tiles.skeleton.refresh_interval_in_seconds') ?? 60,
        ]);
    }
}
