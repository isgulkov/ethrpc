<?php

namespace IsGulkov\EthRPC\Commands;

use Illuminate\Console\Command;

use IsGulkov\EthRPC\Facade\EthRPC;
use IsGulkov\EthRPC\Lib\HTTPTimeoutException;


class EthRpcNodeDiag extends Command
{
    protected $signature = 'ethRpc:nodeDiag';

    protected $description = "Display some diagnostic information about the Ethreum node obtainable through JSON RPC";

    protected $ethRPC;

    public function __construct(EthRPC $ethRPC)
    {
        parent::__construct();

        $this->eth = $ethRPC;
    }

    private const ESC_BOLD = "\e[1m";
    private const ESC_GRN_BOLD = "\e[32;1m";
    private const ESC_RED_BOLD = "\e[31;1m";
    private const ESC_GRN = "\e[32m";
    private const ESC_RED = "\e[31m";
    private const ESC_RESET = "\e[0m";

    private static function bold($s)
    {
        return self::ESC_BOLD . $s . self::ESC_RESET;
    }

    private static function grnBold($s)
    {
        return self::ESC_GRN_BOLD . $s . self::ESC_RESET;
    }

    private static function redBold($s)
    {
        return self::ESC_RED_BOLD . $s . self::ESC_RESET;
    }

    private static function grnRegular($s)
    {
        return self::ESC_GRN . $s . self::ESC_RESET;
    }

    private static function redRegular($s)
    {
        return self::ESC_RED . $s . self::ESC_RESET;
    }

    private static function hexDec0x($xHex)
    {
        return hexdec(substr($xHex, 2));
    }

    private static function randomAddress()
    {
        $address = '0x';

        for($i = 0; $i < 40; $i++) {
            $address .= dechex(rand(0, 15));
        }

        return $address;
    }

    private function printException($typeName, $message=null)
    {
        $messageLines = explode("\n", wordwrap($message));

        $fitsInline = count($messageLines) == 1 && strlen($messageLines[0]) <= 80 - strlen($typeName) - 5;

        $messageLines = array_map(function($line) {
            return self::redRegular($line);
        }, $messageLines);

        $this->line(
            "    " . self::redBold($typeName) .
            (
                is_string($message)
                    ? ":" . (
                        $fitsInline
                            ? " " . $messageLines[0]
                            : ""
                    )
                    : ".")
        );

        if(!$fitsInline) {
            foreach($messageLines as $line) {
                $this->line("    " . $line);
            }
        }
    }

    private function printSyncing()
    {
        $syncing = $this->eth::eth_syncing();

        if($syncing === false) {
            $this->line(
                "Currently " . self::grnBold("not syncing") . ", ".
                "at block " . self::grnBold(self::hexDec0x($this->eth::eth_blockNumber())) . "."
            );
        }
        else {
            $startingBlock = self::hexDec0x($syncing->startingBlock);
            $currentBlock = self::hexDec0x($syncing->currentBlock);
            $highestBlock = self::hexDec0x($syncing->highestBlock);

            $this->line(
                "Currently " . self::redBold("syncing") . ", " .
                "at block " . self::bold(fmtDecimal($currentBlock)) . " " .
                "of " . self::bold(fmtDecimal($highestBlock)) . ", " .
                "started at " . self::bold(fmtDecimal($startingBlock)) . ":"
            );

            $this->line(
                "                   " .
                "synced " . self::bold(fmtDecimal($currentBlock - $startingBlock)) . " blocks, " .
                self::bold(fmtDecimal($highestBlock - $currentBlock)) . " blocks to go."
            );
        }
    }

    private function sprintFloatBalance($balance)
    {
        $isImprecise = log($balance, 2) > 52;

        $decimalBalance = self::grnBold(fmtDecimal($balance, 0));

        if($isImprecise) {
            $decimalBalance = self::grnRegular("~") . $decimalBalance;
        }

        return $decimalBalance;
    }

    private function printBalance($address)
    {
        try {
            $this->line("Getting balance of " . self::bold($address) . ":");

            $balance = self::hexDec0x($this->eth::eth_getBalance($address));

            $this->line(
                "    " . $this->sprintFloatBalance($balance) . " " . self::grnRegular("wei")
            );
        }
        catch(HTTPTimeoutException $e) {
            $this->printException("HTTP timeout", $e->getDuration() !== null ? $e->getDuration() . " ms" : "");
        }
        catch(\Exception $e) {
            $this->printException(get_class($e), $e->getMessage());
        }
    }

    private function printContractData($contractAddress)
    {
        try {
            $this->line("Getting name of ERC20 at " . self::bold($contractAddress) . ":");

            $tokenNameBin = $this->eth::eth_call([
                'to' => $contractAddress,
                'data' => '0x06fdde0383f15d582d1a74511486c9ddf862a882fb7904b3d9fe9b8b8e58a796'
            ]);

            $tokenNameBin = substr($tokenNameBin, 2);

            $stringOffset = hexdec(substr($tokenNameBin, 0  + 48, 64 - 48));
            $stringLength = hexdec(substr($tokenNameBin, 64 + 48, 64 - 48));

            $tokenNameBin = substr($tokenNameBin, 64 + 2 * $stringOffset, 2 * $stringLength);

            $tokenNameBin = explode(
                ',',
                chunk_split(
                    $tokenNameBin,
                    2,
                    ','
                )
            );

            while(!empty($tokenNameBin) && empty(array_last($tokenNameBin))) {
                array_pop($tokenNameBin);
            }

            $tokenName = "";

            foreach($tokenNameBin as $hexOrd) {
                $tokenName .= chr(hexdec($hexOrd));
            }

            $this->line("    the call returned: \"" . self::grnBold($tokenName) . "\".");
        }
        catch(HTTPTimeoutException $e) {
            $this->printException("HTTP timeout", $e->getDuration() !== null ? $e->getDuration() . " ms" : "");
        }
        catch(\Exception $e) {
            $this->printException(get_class($e), $e->getMessage());
        }
    }

