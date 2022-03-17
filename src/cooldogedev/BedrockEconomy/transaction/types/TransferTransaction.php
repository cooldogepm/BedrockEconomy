<?php

/**
 *  Copyright (c) 2022 cooldogedev
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\transaction\types;

use cooldogedev\BedrockEconomy\transaction\Transaction;

final class TransferTransaction extends Transaction
{
    public function __construct(
        protected string $sender,
        protected string $receiver,
        protected int $amount,
        ?int $id = null,
        ?int $issueDate = null,
        ?int $balanceCap = null
    )
    {
        parent::__construct(Transaction::TRANSACTION_TYPE_TRANSFER, $id, $issueDate, $balanceCap);
    }

    public static function jsonDeserialize(string $serialized): Transaction
    {
        $data = json_decode($serialized, true);

        return new TransferTransaction(
            $data["sender"],
            $data["receiver"],
            $data["amount"],

            $data["id"],
            $data["issueDate"],
            $data["balanceCap"]
        );
    }

    public function setSender(string $sender): void
    {
        $this->sender = $sender;
    }

    public function setReceiver(string $receiver): void
    {
        $this->receiver = $receiver;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getReceiver(): string
    {
        return $this->receiver;
    }

    public function jsonSerialize(): string
    {
        return json_encode(
            [
                "id" => $this->id,
                "issueDate" => $this->issueDate,
                "type" => $this->type,
                "balanceCap" => $this->balanceCap,

                "sender" => $this->sender,
                "receiver" => $this->receiver,
                "amount" => $this->amount
            ]
        );
    }
}
