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


use Ikarus\Logic\Model\Component\Socket\ExposedSocketComponentInterface;
use Ikarus\Logic\Model\Executable\Context\RuntimeContextInterface;
use Ikarus\Logic\Model\Executable\Context\SignalServerInterface;
use Ikarus\Logic\Model\Executable\Context\ValuesServerInterface;
use Ikarus\Logic\Model\Executable\ExecutableExpressionNodeComponentInterface;
use Ikarus\Logic\Model\Executable\ExecutableSignalTriggerNodeComponentInterface;

class ExecutableNodeComponent extends NodeComponent implements ExecutableExpressionNodeComponentInterface, ExecutableSignalTriggerNodeComponentInterface
{
    /** @var callable|null */
    private $updateHandler;
    /** @var callable|null */
    private $signalHandler;

    /**
     * @param callable|null $signalHandler
     * @return ExecutableNodeComponent
     */
    public function setSignalHandler(?callable $signalHandler): ExecutableNodeComponent
    {
        $this->signalHandler = $signalHandler;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getSignalHandler(): ?callable
    {
        return $this->signalHandler;
    }

    /**
     * @param callable|null $updateHandler
     * @return ExecutableNodeComponent
     */
    public function setUpdateHandler(?callable $updateHandler): ExecutableNodeComponent
    {
        $this->updateHandler = $updateHandler;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getUpdateHandler(): ?callable
    {
        return $this->updateHandler;
    }

    public function updateNode(ValuesServerInterface $valuesServer, RuntimeContextInterface $context)
    {
        if(is_callable( $cb = $this->getUpdateHandler() )) {
            call_user_func( $cb, $valuesServer, $context );
        } else {
            $socketName = $context->getRequestedOutputSocketName();
            $expose = function($socket) use ($valuesServer) {
                if($socket instanceof ExposedSocketComponentInterface) {
                    $value = $valuesServer->fetchInputValue($socket->getName());
                    if(NULL !== $value) {
                        $valuesServer->exposeValue($socket->getName(), $value);
                    }
                }
            };
            if($socketName) {
                if($sk = $this->getInputSockets()[$socketName] ?? NULL)
                    $expose( $sk );
                elseif($sk = $this->getOutputSockets()[$socketName] ?? NULL)
                    // This should only happen for gateways, which are connected from an output socket to an input
                    $expose( $sk );
            } else {
                foreach($this->getInputSockets() as $socket)
                    $expose($socket);
            }
        }
    }

    public function handleSignalTrigger(string $onInputSocketName, SignalServerInterface $signalServer, RuntimeContextInterface $context)
    {
        if(is_callable( $cb = $this->getSignalHandler() )) {
            call_user_func( $cb, $onInputSocketName, $signalServer, $context );
        } else {
            if($this->getInputSockets()[$onInputSocketName] instanceof ExposedSocketComponentInterface) {
                $signalServer->exposeSignal($onInputSocketName);
            }
        }
    }
}