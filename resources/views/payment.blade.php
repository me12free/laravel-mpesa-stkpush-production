{{--
    Copyright (c) 2025 John Ekiru <johnewoi72@gmail.com>

    Premium Laravel M-Pesa STK Push Integration

    This Blade view provides a modern payment form and modal for status updates.
    It is fully reusable and can be customized for any Laravel project.
--}}
{{-- Standalone view: no @extends for maximum compatibility --}}
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h2 class="mb-4 text-center">Make a Payment</h2>
                    {{-- Display success, error, and validation messages --}}
                    @if(session('success'))
                        <div class="alert alert-success text-center">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger text-center">{{ session('error') }}</div>
                    @endif
                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger text-center">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {{-- Payment form --}}
                    <form id="payment-form" method="POST" action="{{ route('payments.stkpush') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="payer_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="payer_name" name="payer_name" value="{{ old('payer_name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (KES)</label>
                            <input type="number" class="form-control" id="amount" name="amount" min="1" value="{{ old('amount') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference</label>
                            <input type="text" class="form-control" id="reference" name="reference" value="{{ old('reference') }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Pay Now</button>
                    </form>
                </div>
            </div>
            @if(config('mpesa-stkpush.branding.powered_by', true))
                <div class="text-center mt-3 small text-muted">
                    Powered by <a href="https://github.com/me12free/mpesa-laravel-stkpush-premium" target="_blank">M-Pesa Premium</a>
                </div>
            @endif
            @if(config('mpesa-stkpush.branding.upgrade_link', true))
                <div class="text-center mt-1">
                    <a href="https://buymeacoffee.com/johnekiru7v" class="btn btn-link p-0">Upgrade to Premium</a>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- Modal for payment status updates -->
<div class="modal fade" id="paymentStatusModal" tabindex="-1" aria-labelledby="paymentStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content hfho-modal-content">
      <div class="modal-header hfho-modal-header">
        <h5 class="modal-title hfho-modal-title" id="paymentStatusModalLabel">Payment Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body hfho-modal-body" id="paymentStatusBody">
        <div class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Processing...</span>
          </div>
          <p class="mt-3">Waiting for payment confirmation...</p>
        </div>
      </div>
      <div class="modal-footer hfho-modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
{{-- JavaScript for AJAX form submission and modal updates --}}
<script>
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var modal = new bootstrap.Modal(document.getElementById('paymentStatusModal'));
    modal.show();
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => response.json())
    .then(data => {
        var body = document.getElementById('paymentStatusBody');
        if (data.status === 'pending') {
            body.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-3">Payment initiated. Complete on your phone.</p></div>';
        } else if (data.status === 'success') {
            body.innerHTML = '<div class="text-center"><i class="fa fa-check-circle fa-2x"></i><p class="mt-3">Payment successful!</p></div>';
        } else {
            body.innerHTML = '<div class="text-center"><i class="fa fa-times-circle fa-2x"></i><p class="mt-3">' + (data.message || 'Payment failed.') + '</p></div>';
        }
    })
    .catch(() => {
        document.getElementById('paymentStatusBody').innerHTML = '<div class="text-center"><i class="fa fa-times-circle fa-2x"></i><p class="mt-3">An error occurred. Please try again.</p></div>';
    });
});
</script>
