<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductViolationNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($violation, $format = null, array $context = [])
    {
        if (!isset($context['product'])) {
            throw new \InvalidArgumentException('Expects a product context');
        }

        $product = $context['product'];

        if (!$product instanceof ProductInterface && !$product instanceof ProductTemplateInterface) {
            throw new \InvalidArgumentException('Expects a product or a product template as context');
        }

        $path = $violation->getPropertyPath();
        $codeStart = strpos($path, '[') + 1;
        $codeLength = strpos($path, ']') - $codeStart;
        $attributePath = substr($path, $codeStart, $codeLength);
        $productValue = $product->getValues()[$attributePath];

        return [
            'attribute' => $productValue->getAttribute()->getCode(),
            'locale'    => $productValue->getLocale(),
            'scope'     => $productValue->getScope(),
            'message'   => $violation->getMessage()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return in_array($format, $this->supportedFormats)
            && $data instanceof ConstraintViolationInterface
            && (
                $data->getRoot() instanceof ProductInterface
                || $data->getRoot() instanceof ProductTemplateInterface
            );
    }
}
