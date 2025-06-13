<x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
>
    <div class="space-y-4">
        @foreach ($getOptions() as $value => $label)
            <div
                    class="relative cursor-pointer rounded-xl border-2 p-6 transition-all duration-200 hover:shadow-lg
                       {{ $getState() === $value
                          ? 'border-primary-500 bg-primary-50 shadow-md ring-2 ring-primary-200'
                          : 'border-gray-200 bg-white hover:border-gray-300' }}"
                    wire:click="$set('{{ $getStatePath() }}', '{{ $value }}')"
            >
                {{-- Radio Button --}}
                <div class="absolute right-4 top-4">
                    <div class="relative">
                        <input
                                type="radio"
                                name="{{ $getStatePath() }}"
                                value="{{ $value }}"
                                {{ $getState() === $value ? 'checked' : '' }}
                                class="h-5 w-5 border-gray-300 text-primary-600 focus:ring-primary-500"
                                readonly
                        >
                        @if ($getState() === $value)
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="h-3 w-3 text-primary-600" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Icon --}}
                @if (isset($getIcons()[$value]))
                    <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-lg
                                {{ $getState() === $value ? 'bg-primary-100' : 'bg-gray-100' }}">
                        <x-heroicon-o-{{ $getIcons()[$value] }}
                                class="h-6 w-6 {{ $getState() === $value ? 'text-primary-600' : 'text-gray-600' }}"
                        />
                    </div>
                @endif

                {{-- Title --}}
                <h3 class="text-lg font-semibold {{ $getState() === $value ? 'text-primary-900' : 'text-gray-900' }}">
                    {{ $label }}
                </h3>

                {{-- Description --}}
                @if (isset($getDescriptions()[$value]))
                    <p class="mt-2 text-sm {{ $getState() === $value ? 'text-primary-700' : 'text-gray-600' }}">
                        {{ $getDescriptions()[$value] }}
                    </p>
                @endif

                {{-- Selected Indicator --}}
                @if ($getState() === $value)
                    <div class="absolute -right-1 -top-1 h-6 w-6 rounded-full bg-primary-500 shadow-lg">
                        <svg class="h-4 w-4 text-white mt-1 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</x-dynamic-component>