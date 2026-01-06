<?php

/**
 * This file is a part of horstoeko/zugferd.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarketPress\German_Market\horstoeko\zugferd;

use MarketPress\German_Market\GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\BaseTypesHandler;
use MarketPress\German_Market\GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\XmlSchemaDateHandler;
use MarketPress\German_Market\horstoeko\stringmanagement\PathUtils;
use MarketPress\German_Market\horstoeko\zugferd\exception\ZugferdUnknownProfileIdException;
use MarketPress\German_Market\horstoeko\zugferd\exception\ZugferdUnknownProfileParameterException;
use MarketPress\German_Market\horstoeko\zugferd\jms\ZugferdTypesHandler;
use MarketPress\German_Market\horstoeko\zugferd\ZugferdObjectHelper;
use MarketPress\German_Market\horstoeko\zugferd\ZugferdProfileResolver;
use MarketPress\German_Market\JMS\Serializer\Exception\InvalidArgumentException;
use MarketPress\German_Market\JMS\Serializer\Exception\RuntimeException;
use MarketPress\German_Market\JMS\Serializer\Handler\HandlerRegistryInterface;
use MarketPress\German_Market\JMS\Serializer\SerializerBuilder;
use MarketPress\German_Market\JMS\Serializer\SerializerInterface;

/**
 * Class representing the document basics
 *
 * @category Zugferd
 * @package  Zugferd
 * @author   D. Erling <horstoeko@erling.com.de>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/horstoeko/zugferd
 */
class ZugferdDocument
{
    /**
     * @var integer $profileId Internal profile id
     */
    private $profileId = -1;

    /**
     * @var array $profileDefinition Internal profile definition
     */
    private $profileDefinition = [];

    /**
     * @var SerializerBuilder $serializerBuilder Serializer builder
     */
    private $serializerBuilder;

    /**
     * @var SerializerInterface $serializer Serializer
     */
    private $serializer;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\extended\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\rsm\CrossIndustryInvoice $invoiceObject The internal invoice object
     */
    private $invoiceObject;

    /**
     * @var ZugferdObjectHelper $objectHelper Object Helper
     */
    private $objectHelper;

    /**
     * Constructor
     *
     * @param  integer $profile The ID of the profile of the document
     * @return void
     * @throws ZugferdUnknownProfileIdException
     * @throws ZugferdUnknownProfileParameterException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    final protected function __construct(int $profile)
    {
        $this->initProfile($profile);
        $this->initObjectHelper();
        $this->initSerialzer();
    }

    /**
     * Returns the internal invoice object (created by the serializer). This is used e.g. in the validator
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\extended\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\rsm\CrossIndustryInvoice
     */
    protected function getInvoiceObject()
    {
        return $this->invoiceObject;
    }

    /**
     * Create a new instance of the internal invoice object
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\extended\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\rsm\CrossIndustryInvoice
     */
    protected function createInvoiceObject()
    {
        $this->invoiceObject = $this->getObjectHelper()->getCrossIndustryInvoice();

        return $this->invoiceObject;
    }

    /**
     * Get the instance of the internal serializuer
     *
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get object helper instance
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\ZugferdObjectHelper
     */
    protected function getObjectHelper()
    {
        return $this->objectHelper;
    }

    /**
     * Returns the selected profile id
     *
     * @return integer
     */
    public function getProfileId(): int
    {
        return $this->profileId;
    }

    /**
     * Returns the profile definition
     *
     * @return array
     */
    public function getProfileDefinition(): array
    {
        return $this->profileDefinition;
    }

    /**
     * Get a parameter from profile definition
     *
     * @param  string $parameterName
     * @return mixed
     * @throws ZugferdUnknownProfileParameterException
     */
    public function getProfileDefinitionParameter(string $parameterName)
    {
        $profileDefinition = $this->getProfileDefinition();

        if (isset($profileDefinition[$parameterName])) {
            return $profileDefinition[$parameterName];
        }

        throw new ZugferdUnknownProfileParameterException($parameterName);
    }

