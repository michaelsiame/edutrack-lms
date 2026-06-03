<div class="space-y-3">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Select an Intake</h3>

    @php
        $currentIntakes = $course->currentIntakes()->get();
    @endphp

    @if($currentIntakes->isEmpty())
        <div class="p-4 bg-warning-50 border border-warning-200 rounded-lg text-warning-700 text-sm">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No intakes currently open for enrollment. Please check back later.
        </div>
    @else
        <form action="{{ route('enrollments.store', $course) }}" method="POST" class="space-y-3">
            @csrf

            @foreach($currentIntakes as $intake)
                @php
                    $isFull = $intake->is_full;
                    $spotsRemaining = $intake->spots_remaining;
                @endphp
                <label class="relative flex items-start p-4 rounded-xl border-2 transition-colors cursor-pointer
                    {{ $isFull ? 'border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed' : 'border-gray-200 hover:border-primary-300 dark:border-gray-700 dark:hover:border-primary-600' }}
                    {{ old('intake_id') == $intake->id ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : '' }}">
                    <input type="radio" name="intake_id" value="{{ $intake->id }}"
                        {{ $isFull ? 'disabled' : '' }}
                        {{ old('intake_id') == $intake->id ? 'checked' : ($loop->first && !$isFull ? 'checked' : '') }}
                        class="mt-1 w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                        required>
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $intake->name }}</span>
                            @if($isFull)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-danger-100 text-danger-700">FULL</span>
                            @elseif($spotsRemaining !== null && $spotsRemaining <= 5)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-warning-100 text-warning-700">{{ $spotsRemaining }} left</span>
                            @endif
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                            @if($intake->start_date)
                                <div><i class="fas fa-calendar-alt mr-1 w-3"></i>Starts {{ $intake->start_date->format('M d, Y') }}</div>
                            @endif
                            @if($intake->application_deadline)
                                <div><i class="fas fa-clock mr-1 w-3"></i>Apply by {{ $intake->application_deadline->format('M d, Y') }}</div>
                            @endif
                            <div class="font-medium" style="color: var(--od-fg);">
                                @if($intake->price_override)
                                    ZMW {{ number_format($intake->price_override, 2) }}
                                    <span class="text-gray-400 line-through text-xs">ZMW {{ number_format($course->price, 2) }}</span>
                                @else
                                    {{ $course->formatted_price }}
                                @endif
                            </div>
                        </div>
                    </div>
                </label>
            @endforeach

            <button type="submit" class="od-btn od-btn-primary w-full justify-center mt-2">
                <i class="fas fa-user-plus mr-2"></i>Enroll in Selected Intake
            </button>
        </form>
    @endif
</div>
