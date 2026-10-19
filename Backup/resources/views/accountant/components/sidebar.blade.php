@php
    $accountant = Auth::guard('accountant')->user();
@endphp
@if ($accountant->business_type === 'tally') 
    <div class="dlabnav">
        <div class="dlabnav-scroll">

            <ul class="metismenu" id="menu">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('accountant.tally.dashboard') }}" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <!-- Receivable -->
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="nav-text">Receivable</span>
                    </a>
                    <!-- ===== LEVEL 2: SUB MENU ===== -->
                    <ul aria-expanded="false">
                        <!-- Track -->
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false">Track</a>
                            <!-- ==== LEVEL 3: SUB-SUB MENU ==== -->
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);">Collector</a></li>
                                <li><a href="javascript:void(0);">Debtor</a></li>
                                <li><a href="javascript:void(0);">Charts</a></li>
                            </ul>
                        </li>

                        <!-- Collect -->
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false">Collect</a>
                            <!-- ==== LEVEL 3: SUB-SUB MENU ==== -->
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);">Invoices Received Interest Pending</a></li>
                                <li><a href="javascript:void(0);">Invoices Pending</a></li>
                            </ul>
                        </li>

                        <!-- Follow Up Allocate -->
                        @if(!empty($company))
                            <li>
                                <a href="{{ route('accountant.tally.followup.hub', ['company' => urlencode($company)]) }}">Follow Up Allocated</a>
                            </li>
                        @endif
                        
                        

                        @if(!empty($company))
                            <!-- Follow Up Action -->
                            <li>
                                <a href="javascript:void(0);" aria-expanded="false">Follow Up Action</a>

                                <ul aria-expanded="false">
                                    <li>
                                        <a href="{{ route('accountant.tally.company.followup-action.telecaller', ['company' => urlencode($company)]) }}">
                                            Telecaller
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('accountant.tally.company.followup-action.call', ['company' => urlencode($company)]) }}">
                                            Call
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('accountant.tally.company.followup-action.whatsapp-message', ['company' => urlencode($company)]) }}">
                                            WhatsApp / Message
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('accountant.tally.company.followup-action.physical-visit', ['company' => urlencode($company)]) }}">
                                            Physical Visit
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('accountant.tally.company.followup-action.escalation', ['company' => urlencode($company)]) }}">
                                            Escalation
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(!empty($company))
                            <!-- Follow Up History (no sub-sub menu, direct link) -->
                            <li>
                                <a href="{{ route('accountant.tally.company.followup_history', ['company' => urlencode($company)]) }}">Follow Up History</a>
                            </li>
                        @endif

                    </ul>
                </li>

                <!-- Payable -->
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="nav-text">Payable</span>
                    </a>
                    <!-- ===== LEVEL 2: SUB MENU ===== -->
                    <ul aria-expanded="false">
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false">Track</a>
                            <!-- ==== LEVEL 3: SUB-SUB MENU ==== -->
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);">Total</a></li>
                                <li><a href="javascript:void(0);">Creditor</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- Net (no submenu at all) -->
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-balance-scale"></i>
                        <span class="nav-text">Net</span>
                    </a>
                </li>


                <li>
                    <a href="{{ route('accountant.signOut') }}" aria-expanded="false">
                        <i class="fas fa-balance-scale"></i>
                        <span class="nav-text">Sign Out</span>
                    </a>
                </li>
                {{-- Assign Ledgers --}}
            </ul>
        </div>
    </div>
@elseif ($accountant->business_type === 'manual')
    <div class="dlabnav">
        <div class="dlabnav-scroll">
            <ul class="metismenu" id="menu">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('accountant.manual.dashboard') }}" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <!-- Receivable -->
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="nav-text">Receivable</span>
                    </a>
                    <!-- ===== LEVEL 2: SUB MENU ===== -->
                    <ul aria-expanded="false">
                        <!-- Track -->
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false">Track</a>
                            <!-- ==== LEVEL 3: SUB-SUB MENU ==== -->
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);">Collector</a></li>
                                <li><a href="javascript:void(0);">Debtor</a></li>
                                <li><a href="javascript:void(0);">Charts</a></li>
                            </ul>
                        </li>
                        <!-- Collect -->
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false">Collect</a>
                            <!-- ==== LEVEL 3: SUB-SUB MENU ==== -->
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);">Invoices Received Interest Pending</a></li>
                                <li><a href="javascript:void(0);">Invoices Pending</a></li>
                            </ul>
                        </li>
                        <!-- Follow Up Allocate -->
                        <li>
                            <a href="{{ route('accountant.manual.followup.hub') }}">Follow Up Allocated</a>
                        </li>
                        <!-- Follow Up Action -->
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false">Follow Up Action</a>

                            <ul aria-expanded="false">
                                <li>
                                    <a href="{{ route('accountant.manual.followup-action.telecaller') }}">
                                        Telecaller
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('accountant.manual.followup-action.call') }}">
                                        Call
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('accountant.manual.followup-action.whatsapp-message') }}">
                                        WhatsApp / Message
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('accountant.manual.followup-action.physical-visit') }}">
                                        Physical Visit
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('accountant.manual.followup-action.escalation') }}">
                                        Escalation
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- Follow Up History (no sub-sub menu, direct link) -->
                        <li>
                            <a href="{{ route('accountant.manual.followup_history') }}">Follow Up History</a>
                        </li>
                    </ul>
                </li>

                <!-- Payable -->
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="nav-text">Payable</span>
                    </a>
                    <!-- ===== LEVEL 2: SUB MENU ===== -->
                    <ul aria-expanded="false">
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false">Track</a>
                            <!-- ==== LEVEL 3: SUB-SUB MENU ==== -->
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);">Total</a></li>
                                <li><a href="javascript:void(0);">Creditor</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- Net (no submenu at all) -->
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-balance-scale"></i>
                        <span class="nav-text">Net</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('accountant.signOut') }}" aria-expanded="false">
                        <i class="fas fa-balance-scale"></i>
                        <span class="nav-text">Sign Out</span>
                    </a>
                </li>
                {{-- Assign Ledgers --}}
            </ul>
        </div>
    </div>
@endif

<style>
    /* Level 1: Main Menu Items */
    #menu > li > a {
        font-weight: 600;
    }

    /* Level 2: Sub Menu (first nested ul) */
    #menu > li > ul {
        border-left: 3px solid #4a90e2;
        margin-left: 10px;
    }
    #menu > li > ul > li > a {
        padding-left: 25px;
        background: #fafafa;
        color: #333;
    }

    /* Level 3: Sub-Sub Menu (nested inside sub menu) */
    #menu > li > ul > li > ul {
        border-left: 3px solid #f5a623;
        margin-left: 15px;
    }
    #menu > li > ul > li > ul > li > a {
        padding-left: 40px;
        background: #fff;
        color: #666;
        font-size: 0.9em;
    }
</style>