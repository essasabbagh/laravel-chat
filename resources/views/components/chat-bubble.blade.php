<div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} mb-3">
    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $isMine ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-800' }}">
        @if(!$isMine)
            <p class="text-xs font-semibold mb-1">{{ $sender }}</p>
        @endif
        <p class="text-sm">{{ $body }}</p>
        <p class="text-xs text-right mt-1 opacity-75">{{ $time }}</p>
    </div>
</div>
