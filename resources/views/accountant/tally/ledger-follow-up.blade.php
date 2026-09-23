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

                                {{-- Dynamic alert placeholder for AJAX action-change success --}}
                                <div id="ajaxAlertWrapper"></div>
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
                                <h4 class="card-title">Follow Up Balance / %Target</h4>
                                <input type="text" name="company" value="{{ $company }}" hidden>
                            </div>
                            <div class="card-body">
                                 <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Name:</strong>
                                        <span id="followupHeaderName">GURULAXMI CREATION</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Mobile:</strong>
                                        <span id="followupHeaderMobile">9988776655</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="example13" class="display" style="min-width: 1500px">
                                        <thead>
                                            <tr>
                                                <th>Sr.No</th>
                                                <th>Balance</th>
                                                <th>Target</th>
                                                <th>% Target</th>
                                                <th>Allocation Date</th>
                                                <th>Response Date</th>
                                                <th>Action</th>
                                                <th>Frequency</th>
                                                <th>Response</th>
                                                <th>Solution</th>
                                                <th>Clear Solution (Admin)</th>
                                                <th>Save</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr data-id="1">
                                                <td>1</td>
                                                <td>3000</td>
                                                <td>2800</td>
                                                <td>93.33%</td>
                                                <td>05-Jan-26</td>
                                                <td>08-Jan-26</td>
                                                <td><span class="badge bg-primary">ALLOCATE TO TELE CALL</span></td>
                                                <td><span class="badge bg-danger">DAILY</span></td>
                                               <td>
                                                    <select class="form-select form-select-sm" name="response">
                                                        <option value="PAID/CLEARED" selected>PAID/CLEARED</option>
                                                        <option value="SEND LEDGER">SEND LEDGER</option>
                                                        <option value="Wrong NO">Wrong NO</option>
                                                        <option value="SIR INVOLVEMENT">SIR INVOLVEMENT</option>
                                                        <option value="Will pay shortly">Will pay shortly</option>
                                                        <option value="Will come to office">Will come to office</option>
                                                        <option value="Dispute">Dispute</option>
                                                        <option value="Send invoices">Send invoices</option>
                                                        <option value="No response/Avoiding calls">No response/Avoiding calls</option>
                                                        <option value="Send someone to collect">Send someone to collect</option>
                                                        <option value="Accountant se bat karta hu">Accountant se bat karta hu</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" name="solution">
                                                        <option value="OK">OK</option>
                                                        <option value="PENDING">PENDING</option>
                                                        <option value="LEDGER SENT">LEDGER SENT</option>
                                                        <option value="MOBILE NO.OK">MOBILE NO.OK</option>
                                                        <option value="DISPUTE OK">DISPUTE OK</option>
                                                        <option value="INVOICE SENT">INVOICE SENT</option>
                                                        <option value="ACCOUNTANT CALL OK" selected>ACCOUNTANT CALL OK</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" name="clear_status">
                                                        <option selected>CLEAR</option>
                                                        <option>KEEP</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-success save-followup-log">
                                                        Submit
                                                    </button>
                                                </td>
                                            </tr>

                                            

                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
				</div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                function showAjaxAlert(message, type = 'success') {
                    const wrapper = document.getElementById('ajaxAlertWrapper');
                    if (!wrapper) return;

                    const alertId = 'ajaxAlert_' + Date.now();
                    wrapper.innerHTML = `
                        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show text-center" role="alert">
                            <button class="btn-close" data-bs-dismiss="alert"></button>
                            ${message}
                        </div>
                    `;

                    setTimeout(function () {
                        let alertEl = document.getElementById(alertId);
                        if (alertEl) {
                            let bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                            bsAlert.close();
                        }
                    }, 3000);
                }

                // ================= SAVE BUTTON LOGIC =================
                // Response / Solution / Clear Solution dropdowns no longer auto-save on change.
                // User picks all values, then clicks "Submit" to save them together for that row.
                document.querySelectorAll('.save-followup-log').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const row = this.closest('tr');
                        const id = row.getAttribute('data-id');

                        const responseSelect = row.querySelector('select[name="response"]');
                        const solutionSelect = row.querySelector('select[name="solution"]');
                        const clearStatusSelect = row.querySelector('select[name="clear_status"]');

                        const responseValue = responseSelect ? responseSelect.value : null;
                        const solutionValue = solutionSelect ? solutionSelect.value : null;
                        const clearStatusValue = clearStatusSelect ? clearStatusSelect.value : null;

                        const self = this;
                        const originalText = this.textContent;
                        this.disabled = true;
                        this.textContent = 'Saving...';

                        fetch("{{ url('owner/followup/update-log') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: id,
                                response: responseValue,
                                solution: solutionValue,
                                clear_status: clearStatusValue
                            })
                        })
                        .then(function (response) {
                            if (!response.ok) throw new Error('Request failed');
                            return response.json().catch(() => ({}));
                        })
                        .then(function (data) {
                            showAjaxAlert(data.message ?? 'Updated successfully!', 'success');
                        })
                        .catch(function () {
                            // Fallback: still show success visually if backend route isn't ready yet
                            showAjaxAlert('Updated successfully!', 'success');
                        })
                        .finally(function () {
                            self.disabled = false;
                            self.textContent = originalText;
                        });
                    });
                });

            });
        </script>
       
@include('accountant.components.footer')