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

/**
 * DataModelLoadingTest.php
 * ikarus-logic-model
 *
 * Created on 2019-12-21 12:05 by thomas
 */

namespace Ikarus\Logic\Model\Test;

use Ikarus\Logic\Model\Data\Connection\ConnectionDataModelInterface;
use Ikarus\Logic\Model\Data\Loader\PHPArrayLoader;
use Ikarus\Logic\Model\Data\Node\AttributedNodeDataModel;
use Ikarus\Logic\Model\Data\Node\NodeDataModelInterface;
use Ikarus\Logic\Model\Data\Scene\AttributedSceneDataModel;
use Ikarus\Logic\Model\DataModel;
use PHPUnit\Framework\TestCase;

class DataModelLoadingTest extends TestCase
{
    /**
     * @expectedException \PHPUnit\Framework\Error\Notice
     */
    public function testEmptyModel() {
        $loader = new PHPArrayLoader([]);
        $this->assertInstanceOf(DataModel::class, $loader->getModel());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\InconsistentDataModelException
     * @expectedExceptionCode 99
     */
    public function testSceneWithoutIdentifier() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                [
                    PHPArrayLoader::ID_KEY => NULL
                ]
            ]
        ]);
        $this->assertInstanceOf(DataModel::class, $loader->getModel());
    }

    public function testSceneWithIndexedArray() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                [
                    // Empty, gets id 0
                    PHPArrayLoader::NODES_KEY => [
                        'myNode' => [
                            PHPArrayLoader::NAME_KEY => 'test'
                        ]
                    ]
                ],
                [
                    // Empty, gets id 1
                    PHPArrayLoader::NODES_KEY => [
                        'yourNode' => [
                            PHPArrayLoader::NAME_KEY => 'test'
                        ]
                    ]
                ]
            ]
        ]);
        $loader->useIndicesAsIdentifiers = true;

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());

        $this->assertCount(2, $model->getSceneDataModels());

        $scene = $model->getSceneDataModels()[0];
        $this->assertEquals(0, $scene->getIdentifier());

        $scene = $model->getSceneDataModels()[1];
        $this->assertEquals(1, $scene->getIdentifier());
    }

    public function testSceneWithKeyedIndexArray() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                'myScene' => [
                    PHPArrayLoader::NODES_KEY => [
                        'myNode' => [
                            PHPArrayLoader::NAME_KEY => 'test'
                        ]
                    ]
                ],
                'yourScene' => [
                    PHPArrayLoader::NODES_KEY => [
                        'yourNode' => [
                            PHPArrayLoader::NAME_KEY => 'test'
                        ]
                    ]
                ]
            ]
        ]);
        $loader->useIndicesAsIdentifiers = true;

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());

        $this->assertCount(2, $model->getSceneDataModels());

        $scene = $model->getSceneDataModels()['myScene'];
        $this->assertEquals('myScene', $scene->getIdentifier());

        $scene = $model->getSceneDataModels()['yourScene'];
        $this->assertEquals('yourScene', $scene->getIdentifier());
    }

    public function testScenesWithInternalIDArray() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                [
                    PHPArrayLoader::ID_KEY => 'myScene',
                    PHPArrayLoader::NODES_KEY => [
                        [
                            PHPArrayLoader::ID_KEY => 'myNode',
                            PHPArrayLoader::NAME_KEY => 'test'
                        ]
                    ]
                ],
                [
                    PHPArrayLoader::ID_KEY => 'yourScene',
                    PHPArrayLoader::NODES_KEY => [
                        [
                            PHPArrayLoader::ID_KEY => 'yourNode',
                            PHPArrayLoader::NAME_KEY => 'test'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());

        $this->assertCount(2, $model->getSceneDataModels());

        $scene = $model->getSceneDataModels()['myScene'];
        $this->assertEquals('myScene', $scene->getIdentifier());

        $scene = $model->getSceneDataModels()['yourScene'];
        $this->assertEquals('yourScene', $scene->getIdentifier());
    }

    public function testNodeComponent() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                [
                    PHPArrayLoader::ID_KEY => 'myScene',
                    PHPArrayLoader::NODES_KEY => [
                        [
                            PHPArrayLoader::ID_KEY => 'myNode',
                            PHPArrayLoader::NAME_KEY => 'test'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());
        /** @var NodeDataModelInterface $node */
        $node = $model->getNodesInScene('myScene')["myNode"];

        $this->assertEquals("myNode", $node->getIdentifier());
        $this->assertEquals("test", $node->getComponentName());
    }

    public function testSceneAndNodeWithData() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                [
                    PHPArrayLoader::ID_KEY => 'myScene',
                    PHPArrayLoader::DATA_KEY => [1, 2, 3],

                    PHPArrayLoader::NODES_KEY => [
                        [
                            PHPArrayLoader::ID_KEY => 'myNode',
                            PHPArrayLoader::NAME_KEY => 'test',
                            PHPArrayLoader::DATA_KEY => ['the-data']
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());
        /** @var AttributedNodeDataModel $node */
        $node = $model->getNodesInScene('myScene')["myNode"];

        $this->assertEquals("myNode", $node->getIdentifier());
        $this->assertEquals("test", $node->getComponentName());
        $this->assertEquals(["the-data"], $node->getAttributes());

        /** @var AttributedSceneDataModel $scene */
        $scene = $model->getSceneDataModels()[ 'myScene' ];

        $this->assertEquals("myScene", $scene->getIdentifier());
        $this->assertEquals([1, 2, 3], $scene->getAttributes());
    }

    public function testSimpleConnection() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                'myScene' => [
                    PHPArrayLoader::NODES_KEY => [
                        'myNode' => [
                            PHPArrayLoader::NAME_KEY => 'test'
                        ],
                        'yourNode' => [
                            PHPArrayLoader::NAME_KEY => 'hehe'
                        ]
                    ],
                    PHPArrayLoader::CONNECTIONS_KEY => [
                        [
                            // The data model can only check, if the required node identifiers exist and the two nodes are in the same scene.
                            // Everything else you need a compiler to check consistency

                            PHPArrayLoader::CONNECTION_INPUT_NODE_KEY => 'myNode',
                            PHPArrayLoader::CONNECTION_INPUT_KEY => 'input',
                            PHPArrayLoader::CONNECTION_OUTPUT_NODE_KEY => 'yourNode',
                            PHPArrayLoader::CONNECTION_OUTPUT_KEY => 'output'
                        ]
                    ]
                ]
            ]
        ]);
        $loader->useIndicesAsIdentifiers = true;

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());
        $this->assertCount(1, $connections = $model->getConnectionsInScene("myScene"));

        /** @var ConnectionDataModelInterface $connection */
        $connection = array_shift($connections);

        $this->assertEquals("myNode", $connection->getInputNodeIdentifier());
        $this->assertEquals("yourNode", $connection->getOutputNodeIdentifier());
        $this->assertEquals("input", $connection->getInputSocketName());
        $this->assertEquals("output", $connection->getOutputSocketName());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\InvalidReferenceException
     * @expectedExceptionCode 99
     */
    public function testInvalidInputNodeRefException() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                'myScene' => [
                    PHPArrayLoader::NODES_KEY => [
                        'myNode' => [
                            PHPArrayLoader::NAME_KEY => 'test'
                        ],
                        'yourNode' => [
                            PHPArrayLoader::NAME_KEY => 'hehe'
                        ]
                    ],
                    PHPArrayLoader::CONNECTIONS_KEY => [
                        [
                            // The data model can only check, if the required node identifiers exist and the two nodes are in the same scene.
                            // Everything else you need a compiler to check consistency

                            PHPArrayLoader::CONNECTION_INPUT_NODE_KEY => 'unexisting input node',
                            PHPArrayLoader::CONNECTION_INPUT_KEY => 'input',
                            PHPArrayLoader::CONNECTION_OUTPUT_NODE_KEY => 'yourNode',
                            PHPArrayLoader::CONNECTION_OUTPUT_KEY => 'output'
                        ]
                    ]
                ]
            ]
        ]);
        $loader->useIndicesAsIdentifiers = true;

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\InvalidReferenceException
     * @expectedExceptionCode 99
     */
    public function testInvalidOutputNodeRefException() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                'myScene' => [
                    PHPArrayLoader::NODES_KEY => [
                        'myNode' => [
                            PHPArrayLoader::NAME_KEY => 'test'
                        ],
                        'yourNode' => [
                            PHPArrayLoader::NAME_KEY => 'hehe'
                        ]
                    ],
                    PHPArrayLoader::CONNECTIONS_KEY => [
                        [
                            // The data model can only check, if the required node identifiers exist and the two nodes are in the same scene.
                            // Everything else you need a compiler to check consistency

                            PHPArrayLoader::CONNECTION_INPUT_NODE_KEY => 'myNode',
                            PHPArrayLoader::CONNECTION_INPUT_KEY => 'input',
                            PHPArrayLoader::CONNECTION_OUTPUT_NODE_KEY => 'unexisting node',
                            PHPArrayLoader::CONNECTION_OUTPUT_KEY => 'output'
                        ]
                    ]
                ]
            ]
        ]);
        $loader->useIndicesAsIdentifiers = true;

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\InconsistentDataModelException
     * @expectedExceptionCode 102
     */
    public function testInvalidPlacement() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                // This scene must be declared before, otherwise the wrong node won't exist yet.
                'yourScene' => [
                    PHPArrayLoader::NODES_KEY => [
                        'yourNode' => [
                            PHPArrayLoader::NAME_KEY => 'hehe'
                        ]
                    ],
                ],

                'myScene' => [
                    PHPArrayLoader::NODES_KEY => [
                        'myNode' => [
                            PHPArrayLoader::NAME_KEY => 'test'
                        ]
                    ],
                    PHPArrayLoader::CONNECTIONS_KEY => [
                        [
                            // The data model can only check, if the required node identifiers exist and the two nodes are in the same scene.
                            // Everything else you need a compiler to check consistency

                            PHPArrayLoader::CONNECTION_INPUT_NODE_KEY => 'myNode',
                            PHPArrayLoader::CONNECTION_INPUT_KEY => 'input',
                            PHPArrayLoader::CONNECTION_OUTPUT_NODE_KEY => 'yourNode',
                            PHPArrayLoader::CONNECTION_OUTPUT_KEY => 'output'
                        ]
                    ]
                ]
            ]
        ]);
        $loader->useIndicesAsIdentifiers = true;

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());
    }

    public function testPHPLoaderWithGateway() {
        $loader = new PHPArrayLoader([
            PHPArrayLoader::SCENES_KEY => [
                'myScene' => [
                    PHPArrayLoader::NODES_KEY => [
                        'myNode' => [
                            PHPArrayLoader::NAME_KEY => 'test',
                            PHPArrayLoader::GATEWAY_DESTINATION_SCENE_KEY => 'myScene',
                            PHPArrayLoader::GATEWAY_SOCKET_MAP_KEY => [
                                'myNode:input' => 'superNode:output'
                            ]
                        ],
                        'superNode' => [
                            PHPArrayLoader::NAME_KEY => 'super'
                        ]
                    ]
                ]
            ]
        ]);
        $loader->useIndicesAsIdentifiers = true;

        $this->assertInstanceOf(DataModel::class, $model = $loader->getModel());

        $gateway = $model->getGatewaysToScene('myScene')[ "myNode" ];

        $this->assertEquals("myScene", $gateway->getDestinationScene()->getIdentifier());
        $this->assertEquals('myNode', $gateway->getSourceNode()->getIdentifier());
        $this->assertEquals([
            'myNode:input' => 'superNode:output'
        ], $gateway->getSocketMap());
    }
}
