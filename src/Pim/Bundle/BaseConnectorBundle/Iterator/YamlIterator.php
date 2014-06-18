<?php

namespace Pim\Bundle\BaseConnectorBundle\Iterator;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Yaml\Yaml;

/**
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlIterator extends \IteratorIterator
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     * 
     * @param string $file
     * @param array $options
     */
    public function __construct($file, array $options=[])
    {
        $resolver = new OptionsResolver;
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($options);

        $iterator = new ArrayIterator(current(Yaml::parse($file)));
        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $current = parent::current();
        if (isset($this->options['code_field']) && !isset($current[$this->options['code_field']])) {
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
}
