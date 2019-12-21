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


use Ikarus\Logic\Model\Component\NodeComponentInterface;
use Ikarus\Logic\Model\Component\Socket\Type\TypeInterface;

abstract class AbstractPackage implements PackageInterface
{
    /** @var NodeComponentInterface[] */
    protected $components;
    /** @var TypeInterface[] */
    protected $socketTypes;

    /**
     * @return NodeComponentInterface[]
     */
    public function getComponents(): array
    {
        if(NULL === $this->components)
            $this->_resolveComponents();
        return $this->components;
    }

    /**
     * @return TypeInterface[]
     */
    public function getSocketTypes(): array
    {
        if(NULL === $this->socketTypes)
            $this->_resolveComponents();
        return $this->socketTypes;
    }

    private function _resolveComponents() {
        $this->components = $this->socketTypes = [];

        foreach($this->makeComponents() as $component) {
            if($component instanceof TypeInterface)
                $this->socketTypes[ $component->getName() ] = $component;
            elseif($component instanceof NodeComponentInterface)
                $this->components[ $component->getName() ] = $component;
            else
                trigger_error(sprintf("Component of class %s is not supported", get_class($component)), E_USER_WARNING);
        }
    }

    /**
     * Makes all components
     *
     * @return TypeInterface[]|NodeComponentInterface[]
     */
    abstract protected function makeComponents(): array;
}