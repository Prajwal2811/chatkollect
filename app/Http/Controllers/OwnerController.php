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
use App\Models\OverdueTargetSetting;
use App\Models\TallyCompany;
 
use App\Models\TallyResponse;
use App\Models\TallySolution;

 
use App\Models\Followup;
use App\Models\FollowupLog;
 
use App\Services\TallyService;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Models\OwnerBankDetail;
use Illuminate\Support\Facades\Artisan;


use Illuminate\Validation\ValidationException;

class OwnerController extends Controller
{
    protected TallyService $tally;

    public function __construct(TallyService $tally)
    {
        $this->tally = $tally;
    }


    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return response()->json(['message' => 'All caches cleared successfully.']);
    }


    public function subscription()
    {
        return view('owner.tally.subscription');
    }

    public function subscribe()
    {
        $owner = auth('owner')->user();

        $owner->update([
            'is_subscribed' => "true"
        ]);

        return redirect()->route('owner.tally.dashboard')->with('success', 'Subscription activated successfully.');
    }


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
                    'email' => $owner->email,
                    'selected_type' => $request->account_type,
                    'actual_type' => $owner->business_type,
                    'time' => now(),
                ]);

                session()->flash('error', 'This account is not registered under the selected type.');

                return back()->withInput($request->only('email', 'account_type'));
            }

            Log::info('Owner login successful', [
                'email' => $owner->email,
                'name' => $owner->owner_name,
                'type' => $owner->business_type,
                'time' => now(),
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
            'time' => now(),
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
            'port' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $existingConnection = TallyConnection::where('owner_id', $owner->id)->first();
            $isUpdate = (bool) $existingConnection;

            $tallyConnection = TallyConnection::updateOrCreate(
                ['owner_id' => $owner->id],
                [
                    'tailscale_ip' => $request->tailscale_ip,
                    'port' => $request->port,
                    'status' => 'connected',
                ]
            );

            return response()->json([
                'message' => $isUpdate
                    ? 'Tally connection updated successfully.'
                    : 'Tally connected successfully.',
                'data' => $tallyConnection,
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
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:30'],
            'ifsc_code' => ['required', 'string', 'max:15'],
            'upi' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $wasAlreadyAdded = OwnerBankDetail::where('owner_id', $owner->id)->exists();

        OwnerBankDetail::updateOrCreate(
            ['owner_id' => $owner->id],
            [
                'account_holder_name' => $request->account_holder_name,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'ifsc_code' => strtoupper($request->ifsc_code),
                'branch_name' => $request->branch_name,
                'upi' => $request->upi,
            ]
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

                DB::transaction(function () use ($companyName, $owner, $now, &$summary) {

                    $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                        ->where('company_name', $companyName)
                        ->first();

                    if ($tallyCompany) {

                        $tallyCompany->update([
                            'last_synced_at' => $now,
                        ]);

                    } else {

                        $tallyCompany = TallyCompany::create([
                            'owner_id' => $owner->id,
                            'company_name' => $companyName,
                            'last_synced_at' => $now,
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
                                        'company' => $companyName,
                                        'voucher_type' => $voucherTypeName,
                                        'mapped_to' => null,
                                        'created_at' => $now,
                                        'updated_at' => $now,
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
                                    // 'maintain_bill_by_bill'         => $ledger['maintain_bill_by_bill'],
                                    // 'activate_interest_calculation' => $ledger['activate_interest_calculation'],
                                    'balance_synced_at'             => $now,
                                    'updated_at'                    => $now,
                                ];

                                if (empty($tallyLedger->ledger_mobile_number_source)) {
                                    $updateData['ledger_mobile_number'] = $ledger['ledger_mobile_number'];
                                    $updateData['ledger_mobile_number_source'] = !empty($ledger['ledger_mobile_number']) ? 'tally' : null;
                                }


                                if ($tallyLedger->credit_period_source !== 'default') {
                                    $updateData['credit_period'] = $ledger['credit_period'] . ' Days';
                                    $updateData['credit_period_source'] = !empty($ledger['credit_period']) ? 'tally' : null;
                                }


                                if ($tallyLedger->interest_rate_source !== 'default') {
                                    $updateData['interest_rate'] = $ledger['interest_rate'];
                                    $updateData['interest_style'] = $ledger['interest_style'];
                                    $updateData['interest_rate_source'] = !empty($ledger['interest_rate']) ? 'tally' : null;
                                }

                                $tallyLedger->update($updateData);

                            } else {

                                $tallyLedger = TallyLedger::create([
                                    'master_id' => $ledger['master_id'],
                                    'owner_id' => $owner->id,
                                    'tally_company_id' => $tallyCompany->id,
                                    'ledger_name' => $ledger['ledger_name'],
                                    'ledger_email' => $ledger['ledger_email'],
                                    'ledger_mobile_number' => $ledger['ledger_mobile_number'],
                                    'ledger_mobile_number_source' => !empty($ledger['ledger_mobile_number']) ? 'tally' : null,
                                    'parent' => $ledger['parent'],
                                    'opening_balance' => $ledger['opening_balance'],
                                    'closing_balance' => $ledger['closing_balance'],
                                    'credit_period' => $ledger['credit_period'],
                                    'credit_period_source' => !empty($ledger['credit_period']) ? 'tally' : null,
                                    'interest_rate' => $ledger['interest_rate'],
                                    'interest_style' => $ledger['interest_style'],
                                    'interest_rate_source' => !empty($ledger['interest_rate']) ? 'tally' : null,
                                    // 'maintain_bill_by_bill'         => $ledger['maintain_bill_by_bill'],
                                    // 'activate_interest_calculation' => $ledger['activate_interest_calculation'],
                                    'balance_synced_at' => $now,
                                    'created_at' => $now,
                                    'updated_at' => $now,
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

                            $voucherUpdate = [
                                'master_id' => $voucherData['master_id'] ?: $tallyVoucher->master_id,
                                'ledger_id' => $ledgerId,
                                'date' => $voucherData['date'],
                                'voucher_number' => $voucherData['voucher_number'],
                                'party_ledger_name' => $voucherData['party_ledger_name'],
                                'amount' => $voucherData['amount'],
                                'updated_at' => $now,
                            ];

                            if ($tallyVoucher->credit_period_source !== 'voucher') {
                                $voucherUpdate['credit_period'] = $creditPeriod;
                                $voucherUpdate['due_date'] = $dueDate;
                            }

                            $tallyVoucher->update($voucherUpdate);

                        } else {

                            $tallyVoucher = TallyVoucher::create([
                                'owner_id' => $owner->id,
                                'tally_company_id' => $tallyCompany->id,
                                'ledger_id' => $ledgerId,
                                'master_id' => $voucherData['master_id'],
                                'date' => $voucherData['date'],
                                'voucher_number' => $voucherData['voucher_number'],
                                'voucher_type' => $voucherData['voucher_type'],
                                'party_ledger_name' => $voucherData['party_ledger_name'],
                                'amount' => $voucherData['amount'],
                                'credit_period' => $creditPeriod,
                                'due_date' => $dueDate,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);

                            $uniqueVoucherId = $this->buildVoucherUniqueId(
                                $companyName,
                                $voucherData['party_ledger_name'],
                                $voucherData['voucher_type'],
                                $tallyVoucher->id
                            );

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
            'debtor.mobile_number' => ['nullable', 'string', 'max:20'],
            'debtor.credit_period' => ['nullable', 'integer', 'min:0'],
            'debtor.interest_rate' => ['nullable', 'numeric', 'min:0'],
            'debtor.balance_limit' => ['nullable', 'numeric', 'min:0'],

            'creditor.mobile_number' => ['nullable', 'string', 'max:20'],
            'creditor.credit_period' => ['nullable', 'integer', 'min:0'],
            'creditor.interest_rate' => ['nullable', 'numeric', 'min:0'],
            'creditor.balance_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $debtorMobileUpdated = 0;
        $debtorCreditUpdated = 0;
        $debtorInterestUpdated = 0;
        $debtorBalanceUpdated = 0;

        $creditorMobileUpdated = 0;
        $creditorCreditUpdated = 0;
        $creditorInterestUpdated = 0;
        $creditorBalanceUpdated = 0;

        if ($request->filled('debtor.mobile_number')) {
            $debtorMobileUpdated = TallyLedger::where('owner_id', $owner->id)
                ->where('parent', 'Sundry Debtors')
                ->whereNull('ledger_mobile_number_source')
                ->update([
                    'ledger_mobile_number' => $request->input('debtor.mobile_number'),
                    'ledger_mobile_number_source' => 'default',
                    'updated_at' => now(),
                ]);
        }

        if ($request->filled('debtor.credit_period')) {
            $debtorCreditUpdated = TallyLedger::where('owner_id', $owner->id)
                ->where('parent', 'Sundry Debtors')
                ->whereNull('credit_period_source')
                ->update([
                    'credit_period' => $request->input('debtor.credit_period') . ' Days',
                    'credit_period_source' => 'default',
                    'updated_at' => now(),
                ]);
        }

        if ($request->filled('debtor.interest_rate')) {
            $debtorInterestUpdated = TallyLedger::where('owner_id', $owner->id)
                ->where('parent', 'Sundry Debtors')
                ->whereNull('interest_rate_source')
                ->update([
                    'interest_rate' => $request->input('debtor.interest_rate'),
                    'interest_rate_source' => 'default',
                    'updated_at' => now(),
                ]);
        }

        if ($request->filled('debtor.balance_limit')) {
            $debtorBalanceUpdated = TallyLedger::where('owner_id', $owner->id)
                ->where('parent', 'Sundry Debtors')
                ->whereNull('balance_limit_source')
                ->update([
                    'balance_limit' => $request->input('debtor.balance_limit'),
                    'balance_limit_source' => 'default',
                    'updated_at' => now(),
                ]);
        }


        if ($request->filled('creditor.mobile_number')) {
            $creditorMobileUpdated = TallyLedger::where('owner_id', $owner->id)
                ->where('parent', 'Sundry Creditors')
                ->whereNull('ledger_mobile_number_source')
                ->update([
                    'ledger_mobile_number' => $request->input('creditor.mobile_number'),
                    'ledger_mobile_number_source' => 'default',
                    'updated_at' => now(),
                ]);
        }

        if ($request->filled('creditor.credit_period')) {
            $creditorCreditUpdated = TallyLedger::where('owner_id', $owner->id)
                ->where('parent', 'Sundry Creditors')
                ->whereNull('credit_period_source')
                ->update([
                    'credit_period' => $request->input('creditor.credit_period') . ' Days',
                    'credit_period_source' => 'default',
                    'updated_at' => now(),
                ]);
        }

        if ($request->filled('creditor.interest_rate')) {
            $creditorInterestUpdated = TallyLedger::where('owner_id', $owner->id)
                ->where('parent', 'Sundry Creditors')
                ->whereNull('interest_rate_source')
                ->update([
                    'interest_rate' => $request->input('creditor.interest_rate'),
                    'interest_rate_source' => 'default',
                    'updated_at' => now(),
                ]);
        }

        if ($request->filled('creditor.balance_limit')) {
            $creditorBalanceUpdated = TallyLedger::where('owner_id', $owner->id)
                ->where('parent', 'Sundry Creditors')
                ->whereNull('balance_limit_source')
                ->update([
                    'balance_limit' => $request->input('creditor.balance_limit'),
                    'balance_limit_source' => 'default',
                    'updated_at' => now(),
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
        $ledgerPart = $extractPart($ledgerName);
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
                if ($d->year > 2000) {
                    return $d;
                }
            } catch (\Throwable $e) {
                Log::warning('Invalid voucher due date', [
                    'voucher_due_date' => $voucherDueDate,
                    'voucher_date' => $voucherDate->toDateString(),
                    'credit_period' => $creditPeriod,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $voucherDate->copy()->startOfDay()->addDays($creditPeriod);
    }

    // Sale aur Interest Breakdown dono yahi use karenge
    private function overdueDays($dueDate, Carbon $today): int
    {
        if (empty($dueDate)) {
            return 0;
        }

        $due = Carbon::parse($dueDate)->startOfDay();

        return $due->lt($today) ? (int) $due->diffInDays($today, false) : 0;
    }
 

    // $rows = classifyVoucherRow() se bane vouchers, $under = Debtor/Creditor, $creditPeriod = ledger ke days
    // $today = aaj ki date, $asOf = (optional) kisi purani date ke hisaab se snapshot chahiye to wo date
    private function buildOpenInvoices($rows, string $under, int $creditPeriod, Carbon $today, ?Carbon $asOf = null)
    {
        // Creditor hai ya nahi (nahi hai to Debtor maana jayega)
        $isCreditor = $under === 'Sundry Creditors';

        // Date-wise snapshot: agar $asOf diya hai (jaise pichhle mahine ka end) to us date tak ke hi vouchers lo
        // (invoice aur receipt dono, taaki us din ki sthiti dikhe)
        if ($asOf) {
            // $asOf ki copy li (original object na bigde) aur us din ka end (23:59:59) set kiya
            $cut = $asOf->copy()->endOfDay();

            // Sirf wahi rows rakhi jinki date cutoff se pehle ya barabar hai
            $rows = $rows->filter(fn($r) => Carbon::parse($r['date'])->lte($cut));
        }

        // Reference din: snapshot hai to $asOf, warna aaj. Overdue days isi din ke hisaab se nikalenge
        $refDay = ($asOf ?? $today)->copy()->startOfDay();

        // INVOICES list banai
        $invoices = $rows
            ->filter(fn($r) => $isCreditor
                // Creditor: purchase type + credit amount > 0
                ? ($r['mapped_type_low'] === 'purchase' && bccomp($r['credit'], '0', 2) > 0)
                // Debtor: sales type + debit amount > 0
                : ($r['mapped_type_low'] === 'sales' && bccomp($r['debit'], '0', 2) > 0))
            // Sabse purani invoice pehle (FIFO ke liye zaroori)
            ->sortBy(fn($r) => Carbon::parse($r['date'])->timestamp)
            // sortBy ke baad index bigad jaate hain, isliye 0,1,2... dobara
            ->values();

        // POOL: saare receipts (Debtor) ya payments (Creditor) ka total ek number me
        $pool = $rows
            ->filter(fn($r) => $isCreditor
                // Creditor: voucher type me "payment" aur debit > 0
                ? (str_contains($r['voucher_type_low'], 'payment') && bccomp($r['debit'], '0', 2) > 0)
                // Debtor: voucher type me "receipt" aur credit > 0
                : (str_contains($r['voucher_type_low'], 'receipt') && bccomp($r['credit'], '0', 2) > 0))
            // Sab ka amount jod kar ek total (string, bcmath se)
            ->reduce(fn($t, $r) => bcadd($t, $isCreditor ? $r['debit'] : $r['credit'], 2), '0');

        return $invoices
            // &$pool = reference se, taaki har invoice ke baad pool kam hota rahe (agli invoice ko bacha hua mile)
            ->map(function ($r) use (&$pool, $isCreditor, $creditPeriod, $refDay) {

                // Invoice ki poori amount (Creditor me credit side, Debtor me debit side)
                $original = $isCreditor ? $r['credit'] : $r['debit'];

                // Is invoice me pool se kitna adjust hoga:
                // pool chhota hai to poora pool, warna invoice ki poori amount
                $cleared = bccomp($pool, $original, 2) < 0 ? $pool : $original;

                // Pool se wo amount ghata di (agli invoice ke liye bacha hua)
                $pool = bcsub($pool, $cleared, 2);

                // Pending = invoice amount - clear hui amount
                $pending = bcsub($original, $cleared, 2);

                // Invoice ki date (din ki shuruaat par)
                $voucherDate = Carbon::parse($r['date'])->startOfDay();

                // Due date: voucher me saved due_date valid ho to wahi, warna voucher date + credit period
                $dueDate = $this->resolveDueDate($r['due_date'] ?? null, $voucherDate, $creditPeriod);

                return [
                    'id' => $r['id'] ?? null,                          // voucher ka DB id (kuch jagah 'id' nahi hota to null)
                    'date' => $voucherDate->toDateString(),            // invoice date (Y-m-d)
                    'due_date' => $dueDate->toDateString(),            // due date (Y-m-d)
                    'voucher_number' => $r['voucher_number'] ?? null,  // invoice number
                    // Due date se refDay tak kitne din: positive = overdue, 0 ya negative = abhi due nahi
                    'days' => (int) $dueDate->diffInDays($refDay, false),
                    'original' => $original,                           // invoice ki poori amount
                    'cleared' => $cleared,                             // kitni clear hui
                    'pending' => $pending,                             // kitni baaki
                ];
            })
            // Jo invoices poori clear ho gayi (pending 0) unhe hata do, sirf open bachi rahengi
            ->filter(fn($i) => bccomp($i['pending'], '0', 2) > 0)
            // Index dobara 0,1,2...
            ->values();
    }


    private function isOtherCredit(array $v): bool
    {
        return $v['mapped_type_low'] !== 'purchase'
            && bccomp($v['credit'], '0', 2) > 0;
    }

    public function companyLedgers($company)
    {
        try {
            // URL se aaya raw company naam $com me rakh liya (abhi aage use nahi hua, unused variable hai)
            $com = $company;

            // URL-encoded naam (jaise "ABC%20Traders") ko normal text me badla
            $company = urldecode($company);

            // Logged-in owner (owner guard se) nikala
            $owner = Auth::guard('owner')->user();

            // Is owner ki wahi company dhundhi jiska naam URL wale naam se match kare
            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->first();
            // NOTE: agar company nahi mili to $tallyCompany null hoga aur neeche ->id par error aayega (catch me jayega)

            // Is company ke saare ledgers, naam ke hisaab se A-Z sorted
            $ledgerModels = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->orderBy('ledger_name')
                ->get();

            // In saare ledgers ke vouchers EK hi query me laaye 
            $vouchersByLedger = TallyVoucher::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->whereIn('ledger_id', $ledgerModels->pluck('id'))
                ->get()
                ->groupBy('ledger_id');

            // Voucher type -> mapped type ka dictionary, e.g. ["Sales GST" => "Sales"]
            // Isse pata chalta hai konsa voucher type Sales/Purchase maana jayega
            $voucherMappings = VoucherMapping::where('company', $tallyCompany->company_name)
                ->pluck('mapped_to', 'voucher_type')
                ->toArray();

            // echo "<pre>"; print_r($vouchersByLedger); echo "</pre>"; die;

            // Aaj ki date, din ki shuruaat (00:00:00) par - overdue days nikalne ke kaam aayegi
            $today = Carbon::now()->startOfDay();

            // Owner ne overdue target % set kiye hain (month bucket 1..6 -> receivable target %)
            // pluck se result: [bucket_month => receivable_target]
            $ownerTargets = OverdueTargetSetting::where('owner_id', $owner->id)
                ->where('company_id', $tallyCompany->id)
                ->pluck('receivable_target', 'bucket_month');

            // Abhi ka exact time, target calculation me month-end nikalne ke liye pass hoga
            $nowForTarget = now();

            // ledger_id => [collector_id, ...] banane ke liye is company ki saari collector assignments laaye
            $ledgerCollectorRows = DB::table('rms_ledger_collectors')
                ->where('owner_id', $owner->id)
                ->where('company_id', $tallyCompany->id)
                ->get();

            // Har ledger ke saath uske collector IDs ki list (duplicate hata ke) map me rakhi
            $ledgerCollectorMap = $ledgerCollectorRows
                ->groupBy('ledger_id')
                ->map(fn($rows) => $rows->pluck('collector_id')->unique()->values()->all());

            // Page ke collector dropdown ke liye: sirf wahi collectors jinko is company me kam se kam 1 ledger assign hai
            $collectors = Collector::whereIn('id', $ledgerCollectorRows->pluck('collector_id')->unique())
                ->orderBy('name')
                ->get(['id', 'name']);

            // Ab har ledger ko transform karke ek array banate hain (collection map)
            $ledgers = $ledgerModels->map(function ($ledger) use (
                $vouchersByLedger, $today, $voucherMappings, $ownerTargets, $nowForTarget, $ledgerCollectorMap
            ) {
                // Ledger Debtor hai ya Creditor ("Sundry Debtors" / "Sundry Creditors")
                $under = $ledger->parent;

                // Is ledger ke vouchers nikaale (nahi hain to khali collection)
                // Har voucher ko classifyVoucherRow() se debit/credit/mapped type me classify kiya
                // aur due_date + voucher_number extra jod diye
                $vouchers = $vouchersByLedger->get($ledger->id, collect())
                    ->map(fn($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                        'due_date' => $v->due_date ?? null,
                        'voucher_number' => $v->voucher_number,
                    ]);

                // Helper closures: voucher type me "receipt" ya "payment" word hai ya nahi
                $isReceipt = fn($v) => str_contains($v['voucher_type_low'], 'receipt');
                $isPayment = fn($v) => str_contains($v['voucher_type_low'], 'payment');

                // Saare totals '0' string se shuru (bcmath string par chalta hai, float ki galti se bachne ke liye)
                $sale = $purchase = $otherDebits = $otherCredits = $receipts = $payments = '0';

                if ($under === 'Sundry Creditors') {
                    // CREDITOR: Purchase = purchase type ke vouchers ka credit total
                    $purchase = $vouchers->filter(fn($v) => $v['mapped_type_low'] === 'purchase' && bccomp($v['credit'], '0', 2) > 0)
                        ->reduce(fn($t, $v) => bcadd($t, $v['credit'], 2), '0');

                    // Other Credits = purchase ke alawa baaki credit entries (isOtherCredit helper se)
                    $otherCredits = $vouchers->filter(fn($v) => $this->isOtherCredit($v))
                        ->reduce(fn($t, $v) => bcadd($t, $v['credit'], 2), '0');

                    // Payments = payment vouchers ka debit total (creditor ko paisa diya)
                    $payments = $vouchers->filter(fn($v) => $isPayment($v) && bccomp($v['debit'], '0', 2) > 0)
                        ->reduce(fn($t, $v) => bcadd($t, $v['debit'], 2), '0');
                } else {
                    // DEBTOR: Sale = sales type ke vouchers ka debit total
                    $sale = $vouchers->filter(fn($v) => $v['mapped_type_low'] === 'sales' && bccomp($v['debit'], '0', 2) > 0)
                        ->reduce(fn($t, $v) => bcadd($t, $v['debit'], 2), '0');

                    // Other Debits = sales ke alawa baaki debit entries
                    $otherDebits = $vouchers->filter(fn($v) => $v['mapped_type_low'] !== 'sales' && bccomp($v['debit'], '0', 2) > 0)
                        ->reduce(fn($t, $v) => bcadd($t, $v['debit'], 2), '0');

                    // Receipts = receipt vouchers ka credit total (debtor se paisa aaya)
                    $receipts = $vouchers->filter(fn($v) => $isReceipt($v) && bccomp($v['credit'], '0', 2) > 0)
                        ->reduce(fn($t, $v) => bcadd($t, $v['credit'], 2), '0');
                }

                // Ledger ka credit period (days) integer me; khali ho to 0
                $creditPeriod = (int) ($ledger->credit_period ?? 0);

                // Open (unpaid/partly paid) invoices nikale: receipts ko FIFO se invoices me adjust karke
                $openInvoices = $this->buildOpenInvoices($vouchers, $under, $creditPeriod, $today);

                // Tally ka closing balance string me (null ho to '0')
                $balance = (string) ($ledger->closing_balance ?? '0');

                // DUE = sirf wo open invoices jinki days > 0 (due date nikal chuki), unka pending total
                $due = $openInvoices->filter(fn($i) => $i['days'] > 0)
                    ->reduce(fn($t, $i) => bcadd($t, $i['pending'], 2), '0');

                // Saare open invoices ka pending total (due + not due dono)
                $pendingBal = $openInvoices->reduce(fn($t, $i) => bcadd($t, $i['pending'], 2), '0');

                // Balance - pending = wo amount jo invoices se match nahi hui (opening balance / unmatched)
                $extraNotDue = bcsub(ltrim($balance, '-+'), $pendingBal, 2);
                // Negative aaye to 0 kar do
                if (bccomp($extraNotDue, '0', 2) < 0) {
                    $extraNotDue = '0';
                }

                // NOT DUE = (jo open invoices abhi due nahi hue) + (unmatched/opening amount)
                $notDue = bcadd(
                    $openInvoices->filter(fn($i) => $i['days'] <= 0)
                        ->reduce(fn($t, $i) => bcadd($t, $i['pending'], 2), '0'),
                    $extraNotDue,
                    2
                );

                // Pichhle 6 mahino + aaj ke liye target rows banaye (overdue %, target collection, target balance)
                $targetRows = $this->computeTargetRows($vouchers, $under, $creditPeriod, $ownerTargets, $nowForTarget, $extraNotDue);

                // Rows ko ulta karke (latest se) pehli wo row li jisme targetBal null nahi hai
                $firstRow         = collect($targetRows)->reverse()->first(fn($r) => $r['targetBal'] !== null);
                $targetBalance    = $firstRow['targetBal'] ?? '0';          // Target Balance
                $targetCollection = $firstRow['targetCollection'] ?? '0';   // Target Collection (kitna wasool karna hai)

                // Ab is ledger ka final array return (yahi table ki ek row banegi)
                return [
                    'ledger_id'     => $ledger->id,
                    // Is ledger ko assign hue collectors ki list
                    'collector_ids' => $ledgerCollectorMap->get($ledger->id, []),
                    'unique_id' => $ledger->unique_ledger_id,
                    'name' => $ledger->ledger_name,
                    'under' => $under,
                    'mobile' => $ledger->ledger_mobile_number,

                    'balance' => $balance,
                    'due' => $due,
                    'not_due' => $notDue,
                    'target' => $targetBalance,
                    'target_collection' => $targetCollection,
                    'sale' => $sale,

                    'purchase' => $purchase,
                    'other_debits' => $otherDebits,
                    'other_credits' => $otherCredits,
                    'receipts' => $receipts,
                    'payments' => $payments,

                    // Interest wali values ledger table me pehle se saved hain, bas string me convert ki
                    'interest_cost' => (string) ($ledger->interest_cost ?? '0'),
                    'interest_received' => (string) ($ledger->interest_received ?? '0'),
                    'interest_paid' => (string) ($ledger->interest_paid ?? '0'),
                    'interest_due' => (string) ($ledger->interest_due ?? '0'),
                    'interest_waived' => (string) ($ledger->interest_waived ?? '0'),

                    'bad_debts' => (string) ($ledger->bad_debts ?? '0'),
                    // Balance positive hai to Total Debtors me, negative hai to Total Creditors me (minus hata ke)
                    'total_debtors' => bccomp($balance, '0', 2) > 0 ? $balance : '0',
                    'total_creditors' => bccomp($balance, '0', 2) < 0 ? ltrim($balance, '-') : '0',
                    'march_closing_pending' => (string) ($ledger->march_closing_pending ?? '0'),

                    // Ledger ke mark ko Bootstrap badge colour me badla: green->success, red->danger, baaki->secondary
                    'status' => match ($ledger->mark) {
                        'green' => 'success',
                        'red' => 'danger',
                        default => 'secondary',
                    },

                    // overlimit DB me string/0/1 ho sakta hai, isko proper true/false boolean banaya
                    'overlimit' => filter_var($ledger->overlimit, FILTER_VALIDATE_BOOLEAN),
                    'rank' => $ledger->rank ?? null,
                ];
            });

            // Blade view ko company naam, ledgers ki list aur collectors dropdown data bheja
            return view('owner.tally.tally.ledgers', compact('company', 'ledgers', 'collectors'));

        } catch (\Exception $e) {
            // Koi bhi error aaye to log me detail likho
            Log::error('Tally companyLedgers failed', [
                'company' => $company,
                'error' => $e->getMessage(),
            ]);

            // User ko pichle page par bhej do error message ke saath
            return back()->with('error', 'Unable to fetch ledgers from Tally. Please try again.');
        }
    }


    public function updateCreditPeriod(Request $request)
    {
        $request->validate([
            'company' => 'required|string',
            'ledger' => 'required|string',
            'under' => 'nullable|string',
            'voucher_id' => 'nullable',
            'voucher_number' => 'nullable|string',
            'credit_period' => 'required|integer|min:0',
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

        $voucher = null;

        if ($request->filled('voucher_id') && is_numeric($request->voucher_id)) {
            $voucher = (clone $query)->where('id', $request->voucher_id)->first();
        } elseif ($request->filled('voucher_number')) {
            $voucher = (clone $query)->where('voucher_number', $request->voucher_number)->first();
        }

        if (!$voucher) {
            return response()->json(['message' => 'Voucher not found for this ledger.'], 404);
        }

        $newDueDate = Carbon::parse($voucher->date)->startOfDay()
            ->addDays((int) $request->credit_period);

        $voucher->credit_period = $request->credit_period . ' Days';
        $voucher->credit_period_source = 'voucher';
        $voucher->due_date = $newDueDate->toDateString();
        $voucher->save();

        $daysOverdue = max(0, (int) $newDueDate->diffInDays(today(), false));

        return response()->json([
            'success' => true,
            'due_date' => $newDueDate->format('Y-m-d'),
            'days' => $daysOverdue,
        ]);
    }

    public function ledgerDueVouchers(Request $request)
    {
        try {
            // URL query se company aur ledger ka naam liya, (string) cast se null hone par bhi '' milega
            $company = (string) $request->query('company');
            $ledger = (string) $request->query('ledger');

            // Logged-in owner
            $owner = Auth::guard('owner')->user();

            // URL-encoded company naam (abhi aage use nahi hua, unused variable hai)
            $com = urlencode($company);

            // Owner ki company dhundhi; nahi mili to exception (firstOrFail)
            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->firstOrFail();

            // Us company ka wo ledger dhundha; nahi mila to exception
            $ledgerModel = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->where('ledger_name', $ledger)
                ->firstOrFail();

            // Debtor ya Creditor
            $under = $ledgerModel->parent;

            // Ledger ka credit period (days), khali ho to 0
            $creditPeriod = (int) ($ledgerModel->credit_period ?? 0);

            // Aaj ki date, din ki shuruaat par
            $today = Carbon::now()->startOfDay();

            // Voucher type -> mapped type (Sales/Purchase) ka dictionary
            $voucherMappings = VoucherMapping::where('company', $tallyCompany->company_name)
                ->pluck('mapped_to', 'voucher_type')
                ->toArray();

            // Is ledger ke saare vouchers laaye aur har ek ko classify kiya (debit/credit/mapped type)
            // saath me id, due_date, voucher_number aur credit period (aur uska source) bhi jodi
            $rows = TallyVoucher::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->where('ledger_id', $ledgerModel->id)
                ->get()
                ->map(fn($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                    'id' => $v->id,
                    'due_date' => $v->due_date ?? null,
                    'voucher_number' => $v->voucher_number,
                    'credit_period' => $v->credit_period,
                    'credit_period_source' => $v->credit_period_source,
                ]);

            // Lookup maps: voucher_number -> uska credit period / uska source ('voucher' ya 'default' etc.)
            $creditPeriodByVoucherNumber = $rows->pluck('credit_period', 'voucher_number');
            $creditPeriodSourceByVoucherNumber = $rows->pluck('credit_period_source', 'voucher_number');

            // Request Not Due ki hai ya Due ki
            $isNotDue = $request->query('type') === 'not_due';

            // Open invoices (FIFO se receipts adjust karke), aaj ke hisaab se
            $openInvoices = $this->buildOpenInvoices($rows, $under, $creditPeriod, $today);

            // Kaunsi invoices dikhani hain:
            $selected = $isNotDue
                // Not Due: days <= 0, 'days' ascending (jiski due date sabse door hai wo pehle)
                ? $openInvoices->filter(fn($i) => $i['days'] <= 0)->sortBy('days')
                // Due: days > 0, 'days' descending (sabse zyada overdue pehle)
                : $openInvoices->filter(fn($i) => $i['days'] > 0)->sortByDesc('days');

            // Selected invoices ko response ke format me badla
            $dueRows = $selected
                ->map(function ($i) use ($creditPeriodByVoucherNumber, $creditPeriodSourceByVoucherNumber, $creditPeriod) {

                    // Is invoice ka apna credit period aur uska source nikala
                    $voucherCP = $creditPeriodByVoucherNumber->get($i['voucher_number']);
                    $voucherSource = $creditPeriodSourceByVoucherNumber->get($i['voucher_number']);

                    // Agar voucher par alag se credit period set kiya gaya hai (source = 'voucher') to wahi,
                    // warna ledger ka credit period
                    $resolvedCP = ($voucherSource === 'voucher') ? $voucherCP : $creditPeriod;

                    return [
                        'id' => $i['id'] ?? null,
                        'date' => $i['date'],
                        'due_date' => $i['due_date'],
                        'voucher_number' => $i['voucher_number'],
                        'days' => $i['days'],
                        'amount' => $i['pending'],        // baaki bachi amount
                        'original' => $i['original'],     // invoice ki poori amount
                        'credit_period' => (int) $resolvedCP,   // "30 Days" jaisi string ko int 30 banata hai
                    ];
                })->values();

            // Total = dikhayi gayi rows ki pending amount ka sum
            $total = $dueRows->reduce(fn($t, $r) => bcadd($t, $r['amount'], 2), '0');

            // ====== Yahan se sirf NOT DUE ke liye extra logic ======
            if ($isNotDue) {

                // Ledger ka closing balance, sign hata ke
                $balanceAbs = ltrim((string) ($ledgerModel->closing_balance ?? '0'), '-+');

                // Overdue invoices ka total
                $dueSum = $openInvoices->filter(fn($i) => $i['days'] > 0)
                    ->reduce(fn($t, $i) => bcadd($t, $i['pending'], 2), '0');

                // Not Due total = balance - due (yahi companyLedgers wale not_due se match hona chahiye)
                $notDueTotal = bcsub($balanceAbs, $dueSum, 2);
                if (bccomp($notDueTotal, '0', 2) < 0) {
                    $notDueTotal = '0';
                }

                // Abhi jo rows dikhayi gayi unke baad kitna amount "explain" hona baaki hai
                $remaining = bcsub($notDueTotal, $total, 2);

                // Agar kuch amount rows se explain nahi hua (opening / unmatched balance)
                if (bccomp($remaining, '0', 2) > 0) {

                    $isCreditor = $under === 'Sundry Creditors';

                    // Jo voucher numbers pehle se open invoices me aa chuke hain unhe dobara nahi lena
                    $usedNumbers = $openInvoices->pluck('voucher_number')->all();

                    // Candidate vouchers: jo abhi list me nahi hain aur jinki apni side ki amount > 0 hai
                    // (Creditor me credit side, Debtor me debit side), sabse naye pehle
                    $candidates = $rows
                        ->filter(fn($r) => !in_array($r['voucher_number'], $usedNumbers, true)
                            && bccomp($isCreditor ? $r['credit'] : $r['debit'], '0', 2) > 0)
                        ->sortByDesc(fn($r) => Carbon::parse($r['date'])->timestamp)
                        ->values();

                    // Naye voucher se shuru karke remaining amount ko voucher-wise bharte jao
                    foreach ($candidates as $r) {
                        // Remaining 0 ho gaya to ruk jao
                        if (bccomp($remaining, '0', 2) <= 0) {
                            break;
                        }

                        // Voucher ki amount
                        $amt = $isCreditor ? $r['credit'] : $r['debit'];

                        // Itni lo jitni remaining hai (ya voucher ki poori, jo chhota ho)
                        $take = bccomp($remaining, $amt, 2) < 0 ? $remaining : $amt;

                        $voucherDate = Carbon::parse($r['date'])->startOfDay();
                        $dueDate = $this->resolveDueDate($r['due_date'] ?? null, $voucherDate, $creditPeriod);

                        // List me nayi row (days aaj ke hisaab se)
                        $dueRows->push([
                            'id' => $r['id'] ?? null,
                            'date' => $voucherDate->toDateString(),
                            'due_date' => $dueDate->toDateString(),
                            'voucher_number' => $r['voucher_number'],
                            'days' => (int) $dueDate->diffInDays($today, false),
                            'amount' => $take,
                            'original' => $amt,
                            'credit_period' => $creditPeriod,
                        ]);

                        // Remaining kam kiya
                        $remaining = bcsub($remaining, $take, 2);
                    }

                    // Candidates khatam ho gaye par abhi bhi amount bachi hai
                    // to ek alag row "Opening / Other adjustment" ke naam se
                    if (bccomp($remaining, '0', 2) > 0) {
                        $dueRows->push([
                            'id' => null,
                            'date' => null,
                            'due_date' => null,
                            'voucher_number' => 'Opening / Other adjustment',
                            'days' => 0,
                            'amount' => $remaining,
                            'original' => $remaining,
                            'credit_period' => 0,
                        ]);
                    }

                    // Date ke hisaab se naye se purane sort (null date wali row last me)
                    $dueRows = $dueRows->sortByDesc('date')->values();
                }

                // Final total balance - due ke barabar rakha
                $total = $notDueTotal;
            }

            // JSON response: rows, total aur ledger ka credit period
            return response()->json([
                'vouchers' => $dueRows,
                'total' => $total,
                'ledger_credit_period' => $creditPeriod,
            ]);

        } catch (\Throwable $e) {
            // Koi bhi error to log me detail
            Log::error('Tally ledgerDueVouchers failed', [
                'company' => $request->query('company'),
                'ledger' => $request->query('ledger'),
                'error' => $e->getMessage(),
            ]);

            // Frontend ko 500 aur simple message
            return response()->json(['message' => 'Unable to fetch due vouchers.'], 500);
        }
    }




    // $rows = classified vouchers, $under = Debtor/Creditor, $creditPeriod = ledger ke days
    // $targets = [bucket_month => receivable_target %], $now = reference time, $extraNotDue = unmatched/opening amount
    private function computeTargetRows($rows, string $under, int $creditPeriod, $targets, ?Carbon $now = null, string $extraNotDue = '0'): array
    {
        // $now nahi diya to abhi ka time
        $now = $now ?? now();

        // Aaj ki date, din ki shuruaat par
        $today = $now->copy()->startOfDay();

        // Closure: open invoices ki collection ka 'pending' total (bcmath string me)
        $sumPending = fn($c) => $c->reduce(fn($t, $p) => bcadd($t, $p['pending'], 2), '0');

        // BALANCE TODAY = aaj ke open invoices ka pending + unmatched amount
        $openToday = $this->buildOpenInvoices($rows, $under, $creditPeriod, $today);
        $balance = bcadd($sumPending($openToday), $extraNotDue, 2);

        // Balance 0 se zyada hai ya nahi (neeche % nikalte waqt zero se divide na ho)
        $hasBalance = bccomp($balance, '0', 2) > 0;

        // Output rows yahan jama hongi
        $out = [];

        // Ab tak ke months me w1 ka sabse bada value (running maximum), shuru me koi nahi
        $runningMaxW1 = null;

        // 6 se 0 tak loop: i = kitne mahine pehle (6 = sabse purana, 0 = aaj)
        for ($i = 6; $i >= 0; $i--) {

            // i=0 to aaj; warna us mahine ka last din
            // (pehle startOfMonth kiya phir subMonths, taaki 31 tarikh wale month overflow na karein)
            $monthEnd = $i === 0
                ? $now->copy()
                : $now->copy()->startOfMonth()->subMonths($i)->endOfMonth();

            // Cutoff = us din ki shuruaat
            $cutoff = $monthEnd->copy()->startOfDay();

            // SNAPSHOT: us month-end tak ke invoices + receipts lekar us din ki pending sthiti
            // ($asOf = $cutoff, isliye 'days' bhi us din ke hisaab se nikalega)
            $snap = $this->buildOpenInvoices($rows, $under, $creditPeriod, $today, $cutoff);

            // OVERDUE = us din jin invoices ki due date nikal chuki thi (days > 0), unka pending total
            $overdue = $sumPending($snap->filter(fn($p) => $p['days'] > 0));

            // NOT DUE = jo us din tak due nahi hue (days <= 0) + unmatched amount
            $notDue = bcadd($sumPending($snap->filter(fn($p) => $p['days'] <= 0)), $extraNotDue, 2);

            // Is mahine ki row ka base structure
            $row = [
                'month' => $monthEnd->format('M-y'),   // e.g. "Sep-26"
                'overdue' => $overdue,
                'notDue' => $notDue,
                'balance' => $balance,                 // NOTE: har row me aaj ka balance hi hai
                // Overdue % = overdue / balance * 100 (6 decimal tak divide, phir 2 decimal me round)
                'pctOverdue' => $hasBalance ? bcmul(bcdiv($overdue, $balance, 6), '100', 2) : '0',
                'pctNotDue' => $hasBalance ? bcmul(bcdiv($notDue, $balance, 6), '100', 2) : '0',
                // Neeche ke fields shuru me null, target milne par bharenge
                'target' => null,
                'w1' => null,
                'w2' => null,
                'targetCollection' => null,
                'targetBal' => null,
            ];

            // Target sirf pichhle mahino ke liye (i > 0); aaj wali row (i=0) me target nahi hota
            if ($i > 0) {

                // Owner ne is bucket (i mahine pehle) ka target % set kiya hai ya nahi
                $t = $targets[$i] ?? null;

                if ($t !== null) {
                    $t = (string) $t;

                    // W1 = actual overdue - (balance ka target %)
                    // yaani target % se kitna zyada overdue pada hai wo amount
                    $w1 = bcsub($overdue, bcdiv(bcmul($balance, $t, 6), '100', 2), 2);

                    // Negative ho (target se andar hai) to 0
                    if (bccomp($w1, '0', 2) < 0) {
                        $w1 = '0.00';
                    }

                    // Running max: ab tak ka sabse bada W1 yaad rakhte hain
                    if ($runningMaxW1 === null || bccomp($w1, $runningMaxW1, 2) > 0) {
                        $runningMaxW1 = $w1;
                    }

                    $row['target'] = $t;                       // us mahine ka target %
                    $row['w1'] = $w1;                          // is mahine ka extra overdue
                    $row['w2'] = $runningMaxW1;                // ab tak ka max W1
                    $row['targetCollection'] = $runningMaxW1;  // wasool karne wali amount (w2 jaisi hi)

                    // TARGET BALANCE = aaj ka balance - target collection
                    $targetBal = bcsub($balance, $runningMaxW1, 2);
                    if (bccomp($targetBal, '0', 2) < 0) {
                        $targetBal = '0.00';
                    }
                    $row['targetBal'] = $targetBal;
                }
            }

            // Row output me daali
            $out[] = $row;
        }

        // 7 rows: purane se naye (6 mahine pehle ... aaj)
        return $out;
    }


    public function ledgerTargetData(Request $request)
    {
        try {
            // Logged-in owner ki sirf id (owner guard se)
            $ownerId = Auth::guard('owner')->id();

            // Request ke 3 fields zaroori hain, nahi to 422 validation error automatic chala jayega
            $request->validate([
                'company' => 'required|string',
                'ledger' => 'required|string',
                'under' => 'required|string',   // NOTE: validate to hota hai, par aage use nahi hota (neeche ledger ka parent hi use hota hai)
            ]);

            // Owner ki company dhundhi; nahi mili to exception (firstOrFail)
            $tallyCompany = TallyCompany::where('owner_id', $ownerId)
                ->where('company_name', $request->company)
                ->firstOrFail();

            // Is company ke overdue target % settings: [bucket_month => receivable_target]
            // e.g. [1 => 10, 2 => 8, ...] (1 = 1 mahina pehle, 2 = 2 mahine pehle...)
            $targets = OverdueTargetSetting::where('owner_id', $ownerId)
                ->where('company_id', $tallyCompany->id)
                ->pluck('receivable_target', 'bucket_month');

            // Us company ka wo ledger dhundha; nahi mila to exception
            $ledgerModel = TallyLedger::where('owner_id', $ownerId)
                ->where('tally_company_id', $tallyCompany->id)
                ->where('ledger_name', $request->ledger)
                ->firstOrFail();

            // Debtor ya Creditor (DB se, request wale 'under' se nahi)
            $under = $ledgerModel->parent;

            // Ledger ka credit period (days), khali ho to 0
            $creditPeriod = (int) ($ledgerModel->credit_period ?? 0);

            // Is ledger ke saare classified voucher rows (helper function se)
            $rows = $this->getLedgerVoucherRows($ownerId, $tallyCompany, $ledgerModel);

            // Aaj ke open invoices (FIFO se receipts adjust karke)
            $pending = $this->buildOpenInvoices($rows, $under, $creditPeriod, Carbon::now()->startOfDay());

            // Closing balance, sign hata ke
            $balanceAbs = ltrim((string) ($ledgerModel->closing_balance ?? '0'), '-+');

            // Saare open invoices ka pending total
            $pendingSum = $pending->reduce(fn($t, $i) => bcadd($t, $i['pending'], 2), '0');

            // Balance - pending = wo amount jo kisi invoice se match nahi hui (opening / unmatched)
            $extraNotDue = bcsub($balanceAbs, $pendingSum, 2);

            // Negative aaye to 0
            if (bccomp($extraNotDue, '0', 2) < 0) {
                $extraNotDue = '0';
            }

            // 7 rows ka target table JSON me bhej diya
            // $now = null pass kiya, to computeTargetRows andar se now() le leta hai
            return response()->json([
                'rows' => $this->computeTargetRows($rows, $under, $creditPeriod, $targets, null, $extraNotDue),
            ]);

        } catch (ValidationException $e) {
            // Validation error ko dobara throw kiya, taaki Laravel use 422 + errors JSON ke roop me bheje
            // (warna neeche wala generic catch use 500 bana deta)
            throw $e;

        } catch (\Throwable $e) {
            // Baaki koi bhi error: log me detail
            Log::error('Tally ledgerTargetData failed', [
                'company' => $request->company,
                'ledger' => $request->ledger,
                'error' => $e->getMessage(),
            ]);

            // Frontend ko 500 aur simple message
            return response()->json(['message' => 'Unable to fetch target data.'], 500);
        }
    }


    public function ledgerInterestData(Request $request)
    {
        try {
            // Logged-in owner ki id
            $ownerId = Auth::guard('owner')->id();

            // Company aur ledger zaroori; 'under' optional hai (par aage use nahi hota, ledger ka parent hi use hota hai)
            $request->validate([
                'company' => 'required|string',
                'ledger'  => 'required|string',
                'under'   => 'nullable|string',
            ]);

            // Owner ki company dhundhi, nahi mili to exception
            $tallyCompany = TallyCompany::where('owner_id', $ownerId)
                ->where('company_name', $request->company)
                ->firstOrFail();

            // Us company ka ledger dhundha, nahi mila to exception
            $ledgerModel = TallyLedger::where('owner_id', $ownerId)
                ->where('tally_company_id', $tallyCompany->id)
                ->where('ledger_name', $request->ledger)
                ->firstOrFail();

            $under        = $ledgerModel->parent;                       // Debtor ya Creditor
            $isCreditor   = $under === 'Sundry Creditors';              // true = Creditor
            $creditPeriod = (int) ($ledgerModel->credit_period ?? 0);   // ledger ke credit days
            $today        = Carbon::now()->startOfDay();                // aaj ki date

            // Party ka interest rate (% per annum), string me (bcmath ke liye)
            $partyRate = (string) ($ledgerModel->interest_rate ?? '0');

            // Base rate: ledger me bhara ho to wahi, warna config('app.base_interest_rate'), wo bhi na ho to 12
            $baseRate  = !blank($ledgerModel->base_interest_rate)
                ? (string) $ledgerModel->base_interest_rate
                : (string) config('app.base_interest_rate', 12);

            // Simple interest formula: amount * rate% * days / 365  =>  amt * rate * days / 36500
            // days <= 0 ho to interest 0
            $interestYearly = fn($amt, $rate, $days) =>
                $days > 0 ? bcdiv(bcmul(bcmul($amt, $rate, 6), (string) $days, 6), '36500', 2) : '0.00';

            // Interest on Interest: amt * rate * days / 3000  (yaani amt * (rate/100) * (days/30))
            // Yahan $amt me aage "interest" hi pass hota hai, isliye interest par interest nikalta hai
            $interestOnInt = fn($amt, $rate, $days) =>
                $days > 0 ? bcdiv(bcmul(bcmul($amt, $rate, 6), (string) $days, 6), '3000', 2) : '0.00';

            // Is ledger ke saare classified voucher rows (settlement fields ke saath)
            $rows = $this->getLedgerVoucherRows($ownerId, $tallyCompany, $ledgerModel);

            // ===== PENDING invoices (abhi overdue) =====
            $pending = $this->buildOpenInvoices($rows, $under, $creditPeriod, $today)
                ->filter(fn($i) => $i['days'] > 0)    // sirf overdue wale
                ->sortByDesc('days')                  // sabse zyada overdue pehle
                ->values()
                ->map(function ($i) use ($interestYearly, $partyRate, $baseRate, $today) {
                    // Due date se aaj tak overdue days (overdueDays helper se, negative ho to 0)
                    $days = $this->overdueDays($i['due_date'], $today);

                    return [
                        'id'             => $i['id'] ?? null,
                        'date'           => $i['date'],
                        'due_date'       => $i['due_date'],
                        'inv_no'         => $i['voucher_number'],
                        'days'           => $days,
                        'amount'         => $i['pending'],   // baaki amount, isi par interest lagta hai
                        // Party rate par interest
                        'interest_due'   => $interestYearly($i['pending'], $partyRate, $days),
                        // Base rate par interest (_b = base)
                        'interest_due_b' => $interestYearly($i['pending'], $baseRate, $days),
                    ];
                });

                        // ===== RECEIVED invoices (jo late paid hue): FIFO allocation =====

            // Receipts (Debtor) / Payments (Creditor) ki list, purani date pehle
            // Har receipt ke saath 'remaining' (abhi kitna use hona baaki hai)
            $receipts = $rows
                ->filter(fn($r) => $isCreditor
                    ? (str_contains($r['voucher_type_low'], 'payment') && bccomp($r['debit'], '0', 2) > 0)
                    : (str_contains($r['voucher_type_low'], 'receipt') && bccomp($r['credit'], '0', 2) > 0))
                ->sortBy(fn($r) => Carbon::parse($r['date'])->timestamp)
                ->map(fn($r) => [
                    'date'      => Carbon::parse($r['date'])->startOfDay(),
                    'remaining' => $isCreditor ? $r['debit'] : $r['credit'],
                ])
                ->values()
                ->all();   // plain array (taaki neeche $receipts[$ptr]['remaining'] edit ho sake)

            // Invoices (Sales / Purchase), purani date pehle
            $invoices = $rows
                ->filter(fn($r) => $isCreditor
                    ? ($r['mapped_type_low'] === 'purchase' && bccomp($r['credit'], '0', 2) > 0)
                    : ($r['mapped_type_low'] === 'sales' && bccomp($r['debit'], '0', 2) > 0))
                ->sortBy(fn($r) => Carbon::parse($r['date'])->timestamp)
                ->values();

            $received = collect();            // late paid rows yahan jama hongi
            $ptr = 0;                         // receipts list me abhi kaunsi receipt chal rahi hai (pointer)
            $settlementLeftById = [];         // (abhi kahin fill nahi hota, hamesha khali rehta hai)

            // Har invoice par loop (purani se nayi)
            foreach ($invoices as $inv) {
                $need        = $isCreditor ? $inv['credit'] : $inv['debit'];   // invoice ko clear karne ke liye kitna chahiye
                $voucherDate = Carbon::parse($inv['date'])->startOfDay();
                $dueDate     = $this->resolveDueDate($inv['due_date'] ?? null, $voucherDate, $creditPeriod);

                // Is invoice par settlement hui hai ya nahi (voucher me saved)
                $invStatus    = strtolower((string) ($inv['settlement_status'] ?? ''));
                $isSettledInv = ($invStatus === 'settled');

                // Settlement ki amount (numeric ho to wahi, warna 0)
                $leftP = is_numeric($inv['settlement_amount'] ?? null) ? (string) $inv['settlement_amount'] : '0';
                $leftB = $leftP;   // Party aur Base dono ke liye alag alag "bacha hua settlement" track hota hai

                // Jab tak invoice clear nahi hui aur receipts bachi hain
                while (bccomp($need, '0', 2) > 0 && $ptr < count($receipts)) {

                    // Current receipt khatam ho chuki to agli receipt par jao
                    if (bccomp($receipts[$ptr]['remaining'], '0', 2) <= 0) {
                        $ptr++;
                        continue;
                    }

                    // Is round me kitna adjust hoga: invoice ki need aur receipt ka remaining, dono me jo chhota
                    $take = bccomp($need, $receipts[$ptr]['remaining'], 2) < 0
                        ? $need : $receipts[$ptr]['remaining'];

                    // Receipt aur invoice dono se wo amount ghata di
                    $receipts[$ptr]['remaining'] = bcsub($receipts[$ptr]['remaining'], $take, 2);
                    $need = bcsub($need, $take, 2);

                    // Is receipt ki date, aur due date ke baad kitne din late aayi
                    $recdDate  = $receipts[$ptr]['date'];
                    $delayDays = (int) $dueDate->diffInDays($recdDate, false);   // positive = due date ke baad aayi

                    // Sirf late payment par interest banta hai
                    if ($delayDays > 0) {
                        // Receipt aaye hue aaj tak kitne din ho gaye
                        $daysSince = (int) $recdDate->diffInDays($today, false);

                        // Party rate: simple interest, uske upar interest-on-interest, dono ka sum = Interest Due
                        $intPending  = $interestYearly($take, $partyRate, $delayDays);
                        $intOnInt    = $interestOnInt($intPending, $partyRate, $delayDays);
                        $interestDue = bcadd($intPending, $intOnInt, 2);

                        // Wahi calculation Base rate par
                        $intPendingB  = $interestYearly($take, $baseRate, $delayDays);
                        $intOnIntB    = $interestOnInt($intPendingB, $baseRate, $delayDays);
                        $interestDueB = bcadd($intPendingB, $intOnIntB, 2);


                                            if ($isSettledInv) {
                            // PARTY: settlement amount utni lo jitni bachi hai, par interest due se zyada nahi
                            $settlementAmt  = bccomp($leftP, $interestDue, 2) < 0 ? $leftP : $interestDue;
                            $leftP          = bcsub($leftP, $settlementAmt, 2);          // bacha hua settlement
                            $interestWaived = bcsub($interestDue, $settlementAmt, 2);    // maaf hua interest = due - settlement
                            $settledRequest = $interestDue;                              // settlement ke liye request hui amount

                            // BASE: same logic base rate ke saath
                            $settlementAmtB  = bccomp($leftB, $interestDueB, 2) < 0 ? $leftB : $interestDueB;
                            $leftB           = bcsub($leftB, $settlementAmtB, 2);
                            $interestWaivedB = bcsub($interestDueB, $settlementAmtB, 2);
                            $settledRequestB = $interestDueB;
                        } else {
                            // Settlement nahi hui to sab 0
                            $settlementAmt = $interestWaived = $settledRequest = '0.00';
                            $settlementAmtB = $interestWaivedB = $settledRequestB = '0.00';
                        }

                        // Is chunk ki row jama ki
                        $received->push([
                            'id'                => $inv['id'],          // invoice ka id
                            'inv_no'            => $inv['voucher_number'],
                            'recd_date'         => $recdDate->toDateString(),
                            'amount'            => $take,
                            'days'              => $delayDays,
                            'days_since'        => $daysSince,

                            // Party rate ke numbers
                            'int_pending'       => $intPending,
                            'int_on_int'        => $intOnInt,
                            'interest_due'      => $interestDue,
                            'settlement_amount' => $settlementAmt,
                            'interest_waived'   => $interestWaived,
                            'settled_request'   => $settledRequest,

                            // Base rate ke numbers (_b)
                            'int_pending_b'       => $intPendingB,
                            'int_on_int_b'        => $intOnIntB,
                            'interest_due_b'      => $interestDueB,
                            'settlement_amount_b' => $settlementAmtB,
                            'interest_waived_b'   => $interestWaivedB,
                            'settled_request_b'   => $settledRequestB,

                            'settlement_status' => $invStatus ?: null,
                            'is_settled'        => $isSettledInv,
                        ]);
                    }
                }
            }

            // Ek invoice kai receipts se bhar sakti hai (kai chunks). Unhe invoice number se group karke ek row banayi
            $received = $received
                ->groupBy('inv_no')
                ->map(function ($group) {
                    $first = $group->first();
                    // Group me kisi column ka sum nikalne wala helper
                    $sum = fn($key) => $group->reduce(fn($t, $r) => bcadd($t, (string) $r[$key], 2), '0');

                    return array_merge($first, [
                        'recd_date'           => $group->last()['recd_date'],   // aakhri receipt ki date
                        'days'                => $group->max('days'),           // sabse zyada delay
                        'amount'              => $sum('amount'),

                        'int_pending'         => $sum('int_pending'),
                        'int_on_int'          => $sum('int_on_int'),
                        'interest_due'        => $sum('interest_due'),
                        'settlement_amount'   => $sum('settlement_amount'),
                        'interest_waived'     => $sum('interest_waived'),
                        'settled_request'     => $sum('settled_request'),

                        'int_pending_b'       => $sum('int_pending_b'),
                        'int_on_int_b'        => $sum('int_on_int_b'),
                        'interest_due_b'      => $sum('interest_due_b'),
                        'settlement_amount_b' => $sum('settlement_amount_b'),
                        'interest_waived_b'   => $sum('interest_waived_b'),
                        'settled_request_b'   => $sum('settled_request_b'),

                        // Koi bhi chunk settled hai to poori invoice settled
                        'is_settled'          => $group->contains('is_settled', true),
                        'settlement_status'   => $group->contains('is_settled', true) ? 'settled' : $first['settlement_status'],
                    ]);
                })
                ->values();

            // id se voucher rows ka lookup (pending invoices ki settlement info nikalne ke liye)
            $rowsById = $rows->keyBy('id');

            // PENDING rows me settlement info jodi
            $pending = $pending->map(function ($p) use ($rowsById, $settlementLeftById) {
                $src    = $rowsById->get($p['id']);               // original voucher row
                $status = $src['settlement_status'] ?? null;

                // Settlement amount (settlementLeftById khali rehta hai, to voucher ki settlement_amount hi use hogi)
                $left = $settlementLeftById[$p['id']]
                    ?? (is_numeric($src['settlement_amount'] ?? null) ? (string) $src['settlement_amount'] : '0');

                $isSettled = strtolower((string) $status) === 'settled';

                // Settled ho to settlement = min(left, interest due), warna 0 (party aur base dono ke liye)
                $amt  = $isSettled ? (bccomp($left, $p['interest_due'], 2)   < 0 ? $left : $p['interest_due'])   : '0.00';
                $amtB = $isSettled ? (bccomp($left, $p['interest_due_b'], 2) < 0 ? $left : $p['interest_due_b']) : '0.00';

                // Purani row + naye settlement fields
                return $p + [
                    'settlement_status'   => $status,
                    'settlement_amount'   => $amt,
                    'interest_waived'     => $isSettled ? bcsub($p['interest_due'], $amt, 2) : '0.00',
                    'settlement_amount_b' => $amtB,
                    'interest_waived_b'   => $isSettled ? bcsub($p['interest_due_b'], $amtB, 2) : '0.00',
                ];
            })->values();

            // Helper: row settled hai ya nahi
            $isSettledRow = fn($r) => strtolower((string) ($r['settlement_status'] ?? '')) === 'settled';

            // ===== Totals: Party rate =====
            $receivedTotal = $received->reduce(fn($t, $r) => bcadd($t, $r['interest_due'], 2), '0');   // late paid ka total interest
            $pendingTotal  = $pending->reduce(fn($t, $r) => bcadd($t, $r['interest_due'], 2), '0');    // pending ka total interest

            // Received ke settlement totals: request, mila, aur maaf hua (request - mila)
            $settlementRequestTotal  = $received->reduce(fn($t, $r) => bcadd($t, $r['settled_request'], 2), '0');
            $settlementReceivedTotal = $received->reduce(fn($t, $r) => bcadd($t, $r['settlement_amount'], 2), '0');
            $interestWaivedTotal     = bcsub($settlementRequestTotal, $settlementReceivedTotal, 2);

            // Pending me se sirf settled rows ke totals
            $pendingSettled = $pending->filter($isSettledRow);
            $pendingSettlementRequestTotal  = $pendingSettled->reduce(fn($t, $r) => bcadd($t, $r['interest_due'], 2), '0');
            $pendingSettlementReceivedTotal = $pendingSettled->reduce(fn($t, $r) => bcadd($t, $r['settlement_amount'], 2), '0');
            $pendingInterestWaivedTotal     = bcsub($pendingSettlementRequestTotal, $pendingSettlementReceivedTotal, 2);

            // ===== Totals: Base rate (upar jaisa hi, _b columns se) =====
            $receivedTotalB = $received->reduce(fn($t, $r) => bcadd($t, $r['interest_due_b'], 2), '0');
            $pendingTotalB  = $pending->reduce(fn($t, $r) => bcadd($t, $r['interest_due_b'], 2), '0');

            $settlementRequestTotalB  = $received->reduce(fn($t, $r) => bcadd($t, $r['settled_request_b'], 2), '0');
            $settlementReceivedTotalB = $received->reduce(fn($t, $r) => bcadd($t, $r['settlement_amount_b'], 2), '0');
            $interestWaivedTotalB     = bcsub($settlementRequestTotalB, $settlementReceivedTotalB, 2);

            $pendingSettlementRequestTotalB  = $pendingSettled->reduce(fn($t, $r) => bcadd($t, $r['interest_due_b'], 2), '0');
            $pendingSettlementReceivedTotalB = $pendingSettled->reduce(fn($t, $r) => bcadd($t, $r['settlement_amount_b'], 2), '0');
            $pendingInterestWaivedTotalB     = bcsub($pendingSettlementRequestTotalB, $pendingSettlementReceivedTotalB, 2);

            // JSON response: rates, saare totals (party + base), aur dono lists
            return response()->json([
                'party_rate' => $partyRate,
                'base_rate'  => $baseRate,

                // party
                'total'                             => bcadd($receivedTotal, $pendingTotal, 2),   // received + pending interest
                'received_total'                    => $receivedTotal,
                'pending_total'                     => $pendingTotal,
                'settlement_request_total'          => $settlementRequestTotal,
                'settlement_received_total'         => $settlementReceivedTotal,
                'interest_waived_total'             => $interestWaivedTotal,
                'pending_settlement_request_total'  => $pendingSettlementRequestTotal,
                'pending_settlement_received_total' => $pendingSettlementReceivedTotal,
                'pending_interest_waived_total'     => $pendingInterestWaivedTotal,

                // base
                'total_b'                             => bcadd($receivedTotalB, $pendingTotalB, 2),
                'received_total_b'                    => $receivedTotalB,
                'pending_total_b'                     => $pendingTotalB,
                'settlement_request_total_b'          => $settlementRequestTotalB,
                'settlement_received_total_b'         => $settlementReceivedTotalB,
                'interest_waived_total_b'             => $interestWaivedTotalB,
                'pending_settlement_request_total_b'  => $pendingSettlementRequestTotalB,
                'pending_settlement_received_total_b' => $pendingSettlementReceivedTotalB,
                'pending_interest_waived_total_b'     => $pendingInterestWaivedTotalB,

                'received' => $received->values(),
                'pending'  => $pending,
            ]);

        } catch (ValidationException $e) {
            // Validation error ko wapas throw, taaki Laravel 422 bheje
            throw $e;
        } catch (\Throwable $e) {
            // Baaki errors: log karke 500
            Log::error('Tally ledgerInterestData failed', [
                'company' => $request->company,
                'ledger'  => $request->ledger,
                'error'   => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Unable to fetch interest data.'], 500);
        }
    }
    


    // public function ledgerInterestData(Request $request)
    // {
    //     try {
    //         $ownerId = Auth::guard('owner')->id();

    //         $request->validate([
    //             'company' => 'required|string',
    //             'ledger'  => 'required|string',
    //             'under'   => 'nullable|string',
    //         ]);

    //         $tallyCompany = TallyCompany::where('owner_id', $ownerId)
    //             ->where('company_name', $request->company)
    //             ->firstOrFail();

    //         $ledgerModel = TallyLedger::where('owner_id', $ownerId)
    //             ->where('tally_company_id', $tallyCompany->id)
    //             ->where('ledger_name', $request->ledger)
    //             ->firstOrFail();

    //         $under        = $ledgerModel->parent;
    //         $isCreditor   = $under === 'Sundry Creditors';
    //         $creditPeriod = (int) ($ledgerModel->credit_period ?? 0);
    //         $today        = Carbon::now()->startOfDay();

    //         $partyRate = (string) ($ledgerModel->interest_rate ?? '0');   // % per annum
    //         $baseRate  = (string) config('app.base_interest_rate', 12);   // % per annum

    //         // Int Pending: amount * rate% * days / 365  => /36500
    //         $interestYearly = fn ($amt, $rate, $days) =>
    //             $days > 0 ? bcdiv(bcmul(bcmul($amt, $rate, 6), (string) $days, 6), '36500', 2) : '0.00';

    //         // Int on Int: amount * (rate/100) * (days/30)  => /3000
    //         $interestOnInt = fn ($amt, $rate, $days) =>
    //             $days > 0 ? bcdiv(bcmul(bcmul($amt, $rate, 6), (string) $days, 6), '3000', 2) : '0.00';

    //         $rows = $this->getLedgerVoucherRows($ownerId, $tallyCompany, $ledgerModel);

    //         // ===== PENDING invoices (overdue) =====
    //         $pending = $this->buildOpenInvoices($rows, $under, $creditPeriod, $today)
    //             ->filter(fn ($i) => $i['days'] > 0)
    //             ->sortByDesc('days')
    //             ->values()
    //             ->map(function ($i) use ($interestYearly, $partyRate, $baseRate, $today) {
    //                 $days = $this->overdueDays($i['due_date'], $today);

    //                 return [
    //                     'date'           => $i['date'],
    //                     'due_date'       => $i['due_date'],
    //                     'inv_no'         => $i['voucher_number'],
    //                     'days'           => $days,
    //                     'amount'         => $i['pending'],
    //                     'interest_due'   => $interestYearly($i['pending'], $partyRate, $days),
    //                     'interest_due_b' => $interestYearly($i['pending'], $baseRate, $days),
    //                 ];
    //             });

    //         // ===== RECEIVED invoices (late paid) : FIFO allocation =====
    //         $receipts = $rows
    //             ->filter(fn ($r) => $isCreditor
    //                 ? (str_contains($r['voucher_type_low'], 'payment') && bccomp($r['debit'], '0', 2) > 0)
    //                 : (str_contains($r['voucher_type_low'], 'receipt') && bccomp($r['credit'], '0', 2) > 0))
    //             ->sortBy(fn ($r) => Carbon::parse($r['date'])->timestamp)
    //             ->map(fn ($r) => [
    //                 'date'      => Carbon::parse($r['date'])->startOfDay(),
    //                 'remaining' => $isCreditor ? $r['debit'] : $r['credit'],
    //             ])
    //             ->values()
    //             ->all();

    //         $invoices = $rows
    //             ->filter(fn ($r) => $isCreditor
    //                 ? ($r['mapped_type_low'] === 'purchase' && bccomp($r['credit'], '0', 2) > 0)
    //                 : ($r['mapped_type_low'] === 'sales' && bccomp($r['debit'], '0', 2) > 0))
    //             ->sortBy(fn ($r) => Carbon::parse($r['date'])->timestamp)
    //             ->values();

    //         $received = collect();
    //         $ptr = 0;

    //             foreach ($invoices as $inv) {
    //                 $need        = $isCreditor ? $inv['credit'] : $inv['debit'];
    //                 $voucherDate = Carbon::parse($inv['date'])->startOfDay();
    //                 $dueDate     = $this->resolveDueDate($inv['due_date'] ?? null, $voucherDate, $creditPeriod);

    //                 // Invoice ka settlement (ek hi baar, rows mein allocate hoga)
    //                 $settlementStatus = $inv['settlement_status'] ?? null;
    //                 $settlementLeft   = is_numeric($inv['settlement_amount'] ?? null)
    //                     ? (string) $inv['settlement_amount']
    //                     : '0';
    //                 $hasSettlement = !empty($settlementStatus) || bccomp($settlementLeft, '0', 2) > 0;

    //                 while (bccomp($need, '0', 2) > 0 && $ptr < count($receipts)) {
    //                     if (bccomp($receipts[$ptr]['remaining'], '0', 2) <= 0) {
    //                         $ptr++;
    //                         continue;
    //                     }

    //                     $take = bccomp($need, $receipts[$ptr]['remaining'], 2) < 0
    //                         ? $need : $receipts[$ptr]['remaining'];

    //                     $receipts[$ptr]['remaining'] = bcsub($receipts[$ptr]['remaining'], $take, 2);
    //                     $need = bcsub($need, $take, 2);

    //                     $recdDate  = $receipts[$ptr]['date'];
    //                     $delayDays = (int) $dueDate->diffInDays($recdDate, false);

    //                     if ($delayDays > 0) {
    //                         $daysSince = (int) $recdDate->diffInDays($today, false);

    //                         $intPending = $interestYearly($take, $partyRate, $delayDays);
    //                         $intOnInt   = $interestOnInt($intPending, $partyRate, $delayDays);
    //                         $interestDue = bcadd($intPending, $intOnInt, 2);   // ✅ Interest Due

    //                         $intPendingB = $interestYearly($take, $baseRate, $delayDays);
    //                         $intOnIntB   = $interestOnInt($intPendingB, $baseRate, $delayDays);

    //                         // ✅ Settlement ko Interest Due se jodo
    //                         if ($hasSettlement) {
    //                             // is row ka settlement: bacha hua settlement, par Interest Due se zyada nahi
    //                             $settlementAmt  = bccomp($settlementLeft, $interestDue, 2) < 0 ? $settlementLeft : $interestDue;
    //                             $settlementLeft = bcsub($settlementLeft, $settlementAmt, 2);
    //                             $interestWaived = bcsub($interestDue, $settlementAmt, 2);   // Interest Due - Settlement
    //                         } else {
    //                             $settlementAmt  = '0.00';
    //                             $interestWaived = '0.00';
    //                         }

    //                         $received->push([
    //                             'inv_no'            => $inv['voucher_number'],
    //                             'recd_date'         => $recdDate->toDateString(),
    //                             'amount'            => $take,
    //                             'int_pending'       => $intPending,
    //                             'days'              => $delayDays,
    //                             'days_since'        => $daysSince,
    //                             'int_on_int'        => $intOnInt,
    //                             'interest_due'      => $interestDue,
    //                             'settlement_status' => $settlementStatus,
    //                             'settlement_amount' => $settlementAmt,
    //                             'interest_waived'   => $interestWaived,
    //                             'int_pending_b'     => $intPendingB,
    //                             'int_on_int_b'      => $intOnIntB,
    //                             'interest_due_b'    => bcadd($intPendingB, $intOnIntB, 2),
    //                             'is_settled' => strtolower((string) $settlementStatus) === 'settled',   // ✅ NEW
    //                         ]);
    //                     }
    //                 }
    //             }

    //             $received = $received
    //             ->groupBy('inv_no')
    //             ->map(function ($group) {
    //                 $first = $group->first();
    //                 $sum = fn ($key) => $group->reduce(fn ($t, $r) => bcadd($t, (string) $r[$key], 2), '0');

    //                 return array_merge($first, [
    //                     'recd_date'         => $group->last()['recd_date'],
    //                     'days'              => $group->max('days'),
    //                     'amount'            => $sum('amount'),
    //                     'int_pending'       => $sum('int_pending'),
    //                     'int_on_int'        => $sum('int_on_int'),
    //                     'interest_due'      => $sum('interest_due'),
    //                     'settlement_amount' => $sum('settlement_amount'),
    //                     'interest_waived'   => $sum('interest_waived'),
    //                     'int_pending_b'     => $sum('int_pending_b'),
    //                     'int_on_int_b'      => $sum('int_on_int_b'),
    //                     'interest_due_b'    => $sum('interest_due_b'),
    //                 ]);
    //             })
    //             ->values();
                
    //         $receivedTotal = $received->reduce(fn ($t, $r) => bcadd($t, $r['interest_due'], 2), '0');
    //         $pendingTotal  = $pending->reduce(fn ($t, $r) => bcadd($t, $r['interest_due'], 2), '0');

    //         $settledRows = $received->filter(fn ($r) => $r['is_settled']);

    //         $settlementRequestTotal = $settledRows
    //             ->reduce(fn ($t, $r) => bcadd($t, $r['interest_due'], 2), '0');

    //         $settlementReceivedTotal = $settledRows
    //             ->reduce(fn ($t, $r) => bcadd($t, $r['settlement_amount'], 2), '0');

    //         $interestWaivedTotal = bcsub($settlementRequestTotal, $settlementReceivedTotal, 2);

    //         return response()->json([
    //             'party_rate'                => $partyRate,
    //             'base_rate'                 => $baseRate,
    //             'total'                     => bcadd($receivedTotal, $pendingTotal, 2),
    //             'received_total'            => $receivedTotal,
    //             'pending_total'             => $pendingTotal,
    //             'settlement_request_total'  => $settlementRequestTotal,    // ✅ NEW
    //             'settlement_received_total' => $settlementReceivedTotal,   // ✅ NEW
    //             'interest_waived_total'     => $interestWaivedTotal,       // ✅ NEW
    //             'received'                  => $received->values(),
    //             'pending'                   => $pending,
    //         ]);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         throw $e;
    //     } catch (\Throwable $e) {
    //         Log::error('Tally ledgerInterestData failed', [
    //             'company' => $request->company,
    //             'ledger'  => $request->ledger,
    //             'error'   => $e->getMessage(),
    //         ]);
    //         return response()->json(['message' => 'Unable to fetch interest data.'], 500);
    //     }
    // }

    // naya helper: raw classified rows
    // $ownerId = owner ki id, $tallyCompany = TallyCompany model, $ledgerModel = TallyLedger model
    private function getLedgerVoucherRows(int $ownerId, $tallyCompany, $ledgerModel)
    {
        // Voucher type -> mapped type ka dictionary, e.g. ["Sales GST" => "Sales"]
        // classifyVoucherRow ko ye chahiye, taaki wo Sales/Purchase pehchan sake
        $voucherMappings = VoucherMapping::where('company', $tallyCompany->company_name)
            ->pluck('mapped_to', 'voucher_type')
            ->toArray();

        return TallyVoucher::where('owner_id', $ownerId)                  // sirf is owner ke vouchers
            ->where('tally_company_id', $tallyCompany->id)               // sirf is company ke
            ->where('ledger_id', $ledgerModel->id)                       // sirf is ledger ke
            ->get()                                                      // saare vouchers collection me
            ->map(fn($v) => $this->classifyVoucherRow($v, $ledgerModel->parent, $voucherMappings) + [
                // classifyVoucherRow se aaya array (date, debit, credit, mapped_type...) + neeche ki extra fields

                'id' => $v->id,                                          // voucher ka DB id
                'due_date' => $v->due_date ?? null,                      // voucher me saved due date, na ho to null
                'voucher_number' => $v->voucher_number,                  // invoice/voucher number
                'credit_period' => $v->credit_period,                    // voucher ka apna credit period (agar set hai)
                'credit_period_source' => $v->credit_period_source,      // wo kahan se aaya: 'voucher' / 'tally' / etc.

                // Settlement fields (interest me chhoot wali info)
                'settlement_status'    => $v->settlement_status ?? null,             // 'settled' ya null
                'settlement_amount'    => (string) ($v->settlement_amount ?? '0'),   // settlement amount string me, na ho to '0'
            ]);
    }

    // purana method ab helper use karega
    // $ownerId = owner ki id, $tallyCompany = TallyCompany model, $ledger = ledger ka naam (string)
    private function getPendingInvoices(int $ownerId, $tallyCompany, string $ledger)
    {
        // Naam se ledger dhundha (is owner + is company me)
        // first() use hua hai, isliye na mile to null aayega (exception nahi)
        $ledgerModel = TallyLedger::where('owner_id', $ownerId)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('ledger_name', $ledger)
            ->first();

        // Ledger nahi mila to khali collection return, taaki caller par error na aaye
        if (!$ledgerModel) {
            return collect();
        }

        // Is ledger ke saare classified voucher rows (settlement fields ke saath)
        $rows = $this->getLedgerVoucherRows($ownerId, $tallyCompany, $ledgerModel);

        // Open invoices return:
        return $this->buildOpenInvoices(
            $rows,                                       // voucher rows
            $ledgerModel->parent,                        // Debtor ya Creditor
            (int) ($ledgerModel->credit_period ?? 0),    // ledger ka credit period (days), khali ho to 0
            Carbon::now()->startOfDay()                  // aaj ki date, din ki shuruaat par
        );
    }

    // $v = ek TallyVoucher model, $under = "Sundry Debtors"/"Sundry Creditors", $voucherMappings = [voucher_type => mapped_to]
    private function classifyVoucherRow($v, ?string $under, array $voucherMappings): array
    {
        // static = ye arrays function ke har call par dobara nahi bante, ek baar hi memory me bante hain (thoda fast)

        // Debtor ke liye ye voucher types CREDIT side me jayenge (party ne paisa/credit diya)
        static $creditSideTypesForDebtor = ['receipt', 'receipt note', 'credit note'];

        // Creditor ke liye ye voucher types DEBIT side me jayenge (humne party ko paisa diya / debit note)
        static $debitSideTypesForCreditor = ['payment', 'debit note'];

        // Amount se '-' ya '+' sign hata diya, taaki amount hamesha positive rahe
        // (side debit/credit se decide hogi, sign se nahi)
        $amount = ltrim((string) $v->amount, '-+');

        // Dono side shuru me '0' (string, bcmath ke liye)
        $debit = '0';
        $credit = '0';

        // Voucher type ka naam, extra spaces hata ke
        $voucherType = trim((string) $v->voucher_type);

        // Same naam lowercase me, comparison ke liye (case-insensitive match)
        $voucherTypeLow = strtolower($voucherType);

        // Owner ne mapping screen me is voucher type ko kya map kiya hai (Sales/Purchase/...), na ho to null
        $rawMapped = $voucherMappings[$voucherType] ?? null;

        if ($rawMapped !== null && trim($rawMapped) !== '') {
            // 1) Agar owner ne mapping set ki hai to wahi final (sabse high priority)
            $mappedType = trim($rawMapped);

        } elseif (str_contains($voucherTypeLow, 'sale')) {
            // 2) Mapping nahi hai, par naam me "sale" aata hai (e.g. "Sales GST") to Sales maan lo
            $mappedType = 'Sales';

        } elseif (str_contains($voucherTypeLow, 'purchase')) {
            // 3) Naam me "purchase" aata hai to Purchase maan lo
            $mappedType = 'Purchase';

        } else {
            // 4) Kuch bhi match nahi hua to default bucket
            $mappedType = 'Other than Sales/Purchase';
        }

        if ($under === 'Sundry Creditors') {
            // CREDITOR ledger ka case
            if (in_array($voucherTypeLow, $debitSideTypesForCreditor, true)) {
                // Payment / Debit Note -> amount DEBIT me (liability kam hui)
                $debit = $amount;
            } else {
                // Baaki sab (purchase etc.) -> amount CREDIT me (liability badhi)
                $credit = $amount;
            }
        } else {
            // DEBTOR ledger ka case (Creditors ke alawa sab yahin aayenge)
            if (in_array($voucherTypeLow, $creditSideTypesForDebtor, true)) {
                // Receipt / Receipt Note / Credit Note -> amount CREDIT me (receivable kam hua)
                $credit = $amount;
            } else {
                // Baaki sab (sales etc.) -> amount DEBIT me (receivable badha)
                $debit = $amount;
            }
        }

        // Classified row return, baaki functions isi array ko use karte hain
        return [
            'date' => (string) $v->date,                                   // voucher ki date
            'particulars' => trim((string) $v->party_ledger_name) ?: '',   // party ka naam (khali ho to '')
            'voucher_type' => $voucherType,                                // original type, e.g. "Receipt"
            'voucher_type_low' => $voucherTypeLow,                         // lowercase type, str_contains checks ke liye
            'mapped_type' => $mappedType,                                  // Sales / Purchase / Other...
            'mapped_type_low' => strtolower($mappedType),                  // lowercase mapped type, comparisons ke liye
            'voucher_number' => (string) $v->voucher_number,               // invoice/voucher number
            'debit' => $debit,                                             // debit side amount (string)
            'credit' => $credit,                                           // credit side amount (string)
        ];
    }


    // $rows = classified vouchers, $under = Debtor/Creditor, $creditPeriod = ledger ke days
    private function buildReceiptAllocation($rows, ?string $under, int $creditPeriod): array
    {
        // Creditor hai ya nahi (nahi hai to Debtor maana jayega)
        $isCreditor = $under === 'Sundry Creditors';

        // RECEIPTS list: Creditor me "payment" (debit side), Debtor me "receipt" (credit side)
        $receipts = $rows
            ->filter(fn($r) => $isCreditor
                ? (str_contains($r['voucher_type_low'], 'payment') && bccomp($r['debit'], '0', 2) > 0)
                : (str_contains($r['voucher_type_low'], 'receipt') && bccomp($r['credit'], '0', 2) > 0))
            // Purani receipt pehle (FIFO)
            ->sortBy(fn($r) => Carbon::parse($r['date'])->timestamp)
            ->values();

        // INVOICES list: Creditor me purchase (credit side), Debtor me sales (debit side)
        $invoices = $rows
            ->filter(fn($r) => $isCreditor
                ? ($r['mapped_type_low'] === 'purchase' && bccomp($r['credit'], '0', 2) > 0)
                : ($r['mapped_type_low'] === 'sales' && bccomp($r['debit'], '0', 2) > 0))
            // Purani invoice pehle
            ->sortBy(fn($r) => Carbon::parse($r['date'])->timestamp)
            ->values()
            // Har invoice ko chhote array me badla
            ->map(function ($r) use ($isCreditor, $creditPeriod) {
                $voucherDate = Carbon::parse($r['date'])->startOfDay();
                return [
                    'number'    => $r['voucher_number'],
                    // Due date: voucher me saved ho to wahi, warna voucher date + credit period
                    'due_date'  => $this->resolveDueDate($r['due_date'] ?? null, $voucherDate, $creditPeriod)->toDateString(),
                    'amount'    => $isCreditor ? $r['credit'] : $r['debit'],   // invoice ki poori amount
                    'remaining' => $isCreditor ? $r['credit'] : $r['debit'],   // abhi kitni adjust honi baaki (shuru me poori)
                ];
            })
            // Plain array banaya, taaki neeche $invoices[$ip]['remaining'] edit ho sake
            ->all();

        $out = [];   // output rows yahan jama hongi
        $ip  = 0;    // invoices list me abhi kaunsi invoice chal rahi hai (pointer)

        // Har receipt par loop (purani se nayi)
        foreach ($receipts as $rc) {
            // Is receipt ki amount (Creditor me debit, Debtor me credit); $left kam hota jayega
            $left    = $isCreditor ? $rc['debit'] : $rc['credit'];
            // Poori receipt amount alag se yaad rakhi (har output row me dikhane ke liye)
            $recdAmt = $left;
            // Receipt ki date (Y-m-d)
            $date    = Carbon::parse($rc['date'])->toDateString();

            // Jab tak receipt me paisa bacha hai aur invoices bachi hain
            while (bccomp($left, '0', 2) > 0 && $ip < count($invoices)) {

                // Current invoice poori clear ho chuki to agli invoice par jao
                if (bccomp($invoices[$ip]['remaining'], '0', 2) <= 0) {
                    $ip++;
                    continue;
                }

                // Is round me kitna adjust hoga: receipt ka bacha hua aur invoice ka remaining, dono me jo chhota
                $take = bccomp($left, $invoices[$ip]['remaining'], 2) < 0
                    ? $left : $invoices[$ip]['remaining'];

                // Invoice se aur receipt se wo amount ghata di
                $invoices[$ip]['remaining'] = bcsub($invoices[$ip]['remaining'], $take, 2);
                $left = bcsub($left, $take, 2);

                // Output row: "ye receipt, is invoice me, itni lagi"
                $out[] = [
                    'recd_date'      => $date,                              // receipt ki date
                    'recd_no'        => $rc['voucher_number'],              // receipt number
                    'recd_amount'    => $recdAmt,                           // receipt ki poori amount
                    'amount'         => $take,                              // is invoice me lagi hui amount
                    'inv_no'         => $invoices[$ip]['number'],           // invoice number
                    'inv_due_date'   => $invoices[$ip]['due_date'],         // invoice ki due date
                    'invoice_amount' => $invoices[$ip]['amount'],           // invoice ki poori amount
                ];
            }

            // Invoices khatam ho gayi par receipt me paisa bacha hai (advance / unmatched)
            if (bccomp($left, '0', 2) > 0) {
                $out[] = [
                    'recd_date'      => $date,
                    'recd_no'        => $rc['voucher_number'],
                    'recd_amount'    => $recdAmt,
                    'amount'         => $left,       // jo bacha wo amount
                    'inv_no'         => null,        // koi invoice nahi
                    'inv_due_date'   => null,
                    'invoice_amount' => null,
                ];
            }
        }

        // Saari allocation rows return
        return $out;
    }


    // $vouchers = classified vouchers (collection), $under = Debtor/Creditor
    private function buildBalanceBreakdown($vouchers, ?string $under): array
    {
        // Creditor hai ya nahi (nahi hai to Debtor maana jayega)
        $isCreditor = ($under === 'Sundry Creditors');

        // INVOICES list: Creditor me purchase (credit side), Debtor me sales (debit side)
        $invoices = $vouchers->filter(function ($v) use ($isCreditor) {
            return $isCreditor
                ? ($v['mapped_type_low'] === 'purchase' && bccomp($v['credit'], '0', 2) > 0)
                : ($v['mapped_type_low'] === 'sales' && bccomp($v['debit'], '0', 2) > 0);
        })
            // Purani invoice pehle (date string 'Y-m-d' hai, to string sort bhi chal jata hai)
            ->sortBy(fn($v) => $v['date'])
            ->values();

        // CLEARINGS list: Creditor me payments (debit side), Debtor me receipts (credit side)
        $clearings = $vouchers->filter(function ($v) use ($isCreditor) {
            $typeLow = $v['voucher_type_low'];
            return $isCreditor
                ? (str_contains($typeLow, 'payment') && $v['debit'] > 0)    // string > 0 PHP me numeric compare hota hai
                : (str_contains($typeLow, 'receipt') && $v['credit'] > 0);
        })
            // Purani clearing pehle
            ->sortBy(fn($v) => $v['date'])
            ->values()
            // Har clearing ko chhote array me badla
            ->map(function ($v) use ($isCreditor) {
                return [
                    'date' => $v['date'],
                    'voucher_number' => $v['voucher_number'],
                    'amount' => $isCreditor ? $v['debit'] : $v['credit'],      // poori amount
                    'remaining' => $isCreditor ? $v['debit'] : $v['credit'],   // abhi kitni use hona baaki (shuru me poori)
                ];
            })
            // Plain array banaya, taaki neeche reference (&) se edit ho sake
            ->toArray();

        // Abhi kaunsi clearing chal rahi hai (pointer)
        $clearingPointer = 0;

        // Output rows yahan jama hongi
        $result = [];

        // Har invoice par loop (purani se nayi)
        foreach ($invoices as $inv) {
            // Invoice ki poori amount
            $original = $isCreditor ? $inv['credit'] : $inv['debit'];

            // Kitna abhi clear hona baaki hai (shuru me poori)
            $remaining = $original;

            // Is invoice ko kin clearings ne kitna clear kiya, uski list
            $matchedAgainst = [];

            // Jab tak invoice me amount baaki hai aur clearings bachi hain
            while (bccomp($remaining, '0', 2) > 0 && $clearingPointer < count($clearings)) {

                // & = reference, taaki $c me change karne se asli array $clearings me bhi change ho
                $c = &$clearings[$clearingPointer];

                // Current clearing poori use ho chuki to agli par jao
                if (bccomp($c['remaining'], '0', 2) <= 0) {
                    $clearingPointer++;
                    continue;
                }

                // Is round me kitna adjust hoga: invoice ka remaining aur clearing ka remaining, dono me jo chhota
                $take = bccomp($remaining, $c['remaining'], 2) < 0 ? $remaining : $c['remaining'];

                // Dono se wo amount ghata di
                $c['remaining'] = bcsub($c['remaining'], $take, 2);
                $remaining = bcsub($remaining, $take, 2);

                // Is invoice ke "cleared against" me ye clearing jodi
                $matchedAgainst[] = [
                    'voucher_number' => $c['voucher_number'],
                    'date' => $c['date'],
                    'amount' => $take,
                ];

                // Is clearing ka paisa khatam to agli clearing par jao
                if (bccomp($c['remaining'], '0', 2) <= 0) {
                    $clearingPointer++;
                }

                // Reference tod diya (bhoolne par agli iteration me galti se array badal sakta hai)
                unset($c);
            }

            // Cleared = original - jo baaki bacha; Pending = jo baaki bacha
            $cleared = bcsub($original, $remaining, 2);
            $pending = $remaining;

            // Poori clear ho chuki invoice balance me nahi dikhani, skip
            if (bccomp($pending, '0', 2) <= 0) {
                continue;
            }

            // Status: kuch clear hua hai to Partially Paid/Received, warna Pending
            $status = bccomp($cleared, '0', 2) > 0
                ? ($isCreditor ? 'Partially Paid' : 'Partially Received')
                : 'Pending';

            // Output row
            $result[] = [
                'date' => $inv['date'],
                'voucher_number' => $inv['voucher_number'],
                'voucher_type' => $inv['voucher_type'],
                'particulars' => $inv['particulars'],
                'original' => $original,
                // Key ka naam Creditor me 'paid', Debtor me 'received' (dynamic key)
                $isCreditor ? 'paid' : 'received' => $cleared,
                'pending' => $pending,
                'status' => $status,
                'cleared_against' => $matchedAgainst,
            ];
        }

        return $result;
    }


    // Route se aate hain: $company, $ledger, $under (URL-encoded). Query me ?field=... aata hai
    public function ledgerFieldVouchers(Request $request, $company, $ledger, $under)
    {
        // Kaunse field par click hua (Balance / Sale / Purchase / Receipts / Payments / Other Debits / Other Credits)
        $field = $request->get('field');

        // URL-encoded values ko normal text me badla
        $company = urldecode($company);
        $ledger = urldecode($ledger);
        $under = urldecode($under);

        // Logged-in owner
        $owner = Auth::guard('owner')->user();

        // Owner ki company dhundhi (first(), isliye na mile to null)
        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $company)
            ->first();

        // Us company ka ledger dhundha
        // NOTE: $tallyCompany null ho to yahan ->id par error aayega
        $ledgerModel = TallyLedger::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('ledger_name', $ledger)
            ->first();

        // Ledger nahi mila to khali list return, error nahi
        if (!$ledgerModel) {
            return response()->json(['vouchers' => []]);
        }

        // Voucher type -> mapped type ka dictionary
        $voucherMappings = VoucherMapping::where('company', $company)
            ->pluck('mapped_to', 'voucher_type')
            ->toArray();

        // Ledger ka credit period (days), khali ho to 0
        $ledgerCreditPeriod = (int) ($ledgerModel->credit_period ?? 0);

        // Aaj ki date, din ki shuruaat par
        $today = Carbon::now()->startOfDay();

        // Is ledger ke saare vouchers, date ke hisaab se sorted
        // Har ek ko classify kiya (debit/credit/mapped type) + id, due_date, credit period aur uska source jodi
        $vouchers = TallyVoucher::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->where('ledger_id', $ledgerModel->id)
            ->orderBy('date')
            ->get()
            ->map(fn($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                'id' => $v->id,
                'due_date' => $v->due_date ?? null,
                'credit_period' => $v->credit_period,
                'credit_period_source' => $v->credit_period_source,
            ]);

        // ===== Field 1: Balance =====
        // Balance popup: baaki invoices + kin receipts se kitna clear hua
        if ($field === 'Balance') {
            return response()->json([
                'vouchers' => $this->buildBalanceBreakdown($vouchers, $under),
            ]);
        }

        // ===== Field 2: Receipts (Debtor) / Payments (Creditor) =====
        // Har receipt/payment kin invoices me lagi, uski allocation list
        if (in_array($field, ['Receipts', 'Payments'], true)) {
            return response()->json([
                'vouchers' => $this->buildReceiptAllocation($vouchers, $under, $ledgerCreditPeriod),
            ]);
        }

        // ===== Baaki fields (Sale, Purchase, Other Debits, Other Credits) =====
        // Pehle field ke hisaab se vouchers filter (filterVouchersByField me match expression hai)
        $filtered = $this->filterVouchersByField($vouchers, $field, $under);

        // Other Debits: sirf Debtors ke liye hota hai
        if ($field === 'Other Debits') {
            $filtered = ($under === 'Sundry Creditors')
                ? collect()   // Creditor ke liye Other Debits nahi hota, khali
                : $filtered
                    // Sirf wo jinka debit > 0 aur credit bilkul 0 (credit side wale vouchers kabhi nahi)
                    ->filter(fn ($v) => bccomp($v['debit'], '0', 2) > 0 && bccomp($v['credit'], '0', 2) === 0)
                    // credit ko '0' string se pakka kiya
                    ->map(fn ($v) => array_merge($v, ['credit' => '0']))
                    ->values();
        }

        // Other Credits: sirf Creditors ke liye (upar jaisa hi, ulta)
        if ($field === 'Other Credits') {
            $filtered = ($under !== 'Sundry Creditors')
                ? collect()   // Debtor ke liye Other Credits nahi hota, khali
                : $filtered
                    ->filter(fn ($v) => bccomp($v['credit'], '0', 2) > 0 && bccomp($v['debit'], '0', 2) === 0)
                    ->map(fn ($v) => array_merge($v, ['debit' => '0']))
                    ->values();
        }

        // ===== Sale/Purchase ke liye Status (Received / Overdue / Not Due) =====
        $statusByVoucher = collect();                       // default khali
        $creditPeriodForStatus = $ledgerCreditPeriod;

        if (in_array($field, ['Sale', 'Purchase'], true)) {
            // Open invoices nikale (FIFO se receipts adjust karke)
            $openInvoices = $this->buildOpenInvoices($vouchers, $under, $creditPeriodForStatus, $today);

            // voucher_number se lookup banaya: voucher_number => open invoice (days, pending...)
            $statusByVoucher = $openInvoices->keyBy('voucher_number');
        }

        // ===== Final rows banao =====
        $rows = $filtered->map(function ($v) use ($ledgerCreditPeriod, $today, $field, $statusByVoucher) {

            // Credit period: voucher par alag se set ho (source = 'voucher') to wahi, warna ledger ka
            $resolvedCP = ($v['credit_period_source'] === 'voucher' && $v['credit_period'] !== null)
                ? (int) $v['credit_period']     // "30 Days" jaisi string ko (int) se 30
                : $ledgerCreditPeriod;

            // Overdue days: voucher ki saved due_date se aaj tak (negative ho to 0)
            $days = $this->overdueDays($v['due_date'] ?? null, $today);

            // Status aur pending sirf Sale/Purchase ke liye
            $status = null;
            $pending = null;

            if (in_array($field, ['Sale', 'Purchase'], true)) {
                // Is voucher number ki open invoice dhundhi
                $open = $statusByVoucher->get($v['voucher_number']);

                if (!$open) {
                    // Open list me nahi hai => poori clear ho chuki
                    $status = 'received';
                    $pending = '0';
                } elseif ($open['days'] > 0) {
                    // Open hai aur due date nikal chuki
                    $status = 'overdue';
                    $pending = $open['pending'];
                } else {
                    // Open hai par abhi due nahi
                    $status = 'not_due';
                    $pending = $open['pending'];
                }
            }

            // Popup ki ek row
            return [
                'id' => $v['id'] ?? null,
                'date' => $v['date'],
                'voucher_no' => $v['voucher_number'],
                'voucher_type' => $v['voucher_type'],
                'particulars' => $v['particulars'],
                'debit' => $v['debit'],
                'credit' => $v['credit'],
                'due_date' => $v['due_date'] ?? null,
                'credit_period' => $resolvedCP,
                'days' => $days,
                'status' => $status,     // received / overdue / not_due (sirf Sale/Purchase me, baaki me null)
                'pending' => $pending,   // is invoice ki baaki amount (partial case ke liye)
            ];
        })->values();

        // Rows JSON me
        return response()->json(['vouchers' => $rows]);
    }

    // $vouchers = classified vouchers (collection), $field = kaunsa field, $under = Debtor/Creditor (is function me use nahi hota)
    private function filterVouchersByField($vouchers, string $field, ?string $under)
    {
        // Chhota helper: amount 0 se bada hai ya nahi (string/number kuch bhi ho, bcmath se compare)
        $pos = fn($x) => bccomp((string) $x, '0', 2) > 0;

        // match: $field ki value ke hisaab se ek branch chalti hai aur uska result return hota hai
        return match ($field) {

            // SALE: mapped type "sales" ho aur debit side me amount ho
            'Sale' => $vouchers->filter(fn($v) =>
                $v['mapped_type_low'] === 'sales' && $pos($v['debit'])),

            // PURCHASE: mapped type "purchase" ho aur credit side me amount ho
            'Purchase' => $vouchers->filter(fn($v) =>
                $v['mapped_type_low'] === 'purchase' && $pos($v['credit'])),

            // OTHER DEBITS: sales ke alawa wo vouchers jinka debit hai,
            // aur voucher type me "payment" nahi hona chahiye
            'Other Debits' => $vouchers->filter(fn($v) =>
                $v['mapped_type_low'] !== 'sales'
                && $pos($v['debit'])
                && !str_contains($v['voucher_type_low'], 'payment')),

            // OTHER CREDITS: purchase ke alawa wo vouchers jinka credit hai
            // (logic isOtherCredit() helper me hai)
            'Other Credits' => $vouchers->filter(fn($v) => $this->isOtherCredit($v)),

            // RECEIPTS: voucher type me "receipt" ho aur credit side me amount ho
            'Receipts' => $vouchers->filter(fn($v) =>
                str_contains($v['voucher_type_low'], 'receipt') && $pos($v['credit'])),

            // PAYMENTS: voucher type me "payment" ho aur debit side me amount ho
            'Payments' => $vouchers->filter(fn($v) =>
                str_contains($v['voucher_type_low'], 'payment') && $pos($v['debit'])),

            // Koi aur / unknown field ho to saare vouchers bina filter ke
            default => $vouchers,
        };
    }


    // Route se: $company, $ledger, $under (sab URL-encoded). Query me ?fy=2025-2026 aa sakta hai
    public function ledgerVouchers(Request $request, $company, $ledger, $under = null)
    {
        // Debtor/Creditor wala text decode. NOTE: ye try ke bahar hai; $under null ho to '' ban jata hai
        $under = urldecode($under);

        try {
            $owner = Auth::guard('owner')->user();

            // Raw (undecoded) company naam alag rakha, neeche mapping me yahi use hua hai
            $com = $company;

            // Decoded naam: company aur ledger dhundhne ke liye
            $company = urldecode($company);
            $ledger = urldecode($ledger);

            // Voucher type -> mapped type dictionary
            // NOTE: yahan $com (raw) use hua hai, jabki baaki functions decoded naam use karte hain
            $voucherMappings = VoucherMapping::where('company', $com)
                ->pluck('mapped_to', 'voucher_type')
                ->toArray();

            // Company dhundhi
            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->first();

            // Ledger dhundha; company null ho to id 0 (kuch nahi milega)
            $tallyLedger = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id ?? 0)
                ->where('ledger_name', $ledger)
                ->first();

            // ===== DEBUG CODE (yahi page rok raha hai) =====
            // Ledger ke saare vouchers laaye, print kiye aur die() se script band
            // NOTE: $tallyLedger null ho to ->id par error
            $ledgerVoucher = TallyVoucher::where('owner_id', $owner->id)
                ->where('ledger_id', $tallyLedger->id)
                ->get();

            echo "<pre>";
            print_r($ledgerVoucher);
            die;   // <-- iske baad ka code kabhi nahi chalta

            // ===== Yahan se neeche ka code die() hatane par chalega =====

            $vouchers = [];   // yahan processed vouchers jama honge

            // Debtor ke ye voucher types CREDIT side me jayenge
            $creditSideTypesForDebtor = ['receipt', 'receipt note', 'credit note'];
            // Creditor ke ye voucher types DEBIT side me jayenge
            $debitSideTypesForCreditor = ['payment', 'debit note'];

            // Company aur ledger dono mile tabhi vouchers banao
            if ($tallyCompany && $tallyLedger) {
                // Is ledger ke vouchers, date ke hisaab se sorted
                $voucherRows = TallyVoucher::where('owner_id', $owner->id)
                    ->where('tally_company_id', $tallyCompany->id)
                    ->where('ledger_id', $tallyLedger->id)
                    ->orderBy('date')
                    ->get();

                foreach ($voucherRows as $v) {
                    // Amount ka sign hata ke positive float
                    $amount = abs((float) $v->amount);

                    $voucherType = trim((string) $v->voucher_type);
                    $voucherTypeLow = strtolower($voucherType);

                    // Mapped type: sirf mapping table se, na mile to 'Other than Sales/Purchase'
                    // (classifyVoucherRow ki tarah naam me "sale"/"purchase" dekhne wala fallback yahan nahi hai)
                    $mappedType = trim($voucherMappings[$voucherType] ?? 'Other than Sales/Purchase');

                    $debit = 0;
                    $credit = 0;

                    // Debit ya credit side decide karna (classifyVoucherRow jaisa hi logic)
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

                    // Ek voucher ki row (yahan amounts float hain, string nahi)
                    $vouchers[] = [
                        'date' => (string) $v->date,
                        'particulars' => trim((string) $v->party_ledger_name) ?: '',
                        'voucher_type' => $voucherType,
                        'mapped_type' => $mappedType,
                        'voucher_number' => (string) $v->voucher_number,
                        'debit' => $debit,
                        'credit' => $credit,
                        'master_id' => (string) $v->id,   // naam master_id hai par asli me voucher ka DB id hai
                    ];
                }
            }

                    // Ledger ka all-time opening balance (Tally se aaya, pehle FY se bhi pehle ka)
            $openingBalanceAllTime = (float) ($tallyLedger->opening_balance ?? 0);

            $today = Carbon::now();

            // Current FY ka start year: April (4) ya uske baad to isi saal, warna pichhla saal
            // e.g. Oct 2026 -> 2026 (FY 2026-2027); Feb 2026 -> 2025 (FY 2025-2026)
            $currentFyStartYear = $today->month >= 4 ? (int) $today->year : (int) $today->year - 1;

            // Query me ?fy=2025-2026 aaya ho to wo, warna current FY
            $fyParam = $request->get('fy');
            $selectedFyStartYear = $currentFyStartYear;

            // Format "YYYY-YYYY" ho aur dusra saal pehle se theek +1 ho, tabhi maano
            if ($fyParam && preg_match('/^(\d{4})-(\d{4})$/', $fyParam, $m)) {
                if ((int) $m[2] === (int) $m[1] + 1) {
                    $selectedFyStartYear = (int) $m[1];
                }
            }

            // Labels: selected aur previous FY ke
            $selectedFyLabel = $selectedFyStartYear . '-' . ($selectedFyStartYear + 1);
            $previousFyStartYear = $selectedFyStartYear - 1;
            $previousFyLabel = $previousFyStartYear . '-' . ($previousFyStartYear + 1);

            // Selected FY: 1 April se 31 March tak
            $selectedFyStart = Carbon::create($selectedFyStartYear, 4, 1)->startOfDay();
            $selectedFyEnd = Carbon::create($selectedFyStartYear + 1, 3, 31)->endOfDay();

            // Previous FY: usse ek saal pehle ka
            $previousFyStart = Carbon::create($previousFyStartYear, 4, 1)->startOfDay();
            $previousFyEnd = Carbon::create($previousFyStartYear + 1, 3, 31)->endOfDay();

            // Dropdown ke liye pichhle 5 FY ki list (current se peeche)
            $fyOptions = [];
            for ($i = 0; $i < 5; $i++) {
                $y = $currentFyStartYear - $i;
                $fyOptions[] = $y . '-' . ($y + 1);
            }

            // Date parse karne wala helper: pehle 'Ymd' (20260401), na chale to normal parse, wo bhi fail to null
            $parseDate = function ($d) {
                if (empty($d))
                    return null;
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

            // Vouchers ki copy, date ke hisaab se purane se naye (string compare)
            $vouchersAsc = $vouchers;
            usort($vouchersAsc, fn($a, $b) => strcmp($a['date'], $b['date']));

            $netBeforeSelectedFy = 0;   // selected FY se pehle ka net (debit - credit)
            $netWithinSelectedFy = 0;   // selected FY ke andar ka net

            foreach ($vouchersAsc as $v) {
                $d = $parseDate($v['date']);
                if (!$d)
                    continue;                       // date samajh nahi aayi to skip

                $net = $v['debit'] - $v['credit'];  // debit positive, credit negative

                if ($d->lt($selectedFyStart)) {
                    $netBeforeSelectedFy += $net;
                } elseif ($d->between($selectedFyStart, $selectedFyEnd)) {
                    $netWithinSelectedFy += $net;
                }
                // selected FY ke baad ke vouchers dono me nahi gine jate
            }

            // Selected FY ka opening = all-time opening + FY se pehle ka net
            $openingBalance = $openingBalanceAllTime + $netBeforeSelectedFy;
            // Closing = opening + FY ke andar ka net
            $closingBalance = $openingBalance + $netWithinSelectedFy;

            // Selected FY ke vouchers
            $currentFyVouchers = array_values(array_filter($vouchers, function ($v) use ($parseDate, $selectedFyStart, $selectedFyEnd) {
                $d = $parseDate($v['date']);
                return $d && $d->between($selectedFyStart, $selectedFyEnd);
            }));

            // Previous FY ke vouchers
            $previousFyVouchers = array_values(array_filter($vouchers, function ($v) use ($parseDate, $previousFyStart, $previousFyEnd) {
                $d = $parseDate($v['date']);
                return $d && $d->between($previousFyStart, $previousFyEnd);
            }));

            // Previous FY ke Sales vouchers (naye pehle), view ko jayenge
            $salesVouchers = collect($previousFyVouchers)
                ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'sales')
                ->sortByDesc('date')->values()->toArray();

            // Previous FY ke wo vouchers jinka type bilkul "receipt" hai
            $receiptVouchers = collect($previousFyVouchers)
                ->filter(fn($item) => strtolower(trim($item['voucher_type'])) === 'receipt')
                ->sortByDesc('date')->values()->toArray();


                        // Ek voucher set ko 3 bucket + summary me baantne wala closure
            $buildBuckets = function (array $voucherSet, ?string $under) {

                if ($under === "Sundry Debtors") {
                    // Primary = Sales (debit > 0), naye pehle
                    $primaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'sales' && ($item['debit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    // Secondary = saare credit wale (receipts, credit notes)
                    $secondaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => ($item['credit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    // Journal = sales ke alawa baaki debit wale
                    $journalVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) !== 'sales' && ($item['debit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    // Totals
                    $totalSales = collect($primaryVouchers)->sum('debit');
                    $totalOthers = collect($journalVouchers)->sum('debit');
                    $totalCredit = collect($secondaryVouchers)->sum('credit');

                    return [
                        // Primary aur journal ko wapas purane se naye (asc) kiya
                        'primaryVouchers' => collect($primaryVouchers)->sortBy('date')->values()->toArray(),
                        'secondaryVouchers' => $secondaryVouchers,
                        'journalVouchers' => collect($journalVouchers)->sortBy('date')->values()->toArray(),
                        'primaryLabel' => 'Total Debit',
                        'secondaryLabel' => 'Total Credit',
                        // 'sale' = sab debit (sale + other debits), 'receipts' = sab credit
                        'summary' => ['sale' => $totalSales + $totalOthers, 'receipts' => $totalCredit],
                    ];

                } elseif ($under === "Sundry Creditors") {
                    // Bilkul upar jaisa, bas sides ulti:
                    // primary = purchase (credit), secondary = saare debit, journal = purchase ke alawa credit
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
                    $totalOthers = collect($journalVouchers)->sum('credit');
                    $totalDebit = collect($secondaryVouchers)->sum('debit');

                    return [
                        'primaryVouchers' => collect($primaryVouchers)->sortBy('date')->values()->toArray(),
                        'secondaryVouchers' => $secondaryVouchers,
                        'journalVouchers' => collect($journalVouchers)->sortBy('date')->values()->toArray(),
                        'primaryLabel' => 'Total Credit',
                        'secondaryLabel' => 'Total Debit',
                        'summary' => ['sale' => $totalPurchase + $totalOthers, 'receipts' => $totalDebit],
                    ];
                }

                // Debtor/Creditor ke alawa kuch ho to khali
                return [
                    'primaryVouchers' => [],
                    'secondaryVouchers' => [],
                    'journalVouchers' => [],
                    'primaryLabel' => 'Primary',
                    'secondaryLabel' => 'Secondary',
                    'summary' => ['sale' => 0, 'receipts' => 0],
                ];
            };

                    // Ek voucher set me se "kitna abhi tak pending hai" nikalne wala closure
            $buildPendingSnapshot = function (array $voucherSet, ?string $under) {

                if ($under === 'Sundry Debtors') {
                    // Primary = sales invoices; har ek ka pending shuru me poora debit
                    $primary = collect($voucherSet)
                        ->filter(fn($v) => strtolower(trim($v['mapped_type'])) === 'sales' && ($v['debit'] ?? 0) > 0)
                        ->sortBy('date')->values()
                        ->map(function ($v) {
                            $v['pending'] = $v['debit'];
                            return $v; })->all();

                    // Others = sales ke alawa debit wale (journal wagairah), pending shuru me poora
                    $others = collect($voucherSet)
                        ->filter(fn($v) => strtolower(trim($v['mapped_type'])) !== 'sales' && ($v['debit'] ?? 0) > 0)
                        ->sortBy('date')->values()
                        ->map(function ($v) {
                            $v['pending'] = $v['debit'];
                            return $v; })->all();

                    // Receipts = saare credit wale, purane pehle
                    $receipts = collect($voucherSet)
                        ->filter(fn($v) => ($v['credit'] ?? 0) > 0)
                        ->sortBy('date')->values()->all();

                    foreach ($receipts as $receipt) {
                        $amount = $receipt['credit'];   // is receipt me kitna paisa bacha hai

                        // PEHLE "others" ko clear karo
                        foreach ($others as $i => $o) {
                            if ($amount <= 0)
                                break;
                            if ($others[$i]['pending'] <= 0)
                                continue;
                            $adjust = min($amount, $others[$i]['pending']);
                            $others[$i]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }

                        // PHIR bacha hua paisa sales invoices me
                        foreach ($primary as $i => $p) {
                            if ($amount <= 0)
                                break;
                            if ($primary[$i]['pending'] <= 0)
                                continue;
                            $adjust = min($amount, $primary[$i]['pending']);
                            $primary[$i]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                    }

                    // Jinka pending 0.01 se zyada hai wo jodkar date se sort
                    $pendingVouchers = collect(array_merge($primary, $others))
                        ->filter(fn($v) => ($v['pending'] ?? 0) > 0.01)
                        ->sortBy('date')->values()->toArray();

                    return ['pendingAmount' => array_sum(array_column($pendingVouchers, 'pending')), 'pendingVouchers' => $pendingVouchers];

                } elseif ($under === 'Sundry Creditors') {
                    // Bilkul upar jaisa: primary = purchase (credit), others = purchase ke alawa credit,
                    // payments = saare debit wale. Payments pehle others, phir purchase ko clear karte hain.
                    $primary = collect($voucherSet)
                        ->filter(fn($v) => strtolower(trim($v['mapped_type'])) === 'purchase' && ($v['credit'] ?? 0) > 0)
                        ->sortBy('date')->values()
                        ->map(function ($v) {
                            $v['pending'] = $v['credit'];
                            return $v; })->all();

                    $others = collect($voucherSet)
                        ->filter(fn($v) => strtolower(trim($v['mapped_type'])) !== 'purchase' && ($v['credit'] ?? 0) > 0)
                        ->sortBy('date')->values()
                        ->map(function ($v) {
                            $v['pending'] = $v['credit'];
                            return $v; })->all();

                    $payments = collect($voucherSet)
                        ->filter(fn($v) => ($v['debit'] ?? 0) > 0)
                        ->sortBy('date')->values()->all();

                    foreach ($payments as $payment) {
                        $amount = $payment['debit'];

                        foreach ($others as $i => $o) {
                            if ($amount <= 0)
                                break;
                            if ($others[$i]['pending'] <= 0)
                                continue;
                            $adjust = min($amount, $others[$i]['pending']);
                            $others[$i]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                        foreach ($primary as $i => $p) {
                            if ($amount <= 0)
                                break;
                            if ($primary[$i]['pending'] <= 0)
                                continue;
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

            // Kisi cutoff date tak ke vouchers (cutoff ke din tak, barabar bhi)
            $vouchersUpTo = function (array $allVouchersAsc, Carbon $cutoff) use ($parseDate) {
                return array_values(array_filter($allVouchersAsc, function ($v) use ($parseDate, $cutoff) {
                    $d = $parseDate($v['date']);
                    return $d && $d->lte($cutoff);
                }));
            };

            // Previous FY ke end (31 March) tak ki pending => "opening pending"
            $vouchersUpToPreviousFyEnd = $vouchersUpTo($vouchersAsc, $previousFyEnd);
            $pendingSnapshot = $buildPendingSnapshot($vouchersUpToPreviousFyEnd, $under);

            // Selected FY ke end tak ki pending => "closing pending"
            $vouchersUpToSelectedFyEnd = $vouchersUpTo($vouchersAsc, $selectedFyEnd);
            $closingSnapshot = $buildPendingSnapshot($vouchersUpToSelectedFyEnd, $under);

            // Dono FY ke buckets alag alag
            $currentFyOnlyBuckets = $buildBuckets($currentFyVouchers, $under);
            $previousFyOnlyBuckets = $buildBuckets($previousFyVouchers, $under);

            // Upar ke summary cards: sale/receipts selected FY ke, pending previous FY end ka
            $summary = [
                'sale' => $currentFyOnlyBuckets['summary']['sale'],
                'receipts' => $currentFyOnlyBuckets['summary']['receipts'],
                'pending' => $pendingSnapshot['pendingAmount'],
            ];

            // Tables ke liye lists: PREVIOUS FY ke buckets se
            $primaryVouchers = $previousFyOnlyBuckets['primaryVouchers'];
            $secondaryVouchers = $previousFyOnlyBuckets['secondaryVouchers'];
            $journalVouchers = $previousFyOnlyBuckets['journalVouchers'];
            $primaryLabel = $previousFyOnlyBuckets['primaryLabel'];
            $secondaryLabel = $previousFyOnlyBuckets['secondaryLabel'];

            // Pending lists: opening wali (previous FY end) aur closing wali (selected FY end)
            $pendingVouchers = $pendingSnapshot['pendingVouchers'];
            $closingBalanceVouchers = $closingSnapshot['pendingVouchers'];

            // Sab kuch Blade view ko
            return view('owner.tally.tally.ledger-vouchers', compact(
                'company', 'ledger', 'vouchers', 'summary',
                'openingBalance', 'closingBalance',
                'salesVouchers', 'receiptVouchers', 'journalVouchers', 'under',
                'primaryVouchers', 'secondaryVouchers', 'primaryLabel', 'secondaryLabel',
                'fyOptions', 'selectedFyLabel', 'previousFyLabel',
                'pendingVouchers', 'closingBalanceVouchers'
            ));

        } catch (\Throwable $e) {
            // Error ki detail log me
            Log::error('Ledger Voucher Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Pichhle page par error message ke saath
            return back()->with('error', $e->getMessage());
        }
    }

    public function ledgerFollowUp(Request $request, $company, $ledger, $under = null)
    {
        $vouchers = $this->ledgerVouchers($request, $company, $ledger, $under);
        return view('owner.tally.tally.ledger-follow-up', compact('company', 'ledger', 'vouchers'));
    }

    public function clearStatus(Request $request)
    {
        $user = auth('owner')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // IMPORTANT:
        // Replace this example query with your actual allocation table,
        // company ownership conditions and status column.
        $deleted = DB::table('rms_followups')
        ->where('status', 'Pending')
        ->delete();

        return response()->json([
            'message' => 'Follow-up statuses cleared successfully.',
            'deleted' => $deleted,
        ]);
    }

    public function clearRespondedStatus(Request $request)
    {
        $user = auth('owner')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // IMPORTANT:
        // Replace this example query with your actual allocation table,
        
        $updated = DB::table('rms_followups')
            ->where('status', 'Responded')
            ->update([
            'clearStatus' => 1,
            'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Follow-up Respondedstatuses cleared successfully.',
            'updated' => $updated,
        ]);
    }

    public function followUpsHub(Request $request, $company)
    {
        $company = urldecode($company);

        $owner = Auth::guard('owner')->user();

        $type   = $request->query('type', '');
        $ledger = urldecode($request->query('ledger', ''));
        $under  = urldecode($request->query('under', ''));

        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $company)
            ->first();

        $ledgerModels = TallyLedger::select(
            'rms_tally_ledgers.id as ledger_id',
            'rms_tally_ledgers.ledger_name as name',
            'rms_tally_ledgers.ledger_mobile_number as mobile',
            'rms_tally_ledgers.parent as under',
            'rms_tally_ledgers.credit_period',
            'rms_tally_ledgers.closing_balance',

            'rms_followups.id as followup_id',
            'rms_followups.type',
            'rms_followups.status',
            'rms_followups.clearStatus',
            'rms_followups.action',
            'rms_followups.frequency',
            'rms_followups.template',
            'rms_followups.assigned_to',
            'rms_followups.assigned_by',
            'rms_followups.allocation_date',
            'rms_followups.response_date'
        )
        ->leftJoin('rms_followups', function ($join) use ($tallyCompany) {

            $join->on(
                'rms_followups.ledger_id',
                '=',
                'rms_tally_ledgers.id'
            );

            $join->where(
                'rms_followups.company_id',
                $tallyCompany->id
            );
        })
        ->where(
            'rms_tally_ledgers.owner_id',
            $owner->id
        )
        
        ->where(
            'rms_tally_ledgers.tally_company_id',
            $tallyCompany->id
        )
        ->orderBy('rms_tally_ledgers.ledger_name')
        ->get();

        $vouchersByLedger = TallyVoucher::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->whereIn('ledger_id', $ledgerModels->pluck('ledger_id'))
            ->get()
            ->groupBy('ledger_id');

        $voucherMappings = VoucherMapping::where('company', $tallyCompany->company_name)
            ->pluck('mapped_to', 'voucher_type')
            ->toArray();

        // echo "<pre>"; print_r($company); echo "</pre>"; die;

        $today = Carbon::now()->startOfDay();

        $ownerTargets = OverdueTargetSetting::where('owner_id', $owner->id)
            ->where('company_id', $tallyCompany->id)
            ->pluck('receivable_target', 'bucket_month');

        $nowForTarget = now();

        $followUps = $ledgerModels->map(function ($ledger) use ($vouchersByLedger, $today, $voucherMappings, $ownerTargets, $nowForTarget) {
            $under = $ledger->under; 

        
            $vouchers = $vouchersByLedger->get($ledger->ledger_id, collect())
                ->map(fn ($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                    'due_date'       => $v->due_date ?? null,
                    'voucher_number' => $v->voucher_number,
                ]);

            $isReceipt = fn ($v) => str_contains($v['voucher_type_low'], 'receipt');
            $isPayment = fn ($v) => str_contains($v['voucher_type_low'], 'payment');

            $sale = $purchase = $otherDebits = $otherCredits = $receipts = $payments = '0';

            if ($under === 'Sundry Creditors') {
                $purchase     = $vouchers->filter(fn ($v) => $v['mapped_type_low'] === 'purchase' && bccomp($v['credit'], '0', 2) > 0)
                                    ->reduce(fn ($t, $v) => bcadd($t, $v['credit'], 2), '0');
                $otherCredits = $vouchers->filter(fn ($v) => $v['mapped_type_low'] !== 'purchase' && bccomp($v['credit'], '0', 2) > 0)
                                    ->reduce(fn ($t, $v) => bcadd($t, $v['credit'], 2), '0');
                $payments     = $vouchers->filter(fn ($v) => $isPayment($v) && bccomp($v['debit'], '0', 2) > 0)
                                    ->reduce(fn ($t, $v) => bcadd($t, $v['debit'], 2), '0');
            } else {
                $sale        = $vouchers->filter(fn ($v) => $v['mapped_type_low'] === 'sales' && bccomp($v['debit'], '0', 2) > 0)
                                    ->reduce(fn ($t, $v) => bcadd($t, $v['debit'], 2), '0');
                $otherDebits = $vouchers->filter(fn ($v) => $v['mapped_type_low'] !== 'sales' && bccomp($v['debit'], '0', 2) > 0)
                                    ->reduce(fn ($t, $v) => bcadd($t, $v['debit'], 2), '0');
                $receipts    = $vouchers->filter(fn ($v) => $isReceipt($v) && bccomp($v['credit'], '0', 2) > 0)
                                    ->reduce(fn ($t, $v) => bcadd($t, $v['credit'], 2), '0');
            }

            $creditPeriod = (int) ($ledger->credit_period ?? 0);
    
            $openInvoices = $this->buildOpenInvoices($vouchers, $under, $creditPeriod, $today);

            $balance = (string) ($ledger->closing_balance ?? '0');

            $due = $openInvoices->filter(fn ($i) => $i['days'] > 0)
                        ->reduce(fn ($t, $i) => bcadd($t, $i['pending'], 2), '0');

            
            $notDue = bcsub(ltrim($balance, '-+'), $due, 2);
            if (bccomp($notDue, '0', 2) < 0) {
                $notDue = '0';
            }

            $pendingBal = $openInvoices->reduce(fn ($t, $i) => bcadd($t, $i['pending'], 2), '0');

            $extraNotDue = bcsub(ltrim($balance, '-+'), $pendingBal, 2);
            if (bccomp($extraNotDue, '0', 2) < 0) {
                $extraNotDue = '0';
            }

            $targetRows = $this->computeTargetRows($vouchers, $under, $creditPeriod, $ownerTargets, $nowForTarget, $extraNotDue);

            $firstRow      = collect($targetRows)->reverse()->first(fn ($r) => $r['targetBal'] !== null);
            $targetBalance = $firstRow['targetBal'] ?? '0';

        


        /*
        |--------------------------------------------------------------------------
        | Ledger Filter
        |--------------------------------------------------------------------------
        */

        // if (!empty($ledger)) {

        //     $ledger = $ledger
        //         ->filter(function ($item) use ($ledger) {
        //             return $item->name === $ledger;
        //         })
        //         ->values();

        // } elseif (!empty($under)) {

        //     $ledger = $ledger
        //         ->filter(function ($item) use ($under) {
        //             return $item->under === $under;
        //         })
        //         ->values();
        // }


        /*
        |--------------------------------------------------------------------------
        | Prepare Follow Up Data
        |--------------------------------------------------------------------------
        */
        

            /*
            |--------------------------------------------------------------------------
            | Common Data
            |--------------------------------------------------------------------------
            */

            $data = [
                'id' => $ledger->ledger_id,

                'ledger_id' => $ledger->ledger_id,

                'followup_id' => $ledger->followup_id,

                'name' => $ledger->name,

                'mobile' => $ledger->mobile,

                'under' => $ledger->under,

                'credit_period' => $ledger->credit_period,

                'type' => $ledger->type,

                'status' => $ledger->status ?? 'Not Assign Yet',
                'clearStatus' => $ledger->clearStatus ?? 0 ,

                'action' => $ledger->action,

                'frequency' => $ledger->frequency,

                'template' => $ledger->template,

                'assigned_to' => $ledger->assigned_to,

                'assigned_by' => $ledger->assigned_by,

                'allocation_date' => $ledger->allocation_date,

                'response_date' => $ledger->response_date,
                'closing_balance' => $ledger->closing_balance,
                'targetBalance' => $targetBalance,
            ];


            /*
            |--------------------------------------------------------------------------
            | Type Based Logic
            |--------------------------------------------------------------------------
            */

            switch ($ledger->type) {

                /*
                |--------------------------------------------------------------------------
                | Balance / Target %
                |--------------------------------------------------------------------------
                */

                case 'Follow Up-Balances/%Targets':

                    /*
                    * Calculate actual balance from Tally here.
                    */

                    $balance = abs($ledger->closing_balance); // replace with actual Tally calculation

                    $target = $targetBalance; // replace with actual target

                    $targetPct = $target > 0
                        ? round((abs($balance) / $target) * 100, 2)
                        : 0;

                    $data['balance'] = $balance;

                    $data['target'] = $target;

                    $data['target_pct'] = $targetPct;

                    break;


                /*
                |--------------------------------------------------------------------------
                | Balance / Target Month
                |--------------------------------------------------------------------------
                */

                case 'Follow Up-Balances/Targets(months)':

                    /*
                    * Calculate actual balance and target
                    * for the required month.
                    */

                    $balance = $balance;

                    $target = $targetBalance;

                    $targetMonth = now()->subMonths(1)->format('Y-m');

                    $targetPct = $target > 0
                        ? round((abs($balance) / $target) * 100, 2)
                        : 0;

                    $data['balance'] = $balance;

                    $data['target'] = $target;

                    $data['target_month'] = $targetMonth;

                    $data['target_pct'] = $targetPct;

                    break;


                /*
                |--------------------------------------------------------------------------
                | Balance / Age Wise
                |--------------------------------------------------------------------------
                */

                case 'Follow Up-Balances/Days(agewise)':

                    /*
                    * Calculate actual outstanding
                    * and age from Tally transactions.
                    */

                    $balance =$balance;

                    $days = (int) ($openInvoices->filter(fn ($i) => $i['days'] > 0)->max('days') ?? 0);

                    $ageBucket = match (true) {
                        $days > 90 => '90+ Days',
                        $days > 45 => '46-90 Days',
                        $days > 15 => '16-45 Days',
                        default    => '0-15 Days',
                    };

                    $data['balance'] = $balance;

                    $data['days'] = $days;

                    $data['age_bucket'] = $ageBucket;

                    break;


                /*
                |--------------------------------------------------------------------------
                | Balance / Days 10-20-30
                |--------------------------------------------------------------------------
                */

                case 'Follow Up-Balances/Days(10-20-30)':

                    /*
                    * Calculate from actual Tally invoice/bill data.
                    */

                    $balance = $balance;

                    $days = 20;

                    $date = now()->subDays(20)->format('Y-m-d');

                    $dueDate = now()->addDays(10)->format('Y-m-d');

                    $invoiceNo = 'INV-2204';

                    $interestDue = 150;

                    $data['balance'] = $balance;

                    $data['days'] = $days;

                    $data['date'] = $date;

                    $data['due_date'] = $dueDate;

                    $data['inv_no'] = $invoiceNo;

                    $data['interest_due'] = $interestDue;

                    break;


                /*
                |--------------------------------------------------------------------------
                | Due
                |--------------------------------------------------------------------------
                */

                case 'Follow Up-Due':

                    /*
                    * Actual overdue invoice calculation.
                    */

                    $date = now()->subDays(21)->format('Y-m-d');

                    $dueDate = now()->addDays(21)->format('Y-m-d');

                    $invoiceNo = 'INV-2201';

                    $days = 12;

                    $due = 32000.00;

                    $interestDue = 480.00;

                    $data['date'] = $date;

                    $data['due_date'] = $dueDate;

                    $data['inv_no'] = $invoiceNo;

                    $data['days'] = $days;

                    $data['due'] = $due;

                    $data['interest_due'] = $interestDue;

                    break;


                /*
                |--------------------------------------------------------------------------
                | Not Due
                |--------------------------------------------------------------------------
                */

                case 'Follow Up-Not Due':

                    /*
                    * Actual outstanding invoices which
                    * are not yet due.
                    */


                    $date = now()->subDays(5)->format('Y-m-d');

                    $dueDate = now()->addDays(35)->format('Y-m-d');

                    $invoiceNo = 'INV-2202';

                    $daysPending = 5;

                    $notDue = 27500.00;

                    $data['date'] = $date;

                    $data['due_date'] = $dueDate;

                    $data['inv_no'] = $invoiceNo;

                    $data['days_pending'] = $daysPending;

                    $data['not_due'] = $notDue;

                    break;


                /*
                |--------------------------------------------------------------------------
                | Over Limits
                |--------------------------------------------------------------------------
                */

                case 'Follow Up Overlimits':

                    /*
                    * Actual ledger balance vs credit limit.
                    */

                    $balance = $balance;

                    $creditLimit = 0;

                    $overLimit = 0;

                    $data['balance'] = $balance;

                    $data['credit_limit'] = $creditLimit;

                    $data['over_limit'] = $overLimit;

                    break;


                /*
                |--------------------------------------------------------------------------
                | Normal Balance
                |--------------------------------------------------------------------------
                */

                case 'Follow Up-Balances':

                    /*
                    * Actual ledger balance from Tally.
                    */

                    $balance = $balance;

                    $data['balance'] = $balance;

                    break;
            }

            return $data;
        });


        /*
        |--------------------------------------------------------------------------
        | Type Filter
        |--------------------------------------------------------------------------
        */

        if (
            !empty($type) &&
            $type !== 'Follow Up-Quick /Smart - Follow -up'
        ) {

            $followUps = $followUps
                ->filter(function ($row) use ($type) {
                    return $row['type'] === $type;
                })
                ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */
//echo "<pre>"; print_r($followUps); die;
        return view(
            'owner.tally.tally.followup-hub',
            [
                'company' => $company,
                'type' => $type,
                'selectedLedger' => $ledger,
                'selectedUnder' => $under,
                'followUps' => $followUps,
            ]
        );
    }

    public function assignFollowUpAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'type' => 'required|string',
            'action' => 'required|string',
            'frequency' => 'required|string',
            'template' => 'nullable|string',
        ]);

        $owner = Auth::guard('owner')->user();

        $tallyCompany = TallyCompany::where(
            'owner_id',
            $owner->id
        )->first();

        if (!$tallyCompany) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.'
            ], 404);
        }

        foreach ($request->ids as $ledgerId) {

            $followUp = Followup::updateOrCreate(
                [
                    'company_id' => $tallyCompany->id,
                    'ledger_id' => $ledgerId,
                ],
                [
                    'type' => $request->type,
                    'action' => $request->action,
                    'frequency' => $request->frequency,
                    'template' => $request->template,
                    'status' => 'Pending',
                    'assigned_to' => null,
                    'assigned_by' => $owner->id,
                    'allocation_date' => now(),
                    'response_date' => null,
                ]
            );

            $followUpLog = FollowupLog::create([
                'followup_id'     => $followUp->id,
                'company_id'      => $tallyCompany->id,
                'ledger_id'       => $ledgerId,
                'type'             => $request->type,
                'action'           => $request->action,
                'frequency'       => $request->frequency,
                'template'         => $request->template,
                'status'           => 'Pending',
                'assigned_to'      => null,
                'assigned_by'      => $owner->id,
                'allocation_date'  => now(),
                'response_date'    => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Follow Up allocated successfully.',
            'followup_id' => $followUp->id,
        ]);
    }

    public function saveBulkFollowUps(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|integer',
            'ledger_id'  => 'required|integer',
            'type'       => 'required|string|max:255',
            'action'     => 'required|string|max:255',
            'frequency'  => 'required|string|max:255',
            'template'   => 'nullable|string',
        ]);

        $owner = auth('owner')->user();

        $followUp = Followup::create([
            'company_id'      => $validated['company_id'],
            'ledger_id'       => $validated['ledger_id'],
            'type'             => $validated['type'],
            'action'           => $validated['action'],
            'frequency'       => $validated['frequency'],
            'template'        => $validated['template'] ?? null,
            'status'           => 'Pending',
            'assigned_to'      => null,
            'assigned_by'      => $owner->id,
            'allocation_date'  => now(),
            'response_date'    => null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Follow-up created successfully.',
            'data'    => $followUp,
        ]);
    }
    
    public function followUpsHubOLDS(Request $request, $company)
    {

        $com     = $company;
        $company = urldecode($company);
        $owner   = Auth::guard('owner')->user();
        
        $type   = $request->query('type') ?: '';  
        $ledger = urldecode($request->query('ledger', ''));
        $under  = urldecode($request->query('under', ''));
        
        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $company)
            ->first();

        $tallyLedger = TallyLedger::select(
            'rms_tally_ledgers.id',
            'rms_tally_ledgers.ledger_name as name',
            'rms_tally_ledgers.ledger_mobile_number as mobile',
            'rms_tally_ledgers.parent as under',
            'rms_tally_ledgers.credit_period',
            'rms_followups.id as followup_id',
            DB::raw("COALESCE(rms_followups.status, 'Assign') as status"),
            'rms_followups.type'
        )
        ->leftJoin('rms_followups', function ($join) use ($tallyCompany) {
            $join->on('rms_followups.ledger_id', '=', 'rms_tally_ledgers.id')
                ->where('rms_followups.company_id', $tallyCompany->id);
        })
        ->where('rms_tally_ledgers.owner_id', $owner->id)
        ->where('rms_tally_ledgers.tally_company_id', $tallyCompany->id)
        ->orderBy('rms_tally_ledgers.ledger_name')
        ->get();

        
        $allLedgers = [
            [
                'name' => 'Dummy Ledger 1',
                'under' => 'Sundry Debtors',
                'mobile' => '9876543210',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 54321.10,
                'target' => 50000,
            ],
            [
                'name' => 'Dummy Ledger 2',
                'under' => 'Sundry Debtors',
                'mobile' => '9123456780',
                'type' => 'Follow Up-Balances/Targets(months)',
                'balance' => 72500.00,
                'target' => 80000,
                'target_month' => now()->subMonths(1)->format('Y-m'),
            ],
            [
                'name' => 'Dummy Ledger 3',
                'under' => 'Sundry Debtors',
                'mobile' => '9988776655',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 18200.50,
                'age_bucket' => '31-60 Days',
                'days' => 45,
            ],
            [
                'name' => 'Dummy Ledger 4',
                'under' => 'Sundry Creditors',
                'mobile' => '9871234567',
                'type' => 'Follow Up-Balances/Days(10-20-30)',
                'balance' => 9800.00,
                'days' => 20,
                'date' => now()->subDays(20)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2204',
                'interest_due' => 150.00,
            ],
            [
                'name' => 'Dummy Ledger 5',
                'under' => 'Sundry Creditors',
                'mobile' => '9012345678',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(21)->format('Y-m-d'),
                'due_date' => now()->addDays(21)->format('Y-m-d'),
                'inv_no' => 'INV-2201',
                'days' => 12,
                'due' => 32000.00,
                'interest_due' => 480.00,
            ],
            [
                'name' => 'Dummy Ledger 6',
                'under' => 'Sundry Debtors',
                'mobile' => '9090909090',
                'type' => 'Follow Up-Not Due',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'due_date' => now()->addDays(35)->format('Y-m-d'),
                'inv_no' => 'INV-2202',
                'days_pending' => 5,
                'not_due' => 27500.00,
            ],
            [
                'name' => 'Dummy Ledger 7',
                'under' => 'Sundry Creditors',
                'mobile' => '9191919191',
                'type' => 'Follow Up Overlimits',
                'balance' => 12500.00,
            ],
            [
                'name' => 'Dummy Ledger 8',
                'under' => 'Sundry Debtors',
                'mobile' => '9898989898',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 61230.00,
                'target' => 55000,
            ],
            [
                'name' => 'Dummy Ledger 9',
                'under' => 'Sundry Creditors',
                'mobile' => '9797979797',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(10)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2203',
                'days' => 18,
                'due' => 41500.00,
                'interest_due' => 620.00,
            ],
            [
                'name' => 'Dummy Ledger 10',
                'under' => 'Sundry Debtors',
                'mobile' => '9696969696',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 9450.75,
                'age_bucket' => '0-30 Days',
                'days' => 15,
            ],
            [
                'name' => 'Dummy Ledger 11',
                'under' => 'Sundry Debtors',
                'mobile' => '9595959595',
                'type' => 'Follow Up-Balances',
                'balance' => 15750.25,
            ],
        ];

        if (!empty($ledger)) {
            $tallyLedger = collect($tallyLedger)
                ->filter(fn($l) => $l['name'] === $ledger)
                ->values()
                ->all();
        } elseif (!empty($under)) {
            $tallyLedger = collect($tallyLedger)
                ->filter(fn($l) => ($l['under'] ?? null) === $under)
                ->values()
                ->all();
        }

        $followUps = collect($tallyLedger)->values()->map(function ($l, $index) {
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
                'accountant_status' => $l['status'] ? 'Pending' : 'Responded',
            ];
        });

        if (!empty($type) && $type !== 'Follow Up-Quick /Smart - Follow -up') {
            $followUps = $followUps->filter(fn($row) => $row['type'] === $type)->values();
        }

        return view('owner.tally.tally.followup-hub', [
            'company' => $company,
            'type' => $type,
            'selectedLedger' => $ledger,
            'selectedUnder' => $under,
            'followUps' => $followUps,
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


    public function assignLedgers($company)
    {
        $company = urldecode($company);
        $owner   = Auth::guard('owner')->user();

        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $company)
            ->firstOrFail();

        // sirf is owner ke accountants ke collectors
        $accountantIds = Accountant::where('owner_id', $owner->id)->pluck('id');

        $collectors = Collector::whereIn('accountant_id', $accountantIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'accountant_id']);

        $collectorNames = $collectors->pluck('name', 'id');

        // ledger_id => [collector_id, ...]
        $assignMap = DB::table('rms_ledger_collectors')
            ->where('owner_id', $owner->id)
            ->where('company_id', $tallyCompany->id)
            ->get()
            ->groupBy('ledger_id')
            ->map(fn($rows) => $rows->pluck('collector_id')->unique()->values()->all());

        $ledgers = TallyLedger::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->orderBy('ledger_name')
            ->get(['id', 'ledger_name', 'parent', 'unique_ledger_id', 'ledger_mobile_number'])
            ->map(function ($l) use ($assignMap, $collectorNames) {
                $ids = $assignMap->get($l->id, []);
                return [
                    'id'              => $l->id,
                    'name'            => $l->ledger_name,
                    'under'           => $l->parent,
                    'unique_id'       => $l->unique_ledger_id,
                    'mobile'          => $l->ledger_mobile_number,
                    'collector_ids'   => $ids,
                    'collector_names' => collect($ids)->map(fn($id) => $collectorNames[$id] ?? ('#' . $id))->all(),
                ];
            });

        return view('owner.tally.tally.ledgers-assign', [
            'company'      => $company,
            'tallyCompany' => $tallyCompany,
            'collectors'   => $collectors,
            'ledgers'      => $ledgers,
        ]);
    }


    public function saveLedgerCollectors(Request $request)
    {
        $owner = Auth::guard('owner')->user();

        $data = $request->validate([
            'company_id'   => 'required|integer',
            'collector_id' => 'required|integer',
            'ledger_ids'   => 'required|array|min:1',
            'ledger_ids.*' => 'integer',
        ]);

        $tallyCompany = TallyCompany::where('id', $data['company_id'])
            ->where('owner_id', $owner->id)
            ->firstOrFail();

        $collector = Collector::where('id', $data['collector_id'])
            ->whereIn('accountant_id', Accountant::where('owner_id', $owner->id)->pluck('id'))
            ->first();

        if (!$collector) {
            return response()->json(['success' => false, 'message' => 'Collector not found.'], 404);
        }

        $ledgers = TallyLedger::where('owner_id', $owner->id)
            ->where('tally_company_id', $tallyCompany->id)
            ->whereIn('parent', ['Sundry Debtors', 'Sundry Creditors'])
            ->whereIn('id', $data['ledger_ids'])
            ->get(['id', 'parent']);

        if ($ledgers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid ledgers selected.'], 422);
        }

        $now = now();

        DB::transaction(function () use ($ledgers, $collector, $tallyCompany, $owner, $now) {
            // in ledgers ke purane saare collectors hata do (1 ledger = 1 collector)
            DB::table('rms_ledger_collectors')
                ->where('company_id', $tallyCompany->id)
                ->whereIn('ledger_id', $ledgers->pluck('id'))
                ->delete();

            $rows = $ledgers->map(fn($l) => [
                'company_id'    => $tallyCompany->id,
                'ledger_id'     => $l->id,
                'ledger_under'  => $l->parent,
                'owner_id'      => $owner->id,
                'accountant_id' => $collector->accountant_id,
                'collector_id'  => $collector->id,
                'created_at'    => $now,
                'updated_at'    => $now,
            ])->all();

            DB::table('rms_ledger_collectors')->insert($rows);
        });

        return response()->json([
            'success' => true,
            'message' => $ledgers->count() . " ledger(s) assigned to {$collector->name}.",
        ]);
    }


    public function removeLedgerCollectors(Request $request)
    {
        $owner = Auth::guard('owner')->user();

        $data = $request->validate([
            'company_id'   => 'required|integer',
            'collector_id' => 'required|integer',
            'ledger_ids'   => 'required|array|min:1',
            'ledger_ids.*' => 'integer',
        ]);

        $deleted = DB::table('rms_ledger_collectors')
            ->where('owner_id', $owner->id)
            ->where('company_id', $data['company_id'])
            ->where('collector_id', $data['collector_id'])
            ->whereIn('ledger_id', $data['ledger_ids'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} ledger(s) removed from this collector.",
        ]);
    }
    

    private function interestDataForVouchers(array $ids, int $ownerId): array
    {
        $ledgerIds = TallyVoucher::where('owner_id', $ownerId)
            ->whereIn('id', $ids)
            ->pluck('ledger_id')
            ->unique();

        $out = ['received' => [], 'pending' => []];

        foreach (TallyLedger::where('owner_id', $ownerId)->whereIn('id', $ledgerIds)->get() as $ledger) {
            $company = TallyCompany::find($ledger->tally_company_id);

            $res = $this->ledgerInterestData(new Request([
                'company' => $company->company_name,
                'ledger'  => $ledger->ledger_name,
            ]))->getData(true);

            $out['received'] = array_merge($out['received'], $res['received'] ?? []);
            $out['pending']  = array_merge($out['pending'],  $res['pending']  ?? []);
        }

        return $out;
    }

    public function telecaller(Request $request, $company = null)
    {
        $owner = Auth::guard('owner')->user();
    
        $companies = TallyCompany::where('owner_id', $owner->id)
            ->when($company, fn($q) => $q->where('company_name', urldecode($company)))
            ->get();
    
        $companyIds = $companies->pluck('id');
    
        // Follow Up Hub se jo action "tele call" ke liye assign hue
        $assigned = Followup::query()
            ->from('rms_followups')
            ->join('rms_tally_ledgers', 'rms_tally_ledgers.id', '=', 'rms_followups.ledger_id')
            ->whereIn('rms_followups.company_id', $companyIds)
            ->where('rms_tally_ledgers.owner_id', $owner->id)
            ->where(function ($q) {                                   // Hub me Clear kiye hue rows na dikhen
                $q->whereNull('rms_followups.clearStatus')->orWhere('rms_followups.clearStatus', 0);
            })
            ->where('rms_followups.action', 'allocate_tele_call')     // Hub ke dropdown ki exact value
            ->select(
                'rms_followups.id as followup_id',
                'rms_followups.company_id',
                'rms_followups.ledger_id',
                'rms_followups.type',
                'rms_followups.status',
                'rms_followups.clearStatus',
                'rms_followups.frequency',
                'rms_followups.allocation_date',
                'rms_tally_ledgers.ledger_name as name',
                'rms_tally_ledgers.ledger_mobile_number as mobile',
                'rms_tally_ledgers.parent as under',
                'rms_tally_ledgers.credit_period',
                'rms_tally_ledgers.closing_balance',
                'rms_tally_ledgers.interest_rate',
                'rms_tally_ledgers.balance_limit'
            )
            ->orderByDesc('rms_followups.allocation_date')
            ->get();
    
        // DB me type kaise bhi save ho (label ya key), ye blade key me convert karega
        $resolveType = function (?string $raw): ?string {
            $t = strtolower(preg_replace('/\s+/', '', (string) $raw));   // spaces hata do
    
            if ($t === '') return null;
            if (str_contains($t, 'overlimit'))                              return 'overlimits';
            if (str_contains($t, 'notdue') || str_contains($t, 'not_due'))  return 'not_due';
            if (str_contains($t, '10-20-30') || str_contains($t, '10_20_30')) return 'days_10_20_30';
            if (str_contains($t, 'agewise'))                                return 'days_agewise';
            if (str_contains($t, 'months'))                                 return 'targets_months';
            if (str_contains($t, '%targets') || str_contains($t, 'pct_targets')) return 'pct_targets';
            if (str_contains($t, 'due'))                                    return 'due';
            if (str_contains($t, 'balance'))                                return 'balances';
    
            return null;
        };
    
        $today        = Carbon::now()->startOfDay();
        $nowForTarget = now();
        $teleCallData = [];
    
        foreach ($assigned->groupBy('company_id') as $companyId => $items) {
            $tallyCompany = $companies->firstWhere('id', $companyId);
    
            $vouchersByLedger = TallyVoucher::where('owner_id', $owner->id)
                ->where('tally_company_id', $companyId)
                ->whereIn('ledger_id', $items->pluck('ledger_id'))
                ->get()
                ->groupBy('ledger_id');
    
            $voucherMappings = VoucherMapping::where('company', $tallyCompany->company_name)
                ->pluck('mapped_to', 'voucher_type')->toArray();
    
            $ownerTargets = OverdueTargetSetting::where('owner_id', $owner->id)
                ->where('company_id', $companyId)
                ->pluck('receivable_target', 'bucket_month');
    
            foreach ($items as $item) {
                $under        = $item->under;
                $creditPeriod = (int) ($item->credit_period ?? 0);
                $balanceAbs   = ltrim((string) ($item->closing_balance ?? '0'), '-+');
                $balanceF     = (float) $balanceAbs;
    
                $vouchers = $vouchersByLedger->get($item->ledger_id, collect())
                    ->map(fn($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                        'id'             => $v->id,
                        'due_date'       => $v->due_date ?? null,
                        'voucher_number' => $v->voucher_number,
                    ]);
    
                $open = $this->buildOpenInvoices($vouchers, $under, $creditPeriod, $today);
    
                $dueInvoices    = $open->filter(fn($i) => $i['days'] > 0)->sortByDesc('days')->values();
                $notDueInvoices = $open->filter(fn($i) => $i['days'] <= 0)->sortBy('days')->values();
    
                // Fallback: agar us category ka invoice na mile to koi bhi open invoice / last voucher
                $anyInvoice = $open->sortByDesc('days')->first();
                $lastVoucher = $vouchers->sortByDesc(fn($v) => Carbon::parse($v['date'])->timestamp)->first();
    
                $dueInv    = $dueInvoices->first()    ?? $anyInvoice;
                $notDueInv = $notDueInvoices->first() ?? $anyInvoice;
    
                // Target (same logic as ledgers page)
                $pendingSum  = $open->reduce(fn($t, $i) => bcadd($t, $i['pending'], 2), '0');
                $extraNotDue = bcsub($balanceAbs, $pendingSum, 2);
                if (bccomp($extraNotDue, '0', 2) < 0) {
                    $extraNotDue = '0';
                }
    
                $targetRows = $this->computeTargetRows($vouchers, $under, $creditPeriod, $ownerTargets, $nowForTarget, $extraNotDue);
                $firstRow   = collect($targetRows)->reverse()->first(fn($r) => $r['targetBal'] !== null);
                $target     = (float) ($firstRow['targetBal'] ?? 0);
                $targetPct  = $target > 0 ? round(($balanceF / $target) * 100, 2) : 0;
    
                // Overlimit: target = balance limit, % = balance limit ke upar kitna
                $limit        = (float) ($item->balance_limit ?? 0);
                $overlimitPct = $limit > 0 ? round(($balanceF / $limit) * 100, 2) : 0;
    
                // Interest due (overdue invoice par)
                $dueDays     = (int) ($dueInv['days'] ?? 0);
                $interestDue = 0;
                if ($dueInv && $dueDays > 0 && (float) $item->interest_rate > 0) {
                    $interestDue = round(((float) $dueInv['pending'] * (float) $item->interest_rate * $dueDays) / 36500, 2);
                }
    
                $key = $resolveType($item->type);
    
                $row = [
                    'id'            => $item->followup_id,
                    'followup_id'   => $item->followup_id,
                    'ledger_id'     => $item->ledger_id,
                    'type'          => $key,
                    'name'          => $item->name,
                    'mobile'        => $item->mobile,
                    'balance'       => $balanceF,
                    'assigned_date' => $item->allocation_date,
                    'frequency'     => $item->frequency,
                    'status'        => ucfirst(strtolower((string) $item->status)),
                ];
    
                // Hub ke chips ke hisaab se saare fields
                $dueAmount    = (float) ($dueInv['pending'] ?? 0);
                $notDueAmount = (float) ($notDueInv['pending'] ?? 0);
    
                switch ($key) {
                    case 'due':
                        $row += [
                            'date'         => $dueInv['date']           ?? ($lastVoucher['date'] ?? null),
                            'due_date'     => $dueInv['due_date']       ?? null,
                            'inv_no'       => $dueInv['voucher_number'] ?? ($lastVoucher['voucher_number'] ?? null),
                            'days'         => max(0, $dueDays),
                            'due'          => $dueAmount,
                            'interest_due' => $interestDue,
                        ];
                        break;
    
                    case 'not_due':
                        $row += [
                            'date'         => $notDueInv['date']           ?? ($lastVoucher['date'] ?? null),
                            'due_date'     => $notDueInv['due_date']       ?? null,
                            'inv_no'       => $notDueInv['voucher_number'] ?? ($lastVoucher['voucher_number'] ?? null),
                            'days_pending' => $notDueInv ? max(0, -1 * (int) $notDueInv['days']) : 0,
                            'not_due'      => $notDueAmount,
                        ];
                        break;
    
                    case 'pct_targets':
                        $row += [
                            'target'     => $target,
                            'target_pct' => $targetPct,
                        ];
                        break;
    
                    case 'targets_months':
                        $row += [
                            'target'       => $target,
                            'target_pct'   => $targetPct,
                            'target_month' => now()->subMonth()->format('Y-m'),
                        ];
                        break;
    
                    case 'days_agewise':
                        $row['days'] = max(0, $dueDays);
                        break;
    
                    case 'days_10_20_30':
                        $row += [
                            'date'         => $dueInv['date']           ?? ($lastVoucher['date'] ?? null),
                            'due_date'     => $dueInv['due_date']       ?? null,
                            'inv_no'       => $dueInv['voucher_number'] ?? ($lastVoucher['voucher_number'] ?? null),
                            'days'         => max(0, $dueDays),
                            'interest_due' => $interestDue,
                        ];
                        break;
    
                    case 'overlimits':
                    case 'balances':
                        // sirf Balance chip, wo blade me balance se aa jata h
                        break;
                }
    
                $teleCallData[] = $row;
            }
        }
    
        return view('owner.tally.tally.followup-action.telecaller', compact('teleCallData', 'company'));
    }

    public function call()
    {
        $companies = TallyCompany::where('owner_id', $owner->id)
        ->when($company, fn($q) => $q->where('company_name', urldecode($company)))
        ->get();
 
    $companyIds = $companies->pluck('id');
 
    // Follow Up Hub se jo action "tele call" ke liye assign hue
    $assigned = Followup::query()
        ->from('rms_followups')
        ->join('rms_tally_ledgers', 'rms_tally_ledgers.id', '=', 'rms_followups.ledger_id')
        ->whereIn('rms_followups.company_id', $companyIds)
        ->where('rms_tally_ledgers.owner_id', $owner->id)
        ->where(function ($q) {                                   // Hub me Clear kiye hue rows na dikhen
            $q->whereNull('rms_followups.clearStatus')->orWhere('rms_followups.clearStatus', 0);
        })
        ->where('rms_followups.action', 'allocate_tele_call')     // Hub ke dropdown ki exact value
        ->select(
            'rms_followups.id as followup_id',
            'rms_followups.company_id',
            'rms_followups.ledger_id',
            'rms_followups.type',
            'rms_followups.status',
            'rms_followups.clearStatus',
            'rms_followups.frequency',
            'rms_followups.allocation_date',
            'rms_tally_ledgers.ledger_name as name',
            'rms_tally_ledgers.ledger_mobile_number as mobile',
            'rms_tally_ledgers.parent as under',
            'rms_tally_ledgers.credit_period',
            'rms_tally_ledgers.closing_balance',
            'rms_tally_ledgers.interest_rate',
            'rms_tally_ledgers.balance_limit'
        )
        ->orderByDesc('rms_followups.allocation_date')
        ->get();
 
    // DB me type kaise bhi save ho (label ya key), ye blade key me convert karega
    $resolveType = function (?string $raw): ?string {
        $t = strtolower(preg_replace('/\s+/', '', (string) $raw));   // spaces hata do
 
        if ($t === '') return null;
        if (str_contains($t, 'overlimit'))                              return 'overlimits';
        if (str_contains($t, 'notdue') || str_contains($t, 'not_due'))  return 'not_due';
        if (str_contains($t, '10-20-30') || str_contains($t, '10_20_30')) return 'days_10_20_30';
        if (str_contains($t, 'agewise'))                                return 'days_agewise';
        if (str_contains($t, 'months'))                                 return 'targets_months';
        if (str_contains($t, '%targets') || str_contains($t, 'pct_targets')) return 'pct_targets';
        if (str_contains($t, 'due'))                                    return 'due';
        if (str_contains($t, 'balance'))                                return 'balances';
 
        return null;
    };
 
    $today        = Carbon::now()->startOfDay();
    $nowForTarget = now();
    $teleCallData = [];
 
    foreach ($assigned->groupBy('company_id') as $companyId => $items) {
        $tallyCompany = $companies->firstWhere('id', $companyId);
 
        $vouchersByLedger = TallyVoucher::where('owner_id', $owner->id)
            ->where('tally_company_id', $companyId)
            ->whereIn('ledger_id', $items->pluck('ledger_id'))
            ->get()
            ->groupBy('ledger_id');
 
        $voucherMappings = VoucherMapping::where('company', $tallyCompany->company_name)
            ->pluck('mapped_to', 'voucher_type')->toArray();
 
        $ownerTargets = OverdueTargetSetting::where('owner_id', $owner->id)
            ->where('company_id', $companyId)
            ->pluck('receivable_target', 'bucket_month');
 
        foreach ($items as $item) {
            $under        = $item->under;
            $creditPeriod = (int) ($item->credit_period ?? 0);
            $balanceAbs   = ltrim((string) ($item->closing_balance ?? '0'), '-+');
            $balanceF     = (float) $balanceAbs;
 
            $vouchers = $vouchersByLedger->get($item->ledger_id, collect())
                ->map(fn($v) => $this->classifyVoucherRow($v, $under, $voucherMappings) + [
                    'id'             => $v->id,
                    'due_date'       => $v->due_date ?? null,
                    'voucher_number' => $v->voucher_number,
                ]);
 
            $open = $this->buildOpenInvoices($vouchers, $under, $creditPeriod, $today);
 
            $dueInvoices    = $open->filter(fn($i) => $i['days'] > 0)->sortByDesc('days')->values();
            $notDueInvoices = $open->filter(fn($i) => $i['days'] <= 0)->sortBy('days')->values();
 
            // Fallback: agar us category ka invoice na mile to koi bhi open invoice / last voucher
            $anyInvoice = $open->sortByDesc('days')->first();
            $lastVoucher = $vouchers->sortByDesc(fn($v) => Carbon::parse($v['date'])->timestamp)->first();
 
            $dueInv    = $dueInvoices->first()    ?? $anyInvoice;
            $notDueInv = $notDueInvoices->first() ?? $anyInvoice;
 
            // Target (same logic as ledgers page)
            $pendingSum  = $open->reduce(fn($t, $i) => bcadd($t, $i['pending'], 2), '0');
            $extraNotDue = bcsub($balanceAbs, $pendingSum, 2);
            if (bccomp($extraNotDue, '0', 2) < 0) {
                $extraNotDue = '0';
            }
 
            $targetRows = $this->computeTargetRows($vouchers, $under, $creditPeriod, $ownerTargets, $nowForTarget, $extraNotDue);
            $firstRow   = collect($targetRows)->reverse()->first(fn($r) => $r['targetBal'] !== null);
            $target     = (float) ($firstRow['targetBal'] ?? 0);
            $targetPct  = $target > 0 ? round(($balanceF / $target) * 100, 2) : 0;
 
            // Overlimit: target = balance limit, % = balance limit ke upar kitna
            $limit        = (float) ($item->balance_limit ?? 0);
            $overlimitPct = $limit > 0 ? round(($balanceF / $limit) * 100, 2) : 0;
 
            // Interest due (overdue invoice par)
            $dueDays     = (int) ($dueInv['days'] ?? 0);
            $interestDue = 0;
            if ($dueInv && $dueDays > 0 && (float) $item->interest_rate > 0) {
                $interestDue = round(((float) $dueInv['pending'] * (float) $item->interest_rate * $dueDays) / 36500, 2);
            }
 
            $key = $resolveType($item->type);
 
            $row = [
                'id'            => $item->followup_id,
                'followup_id'   => $item->followup_id,
                'ledger_id'     => $item->ledger_id,
                'type'          => $key,
                'name'          => $item->name,
                'mobile'        => $item->mobile,
                'balance'       => $balanceF,
                'assigned_date' => $item->allocation_date,
                'frequency'     => $item->frequency,
                'status'        => ucfirst(strtolower((string) $item->status)),
            ];
 
            // Hub ke chips ke hisaab se saare fields
            $dueAmount    = (float) ($dueInv['pending'] ?? 0);
            $notDueAmount = (float) ($notDueInv['pending'] ?? 0);
 
            switch ($key) {
                case 'due':
                    $row += [
                        'date'         => $dueInv['date']           ?? ($lastVoucher['date'] ?? null),
                        'due_date'     => $dueInv['due_date']       ?? null,
                        'inv_no'       => $dueInv['voucher_number'] ?? ($lastVoucher['voucher_number'] ?? null),
                        'days'         => max(0, $dueDays),
                        'due'          => $dueAmount,
                        'interest_due' => $interestDue,
                    ];
                    break;
 
                case 'not_due':
                    $row += [
                        'date'         => $notDueInv['date']           ?? ($lastVoucher['date'] ?? null),
                        'due_date'     => $notDueInv['due_date']       ?? null,
                        'inv_no'       => $notDueInv['voucher_number'] ?? ($lastVoucher['voucher_number'] ?? null),
                        'days_pending' => $notDueInv ? max(0, -1 * (int) $notDueInv['days']) : 0,
                        'not_due'      => $notDueAmount,
                    ];
                    break;
 
                case 'pct_targets':
                    $row += [
                        'target'     => $target,
                        'target_pct' => $targetPct,
                    ];
                    break;
 
                case 'targets_months':
                    $row += [
                        'target'       => $target,
                        'target_pct'   => $targetPct,
                        'target_month' => now()->subMonth()->format('Y-m'),
                    ];
                    break;
 
                case 'days_agewise':
                    $row['days'] = max(0, $dueDays);
                    break;
 
                case 'days_10_20_30':
                    $row += [
                        'date'         => $dueInv['date']           ?? ($lastVoucher['date'] ?? null),
                        'due_date'     => $dueInv['due_date']       ?? null,
                        'inv_no'       => $dueInv['voucher_number'] ?? ($lastVoucher['voucher_number'] ?? null),
                        'days'         => max(0, $dueDays),
                        'interest_due' => $interestDue,
                    ];
                    break;
 
                case 'overlimits':
                case 'balances':
                    // sirf Balance chip, wo blade me balance se aa jata h
                    break;
            }
 
            $teleCallData[] = $row;
        }
    }
 
            return view('owner.tally.tally.followup-action.telecaller', compact('teleCallData', 'company'));
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
            'balances' => 'Follow Up-Balances',
            'pct_targets' => 'Follow Up-Balances/%Targets',
            'targets_months' => 'Follow Up-Balances/Targets(months)',
            'days_agewise' => 'Follow Up-Balances/Days(agewise)',
            'days_10_20_30' => 'Follow Up-Balances/Days(10-20-30)',
            'due' => 'Follow Up-Due',
            'not_due' => 'Follow Up-Not Due',
            'overlimits' => 'Follow Up Overlimits',
        ];

        $actionLabels = [
            'allocate_tele_call' => 'Allocate to tele call',
            'call' => 'Call',
            'whatsapp' => 'WhatsApp',
            'physical_visit' => 'Physical visit',
            'escalation' => 'Escalation',
            'no_action' => 'No action',
        ];

        $history = [
            [
                'id' => 1,
                'ledger_id' => 101,
                'ledger_name' => 'ABC Traders',
                'mobile' => '9000000001',
                'type' => 'pct_targets',
                'type_label' => $typeLabels['pct_targets'],
                'balance' => 54321.10,
                'target' => 50000,
                'target_pct' => 108.64,
                'assigned_to' => 'Rahul',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01',
                'action' => 'call',
                'action_label' => $actionLabels['call'],
                'frequency' => 'weekly',
                'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th',
                'solution' => 'Payment plan agreed',
                'admin_solution' => null,
            ],
            [
                'id' => 2,
                'ledger_id' => 101,
                'ledger_name' => 'ABC Traders',
                'mobile' => '9000000001',
                'type' => 'due',
                'type_label' => $typeLabels['due'],
                'balance' => 32000,
                'target' => null,
                'target_pct' => null,
                'date' => '2026-07-20',
                'due_date' => '2026-08-10',
                'inv_no' => 'INV-2201',
                'days' => 12,
                'interest_due' => 480,
                'assigned_to' => 'Rahul',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-02',
                'action' => 'whatsapp',
                'action_label' => $actionLabels['whatsapp'],
                'frequency' => 'daily',
                'response_date' => null,
                'response' => null,
                'solution' => null,
                'admin_solution' => null,
            ],
            [
                'id' => 3,
                'ledger_id' => 101,
                'ledger_name' => 'ABC Traders',
                'mobile' => '9000000001',
                'type' => 'overlimits',
                'type_label' => $typeLabels['overlimits'],
                'balance' => 12500,
                'target' => null,
                'target_pct' => null,
                'assigned_to' => 'Priya',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-28',
                'action' => 'escalation',
                'action_label' => $actionLabels['escalation'],
                'frequency' => 'weekly',
                'response_date' => '2026-07-30',
                'response' => 'Escalated to regional manager',
                'solution' => 'Limit revised',
                'admin_solution' => null,
            ],
            [
                'id' => 4,
                'ledger_id' => 102,
                'ledger_name' => 'XYZ Enterprises',
                'mobile' => '9000000002',
                'type' => 'targets_months',
                'type_label' => $typeLabels['targets_months'],
                'balance' => 78900,
                'target' => 80000,
                'target_pct' => 98.63,
                'due_date' => '2026-07-31',
                'assigned_to' => 'Aman',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-15',
                'action' => 'physical_visit',
                'action_label' => $actionLabels['physical_visit'],
                'frequency' => 'monthly',
                'response_date' => '2026-07-20',
                'response' => 'Visited site, cheque collected',
                'solution' => 'Cleared 50% balance',
                'admin_solution' => null,
            ],
            [
                'id' => 5,
                'ledger_id' => 102,
                'ledger_name' => 'XYZ Enterprises',
                'mobile' => '9000000002',
                'type' => 'days_10_20_30',
                'type_label' => $typeLabels['days_10_20_30'],
                'balance' => 15600,
                'target' => null,
                'target_pct' => null,
                'days' => 20,
                'assigned_to' => 'Aman',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-05',
                'action' => 'no_action',
                'action_label' => $actionLabels['no_action'],
                'frequency' => 'daily',
                'response_date' => null,
                'response' => null,
                'solution' => null,
                'admin_solution' => null,
            ],
            [
                'id' => 6,
                'ledger_id' => 103,
                'ledger_name' => 'Global Textiles',
                'mobile' => '9000000003',
                'type' => 'not_due',
                'type_label' => $typeLabels['not_due'],
                'balance' => 43200,
                'target' => null,
                'target_pct' => null,
                'date' => '2026-07-25',
                'due_date' => '2026-08-25',
                'inv_no' => 'INV-3105',
                'days_pending' => 30,
                'assigned_to' => 'Priya',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-06',
                'action' => 'allocate_tele_call',
                'action_label' => $actionLabels['allocate_tele_call'],
                'frequency' => 'weekly',
                'response_date' => '2026-08-07',
                'response' => 'Confirmed, will pay on due date',
                'solution' => null,
                'admin_solution' => null,
            ],
            [
                'id' => 7,
                'ledger_id' => 104,
                'ledger_name' => 'Sunrise Distributors',
                'mobile' => '9000000004',
                'type' => 'days_agewise',
                'type_label' => $typeLabels['days_agewise'],
                'balance' => 99500,
                'target' => null,
                'target_pct' => null,
                'days' => 46,
                'assigned_to' => 'Rahul',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-10',
                'action' => 'escalation',
                'action_label' => $actionLabels['escalation'],
                'frequency' => 'monthly',
                'response_date' => null,
                'response' => null,
                'solution' => null,
                'admin_solution' => null,
            ],
            [
                'id' => 8,
                'ledger_id' => 105,
                'ledger_name' => 'ABCD Traders',
                'mobile' => '9000000001',
                'type' => 'balances',
                'type_label' => $typeLabels['balances'],
                'balance' => 54321.10,
                'target' => 50000,
                'target_pct' => 108.64,
                'assigned_to' => 'Rahul',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01',
                'action' => 'call',
                'action_label' => $actionLabels['call'],
                'frequency' => 'weekly',
                'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th',
                'solution' => 'Payment plan agreed',
                'admin_solution' => null,
            ],
        ];

        return view('owner.tally.tally.followup-history', compact('history'));
    }


    private array $followupTypes = [
        'Follow Up-Balances',
        'Follow Up-Balances/%Targets',
        'Follow Up-Balances/Targets(months)',
        'Follow Up-Balances/Days(agewise)',
        'Follow Up-Balances/Days(10-20-30)',
        'Follow Up-Due',
        'Follow Up-Not Due',
        'Follow Up Overlimits',
    ];
    private array $tones = ['Polite', 'Urgent', 'Firm', 'Friendly'];
    private array $languages = ['English', 'Hindi', 'Hinglish'];
    private array $fixedParams = [1 => 'Party Name', 2 => 'Outstanding Balance', 3 => 'Voucher/Invoice No.', 4 => 'Due Date', 5 => 'Company Name', 6 => 'GSTIN', 7 => 'Payment Link', 8 => 'Days Overdue'];

    private function templateRules(): array
    {
        return [
            'followup_type' => ['required', Rule::in($this->followupTypes)],
            'name' => ['required', 'string', 'max:255'],
            'tone' => ['required', Rule::in($this->tones)],
            'language' => ['required', Rule::in($this->languages)],
            'message' => ['required', 'string'],
        ];
    }

    private function cleanMessage(string $html): string
    {
        return strip_tags($html, '<p><br><strong><em><u><ul><ol><li><a><blockquote><h2><h3><h4>');
    }

    private function resolveCompany($company): TallyCompany
    {
        $ownerId = Auth::guard('owner')->id();
        $company = urldecode((string) $company);

        $query = TallyCompany::where('owner_id', $ownerId);

        // numeric aaye to pehle id try karo, warna naam
        $found = (clone $query)->where('company_name', $company)->first();

        if (!$found && ctype_digit($company)) {
            $found = (clone $query)->where('id', (int) $company)->first();
        }

        abort_if(!$found, 404, 'Company not found for this owner: ' . $company);

        return $found;
    }

    public function templates(Request $request, $company)
    {
        $ownerId = Auth::guard('owner')->id();
        $tallyCompany = $this->resolveCompany($company);

        $templates = WhatsappTemplate::where('owner_id', $ownerId)
            ->where('company_id', $tallyCompany->id)->latest()->get();

        $customParams = WhatsappTemplateParameter::forOwner($ownerId)->orderBy('num')->get()
            ->map(fn($p) => ['num' => $p->num, 'label' => $p->label, 'custom' => true]);

        return view('owner.tally.other.templates', compact('templates', 'company', 'customParams'));
    }

    public function storeTemplate(Request $request, $company): JsonResponse
    {
        $data = $request->validate($this->templateRules());
        $data['message'] = $this->cleanMessage($data['message']);
        $data['owner_id'] = Auth::guard('owner')->id();
        $data['company_id'] = $this->resolveCompany($company)->id;

        $template = WhatsappTemplate::create($data);

        return response()->json(['message' => 'Template saved successfully.', 'template' => $template], 201);
    }

    public function storeParameter(Request $request, $company): JsonResponse
    {
        $ownerId = Auth::guard('owner')->id();

        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
        ]);

        $exists = WhatsappTemplateParameter::forOwner($ownerId)
            ->whereRaw('LOWER(label) = ?', [strtolower($data['label'])])
            ->exists();

        if ($exists) {
            return response()->json(
                ['errors' => ['label' => ['This parameter already exists.']]],
                422
            );
        }

        // 1-8 are fixed, so custom ones start at 9
        $num = max(8, (int) WhatsappTemplateParameter::forOwner($ownerId)->max('num')) + 1;

        $param = WhatsappTemplateParameter::create([
            'owner_id' => $ownerId,
            'num' => $num,
            'label' => $data['label'],
        ]);

        return response()->json(['num' => $param->num, 'label' => $param->label], 201);
    }

    public function updateTemplate(Request $request, $company, $id): JsonResponse
    {
        $template = WhatsappTemplate::where('owner_id', Auth::guard('owner')->id())->findOrFail($id);

        $data = $request->validate($this->templateRules());
        $data['message'] = $this->cleanMessage($data['message']);
        $template->update($data);

        return response()->json(['message' => 'Template updated successfully.', 'template' => $template]);
    }

    public function deleteTemplate($company, $id): JsonResponse
    {
        WhatsappTemplate::where('owner_id', Auth::guard('owner')->id())->findOrFail($id)->delete();

        return response()->json(['message' => 'Template deleted successfully.']);
    }

    public function aiTemplates()
    {
        return view('owner.tally.other.ai-templates');
    }

    public function responses()
    {
        $ownerId = Auth::guard('owner')->id();

        $responses = TallyResponse::where('owner_id', $ownerId)->latest()->get();
        $solutions = TallySolution::where('owner_id', $ownerId)->latest()->get();

        return view('owner.tally.other.responses', compact('responses', 'solutions'));
    }

    // ---------- RESPONSE ----------
    public function responseSubmit(Request $request)
    {
        $request->validate(['response' => 'required|string']);

        $ownerId = Auth::guard('owner')->id();

        if ($request->filled('response_id')) {
            $item = TallyResponse::where('owner_id', $ownerId)->findOrFail($request->response_id);
            $item->update(['response' => $request->response]);
            $message = 'Response updated successfully';
        } else {
            $item = TallyResponse::create([
                'owner_id' => $ownerId,
                'response' => $request->response,
            ]);
            $message = 'Response saved successfully';
        }

        return response()->json(['status' => true, 'message' => $message, 'data' => $item]);
    }

    public function responseDelete($id)
    {
        TallyResponse::where('owner_id', Auth::guard('owner')->id())->findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Response deleted successfully']);
    }

    // ---------- SOLUTION ----------
    public function solutionSubmit(Request $request)
    {
        $request->validate(['solution' => 'required|string']);

        $ownerId = Auth::guard('owner')->id();

        if ($request->filled('solution_id')) {
            $item = TallySolution::where('owner_id', $ownerId)->findOrFail($request->solution_id);
            $item->update(['solution' => $request->solution]);
            $message = 'Solution updated successfully';
        } else {
            $item = TallySolution::create([
                'owner_id' => $ownerId,
                'solution' => $request->solution,
            ]);
            $message = 'Solution saved successfully';
        }

        return response()->json(['status' => true, 'message' => $message, 'data' => $item]);
    }

    public function solutionDelete($id)
    {
        TallySolution::where('owner_id', Auth::guard('owner')->id())->findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Solution deleted successfully']);
    }

    public function overdueTarget($company)
    {
        $company = urldecode($company);
        $owner = Auth::guard('owner')->user();

        $tallyCompany = TallyCompany::where('owner_id', $owner->id)
            ->where('company_name', $company)
            ->firstOrFail();

        $rows = OverdueTargetSetting::where('owner_id', $owner->id)
            ->where('company_id', $tallyCompany->id)
            ->orderBy('bucket_month')
            ->get();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row->bucket_month] = [
                'receivable' => $row->receivable_target !== null ? (float) $row->receivable_target : null,
                'payable' => $row->payable_target !== null ? (float) $row->payable_target : null,
                'diff' => $row->diff_target !== null ? (float) $row->diff_target : null,
            ];
        }

        return view('owner.tally.other.overdue-target', compact('settings', 'tallyCompany'));
    }

    public function saveOverdueTarget(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();

        $validated = $request->validate([
            'company_id' => [
                'required',
                'integer',
                Rule::exists('rms_tally_companies', 'id')->where('owner_id', $ownerId),
            ],
            'receivable' => 'required|array|size:6',
            'receivable.*' => 'nullable|numeric|min:0|max:100',
            'payable' => 'required|array|size:6',
            'payable.*' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach (['receivable' => 'Receivable', 'payable' => 'Payable'] as $key => $label) {
            $last = null;
            foreach (array_values($validated[$key]) as $v) {
                if ($v === null) {
                    continue;
                }
                $v = (float) $v;
                if ($last !== null && $v > $last) {
                    return response()->json([
                        'success' => false,
                        'message' => "$label target % must be in decreasing order.",
                    ], 422);
                }
                $last = $v;
            }
        }

        $companyId = $validated['company_id'];
        $receivable = array_values($validated['receivable']);
        $payable = array_values($validated['payable']);

        DB::transaction(function () use ($ownerId, $companyId, $receivable, $payable) {
            for ($i = 0; $i < 6; $i++) {
                $rcv = $receivable[$i] ?? null;
                $pay = $payable[$i] ?? null;

                $diff = ($rcv !== null && $pay !== null)
                    ? round((float) $rcv - (float) $pay, 2)
                    : null;

                OverdueTargetSetting::updateOrCreate(
                    [
                        'owner_id' => $ownerId,
                        'company_id' => $companyId,
                        'bucket_month' => $i + 1,
                    ],
                    [
                        'receivable_target' => $rcv,
                        'payable_target' => $pay,
                        'diff_target' => $diff,
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully.',
        ]);
    }


    public function defaultSettings($company)
    {
        try {
            $company = urldecode($company);
            $owner = Auth::guard('owner')->user();

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
                    'id' => $ledger->id,
                    'name' => $ledger->ledger_name,
                    'under' => $ledger->parent,
                    'mobile' => $ledger->ledger_mobile_number,
                    'ledger_mobile_number_source' => $ledger->ledger_mobile_number_source,
                    'credit_period' => $ledger->credit_period,
                    'credit_period_source' => $ledger->credit_period_source,
                    'interest_rate' => $ledger->interest_rate,
                    'interest_rate_source' => $ledger->interest_rate_source,
                    'interest_style' => $ledger->interest_style,
                    'assigned_collector' => $ledger->assigned_collector,
                    'rows' => $this->buildLedgerRows($ledger, $vouchers),
                    'base_interest_rate' => $ledger->base_interest_rate, // NEW
                ];
            })->values()->all();

            return view('owner.tally.other.default-settings', compact('ledgers', 'company'));

        } catch (\Exception $e) {
            Log::error('Tally companyLedgers failed', [
                'company' => $company,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to fetch ledgers from Tally. Please try again.');
        }
    }


    public function setCommonSetting(Request $request)
    {
        $validated = $request->validate([
            'company' => 'required|string',
            'under' => 'required|in:Sundry Debtors,Sundry Creditors',
            'credit_period' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0',
            'base_interest_rate' => 'nullable|numeric|min:0', // NEW
            'collector_id' => 'nullable|integer',
        ]);

        $company = $validated['company'];
        $creditPeriod = $validated['credit_period'] ?? null;
        $interestRate = $validated['interest_rate'] ?? null;
        $baseInterestRate = $validated['base_interest_rate'] ?? null; // NEW
        $collectorId = $validated['collector_id'] ?? null;

        if (blank($creditPeriod) && blank($interestRate) && blank($baseInterestRate) && blank($collectorId)) {
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

            if (!blank($baseInterestRate)) {
                $dataToUpdate['base_interest_rate'] = $baseInterestRate;
            }

            if (!blank($creditPeriod) && $ledger->credit_period_source !== 'tally') {
                $dataToUpdate['credit_period'] = $creditPeriod . ' Days';
                // credit_period_source ko touch nahi karna
            }

            if (!blank($interestRate) && $ledger->interest_rate_source !== 'tally') {
                $dataToUpdate['interest_rate'] = $interestRate;
                // interest_rate_source ko touch nahi karna
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
                    'id' => $l->id,
                    'name' => $l->ledger_name,
                    'mobile' => $l->ledger_mobile_number,
                    'ledger_mobile_number_source' => $l->ledger_mobile_number_source,
                    'credit_period' => $l->credit_period,
                    'credit_period_source' => $l->credit_period_source,
                    'interest_rate' => $l->interest_rate,
                    'interest_rate_source' => $l->interest_rate_source,
                    'collector_name' => $collector->name ?? null,
                    'base_interest_rate' => $l->base_interest_rate,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => "Default settings applied to {$updatedCount} ledger(s) successfully.",
            'under' => $validated['under'],
            'ledgers' => $freshLedgers,
        ]);
    }



    private function buildLedgerRows($ledger, $vouchers)
    {
        if (empty($ledger->mobile_number)) {
            return [];
        }

        $sum = fn($types) => $vouchers->whereIn('voucher_type', (array) $types)->sum('amount');
        $isDebtor = $ledger->parent === 'Sundry Debtors';

        $rows = [
            'Balance' => (float) ($ledger->closing_balance ?? 0),
            'Due' => (float) $vouchers->where('is_due', true)->sum('amount'),
            'Target' => (float) ($ledger->target_amount ?? 0),
        ];

        if ($isDebtor) {
            $rows['Sale'] = $sum(['Sales']);
            $rows['Other Debits'] = $sum(['Debit Note', 'Journal']);
            $rows['Receipts'] = $sum(['Receipt']);
            $rows['Interest Received'] = $sum(['Interest Received']);
        } else {
            $rows['Purchase'] = $sum(['Purchase']);
            $rows['Other Credits'] = $sum(['Credit Note', 'Journal']);
            $rows['Payments'] = $sum(['Payment']);
            $rows['Interest Paid'] = $sum(['Interest Paid']);
        }

        $rows['Not Due'] = (float) $vouchers->where('is_due', false)->sum('amount');
        $rows['Interest Cost'] = (float) ($ledger->interest_cost ?? 0);
        $rows['Interest Due'] = (float) ($ledger->interest_due ?? 0);
        $rows['Interest Waived'] = (float) ($ledger->interest_waived ?? 0);
        $rows['Bad Debts / Family A/c'] = (float) ($ledger->bad_debts ?? 0);
        $rows[$isDebtor ? 'Total Debtors' : 'Total Creditors'] = (float) ($ledger->closing_balance ?? 0);
        $rows['March Closing Pending'] = (float) ($ledger->march_closing_pending ?? 0);

        return $rows;
    }



    public function masterSettings($company)
    {

        try {
            $company = urldecode($company);
            $owner = Auth::guard('owner')->user();

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
                    'id' => $ledger->id,
                    'name' => $ledger->ledger_name,
                    'under' => $ledger->parent,
                    'mobile' => $ledger->ledger_mobile_number,
                    'ledger_mobile_number_source' => $ledger->ledger_mobile_number_source,
                    'credit_period' => $ledger->credit_period,
                    'credit_period_source' => $ledger->credit_period_source,
                    'interest_rate' => $ledger->interest_rate,
                    'interest_rate_source' => $ledger->interest_rate_source,
                    'assigned_collector' => $ledger->assigned_collector,
                    'balance_limit' => $balanceLimit,
                    'overlimit' => $overlimit,
                    'mark' => $ledger->mark ?? 'unmarked',
                    'red_reason' => $ledger->red_reason ?? '',
                    'rows' => $this->buildLedgerRows($ledger, $vouchers),
                ];
            })->values()->all();

            // echo "<pre>"; print_r($ledgers); echo "</pre>"; exit;
            return view('owner.tally.other.master-settings', compact('ledgers', 'company'));

        } catch (\Exception $e) {
            Log::error('Tally companyLedgers failed', [
                'company' => $company,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to fetch ledgers from Tally. Please try again.');
        }
    }




    public function saveRow(Request $request, $company)
    {
        $company = urldecode($company);
        $owner = Auth::guard('owner')->user();

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:rms_tally_ledgers,id',
            'under' => 'required|string|in:Sundry Debtors,Sundry Creditors',
            'mobile' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }

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
                }
            ],
            'balance_limit' => 'nullable|numeric|min:0',
            'overlimit' => 'nullable|string',
            'mark' => 'nullable|in:red,green,unmarked',
            'red_reason' => 'nullable|string|max:255',
            'credit_period' => 'nullable|integer|min:0',
            'assigned_collector' => 'nullable|integer|exists:rms_collectors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $mark = $data['mark'] ?? 'unmarked';

        if ($data['under'] === 'Sundry Debtors' && $mark === 'red' && empty($data['red_reason'])) {
            return response()->json([
                'success' => false,
                'errors' => ['red_reason' => ['Reason is required when marked red.']],
            ], 422);
        }

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
                'errors' => ['company' => ['Company not found.']],
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
                'errors' => ['id' => ['Ledger not found for this company.']],
            ], 404);
        }

        if ($data['under'] === 'Sundry Creditors') {
            $ledger->update([
                'ledger_mobile_number' => $data['mobile'] ?? $ledger->ledger_mobile_number,
                'ledger_mobile_number_source' => !empty($data['mobile']) ? 'manual' : $ledger->ledger_mobile_number_source,
            ]);
        } else {
            $creditPeriodManuallySet = array_key_exists('credit_period', $data) && $request->filled('credit_period');

            $ledger->update([
                'ledger_mobile_number' => $data['mobile'] ?? $ledger->ledger_mobile_number,
                'ledger_mobile_number_source' => !empty($data['mobile']) ? 'manual' : $ledger->ledger_mobile_number_source,
                'balance_limit' => $data['balance_limit'] ?? null,
                'overlimit' => $data['overlimit'] ?? null,
                'mark' => $mark,
                'red_reason' => $mark === 'red' ? $data['red_reason'] : null,
                'credit_period' => $data['credit_period'] ?? $ledger->credit_period,
                'credit_period_source' => $creditPeriodManuallySet ? 'manual' : $ledger->credit_period_source,
                'assigned_collector' => $data['assigned_collector'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ledger updated successfully.',
            'data' => $ledger,
            'mobile_source' => $ledger->ledger_mobile_number_source ?? null,
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
            $owner = Auth::guard('owner')->user();

            $tallyCompany = TallyCompany::where('owner_id', $owner->id)
                ->where('company_name', $company)
                ->first();

            if (!$tallyCompany) {
                return back()->with('error', 'Company not found.');
            }

            $ledgers = TallyLedger::where('owner_id', $owner->id)
                ->where('tally_company_id', $tallyCompany->id)
                ->where('parent', 'Sundry Debtors')
                ->orderBy('ledger_name')
                ->get(['id', 'ledger_name', 'closing_balance', 'interest_rate'])
                ->map(fn($l) => [
                    'id'      => $l->id,
                    'name'    => $l->ledger_name,
                    // Tally me debtor balance negative aa sakta hai, isliye abs
                    'balance' => (int) round(abs((float) ltrim((string) ($l->closing_balance ?? '0'), '+'))),
                    'roi'     => (float) ($l->interest_rate ?? 0),
                ])
                ->values()
                ->all();

            return view(
                'owner.tally.tally.set-debtor-emi',
                compact('company', 'ledgers')
            );

        } catch (\Exception $e) {
            Log::error('Tally setDebtorEMI failed', [
                'company' => $company ?? null,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to fetch ledgers. Please try again.');
        }
    }


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
        $type = $request->query('type') ?: '';
        $allLedgers = [
            [
                'name' => 'Rahul Sharma',
                'mobile' => '9876543210',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 54321.10,
                'target' => 50000,
            ],
            [
                'name' => 'Priya Verma',
                'mobile' => '9123456780',
                'type' => 'Follow Up-Balances/Targets(months)',
                'balance' => 72500.00,
                'target' => 80000,
                'target_month' => now()->subMonths(1)->format('Y-m'),
            ],
            [
                'name' => 'Aman Gupta',
                'mobile' => '9988776655',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 18200.50,
                'age_bucket' => '31-60 Days',
                'days' => 45,
            ],
            [
                'name' => 'Sneha Patel',
                'mobile' => '9871234567',
                'type' => 'Follow Up-Balances/Days(10-20-30)',
                'balance' => 9800.00,
                'days' => 20,
                'date' => now()->subDays(20)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2204',
                'interest_due' => 150.00,
            ],
            [
                'name' => 'Vikram Singh',
                'mobile' => '9012345678',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(21)->format('Y-m-d'),
                'due_date' => now()->addDays(21)->format('Y-m-d'),
                'inv_no' => 'INV-2201',
                'days' => 12,
                'due' => 32000.00,
                'interest_due' => 480.00,
            ],
            [
                'name' => 'Anjali Mehta',
                'mobile' => '9090909090',
                'type' => 'Follow Up-Not Due',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'due_date' => now()->addDays(35)->format('Y-m-d'),
                'inv_no' => 'INV-2202',
                'days_pending' => 5,
                'not_due' => 27500.00,
            ],
            [
                'name' => 'Karan Malhotra',
                'mobile' => '9191919191',
                'type' => 'Follow Up Overlimits',
                'balance' => 12500.00,
            ],
            [
                'name' => 'Neha Joshi',
                'mobile' => '9898989898',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 61230.00,
                'target' => 55000,
            ],
            [
                'name' => 'Rohan Desai',
                'mobile' => '9797979797',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(10)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2203',
                'days' => 18,
                'due' => 41500.00,
                'interest_due' => 620.00,
            ],
            [
                'name' => 'Ishita Rao',
                'mobile' => '9696969696',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 9450.75,
                'age_bucket' => '0-30 Days',
                'days' => 15,
            ],
            [
                'name' => 'Aditya Kumar',
                'mobile' => '9595959595',
                'type' => 'Follow Up-Balances',
                'balance' => 15750.25,
            ],
        ];

        $followUps = collect($allLedgers)->values()->map(function ($l, $index) {
            $balance = $l['balance'] ?? 0;
            $target = $l['target'] ?? 0;
            $targetPct = $target > 0 ? round((abs($balance) / $target) * 100, 2) : 0;

            return [
                'id' => $index + 1,
                'name' => $l['name'] ?? '-',
                'mobile' => $l['mobile'] ?? '-',
                'under' => $l['under'] ?? '-',
                'type' => $l['type'] ?? '-',
                'balance' => $balance,
                'target' => $target,
                'target_pct' => $targetPct,
                'target_month' => $l['target_month'] ?? null,
                'age_bucket' => $l['age_bucket'] ?? null,
                'days' => $l['days'] ?? null,
                'days_pending' => $l['days_pending'] ?? null,
                'date' => $l['date'] ?? null,
                'due_date' => $l['due_date'] ?? null,
                'inv_no' => $l['inv_no'] ?? null,
                'due' => $l['due'] ?? null,
                'not_due' => $l['not_due'] ?? null,
                'interest_due' => $l['interest_due'] ?? null,
                'accountant_status' => rand(0, 1) ? 'Pending' : 'Responded',
            ];
        });

        if (!empty($type) && $type !== 'Follow Up-Quick /Smart - Follow -up') {
            $followUps = $followUps->filter(fn($row) => $row['type'] === $type)->values();
        }

        return view('owner.manual.followup-hub', [
            'followUps' => $followUps,
            'type' => $type,
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
            'balances' => 'Follow Up-Balances',
            'pct_targets' => 'Follow Up-Balances/%Targets',
            'targets_months' => 'Follow Up-Balances/Targets(months)',
            'days_agewise' => 'Follow Up-Balances/Days(agewise)',
            'days_10_20_30' => 'Follow Up-Balances/Days(10-20-30)',
            'due' => 'Follow Up-Due',
            'not_due' => 'Follow Up-Not Due',
            'overlimits' => 'Follow Up Overlimits',
        ];

        $actionLabels = [
            'allocate_tele_call' => 'Allocate to tele call',
            'call' => 'Call',
            'whatsapp' => 'WhatsApp',
            'physical_visit' => 'Physical visit',
            'escalation' => 'Escalation',
            'no_action' => 'No action',
        ];

        $history = [
            [
                'id' => 1,
                'ledger_id' => 101,
                'student_name' => 'Rahul Mehta',
                'mobile' => '9000000001',
                'type' => 'pct_targets',
                'type_label' => $typeLabels['pct_targets'],
                'balance' => 54321.10,
                'target' => 50000,
                'target_pct' => 108.64,
                'assigned_to' => 'Rahul',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01',
                'action' => 'call',
                'action_label' => $actionLabels['call'],
                'frequency' => 'weekly',
                'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th',
                'solution' => 'Payment plan agreed',
                'admin_solution' => null,
            ],
            [
                'id' => 2,
                'ledger_id' => 101,
                'student_name' => 'Rahul Mehta',
                'mobile' => '9000000001',
                'type' => 'due',
                'type_label' => $typeLabels['due'],
                'balance' => 32000,
                'target' => null,
                'target_pct' => null,
                'date' => '2026-07-20',
                'due_date' => '2026-08-10',
                'inv_no' => 'INV-2201',
                'days' => 12,
                'interest_due' => 480,
                'assigned_to' => 'Rahul',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-02',
                'action' => 'whatsapp',
                'action_label' => $actionLabels['whatsapp'],
                'frequency' => 'daily',
                'response_date' => null,
                'response' => null,
                'solution' => null,
                'admin_solution' => null,
            ],
            [
                'id' => 3,
                'ledger_id' => 101,
                'student_name' => 'Rahul Mehta',
                'mobile' => '9000000001',
                'type' => 'overlimits',
                'type_label' => $typeLabels['overlimits'],
                'balance' => 12500,
                'target' => null,
                'target_pct' => null,
                'assigned_to' => 'Priya',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-28',
                'action' => 'escalation',
                'action_label' => $actionLabels['escalation'],
                'frequency' => 'weekly',
                'response_date' => '2026-07-30',
                'response' => 'Escalated to regional manager',
                'solution' => 'Limit revised',
                'admin_solution' => null,
            ],
            [
                'id' => 4,
                'ledger_id' => 102,
                'student_name' => 'Sneha Kapoor',
                'mobile' => '9000000002',
                'type' => 'targets_months',
                'type_label' => $typeLabels['targets_months'],
                'balance' => 78900,
                'target' => 80000,
                'target_pct' => 98.63,
                'due_date' => '2026-07-31',
                'assigned_to' => 'Aman',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-15',
                'action' => 'physical_visit',
                'action_label' => $actionLabels['physical_visit'],
                'frequency' => 'monthly',
                'response_date' => '2026-07-20',
                'response' => 'Visited site, cheque collected',
                'solution' => 'Cleared 50% balance',
                'admin_solution' => null,
            ],
            [
                'id' => 5,
                'ledger_id' => 102,
                'student_name' => 'Sneha Kapoor',
                'mobile' => '9000000002',
                'type' => 'days_10_20_30',
                'type_label' => $typeLabels['days_10_20_30'],
                'balance' => 15600,
                'target' => null,
                'target_pct' => null,
                'days' => 20,
                'assigned_to' => 'Aman',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-05',
                'action' => 'no_action',
                'action_label' => $actionLabels['no_action'],
                'frequency' => 'daily',
                'response_date' => null,
                'response' => null,
                'solution' => null,
                'admin_solution' => null,
            ],
            [
                'id' => 6,
                'ledger_id' => 103,
                'student_name' => 'Amit Deshmukh',
                'mobile' => '9000000003',
                'type' => 'not_due',
                'type_label' => $typeLabels['not_due'],
                'balance' => 43200,
                'target' => null,
                'target_pct' => null,
                'date' => '2026-07-25',
                'due_date' => '2026-08-25',
                'inv_no' => 'INV-3105',
                'days_pending' => 30,
                'assigned_to' => 'Priya',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-06',
                'action' => 'allocate_tele_call',
                'action_label' => $actionLabels['allocate_tele_call'],
                'frequency' => 'weekly',
                'response_date' => '2026-08-07',
                'response' => 'Confirmed, will pay on due date',
                'solution' => null,
                'admin_solution' => null,
            ],
            [
                'id' => 7,
                'ledger_id' => 104,
                'student_name' => 'Karan Malhotra',
                'mobile' => '9000000004',
                'type' => 'days_agewise',
                'type_label' => $typeLabels['days_agewise'],
                'balance' => 99500,
                'target' => null,
                'target_pct' => null,
                'days' => 46,
                'assigned_to' => 'Rahul',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-10',
                'action' => 'escalation',
                'action_label' => $actionLabels['escalation'],
                'frequency' => 'monthly',
                'response_date' => null,
                'response' => null,
                'solution' => null,
                'admin_solution' => null,
            ],
            [
                'id' => 8,
                'ledger_id' => 105,
                'student_name' => 'Divya Nair',
                'mobile' => '9000000001',
                'type' => 'balances',
                'type_label' => $typeLabels['balances'],
                'balance' => 54321.10,
                'target' => 50000,
                'target_pct' => 108.64,
                'assigned_to' => 'Rahul',
                'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01',
                'action' => 'call',
                'action_label' => $actionLabels['call'],
                'frequency' => 'weekly',
                'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th',
                'solution' => 'Payment plan agreed',
                'admin_solution' => null,
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

}
