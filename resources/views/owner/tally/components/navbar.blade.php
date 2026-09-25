<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">

                        @switch(Route::currentRouteName())

                            @case('owner.dashboard')
                                Dashboard
                                @break

                            {{-- Accountants --}}
                            @case('owner.accountants.index')
                                Accountants List
                                @break

                            @case('owner.accountants.create')
                                Create Accountant
                                @break

                            @case('owner.accountants.edit')
                                Edit Accountant
                                @break

                            {{-- Collectors --}}
                            @case('owner.collectors.index')
                                Collectors List
                                @break

                            @case('owner.collectors.create')
                                Create Collector
                                @break

                            @case('owner.collectors.edit')
                                Edit Collector
                                @break

                            {{-- Subscription --}}
                            @case('owner.subscription')
                                Subscription
                                @break

                            {{-- Tally --}}
                            @case('owner.tally.dashboard')
                                Tally Dashboard
                                @break

                            @case('owner.tally.company.details')
                                Company Details
                                @break

                            @case('owner.tally.company.ledgers')
                                Company Ledgers
                                @break

                            @case('owner.tally.ledger.invoices')
                                Ledger Invoices
                                @break

                            @case('owner.tally.ledger.receipts')
                                Ledger Receipts
                                @break

                            @case('owner.tally.ledger.vouchers')
                                Ledger Vouchers
                                @break

                            {{-- Ledgers --}}
                            @case('owner.ledgers.index')
                                Ledgers List
                                @break

                            @default
                                {{ ucwords(str_replace('.', ' ', Route::currentRouteName())) }}

                        @endswitch

                    </div>
                </div>
                <ul class="navbar-nav header-right">

                    {{-- ================= CLEAR CACHE (moved here from Tally dashboard) ================= --}}
                    <li class="nav-item header-clear-cache">
                        <button type="button" id="clearCacheBtn" class="btn btn-outline-secondary btn-sm header-clear-cache-btn">
                            <i class="fas fa-broom"></i>
                            <span class="d-none d-md-inline ms-1">Clear Cache</span>
                        </button>
                    </li>
                    {{-- ================= /CLEAR CACHE ================= --}}

                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link bell dz-theme-mode" href="javascript:void(0);">
                            <i id="icon-light" class="fas fa-sun"></i>
                            <i id="icon-dark" class="fas fa-moon"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                xmlns="../www.w3.org/2000/svg.html">    
                                <path d="M23.3333 19.8333H23.1187C23.2568 19.4597 23.3295 19.065 23.3333 18.6666V12.8333C23.3294 10.7663 22.6402 8.75902 21.3735 7.12565C20.1068 5.49228 18.3343 4.32508 16.3333 3.80679V3.49996C16.3333 2.88112 16.0875 2.28763 15.6499 1.85004C15.2123 1.41246 14.6188 1.16663 14 1.16663C13.3812 1.16663 12.7877 1.41246 12.3501 1.85004C11.9125 2.28763 11.6667 2.88112 11.6667 3.49996V3.80679C9.66574 4.32508 7.89317 5.49228 6.6265 7.12565C5.35983 8.75902 4.67058 10.7663 4.66667 12.8333V18.6666C4.67053 19.065 4.74316 19.4597 4.88133 19.8333H4.66667C4.35725 19.8333 4.0605 19.9562 3.84171 20.175C3.62292 20.3938 3.5 20.6905 3.5 21C3.5 21.3094 3.62292 21.6061 3.84171 21.8249C4.0605 22.0437 4.35725 22.1666 4.66667 22.1666H23.3333C23.6428 22.1666 23.9395 22.0437 24.1583 21.8249C24.3771 21.6061 24.5 21.3094 24.5 21C24.5 20.6905 24.3771 20.3938 24.1583 20.175C23.9395 19.9562 23.6428 19.8333 23.3333 19.8333Z"
                                    fill="#717579" />
                                <path d="M9.9819 24.5C10.3863 25.2088 10.971 25.7981 11.6766 26.2079C12.3823 26.6178 13.1838 26.8337 13.9999 26.8337C14.816 26.8337 15.6175 26.6178 16.3232 26.2079C17.0288 25.7981 17.6135 25.2088 18.0179 24.5H9.9819Z"
                                    fill="#717579" />
                            </svg>
                            <span class="badge light text-white bg-warning rounded-circle">12</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div id="DZ_W_Notification1" class="widget-media dlab-scroll p-3" style="height:380px;">
                                <ul class="timeline">
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2">
                                                <img alt="image" width="50" src="images/avatar/1.jpg">
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Dr sultads Send you Photo</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2 media-info">
                                                KG
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Resport created successfully</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2 media-success">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Reminder : Treatment Time!</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2">
                                                <img alt="image" width="50" src="images/avatar/1.jpg">
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Dr sultads Send you Photo</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2 media-danger">
                                                KG
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Resport created successfully</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2 media-primary">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Reminder : Treatment Time!</h6>
                                                <small class="d-block">29 July 2020 - 02:26 PM</small>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <a class="all-notification" href="javascript:void(0);">See all notifications
                                <i class="ti-arrow-end"></i>
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
                            <img src="https://media.istockphoto.com/id/2041572395/vector/blank-avatar-photo-placeholder-icon-vector-illustration.jpg?s=612x612&w=0&k=20&c=wSuiu-si33m-eiwGhXiX_5DvKQDHNS--CBLcyuy68n0="
                                width="56" alt="">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="#" class="dropdown-item ai-icon">
                                <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18"
                                    height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span class="ms-2">Profile </span>
                            </a>
                            <a href="#" class="dropdown-item ai-icon">
                                <svg id="icon-inbox" xmlns="http://www.w3.org/2000/svg" class="text-success" width="18"
                                    height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                    </path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <span class="ms-2">Inbox </span>
                            </a>
                            <a href="{{ route('owner.signOut') }}" class="dropdown-item ai-icon">
                                <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18"
                                    height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span class="ms-2">Logout </span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

