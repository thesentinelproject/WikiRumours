<?php

	class form_TL {

		private $style = 'horizontal'; // alternate styles are "stacked", "inline" and "no-label"

		public function styleForm($style) {
			$this->style = $style;
		}

		public function input($type, $name, $value = null, $mandatory = false, $labelPlaceholder = null, $class = null, $options = null, $maxlength = null, $otherAttributes = null, $truncateLabel = null, $eventHandlers = null) {

			global $parser;
			global $console;

			if (!$type) {
				$console .= __CLASS__ . "->" . __FUNCTION__ . ": No element type specified.\n";
				return false;
			}
			
			if ($labelPlaceholder) {
				$result = explode('|', $labelPlaceholder);
				if (isset($result[0])) $label = $result[0];
				if (isset($result[1])) $placeholder = $result[1];
			}
			
			if (!isset($label)) $label = null;
			if (!isset($placeholder)) $placeholder = null;

			if (($this->style == 'inline' || $this->style == 'no-label') && !$placeholder) $placeholder = $label;
			
			switch($type) {
				/* ------------------------- */
					case 'text':
					case 'password':
					case 'hidden':
					case 'number':
					case 'number_without_spin_buttons':
					case 'url':
					case 'email':
					case 'tel':
					case 'title':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($type == 'number' && $value && !is_numeric($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): Number field must contain a numeric value.\n";
								return false;
							}
							if ($type == 'email' && $value && substr_count($value, '@') < 1 && substr_count($value, '.') < 1) {
								$console .= __FUNCTION__ . " (" . $name . "): Email value is invalid.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							if ($type == 'number_without_spin_buttons' || $type == 'title') $field = "<input type='text' name='" . $name . "' id='" . $name . "'";
							else $field = "<input type='" . $type . "' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($type == 'title') $field .= " class='" . trim('autoCapitalize ' . $class) . "'";
							elseif ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($type == 'number') {
								$eventHandlers['onChange'] = "if (isNaN(this.value)) this.value = 0; " . $eventHandlers['onChange'];
								if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . floatval($otherAttributes['max']) . "; } " . $eventHandlers['onChange'];
								if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . floatval($otherAttributes['min']) . "; } " . $eventHandlers['onChange'];
							}
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'textarea':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							$field = "<textarea name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">" . $value . "</textarea>";
							return $field;
							break;
				/* ------------------------- */
					case 'uneditable':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if (is_null($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): No value specified.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
						// return
							$field = "<span";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							$field .= ">";
							$field .= "<span name='" . $name . "_visible' id='" . $name . "_visible'>". htmlspecialchars($value, ENT_QUOTES) . "</span>";
							$field .= "<input type='hidden' name='" . $name . "' id='" . $name . "'";
							$field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							$field .= " />";
							$field .= "</span>";
							return $field;
							break;
				/* ------------------------- */
					case 'uneditable_static':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if (is_null($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): No value specified.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
						// return
							$field = "<div class='form-control-static";
							if ($class) $field .= " " . $class;
							$field .= "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							$field .= ">";
							$field .= "<span name='" . $name . "_visible' id='" . $name . "_visible'>". $value . "</span>";
							$field .= "<input type='hidden' name='" . $name . "' id='" . $name . "'";
							$field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'search':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							$field = "<input type='search' name='" . $name . "' id='" . $name . "' results='0'";
							$field .= " placeholder='" . ($placeholder ? htmlspecialchars($placeholder, ENT_QUOTES) : "Search") . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'password_with_preview':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							$field = "<div id='password_preview_close_" . $name . "'><div class='input-group'><div class='input-group-addon'><a href='' onClick=" . '"' . "document.getElementById('password_preview_close_" . $name . "').className='hidden'; document.getElementById('password_preview_open_" . $name . "').className='visible'; document.getElementById('preview_" . $name . "').value=document.getElementById('" . $name . "').value; return false;" . '"' . "><span class='glyphicon glyphicon-eye-close' aria-hidden='true'></span></a></div>";
							$field .= "<input type='password' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " /></div></div>";

							$field .= "<div id='password_preview_open_" . $name . "' class='hidden'><div class='input-group'><div class='input-group-addon'><a href='' onClick=" . '"' . "document.getElementById('password_preview_close_" . $name . "').className='visible'; document.getElementById('password_preview_open_" . $name . "').className='hidden'; return false;" . '"' . "><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span></a></div>";
							$field .= "<input type='text' name='preview_" . $name . "' id='preview_" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " disabled /></div></div>";
							return $field;
							break;
				/* ------------------------- */
					case 'password_with_health_meter':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							$field = "<div><input type='password' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " /></div>";
							$field .= "<div id='healthMeterContainer' class='hidden'><div id='healthMeter' class='" . $class . "'></div></div>";
							return $field;
							break;
				/* ------------------------- */
						case 'select':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if (count($options) < 1) {
								$console .= __FUNCTION__ . " (" . $name . "): No options to display in select box.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							$field = "<select name='" . $name;
							if (!is_null(@$otherAttributes['multiple'])) $field .= "[]";
							$field .= "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							if ($placeholder) {
								$field .= "<option value=''>" . $placeholder . "</option>";
								$field .= "<option value=''>--</option>";
							}
							elseif (!$mandatory) $field .= "<option value=''></option>";
							foreach ($options as $optionValue => $optionLabel) {
								$field .= "<option value='" . $optionValue . "'";
								if ($value && is_array($value)) {
									foreach ($value as $id=>$matchValue) {
										if ($optionValue == $id) $field .= " selected";
									}
								}
								elseif ($value) {
									if ($optionValue == $value) $field .= " selected";
								}
								$field .= ">";
								if ($truncateLabel && $truncateLabel < strlen($optionLabel) - 3) $optionLabel = substr($optionLabel, 0, $truncateLabel) . '...';
								$field .= $optionLabel;
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
						case 'yesno_bootstrap_switch':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							$field = '';
							$value = floatval(@$value);
						// return
							$field .= "<input type='checkbox' name='" . $name . "' id='" . $name . "' class='checkboxSwitch'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if (!@$otherAttributes['data-on-text'] && !@$otherAttributes['data-off-text']) $field .= " data-on-text='YES' data-off-text='NO'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($value) $field .= " checked";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'percentage':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($value && !is_numeric($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): Percentage must be a numeric value.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "if (isNaN(this.value)) { this.value = 0; } if (parseInt(this.value) > 100) { this.value = 100; } if (parseInt(this.value) < 0) { this.value = 0; } " . @$eventHandlers['onChange'];
						// return
							$field = "<input type='number' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value) $field .= " value='" . floatval($value) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'percentage_bootstrap':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($value && !is_numeric($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): Percentage must be a numeric value.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "if (isNaN(this.value)) { this.value = 0; } if (parseInt(this.value) > 100) { this.value = 100; } if (parseInt(this.value) < 0) { this.value = 0; } " . @$eventHandlers['onChange'];
						// return
							$field = "<div class='input-group'>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim('form-control ' . $class) . "'";
							if ($value) $field .= " value='" . floatval($value) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "<span class='input-group-addon'>%</span></div>";
							return $field;
							break;
				/* ------------------------- */
					case 'dollars':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							$value = str_replace(',', '', $value);
							if ($value && !is_numeric($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): Currency must be a numeric value.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "this.value = this.value.replace(/,/g, " . '""' . "); if (isNaN(this.value)) { this.value = " . '"0.00"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . '"' . number_format($otherAttributes['max'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . '"' . number_format($otherAttributes['min'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
						// return
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value) $field .= " value='" . number_format($value, 2) . "'";
							else $field .= " value='0.00'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'dollars_bootstrap':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							$value = str_replace(',', '', $value);
							if ($value && !is_numeric($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): Currency must be a numeric value.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "this.value = this.value.replace(/,/g, " . '""' . "); if (isNaN(this.value)) { this.value = " . '"0.00"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . '"' . number_format($otherAttributes['max'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . '"' . number_format($otherAttributes['min'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
						// return
							$field = "<div class='input-group'><span class='input-group-addon'>$</span>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim('form-control ' . $class) . "'";
							if ($value) $field .= " value='" . number_format($value, 2) . "'";
							else $field .= " value='0.00'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'pounds_bootstrap':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							$value = str_replace(',', '', $value);
							if ($value && !is_numeric($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): Currency must be a numeric value.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "this.value = this.value.replace(/,/g, " . '""' . "); if (isNaN(this.value)) { this.value = " . '"0.00"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . '"' . number_format($otherAttributes['max'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . '"' . number_format($otherAttributes['min'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
						// return
							$field = "<div class='input-group'><span class='input-group-addon'>&pound;</span>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim('form-control ' . $class) . "'";
							if ($value) $field .= " value='" . number_format($value, 2) . "'";
							else $field .= " value='0.00'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'yen_bootstrap':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							$value = str_replace(',', '', $value);
							if ($value && !is_numeric($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): Currency must be a numeric value.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "this.value = this.value.replace(/,/g, " . '""' . "); if (isNaN(this.value)) { this.value = " . '"0.00"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . '"' . number_format($otherAttributes['max'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . '"' . number_format($otherAttributes['min'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
						// return
							$field = "<div class='input-group'><span class='input-group-addon'>&yen;</span>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim('form-control ' . $class) . "'";
							if ($value) $field .= " value='" . floatval($value) . "'";
							else $field .= " value='0.00'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'currency':
					case 'currency_full':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
							global $currencies_TL;
							if (!count(@$currencies_TL)) {
								$console .= __FUNCTION__ . " (" . $name . "): Unable to load currencies_TL.\n";
								return false;
							}
						// initialize
							if (!$name) $name = 'currency';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							foreach ($currencies_TL as $currencyID => $currency) {
								$field .= "<option value='" . $currencyID . "'";
								if ($value && $currencyID == $value) $field .= " selected";
								$field .= ">";
								if ($type == 'currency_full') {
									if ($truncateLabel > strlen($currency) - 3) $currency = substr($currency, 0, $truncateLabel) . '...';
									$field .= $currency;
								}
								else $field .= $currencyID;
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'radio':
					case 'radio_stacked':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if (count($options) < 1) {
								$console .= __FUNCTION__ . " (" . $name . "): No options to display as radio buttons.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							$field = '';
							$increment = 0;
						// return
							foreach ($options as $optionValue => $optionLabel) {
								if ($type == 'radio_stacked') $field .= "<div class='radio'>";
								if ($type == 'radio_stacked') $field .= "<label>";
								else $field .= "<label class='radio-inline'>";
								$field .= "<input type='radio' name='" . $name . "' id='" . $name . "_" . $increment . "'";
								$field .= " value='" . $optionValue . "'";
								if ($value == $optionValue) $field .= " checked";
								if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
								if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
								if ($mandatory) $field .= " required";
								$field .= " /> ";
								$field .= $optionLabel;
								$field .= "</label>";
								if ($type == 'radio_stacked') $field .= "</div>";
								$increment++;
							}
							return $field;
							break;
				/* ------------------------- */
					case 'checkbox':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							$field = '';
						// return
							$field .= "<input type='checkbox' name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value) $field .= " checked='checked'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " /> " . $label;
							return $field;
							break;
				/* ------------------------- */
					case 'checkbox_stacked_bootstrap':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							$field = '';
						// return
							$field .= "<div class='checkbox'><label>";
							$field .= "<input type='checkbox' name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value) $field .= " checked='checked'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " /> " . $label;
							$field .= "</label></div>";
							return $field;
							break;
				/* ------------------------- */
					case 'date':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							$dateRegex = "^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$";
						// return
							$field = "<input type='text' name='" . $name . "' id='" . $name . "' placeholder='YYYY-MM-DD' pattern='" . $dateRegex . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value && $value != '0000-00-00') $field .= " value='" . $value . "' ";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'hoursminutes':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							$dateRegex = "^((([0]?[1-9]|1[0-2])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?( )?(AM|am|aM|Am|PM|pm|pM|Pm))|(([0]?[0-9]|1[0-9]|2[0-3])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?))$";
						// return
							$field = "<input type='text' name='" . $name . "' id='" . $name . "' class='" . trim("input-small " . $class) . "' placeholder='HH:MM PM' pattern='" . $dateRegex . "'";
							if ($value) $field .= " value='" . $value . "' ";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'datetime_with_picker':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							$field = "<div id='" . $name . "' class='input-group date form_datetime' data-date-format='yyyy-mm-dd hh:ii:ss' data-link-format='yyyy-mm-dd hh:ii:ss' data-link-field='" . $name . "'>\n";
							$field .= "<input type='text' id='" . $name . "' name='" . $name . "' maxlength='19'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "<span class='input-group-addon'>&nbsp;<span class='glyphicon glyphicon-calendar'></span></span>";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'year':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($value && !is_numeric($value)) {
								$console .= __FUNCTION__ . " (" . $name . "): Year must be a numeric value.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							$dateRegex = "^[0-9]{4}$";
							if (!$eventHandlers && (!is_null(@$otherAttributes['max']) || !is_null(@$otherAttributes['min']))) $eventHandlers = array();
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") this.value = " . intval($otherAttributes['max']) . "; " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") this.value = " . intval($otherAttributes['min']) . "; " . @$eventHandlers['onChange'];
						// return
							$field = "<input type='number' name='" . $name . "' id='" . $name . "' class='input-mini' placeholder='YYYY' pattern='" . $dateRegex . "' maxlength='4'";
							if (floatval($value)) $field .= " value='" . floatval($value) . "' ";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'timezone':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							if (!$name) $name = 'timezone';
							$timezones = array();
							$timestamp = time();
							foreach(timezone_identifiers_list() as $key => $zone) {
								date_default_timezone_set($zone);
								$timezones[$key]['zone'] = $zone;
								$timezones[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
							}
						// return
							$field = "<select name='" . $name;
							if (!is_null(@$otherAttributes['multiple'])) $field .= "[]";
							$field .= "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							if ($placeholder) {
								$field .= "<option value=''>" . $placeholder . "</option>";
								$field .= "<option value=''>--</option>";
							}
							elseif (!$mandatory) $field .= "<option value=''></option>";
							foreach($timezones as $t) {
								$field .= "<option value='" . $t['zone'] . "'";
								if ($value && is_array($value)) {
									foreach ($value as $matchValue) {
										if ($t['zone'] == $matchValue) $field .= " selected";
									}
								}
								elseif ($value) {
									if ($t['zone'] == $value) $field .= " selected";
								}
								$field .= ">";
								$field .= $t['zone'] . " (" . $t['diff_from_GMT'] . ")";
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'file':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							$field = "<input type='file' name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							@$eventHandlers['onChange'] .= 'if (this.files[0]) { document.getElementById("' . $name . '_filesize").value = this.files[0].size; } ';
							$eventHandlers['onChange'] .= 'if (this.files[0]) { document.getElementById("' . $name . '_mime").value = this.files[0].type; } ';
							$eventHandlers['onChange'] .= 'var _URL = window.URL || window.webkitURL; ';
							$eventHandlers['onChange'] .= 'var file, img; ';
							$eventHandlers['onChange'] .= 'if ((file = document.getElementById("' . $name . '").files[0])) { ';
							$eventHandlers['onChange'] .= 'img = new Image(); ';
							$eventHandlers['onChange'] .= 'img.onload = function() { ';
							$eventHandlers['onChange'] .= 'if (document.getElementById("' . $name . '_width")) document.getElementById("' . $name . '_width").value = img.width; ';
							$eventHandlers['onChange'] .= 'if (document.getElementById("' . $name . '_height")) document.getElementById("' . $name . '_height").value = img.height; ';
							$eventHandlers['onChange'] .= '}; ';
							$eventHandlers['onChange'] .= 'img.src = _URL.createObjectURL(file); ';
							$eventHandlers['onChange'] .= '}';
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "<input type='hidden' name='" . $name . "_filesize' id='" . $name . "_filesize' />";
							$field .= "<input type='hidden' name='" . $name . "_mime' id='" . $name . "_mime' />";
							$field .= "<input type='hidden' name='" . $name . "_width' id='" . $name . "_width' />";
							$field .= "<input type='hidden' name='" . $name . "_height' id='" . $name . "_height' />";
							return $field;
							break;
				/* ------------------------- */
					case 'range':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
							$max = @$otherAttributes['max'];
							unset($otherAttributes['max']);
							if (!$max) {
								$console .= __FUNCTION__ . " (" . $name . "): Range requires a maximum value.\n";
								return false;
							}
						// return
							$field = "<input type='range' name='" . $name . "' id='" . $name . "' max='" . $max . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'country':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
							if (!$options) {
								global $countries_TL;
								if (!count(@$countries_TL)) {
									$console .= __FUNCTION__ . " (" . $name . "): Unable to load countries_TL.\n";
									return false;
								}
								else $options = $countries_TL;
							}
						// initialize
							if (!$name) $name = 'country';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							foreach ($options as $countryID => $country) {
								$field .= "<option value='" . $countryID . "'";
								if ($value && $countryID == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($country) - 3) $country = substr($country, 0, $truncateLabel) . '...';
								$field .= $country;
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'region' . substr($type, -3):
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
							global $regions_TL;
							$country = substr($type, -2);
							if (!count(@$regions_TL[$country])) {
								$console .= __FUNCTION__ . " (" . $name . "): Unable to load regions_TL[country].\n";
								return false;
							}
						// initialize
							if (!$name) $name = $type;
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							for ($counter = 0; $counter < count($regions_TL[$country]['regions']); $counter++) {
								$field .= "<option value='" . $regions_TL[$country]['regions'][$counter]['region_id'] . "'";
								if ($value && $regions_TL[$country]['regions'][$counter]['region_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($regions_TL[$country]['regions'][$counter]['region']) - 3) $regions_TL[$country]['regions'][$counter]['region'] = substr($regions_TL[$country]['regions'][$counter]['region'], 0, $truncateLabel) . '...';
								$field .= $regions_TL[$country]['regions'][$counter]['region'];
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'language':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
							global $languages_TL;
							if (!count(@$languages_TL)) {
								$console .= __FUNCTION__ . " (" . $name . "): Unable to load languages_TL.\n";
								return false;
							}
						// initialize
							if (!$name) $name = 'language';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							foreach ($languages_TL as $languageID => $language) {
								$field .= "<option value='" . $languageID . "'";
								if ($value && $languageID == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($language) - 3) $language = substr($language, 0, $truncateLabel) . '...';
								$field .= $language;
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'multipicker':
						// validate
							if (!$name) {
								$console .= __CLASS__ . "->" . __FUNCTION__ . ": No id/name specified for " . $type . " field.\n";
								return false;
							}
							if (count($options) < 1) {
								$console .= __FUNCTION__ . " (" . $name . "): No options to display in multipicker.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							$valueDelimiter = ';';
							$increment = 0;
						// return
							$field = "<div style='padding: 4px; border: 1px solid #ddd; overflow: scroll; overflow-x: hidden;'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							$field .= ">";
							foreach ($options as $optionValue => $optionLabel) {
								if (substr_count($value, $optionValue)) {
									$field .= "<a href='javascript:void(0)' id='multipickerLink_" . $increment . "' style='font-weight: bold; text-decoration: none;' onClick='if (document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight == " . '"normal"' . ") { document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight = " . '"bold"' . "; } else { document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight = " . '"normal"' . "; } if (document.getElementById(" . '"' . $name . '"' . ").value.indexOf(" . '"' . $optionValue . '"' . ") > -1) { document.getElementById(" . '"' . $name . '"' . ").value = document.getElementById(" . '"' . $name . '"' . ").value.replace(" . '"' . $valueDelimiter . $optionValue . '"' . ", " . '""' . "); document.getElementById(" . '"' . $name . '"' . ").value = document.getElementById(" . '"' . $name . '"' . ").value.replace(" . '"' . $optionValue . '"' . ", " . '""' . "); } else { if (!document.getElementById(" . '"' . $name . '"' . ").value) { document.getElementById(" . '"' . $name . '"' . ").value = " . '"' . $optionValue . '"' . "; } else { document.getElementById(" . '"' . $name . '"' . ").value += " . '"' . $valueDelimiter . $optionValue . '"' . "; } } return false;'>";
									$field .= $optionLabel;
									$field .= "</a> &nbsp; ";
								}
								else {
									$field .= "<a href='javascript:void(0)' id='multipickerLink_" . $increment . "' style='font-weight: normal; text-decoration: none;' onClick='if (document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight == " . '"normal"' . ") { document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight = " . '"bold"' . "; } else { document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight = " . '"normal"' . "; } if (document.getElementById(" . '"' . $name . '"' . ").value.indexOf(" . '"' . $optionValue . '"' . ") > -1) { document.getElementById(" . '"' . $name . '"' . ").value = document.getElementById(" . '"' . $name . '"' . ").value.replace(" . '"' . $valueDelimiter . $optionValue . '"' . ", " . '""' . "); document.getElementById(" . '"' . $name . '"' . ").value = document.getElementById(" . '"' . $name . '"' . ").value.replace(" . '"' . $optionValue . '"' . ", " . '""' . "); } else { if (!document.getElementById(" . '"' . $name . '"' . ").value) { document.getElementById(" . '"' . $name . '"' . ").value = " . '"' . $optionValue . '"' . "; } else { document.getElementById(" . '"' . $name . '"' . ").value += " . '"' . $valueDelimiter . $optionValue . '"' . "; } } return false;'>";
									$field .= $optionLabel;
									$field .= "</a> &nbsp; ";
								}
								$increment++;
							}
							$field .= "</div>";
							$field .= "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $value . "' />";
							return $field;
							break;
				/* ------------------------- */
					case 'button':
					case 'submit':
					case 'reset':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// initialize
							if (!$label && $type == 'button') $label = 'Go';
							if (!$label && $type == 'submit') $label = 'Submit';
						// return
							$field = "<button type='" . $type . "'";
							if ($name) $field .= " name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							$field .= ">";
							$field .= $label;
							$field .= "</button>";
							return $field;
							break;
				/* ------------------------- */
					case 'image':
						// validate
							if (!$otherAttributes['src']) {
								$console .= __FUNCTION__ . " (" . $name . "): No image for submit button.\n";
								return false;
							}
							if ($otherAttributes && !is_array($otherAttributes)) {
								$console .= __FUNCTION__ . " (" . $name . "): otherAttributes must be an array.\n";
								return false;
							}
							if ($eventHandlers && !is_array($eventHandlers)) {
								$console .= __FUNCTION__ . " (" . $name . "): eventHandlers must be an array.\n";
								return false;
							}
						// return
							$field = "<input type='image'";
							$field .= " src='" . $otherAttributes['src'] . "'";
							if ($name) $field .= " name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'cancel_and_return':
						// initialize
							if (!$label) $label = 'Cancel';
						// return
							$field = "<input type='button'";
							if ($name) $field .= " name='" . $name . "' id='" . $name . "'";
							$field .= " value='" . htmlspecialchars($label, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes && is_array($otherAttributes)) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							@$eventHandlers['onClick'] .= "window.history.back(); return false;";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'honeypot':
						// validate
							if (!$name) break; // no name
						// return
							$field = "<style>.hpt{display:none;}</style><div class='hpt'>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "' />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'timer':
						// validate
							if (!$name) break; // no name
						// return
							$field = "<style>.hpt{display:none;}</style><div class='hpt'>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "' value='" . time() . "' />";
							$field .= "</div>";
							return $field;
							break;
			}
			
		}
	
		public function row($type, $name, $value = null, $mandatory = false, $labelPlaceholder = null, $class = null, $options = null, $maxlength = null, $otherAttributes = null, $truncateLabel = null, $eventHandlers = null, $duplicateRows = null) {

			global $regions_TL;
			global $operators;
			global $console;

			if (!$type) {
				$console .= __CLASS__ . "->" . __FUNCTION__ . ": No element type specified.\n";
				return false;
			}

			if ($labelPlaceholder) {
				$result = explode('|', $labelPlaceholder);
				if (isset($result[0])) $label = $result[0];
				if (isset($result[1])) $placeholder = $result[1];
			}
			
			if (!isset($label)) $label = null;
			if (!isset($placeholder)) $placeholder = null;
			
			if (floatval($truncateLabel)) $label = $parser->truncate($label, 'character', $truncateLabel, '', '', '');
			
			if ($type == 'checkbox' || $type == 'checkbox_stacked_bootstrap' || $type == 'button' || $type == 'submit' || $type == 'cancel' || $type == 'cancel_and_return') $label = null;

			if ($type == 'country_and_region') {
				$row = $this->row('country', (@$name['country'] ? $name['country'] : 'country'), @$value['country_id'], $mandatory, 'Country', $class, $options, null, $otherAttributes, $truncateLabel, array('onChange'=>'$(".' . (@$name['region'] ? $name['region'] . '_' : false) . 'regions_TL").hide(); if (document.getElementById("' . (@$name['region'] ? $name['region'] . '_' : false) . 'region_" + this.value)) { $("#' . (@$name['region'] ? $name['region'] . '_' : false) . 'region_" + this.value + "_container").show(); }'));
				foreach ($regions_TL as $country => $regionArray) {
					$row .= "<div id='" . (@$name['region'] ? $name['region'] . '_' : false) . "region_" . $country . "_container' class='" . (@$name['region'] ? $name['region'] . '_' : false) . "regions_TL collapse" .  ($country == @$value['country_id'] ? " in" : false) . "'>\n";
					$row .= $this->row('region_' . $country, (@$name['region'] ? $name['region'] . '_' : false) . 'region_' . $country, @$value['region_id'], false, $operators->firstTrue(ucwords($regions_TL[$country]['subdivision']), 'Region'), $class, null, null, $otherAttributes, $truncateLabel, $eventHandlers);
					$row .= "</div>\n";
				}
				$row .= "<div id='region_other'>\n";
				$row .= $this->row('text', (@$name['region'] ? $name['region'] . '_' : false) . 'other_region', @$value['other_region'], false, 'Other region', $class, null, null, $otherAttributes, $truncateLabel, $eventHandlers);
				$row .= "</div>\n";
			}
			else {

				$row = $this->rowStart($name, $label);
				$row .= "      " . $this->input($type, $name, $value, $mandatory, $labelPlaceholder, $class, $options, $maxlength, $otherAttributes, $truncateLabel, $eventHandlers) . "\n";
				
				if ($duplicateRows) {
					for ($counter = 1; $counter <= $duplicateRows; $counter++) {
						$row .= "      <div id='expandingRow_" . $counter . "'></div>\n";
					}
					
					$row .= "      <div id='rowLink'>\n";
					$row .= "        <a href='javascript:void(0)' onClick='addRow(" . $duplicateRows . "); return false;'>Add more...</a>\n";
					$row .= "        <input type='hidden' name='numberOfRows' id='numberOfRows' value='0' />\n";
					$row .= "      </div>\n";
					
					$row .= "      <script type='text/javascript'>\n";
					$row .= "        function addRow(maxRows) {\n";
					$row .= "          document.getElementById('numberOfRows').value++;\n";
					$row .= "          if (document.getElementById('numberOfRows').value > maxRows) alert('Maximum number of rows reached.');\n";
					$row .= "          else {\n";
					$row .= "            var field =\n";
					$row .= "              " . '"' . $this->input($type, $name, $value, $mandatory, $labelPlaceholder, $class, $options, $maxlength, $otherAttributes, $truncateLabel, $eventHandlers) . '"' . ";\n";
					$row .= "            document.getElementById('expandingRow_' + document.getElementById('numberOfRows').value).innerHTML = field.replace(" . '"' . $name . '", "' . $name . '"' . " + " . '"_"' . " + document.getElementById('numberOfRows').value);\n";
					$row .= "          }\n";
					$row .= "        }\n";
					$row .= "      </script>\n";
		
				}

				$row .= $this->rowEnd();

			}
			
			return $row;

		}

		public function rowStart($name = null, $label = null, $truncate = false, $class = null, $stacked = true) {

			global $operators;
			global $parser;

			if (!isset($name)) $name = null;
			if (!isset($label)) $label = null;
			
			if (floatval($truncate)) $label = $parser->truncate($label, 'character', $truncate, '', '', '');
			
			$row = "<!-- " . $operators->firstTrue($label, $name) . " -->\n";
			$row .= "  <div id='" . trim('formContainer_' . $name, '_') . "' class='form-group" . ($class ? ' ' . $class : false) . "'>\n";

			if ($this->style == 'horizontal') {
				$row .= "    <label for='" . $name . "' id='" . trim('formLabel_' . $name, '_') . "' class='col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label'>" . $label . "</label>\n";
				$row .= "    <div id='" . trim('formField_' . $name, '_') . "' class='col-lg-9 col-md-9 col-sm-8 col-xs-12'>\n";
			}
			elseif ($this->style == 'stacked' || $this->style == 'inline') {
				$row .= "    <label for='" . $name . "' id='" . trim('formLabel_' . $name, '_') . "' class='control-label'>" . $label . "</label>\n";
			}

			return $row;
			
		}
			
		public function rowEnd($stacked = true) {
		
			$row = null;

			if ($this->style == 'horizontal') $row .= "    </div>\n";

			$row .= "  </div>\n";
						
			return $row;
			
		}
		
		public function start($name = null, $action = null, $method = 'post', $class = null, $otherAttributes = null, $eventHandlers = null) {
			
			if (!$this->style) {
				$console .= __CLASS__ . "->" . __FUNCTION__ . ": No form style specified.\n";
				return false;
			}
			elseif ($this->style == 'horizontal') $class = trim('form-horizontal ' . $class);
			elseif ($this->style == 'inline') $class = trim('form-inline ' . $class);

			$field = "<form role='form'";
			if ($name) $field .= " name='" . $name . "' id='" . $name . "'";
			if ($action) $field .= " action='" . $action . "'";
			if ($method) $field .= " method='" . $method . "'";
			if ($class) $field .= " class='" . $class . "'";
			if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
			if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
			$field .= ">\n";
			if ($name) $field .= "<input type='hidden' name='formName' id='formName' value='" . $name . "' />";
			
			return $field;
			
		}
		
		public function end() {
			return "</form>\n";
		}
		
		public function paginate($currentPage, $numberOfPages, $urlStructure) {

			/* 	Note that URL structure must contain a # where the
				page number will appear */
			
			// Calculations
				if ($currentPage > 1) $backEventHandler = array('onClick'=>'document.location.href="' . str_replace('#', $currentPage - 1, $urlStructure) . '"; return false;');
				else $backEventHandler = null;
									
				if ($currentPage < $numberOfPages) $nextEventHandler = array('onClick'=>'document.location.href="' . str_replace('#', $currentPage + 1, $urlStructure) . '"; return false;');
				else $nextEventHandler = null;
									
				$allPages = array();
				for ($counter = 1; $counter <= $numberOfPages; $counter++) {
					$allPages[$counter] = $counter;
				}
				
			// Display								
				$paginate = "<!-- Pagination -->\n";
				$paginate .= "  <div id='pagination' class='row'>\n";
				
				/* Back */		$paginate .= "    <div class='col-lg-4 col-md-4 col-sm-3 col-xs-3'>" . $this->input('button', 'paginateButtonBack', null, false, '<', 'btn btn-default btn-block', null, null, null, null, @$backEventHandler) . "</div>\n";
				/* Select */	$paginate .= "    <div class='col-lg-4 col-md-4 col-sm-6 col-xs-6'>" . $this->input('select', 'selectPage', $currentPage, true, null, 'form-control', $allPages, null, null, null, array('onChange'=>'document.location.href="' . str_replace('#', '" + this.value + "', $urlStructure) . '"; return false;')) . "</div>\n";
				/* Next */		$paginate .= "    <div class='col-lg-4 col-md-4 col-sm-3 col-xs-3'>" . $this->input('button', 'paginateButtonBack', null, false, '>', 'btn btn-default btn-block', null, null, null, null, @$nextEventHandler) . "</div>\n";
					
				$paginate .= "  </div>\n";

			// Return
				return $paginate;
			
		}
		
	}
	
/*
	Form Builder

	::	DESCRIPTION
	
		Creates form fields and, if necessary, enclosing CSS scaffording to
		optimize form creation.

	::	DEPENDENT ON
	
		operators_TL
	
	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) Timothy Quinn / Tidal Lock / Consolidated Biro
		
		Permission is hereby granted, free of charge, to any person
		obtaining a copy of this software and associated documentation
		files (the "Software"), to deal in the Software without
		restriction, including without limitation the rights to use,
		copy, modify, merge, publish, distribute, sublicense, and/or
		sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following
		conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
		OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
		HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
		WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
		OTHER DEALINGS IN THE SOFTWARE.
*/

?>
