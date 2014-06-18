<?php

namespace Pim\Bundle\BaseConnectorBundle\Iterator;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * YAML file iterator
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlIterator extends AbstractFileIterator
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Constructor
     * 
     * @param string $file
     * @param array $options
     */
    public function __construct($file, array $options=[])
    {
        parent::__construct($file, $options);

        $this->iterator = new \ArrayIterator(current(Yaml::parse($file)));
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $current = $this->iterator->current();
        if (null !== $current && 
            isset($this->options['code_field']) && !isset($current[$this->options['code_field']])) {

            $current[$this->options['code_field']] = $this->key();
        }

        return $current;
    }

    
    /**
     * Sets the default options
     * 
     * @param OptionsResolverInterface $resolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional('code_field');
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->iterator->valid();
    }
}
