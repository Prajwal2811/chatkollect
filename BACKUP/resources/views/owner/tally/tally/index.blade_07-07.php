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

<div class="modal fade" id="yearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Select Financial Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                @php
$currentYear = date('Y');
$currentMonth = date('n');
$currentFYStart = ($currentMonth >= 4) ? $currentYear : $currentYear - 1;
@endphp

<div class="mb-3">
    <label class="form-label">Financial Year</label>

    <select class="form-control" id="financialYear">
        <option value="">Select Financial Year</option>

        @for($i = 4; $i >= 0; $i--)
            @php
                $start = $currentFYStart - $i;
                $end = substr($start + 1, -2);
                $fy = $start.'-'.$end;
            @endphp

            <option value="{{ $fy }}">
                {{ $fy }}
            </option>
        @endfor

    </select>
</div>

            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button class="btn btn-primary"
                        id="startSync">
                    Sync
                </button>

            </div>

        </div>
    </div>
</div>

							</div>

							<div id="syncMessage"></div>

							<!-- Company Cards -->
							<div class="row">

								@forelse($companies as $company)

									<div class="col-xl-4 col-lg-6 mb-4">

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

												{{-- <a href="{{ route('owner.tally.company.details', urlencode($company['name'])) }}" class="btn btn-sm btn-primary">
													<i class="fas fa-eye"></i>
													View Details
												</a> --}}

												<a href="{{ route('owner.tally.company.ledgers', urlencode($company['name'])) }}" class="btn btn-sm btn-success">
													<i class="fas fa-file-invoice"></i>
													Ledgers
												</a>


												<a href="{{ route('owner.tally.voucher.mappings', urlencode($company['name'])) }}" class="btn btn-sm btn-dark">
													<i class="fas fa-file-invoice"></i>
													Voucher Mapping
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
							$('#syncTallyBtn').click(function () {

								$('#yearModal').modal('show');

							});
							$('#startSync').click(function () {

    let year = $('#financialYear').val();

    if (year == '') {
        alert('Please select Financial Year');
        return;
    }

    let btn = $(this);

    btn.prop('disabled', true);

    btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Syncing...');

    $.ajax({

        url: "{{ route('owner.tally.sync-all') }}",

        type: "POST",

        data: {
            _token: "{{ csrf_token() }}",
            year: year
        },

        success: function (response) {

            $('#yearModal').modal('hide');

            $('#syncMessage').html(`
                <div class="alert alert-success">
                    ${response.message}
                </div>
            `);

        },

        error: function (xhr) {

            $('#syncMessage').html(`
                <div class="alert alert-danger">
                    ${xhr.responseJSON.message}
                </div>
            `);

        },

        complete: function () {

            btn.prop('disabled', false);

            btn.html('Sync');

        }

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
		
		@include('owner.components.footer')