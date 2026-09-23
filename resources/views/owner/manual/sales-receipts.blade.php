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
			// Dummy data (remove this block once real $students is passed from the controller)
			$students = collect([
				(object) [
					'id' => 1,
					'name' => 'Aarav Sharma',
					'email' => 'aarav.sharma@example.com',
					'phone' => '9876543210',
					'course' => '10th',
                    'bal' => 1000,
                    'collector' => 'Ramesh Kulkarni',
					'status' => 'active',
				],
				(object) [
					'id' => 2,
					'name' => 'Priya Verma',
					'email' => 'priya.verma@example.com',
					'phone' => '9876543211',
					'course' => '9th',
                    'bal' => 1000,
                    'collector' => 'Suresh Jadhav',
					'status' => 'active',
				],
				(object) [
					'id' => 3,
					'name' => 'Rohan Deshmukh',
					'email' => 'rohan.deshmukh@example.com',
					'phone' => '9876543212',
					'course' => '12th',
                    'bal' => 1000,
                    'collector' => 'Ramesh Kulkarni',
					'status' => 'inactive',
				],
				(object) [
					'id' => 4,
					'name' => 'Sneha Patil',
					'email' => 'sneha.patil@example.com',
					'phone' => '9876543213',
					'course' => '8th',
                    'bal' => 1000,
                    'collector' => 'Vinod Rane',
					'status' => 'active',
				],
				(object) [
					'id' => 5,
					'name' => 'Karan Mehta',
					'email' => 'karan.mehta@example.com',
					'phone' => '9876543214',
					'course' => '11th',
                    'bal' => 1000,
                    'collector' => 'Suresh Jadhav',
					'status' => 'inactive',
				],
                (object) [
                    'id' => 6,
                    'name' => 'Neha Patel',
                    'email' => 'neha.patel@example.com',
                    'phone' => '9876543215',
                    'course' => '7th',
                    'bal' => 1000,
                    'collector' => 'Vinod Rane',
                    'status' => 'active',
                ],
                (object) [
                    'id' => 7,
                    'name' => 'Aditya Singh',
                    'email' => 'aditya.singh@example.com',
                    'phone' => '9876543216',
                    'course' => '6th',  
                    'bal' => 1000,
                    'collector' => 'Ramesh Kulkarni',
                    'status' => 'inactive',
                ],
                (object) [
                    'id' => 8,
                    'name' => 'Rohit Kapoor',
                    'email' => 'rohit.kapoor@example.com',
                    'phone' => '9876543217',
                    'course' => '5th',
                    'bal' => 1000,
                    'collector' => 'Suresh Jadhav',
                    'status' => 'active',
                ],
                (object) [
                    'id' => 9,
                    'name' => 'Anushka Kapoor',
                    'email' => 'anushka.kapoor@example.com',
                    'phone' => '9876543218',
                    'course' => '4th',
                    'bal' => 1000,
                    'collector' => 'Vinod Rane',
                    'status' => 'inactive',
                ],
                (object) [
                    'id' => 10,
                    'name' => 'Vikram Reddy',
                    'email' => 'vikram.reddy@example.com',
                    'phone' => '9876543219',
                    'course' => '3rd',
                    'bal' => 1000,
                    'collector' => 'Ramesh Kulkarni',
                    'status' => 'active',
                ]
			]);
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
                            <div class="card-header">
                                <h4 class="card-title">Students List</h4>
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
                                                <th>Opening Balance</th>
                                                <th>Collector</th>
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
                                                    <td>{{ $student->bal }}</td>
                                                    <td>{{ $student->collector }}</td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <a href="{{ route('owner.manual.students.salesReceipt', ['id' => $student->id]) }}" class="btn btn-primary btn-sm shadow me-1">
                                                                <i class="fas fa-receipt me-1"></i>
                                                                Sales & Receipts
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Sr.No</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Class</th>
                                                <th>Opening Balance</th>
                                                <th>Collector</th>
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
        </div>
@include('owner.components.footer')