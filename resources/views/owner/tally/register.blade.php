@include('owner.tally.components.header')

<div class="fix-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-11">

                <div class="card mb-0 h-auto">
                    <div class="card-body">

                        <h4 class="text-center mb-4">Business Owner Registration</h4>

                        <form id="ownerRegisterForm"
                              action="{{ route('owner.register.submit') }}"
                              method="POST">

                            @csrf

                            <div class="row">

                                {{-- Owner Name --}}
                                <div class="col-md-6 mb-3">
                                    <label>Owner Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="owner_name"
                                           class="form-control"
                                           placeholder="Enter owner name">
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6 mb-3">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email"
                                           name="email"
                                           class="form-control"
                                           placeholder="hello@example.com">
                                </div>

                                {{-- Phone --}}
                                <div class="col-md-6 mb-3">
                                    <label>Phone Number <span class="text-danger">*</span></label>
                                    <input type="text"
                                        name="phone"
                                        class="form-control"
                                        maxlength="10"
                                        minlength="10"
                                        pattern="[0-9]{10}"
                                        inputmode="numeric"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                        placeholder="Enter 10 digit phone number">
                                </div>

                                {{-- Business Name --}}
                                <div class="col-md-6 mb-3">
                                    <label>Business Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="business_name"
                                           class="form-control"
                                           placeholder="Enter business name">
                                </div>

                                {{-- Business Type --}}
                                <div class="col-md-6 mb-3">
                                    <label>Business Type <span class="text-danger">*</span></label>
                                    <select name="business_type" class="form-control">
                                        <option value="">Select Business Type</option>
                                        <option value="restaurant">Restaurant</option>
                                        <option value="hotel">Hotel</option>
                                        <option value="shop">Shop</option>
                                        <option value="service">Service</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                {{-- GST Number --}}
                                <div class="col-md-6 mb-3">
                                    <label>GST Number</label>
                                    <input type="text"
                                           name="gst_number"
                                           class="form-control"
                                           placeholder="Enter GST Number (Optional)">
                                </div>

                                {{-- Address --}}
                                <div class="col-md-12 mb-3">
                                    <label>Business Address <span class="text-danger">*</span></label>
                                    <textarea name="address"
                                              rows="3"
                                              class="form-control"
                                              placeholder="Enter full business address"></textarea>
                                </div>

                                {{-- Password --}}
                                <div class="col-md-6 mb-3 position-relative">
                                    <label>Password <span class="text-danger">*</span></label>

                                    <input type="password"
                                           name="password"
                                           id="password"
                                           class="form-control pr-5"
                                           placeholder="Enter password">

                                    <span toggle="#password"
                                          class="fa fa-eye toggle-password"
                                          style="position:absolute;top:40px;right:20px;cursor:pointer;">
                                    </span>
                                </div>

                                {{-- Confirm Password --}}
                                <div class="col-md-6 mb-3 position-relative">
                                    <label>Confirm Password <span class="text-danger">*</span></label>

                                    <input type="password"
                                           name="password_confirmation"
                                           id="password_confirmation"
                                           class="form-control pr-5"
                                           placeholder="Confirm password">

                                    <span toggle="#password_confirmation"
                                          class="fa fa-eye toggle-password"
                                          style="position:absolute;top:40px;right:20px;cursor:pointer;">
                                    </span>
                                </div>

                            </div>

                            <div class="text-center mt-3">
                                <button type="submit" id="submitBtn" class="btn btn-primary px-5">
                                    <span class="btn-text">Create Account</span>
                                    <span class="btn-loader d-none">
                                        <i class="fa fa-spinner fa-spin"></i> Processing...
                                    </span>
                                </button>
                            </div>

                        </form>

                        <div class="new-account mt-3 text-center">
                            <p>
                                Already have an account?
                                <a class="text-primary" href="{{ route('owner.login') }}">
                                    Sign In
                                </a>
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('owner.tally.components.footer')

<script>
$(document).ready(function () {

    // Show / Hide Password
    $('.toggle-password').click(function () {

        let input = $($(this).attr('toggle'));

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Only numbers in phone field
    $('input[name="phone"]').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });

    // Remove error while typing
    $(document).on('keyup change', 'input, textarea, select', function () {
        $(this).closest('.mb-3').find('.error-text').remove();
    });

    // Form Validation
    $('#ownerRegisterForm').submit(function (e) {

        $('.error-text').remove();

        let valid = true;

        let owner_name = $('input[name="owner_name"]').val().trim();
        let email = $('input[name="email"]').val().trim();
        let phone = $('input[name="phone"]').val().trim();
        let business_name = $('input[name="business_name"]').val().trim();
        let business_type = $('select[name="business_type"]').val();
        let address = $('textarea[name="address"]').val().trim();
        let password = $('#password').val();
        let confirm_password = $('#password_confirmation').val();

        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let phonePattern = /^[0-9]{10}$/;

        // Owner Name
        if (owner_name === '') {
            $('input[name="owner_name"]').after(
                '<small class="text-danger error-text">Owner name is required.</small>'
            );
            valid = false;
        }

        // Email
        if (email === '') {
            $('input[name="email"]').after(
                '<small class="text-danger error-text">Email is required.</small>'
            );
            valid = false;
        } else if (!emailPattern.test(email)) {
            $('input[name="email"]').after(
                '<small class="text-danger error-text">Enter a valid email address.</small>'
            );
            valid = false;
        }

        // Phone
        if (phone === '') {
            $('input[name="phone"]').after(
                '<small class="text-danger error-text">Phone number is required.</small>'
            );
            valid = false;
        } else if (!phonePattern.test(phone)) {
            $('input[name="phone"]').after(
                '<small class="text-danger error-text">Phone number must be exactly 10 digits.</small>'
            );
            valid = false;
        }

        // Business Name
        if (business_name === '') {
            $('input[name="business_name"]').after(
                '<small class="text-danger error-text">Business name is required.</small>'
            );
            valid = false;
        }

        // Business Type
        if (business_type === '') {
            $('select[name="business_type"]').after(
                '<small class="text-danger error-text">Please select business type.</small>'
            );
            valid = false;
        }

        // Address
        if (address === '') {
            $('textarea[name="address"]').after(
                '<small class="text-danger error-text">Business address is required.</small>'
            );
            valid = false;
        }

        // Password
        if (password === '') {
            $('#password').after(
                '<small class="text-danger error-text">Password is required.</small>'
            );
            valid = false;
        } else if (password.length < 8) {
            $('#password').after(
                '<small class="text-danger error-text">Password must be at least 8 characters.</small>'
            );
            valid = false;
        } else if (!/(?=.*[A-Z])(?=.*[a-z])(?=.*\d)/.test(password)) {
            $('#password').after(
                '<small class="text-danger error-text">Password must contain uppercase, lowercase and a number.</small>'
            );
            valid = false;
        }

        // Confirm Password
        if (confirm_password === '') {
            $('#password_confirmation').after(
                '<small class="text-danger error-text">Confirm password is required.</small>'
            );
            valid = false;
        } else if (password !== confirm_password) {
            $('#password_confirmation').after(
                '<small class="text-danger error-text">Passwords do not match.</small>'
            );
            valid = false;
        }

        // Stop Submit If Validation Failed
        if (!valid) {
            e.preventDefault();
            return false;
        }

        // Disable Button + Show Loader
        $('#submitBtn')
            .prop('disabled', true)
            .html(`
                <span class="spinner-border spinner-border-sm me-2"></span>
                Please Wait...
            `);

        return true;
    });

});
</script>