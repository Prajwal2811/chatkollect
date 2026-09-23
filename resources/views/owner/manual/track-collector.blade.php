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
			// Dummy data (remove this block once real $students is passed from the controller)
			$students = collect([
				(object) [
					'id' => 1,
					'name' => 'Aarav Sharma',
					'roll_number' => 'STU001',
					'course' => '10th',
					'collector' => 'Rahul Sharma',
					'balance' => 4500,
					'status' => 'pending',
				],
				(object) [
					'id' => 2,
					'name' => 'Priya Verma',
					'roll_number' => 'STU002',
					'course' => '9th',
					'collector' => 'Sneha Patil',
					'balance' => 0,
					'status' => 'paid',
				],
				(object) [
					'id' => 3,
					'name' => 'Rohan Deshmukh',
					'roll_number' => 'STU003',
					'course' => '12th',
					'collector' => 'Rahul Sharma',
					'balance' => 1200,
					'status' => 'partial',
				],
				(object) [
					'id' => 4,
					'name' => 'Sneha Patil',
					'roll_number' => 'STU004',
					'course' => '8th',
					'collector' => 'Vikram Rao',
					'balance' => 6800,
					'status' => 'pending',
				],
				(object) [
					'id' => 5,
					'name' => 'Karan Mehta',
					'roll_number' => 'STU005',
					'course' => '11th',
					'collector' => 'Sneha Patil',
					'balance' => 0,
					'status' => 'paid',
				],
				(object) [
					'id' => 6,
					'name' => 'Ishita Nair',
					'roll_number' => 'STU006',
					'course' => '10th',
					'collector' => 'Vikram Rao',
					'balance' => 2300,
					'status' => 'partial',
				],
				(object) [
					'id' => 7,
					'name' => 'Aditya Pawar',
					'roll_number' => 'STU007',
					'course' => '9th',
					'collector' => 'Rahul Sharma',
					'balance' => 5000,
					'status' => 'pending',
				],
			]);

			// Unique collector list, used to build the filter dropdown
			$collectors = $students->pluck('collector')->unique()->values();
		@endphp

		<div class="content-body default-height">
            <div class="container-fluid">
                <!-- row -->
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
                                @if (session('error'))
                                    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                                        <button class="btn-close" data-bs-dismiss="alert"></button>
                                         {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                            <script>
                                setTimeout(function () {
                                    ['successAlert', 'errorAlert'].forEach(function (id) {
                                        let alert = document.getElementById(id);
                                        if (alert) {
                                            let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                            bsAlert.close();
                                        }
                                    });
                                }, 3000); // 3 seconds
                            </script>

                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Track Collector</h4>
                            </div>

                            <div class="card-body">
                                <!-- Filter bar -->
                                <div class="row mb-3">
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <label for="collectorFilter" class="form-label mb-1">Filter by Collector</label>
                                        <select id="collectorFilter" class="form-select form-select-sm">
                                            <option value="">All Collectors</option>
                                            @foreach ($collectors as $collectorName)
                                                <option value="{{ $collectorName }}">{{ $collectorName }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <label for="statusFilter" class="form-label mb-1">Filter by Status</label>
                                        <select id="statusFilter" class="form-select form-select-sm">
                                            <option value="">All Statuses</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Partial">Partial</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table id="example" class="display" style="min-width: 845px">
                                        <thead>
											<tr>
												<th>Sr.No</th>
												<th>Name</th>
												<th>Class</th>
												<th>Collector</th>
												<th>Balance</th>
												<th>Status</th>
											</tr>
										</thead>
                                        <tbody>
                                            @foreach ($students as $student)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $student->name }}</td>
                                                    <td>{{ $student->course }}</td>
                                                    <td data-collector="{{ $student->collector }}">
                                                        {{ $student->collector }}
                                                    </td>
                                                    <td data-order="{{ $student->balance }}">
                                                        ₹{{ number_format($student->balance) }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusLabel = ucfirst($student->status);
                                                            $badgeClass = match($student->status) {
                                                                'paid' => 'badge-success',
                                                                'partial' => 'badge-warning',
                                                                'pending' => 'badge-danger',
                                                                default => 'badge-secondary',
                                                            };
                                                        @endphp
                                                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Sr.No</th>
												<th>Name</th>
												<th>Class</th>
												<th>Collector</th>
												<th>Balance</th>
												<th>Status</th>
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

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Delete Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this student?
                    </div>
                    <div class="modal-footer">
                        <form method="POST" id="deleteForm">
                            @csrf
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-danger">
                                Yes, Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

		@include('owner.components.footer')

		<script>
			$(function () {
				// Grab (or init) the DataTable instance for #example
				var table = $('#example').DataTable();

				// Filter by Collector column (index 3)
				$('#collectorFilter').on('change', function () {
					var value = $(this).val();
					table.column(3).search(value ? '^' + $.fn.dataTable.util.escapeRegex(value) + '$' : '', true, false).draw();
				});

				// Filter by Status column (index 5)
				$('#statusFilter').on('change', function () {
					var value = $(this).val();
					table.column(5).search(value ? '^' + $.fn.dataTable.util.escapeRegex(value) + '$' : '', true, false).draw();
				});
			});

			function setDeleteId(id) {
				$('#deleteForm').attr('action', '/owner/manual/students/' + id);
			}
		</script>