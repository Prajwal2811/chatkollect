@include('collector.components.header')
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
            <div class="col-lg-5 col-md-6">
                <div class="card mb-0 h-auto">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <a href="#"><img class="logo-auth" style="width:300px;" src="{{ asset('asset/images/logo-full.png') }}" alt=""></a>
                        </div>

                        @if(session('success'))
                            <div id="successAlert" class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div id="errorAlert" class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            ['successAlert', 'errorAlert'].forEach(function (id) {
                                let alertBox = document.getElementById(id);
                                if (alertBox) {
                                    setTimeout(function () {
                                        alertBox.classList.remove('show');
                                        alertBox.classList.add('fade');

                                        setTimeout(() => {
                                            alertBox.remove();
                                        }, 500);
                                    }, 3000); // 3 seconds
                                }
                            });
                        });
                        </script>

                        <!-- Step 1: Type selection -->
                        <div id="typeSelection">
                            <h4 class="text-center mb-4">Who are you?</h4>
                            <div class="row g-3 justify-content-center mb-4">
                                <div class="col-6">
                                    <div class="card type-option text-center p-3" data-type="tally" style="cursor:pointer; border:2px solid #dee2e6;">
                                        <i class="fa fa-calculator fa-2x mb-2"></i>
                                        <div class="fw-bold">Tally</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card type-option text-center p-3" data-type="manual" style="cursor:pointer; border:2px solid #dee2e6;">
                                        <i class="fa fa-manual fa-2x mb-2"></i>
                                        <div class="fw-bold">manual</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Login form (hidden until type selected) -->
                        <div id="loginFormWrapper" style="display:none;">
                            <h4 class="text-center mb-4">Sign in your Collector account</h4>
                            <form action="{{ route('collector.auth') }}" method="POST">
                                @csrf
                                <input type="hidden" name="account_type" id="account_type" value="{{ old('account_type') }}">

                                <div class="form-group mb-4">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" placeholder="Enter email" id="email" name="email" value="{{ old('email') }}">
                                </div>
                                <div class="form-group position-relative">
                                    <label>Password</label>
                                    <input type="password" id="password" class="form-control pr-5" placeholder="Enter password" name="password">
                                    <span toggle="#password"
                                        class="fa fa-eye toggle-password"
                                        style="position:absolute; top:38px; right:10px; cursor:pointer;">
                                    </span>
                                </div>

                                <div class="form-row d-flex flex-wrap justify-content-end mb-2 mt-4">
                                    <div class="form-group ms-2">
                                        <a href="#">Forgot Password?</a>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                                </div>
                            </form>
                            <div class="text-center mt-2">
                                <a href="#" id="changeType" class="text-muted small">« Change selection</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeOptions = document.querySelectorAll('.type-option');
    const typeSelection = document.getElementById('typeSelection');
    const loginFormWrapper = document.getElementById('loginFormWrapper');
    const accountTypeInput = document.getElementById('account_type');
    const changeType = document.getElementById('changeType');

    typeOptions.forEach(option => {
        option.addEventListener('click', function () {
            const type = this.dataset.type;
            accountTypeInput.value = type;

            typeSelection.style.display = 'none';
            loginFormWrapper.style.display = 'block';
        });
    });

    if (changeType) {
        changeType.addEventListener('click', function (e) {
            e.preventDefault();
            accountTypeInput.value = '';
            loginFormWrapper.style.display = 'none';
            typeSelection.style.display = 'block';
        });
    }

    // If old('account_type') is already set (after a validation error), show the form directly
    if (accountTypeInput.value) {
        typeSelection.style.display = 'none';
        loginFormWrapper.style.display = 'block';
    }
});
</script>

@include('collector.components.footer')