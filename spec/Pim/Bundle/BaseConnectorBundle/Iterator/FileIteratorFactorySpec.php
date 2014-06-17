<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Iterator;

use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileIteratorFactorySpec extends ObjectBehavior
{
    public function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Iterator\FileIteratorFactory');
    }

    public function it_creates_objects()
    {
        $this->create('stdClass', 'file')->shouldHaveType('stdClass');
    }
}
