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

				<!-- ===================== ALERT MESSAGES ===================== -->
				<div class="col-10 mx-auto mt-4" id="ajaxAlertWrapper">
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

				<div class="row">

					<!-- ========================================================= -->
					<!-- ===================== RESPONSE SECTION ===================== -->
					<!-- ========================================================= -->
					<div class="col-md-6 col-12">
						<div class="card">

							<div class="card-header">
								<h4 class="card-title" id="responseFormTitle">Add Response</h4>
							</div>

							<div class="card-body">

								<!-- FORM -->
								<form id="responseForm">
									<input type="hidden" id="response_id">

									<div class="row">
										<div class="col-md-12 mb-3">
											<label class="form-label">Response</label>
											<textarea id="response" rows="3" class="form-control" placeholder="e.g. Client complained about late delivery of the invoice."></textarea>
											<small class="text-danger error-response"></small>
										</div>
									</div>

									<div class="mt-3 mb-4">
										<button type="submit" class="btn btn-primary" id="responseSubmitBtn">
											Save Response
										</button>
										<button type="button" class="btn btn-light" id="responseResetBtn">
											Reset
										</button>
									</div>
								</form>

								<hr>

								<!-- LIST -->
								<h5 class="mb-3">Response List</h5>
								<div class="table-responsive">
									<table class="table table-bordered table-striped" id="responseTable">
										<thead>
											<tr>
												<th>#</th>
												<th>Response</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody id="responseTableBody">
											@forelse($responses as $item)
												<tr id="response-row-{{ $item->id }}">
													<td>{{ $loop->iteration }}</td>
													<td class="col-response">{{ $item->response }}</td>
													<td>
														<button type="button" class="btn btn-sm btn-info response-edit-btn"
															data-id="{{ $item->id }}"
															data-response="{{ $item->response }}">
															Edit
														</button>
														<button type="button" class="btn btn-sm btn-danger response-delete-btn"
															data-id="{{ $item->id }}">
															Delete
														</button>
													</td>
												</tr>
											@empty
												<tr id="response-noDataRow">
													<td colspan="3" class="text-center">No responses found</td>
												</tr>
											@endforelse
										</tbody>
									</table>
								</div>

							</div>

						</div>
					</div>

					<!-- ========================================================= -->
					<!-- ===================== SOLUTION SECTION ===================== -->
					<!-- ========================================================= -->
					<div class="col-md-6 col-12">
						<div class="card">

							<div class="card-header">
								<h4 class="card-title" id="solutionFormTitle">Add Solution</h4>
							</div>

							<div class="card-body">

								<!-- FORM -->
								<form id="solutionForm">
									<input type="hidden" id="solution_id">

									<div class="row">
										<div class="col-md-12 mb-3">
											<label class="form-label">Solution</label>
											<textarea id="solution" rows="3" class="form-control" placeholder="e.g. Invoice re-sent via email and WhatsApp within 24 hours."></textarea>
											<small class="text-danger error-solution"></small>
										</div>
									</div>

									<div class="mt-3 mb-4">
										<button type="submit" class="btn btn-primary" id="solutionSubmitBtn">
											Save Solution
										</button>
										<button type="button" class="btn btn-light" id="solutionResetBtn">
											Reset
										</button>
									</div>
								</form>

								<hr>

								<!-- LIST -->
								<h5 class="mb-3">Solution List</h5>
								<div class="table-responsive">
									<table class="table table-bordered table-striped" id="solutionTable">
										<thead>
											<tr>
												<th>#</th>
												<th>Solution</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody id="solutionTableBody">
											@forelse($solutions as $item)
												<tr id="solution-row-{{ $item->id }}">
													<td>{{ $loop->iteration }}</td>
													<td class="col-solution">{{ $item->solution }}</td>
													<td>
														<button type="button" class="btn btn-sm btn-info solution-edit-btn"
															data-id="{{ $item->id }}"
															data-solution="{{ $item->solution }}">
															Edit
														</button>
														<button type="button" class="btn btn-sm btn-danger solution-delete-btn"
															data-id="{{ $item->id }}">
															Delete
														</button>
													</td>
												</tr>
											@empty
												<tr id="solution-noDataRow">
													<td colspan="3" class="text-center">No solutions found</td>
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

		<!-- ===================== DELETE CONFIRMATION MODAL ===================== -->
		<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Confirm Delete</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<p class="mb-0" id="deleteModalText">Are you sure you want to delete this?</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-danger" id="deleteConfirmBtn">Yes, Delete</button>
					</div>
				</div>
			</div>
		</div>

		<script>
			const CSRF_TOKEN = "{{ csrf_token() }}";

			// ===================== Alert helper (AJAX + session flash dono ke liye) ===================== //
			function showAlert(type, message) {
				const wrapper = document.getElementById("ajaxAlertWrapper");
				const div = document.createElement("div");
				div.className = `alert alert-${type} alert-dismissible fade show text-center`;
				div.setAttribute("role", "alert");
				div.innerHTML = `<button type="button" class="btn-close" data-bs-dismiss="alert"></button><span></span>`;
				div.querySelector("span").textContent = message;
				wrapper.appendChild(div);
				autoCloseAlert(div);
			}

			function autoCloseAlert(el) {
				setTimeout(function () {
					if (!el || !el.isConnected) return;
					if (window.bootstrap && bootstrap.Alert) {
						bootstrap.Alert.getOrCreateInstance(el).close();
					} else {
						el.remove();
					}
				}, 3000);
			}

			// page load par session flash messages bhi 3 sec me band ho jayen
			document.querySelectorAll("#ajaxAlertWrapper .alert").forEach(autoCloseAlert);

			// ===================== Delete confirmation modal helper ===================== //
			let deleteCallback = null;

			function openDeleteModal(message, onConfirm) {
				document.getElementById("deleteModalText").textContent = message;
				deleteCallback = onConfirm;
				bootstrap.Modal.getOrCreateInstance(document.getElementById("deleteConfirmModal")).show();
			}

			document.getElementById("deleteConfirmBtn").addEventListener("click", function () {
				bootstrap.Modal.getOrCreateInstance(document.getElementById("deleteConfirmModal")).hide();
				if (deleteCallback) {
					const cb = deleteCallback;
					deleteCallback = null;
					cb();
				}
			});

			function escapeHtml(str) {
				return String(str).replace(/[&<>"']/g, m => ({
					'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
				})[m]);
			}

			// ===================== Generic helper to wire up a section ===================== //
			function setupSection({ prefix, field, submitUrl, deleteUrl }) {
				const form       = document.getElementById(`${prefix}Form`);
				const formTitle  = document.getElementById(`${prefix}FormTitle`);
				const submitBtn  = document.getElementById(`${prefix}SubmitBtn`);
				const resetBtn   = document.getElementById(`${prefix}ResetBtn`);
				const tableBody  = document.getElementById(`${prefix}TableBody`);
				const idEl       = document.getElementById(`${prefix}_id`);
				const fieldEl    = document.getElementById(field);
				const errorEl    = document.querySelector(`.error-${field}`);

				function capitalize(str) {
					return str.charAt(0).toUpperCase() + str.slice(1);
				}

				function clearError() {
					errorEl.innerText = "";
				}

				function resetForm() {
					form.reset();
					idEl.value = "";
					formTitle.innerText = `Add ${capitalize(field)}`;
					submitBtn.innerText = `Save ${capitalize(field)}`;
					clearError();
				}

				function validateForm(value) {
					clearError();
					if (!value.trim()) {
						errorEl.innerText = `${capitalize(field)} is required`;
						return false;
					}
					return true;
				}

				function renumberRows() {
					tableBody.querySelectorAll("tr[id^='" + prefix + "-row-']").forEach((tr, i) => {
						tr.children[0].innerText = i + 1;
					});
				}

				function buildRow(item, index) {
					const val = escapeHtml(item[field]);
					return `
						<tr id="${prefix}-row-${item.id}">
							<td>${index}</td>
							<td class="col-${field}">${val}</td>
							<td>
								<button type="button" class="btn btn-sm btn-info ${prefix}-edit-btn"
									data-id="${item.id}"
									data-${field}="${val}">
									Edit
								</button>
								<button type="button" class="btn btn-sm btn-danger ${prefix}-delete-btn" data-id="${item.id}">
									Delete
								</button>
							</td>
						</tr>
					`;
				}

				resetBtn.addEventListener("click", resetForm);

				// ---------- SUBMIT (create / update) ----------
				form.addEventListener("submit", async function (e) {
					e.preventDefault();

					const value = fieldEl.value.trim();
					if (!validateForm(value)) return;

					const payload = { [field]: value };
					if (idEl.value) payload[`${prefix}_id`] = idEl.value;

					submitBtn.disabled = true;
					try {
						const res = await fetch(submitUrl, {
							method: "POST",
							headers: {
								"Content-Type": "application/json",
								"Accept": "application/json",
								"X-CSRF-TOKEN": CSRF_TOKEN
							},
							body: JSON.stringify(payload)
						});
						const data = await res.json();

						if (res.status === 422) {
							const msg = data.errors?.[field]?.[0] ?? "Validation error";
							errorEl.innerText = msg;
							showAlert("danger", msg);
							return;
						}
						if (!res.ok) throw new Error(data.message || "Something went wrong");

						const noDataRow = document.getElementById(`${prefix}-noDataRow`);
						if (noDataRow) noDataRow.remove();

						const existing = document.getElementById(`${prefix}-row-${data.data.id}`);
						if (existing) {
							const index = existing.children[0].innerText;
							existing.outerHTML = buildRow(data.data, index);
						} else {
							// latest upar dikhane ke liye insert top par, controller bhi latest() se order karta hai
							tableBody.insertAdjacentHTML("afterbegin", buildRow(data.data, 1));
							renumberRows();
						}

						showAlert("success", data.message || "Saved successfully");
						resetForm();
					} catch (err) {
						showAlert("danger", err.message || "Something went wrong");
					} finally {
						submitBtn.disabled = false;
					}
				});

				// ---------- EDIT / DELETE (event delegation) ----------
				tableBody.addEventListener("click", function (e) {
					if (e.target.classList.contains(`${prefix}-edit-btn`)) {
						const btn = e.target;
						idEl.value = btn.dataset.id;
						fieldEl.value = btn.dataset[field];

						formTitle.innerText = `Edit ${capitalize(field)}`;
						submitBtn.innerText = `Update ${capitalize(field)}`;

						window.scrollTo({ top: 0, behavior: "smooth" });
					}

					if (e.target.classList.contains(`${prefix}-delete-btn`)) {
						const id = e.target.dataset.id;
						openDeleteModal(`Are you sure you want to delete this ${field}?`, () => {

						fetch(deleteUrl.replace(':id', id), {
							method: "DELETE",
							headers: {
								"Accept": "application/json",
								"X-CSRF-TOKEN": CSRF_TOKEN
							}
						})
						.then(r => {
							if (!r.ok) throw new Error("Delete failed");
							return r.json();
						})
						.then(data => {
							document.getElementById(`${prefix}-row-${id}`)?.remove();
							showAlert("success", data.message || "Deleted successfully");

							// agar edit mode me yahi record khula tha to form reset kar do
							if (idEl.value === String(id)) resetForm();

							if (!tableBody.querySelector("tr")) {
								tableBody.innerHTML = `<tr id="${prefix}-noDataRow"><td colspan="3" class="text-center">No ${field}s found</td></tr>`;
							} else {
								renumberRows();
							}
						})
						.catch(() => showAlert("danger", "Delete failed"));
						});
					}
				});
			}

			// Init both sections independently
			setupSection({
				prefix: "response",
				field: "response",
				submitUrl: "{{ route('owner.tally.responses.submit') }}",
				deleteUrl: "{{ route('owner.tally.responses.delete', ['id' => ':id']) }}"
			});

			setupSection({
				prefix: "solution",
				field: "solution",
				submitUrl: "{{ route('owner.tally.solutions.submit') }}",
				deleteUrl: "{{ route('owner.tally.solutions.delete', ['id' => ':id']) }}"
			});
		</script>

		@include('owner.tally.components.footer')