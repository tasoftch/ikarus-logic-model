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

namespace Ikarus\Logic\Model\Executable\Context;

use Ikarus\Logic\Model\Executable\ExecutableExpressionNodeComponentInterface;

/**
 * The Ikarus Logic Engine provides your component a runtime context so it can obtain several information about the current state
 *
 * @package Ikarus\Logic\Model\Executable\Context
 */
interface RuntimeContextInterface
{
    /**
     * Returns the identifier of the current node that this component should update for.
     *
     * @return string|int
     */
    public function getNodeIdentifier();

    /**
     * Gets the current node's attributes if available.
     *
     * @return array|null
     */
    public function getNodeAttributes(): ?array;




    // Increase runtime performance

    /** @var int Marks as updated for this node, but will continue updating for other nodes of this component */
    const UPDATE_STATE_NODE = 1<<0;

    /** @var int Marks as updated for this component. The render engine won't update the component anymore. */
    const UPDATE_STATE_COMPONENT = 1<<1;

    /** @var int Holds update state above for the current render cycle, means the current cycle and its child cycles. */
    const UPDATE_STATE_CURRENT_CYCLE = 1<<2;

    /** @var int Holds update state until the render cycle has terminated. */
    const UPDATE_STATE_ROOT_CYCLE = 1<<3;

    /** @var int Holds update state until engine terminates. */
    const UPDATE_STATE_FOREVER = 0xFF;

    /**
     * Marks this component as updated, so the render engine won't call the updateNode method anymore.
     * See the UPDATE_STATE_* constants for detailed update states.
     * NOTE: You can combine the update states using bitwise or operator.
     *
     * @param int $updateState
     * @see RuntimeContextInterface::UPDATE_STATE_* constants
     * @see ExecutableExpressionNodeComponentInterface::updateNode()
     */
    public function markAsUpdated(int $updateState);
}