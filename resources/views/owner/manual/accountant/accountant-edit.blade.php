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

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">

							<div class="card-header">
								<h4 class="card-title">Edit Accountant</h4>
							</div>

							<div class="card-body">

								<form action="{{ route('owner.accountants.updateAccountant', $accountant->id) }}"
									method="POST">

									@csrf

									<div class="row">

										<div class="col-md-6 mb-3">
											<label class="form-label">Name</label>
											<input type="text"
												name="name"
												value="{{ $accountant->name }}"
												class="form-control">
											<small class="text-danger error-name"></small>
										</div>

										<div class="col-md-6 mb-3">
											<label class="form-label">Email</label>
											<input type="email"
												name="email"
												value="{{ $accountant->email }}"
												class="form-control">
											<small class="text-danger error-email"></small>
										</div>

										<div class="col-md-6 mb-3">
											<label class="form-label">Phone</label>
											<input type="text"
												name="phone"
												value="{{ $accountant->phone }}"
												class="form-control"
												maxlength="10"
												oninput="this.value=this.value.replace(/[^0-9]/g,'')">
											<small class="text-danger error-phone"></small>
										</div>

										<div class="col-md-6 mb-3">
											<label class="form-label">Address</label>
											<input type="text"
												name="address"
												value="{{ $accountant->address }}"
												class="form-control">
											<small class="text-danger error-address"></small>
										</div>

									</div>

									<button type="submit"
											id="updateBtn"
											class="btn btn-primary">
										Update Accountant
									</button>

								</form>

							</div>

						</div>
					</div>
				</div>
			</div>
		</div>

		 
		
		@include('owner.components.footer')