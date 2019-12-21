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
 * ComponentModelTest.php
 * ikarus-logic-model
 *
 * Created on 2019-12-20 18:44 by thomas
 */

namespace Ikarus\Logic\Model\Test;

use Ikarus\Logic\Model\Component\Socket\Type\Type;
use Ikarus\Logic\Model\Package\BasicTypesPackage;
use Ikarus\Logic\Model\PriorityComponentModel;
use Ikarus\Logic\Model\Test\Component\OneInputAndOneOutputComponent;
use Ikarus\Logic\Model\Test\Component\OneInputComponent;
use Ikarus\Logic\Model\Test\Component\OneOutputComponent;
use Ikarus\Logic\Model\Test\Component\TestComponentPackage;
use PHPUnit\Framework\TestCase;

class ComponentModelTest extends TestCase
{
    public function testAddingComponents() {
        $model = new PriorityComponentModel();

        $model->addComponent( $c1 = new OneInputComponent(), 15 );
        $model->addComponent( $c2 = new OneInputAndOneOutputComponent(), 5 );
        $model->addComponent( $c3 = new OneOutputComponent(), 8 );

        $this->assertSame([
            $c2, $c3, $c1
        ], $model->getComponents());
    }

    public function testRemovingComponents() {
        $model = new PriorityComponentModel();

        $model->addComponent( $c1 = new OneInputComponent(), 15 );
        $model->addComponent( $c2 = new OneInputAndOneOutputComponent(), 5 );
        $model->addComponent( $c3 = new OneOutputComponent(), 8 );

        $model->removeComponent($c1);
        $model->removeComponent("INPUT_OUTPUT");

        $this->assertSame([
            $c3
        ], $model->getComponents());
    }

    public function testGetComponent() {
        $model = new PriorityComponentModel();

        $model->addComponent( $c1 = new OneInputComponent(), 15 );
        $model->addComponent( $c2 = new OneInputAndOneOutputComponent(), 5 );
        $model->addComponent( $c3 = new OneOutputComponent(), 8 );

        $this->assertSame($c1, $model->getComponent( $c1 ));
        $this->assertSame($c3, $model->getComponent( "ONE_OUTPUT" ));
        $this->assertSame($c2, $model->getComponent( new OneInputAndOneOutputComponent() ));
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\InconsistentDataModelException
     */
    public function testDoubleNamedComponent() {
        $model = new PriorityComponentModel();

        $model->addComponent( $c1 = new OneInputComponent(), 15 );
        $model->addComponent( $c2 = new OneInputComponent(), 5 );
    }

    public function testAddingSocketTypes() {
        $model = new PriorityComponentModel();

        $model->addSocketType($c1 = new Type("String"));
        $model->addSocketType($c2 = new Type("Number"));
        $model->addSocketType($c3 = new Type("Boolean"));

        $this->assertSame([$c1, $c2, $c3], $model->getSocketTypes());
    }

    public function testRemovingSocketTypes() {
        $model = new PriorityComponentModel();

        $model->addSocketType($c1 = new Type("String"));
        $model->addSocketType($c2 = new Type("Number"));
        $model->addSocketType($c3 = new Type("Boolean"));

        $model->removeSocketType($c2);
        $model->removeSocketType("String");

        $this->assertSame([$c3], $model->getSocketTypes());
    }

    public function testGetSocketType() {
        $model = new PriorityComponentModel();

        $model->addSocketType($c1 = new Type("String"));
        $model->addSocketType($c2 = new Type("Number"));
        $model->addSocketType($c3 = new Type("Boolean"));

        $this->assertSame($c1, $model->getSocketType( $c1 ));
        $this->assertSame($c3, $model->getSocketType( "Boolean" ));
        $this->assertSame($c2, $model->getSocketType( new Type("Number") ));
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\ComponentNotFoundException
     */
    public function testUnexistingComponent() {
        $model = new PriorityComponentModel();

        $model->getComponent("ONE_INPUT");
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\SocketComponentNotFoundException
     */
    public function testUnexistingSocketType() {
        $model = new PriorityComponentModel();

        $model->getSocketType("String");
    }

    public function testPackages() {
        $model = new PriorityComponentModel();

        $model->addPackage( $pkg = new TestComponentPackage() );

        $this->assertCount( 1, $model->getComponents() );
        $this->assertCount(5, $model->getSocketTypes());

        $model->removePackage(new BasicTypesPackage() );

        $this->assertCount( 1, $model->getComponents() );
        $this->assertCount(0, $model->getSocketTypes());

        $model->removePackage($pkg );

        $this->assertCount( 0, $model->getComponents() );
        $this->assertCount(0, $model->getSocketTypes());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\InconsistentDataModelException
     */
    public function testDoubleNamedSocket() {
        $model = new PriorityComponentModel();

        $model->addSocketType($c2 = new Type("Number"));
        $model->addSocketType($c3 = new Type("Number"));
    }

    public function testDoubleNamesAfterRemove() {
        $model = new PriorityComponentModel();

        $model->addSocketType($c2 = new Type("Number"));
        $model->addSocketType($c3 = new Type("Boolean"));

        $model->removeSocketType("Number");
        $model->addSocketType($c2 = new Type("Number"));

        $this->assertSame([$c3, $c2], $model->getSocketTypes());
    }

    public function testDoubleNamesCompsAfterRemove() {
        $model = new PriorityComponentModel();

        $model->addComponent( $c1 = new OneInputComponent(), 15 );
        $model->addComponent( $c2 = new OneOutputComponent(), 5 );

        $model->removeComponent($c1);
        $model->addComponent( $c1 = new OneInputComponent(), 15 );

        $this->assertSame([$c2, $c1], $model->getComponents());
    }
}