    /**
     * Sets the internal profile definitions
     *
     * @param  int $profile
     * @return ZugferdDocument
     * @throws ZugferdUnknownProfileIdException
     */
    protected function initProfile(int $profile): ZugferdDocument
    {
        $this->profileId = $profile;
        $this->profileDefinition = ZugferdProfileResolver::resolveProfileDefById($profile);

        return $this;
    }

    /**
     * Build the internal object helper
     *
     * @return ZugferdDocument
     */
    protected function initObjectHelper(): ZugferdDocument
    {
        $this->objectHelper = new ZugferdObjectHelper($this->profileId);

        return $this;
    }

    /**
     * Build the internal serialzer
     *
     * @return ZugferdDocument
     * @throws ZugferdUnknownProfileParameterException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function initSerialzer(): ZugferdDocument
    {
        $this->serializerBuilder = SerializerBuilder::create();

        $this->serializerBuilder->addMetadataDir(
            PathUtils::combineAllPaths(
                ZugferdSettings::getYamlDirectory(),
                $this->getProfileDefinitionParameter("name"),
                'qdt'
            ),
            sprintf(
                'MarketPress\German_Market\horstoeko\zugferd\entities\%s\qdt',
                $this->getProfileDefinitionParameter("name")
            )
        );
        $this->serializerBuilder->addMetadataDir(
            PathUtils::combineAllPaths(
                ZugferdSettings::getYamlDirectory(),
                $this->getProfileDefinitionParameter("name"),
                'ram'
            ),
            sprintf(
                'MarketPress\German_Market\horstoeko\zugferd\entities\%s\ram',
                $this->getProfileDefinitionParameter("name")
            )
        );
        $this->serializerBuilder->addMetadataDir(
            PathUtils::combineAllPaths(
                ZugferdSettings::getYamlDirectory(),
                $this->getProfileDefinitionParameter("name"),
                'rsm'
            ),
            sprintf(
                'MarketPress\German_Market\horstoeko\zugferd\entities\%s\rsm',
                $this->getProfileDefinitionParameter("name")
            )
        );
        $this->serializerBuilder->addMetadataDir(
            PathUtils::combineAllPaths(
                ZugferdSettings::getYamlDirectory(),
                $this->getProfileDefinitionParameter("name"),
                'udt'
            ),
            sprintf(
                'MarketPress\German_Market\horstoeko\zugferd\entities\%s\udt',
                $this->getProfileDefinitionParameter("name")
            )
        );

        if (ZugferdSettings::hasSerializerCacheDirectory()) {
            $this->serializerBuilder->setCacheDir(ZugferdSettings::getSerializerCacheDirectory());
        }

        $this->serializerBuilder->addDefaultListeners();
        $this->serializerBuilder->addDefaultHandlers();

        $this->serializerBuilder->configureHandlers(
            function (HandlerRegistryInterface $handler) {
                $handler->registerSubscribingHandler(new BaseTypesHandler());
                $handler->registerSubscribingHandler(new XmlSchemaDateHandler());
                $handler->registerSubscribingHandler(new ZugferdTypesHandler());
            }
        );

        $this->serializer = $this->serializerBuilder->build();

        return $this;
    }

    /**
     * Deserialize XML content to internal invoice object
     *
     * @param  mixed $xmlContent
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\extended\rsm\CrossIndustryInvoice|\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\rsm\CrossIndustryInvoice
     * @throws ZugferdUnknownProfileParameterException
     * @throws RuntimeException
     */
    public function deserialize($xmlContent)
    {
        $this->invoiceObject = $this->getSerializer()->deserialize($xmlContent, 'MarketPress\German_Market\horstoeko\zugferd\entities\\' . $this->getProfileDefinitionParameter("name") . '\rsm\CrossIndustryInvoice', 'xml');

        return $this->invoiceObject;
    }

    /**
     * Serialize internal invoice object as XML
     *
     * @return string
     * @throws RuntimeException
     */
    public function serializeAsXml(): string
    {
        return $this->getSerializer()->serialize($this->getInvoiceObject(), 'xml');
    }

    /**
     * Serialize internal invoice object as JSON
     *
     * @return string
     * @throws RuntimeException
     */
    public function serializeAsJson(): string
    {
        return $this->getSerializer()->serialize($this->getInvoiceObject(), 'json');
    }
}
