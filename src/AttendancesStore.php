<?php

namespace Spatie\AttendancesTile;

use Spatie\Dashboard\Models\Tile;

class AttendancesStore
{
    private Tile $tile;

    public static function make()
    {
        return new static();
    }

    public function __construct()
    {
        $this->tile = Tile::firstOrCreateForName("attendances");
    }

    public function setMembersInOffice(string $weekday, string ...$emails): self
    {
        $this->tile->putData("in_office_{$weekday}", $emails);

        return $this;
    }

    public function getMembersInOffice(string $weekday): array
    {
        return $this->tile->getData("in_office_{$weekday}") ?? [];
    }
}
