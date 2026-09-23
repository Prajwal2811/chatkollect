@include('owner.tally.components.header')
  <div class="fix-wrapper" style="
        background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://plus.unsplash.com/premium_photo-1661382019197-94d5edb38186?q=80&w=1172&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        min-height: 100vh;
        display: flex;
        align-items: center;
    ">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="card mb-0 h-auto shadow-sm border-0">
                    <div class="card-body p-4">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show text-center" role="alert" id="alertBox">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show text-center" role="alert" id="alertBox">
                                {{ session('error') }}
                            </div>
                        @endif

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                let alertBox = document.getElementById('alertBox');

                                if (alertBox) {
                                    setTimeout(function () {
                                        alertBox.classList.remove('show');

                                        setTimeout(() => {
                                            alertBox.remove();
                                        }, 500);
                                    }, 3000);
                                }
                            });
                        </script>

                        <div class="text-center">
                            <div class="mb-3" style="font-size:60px;">
                                🔒
                            </div>

                            <h2 class="fw-bold text-dark mb-3">
                                Subscription Required
                            </h2>

                            <p class="text-muted mb-4">
                                Your subscription plan has expired or is not active.
                                Purchase a subscription to access your dashboard and all business features.
                            </p>

                            <div class="mb-4">
                                <span class="fw-bold text-primary" style="font-size:45px;">
                                    ₹999
                                </span>
                                <span class="text-muted">
                                    / Month
                                </span>
                            </div>

                            <div class=" rounded p-3 mb-4 text-start">

                                <div class=" rounded p-3 mb-4">

                                    <div class="row">

                                        <div class="col-6 mb-3">
                                            <i class="fas fa-chart-line text-success me-2"></i>
                                            Dashboard
                                        </div>

                                        <div class="col-6 mb-3">
                                            <i class="fas fa-users text-success me-2"></i>
                                            Staff Management
                                        </div>

                                        <div class="col-6 mb-3">
                                            <i class="fas fa-user-friends text-success me-2"></i>
                                            Customers
                                        </div>

                                        <div class="col-6 mb-3">
                                            <i class="fas fa-file-alt text-success me-2"></i>
                                            Reports
                                        </div>

                                        <div class="col-6 mb-3">
                                            <i class="fas fa-infinity text-success me-2"></i>
                                            Unlimited Usage
                                        </div>

                                        <div class="col-6 mb-3">
                                            <i class="fas fa-headset text-success me-2"></i>
                                            Priority Support
                                        </div>

                                    </div>

                                </div>
                            </div>

                            <a href="{{ route('owner.subscribe') }}"
                               class="btn btn-primary btn-lg w-100">
                                Buy Subscription
                            </a>

                            <a href="{{ route('owner.signOut') }}"
                               class="btn btn-outline-secondary w-100 mt-2">
                                Logout
                            </a>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('owner.tally.components.footer')