{{-- ================= CLEAR CACHE CONFIRM MODAL (navbar) ================= --}}
<div class="modal fade" id="clearCacheConfirmModal" tabindex="-1" aria-labelledby="clearCacheConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content header-clear-cache-modal">

            <div class="modal-body text-center pt-4 pb-3 px-4">

                <div class="header-clear-cache-icon mb-3">
                    <i class="fas fa-broom"></i>
                </div>

                <h4 class="mb-2 fw-bold" id="clearCacheConfirmModalLabel">Clear Cache?</h4>
                <p class="text-muted mb-0">
                    This will clear the application cache. Are you sure you want to continue?
                </p>

            </div>

            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" id="confirmClearCacheBtn" class="btn btn-primary flex-fill">
                    <i class="fas fa-broom me-2"></i> Yes, Clear Cache
                </button>
            </div>

        </div>
    </div>
</div>
{{-- ================= /CLEAR CACHE CONFIRM MODAL ================= --}}

{{-- ================= CLEAR CACHE LOADING MODAL (navbar) ================= --}}
<div class="modal fade" id="clearCacheLoadingModal" tabindex="-1"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body text-center py-5">

                <div class="mb-3">
                    <i class="fas fa-broom fa-spin text-primary" style="font-size: 40px;"></i>
                </div>

                <h5 class="mb-0">Clearing cache, please wait...</h5>

            </div>

        </div>
    </div>
</div>
{{-- ================= /CLEAR CACHE LOADING MODAL ================= --}}

<style>
    .header-clear-cache {
        display: flex;
        align-items: center;
        margin-right: 10px;
    }

    .header-clear-cache-btn {
        display: inline-flex;
        align-items: center;
        border-radius: 8px;
        font-weight: 600;
    }

    .header-clear-cache-modal {
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }

    .header-clear-cache-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto;
        border-radius: 50%;
        background: linear-gradient(135deg, #E9E2F8, #d7c9f5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header-clear-cache-icon i {
        font-size: 30px;
        color: #4E3F6B;
    }
</style>

<script>
    $(function () {

        var $clearCacheConfirmModal = $('#clearCacheConfirmModal');
        var $clearCacheLoadingModal = $('#clearCacheLoadingModal');

        // Keep these modals attached directly to body so no parent overflow/position
        // rule ever clips or hides them, regardless of which page includes this header.
        if ($clearCacheConfirmModal.length) $clearCacheConfirmModal.appendTo('body');
        if ($clearCacheLoadingModal.length) $clearCacheLoadingModal.appendTo('body');

        function showModalEl($el, options) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance($el[0], options || {}).show();
            } else if (typeof $.fn.modal !== 'undefined') {
                $el.modal(Object.assign({ show: true }, options || {}));
            } else {
                console.error('Bootstrap JS load nahi hua hai. Check script tag order.');
            }
        }

        function hideModalEl($el) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var instance = bootstrap.Modal.getInstance($el[0]);
                if (instance) instance.hide();
            } else if (typeof $.fn.modal !== 'undefined') {
                $el.modal('hide');
            }
        }

        function showHeaderMessage(html) {
            // Agar current page par #syncMessage (Tally dashboard) available hai to wahi
            // use karo, warna ek chhota top-right toast bana dो.
            var $target = $('#syncMessage');

            if ($target.length) {
                $target.html(html);
                return;
            }

            var $toastWrap = $('#headerClearCacheToastWrap');

            if (!$toastWrap.length) {
                $toastWrap = $('<div id="headerClearCacheToastWrap"></div>').css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    zIndex: 2000,
                    minWidth: '280px'
                }).appendTo('body');
            }

            $toastWrap.html(html);

            setTimeout(function () {
                $toastWrap.find('.alert').alert('close');
            }, 4000);
        }

        // Step 1: Clear Cache button click -> open confirmation modal
        $('#clearCacheBtn').on('click', function () {
            showModalEl($clearCacheConfirmModal);
        });

        // Step 2: Confirm click -> close confirm modal, show loading modal, call API
        $('#confirmClearCacheBtn').on('click', function () {

            hideModalEl($clearCacheConfirmModal);

            showModalEl($clearCacheLoadingModal, {
                backdrop: 'static',
                keyboard: false
            });

            $.ajax({
                url: "{{ route('owner.tally.clear-cache') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    setTimeout(function () {
                        hideModalEl($clearCacheLoadingModal);

                        showHeaderMessage(`
                            <div class="alert alert-success alert-dismissible fade show mt-3">
                                ${response.message || 'Cache cleared successfully.'}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);
                    }, 600);
                },
                error: function (xhr) {
                    let message = 'Cache clear nahi ho paya. Phir try karo.';
                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    setTimeout(function () {
                        hideModalEl($clearCacheLoadingModal);

                        showHeaderMessage(`
                            <div class="alert alert-danger alert-dismissible fade show mt-3">
                                ${message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);
                    }, 600);
                }
            });
        });

    });
</script>