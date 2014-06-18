<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Iterator;

use Akeneo\Component\SpreadsheetParser\SpreadsheetInterface;
use Akeneo\Component\SpreadsheetParser\SpreadsheetLoaderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Iterator\ArrayHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SpreadsheetIteratorSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        ArrayHelper $arrayHelper,
        SpreadsheetLoaderInterface $spreadsheetLoader,
        SpreadsheetInterface $spreadsheet
    ) {
        $container->get('pim_base_connector.iterator.array_helper')->willReturn($arrayHelper);
        $container->get('akeneo_spreadsheet_parser.spreadsheet_loader')->willReturn($spreadsheetLoader);
        $arrayHelper->combineArrays(\Prophecy\Argument::type('array'), \Prophecy\Argument::type('array'))
            ->will(
                function ($args) {
                    return array_combine($args[0], $args[1]);
                }
            );
        $spreadsheetLoader->open('path')->willReturn($spreadsheet);
        $spreadsheet->getWorksheets()->willReturn(
            ['included_0', 'included_1', 'included_excluded_2', 'excluded_3', 'other_4']
        );
        $spreadsheet->createRowIterator(\Prophecy\Argument::type('int'), ['parser_options'])->will(
            function ($args) {
                return new \ArrayIterator([
                    1 => ["value_$args[0]_0"],
                    2 => ["value_$args[0]_1"],
                    3 => ["value_$args[0]_2"],
                    4 => ["value_$args[0]_3"]
                ]);
            }
        );
    }

    public function it_is_initializable()
    {
        $this->beConstructedWith('path', ['parser_options' => ['parser_options']]);
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Iterator\SpreadsheetIterator');
    }

    public function it_can_read_multiple_tabs(ContainerInterface $container)
    {
        $this->beConstructedWith('path', ['parser_options' => ['parser_options']]);
        $this->setContainer($container);
        $this->testIterate(
            [
                ['value_0_0' => 'value_0_1'],
                ['value_0_0' => 'value_0_2'],
                ['value_0_0' => 'value_0_3'],
                ['value_1_0' => 'value_1_1'],
                ['value_1_0' => 'value_1_2'],
                ['value_1_0' => 'value_1_3'],
                ['value_2_0' => 'value_2_1'],
                ['value_2_0' => 'value_2_2'],
                ['value_2_0' => 'value_2_3'],
                ['value_3_0' => 'value_3_1'],
                ['value_3_0' => 'value_3_2'],
                ['value_3_0' => 'value_3_3'],
                ['value_4_0' => 'value_4_1'],
                ['value_4_0' => 'value_4_2'],
                ['value_4_0' => 'value_4_3'],
            ]
        );
    }

    public function it_can_filter_non_included_tabs(ContainerInterface $container)
    {
        $this->beConstructedWith(
            'path',
            ['include_worksheets' => ['/included/'], 'parser_options' => ['parser_options']]
        );
        $this->setContainer($container);
        $this->testIterate(
            [
                ['value_0_0' => 'value_0_1'],
                ['value_0_0' => 'value_0_2'],
                ['value_0_0' => 'value_0_3'],
                ['value_1_0' => 'value_1_1'],
                ['value_1_0' => 'value_1_2'],
                ['value_1_0' => 'value_1_3'],
                ['value_2_0' => 'value_2_1'],
                ['value_2_0' => 'value_2_2'],
                ['value_2_0' => 'value_2_3'],
            ]
        );
    }

    public function it_can_filter_excluded_tabs(ContainerInterface $container)
    {
        $this->beConstructedWith(
            'path',
            ['exclude_worksheets' => ['/excluded/'], 'parser_options' => ['parser_options']]
        );
        $this->setContainer($container);
        $this->testIterate(
            [
                ['value_0_0' => 'value_0_1'],
                ['value_0_0' => 'value_0_2'],
                ['value_0_0' => 'value_0_3'],
                ['value_1_0' => 'value_1_1'],
                ['value_1_0' => 'value_1_2'],
                ['value_1_0' => 'value_1_3'],
                ['value_4_0' => 'value_4_1'],
                ['value_4_0' => 'value_4_2'],
                ['value_4_0' => 'value_4_3'],
            ]
        );
    }

    public function it_can_filter_included_and_excluded_tabs(ContainerInterface $container)
    {
        $this->beConstructedWith(
            'path',
            [
                'exclude_worksheets' => ['/excluded/'],
                'include_worksheets' => ['/included/'],
                'parser_options' => ['parser_options']
            ]
        );
        $this->setContainer($container);
        $this->testIterate(
            [
                ['value_0_0' => 'value_0_1'],
                ['value_0_0' => 'value_0_2'],
                ['value_0_0' => 'value_0_3'],
                ['value_1_0' => 'value_1_1'],
                ['value_1_0' => 'value_1_2'],
                ['value_1_0' => 'value_1_3'],
            ]
        );
    }

    public function it_can_use_a_different_data_range(ContainerInterface $container)
    {
        $this->beConstructedWith(
            'path',
            [
                'label_row' => 2,
                'data_row'  => 4,
                'parser_options' => ['parser_options']
            ]
        );
        $this->setContainer($container);
        $this->testIterate(
            [
                ['value_0_1' => 'value_0_3'],
                ['value_1_1' => 'value_1_3'],
                ['value_2_1' => 'value_2_3'],
                ['value_3_1' => 'value_3_3'],
                ['value_4_1' => 'value_4_3'],
            ]
        );
    }

    protected function testIterate($values)
    {
        $this->rewind();
        foreach ($values as $row) {
            $this->valid()->shouldReturn(true);
            $this->current()->shouldReturn($row);
            $this->next();
        }
        $this->valid()->shouldReturn(false);
    }
}
