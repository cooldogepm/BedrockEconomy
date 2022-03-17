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

final class UpdateTransaction extends Transaction
{
    public function __construct(
        int $type,
        protected ?string $issuer,
        protected string $target,
        protected int $value,

        ?int $id = null,
        ?int $issueDate = null,
        ?int $balanceCap = null,
    )
    {
        parent::__construct($type, $id, $issueDate, $balanceCap);
    }

    public static function jsonDeserialize(string $serialized): UpdateTransaction
    {
        $data = json_decode($serialized, true);

        return new UpdateTransaction(
            $data["type"],
            $data["sender"],
            $data["target"],
            $data["value"],

            $data["id"],
            $data["issueDate"],
            $data["balanceCap"]
        );
    }

    public function setIssuer(?string $issuer): void
    {
        $this->issuer = $issuer;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function jsonSerialize(): string
    {
        return json_encode(
            [
                "type" => $this->type,
                "id" => $this->id,
                "balanceCap" => $this->balanceCap,
                "issueDate" => $this->issueDate,

                "issuer" => $this->issuer,
                "target" => $this->target,
                "value" => $this->value
            ]
        );
    }
}
