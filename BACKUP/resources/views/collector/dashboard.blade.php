	@include('accountant.components.header')
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
	@include('accountant.components.navbar')
	@include('accountant.components.sidebar')

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<!-- Dashboard Content Area -->
						<div class="dashboard-content">
							<div class="d-flex justify-content-end mb-3">
								<button type="button" id="syncTallyBtn" class="btn btn-primary">
									<i class="fas fa-sync-alt me-2"></i> Sync Data From Tally
								</button>
							</div>
							<div id="syncMessage"></div>


							<div class="row">
								<div class="col-12">
									<div class="dashboard-placeholder text-center">
										<p>Dashboard modules will be integrated here (charts, stats, reports, etc.)</p>
									</div>
								</div>
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
										url: "{{ route('admin.tally.sync') }}",
										type: "GET",
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
		@include('accountant.components.footer')