@include('owner.tally.components.header')
<div id="main-wrapper">
	<div class="nav-header">
		<a href="#" class="brand-logo">
			<svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
				<text x="55" y="32" font-size="22" font-family="Arial, sans-serif" font-weight="bold" fill="#4E3F6B">RMS</text>
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

	<!-- CKEditor 5 (Classic Build) -->
	<script src="https://cdn.ckeditor.com/ckeditor5/41.3.0/classic/ckeditor.js"></script>

	<style>
		.ai-param-title {
			font-size: 12px;
			color: #6c757d;
			margin: 10px 0 6px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
		}
		.ai-param-list {
			display: flex;
			flex-wrap: wrap;
			gap: 6px;
			align-items: center;
		}
		.ai-param-list span.param-chip {
			background: #fff;
			border: 1px solid #d8d2ec;
			color: #4E3F6B;
			font-size: 12px;
			padding: 3px 9px;
			border-radius: 20px;
			cursor: pointer;
			user-select: none;
			transition: all .15s ease;
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}
		.ai-param-list span.param-chip:hover { background: #4E3F6B; color: #fff; }
		.param-chip .remove-param { font-size: 13px; line-height: 1; color: inherit; opacity: .6; }
		.param-chip .remove-param:hover { opacity: 1; }
		.add-param-btn {
			background: #4E3F6B;
			color: #fff;
			font-size: 12px;
			padding: 3px 10px;
			border-radius: 20px;
			border: none;
			cursor: pointer;
		}
		.add-param-btn:hover { background: #3c3055; }

		.add-param-form {
			display: none;
			gap: 8px;
			align-items: center;
			margin-top: 8px;
			flex-wrap: wrap;
		}
		.add-param-form.show { display: flex; }
		.add-param-form input {
			font-size: 12px;
			padding: 4px 8px;
			border: 1px solid #d8d2ec;
			border-radius: 6px;
			min-width: 200px;
		}
		.add-param-form button { font-size: 12px; padding: 4px 10px; border-radius: 6px; border: none; }
		.add-param-form .btn-save-param { background: #4E3F6B; color: #fff; }
		.add-param-form .btn-cancel-param { background: #eee; color: #444; }

		.badge-soft {
			background: #eee9fb;
			color: #4E3F6B;
			font-size: 11px;
			padding: 3px 8px;
			border-radius: 20px;
			display: inline-block;
			margin-right: 4px;
		}
	</style>

	<div class="content-body default-height">
		<div class="container-fluid">
			<div class="row">

				<div class="col-10 mx-auto mt-4" id="ajaxAlertWrapper"></div>
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
									<div class="col-md-6 mb-3">
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
									<div class="col-md-6 mb-3">
										<label class="form-label">Template Name</label>
										<input type="text" id="name" class="form-control" placeholder="e.g. Order Confirmation">
										<small class="text-danger error-name"></small>
									</div>

									<div class="col-md-6 mb-3">
										<label class="form-label">Tone</label>
										<select id="ai_tone" class="form-control">
											<option value="Polite" selected>Polite</option>
											<option value="Urgent">Urgent</option>
											<option value="Firm">Firm</option>
											<option value="Friendly">Friendly</option>
										</select>
										<small class="text-danger error-tone"></small>
									</div>

									<div class="col-md-6 mb-3">
										<label class="form-label">Language</label>
										<select id="ai_language" class="form-control">
											<option value="English" selected>English</option>
											<option value="Hindi">Hindi</option>
											<option value="Hinglish">Hinglish</option>
										</select>
										<small class="text-danger error-language"></small>
									</div>

									<!-- Message Body (Rich Text Editor) -->
									<div class="col-md-12 mb-3">
										<label class="form-label">Message Body</label>
										<textarea id="message" rows="4" class="form-control" placeholder="Type your message. Use @{{1}}, @{{2}} for dynamic variables e.g. Hello @{{1}}, your outstanding is ₹@{{2}}."></textarea>
										<small class="text-danger error-message"></small>

										<div class="ai-param-title">
											<span>Click a parameter to insert it into the message — values are mapped automatically from Tally</span>
											<button type="button" class="add-param-btn" id="addParamToggleBtn">+ Add Parameter</button>
										</div>

										<div class="ai-param-list" id="paramList"></div>

										<div class="add-param-form" id="addParamForm">
											<input type="text" id="newParamLabel" placeholder="e.g. Contact Person Name">
											<button type="button" class="btn-save-param" id="saveParamBtn">Add</button>
											<button type="button" class="btn-cancel-param" id="cancelParamBtn">Cancel</button>
										</div>
									</div>

								</div>

								<div class="mt-3">
									<button type="submit" class="btn btn-primary" id="submitBtn">Save Template</button>
									<button type="button" class="btn btn-light" id="resetBtn">Reset</button>
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
											<th>Tone / Language</th>
											<th style="width: 450px;">Message Body</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody id="templateTableBody"></tbody>
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
		const nameEl         = document.getElementById("name");
		const toneEl         = document.getElementById("ai_tone");
		const languageEl     = document.getElementById("ai_language");

		// Parameter elements
		const paramListEl       = document.getElementById("paramList");
		const addParamToggleBtn = document.getElementById("addParamToggleBtn");
		const addParamForm      = document.getElementById("addParamForm");
		const newParamLabelEl   = document.getElementById("newParamLabel");
		const saveParamBtn      = document.getElementById("saveParamBtn");
		const cancelParamBtn    = document.getElementById("cancelParamBtn");

		// ------------------------------------------------------------
		// Backend endpoints
		// ------------------------------------------------------------
		const PARAM_ENDPOINT          = "{{ route('owner.other.template.parameters.store', $company) }}";
		const TEMPLATE_STORE_ENDPOINT = "{{ route('owner.other.templates.store', $company) }}";
		const TEMPLATE_UPDATE_URL     = "{{ route('owner.other.templates.update', [$company, '__ID__']) }}";
		const TEMPLATE_DELETE_URL     = "{{ route('owner.other.templates.destroy', [$company, '__ID__']) }}";

		const CSRF_TOKEN = "{{ csrf_token() }}";

		function apiHeaders() {
			return {
				"Content-Type": "application/json",
				"Accept": "application/json",
				"X-Requested-With": "XMLHttpRequest",
				"X-CSRF-TOKEN": CSRF_TOKEN
			};
		}

		function ph(n) {
			return String.fromCharCode(123, 123) + n + String.fromCharCode(125, 125);
		}

		function escapeHtml(str) {
			const div = document.createElement("div");
			div.innerText = str ?? "";
			return div.innerHTML;
		}

		function showAlert(type, message) {
			const wrapper = document.getElementById("ajaxAlertWrapper");
			if (!wrapper) return;

			const cls = type === "success" ? "alert-success" : "alert-danger";

			const el = document.createElement("div");
			el.className = `alert ${cls} alert-dismissible fade show text-center`;
			el.setAttribute("role", "alert");
			el.innerHTML = `<button type="button" class="btn-close" data-bs-dismiss="alert"></button> ${escapeHtml(message)}`;

			wrapper.innerHTML = "";   // keep only the latest message
			wrapper.appendChild(el);

			window.scrollTo({ top: 0, behavior: "smooth" });

			setTimeout(() => {
				if (!el.isConnected) return;
				if (window.bootstrap && bootstrap.Alert) {
					bootstrap.Alert.getOrCreateInstance(el).close();
				} else {
					el.remove();
				}
			}, 3000);
		}

		// ------------------------------------------------------------
		// Parameters
		// ------------------------------------------------------------
		const fixedParams = [
			{ num: 1, label: "Party Name" },
			{ num: 2, label: "Outstanding Balance" },
			{ num: 3, label: "Voucher/Invoice No." },
			{ num: 4, label: "Due Date" },
			{ num: 5, label: "Company Name" },
			{ num: 6, label: "GSTIN" },
			{ num: 7, label: "Payment Link" },
			{ num: 8, label: "Days Overdue" }
		];

		// Saved custom parameters come from DB
		let customParams = @json($customParams ?? []);

		function getAllParams() {
			return [...fixedParams, ...customParams];
		}

		function nextParamNumber() {
			const nums = getAllParams().map(p => p.num);
			return nums.length ? Math.max(...nums) + 1 : 1;
		}

		function renderParamList() {
			paramListEl.innerHTML = getAllParams().map(p => `
				<span class="param-chip" data-param="${ph(p.num)}">
					${ph(p.num)} ${escapeHtml(p.label)}
					${p.custom ? `<span class="remove-param" data-num="${p.num}" title="Remove from this list">&times;</span>` : ''}
				</span>
			`).join("");
		}

		paramListEl.addEventListener("click", function (e) {
			if (e.target.classList.contains("remove-param")) {
				const num = Number(e.target.dataset.num);
				customParams = customParams.filter(p => p.num !== num);
				renderParamList();
				return;
			}

			const chip = e.target.closest(".param-chip");
			if (!chip || !ckEditor) return;

			const param = chip.dataset.param;
			ckEditor.model.change(writer => {
				ckEditor.model.insertContent(
					writer.createText(param),
					ckEditor.model.document.selection
				);
			});
			ckEditor.editing.view.focus();
		});

		addParamToggleBtn.addEventListener("click", () => {
			addParamForm.classList.toggle("show");
			if (addParamForm.classList.contains("show")) {
				newParamLabelEl.value = "";
				newParamLabelEl.focus();
			}
		});

		cancelParamBtn.addEventListener("click", () => addParamForm.classList.remove("show"));

		saveParamBtn.addEventListener("click", async () => {
			const label = newParamLabelEl.value.trim();
			if (!label) { newParamLabelEl.focus(); return; }

			saveParamBtn.disabled = true;
			try {
				const res = await fetch(PARAM_ENDPOINT, {
					method: "POST",
					headers: apiHeaders(),
					body: JSON.stringify({ label })
				});
				const json = await res.json().catch(() => ({}));

				if (!res.ok) {
					showAlert("error", (json.errors && json.errors.label && json.errors.label[0]) || "Could not save the parameter.");
					return;
				}

				customParams.push({ num: json.num, label: json.label, custom: true });
				renderParamList();
				addParamForm.classList.remove("show");
				showAlert("success", "Parameter added successfully.");
			} catch (err) {
				console.error(err);
				alert("Could not save the parameter. Please try again.");
			} finally {
				saveParamBtn.disabled = false;
			}
		});

		newParamLabelEl.addEventListener("keydown", (e) => {
			if (e.key === "Enter") {
				e.preventDefault();
				saveParamBtn.click();
			}
		});

		// ------------------------------------------------------------
		// Templates (seeded from DB)
		// ------------------------------------------------------------
		let templates = @json($templates->values());

		let ckEditor = null;

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
			.then(editor => { ckEditor = editor; })
			.catch(error => console.error(error));

		// Render even if the editor fails to load
		renderParamList();
		renderTable();

		/* ============================================================
		 * FORM / TABLE
		 * ============================================================ */
		function clearErrors() {
			document.querySelectorAll("small.text-danger").forEach(el => el.innerText = "");
		}

		function resetForm() {
			form.reset();
			templateIdEl.value = "";
			followupTypeEl.value = "";
			toneEl.value = "Polite";
			languageEl.value = "English";
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
			if (!data.message.trim()) {
				document.querySelector(".error-message").innerText = "Message body is required";
				isValid = false;
			}
			return isValid;
		}

		function buildRow(template, index) {
			return `
				<tr id="row-${template.id}">
					<td>${index + 1}</td>
					<td class="col-followup">${escapeHtml(template.followup_type)}</td>
					<td class="col-name">${escapeHtml(template.name)}</td>
					<td class="col-meta">
						<span class="badge-soft">${escapeHtml(template.tone || "-")}</span>
						<span class="badge-soft">${escapeHtml(template.language || "-")}</span>
					</td>
					<td class="col-message">${template.message}</td>
					<td>
						<button type="button" class="btn btn-sm btn-info edit-btn" data-id="${template.id}">Edit</button>
						<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${template.id}">Delete</button>
					</td>
				</tr>
			`;
		}

		function renderTable() {
			if (!templates.length) {
				tableBody.innerHTML = `<tr id="noDataRow"><td colspan="6" class="text-center">No templates found</td></tr>`;
				return;
			}
			tableBody.innerHTML = templates.map((t, i) => buildRow(t, i)).join("");
		}

		// Submit (Create / Update)
		form.addEventListener("submit", async function (e) {
			e.preventDefault();

			const data = {
				followup_type: followupTypeEl.value,
				name:          nameEl.value.trim(),
				tone:          toneEl.value,
				language:      languageEl.value,
				message:       ckEditor ? ckEditor.getData().trim() : ""
			};

			if (!validateForm(data)) return;

			const id     = templateIdEl.value;
			const isEdit = !!id;
			const url    = isEdit ? TEMPLATE_UPDATE_URL.replace("__ID__", id) : TEMPLATE_STORE_ENDPOINT;

			submitBtn.disabled = true;
			try {
				const res = await fetch(url, {
					method: isEdit ? "PUT" : "POST",
					headers: apiHeaders(),
					body: JSON.stringify(data)
				});

				const json = await res.json().catch(() => ({}));

				if (!res.ok) {
					if (json.errors) {
						Object.entries(json.errors).forEach(([field, msgs]) => {
							const el = document.querySelector(`.error-${field}`);
							if (el) el.innerText = msgs[0];
						});
					}
					throw new Error("Request failed: " + res.status);
				}

				if (isEdit) {
					const idx = templates.findIndex(t => t.id == id);
					if (idx !== -1) templates[idx] = json.template;
				} else {
					templates.unshift(json.template);
				}

				renderTable();
				resetForm();
				showAlert("success", json.message || "Template saved successfully.");

				renderTable();
				resetForm();
			} catch (err) {
				console.error(err);
				showAlert("error", "Could not save the template. Please try again.");
			} finally {
				submitBtn.disabled = false;
			}
		});

		// Edit / Delete button clicks
		tableBody.addEventListener("click", function (e) {
			const id = e.target.dataset.id;
			if (!id) return;

			if (e.target.classList.contains("edit-btn")) {
				const template = templates.find(t => t.id == id);
				if (!template) return;

				clearErrors();
				templateIdEl.value = template.id;
				followupTypeEl.value = template.followup_type;
				nameEl.value = template.name;
				toneEl.value = template.tone || "Polite";
				languageEl.value = template.language || "English";
				if (ckEditor) ckEditor.setData(template.message);

				formTitle.innerText = "Edit WhatsApp Template";
				submitBtn.innerText = "Update Template";

				window.scrollTo({ top: 0, behavior: "smooth" });
			}

			if (e.target.classList.contains("delete-btn")) {
				if (!confirm("Are you sure you want to delete this template?")) return;

				fetch(TEMPLATE_DELETE_URL.replace("__ID__", id), {
					method: "DELETE",
					headers: apiHeaders()
				})
				.then(async res => {
					const json = await res.json().catch(() => ({}));
					if (!res.ok) throw new Error("Delete failed: " + res.status);
					templates = templates.filter(t => t.id != id);
					renderTable();
					if (templateIdEl.value == id) resetForm();
					showAlert("success", json.message || "Template deleted successfully.");
				})
				.catch(err => {
					console.error(err);
					showAlert("error", "Could not delete the template. Please try again.");
				});
			}
		});
	</script>

</div>
@include('owner.tally.components.footer')