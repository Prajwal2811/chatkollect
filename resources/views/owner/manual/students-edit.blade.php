@include('owner.components.header')
    <div id="main-wrapper">
		<div class="nav-header">
            <a href="#" class="brand-logo">
				<svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
					<!-- RMS Text -->
					<text x="55" y="32"
						font-size="22"
						font-family="Arial, sans-serif"
						font-weight="bold"
						fill="#4E3F6B">
						RMS
					</text>

				</svg>
			</a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span>
						<span class="line"></span>
						<span class="line"></span>
                </div>
            </div>
        </div>
		@include('owner.components.navbar')
		@include('owner.components.sidebar')

		@php
			// Dummy data (remove this block once real $student is passed from the controller)
			$student = (object) [
				'id' => 1,
				'name' => 'Aarav Sharma',
				'email' => 'aarav.sharma@example.com',
				'phone' => '9876543210',
				'address' => 'Nagpur, Maharashtra',
				'course' => '10th',
				'section' => 'A',
				'roll_number' => 'STU001',
			];
		@endphp

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">

							<div class="card-header">
								<h4 class="card-title">Edit Student</h4>
							</div>

							<div class="card-body">

								<form id="studentForm" action="#" method="POST">
									@csrf
									@method('PUT')
									<div class="row">

										<!-- Name -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Full Name</label>
											<input type="text" name="name" value="{{ old('name', $student->name) }}" class="form-control">
											<small class="text-danger error-name"></small>
										</div>

										<!-- Email -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Email</label>
											<input type="email" name="email" value="{{ old('email', $student->email) }}" class="form-control">
											<small class="text-danger error-email"></small>
										</div>

										<!-- Phone -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Phone</label>
											<input type="text"
												name="phone"
												value="{{ old('phone', $student->phone) }}"
												class="form-control"
												maxlength="10"
												minlength="10"
												pattern="[0-9]{10}"
												inputmode="numeric"
												oninput="this.value=this.value.replace(/[^0-9]/g,'')"
												placeholder="Enter 10 digit phone number">
											<small class="text-danger error-phone"></small>
										</div>

										<!-- Address -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Address</label>
											<input type="text" name="address" value="{{ old('address', $student->address) }}" class="form-control">
											<small class="text-danger error-address"></small>
										</div>

										<!-- Class -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Class</label>
											<input type="text" name="course" value="{{ old('course', $student->course) }}" class="form-control" placeholder="e.g. 6th, 7th, 10th, 12th">
											<small class="text-danger error-course"></small>
										</div>

										<!-- Section -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Section</label>
											<input type="text" name="section" value="{{ old('section', $student->section) }}" class="form-control" placeholder="e.g. A, B, C">
											<small class="text-danger error-section"></small>
										</div>

										<!-- Roll Number -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Roll Number</label>
											<input type="text" name="roll_number" value="{{ old('roll_number', $student->roll_number) }}" class="form-control">
											<small class="text-danger error-roll_number"></small>
										</div>

										 

									</div>

									<div class="mt-3">
										<button type="submit" class="btn btn-primary">
											Update Student
										</button>
										<button type="reset" class="btn btn-light">
											Reset
										</button>
									</div>

								</form>

							</div>

						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
			function togglePassword(fieldId, el) {
				let input = document.getElementById(fieldId);
				let icon = el.querySelector("i");

				if (input.type === "password") {
					input.type = "text";
					icon.classList.remove("fa-eye");
					icon.classList.add("fa-eye-slash");
				} else {
					input.type = "password";
					icon.classList.remove("fa-eye-slash");
					icon.classList.add("fa-eye");
				}
			}
		</script>
		<script>
			document.getElementById("studentForm").addEventListener("submit", function (e) {
				e.preventDefault();

				let name = document.querySelector("[name='name']").value.trim();
				let email = document.querySelector("[name='email']").value.trim();
				let phone = document.querySelector("[name='phone']").value.trim();
				let course = document.querySelector("[name='course']").value.trim();
				let section = document.querySelector("[name='section']").value.trim();
				let rollNumber = document.querySelector("[name='roll_number']").value.trim();
				let password = document.querySelector("[name='password']").value;
				let confirmPassword = document.querySelector("[name='password_confirmation']").value;

				let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				let phonePattern = /^[0-9]{10,15}$/;

				document.querySelectorAll("small.text-danger").forEach(el => el.innerText = "");

				let isValid = true;

				if (name === "") {
					document.querySelector(".error-name").innerText = "Name is required";
					isValid = false;
				}

				if (!emailPattern.test(email)) {
					document.querySelector(".error-email").innerText = "Enter valid email";
					isValid = false;
				}

				if (!phonePattern.test(phone)) {
					document.querySelector(".error-phone").innerText = "Enter valid 10-15 digit phone number";
					isValid = false;
				}

				if (course === "") {
					document.querySelector(".error-course").innerText = "Class is required";
					isValid = false;
				}

				if (section === "") {
					document.querySelector(".error-section").innerText = "Section is required";
					isValid = false;
				}

				if (rollNumber === "") {
					document.querySelector(".error-roll_number").innerText = "Roll number is required";
					isValid = false;
				}

				if (password !== "" || confirmPassword !== "") {
					if (password.length < 6) {
						document.querySelector(".error-password").innerText = "Password must be at least 6 characters";
						isValid = false;
					}
					if (password !== confirmPassword) {
						document.querySelector(".error-confirm").innerText = "Passwords do not match";
						isValid = false;
					}
				}

				if (isValid) {
					this.submit();
				}
			});
		</script>
		
		@include('owner.components.footer')