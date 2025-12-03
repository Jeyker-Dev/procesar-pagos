<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('3D Secure Authentication') }}
        </h2>
    </x-slot>

    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">3D Secure Authentication</h1>
        <p class="mb-4">To complete your payment, please authenticate using 3D Secure.</p>
    </div>

    @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            const pk = "{{ config('services.stripe.key') }}";
            console.log(pk);

            if (!pk) {
                console.error('Stripe public key not configured');
            } else {
                const stripe = Stripe(pk);

                stripe.handleCardAction("{{ $clientSecret }}")
                    .then(function(result) {
                        if (result.error) {
                            window.location.replace("{{ route('cancelled') }}");
                        } else {
                            window.location.replace("{{ route('approval') }}");
                        }
                    });
            }
        </script>
    @endpush
</x-app-layout>
