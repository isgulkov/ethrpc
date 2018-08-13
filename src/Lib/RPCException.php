<?php

namespace IsGulkov\EthRPC\Lib;

// TODO: move up from Lib -- the user will be catching these

class RPCException extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": ".(($this->code > 0)?"[{$this->code}]:":"")." {$this->message}\n";
    }
}

class HTTPTimeoutException extends RPCException
{
    private $msDuration;

    public function __construct(?int $msDuration = null)
    {
        if($msDuration !== null) {
            parent::__construct("Request has timed out at " . $msDuration . " ms");
        }
        else {
            parent::__construct("Request has timed out");
        }

        $this->msDuration = $msDuration;
    }

    public function getDuration() : ?int
    {
        return $this->msDuration;
    }
}
