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

namespace Ikarus\Logic\Model\Component;


use Ikarus\Logic\Model\Component\Socket\InputSocketComponentInterface;
use Ikarus\Logic\Model\Component\Socket\OutputSocketComponentInterface;
use Ikarus\Logic\Model\Exception\DuplicateNameException;
use Ikarus\Logic\Model\Exception\InconsistentComponentModelException;

abstract class AbstractNodeComponent implements NodeComponentInterface
{
    protected $inputSockets;
    protected $outputSockets;

    /**
     * @return array
     */
    public function getInputSockets(): ?array
    {
        if(NULL === $this->inputSockets)
            $this->_resolveSocketList( $this->makeSocketComponents() );
        return $this->inputSockets;
    }

    /**
     * @return array
     */
    public function getOutputSockets(): ?array
    {
        if(NULL === $this->outputSockets)
            $this->_resolveSocketList( $this->makeSocketComponents() );
        return $this->outputSockets;
    }

    private function _resolveSocketList($sockets) {
        $this->inputSockets = $this->outputSockets = $list = [];

        foreach($sockets as $socket) {
            if($socket instanceof InputSocketComponentInterface) {
                if(in_array($socket->getName(), $list)) {
                    $e = new DuplicateNameException("Input socket component %s already exists", DuplicateNameException::CODE_DUPLICATE_SYMBOL, NULL, $socket->getName());
                    $e->setProperty($socket);
                    throw $e;
                }
                $this->inputSockets[ $socket->getName() ] = $socket;
                $list[] = $socket->getName();
            } elseif($socket instanceof OutputSocketComponentInterface) {
                if(in_array($socket->getName(), $list)) {
                    $e = new DuplicateNameException("Output socket component %s already exists", DuplicateNameException::CODE_DUPLICATE_SYMBOL, NULL, $socket->getName());
                    $e->setProperty($socket);
                    throw $e;
                }
                $this->outputSockets[ $socket->getName() ] = $socket;
                $list[] = $socket->getName();
            } elseif($socket instanceof ComponentInterface) {
                if(in_array($socket->getName(), $list)) {
                    $e = new DuplicateNameException("Component %s already exists", DuplicateNameException::CODE_DUPLICATE_SYMBOL, NULL, $socket->getName());
                    $e->setProperty($socket);
                    throw $e;
                }

                if($this->resolveComponent($socket))
                    $list[] = $socket->getName();
            } else {
                $e = new InconsistentComponentModelException("Object in socket list is not a component", InconsistentComponentModelException::CODE_INVALID_INSTANCE);
                $e->setProperty($socket);
                throw $e;
            }
        }
    }

    /**
     * Asking subclasses to resolve component.
     * If this method returns true, the component's name is cached for consistency reasons.
     *
     * @param ComponentInterface $component
     * @return bool
     */
    protected function resolveComponent(ComponentInterface $component): bool {
        return false;
    }

    /**
     * Override this method to create all socket components
     *
     * @return InputSocketComponentInterface[]|OutputSocketComponentInterface[]
     */
    protected function makeSocketComponents(): array {
        return [];
    }
}