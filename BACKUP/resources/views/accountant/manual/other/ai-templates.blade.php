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

		<style>
			/* ===================== AI COMPOSER — SCOPED STYLES ===================== */
			:root{
				--ai-violet:#4E3F6B;
				--ai-violet-deep:#372C4C;
				--ai-a:#6C5CE7;
				--ai-b:#00B8A0;
				--ai-wa:#25D366;
				--ai-wa-deep:#128C7E;
				--ai-border:#E7E3F3;
				--ai-tint:#F7F5FC;
			}
			.ai-card{
				border:1px solid var(--ai-border);
				border-radius:16px;
				overflow:hidden;
			}
			.ai-card .card-header{
				background:linear-gradient(100deg, var(--ai-violet-deep), var(--ai-violet));
				border-bottom:none;
			}
			.ai-card .card-header .card-title{
				color:#fff;
				display:flex;
				align-items:center;
				gap:10px;
			}
			.ai-badge{
				font-family:'Courier New', monospace;
				font-size:10.5px;
				font-weight:700;
				letter-spacing:.08em;
				text-transform:uppercase;
				color:#fff;
				background:linear-gradient(100deg,var(--ai-a),var(--ai-b));
				padding:3px 10px;
				border-radius:999px;
			}
			.ai-body{background:var(--ai-tint);}
			.ai-board{display:grid;grid-template-columns:1.15fr .85fr;gap:20px;align-items:start;}
			@media (max-width:991px){ .ai-board{grid-template-columns:1fr;} }

			.ai-field{margin-bottom:18px;}
			.ai-field:last-child{margin-bottom:0;}
			.ai-field label{font-size:13px;font-weight:600;color:#1B1B2F;margin-bottom:8px;display:block;}

			.ai-chip-row{display:flex;flex-wrap:wrap;gap:8px;}
			.ai-chip{
				border:1px solid var(--ai-border);
				background:#fff;
				color:#5B5A72;
				padding:7px 13px;
				border-radius:999px;
				font-size:12.5px;
				font-weight:500;
				cursor:pointer;
				transition:.15s ease;
				user-select:none;
			}
			.ai-chip:hover{border-color:var(--ai-a);color:#1B1B2F;}
			.ai-chip.active{background:linear-gradient(100deg,var(--ai-a),var(--ai-b));border-color:transparent;color:#fff;}

			.ai-param-list{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;}
			.ai-param-list span{
				font-family:'Courier New', monospace;
				font-size:11px;
				background:#EFEAFB;
				color:var(--ai-violet);
				padding:3px 8px;
				border-radius:6px;
				cursor:default;
			}

			.ai-generate-btn{
				width:100%;
				margin-top:6px;
				border:none;
				border-radius:12px;
				padding:13px 16px;
				font-size:14.5px;
				font-weight:600;
				color:#fff;
				cursor:pointer;
				background:linear-gradient(100deg,var(--ai-a),var(--ai-b));
				display:flex;
				align-items:center;
				justify-content:center;
				gap:9px;
				box-shadow:0 10px 22px -10px rgba(108,92,231,.5);
				transition:transform .15s ease;
			}
			.ai-generate-btn:hover{transform:translateY(-1px);}
			.ai-generate-btn:disabled{opacity:.7;cursor:progress;}
			.ai-spark{animation:ai-spin 1.1s linear infinite;transform-origin:center;}
			@keyframes ai-spin{to{transform:rotate(360deg);}}

			/* phone preview — signature element */
			.ai-phone-shell{
				position:sticky;
				top:16px;
				background:linear-gradient(160deg,var(--ai-violet-deep),var(--ai-violet) 65%);
				border-radius:28px;
				padding:12px;
			}
			.ai-phone-screen{
				background:#E9E3F5;
				border-radius:20px;
				overflow:hidden;
				min-height:300px;
				display:flex;
				flex-direction:column;
			}
			.ai-phone-top{
				background:var(--ai-wa-deep);
				color:#fff;
				padding:12px 14px 10px;
				display:flex;
				align-items:center;
				gap:9px;
			}
			.ai-phone-avatar{
				width:30px;height:30px;border-radius:50%;
				background:linear-gradient(135deg,var(--ai-a),var(--ai-b));
				display:flex;align-items:center;justify-content:center;
				font-size:12px;font-weight:700;
			}
			.ai-phone-top .name{font-size:13px;font-weight:600;}
			.ai-phone-top .status{font-size:10.5px;opacity:.8;}
			.ai-phone-body{
				flex:1;
				padding:14px 10px;
				background-image:radial-gradient(rgba(255,255,255,.35) 1px, transparent 1px);
				background-size:14px 14px;
				display:flex;
				flex-direction:column;
				justify-content:flex-end;
				gap:8px;
			}
			.ai-bubble{
				background:#fff;
				border-radius:4px 12px 12px 12px;
				padding:10px 12px 7px;
				max-width:94%;
				font-size:12.5px;
				line-height:1.5;
				color:#222;
			}
			.ai-bubble .var-pill{
				background:#EFEAFB;color:var(--ai-violet);
				font-family:'Courier New', monospace;font-size:11px;
				padding:1px 5px;border-radius:4px;
			}
			.ai-bubble .time{display:block;text-align:right;font-size:10px;color:#9a9a9a;margin-top:3px;}
			.ai-typing{background:#fff;border-radius:4px 12px 12px 12px;padding:10px 14px;display:flex;gap:4px;width:fit-content;}
			.ai-typing span{width:6px;height:6px;border-radius:50%;background:#b9b4cc;animation:ai-bounce 1.1s infinite;}
			.ai-typing span:nth-child(2){animation-delay:.15s;}
			.ai-typing span:nth-child(3){animation-delay:.3s;}
			@keyframes ai-bounce{0%,60%,100%{transform:translateY(0);opacity:.5;}30%{transform:translateY(-4px);opacity:1;}}
			.ai-phone-placeholder{color:#8b85a3;font-size:11.5px;text-align:center;padding:0 16px;margin:auto;}
			.ai-phone-footer{padding:8px 12px 12px;color:#EFEAFB;font-size:10px;text-align:center;font-family:'Courier New', monospace;opacity:.75;}

			/* variant cards */
			.ai-variants-head{display:flex;align-items:center;justify-content:space-between;margin:22px 0 12px;}
			.ai-variants-head h5{margin:0;font-weight:700;color:#1B1B2F;}
			.ai-variants-head .ai-count{font-family:'Courier New', monospace;font-size:12px;color:#5B5A72;}
			.ai-variant-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
			@media (max-width:991px){.ai-variant-grid{grid-template-columns:1fr;}}
			.ai-variant{
				background:#fff;
				border:1px solid var(--ai-border);
				border-radius:12px;
				padding:15px;
				display:flex;
				flex-direction:column;
				gap:10px;
				transition:.15s ease;
			}
			.ai-variant:hover{border-color:var(--ai-a);transform:translateY(-2px);}
			.ai-variant .ai-tag{
				align-self:flex-start;
				font-family:'Courier New', monospace;
				font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
				padding:3px 8px;border-radius:999px;color:var(--ai-wa-deep);background:#E4F9EC;
			}
			.ai-variant p{font-size:12.5px;line-height:1.5;color:#1B1B2F;margin:0;flex:1;}
			.ai-variant p .var-pill{background:#EFEAFB;color:var(--ai-violet);font-family:'Courier New', monospace;font-size:11px;padding:1px 5px;border-radius:4px;}
			.ai-variant-actions{display:flex;gap:8px;}
			.ai-btn-use, .ai-btn-preview{
				flex:1;border-radius:8px;border:1px solid var(--ai-border);background:#fff;
				padding:7px 8px;font-size:12px;font-weight:600;cursor:pointer;color:#1B1B2F;
			}
			.ai-btn-use{background:#1B1B2F;color:#fff;border-color:#1B1B2F;}
			.ai-btn-use:hover{background:var(--ai-violet-deep);}
			.ai-btn-preview:hover{border-color:var(--ai-a);color:var(--ai-a);}
			.ai-empty-note{font-size:12.5px;color:#5B5A72;padding:20px;text-align:center;border:1px dashed var(--ai-border);border-radius:12px;grid-column:1 / -1;}
			.ai-divider{border:none;border-top:1px dashed var(--ai-border);margin:26px 0;}
		</style>

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">

					<!-- ===================== AI TEMPLATE COMPOSER ===================== -->
					<div class="col-12">
						<div class="card ai-card">
							<div class="card-header">
								<h4 class="card-title">
									✨ AI Template Composer
									<span class="ai-badge">Beta</span>
								</h4>
							</div>
							<div class="card-body ai-body">
								<div class="ai-board">

									<!-- LEFT: brief -->
									<div>
										<div class="ai-field">
											<label>Follow-up type</label>
											<select id="ai_followup_type" class="form-control">
												<option value="Follow Up-Balances">Follow Up-Balances</option>
												<option value="Follow Up-Balances/%Targets">Follow Up-Balances/%Targets</option>
												<option value="Follow Up-Balances/Targets(months)">Follow Up-Balances/Targets(months)</option>
												<option value="Follow Up-Balances/Days(agewise)">Follow Up-Balances/Days(agewise)</option>
												<option value="Follow Up-Balances/Days(10-20-30)">Follow Up-Balances/Days(10-20-30)</option>
												<option value="Follow Up-Due">Follow Up-Due</option>
												<option value="Follow Up-Not Due">Follow Up-Not Due</option>
												<option value="Follow Up Overlimits">Follow Up Overlimits</option>
											</select>
										</div>

										<div class="ai-field">
											<label>Tone</label>
											<div class="ai-chip-row" id="ai_tone_row">
												<div class="ai-chip active" data-val="Polite">Polite</div>
												<div class="ai-chip" data-val="Firm">Firm</div>
												<div class="ai-chip" data-val="Urgent">Urgent</div>
												<div class="ai-chip" data-val="Friendly">Friendly</div>
											</div>
										</div>

										<div class="ai-field">
											<label>Language</label>
											<div class="ai-chip-row" id="ai_lang_row">
												<div class="ai-chip active" data-val="English">English</div>
												<div class="ai-chip" data-val="Hindi">Hindi</div>
												<div class="ai-chip" data-val="Hinglish">Hinglish</div>
											</div>
										</div>

										<div class="ai-field">
											<label>Briefly describe the message</label>
											<textarea id="ai_brief" class="form-control" rows="3" placeholder="e.g. Remind the party about overdue outstanding balance as per Tally ledger and share payment link">Remind the party about their overdue outstanding balance as per the Tally ledger and share the payment link.</textarea>
											<div class="ai-param-list" title="Insert into your brief or leave as-is — the AI maps these automatically from Tally">
												<span>@{{1}} Party Name</span>
												<span>@{{2}} Outstanding Balance</span>
												<span>@{{3}} Voucher/Invoice No.</span>
												<span>@{{4}} Due Date</span>
												<span>@{{5}} Company Name</span>
												<span>@{{6}} GSTIN</span>
												<span>@{{7}} Payment Link</span>
												<span>@{{8}} Days Overdue</span>
											</div>
										</div>

										<button type="button" class="ai-generate-btn" id="aiGenerateBtn">
											<svg id="aiGenIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 2L14 9L21 11L14 13L12 20L10 13L3 11L10 9L12 2Z" fill="white"/>
											</svg>
											<span id="aiGenLabel">Generate 3 templates</span>
										</button>
									</div>

									<!-- RIGHT: live phone preview -->
									<div>
										<div class="ai-phone-shell">
											<div class="ai-phone-screen">
												<div class="ai-phone-top">
													<div class="ai-phone-avatar">TP</div>
													<div>
														<div class="name">Party — Tally Ledger</div>
														<div class="status" id="aiPhoneStatus">online</div>
													</div>
												</div>
												<div class="ai-phone-body" id="aiPhoneBody">
													<div class="ai-phone-placeholder">Generate a template to preview it here, exactly as your client will see it.</div>
												</div>
												<div class="ai-phone-footer">LIVE PREVIEW · WHATSAPP BUSINESS API</div>
											</div>
										</div>
									</div>

								</div>

								<div class="ai-variants-head">
									<h5>Generated drafts</h5>
									<span class="ai-count" id="aiVariantCount">0 of 3</span>
								</div>
								<div class="ai-variant-grid" id="aiVariantGrid">
									<div class="ai-empty-note">Your AI-drafted templates will appear here. Pick one to load it into the form below for a final review before saving.</div>
								</div>

							</div>
						</div>
					</div>

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
											<textarea id="message" rows="4" class="form-control" placeholder="Type your message. Use @{{1}}, @{{2}} for dynamic variables e.g. Hello @{{1}}, your order @{{2}} has been confirmed."></textarea>
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

			// Dummy data: accountant -> client payment collection messages (Tally style parameters)
			let templates = [
				{
					id: 1,
					followup_type: "Follow Up-Due",
					name: "Payment Due Reminder",
					message: "Dear @{{1}}, this is a reminder that your outstanding balance of ₹@{{2}} against voucher @{{3}} is due on @{{4}}. Kindly clear the dues at the earliest. Regards, @{{5}}."
				},
				{
					id: 2,
					followup_type: "Follow Up-Balances",
					name: "Invoice Sent",
					message: "Hello @{{1}}, please find invoice @{{3}} for an amount of ₹@{{2}}. GSTIN: @{{6}}. Kindly make the payment at your earliest convenience via @{{7}}."
				},
				{
					id: 3,
					followup_type: "Follow Up-Balances/Days(agewise)",
					name: "Overdue Payment Notice",
					message: "Dear @{{1}}, your outstanding balance of ₹@{{2}} against voucher @{{3}} is overdue by @{{8}} days. Please make the payment immediately to avoid any inconvenience."
				},
				{
					id: 4,
					followup_type: "Follow Up-Not Due",
					name: "Payment Received Confirmation",
					message: "Dear @{{1}}, we have received your payment of ₹@{{2}} against voucher @{{3}}. Thank you for the prompt payment. This message is auto-generated by @{{5}}."
				},
				{
					id: 5,
					followup_type: "Follow Up Overlimits",
					name: "Payment Link Share",
					message: "Hi @{{1}}, please use the following link to pay your pending outstanding of ₹@{{2}}: @{{7}}. Contact us if you face any issue."
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

			/* ===================== AI TEMPLATE COMPOSER LOGIC ===================== */
			const aiToneRow      = document.getElementById("ai_tone_row");
			const aiLangRow      = document.getElementById("ai_lang_row");
			const aiFollowupType = document.getElementById("ai_followup_type");
			const aiBrief        = document.getElementById("ai_brief");
			const aiGenerateBtn  = document.getElementById("aiGenerateBtn");
			const aiGenLabel     = document.getElementById("aiGenLabel");
			const aiGenIcon      = document.getElementById("aiGenIcon");
			const aiVariantGrid  = document.getElementById("aiVariantGrid");
			const aiVariantCount = document.getElementById("aiVariantCount");
			const aiPhoneBody    = document.getElementById("aiPhoneBody");
			const aiPhoneStatus  = document.getElementById("aiPhoneStatus");

			let aiTone = "Polite";
			let aiLang = "English";
			let aiVariants = [];

			aiToneRow.addEventListener("click", e => {
				if (!e.target.classList.contains("ai-chip")) return;
				aiToneRow.querySelectorAll(".ai-chip").forEach(c => c.classList.remove("active"));
				e.target.classList.add("active");
				aiTone = e.target.dataset.val;
			});
			aiLangRow.addEventListener("click", e => {
				if (!e.target.classList.contains("ai-chip")) return;
				aiLangRow.querySelectorAll(".ai-chip").forEach(c => c.classList.remove("active"));
				e.target.classList.add("active");
				aiLang = e.target.dataset.val;
			});

			// ---- local draft "engine" used for this proposal walkthrough ----
			// In production, replace generateDrafts() with a fetch() call to your
			// backend (e.g. POST /owner/whatsapp-templates/ai-generate) which sends
			// { followup_type, tone, language, brief } to the AI provider and
			// returns 3 generated variants as JSON.
			const aiOpeners = {
				Polite:   ["Dear @{{1}}, we hope you're doing well.", "Hello @{{1}}, a gentle reminder from @{{5}}."],
				Firm:     ["Dear @{{1}}, this is a follow-up regarding your pending payment.", "@{{1}}, please treat this as an important notice from @{{5}}."],
				Urgent:   ["@{{1}}, immediate attention is required on your account.", "Dear @{{1}}, this is time-sensitive."],
				Friendly: ["Hi @{{1}}! Just checking in on your payment.", "Hey @{{1}}, hope all's well — quick reminder from @{{5}}!"]
			};
			const aiBodies = [
				"As per our records, an outstanding balance of @{{2}} against voucher @{{3}} was due on @{{4}}.",
				"We show a pending amount of @{{2}} against invoice @{{3}}, due @{{4}}.",
				"Your account reflects @{{2}} overdue by @{{8}} days since voucher @{{3}}."
			];
			const aiClosers = {
				Polite:   ["Kindly clear the dues at your earliest convenience via @{{7}}. Thank you.", "Please make the payment using @{{7}} whenever convenient. We appreciate your cooperation."],
				Firm:     ["Please make the payment using @{{7}} within 48 hours to avoid further action.", "Kindly settle this using @{{7}} without further delay."],
				Urgent:   ["Please pay immediately via @{{7}} to avoid service disruption.", "Urgent action needed — pay now via @{{7}}."],
				Friendly: ["You can settle it anytime here: @{{7}} — thanks a lot!", "Whenever you get a chance, here's the link: @{{7}} 🙂"]
			};
			const aiHinglishNote = " (Kripya jald bhugtan karein.)";

			function aiBuildVariant(i) {
				const opener = aiOpeners[aiTone][i % aiOpeners[aiTone].length];
				const body   = aiBodies[i % aiBodies.length];
				const closer = aiClosers[aiTone][i % aiClosers[aiTone].length];
				let text = `${opener} ${body} ${closer}`;
				if (aiLang === "Hinglish") text += aiHinglishNote;
				if (aiLang === "Hindi") text = `[Hindi] ${text}`;
				return text;
			}

			function aiPillify(text) {
				// NOTE: built with a callback function (not a template-literal replacement
				// string) on purpose — Blade compiles any literal double-curly pair it
				// finds in this .blade.php file as PHP, and a PHP variable can't start
				// with a digit, so writing that pattern directly here would break again.
				var open  = "{" + "{";
				var close = "}" + "}";
				return text.replace(/\{\{(\d)\}\}/g, function (match, num) {
					return '<span class="var-pill">' + open + num + close + '</span>';
				});
			}

			aiGenerateBtn.addEventListener("click", () => {
				aiGenerateBtn.disabled = true;
				aiGenLabel.textContent = "Drafting…";
				aiGenIcon.classList.add("ai-spark");
				aiPhoneStatus.textContent = "typing…";
				aiPhoneBody.innerHTML = `<div class="ai-typing"><span></span><span></span><span></span></div>`;
				aiVariantGrid.innerHTML = `<div class="ai-empty-note">AI is drafting 3 options based on your brief…</div>`;
				aiVariantCount.textContent = "0 of 3";

				setTimeout(() => {
					aiVariants = [0, 1, 2].map(aiBuildVariant);
					aiRenderVariants();
					aiShowInPhone(aiVariants[0]);
					aiPhoneStatus.textContent = "online";
					aiGenLabel.textContent = "Generate 3 templates";
					aiGenIcon.classList.remove("ai-spark");
					aiGenerateBtn.disabled = false;
				}, 1400);
			});

			function aiRenderVariants() {
				aiVariantCount.textContent = `${aiVariants.length} of 3`;
				aiVariantGrid.innerHTML = aiVariants.map((v, i) => `
					<div class="ai-variant">
						<span class="ai-tag">Draft ${i + 1}</span>
						<p>${aiPillify(v)}</p>
						<div class="ai-variant-actions">
							<button type="button" class="ai-btn-use" data-i="${i}" data-action="use">Use in form</button>
							<button type="button" class="ai-btn-preview" data-i="${i}" data-action="preview">Preview</button>
						</div>
					</div>
				`).join("");
			}

			aiVariantGrid.addEventListener("click", e => {
				const i = e.target.dataset.i;
				if (i === undefined) return;
				const text = aiVariants[i];
				aiShowInPhone(text);

				if (e.target.dataset.action === "use") {
					// Load the chosen AI draft into the manual form below so it can
					// be reviewed / tweaked in CKEditor before saving as usual.
					followupTypeEl.value = aiFollowupType.value;
					document.getElementById("name").value =
						`AI Draft - ${aiFollowupType.value} (${aiTone})`;
					if (ckEditor) ckEditor.setData(text);
					formTitle.innerText = "Create WhatsApp Template";
					submitBtn.innerText = "Save Template";
					document.getElementById("templateForm").scrollIntoView({ behavior: "smooth", block: "start" });
				}
			});

			function aiShowInPhone(text) {
				const time = new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
				aiPhoneBody.innerHTML = `
					<div class="ai-bubble">${aiPillify(text)}<span class="time">${time} ✓✓</span></div>
				`;
			}
		</script>

		@include('owner.components.footer')