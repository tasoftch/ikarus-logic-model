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

use Ikarus\Logic\Model\Component\Socket\Type\TypeInterface;
use Ikarus\Logic\Model\Exception\ComponentNotFoundException;
use Ikarus\Logic\Model\Exception\SocketComponentNotFoundException;

/**
 * Component model instances deliver a compiler or an engine with the information of how nodes are setup and rendered.
 *
 * @package Ikarus\Logic\Model
 */
interface ComponentModelInterface
{
    /**
     * This method is asked every time a specific component is required.
     * That may be for compilation or for render.
     *
     * A compiler or engine NEVER caches components! It will always fetch it from the model.
     *
     * @param $name
     * @return NodeComponentInterface
     * @throws ComponentNotFoundException
     */
    public function getComponent($name): NodeComponentInterface;

    /**
     * This method is asked to obtain socket type descriptions.
     *
     * A compiler or engine NEVER caches socket types! It will always fetch it from the model.
     *
     * @param $name
     * @return TypeInterface
     * @throws SocketComponentNotFoundException
     */
    public function getSocketType($name): TypeInterface;
}