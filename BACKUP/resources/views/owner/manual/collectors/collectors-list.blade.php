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
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <script>
                                setTimeout(function () {
                                    let alert = document.getElementById('successAlert');
                                    if (alert) {
                                        let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                        bsAlert.close();
                                    }
                                }, 3000); // 3 seconds
                            </script>
                            <div class="card-header">
                                <h4 class="card-title">Collectors List</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <div class="mb-3">
                                        <button class="btn btn-success" id="assignCollectorBtn">
                                            Assign Accountant
                                        </button>
                                    </div>
                                    <table id="example" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="selectAll">
                                                </th>
                                                <th>Sr.No</th>
                                                <th>Collector Name</th>
                                                <th>Account Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($collectors as $collector)
                                            <tr>
                                                <td>
                                                    <input type="checkbox"
                                                        class="collector-checkbox"
                                                        value="{{ $collector->id }}"
                                                        {{ $collector->accountant_id ? 'disabled' : '' }}>
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $collector->name }}</td>
                                                <td>{{ $collector->accountant_name ?? '-' }}</td>
                                                <td>{{ $collector->email }}</td>
                                                <td>{{ $collector->phone }}</td>

                                                <td>
                                                    <span
                                                        class="badge status-toggle {{ $collector->status == 'active' ? 'light badge-success' : 'light badge-danger' }}"
                                                        data-id="{{ $collector->id }}"
                                                        style="cursor:pointer;">
                                                        {{ ucfirst($collector->status) }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{ route('owner.manual.collectors.edit',$collector->id) }}"
                                                        class="btn btn-primary shadow btn-xs sharp me-1">
                                                            <i class="fas fa-pencil-alt"></i>
                                                        </a>

                                                        <button type="button"
                                                                class="btn btn-danger shadow btn-xs sharp">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="selectAllFooter">
                                                </th>
                                                <th>Sr.No</th>
												<th>Collector Name</th>
												<th>Account Name</th>
												<th>Email</th>
												<th>Phone</th>
												<th>Status</th>
												<th>action</th>
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

        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Delete Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this accountant?
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

        <div class="modal fade" id="assignModal">
            <div class="modal-dialog">
                <form id="assignForm" action="{{ route('owner.collectors.assignAccountant') }}" method="POST">
                    @csrf
                    @php
                        $accountants = \App\Models\Accountant::all();
                    @endphp
                    <input type="hidden" id="collector_ids" name="collector_ids">

                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Assign Accountant</h5>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label>Select Accountant</label>

                                <select class="form-control" name="accountant_id" required>
                                    <option value="">Select Accountant</option>

                                    @foreach($accountants as $accountant)
                                        <option value="{{ $accountant->id }}">
                                            {{ $accountant->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit" class="btn btn-success" id="assignSubmitBtn">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="assignSpinner"></span>
                                <span id="assignBtnText">Assign</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                $('#assignForm').on('submit', function () {
                    $('#assignSubmitBtn').prop('disabled', true);
                    $('#assignSpinner').removeClass('d-none');
                    $('#assignBtnText').text('Assigning...');
                });
            });
        </script>
        <script>
            $(document).ready(function () {

                $('#selectAll').change(function () {
                    $('.collector-checkbox').prop('checked', this.checked);
                });

                $('#assignCollectorBtn').click(function () {

                    let ids = [];

                    $('.collector-checkbox:checked').each(function () {
                        ids.push($(this).val());
                    });

                    if (ids.length == 0) {
                        toastr.warning('Please select at least one collector.');
                        return;
                    }

                    $('#collector_ids').val(ids.join(','));

                    $('#assignModal').modal('show');
                });

            });
        </script>

		@include('owner.components.footer')