<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2018 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer\HTML\Element;

use PhpOffice\PhpWord\Settings;

use PhpOffice\PhpWord\Element\TrackChange;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\Writer\HTML\Style\Font as FontStyleWriter;
use PhpOffice\PhpWord\Writer\HTML\Style\Paragraph as ParagraphStyleWriter;

/**
 * TextRun element HTML writer
 *
 * @since 0.10.0
 */
class Title extends AbstractElement
{
    /**
     * Write heading
     *
     * @return string
     */
    public function write()
    {
        if (!$this->element instanceof \PhpOffice\PhpWord\Element\Title) {
            return '';
        }

        $tag = 'h' . $this->element->getDepth();

        $text = $this->element->getText();
        if (is_string($text)) {
            if (Settings::isOutputEscapingEnabled()) {
                $text = $this->escaper->escapeHtml($text);
            }
        } elseif ($text instanceof \PhpOffice\PhpWord\Element\AbstractContainer) {
            $writer = new Container($this->parentWriter, $text);
            $text = $writer->write();
        }

		// HOSTCMS
		$class = $this->element->getStyle();

		$style = '';
		if (method_exists($this->element, 'getParagraphStyle')) {
			$style = $this->getParagraphStyle();
		}

        $content = "<{$tag}{$style} class=\"{$class}\">{$text}</{$tag}>" . PHP_EOL;

        return $content;
    }
	
    /**
     * Write paragraph style
     *
     * @return string
     */
    private function getParagraphStyle()
    {
		
        /** @var \PhpOffice\PhpWord\Element\Text $element Type hint */
        $element = $this->element;
        $style = '';
        if (!method_exists($element, 'getParagraphStyle')) {
            return $style;
        }

        $paragraphStyle = $element->getParagraphStyle();
        $pStyleIsObject = ($paragraphStyle instanceof Paragraph);
        if ($pStyleIsObject) {
            $styleWriter = new ParagraphStyleWriter($paragraphStyle);
            $style = $styleWriter->write();
        } elseif (is_string($paragraphStyle)) {
            $style = $paragraphStyle;
        }
        if ($style) {
            $attribute = $pStyleIsObject ? 'style' : 'class';
            $style = " {$attribute}=\"{$style}\"";
        }

        return $style;
    }	
}
