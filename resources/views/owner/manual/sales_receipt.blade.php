@include('owner.components.header')
    <div id="main-wrapper">
		<div class="nav-header">
            <a href="#" class="brand-logo">
				<svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
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
			// Dummy student data (remove once real $student is passed from controller)
			$student = (object) [
				'id' => 1,
				'name' => 'Aarav Sharma',
				'email' => 'aarav.sharma@example.com',
				'phone' => '9876543210',
			];

			// Dummy opening balance (remove once real value is passed from controller)
			$openingBalance = 1000.00;

			// Dummy receipts data (remove once real $receipts is passed from controller)
			$receipts = collect([
				(object) [
					'id' => 1,
					'receipt_number' => 'RCPT-1001',
					'amount' => 1150.00,
					'created_at' => \Carbon\Carbon::now()->subDays(1),
				],
                (object) [
                    'id' => 2,
                    'receipt_number' => 'RCPT-1002',
                    'amount' => 2300.00,
                    'created_at' => \Carbon\Carbon::now()->subDays(3),
                ],
                (object) [
                    'id' => 3,
                    'receipt_number' => 'RCPT-1003',
                    'amount' => 3450.00,
                    'created_at' => \Carbon\Carbon::now()->subDays(5),
                ],
                (object) [
                    'id' => 4,
                    'receipt_number' => 'RCPT-1004',
                    'amount' => 4600.00,
                    'created_at' => \Carbon\Carbon::now()->subDays(7),
                ],
                (object) [
                    'id' => 5,
                    'receipt_number' => 'RCPT-1005',
                    'amount' => 5750.00,
                    'created_at' => \Carbon\Carbon::now()->subDays(9),
                ],
                (object) [
                    'id' => 6,
                    'receipt_number' => 'RCPT-1006',
                    'amount' => 6900.00,
                    'created_at' => \Carbon\Carbon::now()->subDays(11),
                ],
                (object) [
                    'id' => 7,
                    'receipt_number' => 'RCPT-1007',
                    'amount' => 8050.00,
                    'created_at' => \Carbon\Carbon::now()->subDays(13),
                ],
                 
			]);

			// Dummy sales data (remove once real $sales is passed from controller)
			$sales = collect([
				(object) [
					'id' => 1,
					'sale_number' => 'SALE-2001',
					'amount' => 2150.00,
					'created_at' => \Carbon\Carbon::now()->subDays(2),
				],
				(object) [
					'id' => 2,
					'sale_number' => 'SALE-2002',
					'amount' => 3300.00,
					'created_at' => \Carbon\Carbon::now()->subDays(4),
				],
				(object) [
					'id' => 3,
					'sale_number' => 'SALE-2003',
					'amount' => 4450.00,
					'created_at' => \Carbon\Carbon::now()->subDays(6),
				],
				(object) [
					'id' => 4,
					'sale_number' => 'SALE-2004',
					'amount' => 5600.00,
					'created_at' => \Carbon\Carbon::now()->subDays(8),
				],
				(object) [
					'id' => 5,
					'sale_number' => 'SALE-2005',
					'amount' => 6750.00,
					'created_at' => \Carbon\Carbon::now()->subDays(10),
				],
				(object) [
					'id' => 6,
					'sale_number' => 'SALE-2006',
					'amount' => 7900.00,
					'created_at' => \Carbon\Carbon::now()->subDays(12),
				],
				(object) [
					'id' => 7,
					'sale_number' => 'SALE-2007',
					'amount' => 9050.00,
					'created_at' => \Carbon\Carbon::now()->subDays(14),
				],
			]);

			// Totals for summary cards
			$totalReceived = $receipts->sum('amount');
			$totalSales = $sales->sum('amount');
			// Outstanding = Opening Balance + Total Sales - Total Received
			$outstandingAmount = $openingBalance + $totalSales - $totalReceived;
		@endphp

		<div class="content-body default-height">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="col-10 mx-auto mt-4">
                                @if (session('success'))
                                    <div id="successAlert" class="alert alert-success alert-dismissible fade show text-center" role="alert">
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
                                }, 3000);
                            </script>

                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">{{ $student->name }} — Sales & Receipts</h4>
                            </div>

                            <div class="card-body">
                                <!-- Student Info -->
                                <div class="row mb-4">
                                    <div class="col-md-4"><strong>Name:</strong> {{ $student->name }}</div>
                                    <div class="col-md-4"><strong>Email:</strong> {{ $student->email }}</div>
                                    <div class="col-md-4"><strong>Phone:</strong> {{ $student->phone }}</div>
                                </div>

                                <!-- Summary Cards -->
                              <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1" style="font-size: 1.2rem;">Opening Balance</h6>
                                            <h3 class="mb-0 text-primary fw-bold" style="font-size: 2rem;">₹{{ number_format($openingBalance, 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1" style="font-size: 1.2rem;">Total Sales</h6>
                                            <h3 class="mb-0 text-primary fw-bold" style="font-size: 2rem;">₹{{ number_format($totalSales, 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1" style="font-size: 1.2rem;">Total Received</h6>
                                            <h3 class="mb-0 text-primary fw-bold" style="font-size: 2rem;">₹{{ number_format($totalReceived, 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1" style="font-size: 1.2rem;">Outstanding Amount</h6>
                                            <h3 class="mb-0 fw-bold {{ $outstandingAmount < 0 ? 'text-danger' : 'text-success' }}" style="font-size: 2rem;">₹{{ number_format($outstandingAmount, 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                <!-- Sales / Receipts Tabs -->
                                <ul class="nav nav-pills mb-4" id="salesReceiptsTabs" role="tablist">
                                    <li class="nav-item me-2">
                                        <button class="nav-link active" id="sales-tab" data-bs-toggle="pill" data-bs-target="#salesTabPane" type="button" role="tab" aria-controls="salesTabPane" aria-selected="true">
                                            Sales ({{ $sales->count() }})
                                        </button>
                                    </li>
                                    <li class="nav-item me-2">
                                        <button class="nav-link" id="receipts-tab" data-bs-toggle="pill" data-bs-target="#receiptsTabPane" type="button" role="tab" aria-controls="receiptsTabPane" aria-selected="false">
                                            Receipts ({{ $receipts->count() }})
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="salesReceiptsTabContent">
                                    <!-- ===================== SALES TAB ===================== -->
                                    <div class="tab-pane fade show active" id="salesTabPane" role="tabpanel" aria-labelledby="sales-tab">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Sales</h5>
                                            <div class="d-flex gap-2">
                                                <a href="#" class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-file-export"></i> Export
                                                </a>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importSaleModal">
                                                    <i class="fas fa-file-import"></i> Import
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                                                    <i class="fas fa-plus"></i> Add Sale
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table id="example" class="display" style="min-width: 845px">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Sale No.</th>
                                                        <th>Amount</th>
                                                        <th>Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($sales as $sale)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $sale->sale_number }}</td>
                                                            <td>₹{{ number_format($sale->amount, 2) }}</td>
                                                            <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button"
                                                                        class="btn btn-outline-primary btn-sm edit-sale-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editSaleModal"
                                                                        data-id="{{ $sale->id }}"
                                                                        data-sale_number="{{ $sale->sale_number }}"
                                                                        data-amount="{{ $sale->amount }}"
                                                                        data-date="{{ $sale->created_at->format('Y-m-d') }}"
                                                                        title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-outline-danger btn-sm delete-sale-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteSaleModal"
                                                                        data-id="{{ $sale->id }}"
                                                                        data-name="{{ $sale->sale_number }}"
                                                                        title="Delete">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="5" class="text-center">No sales found</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- ===================== RECEIPTS TAB ===================== -->
                                    <div class="tab-pane fade" id="receiptsTabPane" role="tabpanel" aria-labelledby="receipts-tab">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Receipts</h5>
                                            <div class="d-flex gap-2">
                                                <a href="#" class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-file-export"></i> Export
                                                </a>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importReceiptModal">
                                                    <i class="fas fa-file-import"></i> Import
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReceiptModal">
                                                    <i class="fas fa-plus"></i> Add Receipt
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table id="example11" class="display" style="min-width: 845px">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Receipt No.</th>
                                                        <th>Amount</th>
                                                        <th>Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($receipts as $receipt)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $receipt->receipt_number }}</td>
                                                            <td>₹{{ number_format($receipt->amount, 2) }}</td>
                                                            <td>{{ $receipt->created_at->format('d-m-Y') }}</td>
                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button"
                                                                        class="btn btn-outline-primary btn-sm edit-receipt-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editReceiptModal"
                                                                        data-id="{{ $receipt->id }}"
                                                                        data-receipt_number="{{ $receipt->receipt_number }}"
                                                                        data-amount="{{ $receipt->amount }}"
                                                                        data-date="{{ $receipt->created_at->format('Y-m-d') }}"
                                                                        title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-outline-danger btn-sm delete-receipt-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteReceiptModal"
                                                                        data-id="{{ $receipt->id }}"
                                                                        data-name="{{ $receipt->receipt_number }}"
                                                                        title="Delete">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="5" class="text-center">No receipts found</td></tr>
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

        <!-- Add Sale Modal -->
        <div class="modal fade" id="addSaleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="#">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Add Sale</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Sale Number</label>
                                <input type="text" name="sale_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Sale</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Sale Modal -->
        <div class="modal fade" id="editSaleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="#" id="editSaleForm">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Sale</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit_sale_id">
                            <div class="mb-3">
                                <label class="form-label">Sale Number</label>
                                <input type="text" name="sale_number" id="edit_sale_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" name="amount" id="edit_sale_amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" id="edit_sale_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Sale</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Sale Modal -->
        <div class="modal fade" id="deleteSaleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="#" id="deleteSaleForm">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                            <h5 class="modal-title text-danger">Delete Sale</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger d-flex align-items-start gap-2 mb-0">
                                <i class="fas fa-exclamation-triangle mt-1"></i>
                                <div>
                                    Are you sure you want to delete sale
                                    <strong id="delete_sale_name"></strong>?
                                    This action <strong>cannot be undone</strong>.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import Sale Modal -->
        <div class="modal fade" id="importSaleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Import Sales</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning d-flex align-items-start gap-2">
                                <i class="fas fa-exclamation-triangle mt-1"></i>
                                <div>
                                    <strong>Warning:</strong> Importing a new file will
                                    <strong>permanently delete all existing sales data</strong>
                                    for this student before the new data is imported.
                                    This action <strong>cannot be undone</strong>.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select File (.csv, .xlsx)</label>
                                <input type="file" name="import_file" class="form-control" accept=".csv,.xlsx" required>
                            </div>
                            <small class="text-muted">
                                Upload a CSV or Excel file with columns: sale_number, amount, date
                            </small>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="confirmSaleImport" required>
                                <label class="form-check-label" for="confirmSaleImport">
                                    I understand the old sales data will be deleted.
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-file-import"></i> Import & Replace
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Receipt Modal -->
        <div class="modal fade" id="addReceiptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="#">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Add Receipt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Receipt Number</label>
                                <input type="text" name="receipt_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Receipt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Receipt Modal -->
        <div class="modal fade" id="editReceiptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="#" id="editReceiptForm">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Receipt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit_receipt_id">
                            <div class="mb-3">
                                <label class="form-label">Receipt Number</label>
                                <input type="text" name="receipt_number" id="edit_receipt_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" name="amount" id="edit_receipt_amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" id="edit_receipt_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Receipt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Receipt Modal -->
        <div class="modal fade" id="deleteReceiptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="#" id="deleteReceiptForm">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                            <h5 class="modal-title text-danger">Delete Receipt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger d-flex align-items-start gap-2 mb-0">
                                <i class="fas fa-exclamation-triangle mt-1"></i>
                                <div>
                                    Are you sure you want to delete receipt
                                    <strong id="delete_receipt_name"></strong>?
                                    This action <strong>cannot be undone</strong>.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import Receipt Modal -->
        <div class="modal fade" id="importReceiptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Import Receipts</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning d-flex align-items-start gap-2">
                                <i class="fas fa-exclamation-triangle mt-1"></i>
                                <div>
                                    <strong>Warning:</strong> Importing a new file will
                                    <strong>permanently delete all existing receipts data</strong>
                                    for this student before the new data is imported.
                                    This action <strong>cannot be undone</strong>.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select File (.csv, .xlsx)</label>
                                <input type="file" name="import_file" class="form-control" accept=".csv,.xlsx" required>
                            </div>
                            <small class="text-muted">
                                Upload a CSV or Excel file with columns: receipt_number, amount, date
                            </small>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="confirmReceiptImport" required>
                                <label class="form-check-label" for="confirmReceiptImport">
                                    I understand the old receipts data will be deleted.
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-file-import"></i> Import & Replace
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // Fill Edit Sale modal with row data
            document.querySelectorAll('.edit-sale-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('edit_sale_id').value = this.dataset.id;
                    document.getElementById('edit_sale_number').value = this.dataset.sale_number;
                    document.getElementById('edit_sale_amount').value = this.dataset.amount;
                    document.getElementById('edit_sale_date').value = this.dataset.date;
                    document.getElementById('editSaleForm').action = "{{ url('owner/sales') }}/" + this.dataset.id;
                });
            });

            // Fill Delete Sale modal with row data
            document.querySelectorAll('.delete-sale-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('delete_sale_name').textContent = this.dataset.name;
                    document.getElementById('deleteSaleForm').action = "{{ url('owner/sales') }}/" + this.dataset.id;
                });
            });

            // Fill Edit Receipt modal with row data
            document.querySelectorAll('.edit-receipt-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('edit_receipt_id').value = this.dataset.id;
                    document.getElementById('edit_receipt_number').value = this.dataset.receipt_number;
                    document.getElementById('edit_receipt_amount').value = this.dataset.amount;
                    document.getElementById('edit_receipt_date').value = this.dataset.date;
                    document.getElementById('editReceiptForm').action = "{{ url('owner/receipts') }}/" + this.dataset.id;
                });
            });

            // Fill Delete Receipt modal with row data
            document.querySelectorAll('.delete-receipt-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('delete_receipt_name').textContent = this.dataset.name;
                    document.getElementById('deleteReceiptForm').action = "{{ url('owner/receipts') }}/" + this.dataset.id;
                });
            });
        </script>

@include('owner.components.footer')