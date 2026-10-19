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
                            <form id="voucherMappingForm"
                                action="{{ route('owner.voucher-mappings.save') }}"
                                method="POST">
                                @csrf

                                <input type="hidden" name="company" value=" ">
                                <input type="hidden" name="Purchase" value=" ">

                                <div class="card">
                                    <div class="col-10 mx-auto mt-4">
                                        @if (session('success'))
                                            <div  id="successAlert" class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                                <button class="btn-close" data-bs-dismiss="alert"></button> 
                                                {{ session('success') }}
                                            </div>
                                        @endif
                                    </div>
                                    <script>
                                        setTimeout(function () {
                                            let alert = document.getElementById('successAlert');
                                            if (alert) {
                                                let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                                bsAlert.close();
                                            }
                                        }, 3000); // 3 seconds
                                    </script>
                                   <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4>Voucher Mapping</h4>

                                        <span class="badge bg-primary fs-6">
                                            Company : {{ $company }}
                                        </span>
                                        <input type="text" name="company" value="{{ $company }}" hidden>
                                    </div>

                                    <div class="card-body">

                                        @php
                                            $mappingOptions = [
                                                'Sales',
                                                'Purchase',
                                                'Other than Sales/Purchase'
                                            ];
 
                                        @endphp
                                        <div class="table-responsive">
                                            <table id="example111" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th width="80">Sr.No</th>
                                                        <th>Voucher Type</th>
                                                        <th>Map To</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @forelse($voucherTypes as $index => $voucher)

                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>

                                                            <td>{{ $voucher['name'] }}</td>

                                                            <td>
                                                                @php
                                                                    $selectedValue = $savedMappings[$voucher['name']] ?? 'Other than Sales/Purchase';
                                                                @endphp

                                                                <div class="d-flex flex-wrap gap-3">

                                                                    @foreach($mappingOptions as $option)

                                                                        <div class="form-check">
                                                                            <input
                                                                                class="form-check-input"
                                                                                type="radio"
                                                                                name="mapping[{{ $voucher['name'] }}]"
                                                                                value="{{ $option }}"
                                                                                {{ $selectedValue == $option ? 'checked' : '' }}
                                                                            >

                                                                            <label class="form-check-label">
                                                                                {{ $option }}
                                                                            </label>
                                                                        </div>

                                                                    @endforeach

                                                                </div>
                                                            </td>
                                                        </tr>

                                                    @empty

                                                        <tr>
                                                            <td colspan="3" class="text-center">
                                                                No Voucher Types Found
                                                            </td>
                                                        </tr>

                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="card-footer text-end">
                                        <button type="submit" id="saveBtn" class="btn btn-primary">
                                            <span id="btnText">Save Mapping</span>
                                            <span id="btnLoader" class="d-none">
                                                <span class="spinner-border spinner-border-sm me-1"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </div>

                            </form>

                            <script>
                                document.getElementById('voucherMappingForm').addEventListener('submit', function() {

                                    let btn = document.getElementById('saveBtn');

                                    // Button Disable
                                    btn.disabled = true;

                                    // Loading Show
                                    document.getElementById('btnText').classList.add('d-none');
                                    document.getElementById('btnLoader').classList.remove('d-none');

                                });
                            </script>
						</div>
					 
					</div>
                   
				</div>
			</div>
		</div>

	 
		
		@include('owner.components.footer')