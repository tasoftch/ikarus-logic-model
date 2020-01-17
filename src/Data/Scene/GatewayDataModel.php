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

class GatewayDataModel implements GatewayDataModelInterface
{
    /** @var string|SceneDataModelInterface */
    private $destinationScene;
    /** @var string|NodeDataModelInterface */
    private $sourceNode;
    /** @var array */
    private $socketMap;

    /**
     * GatewayDataModel constructor.
     * @param SceneDataModelInterface|string $destinationScene
     * @param NodeDataModelInterface|string $sourceNode
     * @param array $socketMap
     */
    public function __construct($destinationScene, $sourceNode, array $socketMap)
    {
        $this->destinationScene = $destinationScene;
        $this->sourceNode = $sourceNode;
        $this->socketMap = $socketMap;
    }

    /**
     * @return SceneDataModelInterface|string
     */
    public function getDestinationScene()
    {
        return $this->destinationScene;
    }

    /**
     * @return NodeDataModelInterface|string
     */
    public function getSourceNode()
    {
        return $this->sourceNode;
    }

    /**
     * @return array
     */
    public function getSocketMap(): array
    {
        return $this->socketMap;
    }
}