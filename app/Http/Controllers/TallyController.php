<?php

namespace App\Http\Controllers;

use App\Services\TallyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TallyController extends Controller
{
    protected TallyService $tally;

    public function __construct(TallyService $tally)
    {
        $this->tally = $tally;
    }

    public function index()
    {
        return view('tally.dashboard');
    }

    /**
     * Companies
     */
    public function companies()
    {
        try {

            $xml = $this->tally->getCompanies();

            $xmlObj = simplexml_load_string($xml);

            $companies = [];

            if ($xmlObj) {

                // XML structure ke hisab se adjust karna padega
                $nodes = $xmlObj->xpath("//*[local-name()='COMPANY']");

                if ($nodes) {

                    foreach ($nodes as $company) {

                        $companies[] = [
                            'name' => (string) ($company['NAME'] ?? $company->NAME)
                        ];
                    }
                }
            }

            return view(
                'owner.tally.company',
                compact('companies', 'xml')
            );

        } catch (\Exception $e) {

            return view('owner.tally.company', [
                'companies' => [],
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Ledgers
     */
    public function ledgers(Request $request)
    {
        try {

            $company = $request->company
                ?? config('tally.default_company');

            $xml = $this->tally->getLedgers($company);

            $data = @simplexml_load_string($xml);

            $ledgers = [];

            if ($data) {

                $ledgers = $data->xpath(
                    "//*[local-name()='LEDGER']"
                ) ?? [];
            }

            return view(
                'tally.ledgers',
                compact(
                    'ledgers',
                    'company',
                    'xml'
                )
            );

        } catch (\Throwable $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

     
     
}