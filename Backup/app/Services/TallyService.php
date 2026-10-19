<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use DOMDocument;
use SimpleXMLElement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\TallyConnection;
use Illuminate\Support\Facades\Auth;

class TallyService
{
    private function getTallyUrl(): string
    {
        $owner = Auth::guard('owner')->user();

        if (!$owner) {
            throw new Exception('Owner not authenticated.');
        }

        $connection = TallyConnection::where('owner_id', $owner->id)
            ->where('status', 'connected')
            ->first();

        if (!$connection) {
            throw new Exception('Tally connection not configured.');
        }

        return "http://{$connection->tailscale_ip}:{$connection->port}";
    }


    public function request(string $xml): string
    {
        Log::info('Tally Request XML', ['xml' => $xml]);

        try {
            $response = Http::timeout(120)
                ->withHeaders(['Content-Type' => 'text/xml'])
                ->withBody($xml, 'text/xml')
                ->post($this->getTallyUrl());

            Log::info('Tally Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->cleanXml($response->body());

        } catch (\Throwable $e) {
            Log::error('Tally Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    
    public function getCompanies(): array
    {
        $xml = <<<XML
            <ENVELOPE>
                <HEADER>
                    <VERSION>1</VERSION>
                    <TALLYREQUEST>EXPORT</TALLYREQUEST>
                    <TYPE>COLLECTION</TYPE>
                    <ID>List of Companies</ID>
                </HEADER>
                <BODY>
                    <DESC>
                        <STATICVARIABLES>
                            <SVEXPORTFORMAT>XML</SVEXPORTFORMAT>
                        </STATICVARIABLES>
                    </DESC>
                </BODY>
            </ENVELOPE>
        XML;

        $response = $this->request($xml);
        $xmlObj = $this->loadXml($response);

        $companies = [];
        $nodes = $xmlObj->xpath("//*[local-name()='COMPANY']");

        if ($nodes) {
            foreach ($nodes as $company) {
                $name = '';

                if (isset($company['NAME'])) {
                    $name = (string) $company['NAME'];
                } elseif (isset($company->NAME)) {
                    $name = (string) $company->NAME;
                }

                if ($name !== '') {
                    $companies[] = ['name' => $name];
                }
            }
        }

        return $companies;
    }


    
    public function getLedgers(string $company)
    {
        $company = $this->escapeXml($company);

        $xml = <<<XML
                <ENVELOPE>
                    <HEADER>
                        <VERSION>1</VERSION>
                        <TALLYREQUEST>EXPORT</TALLYREQUEST>
                        <TYPE>COLLECTION</TYPE>
                        <ID>Ledger Collection</ID>
                    </HEADER>
                    <BODY>
                        <DESC>
                            <STATICVARIABLES>
                                <SVCURRENTCOMPANY>{$company}</SVCURRENTCOMPANY>
                                <SVEXPORTFORMAT>XML</SVEXPORTFORMAT>
                            </STATICVARIABLES>
                            <TDL>
                                <TDLMESSAGE>
                                    <COLLECTION NAME="Ledger Collection">
                                        <TYPE>Ledger</TYPE>
                                        <FETCH>MASTERID, NAME,PARENT,EMAIL,LEDGERMOBILE,OPENINGBALANCE,CLOSINGBALANCE,CREDITPERIOD,BILLCREDITPERIOD,ISBILLWISEON,ISINTERESTON,INTERESTCOLLECTION.LIST</FETCH>
                                    </COLLECTION>
                                </TDLMESSAGE>
                            </TDL>
                        </DESC>
                    </BODY>
                </ENVELOPE>
            XML;

        return $this->request($xml);
    }



    private function childValue(SimpleXMLElement $node, string $tag): string
    {
        $matches = $node->xpath("./*[local-name()='{$tag}']");

        if ($matches && isset($matches[0])) {
            return trim((string) $matches[0]);
        }

        // fallback: attribute ki tarah bhi ho sakta hai
        if (isset($node[$tag])) {
            return trim((string) $node[$tag]);
        }

        return '';
    }


    private function extractInterestParameters(SimpleXMLElement $ledgerNode): array
    {
        $rate  = null;
        $style = null;

        $collectionNodes = $ledgerNode->xpath("./*[local-name()='INTERESTCOLLECTION.LIST']");

        if ($collectionNodes) {
            foreach ($collectionNodes as $collectionNode) {
                $rateValue = $this->childValue($collectionNode, 'INTERESTRATE');
                $styleValue = $this->childValue($collectionNode, 'INTERESTSTYLE');

                if ($rateValue !== '') {
                    $rate = $rateValue;
                }

                if ($styleValue !== '') {
                    $style = $styleValue;
                }

                if ($rate !== null && $style !== null) {
                    break;
                }
            }
        }

        return ['rate' => $rate, 'style' => $style];
    }




    private function yesNo(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return strtolower($value) === 'yes' ? 'Yes' : 'No';
    }



    public function parseLedgersXml(string $xml): array
    {
        $xmlObj = $this->loadXml($xml);

        $ledgers = [];
        $nodes = $xmlObj->xpath("//*[local-name()='LEDGER']");

        foreach ($nodes as $node) {
            $name = isset($node['NAME']) ? (string) $node['NAME'] : $this->childValue($node, 'NAME');

            if ($name === '') {
                continue;
            }

            Log::debug('Ledger node raw XML', ['ledger' => $name, 'xml' => $node->asXML()]);

            $creditPeriod = $this->childValue($node, 'CREDITPERIOD')
                ?: $this->childValue($node, 'BILLCREDITPERIOD');

            $activateInterest = $this->yesNo($this->childValue($node, 'ISINTERESTON'));

            $interestRate  = null;
            $interestStyle = null;

            if ($activateInterest === 'Yes') {
                $interestParams = $this->extractInterestParameters($node);
                $interestRate   = $interestParams['rate'];
                $interestStyle  = $interestParams['style'];
            }

            $ledgers[] = [
                'ledger_name'                   => $name,
                'master_id'                     => $this->childValue($node, 'MASTERID') ?: null,
                'ledger_email'                  => $this->childValue($node, 'EMAIL') ?: null,
                'ledger_mobile_number'          => $this->childValue($node, 'LEDGERMOBILE') ?: null,
                'parent'                        => $this->childValue($node, 'PARENT') ?: null,
                'opening_balance'               => $this->toDecimal($this->childValue($node, 'OPENINGBALANCE')),
                'closing_balance'               => $this->toDecimal($this->childValue($node, 'CLOSINGBALANCE')),
                'credit_period'                 => $creditPeriod ?: null,
                'interest_rate'                 => $interestRate,
                'interest_style'                => $interestStyle,
                'maintain_bill_by_bill'         => $this->yesNo($this->childValue($node, 'ISBILLWISEON')),
                'activate_interest_calculation' => $activateInterest,
            ];
        }

        return $ledgers;
    }


    private function extractInterestRate(SimpleXMLElement $ledgerNode): ?string
    {
        // Case 1: direct tag on ledger
        $rate = $this->childValue($ledgerNode, 'RATEOFINTEREST');

        if ($rate !== '') {
            return $rate;
        }

        // Case 2: nested inside INTERESTCALCPARAMETERS.LIST (Tally often nests it here)
        $paramNodes = $ledgerNode->xpath("./*[local-name()='INTERESTCALCPARAMETERS.LIST']")
            ?: $ledgerNode->xpath("./*[local-name()='ADVANCEINTERESTPARAM.LIST']");

        if ($paramNodes) {
            foreach ($paramNodes as $paramNode) {
                $rate = $this->childValue($paramNode, 'RATEOFINTEREST')
                    ?: $this->childValue($paramNode, 'RATE');

                if ($rate !== '') {
                    return $rate;
                }
            }
        }

        return null;
    }



    public function getVouchersForCompany(string $company)
    {
        $company = $this->escapeXml($company);

        $xml = <<<XML
            <ENVELOPE>
                <HEADER>
                    <VERSION>1</VERSION>
                    <TALLYREQUEST>EXPORT</TALLYREQUEST>
                    <TYPE>COLLECTION</TYPE>
                    <ID>Company Vouchers</ID>
                </HEADER>
                <BODY>
                    <DESC>
                        <STATICVARIABLES>
                            <SVCURRENTCOMPANY>{$company}</SVCURRENTCOMPANY>
                            <SVEXPORTFORMAT>XML</SVEXPORTFORMAT>
                        </STATICVARIABLES>
                        <TDL>
                            <TDLMESSAGE>
                                <COLLECTION NAME="Company Vouchers">
                                    <TYPE>Voucher</TYPE>
                                    <FETCH>
                                        MASTERID,
                                        DATE,
                                        VOUCHERNUMBER,
                                        VOUCHERTYPENAME,
                                        PARTYLEDGERNAME,
                                        ALLLEDGERENTRIES.LIST.LEDGERNAME,
                                        ALLLEDGERENTRIES.LIST.AMOUNT,
                                        ALLLEDGERENTRIES.LIST.ISDEEMEDPOSITIVE,
                                        ALLLEDGERENTRIES.LIST.BILLALLOCATIONS.LIST.NAME,
                                        ALLLEDGERENTRIES.LIST.BILLALLOCATIONS.LIST.BILLTYPE,
                                        ALLLEDGERENTRIES.LIST.BILLALLOCATIONS.LIST.AMOUNT,
                                        ALLLEDGERENTRIES.LIST.BILLALLOCATIONS.LIST.BILLCREDITPERIOD,
                                        ALLLEDGERENTRIES.LIST.BILLALLOCATIONS.LIST.CREDITDAYS,
                                        ALLLEDGERENTRIES.LIST.BILLALLOCATIONS.LIST.DUEDATEOFBILL,
                                        ALLLEDGERENTRIES.LIST.BILLALLOCATIONS.LIST.DUEDATE
                                    </FETCH>
                                </COLLECTION>
                            </TDLMESSAGE>
                        </TDL>
                    </DESC>
                </BODY>
            </ENVELOPE>
            XML;

        return $this->request($xml);
    }


    public function parseVouchersXml(string $xml): array
    {
        $xmlObj = $this->loadXml($xml);

        $vouchers = [];
        $nodes = $xmlObj->xpath("//*[local-name()='VOUCHER']");

        foreach ($nodes as $node) {
            $dateRaw = $this->childValue($node, 'DATE');
            $parsedDate = $this->parseTallyDate($dateRaw);
            $ledgerMatchName = $this->childValue($node, 'PARTYLEDGERNAME') ?: null;
            $entries = $this->extractEntries($node, 'ALLLEDGERENTRIES.LIST');

            $vouchers[] = [
                'master_id'         => $this->childValue($node, 'MASTERID') ?: null,
                'date'              => $parsedDate,
                'voucher_number'    => $this->childValue($node, 'VOUCHERNUMBER') ?: null,
                'voucher_type'      => $this->childValue($node, 'VOUCHERTYPENAME') ?: null,
                'ledger_match_name' => $ledgerMatchName,
                'party_ledger_name' => $this->resolveParticulars($entries, $ledgerMatchName),
                'amount'            => $this->resolveVoucherAmount($entries, $ledgerMatchName),
                'credit_period'     => $this->extractBillCreditPeriod($node, $ledgerMatchName, $parsedDate),
            ];
        }

        return $vouchers;
    }


    private function resolveParticulars(array $entries, ?string $partyLedgerName): ?string
    {
        if (!$entries) {
            return null;
        }

        $counterNames = [];

        foreach ($entries as $entry) {
            if ($partyLedgerName && strcasecmp($entry['ledger_name'], $partyLedgerName) === 0) {
                continue;
            }

            $counterNames[] = $entry['ledger_name'];
        }

        $counterNames = array_values(array_unique($counterNames));

        if (!$counterNames) {
            // No entry differed from the party ledger (single-line/self
            // voucher) - fall back to whatever ledger names exist.
            $counterNames = array_values(array_unique(array_map(
                fn ($e) => $e['ledger_name'],
                $entries
            )));
        }

        return $counterNames ? implode(', ', $counterNames) : null;
    }


    private function extractEntries(SimpleXMLElement $voucherNode, string $listTag): array
    {
        $entries = [];
        $listNodes = $voucherNode->xpath("./*[local-name()='{$listTag}']");

        foreach ($listNodes as $entryNode) {
            $ledgerName = $this->childValue($entryNode, 'LEDGERNAME');

            if ($ledgerName === '') {
                continue;
            }

            $entries[] = [
                'ledger_name' => $ledgerName,
                'amount'      => $this->toDecimal($this->childValue($entryNode, 'AMOUNT')),
            ];
        }

        return $entries;
    }


    private function extractBillCreditPeriod(SimpleXMLElement $voucherNode, ?string $partyLedgerName, ?string $voucherDate): ?string
    {
        $ledgerEntryNodes = $voucherNode->xpath("./*[local-name()='ALLLEDGERENTRIES.LIST']");

        foreach ($ledgerEntryNodes as $entryNode) {
            $ledgerName = $this->childValue($entryNode, 'LEDGERNAME');

            if ($partyLedgerName && strcasecmp($ledgerName, $partyLedgerName) !== 0) {
                continue;
            }

            $billNodes = $entryNode->xpath("./*[local-name()='BILLALLOCATIONS.LIST']");

            foreach ($billNodes as $billNode) {
                Log::debug('Bill node raw XML', ['xml' => $billNode->asXML()]);

                // Try every known variant Tally might send
                $creditPeriod = $this->childValue($billNode, 'BILLCREDITPERIOD')
                    ?: $this->childValue($billNode, 'BILLCREDITPERIODDAYS')
                    ?: $this->childValue($billNode, 'CREDITPERIOD')   // bill-level override, same name as ledger tag
                    ?: $this->childValue($billNode, 'CREDITDAYS')
                    ?: $this->childValue($billNode, 'NUMDAYS');

                if ($creditPeriod !== '') {
                    return $creditPeriod;
                }

                // Fallback 1: explicit due date on the bill
                $dueDateRaw = $this->childValue($billNode, 'DUEDATEOFBILL')
                    ?: $this->childValue($billNode, 'DUEDATE');

                if ($dueDateRaw !== '' && $voucherDate) {
                    try {
                        $due   = Carbon::createFromFormat('Ymd', trim($dueDateRaw));
                        $vDate = Carbon::createFromFormat('Y-m-d', $voucherDate);
                        return (string) $vDate->diffInDays($due);
                    } catch (\Throwable $e) {
                        // ignore, try next fallback
                    }
                }
            }
        }

        return null; // caller falls back to ledger's default credit_period
    }


    private function resolveVoucherAmount(array $entries, ?string $partyLedgerName): float
    {
        if (!$entries) {
            return 0.0;
        }

        if ($partyLedgerName) {
            foreach ($entries as $entry) {
                if (strcasecmp($entry['ledger_name'], $partyLedgerName) === 0) {
                    return abs($entry['amount']);
                }
            }
        }

        // Fallback: largest absolute amount among the entries.
        $amounts = array_map(fn ($e) => abs($e['amount']), $entries);

        return $amounts ? max($amounts) : 0.0;
    }


    private function toDecimal(?string $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) str_replace([',', ' '], '', $value);
    }

    private function toBool(?string $value): bool
    {
        return strtolower(trim((string) $value)) === 'yes';
    }

    private function parseTallyDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            // Tally exports voucher DATE as YYYYMMDD
            return Carbon::createFromFormat('Ymd', $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }


    private function loadXml(string $xml): SimpleXMLElement
    {
        $xml = $this->cleanXml($xml);

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new DOMDocument();
        $dom->recover = true;

        $loaded = @$dom->loadXML($xml, LIBXML_PARSEHUGE | LIBXML_NOWARNING | LIBXML_NOERROR);

        $errors = libxml_get_errors();
        libxml_clear_errors();

        if (!$loaded || $dom->documentElement === null) {
            $message = $errors[0]->message ?? 'Unknown XML parse error';
            Log::error('Tally XML unrecoverable', ['message' => trim($message)]);
            throw new Exception('Invalid XML received from Tally: ' . trim($message));
        }

        if ($errors) {
            // Recovered from these - log for visibility but don't fail the sync.
            Log::warning('Tally XML recovered with errors', [
                'errors' => array_map(fn ($e) => trim($e->message), $errors),
            ]);
        }

        $simplexml = simplexml_import_dom($dom);

        if (!$simplexml) {
            throw new Exception('Invalid XML received from Tally (SimpleXML conversion failed).');
        }

        return $simplexml;
    }

    protected function cleanXml(string $xml): string
    {
        $xml = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $xml);

        $xml = preg_replace(
            '/<(\/?)[A-Za-z_][\w.\-]*:([A-Za-z_][\w.\-]*)/',
            '<$1$2',
            $xml
        );

        $xml = preg_replace_callback(
            '/&#x([0-9A-Fa-f]+);|&#([0-9]+);/',
            function (array $m): string {
                $code = ($m[2] ?? '') !== '' ? (int) $m[2] : hexdec($m[1]);

                $isValid = $code === 0x9 || $code === 0xA || $code === 0xD
                    || ($code >= 0x20 && $code <= 0xD7FF)
                    || ($code >= 0xE000 && $code <= 0xFFFD)
                    || ($code >= 0x10000 && $code <= 0x10FFFF);

                return $isValid ? $m[0] : '';
            },
            $xml
        );

        return $xml;
    }


    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    
    public function getVoucherTypes(string $company)
    {
        $xml = <<<XML
            <ENVELOPE>
                <HEADER>
                    <VERSION>1</VERSION>
                    <TALLYREQUEST>EXPORT</TALLYREQUEST>
                    <TYPE>COLLECTION</TYPE>
                    <ID>Voucher Type Collection</ID>
                </HEADER>

                <BODY>
                    <DESC>
                        <STATICVARIABLES>
                            <SVCURRENTCOMPANY>{$company}</SVCURRENTCOMPANY>
                            <SVEXPORTFORMAT>\$\$SysName:XML</SVEXPORTFORMAT>
                        </STATICVARIABLES>

                        <TDL>
                            <TDLMESSAGE>

                                <COLLECTION NAME="Voucher Type Collection">
                                    <TYPE>Voucher Type</TYPE>
                                    <FETCH>Name</FETCH>
                                </COLLECTION>

                            </TDLMESSAGE>
                        </TDL>

                    </DESC>
                </BODY>
            </ENVELOPE>
            XML;

        return $this->request($xml);
    }
}