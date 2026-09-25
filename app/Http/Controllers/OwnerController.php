<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Owner;
use App\Models\VoucherMapping;
use App\Models\Accountant;
use App\Models\Collector;
use App\Models\TallyConnection;
use App\Models\LedgerCollector;
use App\Models\TallyLedger;
use App\Models\TallyVoucher;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappTemplateParameter;
use Illuminate\Support\Facades\Hash;
use App\Services\TallyService;
use Illuminate\Support\Facades\Auth;
use App\Models\TallyCompany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Models\OwnerBankDetail;
use Illuminate\Support\Facades\Artisan;

class OwnerController extends Controller
{
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return response()->json(['message' => 'All caches cleared successfully.']);
    }
    
    protected TallyService $tally;

    public function __construct(TallyService $tally)
    {
        $this->tally = $tally;
    }
 
    public function subscription()
    {
        return view('owner.tally.subscription');
    }


    public function subscribe(Request $request)
    {
        $owner = auth('owner')->user();

        $owner->update([
            'is_subscribed' => "true"
        ]);

        return redirect()->route('owner.tally.dashboard')->with('success', 'Subscription activated successfully.');
    }


    // Register form submit
    public function registerOwner(Request $request)
    {
        $request->validate([
            'owner_name' => 'required',
            'email' => 'required|email|unique:rms_owners,email',
            'phone' => 'nullable',
            'business_name' => 'required',
            'business_type' => 'required',
            'address' => 'nullable',
            'password' => 'required|min:6|confirmed',
        ]);

        Owner::create([
            'owner_name' => $request->owner_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'is_subscribed' => "false",
            'subscription_expiry' => null,
        ]);

        return redirect()->route('owner.tally.login')->with('success', 'Registration completed successfully. Please login.');
    }


    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'account_type' => 'required|in:tally,manual',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => 'active',
        ];

        if (Auth::guard('owner')->attempt($credentials, $request->boolean('remember'))) {
            $owner = Auth::guard('owner')->user();
    
            if ($owner->business_type !== $request->account_type) {
                Auth::guard('owner')->logout();

                Log::warning('Owner login type mismatch', [
                    'email'         => $owner->email,
                    'selected_type' => $request->account_type,
                    'actual_type'   => $owner->business_type,
                    'time'          => now(),
                ]);

                session()->flash('error', 'This account is not registered under the selected type.');

                return back()->withInput($request->only('email', 'account_type'));
            }

            Log::info('Owner login successful', [
                'email' => $owner->email,
                'name'  => $owner->owner_name,
                'type'  => $owner->business_type,
                'time'  => now(),
            ]);

            if ($owner->is_subscribed !== 'true') {
                return redirect()->route('owner.subscription')
                    ->with('error', 'Please buy a subscription plan first.');
            }

            if ($owner->business_type === 'manual') {
                return redirect()->route('owner.manual.dashboard');
            }

           
            if ($owner->business_type === 'tally') {
                return redirect()->route('owner.tally.dashboard');
            }
        }

        Log::warning('Owner login failed', [
            'email' => $request->email,
            'time'  => now(),
        ]);

        session()->flash('error', 'Either Email/Password is incorrect');

        return back()->withInput($request->only('email', 'account_type'));
    }



    public function signOut()
    {
        $owner = Auth::guard('owner')->user();
        if ($owner) {
            Log::info('Owner logged out', [
                'email' => $owner->email,
                'name' => $owner->owner_name,
                'time' => now()
            ]);
        }
        Auth::guard('owner')->logout(); 
        session()->flash('success', 'You have been logged out successfully.');

        return redirect()->route('owner.login'); 
    }



    public function connect(Request $request)
    {
        $owner = Auth::guard('owner')->user();

        if (!$owner) {
            return response()->json([
                'message' => 'Owner not authenticated. Please login again.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'tailscale_ip' => ['required'],
            'port'         => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {

            $existingConnection = TallyConnection::where('owner_id', $owner->id)->first();
            $isUpdate = (bool) $existingConnection;

            $tallyConnection = TallyConnection::updateOrCreate(
                ['owner_id' => $owner->id],
                [
                    'tailscale_ip' => $request->tailscale_ip,
                    'port'         => $request->port,
                    'status'       => 'connected',
                ]
            );

            return response()->json([
                'message' => $isUpdate
                    ? 'Tally connection updated successfully.'
                    : 'Tally connected successfully.',
                'data'    => $tallyConnection,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Tally connect failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong while connecting to Tally: ' . $e->getMessage(),
            ], 500);
        }
    }

    
    public function dashboard()
    {
        return view('owner.dashboard');
    }


    public function accountants()
    {
        $accountants = Accountant::all();
        return view('owner.tally.accountant.accountants-list', compact('accountants'));
    }


    public function changeStatus(Request $request)
    {
        $accountant = Accountant::findOrFail($request->id);
        $accountant->status = $accountant->status == 'active'
            ? 'inactive'
            : 'active';
        $accountant->save();
        return redirect()->route('owner.tally.accountants.index')->with('success', 'Accountant status updated successfully!');
    }


    public function createAccountant()
    {
        return view('owner.tally.accountant.accountant-create');
    }


    public function storeAccountant(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:rms_accountants,email',
            'phone' => 'required|min:10|max:15',
            'address' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        Accountant::create([
            'owner_id' => auth('owner')->user()->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'pass' => $request->password,
            'status' => 'active',
        ]);

        return redirect()->route('owner.tally.accountants.index')->with('success', 'Accountant created successfully!');
    }



    public function editAccountant($id)
    {
        $accountant = Accountant::find($id);
        return view('owner.tally.accountant.accountant-edit', compact('accountant'));
    }



    public function updateAccountant(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:rms_accountants,email,' . $id,
            'phone' => 'required|digits:10|unique:rms_accountants,phone,' . $id,
            'address' => 'required|string|max:255',
        ]);


        $accountant = Accountant::findOrFail($id);

        $accountant->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('owner.tally.accountants.index')->with('success', 'Accountant updated successfully!');
    }


    public function collectors()
    {
        $collectors = Collector::select('rms_accountants.*', 'rms_collectors.*', 'rms_accountants.name as accountant_name')
            ->from('rms_collectors')
            ->leftJoin('rms_accountants', 'rms_collectors.accountant_id', '=', 'rms_accountants.id')
            ->get();

        return view('owner.tally.collectors.collectors-list', compact('collectors'));
    }


    public function createCollector()
    {
        return view('owner.tally.collectors.collector-create');
    }


    public function storeCollector(Request $request)
    {
        $request->validate([
            'accountant_id' => 'required|exists:rms_accountants,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:rms_collectors,email',
            'phone' => 'required|min:10|max:15|unique:rms_collectors,phone',
            'address' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        Collector::create([
            'accountant_id' => $request->accountant_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'pass' => $request->password,
            'status' => 'active',
        ]);

        return redirect()
            ->route('owner.tally.collectors.index')
            ->with('success', 'Collector created successfully!');
    }


    public function assignAccountant(Request $request)
    {
        $request->validate([
            'accountant_id' => 'required|exists:rms_accountants,id',
            'collector_ids' => 'required|string',
        ]);

        $collectorIds = explode(',', $request->collector_ids);

        Collector::whereIn('id', $collectorIds)->update([
            'accountant_id' => $request->accountant_id,
        ]);

        return redirect()->back()->with('success', 'Accountant assigned successfully.');
    }


    public function changeStatusCollector(Request $request)
    {
        $collector = Collector::findOrFail($request->id);
        $collector->status = $collector->status == 'active'
            ? 'inactive'
            : 'active';
        $collector->save();

        return redirect()->route('owner.tally.collectors.index')->with('success', 'Collector status updated successfully!');
    }


    public function editCollector($id)
    {
        $collector = Collector::find($id);
        return view('owner.tally.collectors.collector-edit', compact('collector'));
    }


    public function updateCollector(Request $request, $id)
    {
        $request->validate([
            'accountant_id' => 'required|exists:rms_accountants,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:rms_collectors,email,' . $id,
            'phone' => 'required|digits:10|unique:rms_collectors,phone,' . $id,
            'address' => 'required|string|max:255',
        ]);

        $collector = Collector::findOrFail($id);

        $collector->update([
            'accountant_id' => $request->accountant_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('owner.tally.collectors.index')->with('success', 'Collector updated successfully!');
    }


   
    public function company()
    {
        try {
            $owner = Auth::guard('owner')->user();

            $companies = TallyCompany::where('owner_id', $owner->id)
                ->orderBy('company_name')
                ->get();

            $tallyConnectionRecord = TallyConnection::where(
                'owner_id',
                $owner->id
            )->first();

            $tallyConnected = $tallyConnectionRecord
                && !empty($tallyConnectionRecord->tailscale_ip)
                && !empty($tallyConnectionRecord->port);

            return view(
                'owner.tally.tally.index',
                compact(
                    'companies',
                    'tallyConnected'
                )
            );

        } catch (\Exception $e) {

            return view('owner.tally.tally.index', [
                'companies' => collect(),
                'tallyConnected' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function saveBankDetails(Request $request)
    {
        $owner = Auth::guard('owner')->user();

        $validator = Validator::make($request->all(), [
            'account_holder_name' => ['required', 'string', 'max:255'],
            'bank_name'           => ['required', 'string', 'max:255'],
            'account_number'      => ['required', 'string', 'max:30'],
            'ifsc_code'           => ['required', 'string', 'max:15'],
            'upi'         => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $wasAlreadyAdded = OwnerBankDetail::where('owner_id', $owner->id)->exists();

        OwnerBankDetail::updateOrCreate(
            ['owner_id' => $owner->id],
            [
                'account_holder_name' => $request->account_holder_name,
                'bank_name'           => $request->bank_name,
                'account_number'      => $request->account_number,
                'ifsc_code'           => strtoupper($request->ifsc_code),
                'branch_name'         => $request->branch_name,
                'upi'                 => $request->upi,]
        );

        return response()->json([
            'message' => $wasAlreadyAdded
                ? 'Bank details updated successfully.'
                : 'Bank details saved successfully.',
        ]);
    }
    

    public function syncAll()
    {
        try {
            $owner = Auth::guard('owner')->user();

            $hadExistingLedgers = TallyLedger::where('owner_id', $owner->id)->exists();

            $companies = $this->tally->getCompanies();
            $now = now();
            $summary = [];

            foreach ($companies as $companyData) {

                $companyName = $companyData['name'];

                DB::transaction(function () use (
                    $companyName,
                    $owner,
                    $now,
                    &$summary
                ) {

                    $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                        ->where('company_name', $companyName)
                        ->first();

                    if ($tallyCompany) {

                        $tallyCompany->update([
                            'last_synced_at' => $now,
                        ]);

                    } else {

                        $tallyCompany = TallyCompany::create([
                            'owner_id'          => $owner->id,
                            'company_name'      => $companyName,
                            'last_synced_at'    => $now,
                        ]);

                        $uniqueCompanyId = $this->buildUniqueId(
                            $companyName,
                            $tallyCompany->id
                        );

                        $tallyCompany->update([
                            'unique_company_id' => $uniqueCompanyId,
                        ]);
                    }

                    $voucherTypeXml = $this->tally->getVoucherTypes(
                        $companyName
                    );

                    $voucherTypeXmlObj = simplexml_load_string(
                        $voucherTypeXml
                    );

                    $voucherMappingCount = 0;

                    if ($voucherTypeXmlObj !== false) {

                        $voucherTypeNodes = $voucherTypeXmlObj->xpath(
                            "//*[local-name()='VOUCHERTYPE']"
                        );

                        if (!empty($voucherTypeNodes)) {

                            foreach ($voucherTypeNodes as $node) {

                                $voucherTypeName = '';

                                if (isset($node->NAME)) {
                                    $voucherTypeName = trim(
                                        (string) $node->NAME
                                    );
                                }

                                if (
                                    empty($voucherTypeName) &&
                                    isset($node['NAME'])
                                ) {
                                    $voucherTypeName = trim(
                                        (string) $node['NAME']
                                    );
                                }

                                if (empty($voucherTypeName)) {
                                    continue;
                                }

                                $existingMapping = VoucherMapping::where('company', $companyName)
                                    ->where('voucher_type', $voucherTypeName)
                                    ->first();

                                if (!$existingMapping) {

                                    VoucherMapping::create([
                                        'company'      => $companyName,
                                        'voucher_type' => $voucherTypeName,
                                        'mapped_to'    => null,
                                        'created_at'   => $now,
                                        'updated_at'   => $now,
                                    ]);

                                    $voucherMappingCount++;
                                }
                            }
                        }
                    }
                
                    $ledgerXml = $this->tally->getLedgers($companyName);

                    $ledgers = $this->tally->parseLedgersXml($ledgerXml);

                    if ($ledgers) {

                        foreach ($ledgers as $ledger) {

                            if (!empty($ledger['master_id'])) {

                                $tallyLedger = TallyLedger::where('owner_id', $owner->id)
                                    ->where('tally_company_id', $tallyCompany->id)
                                    ->where('master_id', $ledger['master_id'])
                                    ->first();

                            } else {

                                $tallyLedger = TallyLedger::where('owner_id', $owner->id)
                                    ->where('tally_company_id', $tallyCompany->id)
                                    ->where('ledger_name', $ledger['ledger_name'])
                                    ->whereNull('master_id')
                                    ->first();
                            }

                            if ($tallyLedger) {

                                $updateData = [
                                    'ledger_name'                   => $ledger['ledger_name'],
                                    'master_id'                     => $ledger['master_id'] ?: $tallyLedger->master_id,
                                    'ledger_email'                  => $ledger['ledger_email'],
                                    'parent'                        => $ledger['parent'],
                                    'opening_balance'               => $ledger['opening_balance'],
                                    'closing_balance'               => $ledger['closing_balance'],
                                    'maintain_bill_by_bill'         => $ledger['maintain_bill_by_bill'],
                                    'activate_interest_calculation' => $ledger['activate_interest_calculation'],
                                    'balance_synced_at'             => $now,
                                    'updated_at'                    => $now,
                                ];

                                // mobile_number: sirf pehli baar set karo (source abhi tak null hai).
                                // Ek baar 'tally' ya 'default' set ho gaya, to future syncs isko touch nahi karenge.
                                if (empty($tallyLedger->ledger_mobile_number_source)) {
                                    $updateData['ledger_mobile_number']        = $ledger['ledger_mobile_number'];
                                    $updateData['ledger_mobile_number_source'] = !empty($ledger['ledger_mobile_number']) ? 'tally' : null;
                                }

                                // credit_period: agar manually 'default' set hai to overwrite mat karo
                                if ($tallyLedger->credit_period_source !== 'default') {
                                    $updateData['credit_period']        = $ledger['credit_period']. ' Days';
                                    $updateData['credit_period_source'] = !empty($ledger['credit_period']) ? 'tally' : null;
                                }

                                // interest_rate: agar manually 'default' set hai to overwrite mat karo
                                if ($tallyLedger->interest_rate_source !== 'default') {
                                    $updateData['interest_rate']        = $ledger['interest_rate'];
                                    $updateData['interest_style']       = $ledger['interest_style'];
                                    $updateData['interest_rate_source'] = !empty($ledger['interest_rate']) ? 'tally' : null;
                                }

                                $tallyLedger->update($updateData);

                            } else {

                                $tallyLedger = TallyLedger::create([
                                    'master_id'                     => $ledger['master_id'],
                                    'owner_id'                      => $owner->id,
                                    'tally_company_id'              => $tallyCompany->id,
                                    'ledger_name'                   => $ledger['ledger_name'],
                                    'ledger_email'                  => $ledger['ledger_email'],
                                    'ledger_mobile_number'          => $ledger['ledger_mobile_number'],
                                    'ledger_mobile_number_source'   => !empty($ledger['ledger_mobile_number']) ? 'tally' : null,
                                    'parent'                        => $ledger['parent'],
                                    'opening_balance'               => $ledger['opening_balance'],
                                    'closing_balance'               => $ledger['closing_balance'],
                                    'credit_period'                 => $ledger['credit_period'],
                                    'credit_period_source'          => !empty($ledger['credit_period']) ? 'tally' : null,
                                    'interest_rate'                 => $ledger['interest_rate'],
                                    'interest_style'                => $ledger['interest_style'],
                                    'interest_rate_source'          => !empty($ledger['interest_rate']) ? 'tally' : null,
                                    'maintain_bill_by_bill'         => $ledger['maintain_bill_by_bill'],
                                    'activate_interest_calculation' => $ledger['activate_interest_calculation'],
                                    'balance_synced_at'             => $now,
                                    'created_at'                    => $now,
                                    'updated_at'                    => $now,
                                ]);

                                $uniqueLedgerId = $this->buildUniqueId(
                                    $ledger['ledger_name'],
                                    $tallyLedger->id
                                );

                                $tallyLedger->update([
                                    'unique_ledger_id' => $uniqueLedgerId,
                                ]);
                            }
                        }
                    }

                    $ledgersByName = TallyLedger::where('owner_id', $owner->id)
                        ->where('tally_company_id', $tallyCompany->id)
                        ->get(['id', 'ledger_name', 'credit_period'])
                        ->keyBy('ledger_name');
                
                    $voucherXml = $this->tally->getVouchersForCompany($companyName);

                    $vouchers = $this->tally->parseVouchersXml($voucherXml);

                    $voucherCount = 0;
                    $skipped = 0;

                    foreach ($vouchers as $voucherData) {

                        $ledgerRecord =
                            $ledgersByName[
                                $voucherData['ledger_match_name']
                            ] ?? null;

                        if (!$ledgerRecord) {

                            $skipped++;

                            Log::warning(
                                'Tally voucher skipped - party ledger not found',
                                [
                                    'company' => $companyName,
                                    'party_ledger_name' => $voucherData['ledger_match_name'],
                                    'voucher_number' => $voucherData['voucher_number'],
                                ]
                            );

                            continue;
                        }

                        $ledgerId = $ledgerRecord->id;

                        $creditPeriod = $voucherData['credit_period'] ?? $ledgerRecord->credit_period;

                        $dueDate = null;
                        if ($voucherData['date'] && $creditPeriod) {
                            $dueDate = Carbon::parse($voucherData['date'])->addDays((int) $creditPeriod);
                        }
                        
                        if (!empty($voucherData['master_id'])) {

                            $tallyVoucher = TallyVoucher::where('owner_id', $owner->id)
                                ->where('tally_company_id', $tallyCompany->id)
                                ->where('master_id', $voucherData['master_id'])
                                ->first();

                        } else {

                            $tallyVoucher = TallyVoucher::where('owner_id', $owner->id)
                                ->where('tally_company_id', $tallyCompany->id)
                                ->where('voucher_number', $voucherData['voucher_number'])
                                ->where('voucher_type', $voucherData['voucher_type'])
                                ->whereNull('master_id')
                                ->first();
                        }

                        if ($tallyVoucher) {

                            $tallyVoucher->update([
                                'master_id'         => $voucherData['master_id'] ?: $tallyVoucher->master_id,
                                'ledger_id'         => $ledgerId,
                                'date'              => $voucherData['date'],
                                'voucher_number'    => $voucherData['voucher_number'],
                                'party_ledger_name' => $voucherData['party_ledger_name'],
                                'amount'            => $voucherData['amount'],
                                'credit_period'     => $creditPeriod,
                                'due_date'          => $dueDate,
                                'updated_at'        => $now,
                            ]);

                        } else {

                            $tallyVoucher = TallyVoucher::create([
                                'owner_id'          => $owner->id,
                                'tally_company_id'  => $tallyCompany->id,
                                'ledger_id'         => $ledgerId,
                                'master_id'         => $voucherData['master_id'],
                                'date'              => $voucherData['date'],
                                'voucher_number'    => $voucherData['voucher_number'],
                                'voucher_type'      => $voucherData['voucher_type'],
                                'party_ledger_name' => $voucherData['party_ledger_name'],
                                'amount'            => $voucherData['amount'],
                                'credit_period'     => $creditPeriod,
                                'due_date'          => $dueDate,
                                'created_at'        => $now,
                                'updated_at'        => $now,
                            ]);

                            $uniqueVoucherId = $this->buildVoucherUniqueId(
                                $companyName,
                                $voucherData['party_ledger_name'],
                                $voucherData['voucher_type'],
                                $tallyVoucher->id
                            );

                            Log::info('Voucher credit period trace', [
                                'voucher_number' => $voucherData['voucher_number'],
                                'master_id'      => $voucherData['master_id'],
                                'ledger_name'    => $ledgerRecord->ledger_name,
                                'ledger_credit_period' => $ledgerRecord->credit_period,
                            ]);

                            $tallyVoucher->update([
                                'unique_voucher_id' => $uniqueVoucherId,
                            ]);
                        }

                        $voucherCount++;
                    }

                    $summary[$companyName] = [
                        'unique_company_id' => $tallyCompany->unique_company_id,
                        'ledgers' => count($ledgers),
                        'vouchers' => $voucherCount,
                        'vouchers_skipped' => $skipped,
                    ];
                });
            }

            session([
                'last_sync' => $now
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tally Sync Completed',
                'data' => [
                    'companies' => $companies,
                    'summary' => $summary,
                ],
                'last_sync' => $now->format('d M Y H:i:s'),

                'show_defaults_prompt' => !$hadExistingLedgers,
            ]);

        } catch (\Exception $e) {

            Log::error(
                'Tally Sync Failed',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),

            ], 500);
        }
    }

    public function applySyncDefaults(Request $request)
{
    $owner = Auth::guard('owner')->user();

    $validator = Validator::make($request->all(), [
        'debtor.mobile_number'   => ['nullable', 'string', 'max:20'],
        'debtor.credit_period'   => ['nullable', 'integer', 'min:0'],
        'debtor.interest_rate'   => ['nullable', 'numeric', 'min:0'],
        'debtor.balance_limit'   => ['nullable', 'numeric', 'min:0'],

        'creditor.mobile_number' => ['nullable', 'string', 'max:20'],
        'creditor.credit_period' => ['nullable', 'integer', 'min:0'],
        'creditor.interest_rate' => ['nullable', 'numeric', 'min:0'],
        'creditor.balance_limit' => ['nullable', 'numeric', 'min:0'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422);
    }

    $debtorMobileUpdated    = 0;
    $debtorCreditUpdated    = 0;
    $debtorInterestUpdated  = 0;
    $debtorBalanceUpdated   = 0;

    $creditorMobileUpdated   = 0;
    $creditorCreditUpdated   = 0;
    $creditorInterestUpdated = 0;
    $creditorBalanceUpdated  = 0;

    // ================= DEBTORS =================

    if ($request->filled('debtor.mobile_number')) {
        $debtorMobileUpdated = TallyLedger::where('owner_id', $owner->id)
            ->where('parent', 'Sundry Debtors')
            ->whereNull('ledger_mobile_number_source')
            ->update([
                'ledger_mobile_number'        => $request->input('debtor.mobile_number'),
                'ledger_mobile_number_source' => 'default',
                'updated_at'                  => now(),
            ]);
    }

    if ($request->filled('debtor.credit_period')) {
        $debtorCreditUpdated = TallyLedger::where('owner_id', $owner->id)
            ->where('parent', 'Sundry Debtors')
            ->whereNull('credit_period_source')
            ->update([
                'credit_period'        => $request->input('debtor.credit_period') . ' Days',
                'credit_period_source' => 'default',
                'updated_at'           => now(),
            ]);
    }

    if ($request->filled('debtor.interest_rate')) {
        $debtorInterestUpdated = TallyLedger::where('owner_id', $owner->id)
            ->where('parent', 'Sundry Debtors')
            ->whereNull('interest_rate_source')
            ->update([
                'interest_rate'        => $request->input('debtor.interest_rate'),
                'interest_rate_source' => 'default',
                'updated_at'           => now(),
            ]);
    }

    if ($request->filled('debtor.balance_limit')) {
        $debtorBalanceUpdated = TallyLedger::where('owner_id', $owner->id)
            ->where('parent', 'Sundry Debtors')
            ->whereNull('balance_limit_source')
            ->update([
                'balance_limit'        => $request->input('debtor.balance_limit'),
                'balance_limit_source' => 'default',
                'updated_at'           => now(),
            ]);
    }

    // ================= CREDITORS =================

    if ($request->filled('creditor.mobile_number')) {
        $creditorMobileUpdated = TallyLedger::where('owner_id', $owner->id)
            ->where('parent', 'Sundry Creditors')
            ->whereNull('ledger_mobile_number_source')
            ->update([
                'ledger_mobile_number'        => $request->input('creditor.mobile_number'),
                'ledger_mobile_number_source' => 'default',
                'updated_at'                  => now(),
            ]);
    }

    if ($request->filled('creditor.credit_period')) {
        $creditorCreditUpdated = TallyLedger::where('owner_id', $owner->id)
            ->where('parent', 'Sundry Creditors')
            ->whereNull('credit_period_source')
            ->update([
                'credit_period'        => $request->input('creditor.credit_period') . ' Days',
                'credit_period_source' => 'default',
                'updated_at'           => now(),
            ]);
    }

    if ($request->filled('creditor.interest_rate')) {
        $creditorInterestUpdated = TallyLedger::where('owner_id', $owner->id)
            ->where('parent', 'Sundry Creditors')
            ->whereNull('interest_rate_source')
            ->update([
                'interest_rate'        => $request->input('creditor.interest_rate'),
                'interest_rate_source' => 'default',
                'updated_at'           => now(),
            ]);
    }

    if ($request->filled('creditor.balance_limit')) {
        $creditorBalanceUpdated = TallyLedger::where('owner_id', $owner->id)
            ->where('parent', 'Sundry Creditors')
            ->whereNull('balance_limit_source')
            ->update([
                'balance_limit'        => $request->input('creditor.balance_limit'),
                'balance_limit_source' => 'default',
                'updated_at'           => now(),
            ]);
    }

    return response()->json([
        'success' => true,
        'message' => "Defaults applied — Debtors: {$debtorMobileUpdated} mobile, {$debtorCreditUpdated} credit period, {$debtorInterestUpdated} interest rate, {$debtorBalanceUpdated} balance limit | "
                   . "Creditors: {$creditorMobileUpdated} mobile, {$creditorCreditUpdated} credit period, {$creditorInterestUpdated} interest rate, {$creditorBalanceUpdated} balance limit.",
    ]);
}

    private function buildUniqueId(string $name, int $primaryId, int $minDigits = 2): string
    {
        $alphaOnly = strtoupper(preg_replace('/[^a-zA-Z]/', '', $name));

        $prefix = str_pad(substr($alphaOnly, 0, 4), 4, 'X');

        $idPart = str_pad((string) $primaryId, $minDigits, '0', STR_PAD_LEFT);

        return $prefix . $idPart;
    }

    private function buildVoucherUniqueId(
        string $companyName,
        string $ledgerName,
        string $voucherType,
        int $primaryId,
        int $minDigits = 2
    ): string {
        $extractPart = function (string $name) {

            $alphaOnly = strtoupper(preg_replace('/[^a-zA-Z]/', '', $name));

            return str_pad(substr($alphaOnly, 0, 3), 3, 'X');
        };

        $companyPart = $extractPart($companyName);
        $ledgerPart  = $extractPart($ledgerName);
        $voucherPart = $extractPart($voucherType);

        $idPart = str_pad((string) $primaryId, $minDigits, '0', STR_PAD_LEFT);

        return $companyPart . $ledgerPart . $voucherPart . $idPart;
    }

    public function companyDetails($company)
    {
        $company = urldecode($company);
        return view(
            'owner.tally.company-details',
            compact('company')
        );
    }

    private function resolveDueDate($voucherDueDate, Carbon $voucherDate, int $creditPeriod): Carbon
    {
        if (!empty($voucherDueDate)) {
            try {
                $d = Carbon::parse($voucherDueDate)->startOfDay();
                if ($d->year > 2000) {   // '0000-00-00' jaisi junk values ignore
                    return $d;
                }
            } catch (\Throwable $e) {
                // credit period pe fallback
            }
        }
    
        return $voucherDate->copy()->startOfDay()->addDays($creditPeriod);
    }
 
    private function buildOpenInvoices($rows, string $under, int $creditPeriod, Carbon $today)
    {
        $isCreditor = $under === 'Sundry Creditors';
    
        $invoices = $rows
            ->filter(fn ($r) => $isCreditor
                ? ($r['mapped_type_low'] === 'purchase' && $r['credit'] > 0)
                : ($r['mapped_type_low'] === 'sales' && $r['debit'] > 0))
            ->sortBy(fn ($r) => Carbon::parse($r['date'])->timestamp)   // purana pehle
            ->values();
    
        // Settlement pool: receipts (debtor) / payments (creditor)
        $pool = (float) $rows
            ->filter(fn ($r) => $isCreditor
                ? (str_contains($r['voucher_type_low'], 'payment') && $r['debit'] > 0)
                : (str_contains($r['voucher_type_low'], 'receipt') && $r['credit'] > 0))
            ->sum(fn ($r) => $isCreditor ? $r['debit'] : $r['credit']);
    
        return $invoices
            ->map(function ($r) use (&$pool, $isCreditor, $creditPeriod, $today) {
                $original = (float) ($isCreditor ? $r['credit'] : $r['debit']);
                $cleared  = min($pool, $original);
                $pool    -= $cleared;
                $pending  = round($original - $cleared, 2);
    
                $voucherDate = Carbon::parse($r['date'])->startOfDay();
                $dueDate     = $this->resolveDueDate($r['due_date'] ?? null, $voucherDate, $creditPeriod);
    
                return [
                    'date'           => $voucherDate->toDateString(),
                    'due_date'       => $dueDate->toDateString(),
                    'voucher_number' => $r['voucher_number'] ?? null,
                    'days'           => (int) $dueDate->diffInDays($today, false), // +ve = overdue
                    'original'       => $original,
                    'cleared'        => round($cleared, 2),
                    'pending'        => $pending,
                ];
            })
            ->filter(fn ($i) => $i['pending'] > 0.009)   // fully cleared hata do
            ->values();
    }
 
 
    public function companyLedgers($company)
    {
        try {
            $com     = $company; // raw (still-encoded) company value, VoucherMapping lookup ke liye
            $company = urldecode($company);
            $owner   = Auth::guard('owner')->user();
    
            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->first();
    
            $ledgerModels = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->orderBy('ledger_name')
                ->get();
    
            $vouchersByLedger = TallyVoucher::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->whereIn('ledger_id', $ledgerModels->pluck('id'))
                ->get()
                ->groupBy('ledger_id');
    
            $voucherMappings = VoucherMapping::where('company', $com)
                ->pluck('mapped_to', 'voucher_type')
                ->toArray();
    
            $today = Carbon::now()->startOfDay();
    
            $ledgers = $ledgerModels->map(function ($ledger) use ($vouchersByLedger, $today, $voucherMappings) {
                $under = $ledger->parent;
    
                // due_date aur voucher_number bhi saath me
                $vouchers = $vouchersByLedger->get($ledger->id, collect())
                    ->map(fn ($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                        'due_date'       => $v->due_date ?? null,
                        'voucher_number' => $v->voucher_number,
                    ]);
    
                $isReceipt = fn ($v) => str_contains($v['voucher_type_low'], 'receipt');
                $isPayment = fn ($v) => str_contains($v['voucher_type_low'], 'payment');
    
                $sale = $purchase = $otherDebits = $otherCredits = $receipts = $payments = 0.0;
    
                if ($under === 'Sundry Creditors') {
                    $purchase     = (float) $vouchers->filter(fn ($v) => $v['mapped_type_low'] === 'purchase' && $v['credit'] > 0)->sum('credit');
                    $otherCredits = (float) $vouchers->filter(fn ($v) => $v['mapped_type_low'] !== 'purchase' && $v['credit'] > 0)->sum('credit');
                    $payments     = (float) $vouchers->filter(fn ($v) => $isPayment($v) && $v['debit'] > 0)->sum('debit');
                } else {
                    $sale        = (float) $vouchers->filter(fn ($v) => $v['mapped_type_low'] === 'sales' && $v['debit'] > 0)->sum('debit');
                    $otherDebits = (float) $vouchers->filter(fn ($v) => $v['mapped_type_low'] !== 'sales' && $v['debit'] > 0)->sum('debit');
                    $receipts    = (float) $vouchers->filter(fn ($v) => $isReceipt($v) && $v['credit'] > 0)->sum('credit');
                }
    
                $creditPeriod = (int) ($ledger->credit_period ?? 0);
    
                // Receipts ke against clear na hue invoices -> Balance Due / Not Due
                $openInvoices = $this->buildOpenInvoices($vouchers, $under, $creditPeriod, $today);
    
                $due    = (float) $openInvoices->filter(fn ($i) => $i['days'] > 0)->sum('pending');
                $notDue = (float) $openInvoices->filter(fn ($i) => $i['days'] <= 0)->sum('pending');
    
                $balance = (float) ($ledger->closing_balance ?? 0);
    
                return [
                    'unique_id' => $ledger->unique_ledger_id,
                    'name'      => $ledger->ledger_name,
                    'under'     => $under,
                    'mobile'    => $ledger->ledger_mobile_number,
    
                    'balance'       => $balance,
                    'due'           => $due,
                    'not_due'       => $notDue,
                    'target'        => $ledger->target ?? 0,
                    'sale'          => $sale,

                    'purchase'      => $purchase,
                    'other_debits'  => $otherDebits,
                    'other_credits' => $otherCredits,
                    'receipts'      => $receipts,
                    'payments'      => $payments,
    
                    'interest_cost'     => $ledger->interest_cost ?? 0,
                    'interest_received' => $ledger->interest_received ?? 0,
                    'interest_paid'     => $ledger->interest_paid ?? 0,
                    'interest_due'      => $ledger->interest_due ?? 0,
                    'interest_waived'   => $ledger->interest_waived ?? 0,
    
                    'bad_debts'             => $ledger->bad_debts ?? 0,
                    'total_debtors'         => $balance > 0 ? $balance : 0,
                    'total_creditors'       => $balance < 0 ? abs($balance) : 0,
                    'march_closing_pending' => $ledger->march_closing_pending ?? 0,
    
                    // Table me `status` column nahi, `mark` (red/green/unmarked) hai
                    'status' => match ($ledger->mark) {
                        'green' => 'success',
                        'red'   => 'danger',
                        default => 'secondary',
                    },
                    // `overlimit` string hai ("Yes"/"No"), seedha (bool) cast karne se "No" bhi true ho jata hai
                    'overlimit' => filter_var($ledger->overlimit, FILTER_VALIDATE_BOOLEAN),
                    'rank'      => $ledger->rank ?? null,
                ];
            });
    
            return view('owner.tally.tally.ledgers', compact('company', 'ledgers'));
    
        } catch (\Exception $e) {
            Log::error('Tally companyLedgers failed', [
                'company' => $company,
                'error'   => $e->getMessage(),
            ]);
    
            return back()->with('error', 'Unable to fetch ledgers from Tally. Please try again.');
        }
    }


    public function updateCreditPeriod(Request $request)
    {
        $request->validate([
            'company'        => 'required|string',
            'ledger'         => 'required|string',
            'under'          => 'nullable|string',
            'voucher_id'     => 'nullable',
            'voucher_number' => 'nullable|string',
            'credit_period'  => 'required|integer|min:0',
        ]);

        $owner = Auth::guard('owner')->user();

        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $request->company)
            ->firstOrFail();

        $ledgerModel = TallyLedger::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('ledger_name', $request->ledger)
            ->firstOrFail();

        $query = TallyVoucher::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('ledger_id', $ledgerModel->id);

        // pehle id se try karo (agar dee gayi ho aur numeric ho), warna voucher_number se
        if ($request->filled('voucher_id') && is_numeric($request->voucher_id)) {
            $voucher = (clone $query)->where('id', $request->voucher_id)->first();
        } else {
            $voucher = null;
        }

        if (!$voucher && $request->filled('voucher_number')) {
            $voucher = (clone $query)->where('voucher_number', $request->voucher_number)->first();
        }

        if (!$voucher) {
            return response()->json(['message' => 'Voucher not found for this ledger.'], 404);
        }

        $voucher->credit_period        = $request->credit_period . ' Days';
        $voucher->credit_period_source = 'voucher'; // ya 'manual', jo bhi convention aap use karte ho ledger side
        $voucher->save();

        $voucherDate = Carbon::parse($voucher->date);
        $newDueDate  = $voucherDate->copy()->addDays((int) $request->credit_period);
        $daysOverdue = now()->diffInDays($newDueDate, false) < 0
            ? now()->diffInDays($newDueDate)
            : 0;

        return response()->json([
            'success'  => true,
            'due_date' => $newDueDate->format('Y-m-d'),
            'days'     => $daysOverdue,
        ]);
    }
 
 
    public function ledgerDueVouchers(Request $request)
    {
        try {
            $company = (string) $request->query('company');
            $ledger  = (string) $request->query('ledger');
            $owner   = Auth::guard('owner')->user();
            $com     = urlencode($company);
    
            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->firstOrFail();
    
            $ledgerModel = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->where('ledger_name', $ledger)
                ->firstOrFail();
    
            $under        = $ledgerModel->parent;
            $creditPeriod = (int) ($ledgerModel->credit_period ?? 0);
            $today        = Carbon::now()->startOfDay();
    
            $voucherMappings = VoucherMapping::where('company', $com)
                ->pluck('mapped_to', 'voucher_type')
                ->toArray();
                
            $rows = TallyVoucher::where('owner_id', $owner->id)
                    ->where('tally_company_id', $tallyCompany->id)
                    ->where('ledger_id', $ledgerModel->id)
                    ->get()
                    ->map(fn ($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                        'id'                    => $v->id,
                        'due_date'              => $v->due_date ?? null,
                        'voucher_number'        => $v->voucher_number,
                        'credit_period'         => $v->credit_period,
                        'credit_period_source'  => $v->credit_period_source,   // ← add this line
                    ]);

            // voucher_number -> uska apna DB credit_period (agar hai), lookup ke liye
            $creditPeriodByVoucherNumber       = $rows->pluck('credit_period', 'voucher_number');
            $creditPeriodSourceByVoucherNumber = $rows->pluck('credit_period_source', 'voucher_number');   // ← add

            $dueRows = $this->buildOpenInvoices($rows, $under, $creditPeriod, $today)
                ->filter(fn ($i) => $i['days'] > 0)
                ->sortByDesc('days')
                ->map(function ($i) use ($creditPeriodByVoucherNumber, $creditPeriodSourceByVoucherNumber, $creditPeriod) {
                        $voucherCP     = $creditPeriodByVoucherNumber->get($i['voucher_number']);
                        $voucherSource = $creditPeriodSourceByVoucherNumber->get($i['voucher_number']);

                        // (int) cast "30 Days" string se bhi automatically 30 nikal leta hai (PHP leading-digit parse)
                        $resolvedCP = ($voucherSource === 'voucher') ? $voucherCP : $creditPeriod;

                        return [
                            'id'             => $i['id'] ?? null,
                            'date'           => $i['date'],
                            'due_date'       => $i['due_date'],
                            'voucher_number' => $i['voucher_number'],
                            'days'           => $i['days'],
                            'amount'         => $i['pending'],
                            'original'       => $i['original'],
                            'credit_period'  => (int) $resolvedCP,   // ← ab hamesha plain number frontend ko jayega
                        ];
                    })->values();
        
            
                return response()->json([
                    'vouchers'             => $dueRows,
                    'total'                => round((float) $dueRows->sum('amount'), 2),
                    'ledger_credit_period' => $creditPeriod,
                ]);
    
        } catch (\Throwable $e) {
            Log::error('Tally ledgerDueVouchers failed', [
                'company' => $request->query('company'),
                'ledger'  => $request->query('ledger'),
                'error'   => $e->getMessage(),
            ]);
    
            return response()->json(['message' => 'Unable to fetch due vouchers.'], 500);
        }
    }
 

    private function classifyVoucherRow($v, ?string $under, array $voucherMappings): array
    {
        static $creditSideTypesForDebtor  = ['receipt', 'receipt note', 'credit note'];
        static $debitSideTypesForCreditor = ['payment', 'debit note'];

        $amount = abs((float) $v->amount);
        $voucherType    = trim((string) $v->voucher_type);
        $voucherTypeLow = strtolower($voucherType);

        $rawMapped = $voucherMappings[$voucherType] ?? null;

        if ($rawMapped !== null && trim($rawMapped) !== '') {
            $mappedType = trim($rawMapped);
        } elseif (str_contains($voucherTypeLow, 'sale')) {
            $mappedType = 'Sales';
        } elseif (str_contains($voucherTypeLow, 'purchase')) {
            $mappedType = 'Purchase';
        } else {
            $mappedType = 'Other than Sales/Purchase';
        }

        $debit  = 0;
        $credit = 0;

        if ($under === 'Sundry Creditors') {
            if (in_array($voucherTypeLow, $debitSideTypesForCreditor, true)) {
                $debit = $amount;
            } else {
                $credit = $amount;
            }
        } else {
            if (in_array($voucherTypeLow, $creditSideTypesForDebtor, true)) {
                $credit = $amount;
            } else {
                $debit = $amount;
            }
        }

        return [
            'date'             => (string) $v->date,
            'particulars'      => trim((string) $v->party_ledger_name) ?: '',
            'voucher_type'     => $voucherType,
            'voucher_type_low' => $voucherTypeLow,
            'mapped_type'      => $mappedType,
            'mapped_type_low'  => strtolower($mappedType),
            'voucher_number'   => (string) $v->voucher_number,
            'debit'            => $debit,
            'credit'           => $credit,
        ];
    }


    private function buildBalanceBreakdown($vouchers, ?string $under): array
    {
        $isCreditor = ($under === 'Sundry Creditors');

        $invoices = $vouchers->filter(function ($v) use ($isCreditor) {
                return $isCreditor ? ($v['credit'] > 0) : ($v['debit'] > 0);
            })
            ->sortBy(fn ($v) => $v['date'])
            ->values();

        $clearings = $vouchers->filter(function ($v) use ($isCreditor) {
                $typeLow = $v['voucher_type_low'];
                return $isCreditor
                    ? (str_contains($typeLow, 'payment') && $v['debit'] > 0)
                    : (str_contains($typeLow, 'receipt') && $v['credit'] > 0);
            })
            ->sortBy(fn ($v) => $v['date'])
            ->values()
            ->map(function ($v) use ($isCreditor) {
                return [
                    'date'           => $v['date'],
                    'voucher_number' => $v['voucher_number'],
                    'amount'         => $isCreditor ? $v['debit'] : $v['credit'],
                    'remaining'      => $isCreditor ? $v['debit'] : $v['credit'],  
                ];
            })
            ->toArray();

        $clearingPointer = 0;
        $result = [];

        foreach ($invoices as $inv) {
            $original  = $isCreditor ? $inv['credit'] : $inv['debit'];
            $remaining = $original;
            $matchedAgainst = [];  

            while ($remaining > 0.009 && $clearingPointer < count($clearings)) {
                $c = &$clearings[$clearingPointer];

                if ($c['remaining'] <= 0.009) {
                    $clearingPointer++;
                    continue;
                }

                $take = min($remaining, $c['remaining']);

                $c['remaining'] -= $take;
                $remaining      -= $take;

                $matchedAgainst[] = [
                    'voucher_number' => $c['voucher_number'],
                    'date'           => $c['date'],
                    'amount'         => round($take, 2),
                ];

                if ($c['remaining'] <= 0.009) {
                    $clearingPointer++;
                }

                unset($c);
            }

            $cleared = round($original - $remaining, 2);
            $pending = round($remaining, 2);

            if ($pending <= 0.009) {
                continue;
            }

            $status = ($cleared > 0)
                ? ($isCreditor ? 'Partially Paid' : 'Partially Received')
                : 'Pending';

            $result[] = [
                'date'              => $inv['date'],
                'voucher_number'    => $inv['voucher_number'],
                'voucher_type'      => $inv['voucher_type'],
                'particulars'       => $inv['particulars'],
                'original'          => round($original, 2),
                $isCreditor ? 'paid' : 'received' => $cleared,
                'pending'           => $pending,
                'status'            => $status,
                'cleared_against'   => $matchedAgainst,  
            ];
        }

        return $result;
    }


    private function cleanTallyXml(string $xml): string
    {
        $xml = preg_replace('/&#x?0*(?:[0-8]|0?[bB]|0?[cC]|1[4-9]|2[0-9]|3[01]);/i', '', $xml);
        $xml = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $xml);
        return $xml;
    }


    private function parseXml(string $xml)
    {
        libxml_use_internal_errors(true);
        $xmlObj = simplexml_load_string($xml);
        if ($xmlObj === false) {
            foreach (libxml_get_errors() as $error) {
                Log::warning('Tally XML parse error: ' . trim($error->message));
            }
            libxml_clear_errors();
        }
        return $xmlObj ?: null;
    }


    private function computeVoucherTotals(string $company, $mergeMap): array
    {
        $totals = [
            'sale'          => [],
            'receipt'       => [],
            'purchase'      => [],
            'payment'       => [],
            'other_debit'   => [],
            'other_credit'  => [],
        ];

        $typeMap = [];
        foreach ($mergeMap as $row) {
            $voucherTypeKey = strtolower(trim($row->voucher_type));
            $typeMap[$voucherTypeKey] = strtolower(trim($row->mapped_to));
        }

        $saleTypes     = ['sales', 'cash sales', 'credit sale'];
        $purchaseTypes = ['purchase', 'cash purchase', 'credit purchase'];

        $xml    = $this->cleanTallyXml($this->tally->getVouchersForCompany($company));
        $xmlObj = $this->parseXml($xml);

        if (!$xmlObj) {
            return $totals;
        }

        $vouchers = $xmlObj->xpath("//*[local-name()='VOUCHER']");
        if (!$vouchers) {
            return $totals;
        }

        $seenGuids = [];

        foreach ($vouchers as $voucher) {
            $isCancelled = strtolower(trim((string) ($voucher->ISCANCELLED ?? 'No'))) === 'yes';
            $isOptional  = strtolower(trim((string) ($voucher->ISOPTIONAL  ?? 'No'))) === 'yes';
            if ($isCancelled || $isOptional) {
                continue;
            }

            $guid = trim((string) ($voucher->GUID ?? ''));
            if ($guid !== '') {
                if (isset($seenGuids[$guid])) {
                    continue;
                }
                $seenGuids[$guid] = true;
            }

            $voucherTypeRaw = trim((string) ($voucher->VOUCHERTYPENAME ?? ''));
            $voucherTypeKey = strtolower($voucherTypeRaw);

            $entries = $voucher->xpath(".//*[local-name()='ALLLEDGERENTRIES.LIST']");
            if (!$entries) {
                continue;
            }

            if (in_array($voucherTypeKey, $saleTypes, true)) {
                // ---- SALE ----
                foreach ($entries as $entry) {
                    $ledgerName = trim((string) ($entry->LEDGERNAME ?? ''));
                    $amount     = (float) ($entry->AMOUNT ?? 0);
                    if ($ledgerName === '') continue;

                    $key = strtolower($ledgerName);
                    $totals['sale'][$key] = ($totals['sale'][$key] ?? 0.0) + abs($amount);
                }
            } elseif (in_array($voucherTypeKey, $purchaseTypes, true)) {
                // ---- PURCHASE ----
                foreach ($entries as $entry) {
                    $ledgerName = trim((string) ($entry->LEDGERNAME ?? ''));
                    $amount     = (float) ($entry->AMOUNT ?? 0);
                    if ($ledgerName === '') continue;

                    $key = strtolower($ledgerName);
                    $totals['purchase'][$key] = ($totals['purchase'][$key] ?? 0.0) + abs($amount);
                }
            } elseif ($voucherTypeKey === 'receipt') {
                // ---- RECEIPT ----
                foreach ($entries as $entry) {
                    $ledgerName = trim((string) ($entry->LEDGERNAME ?? ''));
                    $amount     = (float) ($entry->AMOUNT ?? 0);
                    if ($ledgerName === '') continue;

                    $key = strtolower($ledgerName);
                    $totals['receipt'][$key] = ($totals['receipt'][$key] ?? 0.0) + abs($amount);
                }
            } elseif ($voucherTypeKey === 'payment') {
                // ---- PAYMENT ----
                foreach ($entries as $entry) {
                    $ledgerName = trim((string) ($entry->LEDGERNAME ?? ''));
                    $amount     = (float) ($entry->AMOUNT ?? 0);
                    if ($ledgerName === '') continue;

                    $key = strtolower($ledgerName);
                    $totals['payment'][$key] = ($totals['payment'][$key] ?? 0.0) + abs($amount);
                }
            } else {
                // ---- OTHER THAN SALE/PURCHASE (Journal, Contra, Debit Note, Credit Note, etc.) ----
                $mappedTo = $typeMap[$voucherTypeKey] ?? null;
                if ($mappedTo === null) {
                    Log::warning('Tally voucher type not found in mapping table', [
                        'company' => $company,
                        'type'    => $voucherTypeRaw,
                    ]);
                }

                foreach ($entries as $entry) {
                    $ledgerName = trim((string) ($entry->LEDGERNAME ?? ''));
                    $amount     = (float) ($entry->AMOUNT ?? 0);
                    if ($ledgerName === '') continue;

                    $key = strtolower($ledgerName);

                    $isDeemedPositiveTag = $entry->{'ISDEEMEDPOSITIVE'} ?? null;

                    if ($isDeemedPositiveTag !== null && $isDeemedPositiveTag !== '') {
                        $isDebit = strtolower(trim((string) $isDeemedPositiveTag)) === 'yes';
                    } else {
                        $isDebit = $amount < 0;
                    }

                    if ($isDebit) {
                        $totals['other_debit'][$key] = ($totals['other_debit'][$key] ?? 0.0) + abs($amount);
                    } else {
                        $totals['other_credit'][$key] = ($totals['other_credit'][$key] ?? 0.0) + abs($amount);
                    }
                }
            }
        }

        return $totals;
    }

    
    
    public function ledgerFieldVouchers(Request $request, $company, $ledger, $under)
    {
        $field   = $request->get('field');
        $company = urldecode($company);
        $ledger  = urldecode($ledger);
        $under   = urldecode($under);
        $owner   = Auth::guard('owner')->user();

        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $company)
            ->first();

        $ledgerModel = TallyLedger::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('ledger_name', $ledger)
            ->first();

        if (!$ledgerModel) {
            return response()->json(['vouchers' => []]);
        }

        $voucherMappings = VoucherMapping::where('company', $company)
            ->pluck('mapped_to', 'voucher_type')
            ->toArray();

        $ledgerCreditPeriod = (int) ($ledgerModel->credit_period ?? 0);
        $today = Carbon::now()->startOfDay();

        $vouchers = TallyVoucher::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('ledger_id', $ledgerModel->id)
            ->orderBy('date')
            ->get()
            // 👇 id, due_date, credit_period, credit_period_source bhi saath me le lo
            ->map(fn ($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                'id'                   => $v->id,
                'due_date'             => $v->due_date ?? null,
                'credit_period'        => $v->credit_period,
                'credit_period_source' => $v->credit_period_source,
            ]);

        if ($field === 'Balance') {
            return response()->json([
                'vouchers' => $this->buildBalanceBreakdown($vouchers, $under),
            ]);
        }

        $filtered = $this->filterVouchersByField($vouchers, $field, $under);

        $rows = $filtered->map(function ($v) use ($ledgerCreditPeriod, $today) {
            // per-voucher credit_period agar manually set hai to wahi, warna ledger ka default
            $resolvedCP = ($v['credit_period_source'] === 'voucher' && $v['credit_period'] !== null)
                ? (int) $v['credit_period']   // "30 Days" se bhi (int) cast 30 nikal leta hai
                : $ledgerCreditPeriod;

            $days = 0;
            if (!empty($v['due_date'])) {
                $dueDateCarbon = Carbon::parse($v['due_date'])->startOfDay();
                if ($dueDateCarbon->lt($today)) {
                    $days = $today->diffInDays($dueDateCarbon);
                }
            }

            return [
                'id'            => $v['id'] ?? null,
                'date'          => $v['date'],
                'voucher_no'    => $v['voucher_number'],
                'voucher_type'  => $v['voucher_type'],
                'particulars'   => $v['particulars'],
                'debit'         => $v['debit'],
                'credit'        => $v['credit'],
                'due_date'      => $v['due_date'] ?? null,
                'credit_period' => $resolvedCP,
                'days'          => $days,
            ];
        })->values();

        return response()->json(['vouchers' => $rows]);
    }


    private function buildLedgerVouchers(string $company, string $ledger, $mergeMap): array
    {
        $ledgerKey = strtolower(trim($ledger));

        $saleTypes     = ['sales', 'cash sales', 'credit sale'];
        $purchaseTypes = ['purchase', 'cash purchase', 'credit purchase'];

        $xml    = $this->cleanTallyXml($this->tally->getVouchersForCompany($company));
        $xmlObj = $this->parseXml($xml);

        if (!$xmlObj) {
            return [];
        }

        $vouchers = $xmlObj->xpath("//*[local-name()='VOUCHER']");
        if (!$vouchers) {
            return [];
        }

        $seenGuids = [];
        $result    = [];

        foreach ($vouchers as $voucher) {
            $isCancelled = strtolower(trim((string) ($voucher->ISCANCELLED ?? 'No'))) === 'yes';
            $isOptional  = strtolower(trim((string) ($voucher->ISOPTIONAL  ?? 'No'))) === 'yes';
            if ($isCancelled || $isOptional) {
                continue;
            }

            $guid = trim((string) ($voucher->GUID ?? ''));
            if ($guid !== '') {
                if (isset($seenGuids[$guid])) {
                    continue;
                }
                $seenGuids[$guid] = true;
            }

            $voucherTypeRaw = trim((string) ($voucher->VOUCHERTYPENAME ?? ''));
            $voucherTypeKey = strtolower($voucherTypeRaw);

            $entries = $voucher->xpath(".//*[local-name()='ALLLEDGERENTRIES.LIST']");
            if (!$entries) {
                continue;
            }

            $baseMappedType = null;
            if (in_array($voucherTypeKey, $saleTypes, true)) {
                $baseMappedType = 'sales';
            } elseif (in_array($voucherTypeKey, $purchaseTypes, true)) {
                $baseMappedType = 'purchase';
            } elseif ($voucherTypeKey === 'receipt') {
                $baseMappedType = 'receipt';
            } elseif ($voucherTypeKey === 'payment') {
                $baseMappedType = 'payment';
            }

            foreach ($entries as $entry) {
                $ledgerName = trim((string) ($entry->LEDGERNAME ?? ''));
                if ($ledgerName === '' || strtolower($ledgerName) !== $ledgerKey) {
                    continue;
                }

                $amount     = (float) ($entry->AMOUNT ?? 0);
                $mappedType = $baseMappedType;
                $debit      = 0.0;
                $credit     = 0.0;

                if ($mappedType === 'sales' || $mappedType === 'payment') {
                    $debit = abs($amount);
                } elseif ($mappedType === 'purchase' || $mappedType === 'receipt') {
                    $credit = abs($amount);
                } else {
                    $isDeemedPositiveTag = $entry->{'ISDEEMEDPOSITIVE'} ?? null;
                    $isDebit = ($isDeemedPositiveTag !== null && $isDeemedPositiveTag !== '')
                        ? strtolower(trim((string) $isDeemedPositiveTag)) === 'yes'
                        : $amount < 0;

                    if ($isDebit) {
                        $debit      = abs($amount);
                        $mappedType = 'other_debit';
                    } else {
                        $credit     = abs($amount);
                        $mappedType = 'other_credit';
                    }
                }

                $result[] = [
                    'date'         => (string) ($voucher->DATE ?? ''), // raw Ymd - parseTallyDateForCompare/safeFormatTallyDate isi format ko expect karte hain
                    'voucher_no'   => (string) ($voucher->VOUCHERNUMBER ?? ''),
                    'voucher_type' => $voucherTypeRaw,
                    'mapped_type'  => $mappedType,
                    'particulars'  => (string) ($voucher->PARTYLEDGERNAME ?? $ledgerName),
                    'debit'        => $debit,
                    'credit'       => $credit,
                ];
            }
        }

        return $result;
    }

    
    private function filterVouchersByField($vouchers, string $field, ?string $under)
    {
        $isCreditor = ($under === 'Sundry Creditors');

        return match ($field) {
            'Sale'      => $vouchers->filter(fn ($v) => $v['mapped_type_low'] === 'sales' && $v['debit'] > 0),
            'Purchase'  => $vouchers->filter(fn ($v) => $v['mapped_type_low'] === 'purchase' && $v['credit'] > 0),
            'Other Debits'  => $vouchers->filter(fn ($v) => $v['mapped_type_low'] !== 'sales' && $v['debit'] > 0),
            'Other Credits' => $vouchers->filter(fn ($v) => $v['mapped_type_low'] !== 'purchase' && $v['credit'] > 0),
            'Receipts'  => $vouchers->filter(fn ($v) => str_contains($v['voucher_type_low'], 'receipt') && $v['credit'] > 0),
            'Payments'  => $vouchers->filter(fn ($v) => str_contains($v['voucher_type_low'], 'payment') && $v['debit'] > 0),
            default     => $vouchers,
        };
    }


    private function parseTallyDateForCompare(?string $d): ?Carbon
    {
        if (empty($d)) {
            return null;
        }
    
        try {
            return Carbon::createFromFormat('Ymd', $d)->startOfDay();
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($d)->startOfDay();
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }


    private function safeFormatTallyDate(?string $d): ?string
    {
        if (empty($d)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('Ymd', $d)->format('d-m-Y');
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($d)->format('d-m-Y');
            } catch (\Throwable $e2) {
                return $d;
            }
        }
    }


    public function ledgerVouchers(Request $request, $company, $ledger, $under = null)
    {
        $under = urldecode($under);

        try {
            $owner = Auth::guard('owner')->user();

            $com = $company;  
            $company = urldecode($company);
            $ledger  = urldecode($ledger);

            $voucherMappings = VoucherMapping::where('company', $com)
                ->pluck('mapped_to', 'voucher_type')
                ->toArray();

            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->first();

            $tallyLedger = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id ?? 0)
                ->where('ledger_name', $ledger)
                ->first();
 
            $vouchers = [];

            $creditSideTypesForDebtor  = ['receipt', 'receipt note', 'credit note']; // money IN — reduces receivable
            $debitSideTypesForCreditor = ['payment', 'debit note'];                  // money OUT — reduces payable

            if ($tallyCompany && $tallyLedger) {
                $voucherRows = TallyVoucher::where('owner_id', $owner->id)
                    ->where('tally_company_id', $tallyCompany->id)
                    ->where('ledger_id', $tallyLedger->id)
                    ->orderBy('date')
                    ->get();

                foreach ($voucherRows as $v) {
                    $amount = abs((float) $v->amount);

                    $voucherType    = trim((string) $v->voucher_type);
                    $voucherTypeLow = strtolower($voucherType);

                    $mappedType = trim($voucherMappings[$voucherType] ?? 'Other than Sales/Purchase');

                    $debit  = 0;
                    $credit = 0;

                    if ($under === 'Sundry Creditors') {
                        if (in_array($voucherTypeLow, $debitSideTypesForCreditor, true)) {
                            $debit = $amount;
                        } else {
                            $credit = $amount;
                        }
                    } else {
                        if (in_array($voucherTypeLow, $creditSideTypesForDebtor, true)) {
                            $credit = $amount;
                        } else {
                            $debit = $amount;
                        }
                    }

                    $vouchers[] = [
                        'date'           => (string) $v->date,
                        'particulars'    => trim((string) $v->party_ledger_name) ?: '',
                        'voucher_type'   => $voucherType,
                        'mapped_type'    => $mappedType,
                        'voucher_number' => (string) $v->voucher_number,
                        'debit'          => $debit,
                        'credit'         => $credit,
                        'master_id'      => (string) $v->id,
                    ];
                }
            }

            $openingBalanceAllTime = (float) ($tallyLedger->opening_balance ?? 0);
            $closingBalanceAllTime = (float) ($tallyLedger->closing_balance ?? 0);

            $today = Carbon::now();
            $currentFyStartYear = $today->month >= 4 ? (int) $today->year : (int) $today->year - 1;

            $fyParam = $request->get('fy');
            $selectedFyStartYear = $currentFyStartYear;

            if ($fyParam && preg_match('/^(\d{4})-(\d{4})$/', $fyParam, $m)) {
                if ((int) $m[2] === (int) $m[1] + 1) {
                    $selectedFyStartYear = (int) $m[1];
                }
            }

            $selectedFyLabel = $selectedFyStartYear . '-' . ($selectedFyStartYear + 1);
            $previousFyStartYear = $selectedFyStartYear - 1;
            $previousFyLabel = $previousFyStartYear . '-' . ($previousFyStartYear + 1);

            $selectedFyStart = Carbon::create($selectedFyStartYear, 4, 1)->startOfDay();
            $selectedFyEnd = Carbon::create($selectedFyStartYear + 1, 3, 31)->endOfDay();

            $previousFyStart = Carbon::create($previousFyStartYear, 4, 1)->startOfDay();
            $previousFyEnd = Carbon::create($previousFyStartYear + 1, 3, 31)->endOfDay();

            $fyOptions = [];
            for ($i = 0; $i < 5; $i++) {
                $y = $currentFyStartYear - $i;
                $fyOptions[] = $y . '-' . ($y + 1);
            }

            $parseDate = function ($d) {
                if (empty($d)) return null;
                try {
                    return Carbon::createFromFormat('Ymd', $d)->startOfDay();
                } catch (\Throwable $e) {
                    try {
                        return Carbon::parse($d)->startOfDay();
                    } catch (\Throwable $e2) {
                        return null;
                    }
                }
            };

            $vouchersAsc = $vouchers;
            usort($vouchersAsc, fn($a, $b) => strcmp($a['date'], $b['date']));

            $netBeforeSelectedFy = 0;
            $netWithinSelectedFy = 0;

            foreach ($vouchersAsc as $v) {
                $d = $parseDate($v['date']);
                if (!$d) continue;
                $net = $v['debit'] - $v['credit'];

                if ($d->lt($selectedFyStart)) {
                    $netBeforeSelectedFy += $net;
                } elseif ($d->between($selectedFyStart, $selectedFyEnd)) {
                    $netWithinSelectedFy += $net;
                }
            }

            $openingBalance = $openingBalanceAllTime + $netBeforeSelectedFy;
            $closingBalance = $openingBalance + $netWithinSelectedFy;

            $currentFyVouchers = array_values(array_filter($vouchers, function ($v) use ($parseDate, $selectedFyStart, $selectedFyEnd) {
                $d = $parseDate($v['date']);
                return $d && $d->between($selectedFyStart, $selectedFyEnd);
            }));

            $previousFyVouchers = array_values(array_filter($vouchers, function ($v) use ($parseDate, $previousFyStart, $previousFyEnd) {
                $d = $parseDate($v['date']);
                return $d && $d->between($previousFyStart, $previousFyEnd);
            }));

            $salesVouchers = collect($previousFyVouchers)
                ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'sales')
                ->sortByDesc('date')->values()->toArray();

            $receiptVouchers = collect($previousFyVouchers)
                ->filter(fn($item) => strtolower(trim($item['voucher_type'])) === 'receipt')
                ->sortByDesc('date')->values()->toArray();
 
            $buildBuckets = function (array $voucherSet, ?string $under) {
                if ($under === "Sundry Debtors") {
                    $primaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'sales' && ($item['debit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $secondaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => ($item['credit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $journalVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) !== 'sales' && ($item['debit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $totalSales  = collect($primaryVouchers)->sum('debit');
                    $totalOthers = collect($journalVouchers)->sum('debit');
                    $totalCredit = collect($secondaryVouchers)->sum('credit');

                    return [
                        'primaryVouchers'   => collect($primaryVouchers)->sortBy('date')->values()->toArray(),
                        'secondaryVouchers' => $secondaryVouchers,
                        'journalVouchers'   => collect($journalVouchers)->sortBy('date')->values()->toArray(),
                        'primaryLabel'      => 'Total Debit',
                        'secondaryLabel'    => 'Total Credit',
                        'summary'           => ['sale' => $totalSales + $totalOthers, 'receipts' => $totalCredit],
                    ];
                } elseif ($under === "Sundry Creditors") {
                    $primaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'purchase' && ($item['credit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $secondaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => ($item['debit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $journalVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) !== 'purchase' && ($item['credit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $totalPurchase = collect($primaryVouchers)->sum('credit');
                    $totalOthers   = collect($journalVouchers)->sum('credit');
                    $totalDebit    = collect($secondaryVouchers)->sum('debit');

                    return [
                        'primaryVouchers'   => collect($primaryVouchers)->sortBy('date')->values()->toArray(),
                        'secondaryVouchers' => $secondaryVouchers,
                        'journalVouchers'   => collect($journalVouchers)->sortBy('date')->values()->toArray(),
                        'primaryLabel'      => 'Total Credit',
                        'secondaryLabel'    => 'Total Debit',
                        'summary'           => ['sale' => $totalPurchase + $totalOthers, 'receipts' => $totalDebit],
                    ];
                }

                return [
                    'primaryVouchers' => [], 'secondaryVouchers' => [], 'journalVouchers' => [],
                    'primaryLabel' => 'Primary', 'secondaryLabel' => 'Secondary',
                    'summary' => ['sale' => 0, 'receipts' => 0],
                ];
            };

             
            $buildPendingSnapshot = function (array $voucherSet, ?string $under) {
                if ($under === 'Sundry Debtors') {
                    $primary = collect($voucherSet)
                        ->filter(fn($v) => strtolower(trim($v['mapped_type'])) === 'sales' && ($v['debit'] ?? 0) > 0)
                        ->sortBy('date')->values()
                        ->map(function ($v) { $v['pending'] = $v['debit']; return $v; })->all();

                    $others = collect($voucherSet)
                        ->filter(fn($v) => strtolower(trim($v['mapped_type'])) !== 'sales' && ($v['debit'] ?? 0) > 0)
                        ->sortBy('date')->values()
                        ->map(function ($v) { $v['pending'] = $v['debit']; return $v; })->all();

                    $receipts = collect($voucherSet)
                        ->filter(fn($v) => ($v['credit'] ?? 0) > 0)
                        ->sortBy('date')->values()->all();

                    foreach ($receipts as $receipt) {
                        $amount = $receipt['credit'];

                        foreach ($others as $i => $o) {
                            if ($amount <= 0) break;
                            if ($others[$i]['pending'] <= 0) continue;
                            $adjust = min($amount, $others[$i]['pending']);
                            $others[$i]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                        foreach ($primary as $i => $p) {
                            if ($amount <= 0) break;
                            if ($primary[$i]['pending'] <= 0) continue;
                            $adjust = min($amount, $primary[$i]['pending']);
                            $primary[$i]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                    }

                    $pendingVouchers = collect(array_merge($primary, $others))
                        ->filter(fn($v) => ($v['pending'] ?? 0) > 0.01)
                        ->sortBy('date')->values()->toArray();

                    return ['pendingAmount' => array_sum(array_column($pendingVouchers, 'pending')), 'pendingVouchers' => $pendingVouchers];

                } elseif ($under === 'Sundry Creditors') {
                    $primary = collect($voucherSet)
                        ->filter(fn($v) => strtolower(trim($v['mapped_type'])) === 'purchase' && ($v['credit'] ?? 0) > 0)
                        ->sortBy('date')->values()
                        ->map(function ($v) { $v['pending'] = $v['credit']; return $v; })->all();

                    $others = collect($voucherSet)
                        ->filter(fn($v) => strtolower(trim($v['mapped_type'])) !== 'purchase' && ($v['credit'] ?? 0) > 0)
                        ->sortBy('date')->values()
                        ->map(function ($v) { $v['pending'] = $v['credit']; return $v; })->all();

                    $payments = collect($voucherSet)
                        ->filter(fn($v) => ($v['debit'] ?? 0) > 0)
                        ->sortBy('date')->values()->all();

                    foreach ($payments as $payment) {
                        $amount = $payment['debit'];

                        foreach ($others as $i => $o) {
                            if ($amount <= 0) break;
                            if ($others[$i]['pending'] <= 0) continue;
                            $adjust = min($amount, $others[$i]['pending']);
                            $others[$i]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                        foreach ($primary as $i => $p) {
                            if ($amount <= 0) break;
                            if ($primary[$i]['pending'] <= 0) continue;
                            $adjust = min($amount, $primary[$i]['pending']);
                            $primary[$i]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                    }

                    $pendingVouchers = collect(array_merge($primary, $others))
                        ->filter(fn($v) => ($v['pending'] ?? 0) > 0.01)
                        ->sortBy('date')->values()->toArray();

                    return ['pendingAmount' => array_sum(array_column($pendingVouchers, 'pending')), 'pendingVouchers' => $pendingVouchers];
                }

                return ['pendingAmount' => 0, 'pendingVouchers' => []];
            };

            $vouchersUpTo = function (array $allVouchersAsc, Carbon $cutoff) use ($parseDate) {
                return array_values(array_filter($allVouchersAsc, function ($v) use ($parseDate, $cutoff) {
                    $d = $parseDate($v['date']);
                    return $d && $d->lte($cutoff);
                }));
            };

            $vouchersUpToPreviousFyEnd = $vouchersUpTo($vouchersAsc, $previousFyEnd);
            $pendingSnapshot = $buildPendingSnapshot($vouchersUpToPreviousFyEnd, $under);

            $vouchersUpToSelectedFyEnd = $vouchersUpTo($vouchersAsc, $selectedFyEnd);
            $closingSnapshot = $buildPendingSnapshot($vouchersUpToSelectedFyEnd, $under);

            $currentFyOnlyBuckets = $buildBuckets($currentFyVouchers, $under);
            $previousFyOnlyBuckets = $buildBuckets($previousFyVouchers, $under);

            $summary = [
                'sale'     => $currentFyOnlyBuckets['summary']['sale'],
                'receipts' => $currentFyOnlyBuckets['summary']['receipts'],
                'pending'  => $pendingSnapshot['pendingAmount'],
            ];

            $primaryVouchers   = $previousFyOnlyBuckets['primaryVouchers'];
            $secondaryVouchers = $previousFyOnlyBuckets['secondaryVouchers'];
            $journalVouchers   = $previousFyOnlyBuckets['journalVouchers'];
            $primaryLabel      = $previousFyOnlyBuckets['primaryLabel'];
            $secondaryLabel    = $previousFyOnlyBuckets['secondaryLabel'];

            $pendingVouchers        = $pendingSnapshot['pendingVouchers'];
            $closingBalanceVouchers = $closingSnapshot['pendingVouchers'];

            return view('owner.tally.tally.ledger-vouchers', compact(
                'company', 'ledger', 'vouchers', 'summary', 'openingBalance', 'closingBalance',
                'salesVouchers', 'receiptVouchers', 'journalVouchers', 'under',
                'primaryVouchers', 'secondaryVouchers', 'primaryLabel', 'secondaryLabel',
                'fyOptions', 'selectedFyLabel', 'previousFyLabel',
                'pendingVouchers', 'closingBalanceVouchers'
            ));

        } catch (\Throwable $e) {
            Log::error('Ledger Voucher Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }
   

    public function ledgerFollowUp(Request $request, $company, $ledger, $under = null)
    {
        $vouchers = $this->ledgerVouchers($request, $company, $ledger, $under);
        return view('owner.tally.tally.ledger-follow-up', compact('company', 'ledger', 'vouchers'));
    }


    public function followUpsHub(Request $request, $company)
    {
        $company = urldecode($company);

        $type   = $request->query('type') ?: '';  
        $ledger = urldecode($request->query('ledger', ''));
        $under  = urldecode($request->query('under', ''));

        $allLedgers = [
            [
                'name' => 'Dummy Ledger 1', 'under' => 'Sundry Debtors', 'mobile' => '9876543210',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 54321.10, 'target' => 50000,
            ],
            [
                'name' => 'Dummy Ledger 2', 'under' => 'Sundry Debtors', 'mobile' => '9123456780',
                'type' => 'Follow Up-Balances/Targets(months)',
                'balance' => 72500.00, 'target' => 80000,
                'target_month' => now()->subMonths(1)->format('Y-m'),
            ],
            [
                'name' => 'Dummy Ledger 3', 'under' => 'Sundry Debtors', 'mobile' => '9988776655',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 18200.50, 'age_bucket' => '31-60 Days', 'days' => 45,
            ],
            [
                'name' => 'Dummy Ledger 4', 'under' => 'Sundry Creditors', 'mobile' => '9871234567',
                'type' => 'Follow Up-Balances/Days(10-20-30)',
                'balance' => 9800.00, 'days' => 20,
                'date' => now()->subDays(20)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2204',
                'interest_due' => 150.00,
            ],
            [
                'name' => 'Dummy Ledger 5', 'under' => 'Sundry Creditors', 'mobile' => '9012345678',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(21)->format('Y-m-d'),
                'due_date' => now()->addDays(21)->format('Y-m-d'),
                'inv_no' => 'INV-2201', 'days' => 12,
                'due' => 32000.00, 'interest_due' => 480.00,
            ],
            [
                'name' => 'Dummy Ledger 6', 'under' => 'Sundry Debtors', 'mobile' => '9090909090',
                'type' => 'Follow Up-Not Due',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'due_date' => now()->addDays(35)->format('Y-m-d'),
                'inv_no' => 'INV-2202', 'days_pending' => 5,
                'not_due' => 27500.00,
            ],
            [
                'name' => 'Dummy Ledger 7', 'under' => 'Sundry Creditors', 'mobile' => '9191919191',
                'type' => 'Follow Up Overlimits',
                'balance' => 12500.00,
            ],
            [
                'name' => 'Dummy Ledger 8', 'under' => 'Sundry Debtors', 'mobile' => '9898989898',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 61230.00, 'target' => 55000,
            ],
            [
                'name' => 'Dummy Ledger 9', 'under' => 'Sundry Creditors', 'mobile' => '9797979797',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(10)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2203', 'days' => 18,
                'due' => 41500.00, 'interest_due' => 620.00,
            ],
            [
                'name' => 'Dummy Ledger 10', 'under' => 'Sundry Debtors', 'mobile' => '9696969696',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 9450.75, 'age_bucket' => '0-30 Days', 'days' => 15,
            ],
            [
                'name' => 'Dummy Ledger 11', 'under' => 'Sundry Debtors', 'mobile' => '9595959595',
                'type' => 'Follow Up-Balances',
                'balance' => 15750.25,
            ],
        ];
        
        if (!empty($ledger)) {
            $allLedgers = collect($allLedgers)
                ->filter(fn($l) => $l['name'] === $ledger)
                ->values()
                ->all();
        } elseif (!empty($under)) {
            $allLedgers = collect($allLedgers)
                ->filter(fn($l) => ($l['under'] ?? null) === $under)
                ->values()
                ->all();
        }

        $followUps = collect($allLedgers)->values()->map(function ($l, $index) {
            $balance   = $l['balance'] ?? 0;
            $target    = $l['target'] ?? 0;
            $targetPct = $target > 0 ? round((abs($balance) / $target) * 100, 2) : 0;

            return [
                'id'                => $index + 1,
                'name'              => $l['name'] ?? '-',
                'mobile'            => $l['mobile'] ?? '-',
                'under'             => $l['under'] ?? '-',
                'type'              => $l['type'] ?? '-',
                'balance'           => $balance,
                'target'            => $target,
                'target_pct'        => $targetPct,
                'target_month'      => $l['target_month'] ?? null,
                'age_bucket'        => $l['age_bucket'] ?? null,
                'days'              => $l['days'] ?? null,
                'days_pending'      => $l['days_pending'] ?? null,
                'date'              => $l['date'] ?? null,
                'due_date'          => $l['due_date'] ?? null,
                'inv_no'            => $l['inv_no'] ?? null,
                'due'               => $l['due'] ?? null,
                'not_due'           => $l['not_due'] ?? null,
                'interest_due'      => $l['interest_due'] ?? null,
                'accountant_status' => rand(0, 1) ? 'Pending' : 'Responded',
            ];
        });
        
        if (!empty($type) && $type !== 'Follow Up-Quick /Smart - Follow -up') {
            $followUps = $followUps->filter(fn($row) => $row['type'] === $type)->values();
        }

        return view('owner.tally.tally.followup-hub', [
            'company'        => $company,
            'type'           => $type,
            'selectedLedger' => $ledger,
            'selectedUnder'  => $under,
            'followUps'      => $followUps,
        ]);
    }

    public function voucherMappings($company)
    {
        $company = urldecode($company);

        $voucherTypes = VoucherMapping::where('company', $company)
            ->select('voucher_type as name')
            ->get()
            ->toArray();

        $savedMappings = VoucherMapping::where('company', $company)
            ->pluck('mapped_to', 'voucher_type')
            ->toArray();

        return view(
            'owner.tally.tally.voucher-mappings',
            compact(
                'voucherTypes',
                'savedMappings',
                'company'
            )
        );
    }

    public function saveVoucherMappings(Request $request)
    {
        $voucherTypes = array_keys($request->mapping);

        VoucherMapping::where('company', $request->company)
            ->whereNotIn('voucher_type', $voucherTypes)
            ->delete();

        foreach ($request->mapping as $voucherType => $mappedTo) {
            VoucherMapping::updateOrCreate(
                [
                    'company' => $request->company,
                    'voucher_type' => $voucherType
                ],
                [
                    'mapped_to' => $mappedTo
                ]
            );
        }
        return back()->with('success', 'Voucher Mapping Saved Successfully.');
    }

    public function asignLedgers($company)
    {
        try {

            $company = urldecode($company);
            $xml = $this->tally->getLedgers($company);
            $xml = preg_replace('/&#x?0*4;?/i', '', $xml);
            $xml = preg_replace('/&#[0-8];|&#1[0-9];|&#2[0-9];|&#3[0-1];/', '', $xml);
            $xml = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $xml);
            libxml_use_internal_errors(true);
            $xmlObj = simplexml_load_string($xml);
            if ($xmlObj === false) {
                foreach (libxml_get_errors() as $error) {
                    dump($error->message);
                }
            }
            $xmlObj = simplexml_load_string($xml);
            $ledgers = [];

            if ($xmlObj) {
                $nodes = $xmlObj->xpath("//*[local-name()='LEDGER']");
                if ($nodes) {
                    foreach ($nodes as $ledger) {
                        $name = (string) ($ledger['NAME'] ?? '');
                        $under = (string) ($ledger->PARENT ?? '');
                        if (
                            in_array(
                                trim($under),
                                ['Sundry Debtors', 'Sundry Creditors']
                            )
                        ) {
                            $ledgers[] = [
                                'name' => $name,
                                'under' => $under,
                            ];
                        }
                    }
                }
            }

            $assignedLedgers = LedgerCollector::leftJoin('rms_collectors', 'rms_collectors.id', '=', 'rms_ledger_collectors.collector_id')
                ->where('rms_ledger_collectors.company', $company)
                ->select(
                    'rms_ledger_collectors.ledger_name',
                    'rms_ledger_collectors.collector_id',
                    'rms_collectors.name as collector_name'
                )
                ->get()
                ->keyBy('ledger_name');

            return view(
                'owner.tally.tally.ledgers-assign',
                compact('company', 'ledgers', 'assignedLedgers')
            );

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


    public function assignLedgers(Request $request)
    {
        $request->validate([
            'collector_id' => 'required',
            'ledgers' => 'required|array'
        ]);

        foreach ($request->ledgers as $item) {
            $ledger = json_decode($item, true);
            LedgerCollector::updateOrCreate(
                [
                    'company' => $request->company,
                    'ledger_name' => $ledger['name'],
                ],
                [
                    'ledger_under' => $ledger['under'],
                    'collector_id' => $request->collector_id,
                ]
            );
        }

        return back()->with('success', 'Collector assigned successfully.');
    }

    public function telecaller()
    {
        return view('owner.tally.tally.followup-action.telecaller');
    }

    public function call()
    {
        return view('owner.tally.tally.followup-action.call');
    }

    public function whatsappMessage()
    {
        return view('owner.tally.tally.followup-action.whatsapp-message');
    }

    public function physicalVisit()
    {
        return view('owner.tally.tally.followup-action.physical-visit');
    }

    public function escalation()
    {
        return view('owner.tally.tally.followup-action.escalation');
    }

  
    public function followupHistory()
    {
        $typeLabels = [
            'balances'      => 'Follow Up-Balances',
            'pct_targets'    => 'Follow Up-Balances/%Targets',
            'targets_months' => 'Follow Up-Balances/Targets(months)',
            'days_agewise'   => 'Follow Up-Balances/Days(agewise)',
            'days_10_20_30'  => 'Follow Up-Balances/Days(10-20-30)',
            'due'            => 'Follow Up-Due',
            'not_due'        => 'Follow Up-Not Due',
            'overlimits'     => 'Follow Up Overlimits',
        ];
    
        $actionLabels = [
            'allocate_tele_call' => 'Allocate to tele call',
            'call'                => 'Call',
            'whatsapp'            => 'WhatsApp',
            'physical_visit'      => 'Physical visit',
            'escalation'          => 'Escalation',
            'no_action'           => 'No action',
        ];
         
        $history = [
            [
                'id' => 1, 'ledger_id' => 101, 'ledger_name' => 'ABC Traders', 'mobile' => '9000000001',
                'type' => 'pct_targets', 'type_label' => $typeLabels['pct_targets'],
                'balance' => 54321.10, 'target' => 50000, 'target_pct' => 108.64,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01', 'action' => 'call', 'action_label' => $actionLabels['call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th', 'solution' => 'Payment plan agreed', 'admin_solution' => null,
            ],
            [
                'id' => 2, 'ledger_id' => 101, 'ledger_name' => 'ABC Traders', 'mobile' => '9000000001',
                'type' => 'due', 'type_label' => $typeLabels['due'],
                'balance' => 32000, 'target' => null, 'target_pct' => null,
                'date' => '2026-07-20', 'due_date' => '2026-08-10', 'inv_no' => 'INV-2201', 'days' => 12, 'interest_due' => 480,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-02', 'action' => 'whatsapp', 'action_label' => $actionLabels['whatsapp'],
                'frequency' => 'daily', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 3, 'ledger_id' => 101, 'ledger_name' => 'ABC Traders', 'mobile' => '9000000001',
                'type' => 'overlimits', 'type_label' => $typeLabels['overlimits'],
                'balance' => 12500, 'target' => null, 'target_pct' => null,
                'assigned_to' => 'Priya', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-28', 'action' => 'escalation', 'action_label' => $actionLabels['escalation'],
                'frequency' => 'weekly', 'response_date' => '2026-07-30',
                'response' => 'Escalated to regional manager', 'solution' => 'Limit revised', 'admin_solution' => null,
            ],
            [
                'id' => 4, 'ledger_id' => 102, 'ledger_name' => 'XYZ Enterprises', 'mobile' => '9000000002',
                'type' => 'targets_months', 'type_label' => $typeLabels['targets_months'],
                'balance' => 78900, 'target' => 80000, 'target_pct' => 98.63,
                'due_date' => '2026-07-31', // used as Target Month for this type
                'assigned_to' => 'Aman', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-15', 'action' => 'physical_visit', 'action_label' => $actionLabels['physical_visit'],
                'frequency' => 'monthly', 'response_date' => '2026-07-20',
                'response' => 'Visited site, cheque collected', 'solution' => 'Cleared 50% balance', 'admin_solution' => null,
            ],
            [
                'id' => 5, 'ledger_id' => 102, 'ledger_name' => 'XYZ Enterprises', 'mobile' => '9000000002',
                'type' => 'days_10_20_30', 'type_label' => $typeLabels['days_10_20_30'],
                'balance' => 15600, 'target' => null, 'target_pct' => null, 'days' => 20,
                'assigned_to' => 'Aman', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-05', 'action' => 'no_action', 'action_label' => $actionLabels['no_action'],
                'frequency' => 'daily', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 6, 'ledger_id' => 103, 'ledger_name' => 'Global Textiles', 'mobile' => '9000000003',
                'type' => 'not_due', 'type_label' => $typeLabels['not_due'],
                'balance' => 43200, 'target' => null, 'target_pct' => null,
                'date' => '2026-07-25', 'due_date' => '2026-08-25', 'inv_no' => 'INV-3105', 'days_pending' => 30,
                'assigned_to' => 'Priya', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-06', 'action' => 'allocate_tele_call', 'action_label' => $actionLabels['allocate_tele_call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-07',
                'response' => 'Confirmed, will pay on due date', 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 7, 'ledger_id' => 104, 'ledger_name' => 'Sunrise Distributors', 'mobile' => '9000000004',
                'type' => 'days_agewise', 'type_label' => $typeLabels['days_agewise'],
                'balance' => 99500, 'target' => null, 'target_pct' => null, 'days' => 46,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-10', 'action' => 'escalation', 'action_label' => $actionLabels['escalation'],
                'frequency' => 'monthly', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 8, 'ledger_id' => 105, 'ledger_name' => 'ABCD Traders', 'mobile' => '9000000001',
                'type' => 'balances', 'type_label' => $typeLabels['balances'],
                'balance' => 54321.10, 'target' => 50000, 'target_pct' => 108.64,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01', 'action' => 'call', 'action_label' => $actionLabels['call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th', 'solution' => 'Payment plan agreed', 'admin_solution' => null,
            ],
        ];
    
        return view('owner.tally.tally.followup-history', compact('history'));
    }
    
    public function templates(Request $request, $company)
    {
        $templates = WhatsappTemplate::where('owner_id', Auth::id())
            ->where('company_id', $company)
            ->latest()
            ->get();

        return view('owner.tally.other.templates', compact('templates', 'company'));
    }

      
    public function storeTemplate(Request $request, $company): JsonResponse
    {
        $data = $request->validate([
            'followup_type' => ['required', Rule::in($this->followupTypes)],
            'name'           => ['required', 'string', 'max:255'],
            'tone'           => ['required', Rule::in($this->tones)],
            'language'       => ['required', Rule::in($this->languages)],
            'message'        => ['required', 'string'],
        ]);

        $data['owner_id']   = Auth::id();
        $data['company_id'] = $company;

        $template = WhatsappTemplate::create($data);

        return response()->json([
            'message'  => 'Template saved successfully.',
            'template' => $template,
        ], 201);
    }

    public function storeParameter(Request $request, $company): JsonResponse
    {
        $request->validate([
            'label' => ['required', 'string', 'max:100'],
        ]);

        $ownerId = Auth::id();

        $param = DB::transaction(function () use ($request, $ownerId) {
            $maxFixed  = max(array_keys($this->fixedParams));
            $maxCustom = WhatsappTemplateParameter::forOwner($ownerId)->max('num');
            $nextNum   = max($maxFixed, (int) $maxCustom) + 1;

            return WhatsappTemplateParameter::create([
                'owner_id'  => $ownerId,
                'num'       => $nextNum,
                'label'     => $request->string('label'),
                'is_custom' => true,
            ]);
        });

        return response()->json([
            'message' => 'Parameter added successfully.',
            'num'     => $param->num,
            'label'   => $param->label,
        ], 201);
    }

    public function aiTemplates()
    {
        return view('owner.tally.other.ai-templates');
    }

    public function responses()
    {
        return view('owner.tally.other.responses');
    }

    public function overdueTarget()
    {
        return view('owner.tally.other.overdue-target');
    }


    public function defaultSettings($company)
    {
        try {
            $company = urldecode($company);
            $owner   = Auth::guard('owner')->user();

            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->first();

            if (!$tallyCompany) {
                return back()->with('error', 'Company not found.');
            }

            $ledgerModels = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->whereIn('parent', ['Sundry Debtors', 'Sundry Creditors'])
                ->orderBy('ledger_name')
                ->get();

            $vouchersByLedger = TallyVoucher::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->whereIn('ledger_id', $ledgerModels->pluck('id'))
                ->get()
                ->groupBy('ledger_id');

            $ledgers = $ledgerModels->map(function ($ledger) use ($vouchersByLedger) {
                $vouchers = $vouchersByLedger->get($ledger->id, collect());

                return [
                    'id'                 => $ledger->id,
                    'name'               => $ledger->ledger_name,
                    'under'              => $ledger->parent,
                    'mobile'             => $ledger->ledger_mobile_number,
                    'ledger_mobile_number_source'             => $ledger->ledger_mobile_number_source,
                    'credit_period'      => $ledger->credit_period,
                    'credit_period_source'      => $ledger->credit_period_source,
                    'interest_rate'      => $ledger->interest_rate,
                    'interest_rate_source'      => $ledger->interest_rate_source,
                    'interest_style'     => $ledger->interest_style,
                    'assigned_collector' => $ledger->assigned_collector,
                    'rows'               => $this->buildLedgerRows($ledger, $vouchers),
                ];
            })->values()->all();

            // echo "<pre>"; print_r($ledgers); die;

            return view('owner.tally.other.default-settings', compact('ledgers', 'company'));

        } catch (Exception $e) {
            Log::error('Tally companyLedgers failed', [
                'company' => $company,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to fetch ledgers from Tally. Please try again.');
        }
    }


    public function setCommonSetting(Request $request)
    {
        $validated = $request->validate([
            'company'       => 'required|string',
            'under'         => 'required|in:Sundry Debtors,Sundry Creditors',
            'credit_period' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0',
            'collector_id'  => 'nullable|integer',
        ]);

        $company      = $validated['company'];
        $creditPeriod = $validated['credit_period'] ?? null;
        $interestRate = $validated['interest_rate'] ?? null;
        $collectorId  = $validated['collector_id'] ?? null;

        if (blank($creditPeriod) && blank($interestRate) && blank($collectorId)) {
            return response()->json([
                'success' => false,
                'message' => 'Please fill at least one default value before saving.',
            ], 422);
        }

        $owner = Auth::guard('owner')->user();

        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $company)
            ->first();

        if (!$tallyCompany) {
            Log::warning('setCommonSetting: company not found', ['company' => $company]);
            return response()->json([
                'success' => false,
                'message' => 'Company not found.',
            ], 404);
        }

        $ledgers = TallyLedger::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('parent', $validated['under'])
            ->get();

        if ($ledgers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No ' . $validated['under'] . ' ledgers found for this company.',
            ], 404);
        }

        $updatedCount = 0;

        foreach ($ledgers as $ledger) {
            $dataToUpdate = [];

            // $isBillByBillYes = strtolower((string) $ledger->maintain_bill_by_bill) === 'yes';

            if (!blank($creditPeriod) && $ledger->credit_period_source !== 'tally') {
                $dataToUpdate['credit_period']        = $creditPeriod . ' Days';
                $dataToUpdate['credit_period_source'] = 'default';
            }

            if (!blank($interestRate) && $ledger->interest_rate_source !== 'tally') {
                $dataToUpdate['interest_rate']        = $interestRate;
                $dataToUpdate['interest_rate_source'] = 'default';
            }

            if (!blank($collectorId)) {
                $dataToUpdate['assigned_collector'] = $collectorId;
            }

            if (!empty($dataToUpdate)) {
                $ledger->update($dataToUpdate);
                $updatedCount++;
            }
        }

        if ($updatedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'All ledgers already have these values set — nothing was updated.',
            ], 200);
        }

        $freshLedgers = TallyLedger::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('parent', $validated['under'])
            ->get()
            ->map(function ($l) {
                $collector = $l->assigned_collector
                    ? Collector::find($l->assigned_collector)
                    : null;

                return [
                    'name'                  => $l->name ?? $l->ledger_name ?? '-',
                    'mobile'                => $l->mobile,
                    'credit_period'         => $l->credit_period,
                    'interest_rate'         => $l->interest_rate,
                    'maintain_bill_by_bill' => $l->maintain_bill_by_bill,
                    'collector_name'        => $collector->name ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => "Default settings applied to {$updatedCount} ledger(s) successfully.",
            'under'   => $validated['under'],
            'ledgers' => $freshLedgers,
        ]);
    }
   


    private function buildLedgerRows($ledger, $vouchers)
    {
        if (empty($ledger->mobile_number)) {
            return [];
        }

        $sum = fn ($types) => $vouchers->whereIn('voucher_type', (array) $types)->sum('amount');
        $isDebtor = $ledger->parent === 'Sundry Debtors';

        $rows = [
            'Balance' => (float) ($ledger->closing_balance ?? 0),
            'Due'     => (float) $vouchers->where('is_due', true)->sum('amount'),
            'Target'  => (float) ($ledger->target_amount ?? 0),
        ];

        if ($isDebtor) {
            $rows['Sale']              = $sum(['Sales']);
            $rows['Other Debits']      = $sum(['Debit Note', 'Journal']);
            $rows['Receipts']          = $sum(['Receipt']);
            $rows['Interest Received'] = $sum(['Interest Received']);
        } else {
            $rows['Purchase']      = $sum(['Purchase']);
            $rows['Other Credits'] = $sum(['Credit Note', 'Journal']);
            $rows['Payments']      = $sum(['Payment']);
            $rows['Interest Paid'] = $sum(['Interest Paid']);
        }

        $rows['Not Due']                = (float) $vouchers->where('is_due', false)->sum('amount');
        $rows['Interest Cost']          = (float) ($ledger->interest_cost ?? 0);
        $rows['Interest Due']           = (float) ($ledger->interest_due ?? 0);
        $rows['Interest Waived']        = (float) ($ledger->interest_waived ?? 0);
        $rows['Bad Debts / Family A/c'] = (float) ($ledger->bad_debts ?? 0);
        $rows[$isDebtor ? 'Total Debtors' : 'Total Creditors'] = (float) ($ledger->closing_balance ?? 0);
        $rows['March Closing Pending']  = (float) ($ledger->march_closing_pending ?? 0);

        return $rows;
    }



    public function masterSettings($company)
    {

        try {
            $company = urldecode($company);
            $owner   = Auth::guard('owner')->user();

            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->first();


            $ledgerModels = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->whereIn('parent', ['Sundry Debtors', 'Sundry Creditors'])
                ->orderBy('ledger_name')
                ->get();

            $vouchersByLedger = TallyVoucher::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->whereIn('ledger_id', $ledgerModels->pluck('id'))
                ->get()
                ->groupBy('ledger_id');

            $ledgers = $ledgerModels->map(function ($ledger) use ($vouchersByLedger) {
                $vouchers = $vouchersByLedger->get($ledger->id, collect());

                $currentBalance = $vouchers->sum(function ($v) {
                    return ($v->debit ?? 0) - ($v->credit ?? 0);
                });

                $balanceLimit = $ledger->balance_limit;

                $overlimit = null;
                if (!is_null($balanceLimit) && $currentBalance > $balanceLimit) {
                    $overlimit = $currentBalance - $balanceLimit;
                }

                return [
                    'id'                 => $ledger->id,   // <-- ADD THIS LINE
                    'name'               => $ledger->ledger_name,
                    'under'              => $ledger->parent,
                    'mobile'             => $ledger->ledger_mobile_number,
                    'ledger_mobile_number_source'             => $ledger->ledger_mobile_number_source,
                    'credit_period'      => $ledger->credit_period,
                    'credit_period_source'      => $ledger->credit_period_source,
                    'interest_rate'      => $ledger->interest_rate,
                    'interest_rate_source'      => $ledger->interest_rate_source,
                    'assigned_collector' => $ledger->assigned_collector,
                    'balance_limit'      => $balanceLimit,
                    'overlimit'          => $overlimit,
                    'mark'               => $ledger->mark ?? 'unmarked',
                    'red_reason'         => $ledger->red_reason ?? '',
                    'rows'               => $this->buildLedgerRows($ledger, $vouchers),
                ];
            })->values()->all();

            // echo "<pre>"; print_r($ledgers); die;
            return view('owner.tally.other.master-settings', compact('ledgers', 'company'));

        } catch (\Exception $e) {
            Log::error('Tally companyLedgers failed', [
                'company' => $company,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to fetch ledgers from Tally. Please try again.');
        }
    }

    
   
    
    public function saveRow(Request $request, $company)
    {
        $company = urldecode($company);
        $owner   = Auth::guard('owner')->user();
    
        $validator = Validator::make($request->all(), [
            'id'            => 'required|integer|exists:rms_tally_ledgers,id',
            'under'         => 'required|string|in:Sundry Debtors,Sundry Creditors',
            'mobile'        => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) {
                if (empty($value)) {
                    return;
                }
    
                // Mobile numbers arrive as a comma-separated string,
                // e.g. "9876543210,9123456780". Validate each piece.
                $numbers = array_filter(array_map('trim', explode(',', $value)));
    
                if (empty($numbers)) {
                    return;
                }
    
                foreach ($numbers as $number) {
                    if (!preg_match('/^\d{10}$/', $number)) {
                        $fail("\"{$number}\" is not a valid mobile number. It must be exactly 10 digits.");
                        return;
                    }
                }
            }],
            'balance_limit'      => 'nullable|numeric|min:0',
            'overlimit'          => 'nullable|string',
            'mark'               => 'nullable|in:red,green,unmarked',
            'red_reason'         => 'nullable|string|max:255',
            // New: Credit Period (days) and Collector, editable for Sundry Debtors.
            'credit_period'      => 'nullable|integer|min:0',
            'assigned_collector' => 'nullable|integer|exists:collectors,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }
    
        $data = $validator->validated();
        $mark = $data['mark'] ?? 'unmarked';
    
        if ($data['under'] === 'Sundry Debtors' && $mark === 'red' && empty($data['red_reason'])) {
            return response()->json([
                'success' => false,
                'errors'  => ['red_reason' => ['Reason is required when marked red.']],
            ], 422);
        }
    
        // Normalize the mobile number list: trim, drop duplicates, rejoin with a
        // single comma and no stray spaces, so what's stored is always clean.
        if (!empty($data['mobile'])) {
            $numbers = array_filter(array_map('trim', explode(',', $data['mobile'])));
            $data['mobile'] = implode(',', array_values(array_unique($numbers)));
        }
    
        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $company)
            ->first();
    
        if (!$tallyCompany) {
            return response()->json([
                'success' => false,
                'errors'  => ['company' => ['Company not found.']],
            ], 404);
        }
    
        $ledger = TallyLedger::where('id', $data['id'])
            ->where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('parent', $data['under'])
            ->first();
    
        if (!$ledger) {
            return response()->json([
                'success' => false,
                'errors'  => ['id' => ['Ledger not found for this company.']],
            ], 404);
        }
    
        if ($data['under'] === 'Sundry Creditors') {
            $ledger->update([
                'ledger_mobile_number' => $data['mobile'] ?? $ledger->ledger_mobile_number,
            ]);
        } else {
            // A credit_period key present in the request (even if it's an empty/zero value
            // the user intentionally set) counts as a manual override, so we flip the
            // source to 'default' — same pattern used for the mobile number source.
            $creditPeriodManuallySet = array_key_exists('credit_period', $data) && $request->filled('credit_period');
    
            $ledger->update([
                'ledger_mobile_number' => $data['mobile'] ?? $ledger->ledger_mobile_number,
                'balance_limit'        => $data['balance_limit'] ?? null,
                'overlimit'            => $data['overlimit'] ?? null,
                'mark'                 => $mark,
                'red_reason'           => $mark === 'red' ? $data['red_reason'] : null,
                'credit_period'        => $data['credit_period'] ?? $ledger->credit_period,
                'credit_period_source' => $creditPeriodManuallySet ? 'default' : $ledger->credit_period_source,
                'assigned_collector'   => $data['assigned_collector'] ?? null,
            ]);
        }
    
        return response()->json([
            'success'              => true,
            'message'              => 'Ledger updated successfully.',
            'data'                 => $ledger,
            // Frontend reads these to refresh the Tally/Default badges without a full reload.
            'mobile_source'        => $ledger->ledger_mobile_number_source ?? null,
            'credit_period_source' => $ledger->credit_period_source ?? null,
        ]);
    }

    public function charts()
    {
        return view('owner.tally.reports.charts');
    }


    public function salesAnalysis()
    {
        return view('owner.tally.reports.sales-analysis');
    }


    public function setDebtorEMI($company)
    {
        try {

            $company = urldecode($company);

            $xml = $this->tally->getLedgers($company);

            $xml = preg_replace('/&#x?0*4;?/i', '', $xml);
            $xml = preg_replace('/&#[0-8];|&#1[0-9];|&#2[0-9];|&#3[0-1];/', '', $xml);

            $xml = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $xml);

            libxml_use_internal_errors(true);

            $xmlObj = simplexml_load_string($xml);

            if ($xmlObj === false) {
                foreach (libxml_get_errors() as $error) {
                    dump($error->message);
                }
            }

            $xmlObj = simplexml_load_string($xml);

            $ledgers = [];

            if ($xmlObj) {

                $nodes = $xmlObj->xpath("//*[local-name()='LEDGER']");

                if ($nodes) {

                    foreach ($nodes as $ledger) {

                        $name = (string) ($ledger['NAME'] ?? '');
                        $under = (string) ($ledger->PARENT ?? '');

                        if (
                            in_array(
                                trim($under),
                                ['Sundry Debtors', 'Sundry Creditors']
                            )
                        ) {
                            $ledgers[] = [
                                'name' => $name,
                                'under' => $under,
                            ];
                        }
                    }
                }
            }

            return view(
                'owner.tally.tally.set-debtor-emi',
                compact('company', 'ledgers')
            );

        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }
    
    // tally ke functions end



    // manual ke functions starts
    public function manualDashboard()
    {
        return view('owner.manual.dashboard');

    }

    public function createStudent()
    {
        return view('owner.manual.students-create');

    }


    public function students()
    {
        return view('owner.manual.students');

    }

    public function editStudent()
    {
        return view('owner.manual.students-edit');

    }

    public function manualAccountants()
    {
        $accountants = Accountant::where('owner_id', auth()->id())->get();
        return view('owner.manual.accountant.accountants-list', compact('accountants'));

    }

    public function createmanualAccountant()
    {
        return view('owner.manual.accountant.accountant-create');
    }
    

   public function manualCollectors()
    {
        $collectors = Collector::select(
                'rms_collectors.*',
                'rms_accountants.name as accountant_name'
            )
            ->from('rms_collectors')
            ->join(
                'rms_accountants',
                'rms_collectors.accountant_id',
                '=',
                'rms_accountants.id'
            )
            ->where('rms_accountants.owner_id', auth()->id())
            ->get();

        return view(
            'owner.manual.collectors.collectors-list',
            compact('collectors')
        );
    }

    public function editmanualCollector($id)
    {
        $collector = Collector::find($id);
        return view('owner.manual.collectors.collector-edit', compact('collector'));

    }

    public function createmanualCollector()
    {
        return view('owner.manual.collectors.collector-create');
    }


    public function manualSalesReceipts()
    {
        return view('owner.manual.sales-receipts');
    }


    public function salesReceipt($id)
    {
     return view('owner.manual.sales_receipt');
    }



    public function manualFollowUpsHub(Request $request)
    {
        $type   = $request->query('type') ?: ''; 
        $allLedgers = [
            [
                'name' => 'Rahul Sharma', 'mobile' => '9876543210',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 54321.10, 'target' => 50000,
            ],
            [
                'name' => 'Priya Verma', 'mobile' => '9123456780',
                'type' => 'Follow Up-Balances/Targets(months)',
                'balance' => 72500.00, 'target' => 80000,
                'target_month' => now()->subMonths(1)->format('Y-m'),
            ],
            [
                'name' => 'Aman Gupta', 'mobile' => '9988776655',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 18200.50, 'age_bucket' => '31-60 Days', 'days' => 45,
            ],
            [
                'name' => 'Sneha Patel', 'mobile' => '9871234567',
                'type' => 'Follow Up-Balances/Days(10-20-30)',
                'balance' => 9800.00, 'days' => 20,
                'date' => now()->subDays(20)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2204',
                'interest_due' => 150.00,
            ],
            [
                'name' => 'Vikram Singh', 'mobile' => '9012345678',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(21)->format('Y-m-d'),
                'due_date' => now()->addDays(21)->format('Y-m-d'),
                'inv_no' => 'INV-2201', 'days' => 12,
                'due' => 32000.00, 'interest_due' => 480.00,
            ],
            [
                'name' => 'Anjali Mehta', 'mobile' => '9090909090',
                'type' => 'Follow Up-Not Due',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'due_date' => now()->addDays(35)->format('Y-m-d'),
                'inv_no' => 'INV-2202', 'days_pending' => 5,
                'not_due' => 27500.00,
            ],
            [
                'name' => 'Karan Malhotra', 'mobile' => '9191919191',
                'type' => 'Follow Up Overlimits',
                'balance' => 12500.00,
            ],
            [
                'name' => 'Neha Joshi', 'mobile' => '9898989898',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 61230.00, 'target' => 55000,
            ],
            [
                'name' => 'Rohan Desai', 'mobile' => '9797979797',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(10)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2203', 'days' => 18,
                'due' => 41500.00, 'interest_due' => 620.00,
            ],
            [
                'name' => 'Ishita Rao', 'mobile' => '9696969696',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 9450.75, 'age_bucket' => '0-30 Days', 'days' => 15,
            ],
            [
                'name' => 'Aditya Kumar', 'mobile' => '9595959595',
                'type' => 'Follow Up-Balances',
                'balance' => 15750.25,
            ],
        ];

        $followUps = collect($allLedgers)->values()->map(function ($l, $index) {
            $balance   = $l['balance'] ?? 0;
            $target    = $l['target'] ?? 0;
            $targetPct = $target > 0 ? round((abs($balance) / $target) * 100, 2) : 0;

            return [
                'id'                => $index + 1,
                'name'              => $l['name'] ?? '-',
                'mobile'            => $l['mobile'] ?? '-',
                'under'             => $l['under'] ?? '-',
                'type'              => $l['type'] ?? '-',
                'balance'           => $balance,
                'target'            => $target,
                'target_pct'        => $targetPct,
                'target_month'      => $l['target_month'] ?? null,
                'age_bucket'        => $l['age_bucket'] ?? null,
                'days'              => $l['days'] ?? null,
                'days_pending'      => $l['days_pending'] ?? null,
                'date'              => $l['date'] ?? null,
                'due_date'          => $l['due_date'] ?? null,
                'inv_no'            => $l['inv_no'] ?? null,
                'due'               => $l['due'] ?? null,
                'not_due'           => $l['not_due'] ?? null,
                'interest_due'      => $l['interest_due'] ?? null,
                'accountant_status' => rand(0, 1) ? 'Pending' : 'Responded',
            ];
        });

        if (!empty($type) && $type !== 'Follow Up-Quick /Smart - Follow -up') {
            $followUps = $followUps->filter(fn($row) => $row['type'] === $type)->values();
        }

        return view('owner.manual.followup-hub', [
            'followUps'      => $followUps,
            'type'           => $type,
        ]);
    }

    public function manualTelecaller()
    {
        return view('owner.manual.followup-action.telecaller');
    }

    public function manualCall()
    {
        return view('owner.manual.followup-action.call');
    }

    public function manualWhatsappMessage()
    {
        return view('owner.manual.followup-action.whatsapp-message');
    }

    public function manualPhysicalVisit()
    {
        return view('owner.manual.followup-action.physical-visit');
    }

    public function manualEscalation()
    {
        return view('owner.manual.followup-action.escalation');
    }

    public function manualFollowupHistory()
    {
        $typeLabels = [
            'balances'      => 'Follow Up-Balances',
            'pct_targets'    => 'Follow Up-Balances/%Targets',
            'targets_months' => 'Follow Up-Balances/Targets(months)',
            'days_agewise'   => 'Follow Up-Balances/Days(agewise)',
            'days_10_20_30'  => 'Follow Up-Balances/Days(10-20-30)',
            'due'            => 'Follow Up-Due',
            'not_due'        => 'Follow Up-Not Due',
            'overlimits'     => 'Follow Up Overlimits',
        ];
    
        $actionLabels = [
            'allocate_tele_call' => 'Allocate to tele call',
            'call'                => 'Call',
            'whatsapp'            => 'WhatsApp',
            'physical_visit'      => 'Physical visit',
            'escalation'          => 'Escalation',
            'no_action'           => 'No action',
        ];
      
        $history = [
            [
                'id' => 1, 'ledger_id' => 101, 'student_name' => 'Rahul Mehta', 'mobile' => '9000000001',
                'type' => 'pct_targets', 'type_label' => $typeLabels['pct_targets'],
                'balance' => 54321.10, 'target' => 50000, 'target_pct' => 108.64,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01', 'action' => 'call', 'action_label' => $actionLabels['call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th', 'solution' => 'Payment plan agreed', 'admin_solution' => null,
            ],
            [
                'id' => 2, 'ledger_id' => 101, 'student_name' => 'Rahul Mehta', 'mobile' => '9000000001',
                'type' => 'due', 'type_label' => $typeLabels['due'],
                'balance' => 32000, 'target' => null, 'target_pct' => null,
                'date' => '2026-07-20', 'due_date' => '2026-08-10', 'inv_no' => 'INV-2201', 'days' => 12, 'interest_due' => 480,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-02', 'action' => 'whatsapp', 'action_label' => $actionLabels['whatsapp'],
                'frequency' => 'daily', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 3, 'ledger_id' => 101, 'student_name' => 'Rahul Mehta', 'mobile' => '9000000001',
                'type' => 'overlimits', 'type_label' => $typeLabels['overlimits'],
                'balance' => 12500, 'target' => null, 'target_pct' => null,
                'assigned_to' => 'Priya', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-28', 'action' => 'escalation', 'action_label' => $actionLabels['escalation'],
                'frequency' => 'weekly', 'response_date' => '2026-07-30',
                'response' => 'Escalated to regional manager', 'solution' => 'Limit revised', 'admin_solution' => null,
            ],
            [
                'id' => 4, 'ledger_id' => 102, 'student_name' => 'Sneha Kapoor', 'mobile' => '9000000002',
                'type' => 'targets_months', 'type_label' => $typeLabels['targets_months'],
                'balance' => 78900, 'target' => 80000, 'target_pct' => 98.63,
                'due_date' => '2026-07-31', // used as Target Month for this type
                'assigned_to' => 'Aman', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-15', 'action' => 'physical_visit', 'action_label' => $actionLabels['physical_visit'],
                'frequency' => 'monthly', 'response_date' => '2026-07-20',
                'response' => 'Visited site, cheque collected', 'solution' => 'Cleared 50% balance', 'admin_solution' => null,
            ],
            [
                'id' => 5, 'ledger_id' => 102, 'student_name' => 'Sneha Kapoor', 'mobile' => '9000000002',
                'type' => 'days_10_20_30', 'type_label' => $typeLabels['days_10_20_30'],
                'balance' => 15600, 'target' => null, 'target_pct' => null, 'days' => 20,
                'assigned_to' => 'Aman', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-05', 'action' => 'no_action', 'action_label' => $actionLabels['no_action'],
                'frequency' => 'daily', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 6, 'ledger_id' => 103, 'student_name' => 'Amit Deshmukh', 'mobile' => '9000000003',
                'type' => 'not_due', 'type_label' => $typeLabels['not_due'],
                'balance' => 43200, 'target' => null, 'target_pct' => null,
                'date' => '2026-07-25', 'due_date' => '2026-08-25', 'inv_no' => 'INV-3105', 'days_pending' => 30,
                'assigned_to' => 'Priya', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-06', 'action' => 'allocate_tele_call', 'action_label' => $actionLabels['allocate_tele_call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-07',
                'response' => 'Confirmed, will pay on due date', 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 7, 'ledger_id' => 104, 'student_name' => 'Karan Malhotra', 'mobile' => '9000000004',
                'type' => 'days_agewise', 'type_label' => $typeLabels['days_agewise'],
                'balance' => 99500, 'target' => null, 'target_pct' => null, 'days' => 46,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-10', 'action' => 'escalation', 'action_label' => $actionLabels['escalation'],
                'frequency' => 'monthly', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 8, 'ledger_id' => 105, 'student_name' => 'Divya Nair', 'mobile' => '9000000001',
                'type' => 'balances', 'type_label' => $typeLabels['balances'],
                'balance' => 54321.10, 'target' => 50000, 'target_pct' => 108.64,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01', 'action' => 'call', 'action_label' => $actionLabels['call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th', 'solution' => 'Payment plan agreed', 'admin_solution' => null,
            ],
        ];
    
        return view('owner.manual.followup-history', compact('history'));
    }


    public function manualTemplates()
    {
        return view('owner.manual.other.templates');
    }


    public function manualResponses()
    {
        return view('owner.manual.other.responses');
    }


    public function manualOverdueTarget()
    {
        return view('owner.manual.other.overdue-target');
    }


    public function manualDefaultSettings()
    {
        return view('owner.manual.other.default-settings');
    }


    public function manualMasterSettings()
    {
        return view('owner.manual.other.debtors');
    }


    public function manualTrackCollectors()
    {
        return view('owner.manual.track-collector');
    }

    
    public function manualCharts()
    {
        return view('owner.manual.reports.charts');
    }


    public function manaulSalesAnalysis()
    {
        return view('owner.manual.reports.sales-analysis');
    }


    public function manaualSetDebtorEMI()
    {
        return view('owner.manual.set-debtor-emi');
    }

    // manual ke functions end
}
                                     