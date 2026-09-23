	@include('owner.tally.components.header')
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
		@include('owner.tally.components.navbar')
		@include('owner.tally.components.sidebar')

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
                            <div class="card-header">
                                <h4 class="card-title">Accountants List</h4>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example" class="display" style="min-width: 845px">
                                        <thead>
											<tr>
												<th>Sr.No</th>
												<th>Name</th>
												<th>Email</th>
												<th>Phone</th>
												<th>Status</th>
												<th>action</th>
											</tr>
										</thead>
                                        <tbody>
                                            @foreach ($accountants as $accountant)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $accountant->name }}</td>
                                                    <td>{{ $accountant->email }}</td>
                                                    <td>{{ $accountant->phone }}</td>
                                                   <td>
                                                        <span
                                                            class="badge status-toggle {{ $accountant->status == 'active' ? 'light badge-success' : 'light badge-danger' }}"
                                                            data-id="{{ $accountant->id }}"
                                                            style="cursor:pointer;"
                                                        >
                                                            {{ ucfirst($accountant->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <a href="{{ route('owner.accountants.edit', ['id' => $accountant->id]) }}" 
                                                            class="btn btn-primary shadow btn-xs sharp me-1">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </a>

                                                            <button type="button"
                                                                    class="btn btn-danger shadow btn-xs sharp"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteModal"
                                                                    onclick="setDeleteId(1)">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <script>
                                                $(document).on('click', '.status-toggle', function () {

                                                    let badge = $(this);
                                                    let id = badge.data('id');

                                                    $.ajax({
                                                        url: "{{ route('owner.accountants.changeStatus') }}",
                                                        type: "POST",
                                                        data: {
                                                            _token: "{{ csrf_token() }}",
                                                            id: id
                                                        },
                                                        success: function (response) {

                                                            if (response.status === 'active') {

                                                                badge
                                                                    .removeClass('badge-danger')
                                                                    .addClass('badge-success')
                                                                    .text('Active');

                                                            } else {

                                                                badge
                                                                    .removeClass('badge-success')
                                                                    .addClass('badge-danger')
                                                                    .text('Inactive');
                                                            }

                                                            toastr.success(response.message);
                                                        },
                                                        error: function () {
                                                            toastr.error('Something went wrong.');
                                                        }
                                                    });

                                                });
                                            </script>
                                            
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Sr.No</th>
												<th>Name</th>
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
		@include('owner.tally.components.footer')