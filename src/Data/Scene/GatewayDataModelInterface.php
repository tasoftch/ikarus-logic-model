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

namespace Ikarus\Logic\Model\Data\Scene;


use Ikarus\Logic\Model\Data\Node\NodeDataModelInterface;

interface GatewayDataModelInterface
{
    /**
     * Gets the destination scene, where the gateway should be linked.
     * Returning a string must be the identifier of the scene.
     *
     * @return string|SceneDataModelInterface
     */
    public function getDestinationScene();

    /**
     * Gets the node from where the gateway should be linked
     * Returning a string must be the identifier of the node.
     * Please note that the source node must reference Ikarus component GATEWAY defined in ikarus/logic-engine
     *
     * @return string|NodeDataModelInterface
     */
    public function getSourceNode();

    /**
     * Return the socket map which means which sockets are linked to each other.
     * Example Node A (id A1 and Ikarus component GATEWAY!) has input1, input2, and output1.
     * Scene B has nodes exposing output (Node id B1), output (Node id B2) and input1.
     * So a valid socket map ALWAYS MUST LINK AN INPUT WITH AN OUTPUT!
     * Possible map:
     *  ['A1.input1' => 'B1.output', 'A1.input2' => 'B2.output', 'A1.output1' => 'B1.input1']
     *
     * @return array
     */
    public function getSocketMap(): array;
}