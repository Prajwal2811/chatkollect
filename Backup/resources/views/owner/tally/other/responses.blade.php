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
											<!-- Dummy data rows -->
											<tr id="response-row-1">
												<td>1</td>
												<td class="col-response">Lorem ipsum dolor sit amet consectetur.</td>
												<td>
													<button type="button" class="btn btn-sm btn-info response-edit-btn"
														data-id="1"
														data-response="Lorem ipsum dolor sit amet consectetur.">
														Edit
													</button>
													<button type="button" class="btn btn-sm btn-danger response-delete-btn" data-id="1">
														Delete
													</button>
												</td>
											</tr>
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
											<!-- Dummy data rows -->
											<tr id="solution-row-1">
												<td>1</td>
												<td class="col-solution">Lorem ipsum dolor sit amet consectetur.</td>
												<td>
													<button type="button" class="btn btn-sm btn-info solution-edit-btn"
														data-id="1"
														data-solution="Lorem ipsum dolor sit amet consectetur.">
														Edit
													</button>
													<button type="button" class="btn btn-sm btn-danger solution-delete-btn" data-id="1">
														Delete
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
			// ===================== Generic helper to wire up a section ===================== //
			function setupSection({ prefix, field, dummyCount }) {
				const form       = document.getElementById(`${prefix}Form`);
				const formTitle  = document.getElementById(`${prefix}FormTitle`);
				const submitBtn  = document.getElementById(`${prefix}SubmitBtn`);
				const resetBtn   = document.getElementById(`${prefix}ResetBtn`);
				const tableBody  = document.getElementById(`${prefix}TableBody`);
				const idEl       = document.getElementById(`${prefix}_id`);
				const fieldEl    = document.getElementById(field);
				const errorEl    = document.querySelector(`.error-${field}`);

				let nextId = dummyCount;

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

				function buildRow(item) {
					return `
						<tr id="${prefix}-row-${item.id}">
							<td>${tableBody.children.length + 1}</td>
							<td class="col-${field}">${item[field]}</td>
							<td>
								<button type="button" class="btn btn-sm btn-info ${prefix}-edit-btn"
									data-id="${item.id}"
									data-${field}="${item[field]}">
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

				form.addEventListener("submit", function (e) {
					e.preventDefault();

					const value = fieldEl.value.trim();
					if (!validateForm(value)) return;

					const id = idEl.value;
					const isEdit = !!id;

					const noDataRow = document.getElementById(`${prefix}-noDataRow`);
					if (noDataRow) noDataRow.remove();

					if (isEdit) {
						const row = document.getElementById(`${prefix}-row-${id}`);
						if (row) row.outerHTML = buildRow({ id, [field]: value });
					} else {
						const newItem = { id: ++nextId, [field]: value };
						tableBody.insertAdjacentHTML("beforeend", buildRow(newItem));
					}

					resetForm();
				});

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
						if (!confirm(`Are you sure you want to delete this ${field}?`)) return;

						const row = document.getElementById(`${prefix}-row-${id}`);
						if (row) row.remove();

						if (!tableBody.children.length) {
							tableBody.innerHTML = `<tr id="${prefix}-noDataRow"><td colspan="3" class="text-center">No ${field}s found</td></tr>`;
						}
					}
				});
			}

			// Init both sections independently
			setupSection({ prefix: "response", field: "response", dummyCount: 3 });
			setupSection({ prefix: "solution", field: "solution", dummyCount: 3 });
		</script>

		@include('owner.tally.components.footer')