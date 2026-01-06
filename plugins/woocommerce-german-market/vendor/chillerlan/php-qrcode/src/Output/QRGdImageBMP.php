<?php
/**
 * Class QRGdImageBMP
 *
 * @created      25.10.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 *
 * Modified by MarketPress GmbH on 20-May-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\chillerlan\QRCode\Output;

use function imagebmp;

/**
 * GdImage bmp output
 *
 * @see \imagebmp()
 */
class QRGdImageBMP extends QRGdImage{

	public const MIME_TYPE = 'image/bmp';

	/**
	 * @inheritDoc
	 */
	protected function renderImage():void{
		imagebmp($this->image, null, ($this->options->quality > 0));
	}

}
