<?php

namespace Jcsofts\LaravelEthereum\Lib;

class Ethereum extends JsonRPC
{
    public function ether_request($method, $params=array())
    {
        try
        {
            $ret = $this->request($method, $params);
            return $ret->result;
        }
        catch(RPCException $e)
        {
            throw $e;
        }
    }

    private function decode_hex($input)
    {
        if(substr($input, 0, 2) == '0x')
            $input = substr($input, 2);

        if(preg_match('/[a-f0-9]+/', $input))
            return hexdec($input);

        return $input;
    }

    function __call($name, $arguments)
    {
        // Nigga, what?

        if(count($arguments) !== 0) {
            return $this->ether_request($name, $arguments);
        }
        else {
            return $this->ether_request($name);
        }
    }

    function eth_blockNumber($decode_hex=FALSE)
    {
        $block = $this->ether_request(__FUNCTION__);

        if($decode_hex)
            $block = $this->decode_hex($block);

        return $block;
    }

    function eth_getBalance($address, $block='latest', $decode_hex=FALSE)
    {
        $balance = $this->ether_request(__FUNCTION__, array($address, $block));

        if($decode_hex)
            $balance = $this->decode_hex($balance);

        return $balance;
    }

    function eth_getStorageAt($address, $at, $block='latest')
    {
        return $this->ether_request(__FUNCTION__, array($address, $at, $block));
    }

    function eth_getTransactionCount($address, $block='latest', $decode_hex=FALSE)
    {
        $count = $this->ether_request(__FUNCTION__, array($address, $block));

        if($decode_hex)
            $count = $this->decode_hex($count);

        return $count;
    }

    function eth_getBlockTransactionCountByNumber($tx='latest')
    {
        return $this->ether_request(__FUNCTION__, array($tx));
    }

    function eth_getUncleCountByBlockNumber($block='latest')
    {
        return $this->ether_request(__FUNCTION__, array($block));
    }

    function eth_getCode($address, $block='latest')
    {
        return $this->ether_request(__FUNCTION__, array($address, $block));
    }

    function eth_sendTransaction($transaction)
    {
        if(!is_a($transaction, EthereumTransaction::class))
        {
            throw new ErrorException('Transaction object expected');
        }
        else
        {
            return $this->ether_request(__FUNCTION__, $transaction->toArray());
        }
    }

    function eth_call($message, $block)
    {
        if(!is_a($message, EthereumMessage::class))
        {
            throw new ErrorException('Message object expected');
        }
        else
        {
            return $this->ether_request(__FUNCTION__, $message->toArray());
        }
    }

    function eth_estimateGas($message, $block)
    {
        if(!is_a($message, EthereumMessage::class))
        {
            throw new ErrorException('Message object expected');
        }
        else
        {
            return $this->ether_request(__FUNCTION__, $message->toArray());
        }
    }

    function eth_getBlockByHash($hash, $full_tx=TRUE)
    {
        return $this->ether_request(__FUNCTION__, array($hash, $full_tx));
    }

    function eth_getBlockByNumber($block='latest', $full_tx=TRUE)
    {
        return $this->ether_request(__FUNCTION__, array($block, $full_tx));
    }

    function eth_newFilter($filter, $decode_hex=FALSE)
    {
        if(!is_a($filter, EthereumFilter::class))
        {
            throw new ErrorException('Expected a Filter object');
        }
        else
        {
            $id = $this->ether_request(__FUNCTION__, $filter->toArray());

            if($decode_hex)
                $id = $this->decode_hex($id);

            return $id;
        }
    }

    function eth_newBlockFilter($decode_hex=FALSE)
    {
        $id = $this->ether_request(__FUNCTION__);

        if($decode_hex)
            $id = $this->decode_hex($id);

        return $id;
    }

    function eth_newPendingTransactionFilter($decode_hex=FALSE)
    {
        $id = $this->ether_request(__FUNCTION__);

        if($decode_hex)
            $id = $this->decode_hex($id);

        return $id;
    }

    function eth_getLogs($filter)
    {
        if(!is_a($filter, EthereumFilter::class))
        {
            throw new ErrorException('Expected a Filter object');
        }
        else
        {
            return $this->ether_request(__FUNCTION__, $filter->toArray());
        }
    }

    function shh_post($post)
    {
        if(!is_a($post, WhisperPost::class))
        {
            throw new \ErrorException('Expected a Whisper post');
        }
        else
        {
            return $this->ether_request(__FUNCTION__, $post->toArray());
        }
    }

    function shh_newFilter($to=NULL, $topics=array())
    {
        return $this->ether_request(__FUNCTION__, array(array('to'=>$to, 'topics'=>$topics)));
    }

    function personal_unlockAccount($address,$passphrase,$duration=300){
        return $this->ether_request(__FUNCTION__, array($address,$passphrase,$duration));
    }

    function personal_sendTransaction(EthereumTransaction $transaction,$passphrase){
        $params=$transaction->toArray();
        array_push($params,$passphrase);
        return $this->ether_request(__FUNCTION__, $params);
    }
}
