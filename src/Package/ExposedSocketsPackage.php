<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2020, TASoft Applications
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


use Ikarus\Logic\Model\Component\ExecutableNodeComponent;
use Ikarus\Logic\Model\Component\Socket\ExposedInputComponent;
use Ikarus\Logic\Model\Component\Socket\ExposedOutputComponent;

class ExposedSocketsPackage extends AbstractPackage
{
    private $types;

    private $inputSocketKey = 'input';
    private $outputSocketKey = 'output';

    const NAME_PREFIX_IN_COMPONENT = 'IKARUS.IN.';
    const NAME_PREFIX_OUT_COMPONENT = 'IKARUS.OUT.';


    /**
     * @return string
     */
    public function getInputSocketKey(): string
    {
        return $this->inputSocketKey;
    }

    /**
     * @return string
     */
    public function getOutputSocketKey(): string
    {
        return $this->outputSocketKey;
    }

    public function __construct(...$types)
    {
        $this->types = $types;
    }

    /**
     * @param string $outputSocketKey
     * @return ExposedSocketsPackage
     */
    public function setOutputSocketKey(string $outputSocketKey): ExposedSocketsPackage
    {
        $this->outputSocketKey = $outputSocketKey;
        return $this;
    }

    /**
     * @param string $inputSocketKey
     * @return ExposedSocketsPackage
     */
    public function setInputSocketKey(string $inputSocketKey): ExposedSocketsPackage
    {
        $this->inputSocketKey = $inputSocketKey;
        return $this;
    }

    protected function makeComponents(): array
    {
        $components = [];
        $i = $this->getInputSocketKey();
        $o = $this->getOutputSocketKey();

        foreach($this->types as $type) {
            $k = strtoupper($type);
            $components[] = new ExecutableNodeComponent(static::NAME_PREFIX_IN_COMPONENT . $k, [
                new ExposedOutputComponent($o, $type)
            ]);
            $components[] = new ExecutableNodeComponent(static::NAME_PREFIX_OUT_COMPONENT . $k, [
                new ExposedInputComponent($i, $type)
            ]);
        }
        return $components;
    }
}