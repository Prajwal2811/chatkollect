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

		<!-- CKEditor 5 (Classic Build) -->
		<script src="https://cdn.ckeditor.com/ckeditor5/41.3.0/classic/ckeditor.js"></script>

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">

					<!-- ===================== ADD / EDIT TEMPLATE FORM ===================== -->
					<div class="col-12">
						<div class="card">

							<div class="card-header">
								<h4 class="card-title" id="formTitle">Create WhatsApp Template</h4>
							</div>

							<div class="card-body">

								<form id="templateForm">
									<input type="hidden" id="template_id">

									<div class="row">

										<!-- Follow-up Type -->
										<div class="col-md-12 mb-3">
											<label class="form-label">Follow-up Type</label>
											<select id="followup_type" class="form-control">
												<option value="">-- Select Follow-up Type --</option>
												<option value="Follow Up-Balances">Follow Up-Balances</option>
												<option value="Follow Up-Balances/%Targets">Follow Up-Balances/%Targets</option>
												<option value="Follow Up-Balances/Targets(months)">Follow Up-Balances/Targets(months)</option>
												<option value="Follow Up-Balances/Days(agewise)">Follow Up-Balances/Days(agewise)</option>
												<option value="Follow Up-Balances/Days(10-20-30)">Follow Up-Balances/Days(10-20-30)</option>
												<option value="Follow Up-Due">Follow Up-Due</option>
												<option value="Follow Up-Not Due">Follow Up-Not Due</option>
												<option value="Follow Up Overlimits">Follow Up Overlimits</option>
											</select>
											<small class="text-danger error-followup_type"></small>
										</div>

										<!-- Template Name -->
										<div class="col-md-12 mb-3">
											<label class="form-label">Template Name</label>
											<input type="text" id="name" class="form-control" placeholder="e.g. Order Confirmation">
											<small class="text-danger error-name"></small>
										</div>

										<!-- Message Body (Rich Text Editor) -->
										<div class="col-md-12 mb-3">
											<label class="form-label">Message Body</label>
											<textarea id="message" rows="4" class="form-control" placeholder="Type your message. Use {{1}}, {{2}} for dynamic variables e.g. Hello {{1}}, your order {{2}} has been confirmed."></textarea>
											<small class="text-danger error-message"></small>
										</div>

									</div>

									<div class="mt-3">
										<button type="submit" class="btn btn-primary" id="submitBtn">
											Save Template
										</button>
										<button type="button" class="btn btn-light" id="resetBtn">
											Reset
										</button>
									</div>

								</form>

							</div>

						</div>
					</div>

					<!-- ===================== TEMPLATE LIST ===================== -->
					<div class="col-12">
						<div class="card">

							<div class="card-header">
								<h4 class="card-title">WhatsApp Templates List</h4>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-striped" id="templateTable">
										<thead>
											<tr>
												<th>#</th>
												<th>Follow-up Type</th>
												<th>Template Name</th>
												<th style="width: 500px;">Message Body</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody id="templateTableBody">
											<!-- Rows are rendered by JS from the `templates` array below -->
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
			const form           = document.getElementById("templateForm");
			const formTitle      = document.getElementById("formTitle");
			const submitBtn      = document.getElementById("submitBtn");
			const resetBtn       = document.getElementById("resetBtn");
			const tableBody      = document.getElementById("templateTableBody");
			const templateIdEl   = document.getElementById("template_id");
			const followupTypeEl = document.getElementById("followup_type");

			let nextId = 6; // dummy id counter

			// Dummy data: accountant -> client payment collection messages
			let templates = [
				{
					id: 1,
					followup_type: "Follow Up-Due",
					name: "Payment Due Reminder",
					message: "Dear {{1}}, this is a reminder that your payment of ₹{{2}} for {{3}} is due on {{4}}. Kindly clear the dues at the earliest. Thank you."
				},
				{
					id: 2,
					followup_type: "Follow Up-Balances",
					name: "Invoice Sent",
					message: "Hello {{1}}, please find attached invoice {{2}} dated {{3}} for an amount of ₹{{4}}. Kindly make the payment at your earliest convenience."
				},
				{
					id: 3,
					followup_type: "Follow Up-Balances/Days(agewise)",
					name: "Overdue Payment Notice",
					message: "Dear {{1}}, your payment of ₹{{2}} against invoice {{3}} is overdue since {{4}}. Please make the payment immediately to avoid any inconvenience."
				},
				{
					id: 4,
					followup_type: "Follow Up-Not Due",
					name: "Payment Received Confirmation",
					message: "Dear {{1}}, we have received your payment of ₹{{2}} on {{3}}. Thank you for the prompt payment. This message is auto-generated."
				},
				{
					id: 5,
					followup_type: "Follow Up Overlimits",
					name: "Payment Link Share",
					message: "Hi {{1}}, please use the following link to pay your pending amount of ₹{{2}}: {{3}}. Contact us if you face any issue."
				}
			];

			let ckEditor = null;

			// Initialize CKEditor on the message textarea
			ClassicEditor
				.create(document.querySelector('#message'), {
					toolbar: [
						'heading', '|',
						'bold', 'italic', 'underline', '|',
						'bulletedList', 'numberedList', '|',
						'link', 'blockQuote', '|',
						'undo', 'redo'
					]
				})
				.then(editor => {
					ckEditor = editor;
					renderTable(); // render dummy rows once editor is ready
				})
				.catch(error => {
					console.error(error);
				});

			function clearErrors() {
				document.querySelectorAll("small.text-danger").forEach(el => el.innerText = "");
			}

			function resetForm() {
				form.reset();
				templateIdEl.value = "";
				followupTypeEl.value = "";
				if (ckEditor) ckEditor.setData("");
				formTitle.innerText = "Create WhatsApp Template";
				submitBtn.innerText = "Save Template";
				clearErrors();
			}

			resetBtn.addEventListener("click", resetForm);

			function validateForm(data) {
				let isValid = true;
				clearErrors();

				if (!data.followup_type.trim()) {
					document.querySelector(".error-followup_type").innerText = "Please select a follow-up type";
					isValid = false;
				}
				if (!data.name.trim()) {
					document.querySelector(".error-name").innerText = "Template name is required";
					isValid = false;
				}
				if (!data.message.trim() || data.message.trim() === "") {
					document.querySelector(".error-message").innerText = "Message body is required";
					isValid = false;
				}

				return isValid;
			}

			function escapeHtml(str) {
				const div = document.createElement("div");
				div.innerText = str;
				return div.innerHTML;
			}

			function buildRow(template, index) {
				return `
					<tr id="row-${template.id}">
						<td>${index + 1}</td>
						<td class="col-followup">${escapeHtml(template.followup_type)}</td>
						<td class="col-name">${escapeHtml(template.name)}</td>
						<td class="col-message">${template.message}</td>
						<td>
							<button type="button" class="btn btn-sm btn-info edit-btn" data-id="${template.id}">
								Edit
							</button>
							<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${template.id}">
								Delete
							</button>
						</td>
					</tr>
				`;
			}

			function renderTable() {
				if (!templates.length) {
					tableBody.innerHTML = `<tr id="noDataRow"><td colspan="5" class="text-center">No templates found</td></tr>`;
					return;
				}
				tableBody.innerHTML = templates.map((t, i) => buildRow(t, i)).join("");
			}

			// Submit (Create / Update) - UI only, no backend call
			form.addEventListener("submit", function (e) {
				e.preventDefault();

				const data = {
					followup_type: followupTypeEl.value,
					name: document.getElementById("name").value.trim(),
					message: ckEditor ? ckEditor.getData().trim() : ""
				};

				if (!validateForm(data)) return;

				const id = templateIdEl.value;

				if (id) {
					// update existing
					const idx = templates.findIndex(t => t.id == id);
					if (idx !== -1) {
						templates[idx] = { id: Number(id), ...data };
					}
				} else {
					// create new
					templates.push({ id: nextId++, ...data });
				}

				renderTable();
				resetForm();
			});

			// Edit / Delete button clicks
			tableBody.addEventListener("click", function (e) {
				const id = e.target.dataset.id;
				if (!id) return;

				if (e.target.classList.contains("edit-btn")) {
					const template = templates.find(t => t.id == id);
					if (!template) return;

					templateIdEl.value = template.id;
					followupTypeEl.value = template.followup_type;
					document.getElementById("name").value = template.name;
					if (ckEditor) ckEditor.setData(template.message);

					formTitle.innerText = "Edit WhatsApp Template";
					submitBtn.innerText = "Update Template";

					window.scrollTo({ top: 0, behavior: "smooth" });
				}

				if (e.target.classList.contains("delete-btn")) {
					if (!confirm("Are you sure you want to delete this template?")) return;
					templates = templates.filter(t => t.id != id);
					renderTable();
				}
			});
		</script>

		@include('owner.components.footer')