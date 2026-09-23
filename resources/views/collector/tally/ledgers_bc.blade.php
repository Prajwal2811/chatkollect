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
                <!-- row -->
                <div class="row">
                    <div class="col-12">
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
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title">Ledger List</h4>
                                <span class="badge bg-primary">
                                    {{ count($ledgers) }}
                                </span>
                            </div>
                            

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example" class="display" style="min-width: 845px">
                                        <thead>
											<tr>
												<th>Sr.No</th>
												<th>Ledger Name</th>
                                                <th>Action</th>
											</tr>
										</thead>
                                        <tbody>
                                            @forelse($ledgers as $index => $ledger)

                                                @if(!empty(trim($ledger['name'] ?? '')))
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>

                                                    <td>{{ $ledger['name'] }}</td>

                                                    <td>
                                                        <a href="{{ route('owner.tally.ledger.invoices', [
                                                            'company' => urlencode($company),
                                                            'ledger' => urlencode($ledger['name'])
                                                        ]) }}"
                                                        class="btn btn-primary btn-sm">
                                                            <i class="fas fa-eye"></i> Invoices
                                                        </a>

                                                        <a href="{{ route('owner.tally.ledger.receipts', [
                                                            'company' => urlencode($company),
                                                            'ledger' => urlencode($ledger['name'])
                                                        ]) }}"
                                                        class="btn btn-success btn-sm">
                                                            <i class="fas fa-money-bill"></i> Receipts
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endif

                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        No Ledgers Found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Sr.No</th>
												<th>Ledger Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
				</div>
            </div>
        </div>

       
		@include('owner.components.footer')