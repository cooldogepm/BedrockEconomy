<?php

/**
 *  Copyright (c) 2021-2023 cooldogedev
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

namespace cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\exception;

use Exception;

class SQLException extends Exception
{
    public function __construct(
        protected array $_trace = [],

        protected string $_traceAsString = "",
        protected string $_message = "",
        protected string $_file = "",

        protected int $_code = 0,
        protected int $_line = 0,
    ) {
        parent::__construct($this->_message, $this->_code);
    }

    public function _getMessage(): string
    {
        return $this->_message;
    }

    public function _getCode(): int
    {
        return $this->_code;
    }

    public function _getTrace(): array
    {
        return $this->_trace;
    }

    public function _getFile(): string
    {
        return $this->_file;
    }

    public function _getLine(): int
    {
        return $this->_line;
    }

    public function _getTraceAsString(): string
    {
        return $this->_traceAsString;
    }

    public static function fromArray(array $exception): SQLException
    {
        $class = $exception["class"] ?? SQLException::class;

        return new $class(
            _trace: $exception["trace"],
            _traceAsString: $exception["trace_string"],

            _message: $exception["message"],

            _file: $exception["file"],
            _code: $exception["code"],
            _line: $exception["line"],
        );
    }
}