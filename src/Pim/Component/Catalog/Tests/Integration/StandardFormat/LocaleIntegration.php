<?php

namespace Pim\Component\Catalog\Tests\Integration\StandardFormat;

use Pim\Integration\PimTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleIntegration extends PimTestCase
{
    public function testLocale()
    {
        $expected = [
            'code'    => 'en_US',
            'enabled' => true,
        ];

        $repository = $this->get('pim_catalog.repository.locale');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('en_US'), 'standard');

        $this->assertSame($expected, $result);
    }
}