    private function printOwnAccounts()
    {
        $ownAccounts = $this->eth::eth_accounts();

        if(empty($ownAccounts)) {
            return false;
        }

        $this->line(
            "Currently own " . self::grnBold(count($ownAccounts)) . " " .
            "account" . (count($ownAccounts) > 1 ? "s" : "") . ":"
        );

        foreach(array_slice($ownAccounts, 0, 10) as $account) {
            $balance = self::getBoldBalanceOrNull($account);

            $listItem =
                "  * " . self::bold($account) . " " .
                "(" . ($balance !== null ? $balance : self::redBold("???")) . " ETH);";

            $this->line($listItem);

            try {
                $this->eth::personal_unlockAccount($account, "hui", null);

                $this->line(
                    str_repeat(
                        ' ',
                        strlen($listItem) - 8 - 11 - 8 - ($balance !== null ? 9 : 0) - 1
                    ) . self::grnBold("unlocked"));
            }
            catch(\IsGulkov\EthRPC\Lib\RPCException $e) {
                $this->printException("Couldn't unlock", $e->getMessage());
            }
        }

        if(count($ownAccounts) > 10) {
            $this->line("  * ... (" . (count($ownAccounts) - 10) . " skipped)");
        }

        return true;
    }

    private function getBoldBalanceOrNull($address)
    {
        try {
            $balance = self::hexDec0x($this->eth::eth_getBalance($address));

            return $this->sprintFloatBalance($balance) . " " . self::grnRegular("wei");
        }
        catch(\Exception $e) {
            return null;
        }
    }

    private function printSignTransaction()
    {
        $ownAccounts = $this->eth::eth_accounts();

        if(empty($ownAccounts)) {
            $this->line("Have no accounts, nothing to sign stuff with.");
            return;
        }

        $ownAccount = $ownAccounts[0];

        try {
            $this->line("Will sign a transaction from " . self::bold($ownAccount) . ":");

            $rawTx = $this->eth::eth_signTransaction([
                'from' => $ownAccount,
                'to' => "0x" . str_repeat('0', 40),
                'value' => '0x0'
            ])->raw;

            $rawTxLines = array_map(
                function($line) {
                    return "    " . self::grnBold($line);
                },
                explode(
                    "\r\n",
                    chunk_split($rawTx, 72)
                )
            );

            foreach($rawTxLines as $line) {
                $this->line($line);
            }
        }
        catch(HTTPTimeoutException $e) {
            $this->printException("HTTP timeout", $e->getDuration() !== null ? $e->getDuration() . " ms" : "");
        }
    }

    public function handle()
    {
        $eth = $this->eth;

        if($eth::getTimeout() === 0) {
            $this->line("Connection timeout is " . self::redBold("disabled") . ".");
        }
        else {
            $this->line("Connection timeout is " . self::grnBold($eth::getTimeout()) . "s.");
        }

        try {
            $clientVersion = $eth::web3_clientVersion();

            $this->line("Node " . self::grnBold("RESPONSIVE") . ", " . $clientVersion);
        }
        catch(HTTPTimeoutException $e) {
            $this->line("Node " . self::redBold("UNRESPONSIVE") . ". Check connection settings");
            return;
        }

        $this->line(
            "Running "  . self::grnBold($eth::parity_nodeKind()->capability) . " node " .
            "on chain " . self::grnBold($eth::parity_chain()) . ", " .
            "peers: "   . self::grnBold($eth::parity_netPeers()->connected) . "."
        );

        $this->printSyncing();

        $this->printBalance('0x81b7e08f65bdf5648606c89998a9cc8164397647');
        $this->printBalance(self::randomAddress());

        // [{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"}],"name":"approve","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"payable":false,"stateMutability":"nonpayable","type":"fallback"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_from","type":"address"},{"indexed":true,"name":"_to","type":"address"},{"indexed":false,"name":"_value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_owner","type":"address"},{"indexed":true,"name":"_spender","type":"address"},{"indexed":false,"name":"_value","type":"uint256"}],"name":"Approval","type":"event"}]
        $this->printContractData('0xe1623dfc79fe86fb966f5784e4196406e02469fc');
        $this->printContractData(self::randomAddress());

        if(!$this->printOwnAccounts()) {
            $this->line("Currently own " . self::redBold('0') . " accounts.");

            if($this->confirm("   Try to create one?")) {
                $this->line("    New account: " . self::grnBold($eth::personal_newAccount("hui")) . ".");

                $this->printOwnAccounts();
            }
        }

        $this->printSignTransaction();

        // TODO: check anything else that will be used in the app

        return;
    }
}
