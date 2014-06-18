<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File as AssertFile;

/**
 * Spreadsheet reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SpreadsheetReader extends FileIteratorReader 
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @AssertFile(
     *     groups={"Execution"},
     *     allowedExtensions={"csv", "zip", "xlsx", "xlsm"},
     *     mimeTypes={
     *         "text/csv",
     *         "text/comma-separated-values",
     *         "text/plain",
     *         "application/csv",
     *         "application/zip",
     *         "application/vnd.ms-excel",
     *         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
     *     }
     * )
     */
    protected $filePath;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"}, message="The value must be one of , or ; or |")
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     */
    protected $enclosure = '"';

    /**
     * @Assert\NotBlank
     */
    protected $escape = '\\';

    /**
     * @var string
     */
    protected $encoding = '';

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var string $extractedPath
     */
    protected $extractedPath;

    /**
     * Remove the extracted directory
     */
    public function flush()
    {
        parent::flush();
        if ($this->extractedPath) {
            $fileSystem = new Filesystem();
            $fileSystem->remove($this->extractedPath);
        }
    }

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
                    'allowedExtensions' => array('csv', 'zip', 'xlsx', 'xlsm'),
                    'mimeTypes'         => array(
                        'text/csv',
                        'text/comma-separated-values',
                        'text/plain',
                        'application/csv',
                        'application/zip',
                        "application/vnd.ms-excel",
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    )
                )
            )
        );
    }

    /**
     * Set delimiter
     * @param string $delimiter
     *
     * @return CsvReader
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Get delimiter
     * @return string $delimiter
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set enclosure
     * @param string $enclosure
     *
     * @return CsvReader
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Get enclosure
     * @return string $enclosure
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set escape
     * @param string $escape
     *
     * @return CsvReader
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * Get escape
     * @return string $escape
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if ('zip' === strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION))) {
            $this->extractZipArchive();
        }
        parent::initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return parent::getConfigurationFields() + array(
            'encoding' => array(
                'options' => array(
                    'label' => 'pim_base_connector.import.encoding.label',
                    'help'  => 'pim_base_connector.import.encoding.help'
                )
            ),
            'delimiter' => array(
                'options' => array(
                    'label' => 'pim_base_connector.import.delimiter.label',
                    'help'  => 'pim_base_connector.import.delimiter.help'
                )
            ),
            'enclosure' => array(
                'options' => array(
                    'label' => 'pim_base_connector.import.enclosure.label',
                    'help'  => 'pim_base_connector.import.enclosure.help'
                )
            ),
            'escape' => array(
                'options' => array(
                    'label' => 'pim_base_connector.import.escape.label',
                    'help'  => 'pim_base_connector.import.escape.help'
                )
            ),
        );
    }

    /**
     * Extract the zip archive to be imported
     * @throws \RuntimeException When archive cannot be opened or extracted
     *                           or does not contain exactly one csv file
     */
    protected function extractZipArchive()
    {
        $archive = new \ZipArchive();

        $status = $archive->open($this->filePath);

        if ($status !== true) {
            throw new \RuntimeException(sprintf('Error "%d" occured while opening the zip archive.', $status));
        } else {
            $targetDir = sprintf(
                '%s/%s_%d_%s',
                pathinfo($this->filePath, PATHINFO_DIRNAME),
                pathinfo($this->filePath, PATHINFO_FILENAME),
                $this->stepExecution->getId(),
                md5(microtime() . $this->stepExecution->getId())
            );

            if ($archive->extractTo($targetDir) !== true) {
                throw new \RuntimeException('Error occured while extracting the zip archive.');
            }

            $archive->close();
            $this->extractedPath = $targetDir;

            $csvFiles = glob($targetDir . '/*.[cC][sS][vV]') + glob($targetDir . '/*.[xX][lL][sS][xXmM]');

            $csvCount = count($csvFiles);
            if (1 !== $csvCount) {
                throw new \RuntimeException(
                    sprintf(
                        'Expecting the root directory of the archive to contain exactly 1 csv/xlsx file, found %d',
                        $csvCount
                    )
                );
            }

            $this->filePath = current($csvFiles);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorOptions()
    {
        $options = parent::getIteratorOptions();
        if ('csv' === strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION))) {
            $options['parser_options'] = [
                'delimiter' => $this->delimiter,
                'enclosure' => $this->enclosure,
                'escape'    => $this->escape,
                'encoding'  => $this->encoding,
            ];
        }

        return $options;
    }
}
