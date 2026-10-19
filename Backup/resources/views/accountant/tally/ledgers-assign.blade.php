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
                            @php
                                $collectors = \App\Models\Collector::where('status', 'active')->get();
                            @endphp
                                <form action="{{ route('accountant.assign.collectors') }}" method="POST">
                                    @csrf

                                    <input type="hidden" name="company" value="{{ $company }}">

                                    <div class="card border shadow-sm mb-4">
                                        <div class="card-body">

                                            <div class="row align-items-end">

                                                <div class="col-lg-5 col-md-6 mb-3">
                                                    <label class="form-label fw-bold">
                                                        <i class="fas fa-user me-1"></i>
                                                        Select Collector
                                                    </label>

                                                    <select name="collector_id" class="form-select" required>
                                                        <option value="">-- Select Collector --</option>

                                                        @foreach($collectors as $collector)
                                                            <option value="{{ $collector->id }}">
                                                                {{ $collector->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-lg-4 col-md-6 mb-3">
                                                    <div class="text-muted small">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Select one or more ledgers using the checkboxes below.
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 text-lg-end mb-3">
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Assign Selected
                                                    </button>
                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                
                                <ul class="nav nav-pills mb-4" id="ledgerTabs" role="tablist">
                                    <li class="nav-item me-2">
                                        <button class="nav-link active" type="button"
                                            data-bs-toggle="pill"
                                            data-bs-target="#debtors-tab">
                                            Sundry Debtors
                                            ({{ collect($ledgers)->where('under', 'Sundry Debtors')->count() }})
                                        </button>
                                    </li>

                                    <li class="nav-item me-2">
                                        <button class="nav-link" type="button"
                                            data-bs-toggle="pill"
                                            data-bs-target="#creditors-tab">
                                            Sundry Creditors
                                            ({{ collect($ledgers)->where('under', 'Sundry Creditors')->count() }})
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <!-- Debtors -->
                                    <div class="tab-pane fade show active" id="debtors-tab">
                                        <div class="table-responsive">
                                            <table id="example11" class="display" style="min-width: 845px">
                                                <thead>
                                                    <tr>
                                                        <th width="50">
                                                            <input type="checkbox" id="selectAllDebtors">
                                                        </th>
                                                        <th>Sr.No</th>
                                                        <th>Ledger Name</th>
                                                        <th>Collector</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody>

                                                    @php $i = 1; @endphp

                                                    @forelse($ledgers as $ledger)

                                                        @if(($ledger['under'] ?? '') == 'Sundry Debtors')

                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox"
                                                                        class="ledger-checkbox-debtors"
                                                                        name="ledgers[]"
                                                                        value="{{ json_encode([
                                                                            'name' => $ledger['name'],
                                                                            'under' => $ledger['under']
                                                                        ]) }}"
                                                                        {{ $assignedLedgers->has($ledger['name']) ? 'checked' : '' }}>
                                                                </td>
                                                                <td>{{ $i++ }}</td>

                                                                <td>{{ $ledger['name'] }}</td>
                                                                <td>
                                                                    {{ optional($assignedLedgers->get($ledger['name']))->collector_name ?? '-' }}
                                                                </td>

                                                                <td>
                                                                    <a href="{{ route(
                                                                        'accountant.tally.ledger.vouchers',
                                                                        [
                                                                            'company' => urlencode($company),
                                                                            'ledger'  => urlencode($ledger['name']),
                                                                            'under'   => urlencode($ledger['under'] ?? '')
                                                                        ]
                                                                    ) }}"
                                                                    class="btn btn-primary btn-sm">
                                                                        View Vouchers
                                                                    </a>
                                                                </td>
                                                            </tr>

                                                        @endif

                                                    @empty

                                                        <tr>
                                                            <td colspan="3" class="text-center">
                                                                No Debtors Found
                                                            </td>
                                                        </tr>

                                                    @endforelse

                                                </tbody>
                                            </table>
                                        </div>

                                    </div>

                                    <!-- Creditors -->
                                    <div class="tab-pane fade" id="creditors-tab">

                                        <div class="table-responsive">
                                            <table id="example12" class="display" style="min-width: 845px">
                                                <thead>
                                                   <tr>
                                                        <th width="50">
                                                            <input type="checkbox" id="selectAllCreditors">
                                                        </th>
                                                        <th>Sr.No</th>
                                                        <th>Ledger Name</th>
                                                        <th>Collector</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody>

                                                    @php $j = 1; @endphp

                                                    @forelse($ledgers as $ledger)

                                                        @if(($ledger['under'] ?? '') == 'Sundry Creditors')

                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox"
                                                                        class="ledger-checkbox-creditors"
                                                                        name="ledgers[]"
                                                                        value="{{ json_encode([
                                                                            'name' => $ledger['name'],
                                                                            'under' => $ledger['under']
                                                                        ]) }}"
                                                                        {{ $assignedLedgers->has($ledger['name']) ? 'checked' : '' }}>
                                                                </td>
                                                                <td>{{ $j++ }}</td>

                                                                <td>{{ $ledger['name'] }}</td>
                                                                <td>
                                                                    {{ optional($assignedLedgers->get($ledger['name']))->collector_name ?? '-' }}
                                                                </td>

                                                                <td>
                                                                    <a href="{{ route(
                                                                        'accountant.tally.ledger.vouchers',
                                                                        [
                                                                            'company' => urlencode($company),
                                                                            'ledger'  => urlencode($ledger['name']),
                                                                            'under'   => urlencode($ledger['under'] ?? '')
                                                                        ]
                                                                    ) }}"
                                                                    class="btn btn-primary btn-sm">
                                                                        View Vouchers
                                                                    </a>
                                                                </td>
                                                            </tr>

                                                        @endif

                                                    @empty

                                                        <tr>
                                                            <td colspan="3" class="text-center">
                                                                No Creditors Found
                                                            </td>
                                                        </tr>

                                                    @endforelse

                                                </tbody>
                                            </table>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
				</div>
            </div>
        </div>

        <script>
            $('#selectAllDebtors').on('change', function () {
                $('.ledger-checkbox-debtors').prop('checked', this.checked);
            });

            $('#selectAllCreditors').on('change', function () {
                $('.ledger-checkbox-creditors').prop('checked', this.checked);
            });
        </script>
       
		@include('accountant.components.footer')