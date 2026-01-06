<?php

namespace FlexibleCouponsProVendor\Mpdf;

use FlexibleCouponsProVendor\Mpdf\Color\ColorConverter;
use FlexibleCouponsProVendor\Mpdf\Color\ColorModeConverter;
use FlexibleCouponsProVendor\Mpdf\Color\ColorSpaceRestrictor;
use FlexibleCouponsProVendor\Mpdf\Fonts\FontCache;
use FlexibleCouponsProVendor\Mpdf\Fonts\FontFileFinder;
use FlexibleCouponsProVendor\Mpdf\Image\ImageProcessor;
use FlexibleCouponsProVendor\Mpdf\Pdf\Protection;
use FlexibleCouponsProVendor\Mpdf\Pdf\Protection\UniqidGenerator;
use FlexibleCouponsProVendor\Mpdf\Writer\BaseWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\BackgroundWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\ColorWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\BookmarkWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\FontWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\FormWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\ImageWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\JavaScriptWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\MetadataWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\OptionalContentWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\PageWriter;
use FlexibleCouponsProVendor\Mpdf\Writer\ResourceWriter;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
class ServiceFactory
{
    public function getServices(Mpdf $mpdf, LoggerInterface $logger, $config, $restrictColorSpace, $languageToFont, $scriptToLanguage, $fontDescriptor, $bmp, $directWrite, $wmf)
    {
        $sizeConverter = new SizeConverter($mpdf->dpi, $mpdf->default_font_size, $mpdf, $logger);
        $colorModeConverter = new ColorModeConverter();
        $colorSpaceRestrictor = new ColorSpaceRestrictor($mpdf, $colorModeConverter, $restrictColorSpace);
        $colorConverter = new ColorConverter($mpdf, $colorModeConverter, $colorSpaceRestrictor);
        $tableOfContents = new TableOfContents($mpdf, $sizeConverter);
        $cacheBasePath = $config['tempDir'] . '/mpdf';
        $cache = new Cache($cacheBasePath, $config['cacheCleanupInterval']);
        $fontCache = new FontCache(new Cache($cacheBasePath . '/ttfontdata', $config['cacheCleanupInterval']));
        $fontFileFinder = new FontFileFinder($config['fontDir']);
        $cssManager = new CssManager($mpdf, $cache, $sizeConverter, $colorConverter);
        $otl = new Otl($mpdf, $fontCache);
        $protection = new Protection(new UniqidGenerator());
        $writer = new BaseWriter($mpdf, $protection);
        $gradient = new Gradient($mpdf, $sizeConverter, $colorConverter, $writer);
        $formWriter = new FormWriter($mpdf, $writer);
        $form = new Form($mpdf, $otl, $colorConverter, $writer, $formWriter);
        $hyphenator = new Hyphenator($mpdf);
        $remoteContentFetcher = new RemoteContentFetcher($mpdf, $logger);
        $imageProcessor = new ImageProcessor($mpdf, $otl, $cssManager, $sizeConverter, $colorConverter, $colorModeConverter, $cache, $languageToFont, $scriptToLanguage, $remoteContentFetcher, $logger);
        $tag = new Tag($mpdf, $cache, $cssManager, $form, $otl, $tableOfContents, $sizeConverter, $colorConverter, $imageProcessor, $languageToFont);
        $fontWriter = new FontWriter($mpdf, $writer, $fontCache, $fontDescriptor);
        $metadataWriter = new MetadataWriter($mpdf, $writer, $form, $protection, $logger);
        $imageWriter = new ImageWriter($mpdf, $writer);
        $pageWriter = new PageWriter($mpdf, $form, $writer, $metadataWriter);
        $bookmarkWriter = new BookmarkWriter($mpdf, $writer);
        $optionalContentWriter = new OptionalContentWriter($mpdf, $writer);
        $colorWriter = new ColorWriter($mpdf, $writer);
        $backgroundWriter = new BackgroundWriter($mpdf, $writer);
        $javaScriptWriter = new JavaScriptWriter($mpdf, $writer);
        $resourceWriter = new ResourceWriter($mpdf, $writer, $colorWriter, $fontWriter, $imageWriter, $formWriter, $optionalContentWriter, $backgroundWriter, $bookmarkWriter, $metadataWriter, $javaScriptWriter, $logger);
        return ['otl' => $otl, 'bmp' => $bmp, 'cache' => $cache, 'cssManager' => $cssManager, 'directWrite' => $directWrite, 'fontCache' => $fontCache, 'fontFileFinder' => $fontFileFinder, 'form' => $form, 'gradient' => $gradient, 'tableOfContents' => $tableOfContents, 'tag' => $tag, 'wmf' => $wmf, 'sizeConverter' => $sizeConverter, 'colorConverter' => $colorConverter, 'hyphenator' => $hyphenator, 'remoteContentFetcher' => $remoteContentFetcher, 'imageProcessor' => $imageProcessor, 'protection' => $protection, 'languageToFont' => $languageToFont, 'scriptToLanguage' => $scriptToLanguage, 'writer' => $writer, 'fontWriter' => $fontWriter, 'metadataWriter' => $metadataWriter, 'imageWriter' => $imageWriter, 'formWriter' => $formWriter, 'pageWriter' => $pageWriter, 'bookmarkWriter' => $bookmarkWriter, 'optionalContentWriter' => $optionalContentWriter, 'colorWriter' => $colorWriter, 'backgroundWriter' => $backgroundWriter, 'javaScriptWriter' => $javaScriptWriter, 'resourceWriter' => $resourceWriter];
    }
}
