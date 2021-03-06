<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Ikarus\Logic\Model\Package;

use Ikarus\Logic\Model\Component\Socket\Type\Signal;
use Ikarus\Logic\Model\Component\Socket\Type\Type;

class BasicTypesPackage extends AbstractPackage
{
    const TYPE_SIGNAL = 'Signal';

    const TYPE_ANY = 'Any';
    const TYPE_STRING = 'String';
    const TYPE_NUMBER = 'Number';
    const TYPE_BOOLEAN = 'Boolean';

    protected function makeComponents(): array
    {
        $any = new Type(static::TYPE_ANY);
        $number = new Type(static::TYPE_NUMBER);
        $string = new Type(static::TYPE_STRING);
        $bool = new Type(static::TYPE_BOOLEAN);

        $any
            ->combineWithType($number)
            ->combineWithType($string)
            ->combineWithType($bool)
        ;

        $number->combineWithType($bool);

        $string
            ->combineWithType($number)
            ->combineWithType($bool)
        ;
        $bool->combineWithType($number);

        return [
            new Signal(static::TYPE_SIGNAL),
            $any,
            $string,
            $number,
            $bool
        ];
    }
}