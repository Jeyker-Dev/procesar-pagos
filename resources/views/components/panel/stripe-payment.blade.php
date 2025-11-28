<label class="my-3">Card details:</label>

<div id="card-element" class="mt-2 p-4 border border-gray-300 rounded-md form-control">
    <!-- A Stripe Element will be inserted here. -->
</div>
<div id="card-errors" role="alert" class="text-red-600 mt-2"></div>
<input type="hidden" wire:model="paymentMethod" name="payment_method" id="payment-method">

@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pk = "{{ config('services.stripe.key') }}";

            if (!pk) {
                console.error('Stripe public key not configured');
                return;
            }

            const stripe = Stripe(pk);
            const elements = stripe.elements();
            let cardElement = null;
            let mounted = false;

            function mountCard() {
                const container = document.getElementById('card-element');
                if (!container || mounted) return;

                cardElement = elements.create('card');
                cardElement.mount('#card-element');
                mounted = true;
            }

            setTimeout(mountCard, 0);

            const observer = new MutationObserver(() => {
                if (!mounted && document.getElementById('card-element')) {
                    mountCard();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
            });

            const stopWhenMounted = setInterval(() => {
                if (mounted) {
                    observer.disconnect();
                    clearInterval(stopWhenMounted);
                }
            }, 200);

            const form = document.getElementById('payment-form');
            const payButton = document.getElementById('pay-button');
            const errorContainer = document.getElementById('card-errors');

            if (form && payButton) {
                payButton.addEventListener('click', async (e) => {
                    e.preventDefault();
                    mountCard();

                    if (!cardElement) {
                        console.error('cardElement no montado');
                        return;
                    }

                    const selected = @this.get('paymentPlatform');
                    const stripePlatformId = @json(\App\Models\PaymentPlatform::where('name', 'Stripe')->value('id'));

                    if (String(selected) !== String(stripePlatformId)) {
                        @this.call('pay');
                        return;
                    }

                    if (errorContainer) {
                        errorContainer.textContent = '';
                    }

                    const { paymentMethod, error } = await stripe.createPaymentMethod({
                        type: 'card',
                        card: cardElement,
                        billing_details: {
                            name: "{{ auth()->user()->name }}",
                            email: "{{ auth()->user()->email }}",
                        },
                    });

                    if (error) {
                        console.error('Error creating payment method:', error);

                        if (errorContainer) {
                            errorContainer.textContent = error.message || 'Error al procesar la tarjeta.';
                        }

                        return;
                    }

                    const tokenInput = document.getElementById('payment-method');
                    if (!tokenInput) {
                        console.error('Hidden input for payment method not found');
                        return;
                    }

                    tokenInput.value = paymentMethod.id;

                    @this.set('paymentMethod', paymentMethod.id);

                    @this.call('pay');
                });
            }
        });
    </script>
@endpush
