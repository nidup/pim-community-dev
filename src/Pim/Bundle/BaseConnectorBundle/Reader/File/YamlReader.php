<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File as AssertFile;

/**
 * Yaml reader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SpreadsheetReader extends FileIteratorReader implements
    ItemReaderInterface,
    UploadedFileAwareInterface,
    StepExecutionAwareInterface
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @AssertFile(
     *     groups={"Execution"},
     *     allowedExtensions={"yaml", "yml"},
     *     mimeTypes={
     *         "text/yaml",
     *         "text/plain"
     *     }
     * )
     */
    protected $filePath;

    /**
     * Get uploaded file constraints
     *
     * @return array
     */
    public function getUploadedFileConstraints()
    {
        return array(
            new Assert\NotBlank(),
            new AssertFile(
                array(
                    'allowedExtensions' => array('yml', 'yaml'),
                    'mimeTypes'         => array(
                        'text/yml',
                        'text/yaml',
                        'text/plain'
                    )
                )
            )
        );
    }
}
