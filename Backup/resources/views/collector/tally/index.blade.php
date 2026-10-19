	@include('collector.components.header')
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
		@include('collector.components.navbar')
		@include('collector.components.sidebar')

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<!-- Dashboard Content Area -->
						<div class="dashboard-content">
							<!-- Top Stats -->
							<div class="row mb-4">
								<div class="col-xl-4 col-md-6">
									<div class="card bg-primary text-white">
										<div class="card-body">
											<h6>Total Companies</h6>
											<h2>{{ count($companies) }}</h2>
										</div>
									</div>
								</div>

								<div class="col-xl-4 col-md-6">
									<div class="card bg-primary text-white">
										<div class="card-body">
											<h6>Tally Status</h6>
											@if($tallyConnected)
												<h2>
													<i class="fas fa-check-circle"></i>
													Connected
												</h2>
											@else
												<h2>
													<i class="fas fa-times-circle"></i>
													Disconnected
												</h2>
											@endif
										</div>
									</div>
								</div>

								<div class="col-xl-4 col-md-6">
									<div class="card bg-primary text-white">
										<div class="card-body">
											<h6 id="lastSyncTime">Last Sync</h6>
											<h5>
												{{ session('last_sync') 
													? \Carbon\Carbon::parse(session('last_sync'))->format('d M Y H:i:s')
													: 'Never Synced'
												}}
											</h5>
										</div>
									</div>
								</div>
							</div>

							<!-- Action Buttons -->
							<div class="d-flex justify-content-between align-items-center mb-4">

								<h3 class="mb-0">
									<i class="fas fa-building text-primary"></i>
									Tally Companies
								</h3>

								<button type="button" id="syncTallyBtn" class="btn btn-primary">
									<i class="fas fa-sync-alt me-2"></i>
									Sync Data From Tally
								</button>

							</div>

							<div id="syncMessage"></div>

							<!-- Company Cards -->
							<div class="row">

								@forelse($companies as $company)

									<div class="col-xl-6 col-lg-6 mb-4">

										<div class="card shadow-sm border-0 h-100">

											<div class="card-body">

												<div class="d-flex align-items-center">

													<div class="me-3">
														<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
															style="width:60px;height:60px;">
															<i class="fas fa-building fa-lg"></i>
														</div>
													</div>

													<div>
														<h5 class="mb-1">
															{{ $company['name'] }}
														</h5>

														<small class="text-success">
															<i class="fas fa-circle"></i>
															Active
														</small>
													</div>

												</div>

											</div>

											<div class="card-footer bg-light">

												<a href="{{ route('collector.tally.company.ledgers', urlencode($company['name'])) }}"
												class="btn btn-sm btn-primary">
													<i class="fas fa-book"></i>
													Ledgers
												</a>

											</div>

										</div>

									</div>

								@empty

									<div class="col-12">
										<div class="alert alert-warning text-center">
											No Companies Found In Tally
										</div>
									</div>

								@endforelse

							</div>

						</div>
						 <script>
							$(document).ready(function () {

								$('#syncTallyBtn').click(function () {

									let btn = $(this);

									btn.prop('disabled', true);
									btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Syncing...');

									$('#syncMessage').html('');

									$.ajax({
										url: "{{ route('collector.tally.sync-all') }}",
										type: "POST",
										data: {
											_token: "{{ csrf_token() }}"
										},
										success: function (response) {

											$('#syncMessage').html(`
												<div class="alert alert-success alert-dismissible fade show mt-3">
													${response.message || 'Data synced successfully.'}
													<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
												</div>
											`);

											// Last Sync Date Update
											if(response.last_sync){
												$('#lastSyncTime').text(response.last_sync);
											}
										},
										error: function (xhr) {

											let message = 'Failed to sync data from Tally.';

											if(xhr.responseJSON?.message){
												message = xhr.responseJSON.message;
											}

											$('#syncMessage').html(`
												<div class="alert alert-danger alert-dismissible fade show mt-3">
													${message}
													<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
												</div>
											`);

										},
										complete: function () {

											btn.prop('disabled', false);
											btn.html('<i class="fas fa-sync-alt me-2"></i> Sync Data From Tally');

											// Auto hide after 5 sec
											setTimeout(function () {
												$('#syncMessage .alert').fadeOut('slow', function () {
													$(this).remove();
												});
											}, 5000);

										}
									});

								});

							});
						</script>
					</div>
                   
				</div>
			</div>
		</div>

		<style>
			.bg-primary{
				background-color: #E9E2F8 !important;
			}
		</style>
		
		@include('collector.components.footer')