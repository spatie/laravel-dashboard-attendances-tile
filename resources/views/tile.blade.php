<x-dashboard-tile :position="$position">
    <div class="grid gap-1 h-full">
        <div
            class="flex items-center justify-center w-10 h-10 rounded-full"
            style="background-color: rgba(255, 255, 255, .9)"
        >
            <div class="text-2xl leading-none -mt-1">
                🏢
            </div>
        </div>
        <ul class="self-center divide-y-2">
            @foreach($days as $day)
                <li class="py-1 flex flex-row justify-between items-center">
                    <span>{{ substr($day, 0, 3) }}</span>
                    <div class="flex flex-row flex-nowrap space-x-1">
                        @foreach($attendances->getMembersInOffice($day) as $email)
                            <div class="overflow-hidden w-4 h-4 rounded-full relative">
                                <img src="https://gravatar.com/avatar/{{ md5($email) }}?s=240" class="block w-4 h-4 object-cover filter-gray" style="filter: contrast(75%) grayscale(1) brightness(150%)">
                                <div class="absolute inset-0 bg-accent opacity-25"></div>
                            </div>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</x-dashboard-tile>
