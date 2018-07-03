<?php
/**
 * Fieldthemes
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Fieldthemes.com license that is
 * available through the world-wide-web at this URL:
 * http://www.fieldthemes.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Fieldthemes
 * @package    Field_Productlist
 * @copyright  Copyright (c) 2014 Fieldthemes (http://www.fieldthemes.com/)
 * @license    http://www.fieldthemes.com/LICENSE-1.0.html
 */
namespace Field\Productlist\Model\Config\Source;
class EasingType implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{

		$easing_types = [
			"swing",
			"easeInQuad",
			"easeOutQuad",
			"easeInOutQuad",
			"easeInCubic",
			"easeOutCubic",
			"easeInOutCubic",
			"easeInQuart",
			"easeOutQuart",
			"easeInOutQuart",
			"easeInQuint",
			"easeOutQuint",
			"easeInOutQuint",
			"easeInSine",
			"easeOutSine",
			"easeInOutSine",
			"easeInExpo",
			"easeOutExpo",
			"easeInOutExpo",
			"easeInCirc",
			"easeOutCirc",
			"easeInOutCirc",
			"easeInElastic",
			"easeOutElastic",
			"easeInOutElastic",
			"easeInBack",
			"easeOutBack",
			"easeInOutBack",
			"easeInBounce",
			"easeOutBounce",
			"easeInOutBounce"];
		$easingType = [];
		foreach ($easing_types as $key => $value) {
			$type = [];
			$type['label'] = $type['value'] = $value;
			$easingType[] = $type;
		}
		return $easingType;
	}
}