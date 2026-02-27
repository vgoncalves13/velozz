<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($this->getPlans() as $plan)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border-2 @if($plan->name === 'Professional') border-primary-500 @else border-gray-200 dark:border-gray-700 @endif">
                @if($plan->name === 'Professional')
                    <div class="bg-primary-500 text-white text-center py-2 text-sm font-semibold">
                        MOST POPULAR
                    </div>
                @endif

                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $plan->name }}
                    </h3>

                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">
                            €{{ number_format($plan->price, 2) }}
                        </span>
                        <span class="text-gray-600 dark:text-gray-400">/month</span>
                    </div>

                    @if($plan->trial_days > 0)
                        <div class="mb-4 px-3 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg text-sm text-center">
                            {{ $plan->trial_days }} days free trial
                        </div>
                    @endif

                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ number_format($plan->leads_limit_per_month) }} leads/month
                            </span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ number_format($plan->messages_limit_per_day) }} messages/day
                            </span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ $plan->operators_limit }} {{ Str::plural('operator', $plan->operators_limit) }}
                            </span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ $plan->whatsapp_instances_limit }} WhatsApp {{ Str::plural('instance', $plan->whatsapp_instances_limit) }}
                            </span>
                        </li>
                    </ul>

                    <button
                        wire:click="subscribe({{ $plan->id }})"
                        class="w-full py-3 px-6 rounded-lg font-semibold transition-colors
                            @if($plan->name === 'Professional')
                                bg-primary-600 hover:bg-primary-700 text-white
                            @else
                                bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white
                            @endif"
                    >
                        Choose {{ $plan->name }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    @if($errors->any())
        <div class="mt-6 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-300 mb-2">
            Test Mode Enabled
        </h4>
        <p class="text-blue-800 dark:text-blue-400 text-sm mb-3">
            This is Stripe Test Mode. Use test card: <code class="bg-white dark:bg-gray-800 px-2 py-1 rounded">4242 4242 4242 4242</code>
        </p>
        <p class="text-blue-700 dark:text-blue-500 text-xs">
            No real charges will be made. Any future expiry date and any 3-digit CVC will work.
        </p>
    </div>
</x-filament-panels::page>
