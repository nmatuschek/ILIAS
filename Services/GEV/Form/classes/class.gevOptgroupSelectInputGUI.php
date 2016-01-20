<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';
include_once 'Services/Form/interfaces/interface.ilMultiValuesItem.php';

/**
* This class represents a selection list property in a property form.
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class gevOptgroupSelectInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
	protected $cust_attr = array();
	protected $options = array();
	protected $value;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("select");
	}

	/**
	* Set Options.
	*
	* @param	array	$a_options	Options. Array ("value" => "option_text")
	*/
	function setOptions($a_options)
	{
		$this->options = $a_options;
	}

	/**
	* Get Options.
	*
	* @return	array	Options. Array ("value" => "option_text")
	*/
	function getOptions()
	{
		return $this->options ? $this->options : array();
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		if($this->getMulti() && is_array($a_value))
		{						
			$this->setMultiValues($a_value);	
			$a_value = array_shift($a_value);		
		}	
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->value;
	}
	
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{		
		$this->setValue($a_values[$this->getPostVar()]);
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;

		$valid = true;
		if(!$this->getMulti())
		{
			$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
			if($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
			{
				$valid = false;
			}
		}
		else
		{
			foreach($_POST[$this->getPostVar()] as $idx => $value)
			{
				$_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
			}		
			$_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);

			if($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()])))
			{
				$valid = false;
			}
		}
		if (!$valid)
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return false;
		}
		return $this->checkSubItemsInput();
	}
	
	public function addCustomAttribute($a_attr)
	{
		$this->cust_attr[] = $a_attr;
	}
	
	public function getCustomAttributes()
	{
		return (array) $this->cust_attr;
	}

	/**
	* Render item
	*/
	function render($a_mode = "")
	{
		$tpl = new ilTemplate("tpl.gev_prop_optgroup_select.html", true, true, "Services/GEV/Form");
		
		foreach($this->getCustomAttributes() as $attr)
		{
			$tpl->setCurrentBlock('cust_attr');
			$tpl->setVariable('CUSTOM_ATTR',$attr);
			$tpl->parseCurrentBlock();
		}
		
		// determin value to select. Due to accessibility reasons we
		// should always select a value (per default the first one)
		$first = false;
		foreach($this->getOptions() as $option_group => $options) {
			foreach ($options as $option_value => $option) {
				if ((string) $option_value == (string) $this->getValue())
				{
					$sel_value = $option_value;
				}
			}
		}

		foreach($this->getOptions() as $option_group => $options)
		{
			$tpl->setCurrentBlock("prop_group");
			$tpl->setVariable("HEADER", ilUtil::prepareFormOutput($option_group));

			$options_html = "";
			foreach ($options as $option_value => $option_text) {
				$option_tpl = new ilTemplate("tpl.gev_prop_optgroup_select_option_row.html", true, true, "Services/GEV/Form");
				$option_tpl->setCurrentBlock("prop_select_option");
				$option_tpl->setVariable("VAL_SELECT_OPTION", ilUtil::prepareFormOutput($option_value));
				if((string) $sel_value == (string) $option_value)
				{
					$option_tpl->setVariable("CHK_SEL_OPTION",
						'selected="selected"');
				}
				$option_tpl->setVariable("TXT_SELECT_OPTION", $option_text);
				$option_tpl->parseCurrentBlock();

				$options_html .= $option_tpl->get();
			}

			$tpl->setVariable("OPTIONS", $options_html);
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("ID", $this->getFieldId());
		
		$postvar = $this->getPostVar();		
		if($this->getMulti() && substr($postvar, -2) != "[]")
		{
			$postvar .= "[]";
		}
		
		if ($this->getDisabled())
		{						
			if($this->getMulti())
			{
				$value = $this->getMultiValues();
				$hidden = "";	
				if(is_array($value))
				{
					foreach($value as $item)
					{
						$hidden .= $this->getHiddenTag($postvar, $item);
					}
				}
			}
			else
			{			
				$hidden = $this->getHiddenTag($postvar, $this->getValue());
			}			
			if($hidden)
			{
				$tpl->setVariable("DISABLED", " disabled=\"disabled\"");
				$tpl->setVariable("HIDDEN_INPUT", $hidden);
			}			
		}
		else
		{					
			$tpl->setVariable("POST_VAR", $postvar);
		}

		return $tpl->get();
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $this->render());
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Get HTML for table filter
	*/
	function getTableFilterHTML()
	{
		$html = $this->render();
		return $html;
	}

	/**
	* Get HTML for toolbar
	*/
	function getToolbarHTML()
	{
		$html = $this->render("toolbar");
		return $html;
	}

}
