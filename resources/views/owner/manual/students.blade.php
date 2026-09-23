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
					'email' => 'aarav.sharma@example.com',
					'phone' => '9876543210',
					'course' => '10th',
					'section' => 'A',
					'roll_number' => 'STU001',
					'status' => 'active',
				],
				(object) [
					'id' => 2,
					'name' => 'Priya Verma',
					'email' => 'priya.verma@example.com',
					'phone' => '9876543211',
					'course' => '9th',
					'section' => 'B',
					'roll_number' => 'STU002',
					'status' => 'active',
				],
				(object) [
					'id' => 3,
					'name' => 'Rohan Deshmukh',
					'email' => 'rohan.deshmukh@example.com',
					'phone' => '9876543212',
					'course' => '12th',
					'section' => 'A',
					'roll_number' => 'STU003',
					'status' => 'inactive',
				],
				(object) [
					'id' => 4,
					'name' => 'Sneha Patil',
					'email' => 'sneha.patil@example.com',
					'phone' => '9876543213',
					'course' => '8th',
					'section' => 'C',
					'roll_number' => 'STU004',
					'status' => 'active',
				],
				(object) [
					'id' => 5,
					'name' => 'Karan Mehta',
					'email' => 'karan.mehta@example.com',
					'phone' => '9876543214',
					'course' => '11th',
					'section' => 'B',
					'roll_number' => 'STU005',
					'status' => 'inactive',
				],
			]);
		@endphp

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
                                <h4 class="card-title mb-0">Students List</h4>

                                <div class="d-flex gap-2">

                                    <!-- Export -->
                                    <a href="#"
                                    class="btn btn-outline-success btn-sm">
                                        <i class="fa fa-file-excel me-1"></i> Export
                                    </a>

                                    <!-- Bulk Import -->
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#bulkImportModal">
                                        <i class="fa fa-file-upload me-1"></i> Bulk Import
                                    </button>

                                </div>
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
												<th>Class</th>
												<th>action</th>
											</tr>
										</thead>
                                        <tbody>
                                            @foreach ($students as $student)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $student->name }}</td>
                                                    <td>{{ $student->email }}</td>
                                                    <td>{{ $student->phone }}</td>
                                                    <td>{{ $student->course }}</td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <a href="{{ route('owner.manual.students.edit', ['id' => $student->id]) }}" 
                                                            class="btn btn-primary shadow btn-xs sharp me-1">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </a>

                                                            <button type="button"
                                                                    class="btn btn-danger shadow btn-xs sharp"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteModal"
                                                                    onclick="setDeleteId({{ $student->id }})">
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
                                                        url: "{{ route('owner.manual.students.changeStatus') }}",
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
												<th>Class</th>
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

        <!-- Bulk Import Modal -->
        <div class="modal fade" id="bulkImportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <form method="POST"
                          action="#"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title">Bulk Import Students</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-2">
                                Upload an Excel (.xlsx) or CSV file containing student details.
                            </p>

                            <div class="mb-2">
                                <input type="file"
                                       name="import_file"
                                       accept=".xlsx,.xls,.csv"
                                       class="form-control"
                                       required>
                            </div>

                            @error('import_file')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-file-upload me-1"></i> Import
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
		@include('owner.components.footer')