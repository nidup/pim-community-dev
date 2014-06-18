<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Symfony\Component\HttpFoundation\File\File;


/**
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductReader extends AbstractConfigurableStepElement implements UploadedFileAwareInterface,
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var ItemReaderInterface
     */
    protected $reader;

    /**
     * @var array
     */
    private $mediaAttributeCodes;

    /**
     * Constructor
     * 
     * @param AttributeRepository $attributeRepository
     * @param ItemReaderInterface $reader
     */
    function __construct(AttributeRepository $attributeRepository, ItemReaderInterface $reader)
    {
        $this->attributeRepository = $attributeRepository;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return $this->reader->getConfigurationFields() +
            [
                'mediaAttributes' => [
                    'system' => true
                ]
            ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFileConstraints()
    {
        return $this->reader->getUploadedFileConstraints();
    }

    /**
     * {@inheritdoc}
     */
    public function setUploadedFile(File $uploadedFile)
    {
        $this->reader->setUploadedFile($uploadedFile);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return $data = $this->reader->read()
            ? $this->transformMediaPathToAbsolute($data)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        if ($this->reader instanceof StepExecutionAwareInterface) {
            $this->reader->setStepExecution($stepExecution);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->reader, $name], $arguments);
    }

    /**
     * Returns the media attribute codes
     * 
     * @return array
     */
    protected function getMediaAttributeCodes()
    {
        if (!isset($this->mediaAttributeCodes)) {
            $this->mediaAttributeCodes = $this->attributeRepository->findMediaAttributeCodes();
        }

        return $this->mediaAttributeCodes;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function transformMediaPathToAbsolute(array $data)
    {
        $mediaAttributeCodes = $this->getMediaAttributeCodes();
        $dir = realpath(dirname($this->filePath));

        foreach ($data as $code => $value) {
            $pos = strpos($code, '-');
            $attributeCode = false !== $pos ? substr($code, 0, $pos) : $code;

            if (in_array($attributeCode, $mediaAttributeCodes)) {
                $data[$code] = $dir . '/' . $value;
            }
        }

        return $data;
    }
}
