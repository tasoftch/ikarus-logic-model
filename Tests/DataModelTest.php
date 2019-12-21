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
 * DataModelTest.php
 * ikarus-logic-model
 *
 * Created on 2019-12-20 20:30 by thomas
 */

namespace Ikarus\Logic\Model\Test;

use Ikarus\Logic\Model\Data\Node\AttributedNodeDataModel;
use Ikarus\Logic\Model\Data\Scene\AttributedSceneDataModel;
use Ikarus\Logic\Model\Data\Scene\SceneDataModel;
use Ikarus\Logic\Model\Data\Scene\SceneDataModelInterface;
use Ikarus\Logic\Model\DataModel;
use PHPUnit\Framework\TestCase;

class DataModelTest extends TestCase
{
    public function testAddScene() {
        $model = new DataModel();
        $scene = new SceneDataModel("myID");

        $model->addSceneDataModel($scene);

        $this->assertSame(['myID' => $scene], $model->getSceneDataModels());
    }

    public function testAddSceneManual() {
        $model = new DataModel();

        $model->addScene("myID");
        $model->addScene("yourID", [1, 2, 3]);

        $this->assertCount(2, $model->getSceneDataModels());
        $this->assertContainsOnlyInstancesOf(SceneDataModelInterface::class, $model->getSceneDataModels());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\DuplicateIdentifierException
     */
    public function testAddSceneInconsistent() {
        $model = new DataModel();

        $model->addScene("myID");
        $model->addScene("myID", [1, 2, 3]);
    }

    public function testAddNode() {
        $model = new DataModel();
        $model->addScene("myID", [1, 2, 3]);

        $model->addNode("myNode", "ONE_INPUT", 'myID', [1, 2, 3]);

        $this->assertCount(1, $scenes = $model->getSceneDataModels());
        /** @var AttributedSceneDataModel $scene */
        $this->assertInstanceOf(AttributedSceneDataModel::class, $scene = $scenes['myID']);

        $this->assertEquals("myID", $scene->getIdentifier());
        $this->assertEquals([1, 2, 3], $scene->getAttributes());

        $this->assertCount(1, $model->getNodesInScene($scene));
        $this->assertCount(1, $nodes = $model->getNodesInScene('myID'));

        /** @var AttributedNodeDataModel $node */
        $this->assertInstanceOf(AttributedNodeDataModel::class, $node = $nodes["myNode"]);

        $this->assertEquals('myNode', $node->getIdentifier());
        $this->assertEquals('ONE_INPUT', $node->getComponentName());
        $this->assertEquals([1, 2, 3], $node->getAttributes());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\DuplicateIdentifierException
     */
    public function testAddNodeInconsistent() {
        $model = new DataModel();

        $model->addScene("myID");
        $model->addNode("myID", 'ONE_INPUT', 'myID');
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\InvalidReferenceException
     */
    public function testAddNodeInconsistent2() {
        $model = new DataModel();

        $model->addScene("myID");
        $model->addNode("theID", 'ONE_INPUT', 'whereID');
    }
}
