<?php

/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2014-2016 http://www.oneall.com - All rights reserved
 * @license   	GNU/GPL 2 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */

class OneAll_SocialLogin_Block_Adminhtml_System_Config_Fieldset_General extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
	protected function _getHeaderHtml ($element)
	{
		if (method_exists ($this, '_getHeaderTitleHtml'))
		{
			if ($element->getIsNested ())
			{
				$html = '<tr class="nested"><td colspan="4"><div class="' . $this->_getFrontendClass ($element) . '">';
			}
			else
			{
				$html = '<div class="' . $this->_getFrontendClass ($element) . '">';
			}
			
			$html .= $this->_getHeaderTitleHtml ($element);
			$html .= '<input id="' . $element->getHtmlId () . '-state" name="config_state[' . $element->getId () . ']" type="hidden" value="' . (int) $this->_getCollapseState ($element) . '" />';
			$html .= '<fieldset class="' . $this->_getFieldsetCss ($element) . '" id="' . $element->getHtmlId () . '">';
			$html .= '<legend>' . $element->getLegend () . '</legend>';
			$html .= $this->_getHeaderCommentHtml ($element);

			// field label column
			$html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
			if ($this->getRequest ()->getParam ('website') || $this->getRequest ()->getParam ('store'))
			{
				$html .= '<colgroup class="use-default" />';
			}
			$html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
		}
		else
		{
			$default = ! $this->getRequest ()->getParam ('website') && ! $this->getRequest ()->getParam ('store');
			$html = '<div  class="entry-edit-head collapseable" >';
			$html .= '<a id="' . $element->getHtmlId () . '-head" href="#" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId () . '\', \'' . $this->getUrl ('*/*/state') . '\'); return false;">' . $element->getLegend () . '</a></div>';
			$html .= '<input id="' . $element->getHtmlId () . '-state" name="config_state[' . $element->getId () . ']" type="hidden" value="' . (int) $this->_getCollapseState ($element) . '" />';
			$html .= '<fieldset class="' . $this->_getFieldsetCss () . '" id="' . $element->getHtmlId () . '">';
			$html .= '<legend>' . $element->getLegend () . '</legend>';

			if ($element->getComment ())
			{
				$html .= '<div class="comment">' . $element->getComment () . '</div>';
			}
			
			// Field label column
			$html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
			if (! $default)
			{
				$html .= '<colgroup class="use-default" />';
			}
			$html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
		}
		return $html;
	}

	protected function _getFieldsetCss ($element = null)
	{
		$configCss = (string) $this->getGroup ($element)->fieldset_css;
		return 'config collapseable' . ($configCss ? ' ' . $configCss : '');
	}
}
